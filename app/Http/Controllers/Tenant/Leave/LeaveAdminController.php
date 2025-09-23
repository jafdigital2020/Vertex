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

class LeaveAdminController extends Controller
{
    public function authUser()
    {
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
        $permission = PermissionHelper::get(19);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $dateRange = $request->input('dateRange');
        $status = $request->input('status');
        $leavetype = $request->input('leavetype');

        $query = LeaveRequest::with(['user', 'leaveType'])
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
        $approvedLeavesCount =  $leaveRequests->where('status', 'approved')->count(); 
        $rejectedLeavesCount = $leaveRequests->where('status', 'rejected')->count(); 
        $pendingLeavesCount = $leaveRequests->where('status', 'pending')->count();

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
        $fullname = trim($authUser->personalInformation->first_name . ' ' . $authUser->personalInformation->last_name);
 
        $leaveRequests = $leaveRequests->filter(function ($lr) use ($fullname) {
            return in_array($fullname, $lr->next_approvers ?? []);
        })->values();

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

        // total Approved leave for the current month
        $approvedLeavesCount = LeaveRequest::where('tenant_id', $tenantId)
            ->where('status', 'approved') 
            ->whereYear('start_date', Carbon::now()->year)
            ->count();

        // total Rejected leave for the current month
        $rejectedLeavesCount = LeaveRequest::where('tenant_id', $tenantId)
            ->where('status', 'rejected') 
            ->whereYear('start_date', Carbon::now()->year)
            ->count();

        // total Pending leave for the current month
        $pendingLeavesCount = LeaveRequest::where('tenant_id', $tenantId)
            ->where('status', 'pending') 
            ->whereYear('start_date', Carbon::now()->year)
            ->count();

        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();

        $leaveRequests = LeaveRequest::with(['user', 'leaveType'])
            ->where('tenant_id', $tenantId)
            ->whereBetween('start_date', [$startOfYear, $endOfYear]) 
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc')
            ->get();

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
        $fullname = trim($authUser->personalInformation->first_name . ' ' . $authUser->personalInformation->last_name);
 
        $leaveRequests = $leaveRequests->filter(function ($lr) use ($fullname) {
            return in_array($fullname, $lr->next_approvers ?? []);
        })->values();

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

        // 1.b) Prevent spamming a second “REJECTED”
        if ($data['action'] === 'REJECTED' && $oldStatus === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Leave request has already been rejected.',
            ], 400);
        }

        // 2) Build the approval workflow for this leave-owner’s branch
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
                    // Reporting To - Leave Notification to user
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
                }
            });

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
            $steps,
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

                    Log::info('Refunded leave days', [
                        'request_id'    => $leave->id,
                        'restored_days' => $leave->days_requested,
                        'new_balance'   => optional($ent)->current_balance,
                    ]);
                }
            }
        });

        $leave->refresh();
        $next = LeaveApproval::nextApproversFor($leave, $steps);

        return response()->json([
            'success'        => true,
            'message'        => 'Action recorded.',
            'data'           => $leave,
            'next_approvers' => $next,
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
}
