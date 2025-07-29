<?php

namespace App\Http\Controllers\Tenant;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use App\Models\Holiday;
use App\Models\Payroll;
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

    public function adminDashboard(Request $request)
    {
        $permission = PermissionHelper::get(1);

        $tenantId = $this->authUser()->tenant_id ?? null;
        $usersQuery = User::where('tenant_id', $tenantId);
        $branches = Branch::where('tenant_id', $tenantId)->get();

        $totalUsers = (clone $usersQuery)->count();
        $totalActiveUsers = (clone $usersQuery)
            ->whereHas('employmentDetail', function ($query) {
                $query->where('status', '1');
            })
            ->count();

        // Total Users Percentage
        $totalUserPercentage = 0;
        if ($totalUsers > 0) {
            $totalUserPercentage = ($totalActiveUsers / $totalUsers) * 100;
        }

        $totalInactive = (clone $usersQuery)
            ->whereHas('employmentDetail', function ($query) {
                $query->where('status', '0');
            })
            ->count();

        // Total Inactive Percentage
        $totalInactivePercentage = 0;
        if ($totalUsers > 0) {
            $totalInactivePercentage = ($totalInactive / $totalUsers) * 100;
        }

        // Present Today Users
        $presentTodayUsers = (clone $usersQuery)
            ->whereHas('attendance', function ($query) {
                $query->whereDate('attendance_date', Carbon::today())
                    ->where('status', 'present');
            })
            ->with(['attendance' => function ($query) {
                $query->whereDate('attendance_date', Carbon::today())
                    ->orderByDesc('id'); // or orderByDesc('created_at') if available
            }])
            ->get();
        $presentTodayUsersCount = $presentTodayUsers->count();

        // Present Today Users Percentage
        $presentTodayUsersPercentage = 0;
        if ($totalUsers > 0) {
            $presentTodayUsersPercentage = ($presentTodayUsersCount / $totalUsers) * 100;
        }

        // Late Today Users
        $lateTodayUsers = (clone $usersQuery)
            ->whereHas('attendance', function ($query) {
                $query->whereDate('attendance_date', Carbon::today())
                    ->where('status', 'late');
            })
            ->with(['attendance' => function ($query) {
                $query->whereDate('attendance_date', Carbon::today())
                    ->orderByDesc('id');
            }])
            ->get();

        $lateTodayUsersCount = $lateTodayUsers->count();

        // Late Today Users Percentage
        $lateTodayUsersPercentage = 0;
        if ($totalUsers > 0) {
            $lateTodayUsersPercentage = ($lateTodayUsersCount / $totalUsers) * 100;
        }

        // Leave Today Users
        $leaveTodayUsers = (clone $usersQuery)
            ->whereHas('leaveRequest', function ($query) {
                $query->whereDate('status', 'approved')
                    ->whereDate('start_date', '<=', Carbon::today())
                    ->whereDate('end_date', '>=', Carbon::today());
            })
            ->count();

        // Birthday Today Users
        $birthdayTodayUsers = $usersQuery
            ->whereHas('personalInformation', function ($query) {
                $query->where(function ($q) {
                    $q->whereMonth('birth_date', now()->month)
                        ->whereDay('birth_date', now()->day);
                })
                    ->orWhereRaw("DATE_FORMAT(birth_date, '%m-%d') > ?", [now()->format('m-d')]);
            })
            ->with('personalInformation')
            ->get()
            ->sortBy(function ($user) {
                return $user->personalInformation
                    ? Carbon::parse($user->personalInformation->birth_date)->format('m-d')
                    : '9999-99-99';
            })
            ->values()
            ->take(2);


        // Nearest Birthday
        $nearestBirthdays = $usersQuery
            ->whereHas('personalInformation', function ($query) {
                $query->whereRaw("DATE_FORMAT(birth_date, '%m-%d') > ?", [now()->format('m-d')]);
            })
            ->with('personalInformation')
            ->join('employment_personal_information', 'users.id', '=', 'employment_personal_information.user_id')
            ->orderByRaw("DATE_FORMAT(employment_personal_information.birth_date, '%m-%d')")
            ->select('users.*')
            ->take(3)
            ->get();

        // Users with shift today but no clock in
        $today = Carbon::today()->toDateString();
        $weekday = strtolower(Carbon::today()->format('D'));

        $noClockInToday = User::with(['personalInformation', 'shiftAssignment.shift'])
            ->where('tenant_id', $tenantId)
            ->whereHas('shiftAssignment', function ($query) use ($today, $weekday) {
                $query->where(function ($q) use ($today, $weekday) {
                    $q->where(function ($sub) use ($today, $weekday) {
                        $sub->where('type', 'recurring')
                            ->whereDate('start_date', '<=', $today)
                            ->where(function ($end) use ($today) {
                                $end->whereNull('end_date')
                                    ->orWhereDate('end_date', '>=', $today);
                            })
                            ->whereJsonContains('days_of_week', $weekday)
                            ->where(function ($ex) use ($today) {
                                $ex->whereNull('excluded_dates')
                                    ->orWhereJsonDoesntContain('excluded_dates', $today);
                            })
                            ->where('is_rest_day', false);
                    })->orWhere(function ($sub) use ($today) {
                        $sub->where('type', 'custom')
                            ->whereJsonContains('custom_dates', $today)
                            ->where(function ($ex) use ($today) {
                                $ex->whereNull('excluded_dates')
                                    ->orWhereJsonDoesntContain('excluded_dates', $today);
                            })
                            ->where('is_rest_day', false);
                    });
                });
            })
            ->whereHas('shiftAssignment.shift', function ($q) {
                $q->whereNotNull('start_time')
                    ->whereNotNull('end_time');
            })
            ->whereDoesntHave('attendance', function ($query) use ($today) {
                $query->whereDate('attendance_date', $today);
            })
            ->get();


        if ($request->wantsJson()) {
            return response()->json([
                'permission' => $permission,
                'totalUsers' => $totalUsers,
                'totalActiveUsers' => $totalActiveUsers,
                'totalInactive' => $totalInactive,
                'totalUserPercentage' => $totalUserPercentage,
                'totalInactivePercentage' => $totalInactivePercentage,
                'branches' => $branches,
                'presentTodayUsers' => $presentTodayUsers,
                'presentTodayUsersPercentage' => $presentTodayUsersPercentage,
                'lateTodayUsers' => $lateTodayUsers,
                'lateTodayUsersPercentage' => $lateTodayUsersPercentage,
                'leaveTodayUsers' => $leaveTodayUsers,
                'birthdayTodayUsers' => $birthdayTodayUsers,
                'nearestBirthdays' => $nearestBirthdays,
                'noClockInToday' => $noClockInToday,
                'presentTodayUsersCount' => $presentTodayUsersCount,
                'lateTodayUsersCount' => $lateTodayUsersCount,
            ]);
        }

        return view('tenant.dashboard.admin', [
            'permission' => $permission,
            'totalUsers' => $totalUsers,
            'totalActiveUsers' => $totalActiveUsers,
            'totalInactive' => $totalInactive,
            'totalUserPercentage' => $totalUserPercentage,
            'totalInactivePercentage' => $totalInactivePercentage,
            'branches' => $branches,
            'presentTodayUsers' => $presentTodayUsers,
            'presentTodayUsersPercentage' => $presentTodayUsersPercentage,
            'lateTodayUsers' => $lateTodayUsers,
            'lateTodayUsersPercentage' => $lateTodayUsersPercentage,
            'leaveTodayUsers' => $leaveTodayUsers,
            'birthdayTodayUsers' => $birthdayTodayUsers,
            'nearestBirthdays' => $nearestBirthdays,
            'noClockInToday' => $noClockInToday,
            'presentTodayUsersCount' => $presentTodayUsersCount,
            'lateTodayUsersCount' => $lateTodayUsersCount,
        ]);
    }

    // Admin Dashboard Attendance Summary Today
    public function attendanceSummaryToday(Request $request)
    {
        $tenantId = $this->authUser()->tenant_id ?? null;
        $usersQuery = User::where('tenant_id', $tenantId);

        $totalUsers = (clone $usersQuery)->count();

        $totalAttendance = (clone $usersQuery)->whereHas('attendance', function ($q) {
            $q->whereDate('attendance_date', Carbon::today());
        })->count();

        // Present
        $present = (clone $usersQuery)->whereHas('attendance', function ($q) {
            $q->whereDate('attendance_date', Carbon::today())
                ->where('status', 'present');
        })->count();

        // Late
        $late = (clone $usersQuery)->whereHas('attendance', function ($q) {
            $q->whereDate('attendance_date', Carbon::today())
                ->where('status', 'late');
        })->count();

        // Official Business
        $officialBusiness = (clone $usersQuery)->whereHas('attendance', function ($q) {
            $q->whereDate('attendance_date', Carbon::today())
                ->where('status', 'ob');
        })->count();

        // Absent
        $absent = (clone $usersQuery)->whereHas('attendance', function ($q) {
            $q->whereDate('attendance_date', Carbon::today())
                ->where('status', 'absent');
        })->count();

        return response()->json([
            'totalUsers' => $totalUsers,
            'present' => $present,
            'late' => $late,
            'official_business' => $officialBusiness,
            'absent' => $absent,
            'totalAttendance' => $totalAttendance,
        ]);
    }

    // Admin Dashboard Payroll Overview
    public function payrollOverview(Request $request)
    {
        $currentYear = Carbon::now()->year;
        $tenantId = $this->authUser()->tenant_id;

        // Query to get the net pay for each month from January to December, where status is "paid"
        $payrollData = Payroll::selectRaw('
            MONTH(payment_date) as month,
            SUM(net_salary) as total_netpay
        ')
            ->where('tenant_id', $tenantId)
            ->whereYear('payment_date', $currentYear)
            ->where('status', 'paid')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Initialize array for net pay by month (January to December)
        $monthlyNetPay = array_fill(0, 12, 0);

        foreach ($payrollData as $data) {
            $monthlyNetPay[$data->month - 1] = $data->total_netpay;
        }

        // Return the payroll data as JSON
        return response()->json([
            'monthlyNetPay' => $monthlyNetPay
        ]);
    }

    // Admin Dashboard Overtime and Holiday Pay
    public function overtimeOverview(Request $request)
    {
        $currentYear = Carbon::now()->year;
        $tenantId = $this->authUser()->tenant_id;

        // Query to get the overtime pay for each month from January to December, where status is "paid"
        $payrollData = Payroll::selectRaw('
        MONTH(payment_date) as month,
        SUM(overtime_pay) as total_overtime
    ')
            ->where('tenant_id', $tenantId)
            ->whereYear('payment_date', $currentYear)
            ->where('status', 'paid')
            ->groupBy('month')
            ->orderBy('month', 'asc') // Ensure months are ordered from January to December
            ->get();

        // Initialize array for overtime pay by month (January to December)
        $monthlyOvertimePay = array_fill(0, 12, 0);

        foreach ($payrollData as $data) {
            $monthlyOvertimePay[$data->month - 1] = $data->total_overtime;
        }

        // Return the overtime data as JSON
        return response()->json([
            'monthlyOvertimePay' => $monthlyOvertimePay
        ]);
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
