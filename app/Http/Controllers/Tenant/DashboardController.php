<?php

namespace App\Http\Controllers\Tenant;

use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\Overtime;
use App\Models\Attendance;
use Illuminate\Support\Str;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Models\LeaveEntitlement;
use App\Models\OfficialBusiness;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\DataAccessController;
use App\Http\Controllers\RoleAccessController;

class DashboardController extends Controller
{
    // Admin Dashboard

    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    }
    public function adminDashboard()
    {
        $permission = PermissionHelper::get(1);
        return view('tenant.dashboard.admin', compact('permission'));
    }

    // Employee Dashboard
    public function employeeDashboard(Request $request)
    {

        $authUser = $this->authUser();
        $permission = PermissionHelper::get(2);
        $authUserTenantId = $authUser->tenant_id ?? null;
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $today = Carbon::today();

        $upcomingHoliday = $accessData['holidays']
            ->where(function ($query) use ($today) {
                $query->where(function ($q) use ($today) {
                    $q->whereNotNull('date')
                        ->whereDate('date', '>=', $today);
                })
                    ->orWhere(function ($q) use ($today) {
                        $q->whereNull('date')
                            ->whereNotNull('month_day')
                            ->whereRaw("STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', month_day), '%Y-%m-%d') >= ?", [$today->toDateString()]);
                    });
            })
            ->orderByRaw("
            CASE
                WHEN date IS NOT NULL THEN date
                ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', month_day), '%Y-%m-%d')
            END ASC
            ")
            ->first();

        // Branch Birthday Employee
        $branchBirthdayEmployees = collect();

        $employmentDetail = $authUser->employmentDetail ?? null;
        $branch = $employmentDetail && isset($employmentDetail->branch) ? $employmentDetail->branch : null;
        $branchEmploymentDetails = $branch && isset($branch->employmentDetail) ? $branch->employmentDetail : [];

        if ($branchEmploymentDetails && is_iterable($branchEmploymentDetails)) {
            foreach ($branchEmploymentDetails as $employmentDetail) {
                $user = $employmentDetail->user ?? null;
                $pi = $user && isset($user->personalInformation) ? $user->personalInformation : null;
                $birthDate = $pi && isset($pi->birth_date) ? $pi->birth_date : null;

                if ($birthDate && Carbon::parse($birthDate)->isToday()) {
                    $firstName = $pi->first_name ?? '';
                    $middleName = $pi->middle_name ?? '';
                    $lastName = $pi->last_name ?? '';
                    $fullName = trim("{$firstName} {$middleName} {$lastName}");

                    $profilePicture = isset($pi->profile_picture) && $pi->profile_picture
                        ? asset('storage/' . $pi->profile_picture)
                        : asset('build/img/users/user-35.jpg'); // fallback image

                    $designation = isset($employmentDetail->designation) && isset($employmentDetail->designation->designation_name)
                        ? $employmentDetail->designation->designation_name
                        : '—';

                    $branchBirthdayEmployees->push([
                        'full_name' => $fullName,
                        'designation' => $designation,
                        'profile_picture' => $profilePicture,
                    ]);
                }
            }
        }

        // Get the authenticated user's ID
        $userId = $authUser->id;
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        // Pull the latest overtime, ob and leave approved/rejected requests
        $leaveRequests = LeaveRequest::where('user_id', $userId)
            ->whereIn('status', ['approved', 'rejected'])
            ->orderByDesc('updated_at')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type'        => 'leave',
                    'date'        => $item->updated_at,
                    'status'      => $item->status,
                    'main_date'   => $item->start_date,
                    'label'       => 'Leave Request',
                ];
            });

        $overtimes = Overtime::where('user_id', $userId)
            ->whereIn('status', ['approved', 'rejected'])
            ->orderByDesc('updated_at')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type'        => 'overtime',
                    'date'        => $item->updated_at,
                    'status'      => $item->status,
                    'main_date'   => $item->overtime_date,
                    'label'       => 'Overtime Request',
                ];
            });

        $officialBusinesses = OfficialBusiness::where('user_id', $userId)
            ->whereIn('status', ['approved', 'rejected'])
            ->orderByDesc('updated_at')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type'        => 'official_business',
                    'date'        => $item->updated_at,
                    'status'      => $item->status,
                    'main_date'   => $item->ob_date,
                    'label'       => 'Official Business Request',
                ];
            });

        // Merge all collections, sort by date DESC
        $allNotifications = $leaveRequests
            ->concat($overtimes)
            ->concat($officialBusinesses)
            ->sortByDesc('date')
            ->values();


        return view('tenant.dashboard.employee', [
            'upcomingHoliday' => $upcomingHoliday,
            'authUser' => $authUser,
            'permission' => $permission,
            'branchBirthdayEmployees' => $branchBirthdayEmployees,
            'allNotifications' => $allNotifications,
            
        ]);
    }

    // Attendance Analytics
    public function getAttendanceAnalytics(Request $request)
    {
        $userId = Auth::user()->id ?? null;
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $attendanceAnalytics = Attendance::where('user_id', $userId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->select('status')
            ->get()
            ->groupBy('status')
            ->map(function ($rows) {
                return count($rows);
            });

        $attendanceCircleData = [
            'present'   => $attendanceAnalytics['present'] ?? 0,
            'late'      => $attendanceAnalytics['late'] ?? 0,
            'undertime' => $attendanceAnalytics['undertime'] ?? 0,
            'absent'    => $attendanceAnalytics['absent'] ?? 0,
        ];

        return response()->json([
            'circleData' => $attendanceCircleData
        ]);
    }

    // Employee Leave Analytics
    public function getLeaveAnalytics(Request $request)
    {
        $userId = Auth::user()->id ?? null;
        $year = $request->get('year', now()->year);

        $startOfYear = "$year-01-01";
        $endOfYear = "$year-12-31";

        $totalLeaves = LeaveEntitlement::where('user_id', $userId)
            ->where('period_end', '>=', $startOfYear)
            ->where('period_start', '<=', $endOfYear)
            ->sum('current_balance');

        // Leave Requests for selected year
        $leaveRequests = LeaveRequest::where('user_id', $userId)
            ->whereYear('start_date', $year);

        $totalPending   = (clone $leaveRequests)->where('status', 'pending')->count();
        $totalApproved  = (clone $leaveRequests)->where('status', 'approved')->count();
        $totalRejected  = (clone $leaveRequests)->where('status', 'rejected')->count();

        // Attendance for selected year
        $attendance = Attendance::where('user_id', $userId)
            ->whereYear('attendance_date', $year);

        $workedDays = (clone $attendance)->whereIn('status', ['present', 'late', 'ob'])->count();
        $absents    = (clone $attendance)->where('status', 'absent')->count();

        return response()->json([
            'totalLeaves'   => $totalLeaves,
            'totalPending'  => $totalPending,
            'totalApproved' => $totalApproved,
            'totalRejected' => $totalRejected,
            'workedDays'    => $workedDays,
            'absents'       => $absents,
        ]);
    }
}
