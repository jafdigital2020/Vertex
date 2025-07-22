<?php

namespace App\Http\Controllers\Tenant;

use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\Overtime;
use App\Models\ShiftList;
use App\Models\Attendance;
use Illuminate\Support\Str;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Models\ShiftAssignment;
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

    // Employee Bar Chart Analytics (Attendance)
    public function getAttendanceBarData(Request $request)
    {
        $userId = Auth::user()->id ?? null;
        $year = $request->get('year', now()->year);

        $attendancePerMonth = \App\Models\Attendance::where('user_id', $userId)
            ->whereYear('attendance_date', $year)
            ->selectRaw('MONTH(attendance_date) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Prepare array for all months Jan-Dec
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = isset($attendancePerMonth[$i]) ? $attendancePerMonth[$i] : 0;
        }

        return response()->json([
            'months' => $months,
        ]);
    }

    // Employee Shift Schedule
    public function getUserShiftsForWidget()
    {
        $userId = Auth::user()->id ?? null;
        $today = Carbon::today();

        // 1. Get all shift assignments for user
        $assignments = ShiftAssignment::where('user_id', $userId)->get();

        // 2. Find today's shift
        $shiftToday = null;
        foreach ($assignments as $assignment) {
            if ($assignment->is_rest_day) continue;
            // Excluded date check
            $isExcluded = is_array($assignment->excluded_dates) && in_array($today->toDateString(), $assignment->excluded_dates ?? []);
            if ($isExcluded) continue;

            if ($assignment->type === 'recurring') {
                $start = Carbon::parse($assignment->start_date);
                $end = $assignment->end_date ? Carbon::parse($assignment->end_date) : null;
                if ($start->lte($today) && (!$end || $end->gte($today))) {
                    $weekday = strtolower($today->format('D'));
                    if (is_array($assignment->days_of_week) && in_array($weekday, $assignment->days_of_week)) {
                        $shiftToday = ['assignment' => $assignment, 'date' => $today->toDateString()];
                        break;
                    }
                }
            }
            if ($assignment->type === 'custom') {
                if (is_array($assignment->custom_dates) && in_array($today->toDateString(), $assignment->custom_dates)) {
                    $shiftToday = ['assignment' => $assignment, 'date' => $today->toDateString()];
                    break;
                }
            }
        }

        // 3. Find next shift (nearest date after today)
        $nextShifts = collect();
        $daysToCheck = 30;
        for ($i = 1; $i <= $daysToCheck; $i++) {
            $checkDate = $today->copy()->addDays($i);
            foreach ($assignments as $assignment) {
                if ($assignment->is_rest_day) continue;
                $isExcluded = is_array($assignment->excluded_dates) && in_array($checkDate->toDateString(), $assignment->excluded_dates ?? []);
                if ($isExcluded) continue;

                if ($assignment->type === 'recurring') {
                    $start = Carbon::parse($assignment->start_date);
                    $end = $assignment->end_date ? Carbon::parse($assignment->end_date) : null;
                    if ($start->lte($checkDate) && (!$end || $end->gte($checkDate))) {
                        $weekday = strtolower($checkDate->format('D'));
                        if (is_array($assignment->days_of_week) && in_array($weekday, $assignment->days_of_week)) {
                            $nextShifts->push(['assignment' => $assignment, 'date' => $checkDate->toDateString()]);
                        }
                    }
                }
                if ($assignment->type === 'custom') {
                    if (is_array($assignment->custom_dates) && in_array($checkDate->toDateString(), $assignment->custom_dates)) {
                        $nextShifts->push(['assignment' => $assignment, 'date' => $checkDate->toDateString()]);
                    }
                }
            }
        }
        // Earliest valid next shift
        $shiftNext = $nextShifts->sortBy('date')->first();

        // Get shift details
        $shiftInfoToday = null;
        $shiftInfoNext = null;
        if ($shiftToday) {
            $shift = ShiftList::find($shiftToday['assignment']->shift_id);
            $shiftInfoToday = [
                'name' => $shift?->name ?? 'N/A',
                'start_time' => $shift?->start_time ?? '--:--',
                'end_time' => $shift?->end_time ?? '--:--',
                'date' => Carbon::parse($shiftToday['date'])->format('l, F j'),
                'notes' => $shift?->notes ?? '',
            ];
        }
        if ($shiftNext) {
            $shift = ShiftList::find($shiftNext['assignment']->shift_id);
            $shiftInfoNext = [
                'name' => $shift?->name ?? 'N/A',
                'start_time' => $shift?->start_time ?? '--:--',
                'end_time' => $shift?->end_time ?? '--:--',
                'date' => Carbon::parse($shiftNext['date'])->format('l, F j'),
                'notes' => $shift?->notes ?? '',
            ];
        }

        return response()->json([
            'today' => $shiftInfoToday,
            'next' => $shiftInfoNext,
        ]);
    }
}
