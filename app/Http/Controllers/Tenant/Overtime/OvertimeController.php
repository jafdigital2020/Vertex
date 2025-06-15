<?php

namespace App\Http\Controllers\Tenant\Overtime;

use Carbon\Carbon;
use App\Models\UserLog;
use App\Models\Overtime;
use Illuminate\Http\Request;
use App\Models\OvertimeApproval;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    public function overtimeIndex(Request $request)
    {
        // Auth User Tenant ID
        $tenantId = Auth::user()->tenant_id;

        $overtimes = Overtime::with('user')
            ->whereHas('user', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('overtime_date', 'desc')
            ->get();

        // Request Count
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $pendingCount = $overtimes->where('status', 'pending')
            ->where('overtime_date', '>=', Carbon::create($currentYear, $currentMonth, 1)->startOfDay())
            ->where('overtime_date', '<=', Carbon::create($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay())
            ->count();

        $approvedCount = $overtimes->where('status', 'approved')
            ->where('overtime_date', '>=', Carbon::create($currentYear, $currentMonth, 1)->startOfDay())
            ->where('overtime_date', '<=', Carbon::create($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay())
            ->count();

        $rejectedCount = $overtimes->where('status', 'rejected')
            ->where('overtime_date', '>=', Carbon::create($currentYear, $currentMonth, 1)->startOfDay())
            ->where('overtime_date', '<=', Carbon::create($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay())
            ->count();

        $totalRequests = $pendingCount + $approvedCount + $rejectedCount;

        // Approvers and steps
        foreach ($overtimes as $ot) {
            $branchId = optional($ot->user->employmentDetail)->branch_id;
            $steps = OvertimeApproval::stepsForBranch($branchId);
            $ot->total_steps     = $steps->count();

            $ot->next_approvers = OvertimeApproval::nextApproversFor($ot, $steps);

            if ($latest = $ot->latestApproval) {
                $approver = $latest->approver;
                $pi       = optional($approver->personalInformation);

                $ot->last_approver = trim("{$pi->first_name} {$pi->last_name}");

                $ot->last_approver_type = optional(
                    optional($approver->employmentDetail)->branch
                )->name ?? 'Global';
            } else {
                $ot->last_approver      = null;
                $ot->last_approver_type = null;
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Overtime index page',
                'data' => [
                    'overtimes' => $overtimes,
                    'pendingCount' => $pendingCount,
                    'approvedCount' => $approvedCount,
                    'rejectedCount' => $rejectedCount,
                    'totalRequests' => $totalRequests,
                ]
            ]);
        }

        return view('tenant.overtime.overtime', [
            'overtimes' => $overtimes,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'totalRequests' => $totalRequests,
        ]);
    }

    public function overtimeApproval(Request $request, Overtime $overtime)
    {
        // 1) Validate payload
        $data = $request->validate([
            'action'  => 'required|in:approved,rejected,pending',
            'comment' => 'nullable|string',
        ]);

        $user      = $request->user();
        $currStep  = $overtime->current_step;
        $branchId  = (int) optional($overtime->user->employmentDetail)->branch_id;
        $oldStatus = $overtime->status;

        // 1.a) Prevent spamming a second “REJECTED”
        if ($data['action'] === 'rejected' && $oldStatus === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Overtime request has already been rejected.',
            ], 400);
        }

        Log::info('Overtime approval attempt', [
            'request_id' => $overtime->id,
            'step'       => $currStep,
            'user_id'    => $user->id,
            'action'     => $data['action'],
            'old_status' => $oldStatus,
        ]);

        // 2) Build the approval workflow for this overtime-owner’s branch
        $steps = OvertimeApproval::stepsForBranch($branchId);

        $maxLevel = $steps->max('level');

        if ($currStep > $maxLevel) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid step level.',
            ], 400);
        }

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
                $deptHead = optional(optional($overtime->user->employmentDetail)->department)
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

        // 6) Perform transaction
        DB::transaction(function () use (
            $overtime,
            $data,
            $user,
            $currStep,
            $steps,
            $newStatus,
            $oldStatus,
            $maxLevel,
        ) {
            // a) record the approval
            OvertimeApproval::create([
                'overtime_id' => $overtime->id,
                'approver_id'      => $user->id,
                'step_number'      => $currStep,
                'action'           => strtolower($data['action']),
                'comment'          => $data['comment'] ?? null,
                'acted_at'         => Carbon::now(),
            ]);

            if ($data['action'] === 'approved') {
                if ($currStep < $maxLevel) {
                    $overtime->update([
                        'current_step' => $currStep + 1,
                        'status'       => 'pending',
                    ]);
                } else {
                    $overtime->update(['status' => 'approved']);
                }
            } else {
                // REJECTED or CHANGES_REQUESTED
                $overtime->update(['status' => $newStatus]);
            }
        });

        // 7) Return JSON
        $overtime->refresh();
        $next = OvertimeApproval::nextApproversFor($overtime, $steps);

        return response()->json([
            'success'        => true,
            'message'        => 'Action recorded.',
            'data'           => $overtime,
            'next_approvers' => $next,
        ]);
    }

    public function overtimeReject(Request $request, Overtime $overtime)
    {
        $data = $request->validate([
            'comment' => 'nullable|string',
        ]);

        $request->merge([
            'action'  => 'rejected',
            'comment' => $data['comment'],
        ]);

        return $this->overtimeApproval($request, $overtime);
    }

    // Manual Overtime Edit
    public function overtimeAdminUpdate(Request $request, $id)
    {
        $request->validate([
            'overtime_date'      => 'required|date',
            'date_ot_in'         => 'required|date',
            'date_ot_out'        => 'required|date|after:date_ot_in',
            'total_ot_minutes'   => 'required|numeric',
            'file_attachment'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'offset_date'        => 'nullable|date',
        ]);

        $overtime = Overtime::findOrFail($id);
        $userRequesterId = $request->user_id;

        // Prevent duplicate for same user & date, excluding this record
        $exists = Overtime::where('user_id', $userRequesterId)
            ->whereDate('overtime_date', $request->overtime_date)
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'The user has an overtime entry for this date.',
            ], 422);
        }

        // Save old data for logging
        $oldData = $overtime->toArray();

        // Handle file upload if new file
        if ($request->hasFile('file_attachment')) {
            $filePath = $request->file('file_attachment')->store('overtime_attachments', 'public');
            $overtime->file_attachment = $filePath;
        }

        $overtime->overtime_date = $request->overtime_date;
        $overtime->date_ot_in = $request->date_ot_in;
        $overtime->date_ot_out = $request->date_ot_out;
        $overtime->total_ot_minutes = $request->total_ot_minutes;
        $overtime->offset_date = $request->offset_date;

        $overtime->save();

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'    => $userId,
            'global_user_id' => $globalUserId,
            'module'     => 'Employee Overtime',
            'action'     => 'edit_overtime',
            'description' => 'Edited manual overtime, ID: ' . $overtime->id,
            'affected_id' => $overtime->id,
            'old_data'   => json_encode($oldData),
            'new_data'   => json_encode($overtime->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime updated successfully.',
            'data'    => $overtime,
        ]);
    }

    // Overtime admin Delete
    public function overtimeAdminDelete($id)
    {
        $overtime = Overtime::findOrFail($id);

        // Save old data for logging
        $oldData = $overtime->toArray();

        $overtime->delete();

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'    => $userId,
            'global_user_id' => $globalUserId,
            'module'     => 'Employee Overtime',
            'action'     => 'delete_overtime',
            'description' => 'Deleted manual overtime, ID: ' . $overtime->id,
            'affected_id' => $overtime->id,
            'old_data'   => json_encode($oldData),
            'new_data'   => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime deleted successfully.',
        ]);
    }
}
