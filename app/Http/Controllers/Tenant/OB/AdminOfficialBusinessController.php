<?php

namespace App\Http\Controllers\Tenant\OB;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\OfficialBusiness;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\OfficialBusinessApproval;

class AdminOfficialBusinessController extends Controller
{
    public function adminOBIndex(Request $request)
    {
        // Tenant ID
        $tenantId = Auth::user()->tenant_id ?? null;

        $obEntries = OfficialBusiness::whereHas('user', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })
            ->orderBy('ob_date', 'desc')
            ->get();

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Pending Counts This Month
        $pendingCount = OfficialBusiness::where('status', 'pending')
            ->whereMonth('ob_date', $currentMonth)
            ->whereYear('ob_date', $currentYear)
            ->whereHas('user', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->count();

        // Approved Counts This Month
        $approvedCount = OfficialBusiness::where('status', 'approved')
            ->whereMonth('ob_date', $currentMonth)
            ->whereYear('ob_date', $currentYear)
            ->whereHas('user', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->count();

        // Rejected Counts This Month
        $rejectedCount = OfficialBusiness::where('status', 'rejected')
            ->whereMonth('ob_date', $currentMonth)
            ->whereYear('ob_date', $currentYear)
            ->whereHas('user', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->count();

        // Total OB Requests This Month
        $totalOBRequests = OfficialBusiness::whereMonth('ob_date', $currentMonth)
            ->whereYear('ob_date', $currentYear)
            ->whereHas('user', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->count();

        // Approvers and steps
        foreach ($obEntries as $ob) {
            $branchId = optional($ob->user->employmentDetail)->branch_id;
            $steps = OfficialBusinessApproval::stepsForBranch($branchId);
            $ob->total_steps     = $steps->count();

            $ob->next_approvers = OfficialBusinessApproval::nextApproversFor($ob, $steps);

            if ($latest = $ob->latestApproval) {
                $approver = $latest->approver;
                $pi       = optional($approver->personalInformation);

                $ob->last_approver = trim("{$pi->first_name} {$pi->last_name}");

                $ob->last_approver_type = optional(
                    optional($approver->employmentDetail)->branch
                )->name ?? 'Global';
            } else {
                $ob->last_approver      = null;
                $ob->last_approver_type = null;
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Admin Official Business Index',
                'data' => $obEntries,
                'counts' => [
                    'pending' => $pendingCount,
                    'approved' => $approvedCount,
                    'rejected' => $rejectedCount,
                    'total' => $totalOBRequests,
                ],
            ]);
        }

        return view('tenant.ob.ob-admin', [
            'obEntries' => $obEntries,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'totalOBRequests' => $totalOBRequests,
        ]);
    }

    // Approve OB
    public function obApproval(Request $request, OfficialBusiness $ob)
    {
        // 1) Validate payload
        $data = $request->validate([
            'action'  => 'required|in:approved,rejected,pending',
            'comment' => 'nullable|string',
        ]);

        $user      = $request->user();
        Log::info('OB Approval Request', [
            'user_id' => $user->id,
            'ob_id'   => $ob->id,
            'action'  => $data['action'],
            'comment' => $data['comment'] ?? null,
        ]);
        $currStep  = $ob->current_step;
        $branchId  = (int) optional($ob->user->employmentDetail)->branch_id;
        $oldStatus = $ob->status;
        $requester = $ob->user;
        $reportingToId = optional($ob->user->employmentDetail)->reporting_to;

        //  Prevent self-approval
        if ($user->id === $requester->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot approve your own overtime request.',
            ], 403);
        }

        // 1.a) Prevent spamming a second “REJECTED”
        if ($data['action'] === 'rejected' && $oldStatus === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Overtime request has already been rejected.',
            ], 400);
        }

        // 2) Build the approval workflow for this overtime-owner’s branch
        $steps = OfficialBusinessApproval::stepsForBranch($branchId);
        $maxLevel = $steps->max('level');

        if ($currStep > $maxLevel) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid step level.',
            ], 400);
        }

        // 3) Special rule: if reporting_to exists, only that user can approve at step 1, and auto-approved na dapat
        if ($currStep === 1 && $reportingToId) {
            if ($user->id !== $reportingToId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the reporting manager can approve this overtime request.',
                ], 403);
            }

            $newStatus = strtolower($data['action']);
            DB::transaction(function () use ($ob, $data, $user, $newStatus, $maxLevel) {
                OfficialBusinessApproval::create([
                    'official_business_id' => $ob->id,
                    'approver_id'          => $user->id,
                    'step_number'         => 1,
                    'action'              => strtolower($data['action']),
                    'comment'             => $data['comment'] ?? null,
                    'acted_at'            => Carbon::now(),
                ]);
                if ($data['action'] === 'approved') {
                    $ob->update([
                        'current_step' => 1,
                        'status'       => 'approved',
                    ]);
                } else {
                    $ob->update(['status' => $newStatus]);
                }
            });

            $ob->refresh();
            return response()->json([
                'success'        => true,
                'message'        => 'Action recorded.',
                'data'           => $ob,
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
                $deptHead = optional(optional($ob->user->employmentDetail)->department)
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

        $newStatus = strtolower($data['action']);

        DB::transaction(function () use (
            $ob,
            $data,
            $user,
            $currStep,
            $steps,
            $newStatus,
            $oldStatus,
            $maxLevel
        ) {
            OfficialBusinessApproval::create([
                'official_business_id' => $ob->id,
                'approver_id'          => $user->id,
                'step_number'         => $currStep,
                'action'              => strtolower($data['action']),
                'comment'             => $data['comment'] ?? null,
                'acted_at'         => Carbon::now(),
            ]);

            if ($data['action'] === 'approved') {
                if ($currStep < $maxLevel) {
                    $ob->update([
                        'current_step' => $currStep + 1,
                        'status'       => 'pending',
                    ]);
                } else {
                    $ob->update(['status' => 'approved']);
                }
            } else {
                // REJECTED or CHANGES_REQUESTED
                $ob->update(['status' => $newStatus]);
            }
        });

        // 7) Return JSON
        $ob->refresh();
        $next = OfficialBusinessApproval::nextApproversFor($ob, $steps);

        return response()->json([
            'success'        => true,
            'message'        => 'Action recorded.',
            'data'           => $ob,
            'next_approvers' => $next,
        ]);
    }

    // Reject OB
    public function obReject(Request $request, OfficialBusiness $ob)
    {
        $data = $request->validate([
            'comment' => 'nullable|string',
        ]);

        $request->merge([
            'action'  => 'rejected',
            'comment' => $data['comment'],
        ]);

        return $this->obApproval($request, $ob);
    }
}
