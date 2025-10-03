<?php

namespace App\Http\Controllers\Tenant\Attendance;

use Carbon\Carbon;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Models\RequestAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\RequestAttendanceApproval;
use App\Http\Controllers\DataAccessController;

class AttendanceRequestAdminController extends Controller
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
        $permission = PermissionHelper::get(14);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation');
        $status = $request->input('status');


        $query  = $accessData['userAttendances'];

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('request_date', [$start, $end]);
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

        $userAttendances = $query->get();

        foreach ($userAttendances as $req) {
            $branchId = optional($req->user->employmentDetail)->branch_id;
            $steps = RequestAttendanceApproval::stepsForBranch($branchId);
            $req->total_steps     = $steps->count();

            $req->next_approvers = RequestAttendanceApproval::nextApproversFor($req, $steps);

            if ($latest = $req->latestApproval) {
                $approver = $latest->attendanceApprover;
                $pi       = optional($approver->personalInformation);

                $req->last_approver = trim("{$pi->first_name} {$pi->last_name}");

                $req->last_approver_type = optional(
                    optional($approver->employmentDetail)->branch
                )->name ?? 'Global';
            } else {
                $req->last_approver      = null;
                $req->last_approver_type = null;
            }
        }

        $html = view('tenant.attendance.attendance.adminrequest_filter', compact('userAttendances', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }


    public function adminRequestAttendanceIndex(Request $request)
    {

        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $today = Carbon::today()->toDateString();
        $permission = PermissionHelper::get(14);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->get();
        $departments  = $accessData['departments']->get();
        $designations = $accessData['designations']->get();

        $userAttendances =  $accessData['userAttendances']->whereBetween('request_date', [
            now()->subDays(29)->startOfDay(),
            now()->endOfDay()
        ])->get();

        // Total Present for today
        $totalPresent = Attendance::whereDate('attendance_date', $today)
            ->whereIn('status', ['present', 'late'])
            ->whereHas('user', function ($userQ) use ($tenantId) {
                $userQ->where('tenant_id', $tenantId)
                    ->whereHas('employmentDetail', fn($edQ) => $edQ->where('status', '1'));
            })
            ->count();


        // Total Late for today
        $totalLate = Attendance::whereDate('attendance_date', $today)
            ->where('status', 'late')
            ->whereHas('user', function ($userQ) use ($tenantId) {
                $userQ->where('tenant_id', $tenantId)
                    ->whereHas('employmentDetail', fn($edQ) => $edQ->where('status', '1'));
            })
            ->count();

        // Total Absent
        $totalAbsent = Attendance::whereDate('attendance_date', $today)
            ->where('status', 'absent')
            ->whereHas('user', function ($userQ) use ($tenantId) {
                $userQ->where('tenant_id', $tenantId)
                    ->whereHas('employmentDetail', fn($edQ) => $edQ->where('status', '1'));
            })
            ->count();

        // Get next approvers for each request
        foreach ($userAttendances as $req) {
            $branchId = optional($req->user->employmentDetail)->branch_id;
            $steps = RequestAttendanceApproval::stepsForBranch($branchId);
            $req->total_steps     = $steps->count();

            $req->next_approvers = RequestAttendanceApproval::nextApproversFor($req, $steps);

            if ($latest = $req->latestApproval) {
                $approver = $latest->attendanceApprover;
                $pi       = optional($approver->personalInformation);

                $req->last_approver = trim("{$pi->first_name} {$pi->last_name}");

                $req->last_approver_type = optional(
                    optional($approver->employmentDetail)->branch
                )->name ?? 'Global';
            } else {
                $req->last_approver      = null;
                $req->last_approver_type = null;
            }
        }

        // Api Route
        if ($request->wantsJson()) {
            return response()->json([
                'status'         => true,
                'userAttendance' => $userAttendances,
                'total_present'   => $totalPresent,
                'total_late' => $totalLate,
                'total_absent' => $totalAbsent,
            ]);
        }

        // Web Route
        return view('tenant.attendance.attendance.adminrequest', [
            'userAttendances' => $userAttendances,
            'totalPresent'   => $totalPresent,
            'totalLate' => $totalLate,
            'totalAbsent' => $totalAbsent,
            'permission' => $permission,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations
        ]);
    }

    // Approve Request Attendance
    public function requestAttendanceApproval(Request $request, RequestAttendance $req)
    {
        // 1) Validate payload
        $data = $request->validate([
            'action'  => 'required|in:approved,rejected,pending',
            'comment' => 'nullable|string',
        ]);

        $user      = $request->user();
        $currStep  = $req->current_step;
        $branchId  = (int) optional($req->user->employmentDetail)->branch_id;
        $oldStatus = $req->status;
        $requester = $req->user;
        $reportingToId = optional($req->user->employmentDetail)->reporting_to;

        // Prevent self-approval
        if ($user->id === $requester->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot take action on your own attendance request.',
            ], 403);
        }

        // Prevent spamming a second “REJECTED”
        if ($data['action'] === 'rejected' && $oldStatus === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Attendance request has already been rejected.',
            ], 400);
        }

        // 2) Build the approval workflow for this overtime-owner’s branch
        $steps = RequestAttendanceApproval::stepsForBranch($branchId);
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
            DB::transaction(function () use ($req, $data, $user, $newStatus, $maxLevel) {
                RequestAttendanceApproval::create([
                    'request_attendance_id' => $req->id,
                    'approver_id'          => $user->id,
                    'step_number'         => 1,
                    'action'              => strtolower($data['action']),
                    'comment'             => $data['comment'] ?? null,
                    'acted_at'            => Carbon::now(),
                ]);
                if ($data['action'] === 'approved') {
                    $req->update([
                        'current_step' => 1,
                        'status'       => 'approved',
                    ]);
                    // Attendance Update
                    $this->updateAttendanceForRequestAttendance($req);
                } else {
                    $req->update(['status' => $newStatus]);
                }
            });

            $req->refresh();
            return response()->json([
                'success'        => true,
                'message'        => 'Action recorded.',
                'data'           => $req,
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
                $deptHead = optional(optional($req->user->employmentDetail)->department)
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
            $req,
            $data,
            $user,
            $currStep,
            $steps,
            $newStatus,
            $oldStatus,
            $maxLevel
        ) {
            RequestAttendanceApproval::create([
                'request_attendance_id' => $req->id,
                'approver_id'          => $user->id,
                'step_number'         => $currStep,
                'action'              => strtolower($data['action']),
                'comment'             => $data['comment'] ?? null,
                'acted_at'         => Carbon::now(),
            ]);
            if ($data['action'] === 'approved') {
                if ($currStep < $maxLevel) {
                    $req->update([
                        'current_step' => $currStep + 1,
                        'status'       => 'pending',
                    ]);
                } else {
                    $req->update(['status' => 'approved']);

                    // Attendance Update
                    $this->updateAttendanceForRequestAttendance($req);
                }
            } else {
                // REJECTED or CHANGES_REQUESTED
                $req->update(['status' => $newStatus]);
            }
        });

        // 7) Return JSON
        $req->refresh();
        $next = RequestAttendanceApproval::nextApproversFor($req, $steps);

        return response()->json([
            'success'        => true,
            'message'        => 'Action recorded.',
            'data'           => $req,
            'next_approvers' => $next,
        ]);
    }

    // Request Attendance Approval
    public function requestAttendanceReject(Request $request, RequestAttendance $req)
    {
        $data = $request->validate([
            'comment' => 'nullable|string',
        ]);

        $request->merge([
            'action'  => 'rejected',
            'comment' => $data['comment'],
        ]);

        return $this->requestAttendanceApproval($request, $req);
    }

    // Update Attendance
    protected function updateAttendanceForRequestAttendance(RequestAttendance $req)
    {
        $reqDate = Carbon::parse($req->request_date)->toDateString();

        // Check if attendance already exists for the same date
        $attendance = Attendance::where('user_id', $req->user_id)
            ->where(DB::raw('DATE(attendance_date)'), $reqDate)
            ->first();

        if ($attendance) {
            Log::info('♻️ Updating existing attendance record to request.');
            $attendance->attendance_date = $reqDate;
            $attendance->status = 'Request';
            $attendance->date_time_in = $req->request_date_in;
            $attendance->date_time_out = $req->request_date_out;
            $attendance->total_work_minutes = $req->total_request_minutes;
            $attendance->total_night_diff_minutes = $req->total_request_nd_minutes;
            $attendance->save();
        } else {
            Attendance::create([
                'user_id'                  => $req->user_id,
                'attendance_date'          => $req->request_date,
                'date_time_in'             => $req->request_date_in,
                'date_time_out'            => $req->request_date_out,
                'total_work_minutes'       => $req->total_request_minutes,
                'total_night_diff_minutes' => $req->total_request_nd_minutes,
                'status'                   => 'Request',
            ]);
        }
    }
}
