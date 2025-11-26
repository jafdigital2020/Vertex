<?php

namespace App\Http\Controllers\Tenant\Leave;

use Carbon\Carbon;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\LeaveSetting;
use Illuminate\Http\Request;
use App\Models\LeaveApproval;
use App\Models\LeaveEntitlement;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\LeaveFiledNotification;
use App\Http\Controllers\DataAccessController;

class LeaveEmployeeController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    private function hasPermission(string $action, int $moduleId = 20): bool
    {
        // For API (token) requests, skip session-based PermissionHelper and allow controller-level ownership checks
        if (request()->is('api/*') || request()->expectsJson()) {
            return true;
        }

        $permission = PermissionHelper::get($moduleId);
        return in_array($action, $permission);
    }

    public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(20);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $dateRange = $request->input('dateRange');
        $status = $request->input('status');
        $leavetype = $request->input('leavetype');

        $query = LeaveRequest::with([
            'leaveType',
            'latestApproval.approver.personalInformation',
            'latestApproval.approver.employmentDetail.department',
        ])
            ->where('user_id', $authUserId)
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc');

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->where(function ($q) use ($start, $end) {
                    $q->whereDate('start_date', '<=', $end)
                        ->whereDate('end_date', '>=', $start);
                });
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date range format.'
                ]);
            }
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($leavetype) {
            $query->where('leave_type_id', $leavetype);
        }

        $leaveRequests = $query->get();

        foreach ($leaveRequests as $lr) {
            if ($la = $lr->latestApproval) {
                $approver = $la->approver;
                $pi       = optional($approver->personalInformation);
                $dept     = optional($approver->employmentDetail->department)->department_name;

                $lr->lastApproverName = trim("{$pi->first_name} {$pi->last_name}");
                $lr->lastApproverDept = $dept ?: '—';
            } else {
                $lr->lastApproverName = null;
                $lr->lastApproverDept = null;
            }
        }

        $html = view('tenant.leave.employeeleave_filter', compact('leaveRequests', 'permission'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function leaveEmployeeIndex(Request $request)
    {
        $user = Auth::user();;
        $permission = PermissionHelper::get(20);
        $today = Carbon::today()->toDateString();

        $entitledTypeIds = LeaveEntitlement::where('user_id', $user?->id)
            ->where('period_start', '<=', $today)
            ->where('period_end',   '>=', $today)
            ->pluck('leave_type_id')
            ->unique()
            ->toArray();

        $leaveTypes = LeaveType::with(['leaveSetting', 'leaveEntitlement'])
            ->whereIn('id', $entitledTypeIds)
            ->get();

        $ents = LeaveEntitlement::where('user_id', $user?->id)
            ->whereIn('leave_type_id', $entitledTypeIds)
            ->where('period_start', '<=', $today)
            ->where('period_end',   '>=', $today)
            ->get()
            ->keyBy('leave_type_id');

        $leaveTypes->transform(function ($lt) use ($ents) {
            $lt->current_balance = optional($ents[$lt->id])->current_balance ?? 0;
            return $lt;
        });

        $startOfYear = now()->startOfYear();
        $endOfYear   = now()->endOfYear();

        $leaveRequests = LeaveRequest::with([
            'leaveType',
            'leaveType.leaveRequest.user.personalInformation',
            'latestApproval.approver.personalInformation',
            'latestApproval.approver.employmentDetail.department',
        ])
            ->where('user_id', $user?->id)
            ->whereBetween('start_date', [$startOfYear, $endOfYear])
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($leaveRequests as $lr) {
            if ($la = $lr->latestApproval) {
                $approver = $la->approver;
                $pi       = optional($approver->personalInformation);
                $dept     = optional($approver->employmentDetail->department)->department_name;

                $lr->lastApproverName = trim("{$pi->first_name} {$pi->last_name}");
                $lr->lastApproverDept = $dept ?: '—';
            } else {
                $lr->lastApproverName = null;
                $lr->lastApproverDept = null;
            }
        }


        if ($request->wantsJson()) {
            return response()->json([
                'message'    => 'Available leave types fetched.',
                'status'     => 'success',
                'leaveTypes' => $leaveTypes->keyBy('id'),
                'leaveRequests' => $leaveRequests,
            ], 200);
        }

        // in blade we'll inject the same keyed JSON
        return view('tenant.leave.employeeleave', [
            'leaveTypes' => $leaveTypes,
            'leaveRequests' => $leaveRequests,
            'permission' => $permission
        ]);
    }
    public function sendLeaveNotificationToApprover($authUser, $leaveStartDate, $leaveEndDate)
    {
        $employment = $authUser->employmentDetail;
        $reportingToId = optional($employment)->reporting_to;
        $branchId = optional($employment)->branch_id;

        $requestor = trim(optional($authUser->personalInformation)->first_name.' '.optional($authUser->personalInformation)->last_name);

        $notifiedUser = null;
 
        if ($reportingToId) {
            $notifiedUser = User::find($reportingToId);
        } 
        else { 
            $steps = LeaveApproval::stepsForBranch($branchId); 
            $firstStep = $steps->first();

            if ($firstStep) {
                if ($firstStep->approver_kind === 'department_head') {

                    $departmentHeadId = optional(optional($employment)->department)->head_of_department;
                    if ($departmentHeadId) {
                        $notifiedUser = User::find($departmentHeadId);
                    }

                } elseif ($firstStep->approver_kind === 'user') {

                    $userApproverId = $firstStep->approver_user_id;
                    if ($userApproverId) {
                        $notifiedUser = User::find($userApproverId);
                    }

                }
            }
        } 
        if ($notifiedUser) {
            $message = "New leave request from {$requestor}: {$leaveStartDate} - {$leaveEndDate}. Pending your approval.";
            $notifiedUser->notify(new UserNotification($message));
        }
    }


    public function leaveEmployeeRequest(Request $request)
    {
        $authUser   = $this->authUser();
        $tenantId   = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;

        if (!$this->hasPermission('Create')) {
            return response()->json([
                'status' => 'error',
                'message' => "Sorry, you can’t file a leave right now. Your account doesn’t have permission to do this.",
            ], 403);
        }

        // Validate that the leave type exists (friendlier than findOrFail)
        $leaveTypeId = (int) $request->input('leave_type_id');
        $type = LeaveType::find($leaveTypeId);
        if (!$type) {
            return response()->json([
                'message' => "We couldn’t find the leave type you selected. Please choose a valid leave type.",
            ], 422);
        }

        // Settings check
        $cfg = LeaveSetting::where('leave_type_id', $type->id)->first();
        if (!$cfg) {
            return response()->json([
                'message' => "Leave settings for this type aren’t set up yet. Please contact HR.",
            ], 422);
        }

        // Build date rules based on settings
        $today = \Carbon\Carbon::today();
        if ($cfg->allow_backdated) {
            $min = $today->copy()->subDays($cfg->backdated_days);
        } else {
            $min = $today->copy()->addDays($cfg->advance_notice_days);
        }
        $minDate = $min->toDateString();

        // Validation rules
        $rules = [
            'leave_type_id'  => 'required|integer|exists:leave_types,id',
            'start_date'     => "required|date|after_or_equal:{$minDate}",
            'end_date'       => "required|date|after_or_equal:start_date|after_or_equal:{$minDate}",
            'reason'         => 'nullable|string|max:1000',
        ];

        // Half-day logic
        if ($cfg->allow_half_day) {
            $rules['half_day_type'] = 'nullable|in:AM,PM';
        } else {
            $rules['half_day_type'] = 'prohibited';
        }

        // Attachment logic
        if ($request->hasFile('file_attachment')) {
            // File is present, validate it
            $rules['file_attachment'] = 'file|mimes:pdf,jpg,jpeg,png|max:2048';
        } elseif ($cfg->require_documents) {
            // File is required but not present
            return response()->json([
                'message' => 'Please attach a supporting document (PDF, JPG, JPEG, or PNG).',
            ], 422);
        }

        // Custom, layman-style messages
        $messages = [
            'leave_type_id.required' => 'Please select a leave type.',
            'leave_type_id.integer'  => 'Please select a valid leave type.',
            'leave_type_id.exists'   => 'That leave type is not available.',

            'start_date.required'    => 'Please choose when your leave starts.',
            'start_date.date'        => 'Start date must be a valid date.',
            "start_date.after_or_equal" => $cfg->allow_backdated
                ? "Start date can’t be earlier than {$minDate}."
                : ($cfg->advance_notice_days > 0
                    ? "Start date must be on or after {$minDate} (advance notice needed)."
                    : "Start date must be today or later."),

            'end_date.required'      => 'Please choose when your leave ends.',
            'end_date.date'          => 'End date must be a valid date.',
            'end_date.after_or_equal' => 'End date must be the same as or later than your start date.',

            'half_day_type.in'       => 'Half day must be either AM or PM.',
            'half_day_type.prohibited' => 'Half-day filing is not allowed for this leave type.',

            'file_attachment.required' => 'Please attach a supporting document.',
            'file_attachment.file'     => 'The attachment must be a valid file.',
            'file_attachment.mimes'    => 'Allowed file types: PDF, JPG, JPEG, PNG.',
            'file_attachment.max'      => 'File is too large. Maximum size is 2MB.',

            'reason.max'             => 'Reason is too long. Keep it within 1,000 characters.',
        ];

        // Human-friendly field names
        $attributes = [
            'leave_type_id'  => 'leave type',
            'start_date'     => 'start date',
            'end_date'       => 'end date',
            'half_day_type'  => 'half-day selection',
            'file_attachment' => 'attachment',
            'reason'         => 'reason',
        ];

        // Run validator so we can format the error nicely
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->fails()) {
            // Return the first, clearest message
            $first = collect($validator->errors()->all())->first();
            return response()->json(['message' => $first], 422);
        }
        $data = $validator->validated();

        // Compute days requested
        $start = \Carbon\Carbon::parse($data['start_date']);
        $end   = \Carbon\Carbon::parse($data['end_date']);
        $span  = $start->diffInDays($end) + 1;
        $currentUser = $request->user() ?? User::find($authUserId);
        $daysRequested = ($cfg->allow_half_day && !empty($data['half_day_type'])) ? 0.5 : $span;

        // Check balance
        $ent = LeaveEntitlement::where('user_id', $request->user()->id)
            ->where('leave_type_id', $type->id)
            ->where('period_start', '<=', $today->toDateString())
            ->where('period_end',   '>=', $today->toDateString())
            ->first();

        if (!$ent || $ent->current_balance < $daysRequested) {
            return response()->json([
                'message' => "You don’t have enough leave credits for the dates you chose.",
            ], 422);
        }

        // Handle file upload
        $path = null;
        if ($request->hasFile('file_attachment')) {
            $file = $request->file('file_attachment');

            // ⭐ Additional validation for API requests
            if (!$file->isValid()) {
                return response()->json([
                    'message' => 'The uploaded file is invalid or corrupted. Please try again.',
                ], 422);
            }

            $path = $file->store("leave_requests/{$currentUser->id}", 'public');
        }

        // Create leave request
        $lr = LeaveRequest::create([
            'tenant_id'       => $tenantId,
            'user_id'         => $request->user()->id,
            'leave_type_id'   => $type->id,
            'start_date'      => $data['start_date'],
            'end_date'        => $data['end_date'],
            'days_requested'  => $daysRequested,
            'half_day_type'   => $data['half_day_type'] ?? null,
            'file_attachment' => $path,
            'reason'          => $data['reason'] ?? null,
        ]);

        $this->sendLeaveNotificationToApprover($authUser ,  $data['start_date'], $data['end_date']);
        
        // ================= EMAIL NOTIFICATION TO FIRST APPROVER ===================
        $employee = $request->user();
        $branchId = optional($lr->user->employmentDetail)->branch_id;
        $steps    = LeaveApproval::stepsForBranch($branchId);
        $firstStep = $steps->firstWhere('level', 1);

        $firstStepApprover  = null;
        $firstStepApprovers = collect();

        if ($reportingToId = optional($lr->user->employmentDetail)->reporting_to) {
            $firstStepApprover = User::find($reportingToId);
        } elseif ($firstStep) {
            switch ($firstStep->approver_kind) {
                case 'user':
                    $firstStepApprover = User::find($firstStep->approver_user_id);
                    break;

                case 'department_head':
                    $head = optional(optional($lr->user->employmentDetail)->department)->head_of_department;
                    if ($head instanceof User) {
                        $firstStepApprover = $head;
                    } elseif (is_numeric($head)) {
                        $firstStepApprover = User::find($head);
                    }
                    break;

                case 'role':
                    $firstStepApprovers = User::role($firstStep->approver_value)->get();
                    break;
            }
        }

        if ($firstStepApprover) {
            $firstStepApprover->notify(new LeaveFiledNotification($employee, $lr));
            Log::info("Leave notification sent to single approver", [
                'employee_id'      => $employee->id,
                'employee_email'   => $employee->email,
                'approver_id'      => $firstStepApprover->id,
                'approver_email'   => $firstStepApprover->email,
                'leave_request_id' => $lr->id
            ]);
        }

        if ($firstStepApprovers->count()) {
            foreach ($firstStepApprovers as $approver) {
                $approver->notify(new LeaveFiledNotification($employee, $lr));
                Log::info("Leave notification sent to role-based approver", [
                    'employee_id'      => $employee->id,
                    'employee_email'   => $employee->email,
                    'approver_id'      => $approver->id,
                    'approver_email'   => $approver->email,
                    'leave_request_id' => $lr->id
                ]);
            }
        }

        if (!$firstStepApprover && $firstStepApprovers->isEmpty()) {
            Log::warning("Leave notification: No approver found", [
                'employee_id'      => $employee->id,
                'employee_email'   => $employee->email,
                'leave_request_id' => $lr->id,
                'branch_id'        => $branchId
            ]);
            // Optional: tell user gently, but still 201
            return response()->json([
                'message' => 'Your leave was filed, but we couldn’t find an approver. Your Administrator will review it.',
                'leave_request' => $lr,
            ], 201);
        }

        return response()->json([
            'message'       => 'Your leave request was sent. We’ll notify your approver.',
            'leave_request' => $lr,
        ], 201);
    }

    public function leaveEmployeeRequestEdit(Request $request, $id)
    {

        if (!$this->hasPermission('Update')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }

        // only allow editing your own request
        $lr = LeaveRequest::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($lr->status !== 'pending' || $lr->current_step !== 1) {
            return response()->json([
                'message' => 'This leave request can no longer be edited.'
            ], 403);
        }

        $type = LeaveType::findOrFail($request->input('leave_type_id'));
        $cfg = LeaveSetting::where('leave_type_id', $type->id)->firstOrFail();

        // base rules
        $rules = [
            'leave_type_id' => 'required|integer|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'nullable|string|max:1000',
        ];

        // half-day
        if ($cfg->allow_half_day) {
            $rules['half_day_type'] = 'nullable|in:AM,PM';
        } else {
            $rules['half_day_type'] = 'prohibited';
        }

        // document
        $rules['file_attachment'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048';

        // advance/back-date logic
        $today = Carbon::today();
        if ($cfg->allow_backdated) {
            $min = $today->subDays($cfg->backdated_days);
        } else {
            $min = $today->addDays($cfg->advance_notice_days);
        }
        $minDate = $min->toDateString();
        $rules['start_date'] .= "|after_or_equal:{$minDate}";
        $rules['end_date']   .= "|after_or_equal:{$minDate}";

        $data = $request->validate($rules);

        // compute days requested
        $start = Carbon::parse($data['start_date']);
        $end   = Carbon::parse($data['end_date']);
        $span  = $start->diffInDays($end) + 1;
        $daysRequested = ($cfg->allow_half_day && !empty($data['half_day_type']))
            ? 0.5
            : $span;

        // check entitlement (give back the original days so you don’t accidentally “double-charge”)
        $ent = LeaveEntitlement::where('user_id', $request->user()->id)
            ->where('leave_type_id', $type->id)
            ->where('period_start', '<=', $today->toDateString())
            ->where('period_end',   '>=', $today->toDateString())
            ->firstOrFail();

        $available = $ent->current_balance + $lr->days_requested;
        if ($available < $daysRequested) {
            return response()->json(['message' => 'Insufficient leave balance.'], 422);
        }

        // handle new file
        if ($request->hasFile('file_attachment')) {
            if ($lr->file_attachment) {
                Storage::disk('public')->delete($lr->file_attachment);
            }
            $lr->file_attachment = $request
                ->file('file_attachment')
                ->store("leave_requests/{$request->user()->id}", 'public');
        }

        // persist updates
        $lr->update([
            'leave_type_id'  => $type->id,
            'start_date'     => $data['start_date'],
            'end_date'       => $data['end_date'],
            'days_requested' => $daysRequested,
            'half_day_type'  => $data['half_day_type'] ?? null,
            'reason'         => $data['reason'] ?? null,
            'file_attachment' => $lr->file_attachment,
        ]);

        return response()->json([
            'message'       => 'Leave request updated successfully.',
            'leave_request' => $lr,
        ], 200);
    }

    public function leaveEmployeeRequestDelete(Request $request, $id)
    {
        if (!$this->hasPermission('Delete')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        // only allow deleting your own request
        $lr = LeaveRequest::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($lr->status !== 'pending' || $lr->current_step !== 1) {
            return response()->json([
                'message' => 'This leave request can no longer be deleted.'
            ], 403);
        }

        // delete file attachment if exists
        if ($lr->file_attachment) {
            Storage::disk('public')->delete($lr->file_attachment);
        }

        // delete the leave request
        $lr->delete();

        return response()->json([
            'message' => 'Leave request deleted successfully.'
        ], 200);
    }
}
