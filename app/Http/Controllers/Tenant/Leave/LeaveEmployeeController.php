<?php

namespace App\Http\Controllers\Tenant\Leave;

use Carbon\Carbon;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\LeaveSetting;
use Illuminate\Http\Request;
use App\Models\LeaveEntitlement;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\DataAccessController;

class LeaveEmployeeController extends Controller
{
    public function authUser() {
      if (Auth::guard('global')->check()) {
         return Auth::guard('global')->user();
      }
      return Auth::guard('web')->user();
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

        $leaveRequests = LeaveRequest::with([
            'leaveType',
            'latestApproval.approver.personalInformation',
            'latestApproval.approver.employmentDetail.department',
        ])->where('user_id', $user?->id)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
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

    public function leaveEmployeeRequest(Request $request)
    {

        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(20);
        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
        }

        $type = LeaveType::findOrFail($request->input('leave_type_id'));

        $cfg = LeaveSetting::where('leave_type_id', $type->id)
            ->firstOrFail();

        $rules = [
            'leave_type_id'   => 'required|integer|exists:leave_types,id',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'reason'          => 'nullable|string|max:1000',
        ];

        // half_day_type: nullable = full day, AM/PM = half day
        if ($cfg->allow_half_day) {
            $rules['half_day_type'] = 'nullable|in:AM,PM';
        } else {
            $rules['half_day_type'] = 'prohibited';
        }

        // supporting document
        if ($cfg->require_documents) {
            $rules['file_attachment'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
        } else {
            $rules['file_attachment'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048';
        }

        // advance-notice vs back-dated
        $today = Carbon::today();
        if ($cfg->allow_backdated) {
            $min = $today->copy()->subDays($cfg->backdated_days);
        } else {
            $min = $today->copy()->addDays($cfg->advance_notice_days);
        }
        $minDate = $min->toDateString();
        $rules['start_date'] .= "|after_or_equal:{$minDate}";
        $rules['end_date']   .= "|after_or_equal:{$minDate}";

        $data = $request->validate($rules);

        $start = Carbon::parse($data['start_date']);
        $end   = Carbon::parse($data['end_date']);
        $span  = $start->diffInDays($end) + 1;
        $daysRequested = ($cfg->allow_half_day && !empty($data['half_day_type']))
            ? 0.5
            : $span;

        $ent = LeaveEntitlement::where('user_id', $request->user()->id)
            ->where('leave_type_id', $type->id)
            ->where('period_start', '<=', $today->toDateString())
            ->where('period_end',   '>=', $today->toDateString())
            ->first();

        if (! $ent || $ent->current_balance < $daysRequested) {
            return response()->json([
                'message' => 'Insufficient leave balance.'
            ], 422);
        }

        $path = null;
        if ($request->hasFile('file_attachment')) {
            $path = $request->file('file_attachment')
                ->store("leave_requests/{$request->user()->id}", 'public');
        }

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

        return response()->json([
            'message'       => 'Leave request submitted and is now pending.',
            'leave_request' => $lr,
        ], 201);
    }

    public function leaveEmployeeRequestEdit(Request $request, $id)
    {

        $permission = PermissionHelper::get(20);
        if (!in_array('Update', $permission)) {
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
        $permission = PermissionHelper::get(20);
        if (!in_array('Delete', $permission)) {
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
