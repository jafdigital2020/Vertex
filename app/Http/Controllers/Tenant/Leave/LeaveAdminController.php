<?php

namespace App\Http\Controllers\Tenant\Leave;

use Carbon\Carbon;
use App\Models\User;
use App\Models\ApprovalStep;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Models\LeaveApproval;
use App\Models\LeaveEntitlement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LeaveAdminController extends Controller
{
    public function leaveAdminIndex(Request $request)
    {
        $leaveRequests = LeaveRequest::with(['user', 'leaveType'])
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc')
            ->get();

        // compute once
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
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'This is the leave admin index endpoint.',
                'status' => 'success',
                'leaveRequests' => $leaveRequests,
            ]);
        }

        return view('tenant.leave.adminleave', [
            'leaveRequests' => $leaveRequests,
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
        $currStep  = $leave->current_step;
        $branchId  = (int) optional($leave->user->employmentDetail)->branch_id;
        $oldStatus = $leave->status;

        // 1.a) Prevent spamming a second “REJECTED”
        if ($data['action'] === 'REJECTED' && $oldStatus === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Leave request has already been rejected.',
            ], 400);
        }

        Log::info('Leave approval attempt', [
            'request_id' => $leave->id,
            'step'       => $currStep,
            'user_id'    => $user->id,
            'action'     => $data['action'],
            'old_status' => $oldStatus,
        ]);

        // 2) Build the approval workflow for this leave-owner’s branch
        $steps = LeaveApproval::stepsForBranch($branchId);

        // 3) Find config for current step
        $cfg = $steps->firstWhere('level', $currStep);
        if (! $cfg) {
            return response()->json([
                'success' => false,
                'message' => 'Approval step misconfigured.',
            ], 500);
        }

        // 4) Authorization (same as before)…
        $allowed = false;
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

        // 5) Map action to new status
        $mapStatus = [
            'APPROVED'          => 'approved',
            'REJECTED'          => 'rejected',
            'CHANGES_REQUESTED' => 'pending',
        ];
        $newStatus = $mapStatus[$data['action']];

        // 6) Perform transaction
        DB::transaction(function () use (
            $leave,
            $data,
            $user,
            $currStep,
            $steps,
            $newStatus,
            $oldStatus
        ) {
            // a) record the approval
            LeaveApproval::create([
                'leave_request_id' => $leave->id,
                'approver_id'      => $user->id,
                'step_number'      => $currStep,
                'action'           => strtolower($data['action']),
                'comment'          => $data['comment'] ?? null,
                'acted_at'         => Carbon::now(),
            ]);

            $maxLevel = $steps->max('level');

            if ($data['action'] === 'APPROVED') {
                // bump step or finalize
                if ($currStep < $maxLevel) {
                    $leave->update([
                        'current_step' => $currStep + 1,
                        'status'       => 'pending',
                    ]);
                } else {
                    $leave->update(['status' => 'approved']);
                    // deduct days
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

        // 7) Return JSON
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
}
