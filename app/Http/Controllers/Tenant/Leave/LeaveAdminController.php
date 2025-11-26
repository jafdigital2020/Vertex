<?php

namespace App\Http\Controllers\Tenant\Leave;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserLog;
use App\Models\LeaveType;
use App\Models\ApprovalStep;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Models\LeaveApproval;
use App\Models\LeaveEntitlement;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;
use App\Notifications\LeaveFinalStatusNotification;
use App\Notifications\LeaveNextApproverNotification;

class LeaveAdminController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(19);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $dateRange = $request->input('dateRange');
        $status = $request->input('status');
        $leavetype = $request->input('leavetype');

        $query = $accessData['leaveRequests']
            ->where('tenant_id', $tenantId)
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

        $approvedLeavesCount = $leaveRequests
            ->where('status', 'approved')
            ->count();

        $rejectedLeavesCount = $leaveRequests
            ->where('status', 'rejected')
            ->count();

        $pendingLeavesCount = $leaveRequests
            ->where('status', 'pending')
            ->count();

        foreach ($leaveRequests as $lr) {
            $branchId = optional($lr->user->employmentDetail)->branch_id;
            $steps = LeaveApproval::stepsForBranch($branchId);
            $lr->total_steps     = $steps->count();
            $lr->next_approvers = LeaveApproval::nextApproversFor($lr, $steps);

            if ($latest = $lr->latestApproval) {
                $approver = $latest->approver;
                $pi       = optional($approver->personalInformation);

                $lr->last_approver = trim("{$pi->first_name} {$pi->last_name}");
                $lr->last_approver_type = optional(
                    optional($approver->employmentDetail)->branch
                )->name ?? 'Global';
            } else {
                $lr->last_approver      = null;
                $lr->last_approver_type = null;
            }

            // Fetch the remaining balance (current_balance) for the user and leave type
            $leaveEntitlement = LeaveEntitlement::where('user_id', $lr->user_id)
                ->where('leave_type_id', $lr->leave_type_id)
                ->first();

            // If entitlement found, get the remaining balance
            if ($leaveEntitlement) {
                $remainingBalance = $leaveEntitlement->current_balance;
                $lr->remaining_balance = $remainingBalance;  // Set the remaining balance for the leave request
            } else {
                $lr->remaining_balance = 0; // Default to 0 if no entitlement is found
            }
        }

        $html = view('tenant.leave.adminleave_filter', compact('leaveRequests', 'permission'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'approvedLeavesCount' => $approvedLeavesCount,
            'rejectedLeavesCount' => $rejectedLeavesCount,
            'pendingLeavesCount' => $pendingLeavesCount,
        ]);
    }

    public function leaveAdminIndex(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(19);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();

        $leaveRequests = $accessData['leaveRequests']
            ->where('tenant_id', $tenantId)
            ->whereBetween('start_date', [$startOfYear, $endOfYear])
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc')
            ->with(['user.personalInformation', 'user.employmentDetail'])
            ->get();

        // total Approved leave for this year
        $approvedLeavesCount =  $leaveRequests
            ->where('status', 'approved')
            ->whereBetween('start_date', [$startOfYear, $endOfYear])
            ->count();

        // total Rejected leave for this year
        $rejectedLeavesCount = $leaveRequests
            ->where('status', 'rejected')
            ->whereBetween('start_date', [$startOfYear, $endOfYear])
            ->count();

        // total Pending leave for this year
        $pendingLeavesCount = $leaveRequests
            ->where('status', 'pending')
            ->whereBetween('start_date', [$startOfYear, $endOfYear])
            ->count();


        $entitledTypeIds = LeaveEntitlement::where('period_start', '<=', $today)
            ->where('period_end', '>=', $today)
            ->pluck('leave_type_id')
            ->unique()
            ->toArray();

        $leaveTypes = LeaveType::with(['leaveSetting', 'leaveEntitlement'])
            ->whereIn('id', $entitledTypeIds)
            ->get();

        // Ensure $leaveTypes is always defined as a collection
        if (!isset($leaveTypes)) {
            $leaveTypes = collect();
        }

        // Compute once
        foreach ($leaveRequests as $lr) {
            $branchId = optional($lr->user->employmentDetail)->branch_id;
            $steps = LeaveApproval::stepsForBranch($branchId);
            $lr->total_steps     = $steps->count();

            // Reporting to
            $reportingToId = optional($lr->user->employmentDetail)->reporting_to;

            if ($lr->status === 'pending') {
                if ($lr->current_step === 1 && $reportingToId) {
                    // Show manager name
                    $manager = User::with('personalInformation')->find($reportingToId);
                    if ($manager && $manager->personalInformation) {
                        $managerName = trim("{$manager->personalInformation->first_name} {$manager->personalInformation->last_name}");
                        $lr->next_approvers = [$managerName];
                    } else {
                        $lr->next_approvers = [];
                    }
                } else {
                    $lr->next_approvers = LeaveApproval::nextApproversFor($lr, $steps);
                }
            } else {
                $lr->next_approvers = []; // No next approvers if not pending
            }

            // Handle last approver info
            if ($latest = $lr->latestApproval) {
                $approver = $latest->approver;
                $pi       = optional($approver->personalInformation);

                $lr->last_approver = trim("{$pi->first_name} {$pi->last_name}");
                $lr->last_approver_type = optional(
                    optional($approver->employmentDetail)->branch
                )->name ?? 'Global';
            } else {
                $lr->last_approver      = null;
                $lr->last_approver_type = null;
            }

            // Fetch the remaining balance (current_balance) for the user and leave type
            $leaveEntitlement = LeaveEntitlement::where('user_id', $lr->user_id)
                ->where('leave_type_id', $lr->leave_type_id)
                ->first();

            // If entitlement found, get the remaining balance
            if ($leaveEntitlement) {
                $remainingBalance = $leaveEntitlement->current_balance;
                $lr->remaining_balance = $remainingBalance;  // Set the remaining balance for the leave request
            } else {
                $lr->remaining_balance = 0; // Default to 0 if no entitlement is found
            }
        }


        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'This is the leave admin index endpoint.',
                'status' => 'success',
                'leaveRequests' => $leaveRequests,
                'leaveTypes' => $leaveTypes,
                'approvedLeavesCount' => $approvedLeavesCount,
                'rejectedLeavesCount' => $rejectedLeavesCount,
                'pendingLeavesCount' => $pendingLeavesCount,
            ]);
        }

        return view('tenant.leave.adminleave', [
            'leaveRequests' => $leaveRequests,
            'leaveTypes'    => $leaveTypes,
            'approvedLeavesCount' => $approvedLeavesCount,
            'rejectedLeavesCount' => $rejectedLeavesCount,
            'pendingLeavesCount' => $pendingLeavesCount,
            'permission' => $permission
        ]);
    }

    public function leaveApproval(Request $request, LeaveRequest $leave)
    {
        // 1) Validate payload
        $data = $request->validate([
            'action'  => 'required|in:APPROVED,REJECTED,CHANGES_REQUESTED',
            'comment' => 'nullable|string',
        ]);

        $user      = $request->user();
        $requester = $leave->user;
        $currStep  = $leave->current_step;
        $branchId  = (int) optional($leave->user->employmentDetail)->branch_id;
        $oldStatus = $leave->status;

        // 1.a) Prevent self-approval
        if ($user->id === $requester->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot approve your own leave request.',
            ], 403);
        }

        // 1.b) Prevent spamming a second "REJECTED"
        if ($data['action'] === 'REJECTED' && $oldStatus === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Leave request has already been rejected.',
            ], 400);
        }

        // 2) Build the approval workflow for this leave-owner's branch
        $steps = LeaveApproval::stepsForBranch($branchId);
        $maxLevel = $steps->max('level');
        $reportingToId = optional($leave->user->employmentDetail)->reporting_to;

        // 3) Special rule: If reporting_to exists, only that user can approve at step 1, and auto-approved na dapat
        if ($currStep === 1 && $reportingToId) {
            if ($user->id !== $reportingToId) {
                // Not allowed: Only reporting manager can approve step 1 if set
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot approve this request.',
                ], 403);
            }

            // If action is approved, auto-final approve (skip further steps)
            $mapStatus = [
                'APPROVED'          => 'approved',
                'REJECTED'          => 'rejected',
                'CHANGES_REQUESTED' => 'pending',
            ];
            $newStatus = $mapStatus[$data['action']];

            DB::transaction(function () use ($leave, $data, $user, $newStatus) {
                LeaveApproval::create([
                    'leave_request_id' => $leave->id,
                    'approver_id'      => $user->id,
                    'step_number'      => 1,
                    'action'           => strtolower($data['action']),
                    'comment'          => $data['comment'] ?? null,
                    'acted_at'         => Carbon::now(),
                ]);
                if ($data['action'] === 'APPROVED') {
                    $leave->update([
                        'current_step' => 1,
                        'status'       => 'approved',
                    ]);
 
                    $requesterNotif = User::find($leave->user_id); 
                    $start = Carbon::parse($leave->start_date)->format('M d');
                    $end   = Carbon::parse($leave->end_date)->format('M d');

                    $requesterNotif->notify(new UserNotification(
                        'Your ' . $leave->leaveType->name . ' for ' . $start . ' - ' . $end . ' has been approved.'
                    ));  

                    // Deduct leave days
                    $ent = LeaveEntitlement::where('user_id', $leave->user_id)
                        ->where('leave_type_id', $leave->leave_type_id)
                        ->where('period_start', '<=', $leave->start_date)
                        ->where('period_end',   '>=', $leave->end_date)
                        ->first();
                    optional($ent)->decrement('current_balance', $leave->days_requested);
                } else {
                    // REJECTED or CHANGES_REQUESTED
                    $leave->update(['status' => $newStatus]);
                    $requesterNotif = User::find($leave->user_id); 
                    $start = Carbon::parse($leave->start_date)->format('M d');
                    $end   = Carbon::parse($leave->end_date)->format('M d');

                    $requesterNotif->notify(new UserNotification(
                        'Your ' . $leave->leaveType->name . ' for ' . $start . ' - ' . $end . ' has been rejected.'
                    ));
                }
            });

            // SEND EMAIL TO REQUESTER ABOUT APPROVAL/REJECTION
            if ($data['action'] === 'APPROVED' || $data['action'] === 'REJECTED') {
                $requester->notify(new LeaveFinalStatusNotification($leave, $data['action'], $user));
                Log::info('Leave notification sent', [
                    'leave_request_id' => $leave->id,
                    'notification_type' => 'LeaveFinalStatusNotification',
                    'recipient_email' => $requester->email,
                    'recipient_name' => $requester->name ?? 'N/A',
                    'action' => $data['action'],
                    'sender_id' => $user->id
                ]);
            }

            $leave->refresh();
            return response()->json([
                'success'        => true,
                'message'        => 'Action recorded.',
                'data'           => $leave,
                'next_approvers' => [],
            ]);
        }

        // 4) If NO reporting_to, continue with the normal step workflow
        $cfg = $steps->firstWhere('level', $currStep);
        if (! $cfg) {
            return response()->json([
                'success' => false,
                'message' => 'Approval step misconfigured.',
            ], 500);
        }

        // 5) Authorization (same as before)
        $allowed = false;
        $deptHead = null;
        switch ($cfg->approver_kind) {
            case 'user':
                $allowed = ($user->id === $cfg->approver_user_id);
                break;
            case 'department_head':
                $deptHead = optional(optional($leave->user->employmentDetail)->department)
                    ->head_of_department;
                $allowed  = ($deptHead && $user->id === $deptHead);
                break;
            case 'role':
                $allowed = $user->hasRole($cfg->approver_value);
                break;
        }
        if (! $allowed) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized for this step.',
            ], 403);
        }

        $mapStatus = [
            'APPROVED'          => 'approved',
            'REJECTED'          => 'rejected',
            'CHANGES_REQUESTED' => 'pending',
        ];
        $newStatus = $mapStatus[$data['action']];

        DB::transaction(function () use (
            $leave,
            $data,
            $user,
            $currStep,
            $newStatus,
            $oldStatus,
            $maxLevel
        ) {
            LeaveApproval::create([
                'leave_request_id' => $leave->id,
                'approver_id'      => $user->id,
                'step_number'      => $currStep,
                'action'           => strtolower($data['action']),
                'comment'          => $data['comment'] ?? null,
                'acted_at'         => Carbon::now(),
            ]);

            if ($data['action'] === 'APPROVED') {
                if ($currStep < $maxLevel) {
                    $leave->update([
                        'current_step' => $currStep + 1,
                        'status'       => 'pending',
                    ]);
                    $requesterNotif = User::find($leave->user_id); 
                    $start = Carbon::parse($leave->start_date)->format('M d');
                    $end   = Carbon::parse($leave->end_date)->format('M d'); 
                    
                    $requesterNotif->notify(new UserNotification(
                        'Your ' . $leave->leaveType->name . ' for ' . $start . ' - ' . $end . ' has been pre-approved by Level '.$currStep. '.'
                    ));   
                } else {
                    $leave->update(['status' => 'approved']);
                    $requesterNotif = User::find($leave->user_id); 
                    $start = Carbon::parse($leave->start_date)->format('M d');
                    $end   = Carbon::parse($leave->end_date)->format('M d');

                    $requesterNotif->notify(new UserNotification(
                        'Your ' . $leave->leaveType->name . ' for ' . $start . ' - ' . $end . ' has been approved.'
                    ));  

                    // Deduct leave days
                    $ent = LeaveEntitlement::where('user_id', $leave->user_id)
                        ->where('leave_type_id', $leave->leave_type_id)
                        ->where('period_start', '<=', $leave->start_date)
                        ->where('period_end',   '>=', $leave->end_date)
                        ->first();
                    optional($ent)->decrement('current_balance', $leave->days_requested);
                }
            } else {
                // REJECTED or CHANGES_REQUESTED
                $leave->update(['status' => $newStatus]);
                $requesterNotif = User::find($leave->user_id); 
                $start = Carbon::parse($leave->start_date)->format('M d');
                $end   = Carbon::parse($leave->end_date)->format('M d');

                $requesterNotif->notify(new UserNotification(
                    'Your ' . $leave->leaveType->name . ' for ' . $start . ' - ' . $end . ' has been rejected.'
                ));    
                // refund only if it was previously approved
                if ($oldStatus === 'approved') {
                    $ent = LeaveEntitlement::where('user_id', $leave->user_id)
                        ->where('leave_type_id', $leave->leave_type_id)
                        ->where('period_start', '<=', $leave->start_date)
                        ->where('period_end',   '>=', $leave->end_date)
                        ->first();
                    optional($ent)->increment('current_balance', $leave->days_requested);
                }
            }
        });

        $leave->refresh();

        // Get next approvers based on the updated leave status
        if ($data['action'] === 'APPROVED' && $leave->status === 'pending') {
            $steps = LeaveApproval::stepsForBranch($branchId);
            $next = LeaveApproval::nextApproversForNotification($leave, $steps);

            Log::info('Processing next approvers', [
                'leave_request_id' => $leave->id,
                'current_step' => $leave->current_step,
                'next_approvers_count' => is_array($next) ? count($next) : (is_object($next) ? $next->count() : 0),
                'next_approvers_data' => $next
            ]);

            // Notify next approvers
            if (!empty($next)) {
                foreach ($next as $approver) {
                    $userToNotify = null;

                    Log::info('Processing approver', [
                        'leave_request_id' => $leave->id,
                        'approver_type' => gettype($approver),
                        'approver_data' => $approver
                    ]);

                    if ($approver instanceof User) {
                        $userToNotify = $approver;
                    } elseif (is_numeric($approver)) {
                        $userToNotify = User::find($approver);
                    } elseif (is_array($approver) && isset($approver['id'])) {
                        $userToNotify = User::find($approver['id']);
                    } elseif (is_object($approver) && isset($approver->id)) {
                        $userToNotify = User::find($approver->id);
                    }

                    if ($userToNotify && $userToNotify->email) {
                        try {
                            $userToNotify->notify(new LeaveNextApproverNotification($leave));
                            Log::info('Next approver notification sent', [
                                'leave_request_id' => $leave->id,
                                'notification_type' => 'LeaveNextApproverNotification',
                                'recipient_email' => $userToNotify->email,
                                'recipient_name' => $userToNotify->name ?? 'N/A',
                                'recipient_id' => $userToNotify->id,
                                'action' => 'NEXT_APPROVAL_REQUIRED',
                                'sender_id' => $user->id
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to send next approver notification', [
                                'leave_request_id' => $leave->id,
                                'recipient_email' => $userToNotify->email ?? 'N/A',
                                'recipient_id' => $userToNotify->id ?? 'N/A',
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    } else {
                        Log::warning('Next approver user not found or has no email', [
                            'leave_request_id' => $leave->id,
                            'approver_data' => $approver,
                            'user_found' => $userToNotify ? 'yes' : 'no',
                            'user_email' => $userToNotify->email ?? 'N/A'
                        ]);
                    }
                }
            } else {
                Log::warning('No next approvers found', [
                    'leave_request_id' => $leave->id,
                    'current_step' => $leave->current_step,
                    'status' => $leave->status
                ]);
            }
        } elseif ($data['action'] === 'APPROVED' && $leave->status === 'approved') {
            // Final approval - notify requester
            $next = [];
            try {
                $requester->notify(new LeaveFinalStatusNotification($leave, 'APPROVED', $user));
                Log::info('Final approval notification sent', [
                    'leave_request_id' => $leave->id,
                    'notification_type' => 'LeaveFinalStatusNotification',
                    'recipient_email' => $requester->email,
                    'recipient_name' => $requester->name ?? 'N/A',
                    'action' => 'APPROVED',
                    'sender_id' => $user->id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send final approval notification', [
                    'leave_request_id' => $leave->id,
                    'recipient_email' => $requester->email,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            $next = [];
        }

        // If rejected, notify requester immediately
        if ($data['action'] === 'REJECTED') {
            try {
                $requester->notify(new LeaveFinalStatusNotification($leave, 'REJECTED', $user));
                Log::info('Rejection notification sent', [
                    'leave_request_id' => $leave->id,
                    'notification_type' => 'LeaveFinalStatusNotification',
                    'recipient_email' => $requester->email,
                    'recipient_name' => $requester->name ?? 'N/A',
                    'action' => 'REJECTED',
                    'sender_id' => $user->id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send rejection notification', [
                    'leave_request_id' => $leave->id,
                    'recipient_email' => $requester->email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success'        => true,
            'message'        => 'Action recorded.',
            'data'           => $leave,
            'next_approvers' => $next ?? [],
        ]);
    }

    public function leaveReject(Request $request, LeaveRequest $leave)
    {
        $data = $request->validate([
            'comment' => 'nullable|string',
        ]);

        $request->merge([
            'action'  => 'REJECTED',
            'comment' => $data['comment'],
        ]);

        return $this->leaveApproval($request, $leave);
    }

    // Edit Leave Request
    public function editLeaveRequest(Request $request, $leaveRequestId)
    {
        $permission = PermissionHelper::get(19);
        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'leave_type_id'    => 'required|integer|exists:leave_types,id',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date',
            'days_requested'   => 'required|numeric',
            'reason'           => 'nullable|string',
            'file_attachment'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',  // Adjust file validation as needed
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
        }

        // Find the LeaveRequest by ID
        $leaveRequest = LeaveRequest::findOrFail($leaveRequestId);

        // Store the old data for logging purposes
        $oldData = $leaveRequest->getOriginal();

        // Update the LeaveRequest data
        $leaveRequest->leave_type_id = $request->leave_type_id;
        $leaveRequest->start_date = $request->start_date;
        $leaveRequest->end_date = $request->end_date;
        $leaveRequest->days_requested = $request->days_requested;
        $leaveRequest->reason = $request->reason;

        // Handle file upload if provided
        if ($request->hasFile('file_attachment')) {
            // Store the new file
            $filePath = $request->file('file_attachment')->store('leave_requests', 'public');  // Store in public disk

            // If the leave request already has an old file, delete it
            if ($leaveRequest->file_attachment) {
                Storage::disk('public')->delete($leaveRequest->file_attachment);
            }

            // Update the file path in the database
            $leaveRequest->file_attachment = $filePath;
        }

        // Save the leave request
        $leaveRequest->save();

        // Update the remaining balance in LeaveEntitlement (if applicable)
        $leaveEntitlement = LeaveEntitlement::where('user_id', $leaveRequest->user_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('period_start', '<=', $leaveRequest->start_date)
            ->where('period_end', '>=', $leaveRequest->end_date)
            ->first();

        if ($leaveEntitlement) {
            // Decrease the remaining balance based on the days requested
            $leaveEntitlement->current_balance -= $leaveRequest->days_requested;

            // Ensure the balance doesn't go negative
            if ($leaveEntitlement->current_balance < 0) {
                return response()->json(['status' => 'error', 'message' => 'Insufficient leave balance.']);
            }

            // Save the updated remaining balance
            $leaveEntitlement->save();
        }

        // Logging the update action
        // Logging Start
        $empId = null;
        $globalUserId = null;

        // Check which guard is authenticated
        if (Auth::guard('web')->check()) {
            $empId = Auth::guard('web')->id();  // Regular employee user
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();  // Global admin user
        }

        // Create the log entry
        UserLog::create([
            'user_id' => $empId,
            'global_user_id' => $globalUserId,
            'module' => 'Leave Request',
            'action' => 'Update',
            'description' => 'Updated leave request',
            'affected_id' => $leaveRequest->id,
            'old_data' => json_encode($oldData),  // Store the old data before update
            'new_data' => json_encode($leaveRequest->toArray()),  // Store the new data after update
        ]);
        // Logging End

        // Return success response
        return response()->json([
            'status' => 'success',
            'message' => 'Leave request updated successfully!',
            'leaveRequest' => $leaveRequest,
        ]);
    }

    // Delete Leave Request
    public function deleteLeaveRequest($leaveRequestId)
    {
        $permission = PermissionHelper::get(19);
        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        try {
            // Find the LeaveRequest by ID
            $leaveRequest = LeaveRequest::findOrFail($leaveRequestId);
            $filePath = $leaveRequest->file_attachment;

            // Store old data for logging before deletion
            $oldData = $leaveRequest->toArray();

            // Delete the leave request
            $leaveRequest->delete();

            // Delete the file attachment if it exists
            if ($filePath) {
                // Deleting the file from public storage (using the relative path from database)
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            // Logging the delete action
            $empId = null;
            $globalUserId = null;

            // Check which guard is authenticated
            if (Auth::guard('web')->check()) {
                $empId = Auth::guard('web')->id();  // Regular employee user
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();  // Global admin user
            }

            // Create the log entry
            UserLog::create([
                'user_id' => $empId,
                'global_user_id' => $globalUserId,
                'module' => 'Leave Request',
                'action' => 'Delete',
                'description' => 'Deleted leave request',
                'affected_id' => $leaveRequestId,
                'old_data' => json_encode($oldData),  // Store the old data before deletion
                'new_data' => null,  // No new data after deletion
            ]);

            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => 'Leave request deleted successfully!',
            ]);
        } catch (Exception $e) {
            // Log the error with context for debugging
            Log::error('Error deleting leave request', [
                'leaveRequestId' => $leaveRequestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error while deleting leave request: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Leave Bulk Action
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,delete',
            'leave_ids' => 'required|array|min:1',
            'leave_ids.*' => 'exists:leave_requests,id',
            'comment' => 'nullable|string|max:500'
        ]);

        $action = $request->action;
        $leaveIds = $request->leave_ids;
        $comment = $request->comment ?? "Bulk {$action} by admin";
        $userId = Auth::id();
        $tenantId = Auth::user()->tenant_id;

        try {
            DB::beginTransaction();

            $successCount = 0;
            $errors = [];

            foreach ($leaveIds as $leaveId) {
                Log::info("Processing leave request", [
                    'leave_id' => $leaveId,
                    'action' => $action
                ]);

                try {

                    $leaveRequest = LeaveRequest::where('id', $leaveId)
                        ->where('tenant_id', $tenantId)
                        ->first();

                    if (!$leaveRequest) {
                        $error = "Leave request {$leaveId} not found";
                        $errors[] = $error;
                        Log::warning("Leave request not found", [
                            'leave_id' => $leaveId,
                            'tenant_id' => $tenantId
                        ]);
                        continue;
                    }


                    // For delete action, we can delete regardless of status
                    if ($action === 'delete') {
                        $leaveRequest->delete();
                        Log::info("Leave request deleted successfully", [
                            'leave_id' => $leaveId,
                            'user_id' => $userId
                        ]);
                    }
                    // For approve/reject, only process if status is pending
                    else {
                        // Check if already processed
                        if ($leaveRequest->status !== 'pending') {
                            $error = "Leave request {$leaveId} is already {$leaveRequest->status}";
                            $errors[] = $error;
                            Log::warning("Leave request already processed", [
                                'leave_id' => $leaveId,
                                'current_status' => $leaveRequest->status,
                                'attempted_action' => $action
                            ]);
                            continue;
                        }

                        // Process the action
                        if ($action === 'approve') {
                            $this->approveLeaveRequest($leaveRequest, $comment, $userId);
                            Log::info("Leave request approved successfully", [
                                'leave_id' => $leaveId,
                                'user_id' => $userId
                            ]);
                        } else {
                            $this->rejectLeaveRequest($leaveRequest, $comment, $userId);
                            Log::info("Leave request rejected successfully", [
                                'leave_id' => $leaveId,
                                'user_id' => $userId
                            ]);
                        }
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $error = "Failed to {$action} leave request {$leaveId}: " . $e->getMessage();
                    $errors[] = $error;
                    Log::error("Failed to process leave request in bulk action", [
                        'leave_id' => $leaveId,
                        'action' => $action,
                        'error_message' => $e->getMessage(),
                        'error_trace' => $e->getTraceAsString(),
                        'user_id' => $userId,
                        'tenant_id' => $tenantId
                    ]);
                }
            }

            DB::commit();

            $message = "Successfully {$action}d {$successCount} leave request(s).";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " failed.";
            }

            Log::info("Bulk action completed", [
                'action' => $action,
                'total_processed' => count($leaveIds),
                'successful' => $successCount,
                'failed' => count($errors),
                'errors' => $errors
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'processed' => $successCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Bulk action transaction failed", [
                'action' => $action,
                'leave_ids' => $leaveIds,
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Bulk action failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ Helper method for approving leave requests bulk action
    private function approveLeaveRequest($leaveRequest, $comment, $userId)
    {
        $user = User::find($userId);
        $requester = $leaveRequest->user;

        $requester->refresh();
        $requester->load('employmentDetail');

        $currStep = $leaveRequest->current_step;
        $branchId = (int) optional($requester->employmentDetail)->branch_id;
        $oldStatus = $leaveRequest->status;

        // 1) Prevent self-approval
        if ($user->id === $requester->id) {
            throw new \Exception('Cannot approve own leave request.');
        }

        // ✅ NEW: Check leave balance before processing approval
        $entitlement = LeaveEntitlement::where('user_id', $leaveRequest->user_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('period_start', '<=', $leaveRequest->start_date)
            ->where('period_end', '>=', $leaveRequest->end_date)
            ->first();

        if (!$entitlement) {
            throw new \Exception('No leave entitlement found for this leave type and period.');
        }

        if ($entitlement->current_balance < $leaveRequest->days_requested) {
            throw new \Exception("Insufficient leave balance. Available: {$entitlement->current_balance} days, Requested: {$leaveRequest->days_requested} days.");
        }

        Log::info('Leave balance check passed', [
            'leave_request_id' => $leaveRequest->id,
            'user_id' => $leaveRequest->user_id,
            'current_balance' => $entitlement->current_balance,
            'days_requested' => $leaveRequest->days_requested,
            'remaining_after_approval' => $entitlement->current_balance - $leaveRequest->days_requested
        ]);

        // 2) Build the approval workflow
        $steps = LeaveApproval::stepsForBranch($branchId);
        $maxLevel = $steps->max('level');

        // ✅ CRITICAL: Get CURRENT reporting_to (not cached)
        $reportingToId = optional($requester->employmentDetail)->reporting_to;

        // 3) Special rule: If reporting_to exists at step 1
        if ($currStep === 1 && $reportingToId) {
            if ($user->id !== $reportingToId) {
                throw new \Exception('Only the current reporting manager can approve this request.');
            }

            // Auto-final approve for reporting manager
            LeaveApproval::create([
                'leave_request_id' => $leaveRequest->id,
                'approver_id' => $user->id,
                'step_number' => 1,
                'action' => 'approved',
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            $leaveRequest->update([
                'current_step' => 1,
                'status' => 'approved',
            ]);

            $this->deductLeaveBalance($leaveRequest);
            return;
        }

        // 4) If NO reporting_to, continue with normal step workflow
        $cfg = $steps->firstWhere('level', $currStep);
        if (!$cfg) {
            throw new \Exception('Approval step misconfigured.');
        }

        // 5) Authorization check (same as main method)
        $allowed = false;
        switch ($cfg->approver_kind) {
            case 'user':
                $allowed = ($user->id === $cfg->approver_user_id);
                break;
            case 'department_head':
                $deptHead = optional(optional($requester->employmentDetail)->department)->head_of_department;
                $allowed = ($deptHead && $user->id === $deptHead);
                break;
            case 'role':
                $allowed = $user->hasRole($cfg->approver_value);
                break;
        }

        if (!$allowed) {
            throw new \Exception('Not authorized for this approval step.');
        }

        // 6) Create approval record
        LeaveApproval::create([
            'leave_request_id' => $leaveRequest->id,
            'approver_id' => $user->id,
            'step_number' => $currStep,
            'action' => 'approved',
            'comment' => $comment,
            'acted_at' => now(),
        ]);

        // 7) Update leave request based on step progression
        if ($currStep < $maxLevel) {
            // Move to next step
            $leaveRequest->update([
                'current_step' => $currStep + 1,
                'status' => 'pending',
            ]);
        } else {
            // Final approval - deduct balance only on final approval
            $leaveRequest->update(['status' => 'approved']);
            $this->deductLeaveBalance($leaveRequest);
        }
    }

    // ✅ UPDATED: Helper method for rejecting leave requests - use refundLeaveBalance helper
    private function rejectLeaveRequest($leaveRequest, $comment, $userId)
    {
        $user = User::find($userId);
        $requester = $leaveRequest->user;
        $currStep = $leaveRequest->current_step;
        $branchId = (int) optional($leaveRequest->user->employmentDetail)->branch_id;
        $oldStatus = $leaveRequest->status;

        // 1) Prevent self-approval
        if ($user->id === $requester->id) {
            throw new \Exception('Cannot reject own leave request.');
        }

        // 2) Build the approval workflow
        $steps = LeaveApproval::stepsForBranch($branchId);
        $reportingToId = optional($leaveRequest->user->employmentDetail)->reporting_to;

        // 3) Special rule: If reporting_to exists at step 1
        if ($currStep === 1 && $reportingToId) {
            if ($user->id !== $reportingToId) {
                throw new \Exception('Only reporting manager can reject this request.');
            }

            // Direct rejection by reporting manager
            LeaveApproval::create([
                'leave_request_id' => $leaveRequest->id,
                'approver_id' => $user->id,
                'step_number' => 1,
                'action' => 'rejected',
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            $leaveRequest->update(['status' => 'rejected']);
            return;
        }

        // 4) Normal workflow authorization check
        $cfg = $steps->firstWhere('level', $currStep);
        if (!$cfg) {
            throw new \Exception('Approval step misconfigured.');
        }

        // 5) Authorization check
        $allowed = false;
        switch ($cfg->approver_kind) {
            case 'user':
                $allowed = ($user->id === $cfg->approver_user_id);
                break;
            case 'department_head':
                $deptHead = optional(optional($leaveRequest->user->employmentDetail)->department)
                    ->head_of_department;
                $allowed = ($deptHead && $user->id === $deptHead);
                break;
            case 'role':
                $allowed = $user->hasRole($cfg->approver_value);
                break;
        }

        if (!$allowed) {
            throw new \Exception('Not authorized for this approval step.');
        }

        // 6) Create rejection record
        LeaveApproval::create([
            'leave_request_id' => $leaveRequest->id,
            'approver_id' => $user->id,
            'step_number' => $currStep,
            'action' => 'rejected',
            'comment' => $comment,
            'acted_at' => now(),
        ]);

        // 7) Update leave request status
        $leaveRequest->update(['status' => 'rejected']);

        // 8) Refund balance if it was previously approved
        if ($oldStatus === 'approved') {
            // ✅ USE HELPER METHOD for leave balance refund
            $this->refundLeaveBalance($leaveRequest);
        }
    }

    // ✅ FIXED: Helper method to deduct leave balance bulk action
    private function refundLeaveBalance($leaveRequest)
    {
        $entitlement = LeaveEntitlement::where('user_id', $leaveRequest->user_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('period_start', '<=', $leaveRequest->start_date)
            ->where('period_end', '>=', $leaveRequest->end_date)
            ->first();

        if ($entitlement) {
            $entitlement->increment('current_balance', $leaveRequest->days_requested);

            Log::info('Leave balance refunded', [
                'leave_request_id' => $leaveRequest->id,
                'user_id' => $leaveRequest->user_id,
                'leave_type_id' => $leaveRequest->leave_type_id,
                'days_refunded' => $leaveRequest->days_requested,
                'new_balance' => $entitlement->current_balance
            ]);
        }
    }

    // ✅ ENHANCED: Helper method to deduct leave balance with logging
    private function deductLeaveBalance($leaveRequest)
    {
        $entitlement = LeaveEntitlement::where('user_id', $leaveRequest->user_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('period_start', '<=', $leaveRequest->start_date)
            ->where('period_end', '>=', $leaveRequest->end_date)
            ->first();

        if (!$entitlement) {
            Log::error('No leave entitlement found for deduction', [
                'leave_request_id' => $leaveRequest->id,
                'user_id' => $leaveRequest->user_id,
                'leave_type_id' => $leaveRequest->leave_type_id,
                'start_date' => $leaveRequest->start_date,
                'end_date' => $leaveRequest->end_date
            ]);
            throw new \Exception('No leave entitlement found for this leave type and period.');
        }

        // ✅ Double-check balance before deduction
        if ($entitlement->current_balance < $leaveRequest->days_requested) {
            Log::error('Insufficient balance for deduction', [
                'leave_request_id' => $leaveRequest->id,
                'user_id' => $leaveRequest->user_id,
                'current_balance' => $entitlement->current_balance,
                'days_requested' => $leaveRequest->days_requested
            ]);
            throw new \Exception("Insufficient leave balance for deduction. Available: {$entitlement->current_balance} days, Requested: {$leaveRequest->days_requested} days.");
        }

        $oldBalance = $entitlement->current_balance;
        $entitlement->decrement('current_balance', $leaveRequest->days_requested);

        Log::info('Leave balance deducted successfully', [
            'leave_request_id' => $leaveRequest->id,
            'user_id' => $leaveRequest->user_id,
            'leave_type_id' => $leaveRequest->leave_type_id,
            'days_deducted' => $leaveRequest->days_requested,
            'old_balance' => $oldBalance,
            'new_balance' => $entitlement->current_balance
        ]);
    }
}
