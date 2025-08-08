<?php

namespace App\Http\Controllers\Tenant\OB;

use Carbon\Carbon;
use App\Models\UserLog;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\OfficialBusiness;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\OfficialBusinessApproval;
use App\Http\Controllers\DataAccessController;

class AdminOfficialBusinessController extends Controller
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
        $permission = PermissionHelper::get(47);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation');
        $status = $request->input('status');


        $query  = $accessData['obEntries'];

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('ob_date', [$start, $end]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date range format.'
                ]);
            }
        }
        if ($branch) {
            $query->whereHas('user.employmentDetail', function ($q) use ($branch) {
                $q->where('branch_id', $branch);
            });
        }
        if ($department) {
            $query->whereHas('user.employmentDetail', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
        if ($designation) {
            $query->whereHas('user.employmentDetail', function ($q) use ($designation) {
                $q->where('designation_id', $designation);
            });
        }
        if ($status) {
            $query->where('status', $status);
        }

        $obEntries = $query->get();

        foreach ($obEntries as $ob) {
            $branchId = optional($ob->user->employmentDetail)->branch_id;
            $steps = OfficialBusinessApproval::stepsForBranch($branchId);
            $ob->total_steps     = $steps->count();

            $ob->next_approvers = OfficialBusinessApproval::nextApproversFor($ob, $steps);

            if ($latest = $ob->latestApproval) {
                $approver = $latest->ObApprover;
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

        $html = view('tenant.ob.ob-admin_filter', compact('obEntries', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function adminOBIndex(Request $request)
    {

        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $authUserId = $authUser->id;
        $permission = PermissionHelper::get(47);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches =  $accessData['branches']->get();
        $departments =  $accessData['departments']->get();
        $designations =  $accessData['designations']->get();

        $startOfYear = now()->startOfYear();
        $endOfYear   = now()->endOfYear();

        $obEntries = $accessData['obEntries']
            ->whereBetween('ob_date', [$startOfYear, $endOfYear])
            ->get();

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Pending Counts This Month
        $pendingCount =  $accessData['obEntries']->where('status', 'pending')
            ->whereMonth('ob_date', $currentMonth)
            ->whereYear('ob_date', $currentYear)
            ->whereHas('user', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->count();

        // Approved Counts This Month
        $approvedCount =  $accessData['obEntries']->where('status', 'approved')
            ->whereMonth('ob_date', $currentMonth)
            ->whereYear('ob_date', $currentYear)
            ->whereHas('user', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->count();

        // Rejected Counts This Month
        $rejectedCount = $accessData['obEntries']->where('status', 'rejected')
            ->whereMonth('ob_date', $currentMonth)
            ->whereYear('ob_date', $currentYear)
            ->whereHas('user', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->count();

        // Total OB Requests This Month
        $totalOBRequests =  $accessData['obEntries']->whereMonth('ob_date', $currentMonth)
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
                $approver = $latest->ObApprover;
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
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,
            'permission' => $permission
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
        $currStep  = $ob->current_step;
        $branchId  = (int) optional($ob->user->employmentDetail)->branch_id;
        $oldStatus = $ob->status;
        $requester = $ob->user;
        $reportingToId = optional($ob->user->employmentDetail)->reporting_to;

        // Prevent self-approval
        if ($user->id === $requester->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot take action on your own overtime request.',
            ], 403);
        }

        // Prevent spamming a second “REJECTED”
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
                    'message' => 'You cannot approve this request.',
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
                    // Attendance Update
                    $this->updateAttendanceForOB($ob);
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

                    // Attendance Update
                    $this->updateAttendanceForOB($ob);
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

    // OB attendance creator/update
    protected function updateAttendanceForOB(OfficialBusiness $ob)
    {
        // Normalize OB date (strip time)
        $obDate = Carbon::parse($ob->ob_date)->toDateString();

        // Check if attendance already exists for the same date
        $attendance = Attendance::where('user_id', $ob->user_id)
            ->where(DB::raw('DATE(attendance_date)'), $obDate)
            ->first();

        if ($attendance) {

            Log::info('♻️ Updating absent record to OB.');

            if (strtolower($attendance->status) === 'absent') {
                $attendance->status = 'OB';
                $attendance->total_work_minutes = $ob->total_ob_minutes;
                $attendance->save();
            }
        } else {
            Attendance::create([
                'user_id'             => $ob->user_id,
                'attendance_date'     => $ob->ob_date, // keep full timestamp
                'total_work_minutes'  => $ob->total_ob_minutes,
                'status'              => 'OB',
            ]);
        }
    }

    // Update OB (Admin)
    public function adminUpdateOB(Request $request, $id)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(47);
        $authUserTenantId = $authUser->tenant_id ?? null;
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }

        $request->validate([
            'ob_date'      => 'required|date',
            'date_ob_in'         => 'required|date',
            'date_ob_out'        => 'required|date|after:date_ob_in',
            'total_ob_minutes'   => 'required|numeric',
            'file_attachment'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'purpose'            => 'required|string|max:255',
        ]);

        $ob = OfficialBusiness::findOrFail($id);

        // Prevent duplicate for same user & date, excluding this record
        $exists = OfficialBusiness::where('user_id', $ob->user_id)
            ->whereDate('ob_date', $request->ob_date)
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an official business entry for this date.',
            ], 422);
        }

        // Save old data for logging
        $oldData = $ob->toArray();

        // Handle file upload if new file
        if ($request->hasFile('file_attachment')) {
            $filePath = $request->file('file_attachment')->store('ob_attachments', 'public');
            $ob->file_attachment = $filePath;
        }

        $ob->ob_date = $request->ob_date;
        $ob->date_ob_in = $request->date_ob_in;
        $ob->date_ob_out = $request->date_ob_out;
        $ob->total_ob_minutes = $request->total_ob_minutes;
        $ob->purpose = $request->purpose;

        $ob->save();

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
            'module'     => 'Official Business',
            'action'     => 'Edit Official Business',
            'description' => 'Edited Employee Official Busienss, ID: ' . $ob->id,
            'affected_id' => $ob->id,
            'old_data'   => json_encode($oldData),
            'new_data'   => json_encode($ob->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Official Business request updated successfully.',
            'data'    => $ob,
        ]);
    }

    // Delete OB (Admin)
    public function adminDeleteOB($id)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(47);
        $authUserTenantId = $authUser->tenant_id ?? null;
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        $ob = OfficialBusiness::findOrFail($id);
        $ob->delete();

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
            'module'     => 'Official Business',
            'action'     => 'Delete Official Business',
            'description' => 'Deleted Employee Official Busienss, ID: ' . $ob->id,
            'affected_id' => $ob->id,
            'old_data'   => json_encode($ob->toArray()),
            'new_data'   => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Official business deleted successfully.',
        ]);
    }
}
