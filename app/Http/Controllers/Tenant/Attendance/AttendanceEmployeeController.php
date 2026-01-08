<?php

namespace App\Http\Controllers\Tenant\Attendance;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Holiday;
use App\Models\UserLog;
use App\Models\Geofence;
use App\Models\Overtime;
use App\Models\Attendance;
use Jenssegers\Agent\Agent;
use App\Models\GeofenceUser;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\ShiftAssignment;
use App\Models\HolidayException;
use App\Helpers\PermissionHelper;
use App\Models\RequestAttendance;
use App\Models\AttendanceSettings;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Storage;
use App\Models\RequestAttendanceApproval;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;

class AttendanceEmployeeController extends Controller
{
    // Authenticated User Getter
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    private function buildAttendanceQuery($userId, $dateRange = null, $status = null)
    {
        $query = Attendance::where('user_id', $userId);

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();
                $query->whereBetween('attendance_date', [$start, $end]);
            } catch (\Exception $e) {
                Log::error('Error parsing date range: ' . $e->getMessage());
            }
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query;
    }

    // Filter Employee Attendance
    public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(15);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $status = $request->input('status');

        $query = $this->buildAttendanceQuery($authUserId, $dateRange, $status);

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->get();

        $html = view('tenant.attendance.attendance.employeeattendance_filter', compact('attendances', 'permission'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'attendances' => $attendances,
        ]);
    }

    /**
     * Display the employee's attendance records and summary.
     *
     * @param \Illuminate\Http\Request $request
     * @queryParam dateRange string Optional. Date range in format "mm/dd/yyyy - mm/dd/yyyy". Example: "11/01/2025 - 11/27/2025"
     * @queryParam status string Optional. Filter attendance by status (e.g., "present", "late", "absent").
     *
     * Returns attendance records, summary statistics, shift assignments, and subscription status for the authenticated employee.
     */
    public function employeeAttendanceIndex(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = Auth::guard('global')->check() ? null : ($authUser->id ?? null);
        $permission = PermissionHelper::get(15);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $settings = AttendanceSettings::first();
        $today    = Carbon::today()->toDateString();
        $todayDay = strtolower(now()->format('D'));
        $now = Carbon::now();

        // Pass DateRange/Status
        $dateRange = $request->input('dateRange') ?? Carbon::today()->format('m/d/Y') . ' - ' . Carbon::today()->format('m/d/Y');
        $status = $request->input('status');

        // Private Build Query
        $filteredQuery = $this->buildAttendanceQuery($authUserId, $dateRange, $status);
        $attendances = $filteredQuery->with('shift')->orderBy('attendance_date', 'desc')->get();

        if (!$settings) {
            $settings = AttendanceSettings::create([
                'require_photo_capture' => false,
                'geotagging_enabled' => false,
                'geofencing_enabled' => false,
                'enable_late_status_box' => false,
                'geofence_buffer' => 0,
                'geofence_allowed_geotagging' => false,
            ]);
        }

        // Subscription validation
        $subscription = Subscription::where('tenant_id', $authUser->tenant_id)->first();

        $nowDate = now()->startOfDay();
        $trialEnded = $subscription
            && $subscription->status === 'trial'
            && $subscription->trial_end
            && $nowDate->greaterThanOrEqualTo(Carbon::parse($subscription->trial_end)->startOfDay());

        $expired = $subscription && in_array($subscription->status, ['expired', 'inactive', 'cancelled']);

        $subBlocked = $trialEnded || $expired;
        $subBlockMessage = $trialEnded
            ? 'Your 7-day trial period has ended. Please contact your administrator.'
            : ($expired ? 'Your subscription has expired. Please contact your administrator.' : null);

        // ✅ ENHANCED: Use shift-based logic to find current active attendance
        $currentClockIn = $this->findActiveAttendanceByShift($authUserId);

        $latestAttendance = Attendance::where('user_id',  $authUserId)
            ->latest('date_time_in')
            ->first();

        $todayAttendances = Attendance::where('user_id', $authUserId)
            ->where('attendance_date', $today)
            ->orderBy('date_time_in', 'desc')
            ->get();

        $isCurrentlyClockedIn = $currentClockIn !== null;

        $clockInStatus = null;
        if ($currentClockIn) {
            $shift = $currentClockIn->shift;
            $clockInStatus = [
                'attendance_id' => $currentClockIn->id,
                'shift_name' => $shift ? $shift->name : 'Unknown Shift',
                'clock_in_time' => $currentClockIn->date_time_in->format('g:i A'),
                'status' => $currentClockIn->status,
                'is_late' => $currentClockIn->status === 'late',
                'late_minutes' => $currentClockIn->total_late_minutes ?? 0,
                'is_holiday' => $currentClockIn->is_holiday,
            ];
        }

        $latest = Attendance::where('user_id',  $authUserId)
            ->where('attendance_date', $today)
            ->whereNotNull('date_time_in')
            ->latest('date_time_in')
            ->first();

        // Calculate total hours for the current week (Monday to Sunday)
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $weeklyAttendances = Attendance::where('user_id',  $authUserId)
            ->whereBetween('attendance_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

        $totalWeeklyMinutes = $weeklyAttendances->sum(function ($attendance) {
            return $attendance->total_work_minutes ?? 0;
        });

        $totalWeeklyHours = round($totalWeeklyMinutes / 60, 2);

        // Calculate total hours for the current month (1st to last day)
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $monthlyAttendances = Attendance::where('user_id',  $authUserId)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $totalMonthlyMinutes = $monthlyAttendances->sum(function ($attendance) {
            return $attendance->total_work_minutes ?? 0;
        });

        $totalMonthlyHours = round($totalMonthlyMinutes / 60, 2);

        // Night Diff For This Month
        $monthlyNightAttendance = Attendance::where('user_id',  $authUserId)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $totalMonthlyNightMinutes = $monthlyNightAttendance->sum(function ($attendance) {
            return $attendance->total_night_diff_minutes ?? 0;
        });

        $totalMonthlyNightHours = round($totalMonthlyNightMinutes / 60, 2);

        // Late Minutes for this month
        $monthlyLateAttendance = Attendance::where('user_id',  $authUserId)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $totalMonthlyLateMinutes = $monthlyLateAttendance->sum(function ($attendance) {
            return $attendance->total_late_minutes ?? 0;
        });

        $totalMonthlyLateHours = round($totalMonthlyLateMinutes / 60, 2);

        // Undertime Minutes for this month
        $monthlyUndertimeAttendance = Attendance::where('user_id',  $authUserId)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $totalMonthlyUndertimeMinutes = $monthlyUndertimeAttendance->sum(function ($attendance) {
            return $attendance->total_undertime_minutes ?? 0;
        });

        $totalMonthlyUndertimeHours = round($totalMonthlyUndertimeMinutes / 60, 2);

        // format minutes as "X hr Y min"
        $formatMinutes = function ($minutes) {
            if ($minutes <= 0) {
                return '0 min';
            }
            $hours = intdiv($minutes, 60);
            $mins  = $minutes % 60;
            $parts = [];
            if ($hours > 0) {
                $parts[] = "{$hours} hr";
            }
            if ($mins > 0) {
                $parts[] = "{$mins} min";
            }
            return implode(' ', $parts);
        };

        $totalMonthlyHoursFormatted = $formatMinutes($totalMonthlyMinutes);
        $totalWeeklyHoursFormatted  = $formatMinutes($totalWeeklyMinutes);
        $totalMonthlyNightHoursFormatted = $formatMinutes($totalMonthlyNightMinutes);
        $totalMonthlyLateHoursFormatted = $formatMinutes($totalMonthlyLateMinutes);
        $totalMonthlyUndertimeHoursFormatted = $formatMinutes($totalMonthlyUndertimeMinutes);

        // ✅ UPDATED: Get shift assignments including rest days
        $assignments = ShiftAssignment::with('shift')
            ->where('user_id', $authUser->id)
            ->get()

            // 1️⃣ Date/Day filter (recurring & custom)
            ->filter(function ($assignment) use ($today, $todayDay) {
                // skip excluded dates
                if ($assignment->excluded_dates && in_array($today, $assignment->excluded_dates)) {
                    return false;
                }

                // recurring
                if ($assignment->type === 'recurring') {
                    $start = Carbon::parse($assignment->start_date);
                    $end   = $assignment->end_date
                        ? Carbon::parse($assignment->end_date)
                        : now();
                    return $start->lte($today)
                        && $end->gte($today)
                        && in_array($todayDay, $assignment->days_of_week);
                }

                // custom
                if ($assignment->type === 'custom') {
                    return in_array($today, $assignment->custom_dates ?? []);
                }

                return false;
            })

            // 2️⃣ Time-window filter: drop shifts that have already ended
            ->filter(function ($assignment) use ($today, $now) {

                if ($assignment->is_rest_day) {
                    return true;
                }

                $shift = $assignment->shift;

                // ✅ Keep null check for regular shifts only
                if (!$shift) {
                    return false;
                }

                // If flexible, always allow
                if ($shift->is_flexible) {
                    return true;
                }

                if (!$shift->start_time || !$shift->end_time) {
                    return true; // if missing times, skip this filter
                }

                $start = Carbon::parse("{$today} {$shift->start_time}");
                $end = Carbon::parse("{$today} {$shift->end_time}");

                // Handle night shifts that cross midnight
                if ($end->lte($start)) {
                    $end->addDay(); // Move end time to next day
                }

                // keep only if now is before or equal end time
                return $now->lte($end);
            })

            // 3️⃣ Sort by shift start time
            ->sortBy(function ($assignment) {

                if ($assignment->is_rest_day) {
                    return '00:00:00';
                }
                return $assignment->shift ? ($assignment->shift->start_time ?? '99:99:99') : '99:99:99';
            });

        // ✅ UPDATED: Detect rest day and regular assignments
        $restDayAssignment = $assignments->firstWhere('is_rest_day', true);
        $regularAssignment = $assignments->firstWhere('is_rest_day', false);

        // ✅ UPDATED: Set flags
        $isRestDay = $restDayAssignment !== null;

        // ✅ UPDATED: Allow hasShift to be true if rest day is allowed
        $hasShift = $regularAssignment !== null || ($restDayAssignment && $settings->rest_day_time_in_allowed);

        // ✅ UPDATED: Get next assignment (prioritize rest day if allowed)
        $nextAssignment = $assignments->first(function ($assignment) use ($authUser, $today) {
            // ✅ Allow rest day assignments
            if ($assignment->is_rest_day) {
                return true; // Always return rest day if it exists
            }

            // ✅ For regular shifts, check if shift exists before checking attendance
            if (!$assignment->shift) {
                return false;
            }

            return !Attendance::where('user_id', $authUser->id)
                ->where('shift_id', $assignment->shift_id)
                ->where('shift_assignment_id', $assignment->id)
                ->where('attendance_date', $today)
                ->exists();
        });

        $currentActiveAssignment = null;
        if (!$nextAssignment) {
            // If no next assignment, check if user is currently clocked in to any shift
            $currentAttendance = Attendance::where('user_id', $authUser->id)
                ->where(function ($query) use ($today) {
                    $yesterday = Carbon::yesterday()->toDateString();
                    // Include today's attendance OR yesterday's attendance (for night shifts)
                    $query->where('attendance_date', $today)
                        ->orWhere('attendance_date', $yesterday);
                })
                ->whereNotNull('date_time_in')
                ->whereNull('date_time_out') // Currently clocked in
                ->latest('date_time_in')
                ->first();

            if ($currentAttendance && $currentAttendance->shiftAssignment) {
                $currentActiveAssignment = $currentAttendance->shiftAssignment;
            }
        }

        $assignmentForBreakManagement = $nextAssignment ?? $currentActiveAssignment;

        // ✅ UPDATED: Grace Period getter - safe access (return 0 for rest days)
        $gracePeriod = ($assignmentForBreakManagement && !$assignmentForBreakManagement->is_rest_day && $assignmentForBreakManagement->shift)
            ? ($assignmentForBreakManagement->shift->grace_period ?? 0)
            : 0;

        // ✅ UPDATED: Check if its Flexible Shift - treat rest days as flexible
        $isFlexible = $assignmentForBreakManagement
            ? ($assignmentForBreakManagement->is_rest_day || ($assignmentForBreakManagement->shift && $assignmentForBreakManagement->shift->is_flexible))
            : false;

        // API response
        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Attendance data loaded',
                'data' => $attendances,
                'isCurrentlyClockedIn' => $isCurrentlyClockedIn,
                'currentClockIn' => $currentClockIn,
                'hasShift' => $hasShift,
                'isRestDay' => $isRestDay,
                'nextAssignment' => $nextAssignment,
                'gracePeriod' => $gracePeriod,
                'isFlexible' => $isFlexible,
                'settings' => $settings,
                'subBlocked' => $subBlocked,
                'subBlockMessage' => $subBlockMessage,
                'employmentDetail' => [
                    'employee_id' => $authUser->employmentDetail->employee_id ?? null,
                    'department_name' => $authUser->employmentDetail->department->department_name ?? null,
                    'designation_name' => $authUser->employmentDetail->designation->designation_name ?? null,
                    'employment_type' => $authUser->employmentDetail->employment_type ?? null,
                    'employment_status' => $authUser->employmentDetail->employment_status ?? null,
                    'branch_id' => $authUser->employmentDetail->branch_id ?? null,
                ],
                'personalInformation' => [
                    'first_name' => $authUser->personalInformation->first_name ?? null,
                    'last_name' => $authUser->personalInformation->last_name ?? null,
                    'middle_name' => $authUser->personalInformation->middle_name ?? null,
                    'profile_photo_path' => $authUser->personalInformation->profile_picture ?? null,
                ],
                'summary' => [
                    'totalWeeklyHours' => $totalWeeklyHoursFormatted,
                    'totalMonthlyHours' => $totalMonthlyHoursFormatted,
                    'totalMonthlyNightHours' => $totalMonthlyNightHoursFormatted,
                    'totalMonthlyLateHours' => $totalMonthlyLateHoursFormatted,
                    'totalMonthlyUndertimeHours' => $totalMonthlyUndertimeHoursFormatted,
                ],

            ]);
        }

        // Web response
        return view(
            'tenant.attendance.attendance.employeeattendance',
            [
                'attendances' => $attendances,
                'latest' => $latestAttendance,
                'settings' => $settings,
                'nextAssignment'  => $nextAssignment,
                'latest' => $latest,
                'hasShift' => $hasShift,
                'totalWeeklyHours' => $totalWeeklyHours,
                'totalMonthlyHours' => $totalMonthlyHours,
                'totalMonthlyHoursFormatted' => $totalMonthlyHoursFormatted,
                'totalWeeklyHoursFormatted' => $totalWeeklyHoursFormatted,
                'totalMonthlyNightHours' => $totalMonthlyNightHours,
                'totalMonthlyNightHoursFormatted' => $totalMonthlyNightHoursFormatted,
                'totalMonthlyLateHoursFormatted' => $totalMonthlyLateHoursFormatted,
                'totalMonthlyUndertimeHoursFormatted' => $totalMonthlyUndertimeHoursFormatted,
                'permission' => $permission,
                'gracePeriod' => $gracePeriod,
                'isFlexible' => $isFlexible,
                'isRestDay' => $isRestDay,
                'subBlocked' => $subBlocked,
                'subBlockMessage' => $subBlockMessage,
                'currentActiveAssignment' => $currentActiveAssignment,
                'allowedMinutesBeforeClockIn' => $nextAssignment && !$nextAssignment->is_rest_day && $nextAssignment->shift ? ($nextAssignment->shift->allowed_minutes_before_clock_in ?? 0) : 0,
                'shiftName' => $nextAssignment ? ($nextAssignment->is_rest_day ? 'Rest Day' : ($nextAssignment->shift->name ?? 'Current Shift')) : 'Current Shift',
            ]
        );
    }

    /**
     * Clock in for the current shift or rest day.
     *
     * Allows an employee to clock in for their assigned shift or rest day, with support for geotagging, geofencing, photo capture, and late reason.
     *
     * @param \Illuminate\Http\Request $request
     * @bodyParam clock_in_method string Optional. Device or method used for clock-in. Example: "Timora Mobile App"
     * @bodyParam time_in_photo file Optional. Photo for clock-in (required if photo capture is enabled).
     * @bodyParam time_in_latitude float Optional. Latitude for geotagging (required if geotagging/geofencing is enabled). Example: 14.5995
     * @bodyParam time_in_longitude float Optional. Longitude for geotagging (required if geotagging/geofencing is enabled). Example: 120.9842
     * @bodyParam time_in_accuracy float Optional. Location accuracy in meters. Example: 5.0
     * @bodyParam late_status_reason string Optional. Reason for being late (required if late status box is enabled and user is late).
     *
     * @response 200 {
     *   "message": "Clock-In successful for Morning Shift",
     *   "data": { "attendance_id": 123, ... }
     * }
     * @response 403 {
     *   "message": "Your 7-day trial period has ended. Please contact your administrator."
     * }
     * @response 422 {
     *   "message": "Photo is required before clock-in."
     * }
     */
    public function employeeAttendanceClockIn(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $todayMonthDay = Carbon::today()->format('m-d');
        $now = Carbon::now();
        $todayDay = strtolower($now->format('D'));
        $settings = AttendanceSettings::first();

        // Subscription validation
        $subscription = Subscription::where('tenant_id', $user->tenant_id)->first();

        if (
            $subscription &&
            $subscription->status === 'trial' &&
            $subscription->trial_end &&
            now()->toDateString() >= \Carbon\Carbon::parse($subscription->trial_end)->toDateString()
        ) {
            Log::warning('Clock-in blocked: Trial period ended', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'subscription_id' => $subscription->id,
                'trial_end' => $subscription->trial_end,
                'attempted_at' => now()->toDateTimeString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Your 7-day trial period has ended. Please contact your administrator.'
            ], 403);
        }

        if (
            $subscription &&
            $subscription->status === 'expired'
        ) {
            Log::warning('Clock-in blocked: Subscription expired', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
                'subscription_end' => $subscription->subscription_end,
                'attempted_at' => now()->toDateTimeString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Your subscription has expired. Please contact your administrator.'
            ], 403);
        }

        // Check if current date exceeds subscription end date
        if (
            $subscription &&
            $subscription->subscription_end &&
            now()->toDateString() > \Carbon\Carbon::parse($subscription->subscription_end)->toDateString()
        ) {
            Log::warning('Clock-in blocked: Subscription end date exceeded', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'subscription_id' => $subscription->id,
                'subscription_end' => $subscription->subscription_end,
                'current_date' => now()->toDateString(),
                'attempted_at' => now()->toDateTimeString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Your subscription period has ended. Please contact your administrator to renew.'
            ], 403);
        }

        // 1. Get all shift assignments for today
        $assignments = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->get()
            ->filter(function ($assignment) use ($today, $todayDay) {
                // ✅ FIX: Allow rest day assignments
                if ($assignment->is_rest_day) {
                    // Check if rest day is valid for today
                    if ($assignment->excluded_dates && in_array($today, $assignment->excluded_dates)) {
                        return false;
                    }

                    if ($assignment->type === 'recurring') {
                        $start = Carbon::parse($assignment->start_date);
                        $end = $assignment->end_date ? Carbon::parse($assignment->end_date) : now();
                        return $start->lte($today) && $end->gte($today) && in_array($todayDay, $assignment->days_of_week);
                    }

                    if ($assignment->type === 'custom') {
                        return in_array($today, $assignment->custom_dates ?? []);
                    }

                    return false;
                }

                // Regular shift validation
                if (!$assignment->shift) {
                    Log::warning('ShiftAssignment has no related shift during clock-in', [
                        'assignment_id' => $assignment->id,
                        'user_id' => $assignment->user_id,
                        'shift_id' => $assignment->shift_id
                    ]);
                    return false;
                }

                if ($assignment->excluded_dates && in_array($today, $assignment->excluded_dates)) {
                    return false;
                }

                if ($assignment->type === 'recurring') {
                    $start = Carbon::parse($assignment->start_date);
                    $end = $assignment->end_date ? Carbon::parse($assignment->end_date) : now();
                    return $start->lte($today) && $end->gte($today) && in_array($todayDay, $assignment->days_of_week);
                }

                if ($assignment->type === 'custom') {
                    return in_array($today, $assignment->custom_dates ?? []);
                }

                return false;
            })

            // 2️⃣ Time-window filter: drop shifts that have already ended
            ->filter(function ($assignment) use ($today, $now) {
                // ✅ Skip time filter for rest days
                if ($assignment->is_rest_day) {
                    return true;
                }

                $shift = $assignment->shift;
                if (!$shift) {
                    return false;
                }

                if ($shift->is_flexible) {
                    return true;
                }

                if (!$shift->start_time || !$shift->end_time) {
                    return true;
                }

                $start = Carbon::parse("{$today} {$shift->start_time}");
                $end = Carbon::parse("{$today} {$shift->end_time}");

                if ($end->lte($start)) {
                    $end->addDay();
                }

                return $now->lte($end);
            })

            //  Sort by shift start time (flexible shifts will have null, so sort last)
            ->sortBy(function ($assignment) {
                if ($assignment->is_rest_day) {
                    return '00:00:00'; // Prioritize rest days
                }
                return $assignment->shift ? ($assignment->shift->start_time ?? '99:99:99') : '99:99:99';
            });

        if ($assignments->isEmpty()) {
            return response()->json(['message' => 'No active shift today.'], 403);
        }

        // Check if it's a rest day today (even if shift matches)
        $restDayAssignment = $assignments->firstWhere('is_rest_day', true);

        if ($restDayAssignment && !$settings->rest_day_time_in_allowed) {
            return response()->json(['message' => 'Clock-in on rest days is not allowed. Please contact your administrator.'], 403);
        }

        // Check existing attendance for today and yesterday (for night shifts)
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where(function ($query) use ($today) {
                $yesterday = Carbon::yesterday()->toDateString();
                // Include today's attendance OR yesterday's attendance (for night shifts)
                $query->where('attendance_date', $today)
                    ->orWhere('attendance_date', $yesterday);
            })
            ->whereNull('date_time_out') // Only unclosed attendance matters
            ->latest('date_time_in')
            ->first();

        if ($existingAttendance) {
            return response()->json(['message' => 'You already clocked in your current shift and haven\'t clocked out.'], 403);
        }

        // ✅ Prioritize rest day if settings allow
        if ($restDayAssignment && $settings->rest_day_time_in_allowed) {
            $nextAssignment = $restDayAssignment;
        } else {
            // Find the next available shift assignment
            $nextAssignment = $assignments->first(function ($assignment) use ($user, $today) {
                if ($assignment->is_rest_day) {
                    return false; // Skip rest days if not prioritized
                }

                $alreadyClocked = Attendance::where('user_id', $user->id)
                    ->where('shift_id', $assignment->shift_id)
                    ->where('shift_assignment_id', $assignment->id)
                    ->where('attendance_date', $today)
                    ->exists();

                return !$alreadyClocked;
            });
        }

        if (!$nextAssignment) {
            return response()->json(['message' => 'All shifts already clocked in today.'], 403);
        }

        // ✅ Handle rest day clock-in differently
        if ($nextAssignment->is_rest_day) {
            // Rest day logic - no shift validation needed
            $shift = null;
            $isFlexible = true; // Treat rest days as flexible
            $graceMin = 0;
            $shiftStart = null;
            $lateMinutes = 0;
            $status = 'present';
            $totalLateMinutes = 0;
            $lateReason = null; // No late reason for rest days

            Log::info('Rest day clock-in', [
                'user_id' => $user->id,
                'assignment_id' => $nextAssignment->id,
                'date' => $today
            ]);
        } else {
            // Regular shift logic
            if (!$nextAssignment->shift) {
                return response()->json([
                    'message' => 'Shift configuration not found for this assignment.'
                ], 403);
            }

            $shift = $nextAssignment->shift;
            $isFlexible = $shift && $shift->is_flexible;

            if (!$isFlexible && $shift->start_time && $shift->allowed_minutes_before_clock_in !== null) {
                $allowedMinutesBefore = (int) $shift->allowed_minutes_before_clock_in;

                if ($allowedMinutesBefore > 0) {
                    $shiftStart = Carbon::parse("{$today} {$shift->start_time}");
                    $earliestAllowedTime = $shiftStart->copy()->subMinutes($allowedMinutesBefore);

                    if ($now->lessThan($earliestAllowedTime)) {
                        $timeUntilAllowed = $now->diffInMinutes($earliestAllowedTime);
                        $allowedTime = $earliestAllowedTime->format('g:i A');

                        return response()->json([
                            'message' => "You can only clock in starting at {$allowedTime}.",
                            'earliest_allowed_time' => $allowedTime,
                            'minutes_until_allowed' => $timeUntilAllowed
                        ], 403);
                    }
                }
            }

            // Grace Period & Late Computation
            $graceMin = $isFlexible ? 0 : ($shift->grace_period ?? 0);
            $shiftStart = $isFlexible || !$shift->start_time ? null : Carbon::parse("{$today} {$shift->start_time}");
            $lateMinutes = 0;

            if (!$isFlexible && $shiftStart && $now->greaterThan($shiftStart)) {
                $lateMinutes = floor($shiftStart->diffInMinutes($now, false));
            }

            if ($isFlexible) {
                $status = 'present';
                $totalLateMinutes = 0;
            } else {
                if ($lateMinutes > $graceMin) {
                    $status            = 'late';
                    $totalLateMinutes  = $lateMinutes;
                } else {
                    $status            = 'present';
                    $totalLateMinutes  = 0;
                }
            }

            // Late Status Box (Late Reason)
            $lateReason = null;
            if ($settings->enable_late_status_box && $status === 'late') {
                if (!$request->filled('late_status_reason')) {
                    return response()->json([
                        'message' => 'Please provide a reason for being late.'
                    ], 422);
                }
                $lateReason = $request->input('late_status_reason');
            }
        }

        // Require Photo
        $photoPath = null;
        if ($settings->require_photo_capture) {
            if (!$request->hasFile('time_in_photo')) {
                return response()->json([
                    'message' => 'Photo is required before clock-in.'
                ], 422);
            }
            $photoPath = $request->file('time_in_photo')->store('attendance_photos', 'public');
        }

        // Geotagging Handling
        $latitude  = null;
        $longitude = null;
        $accuracy  = 0;
        if ($settings->geotagging_enabled || $settings->geofencing_enabled) {
            $latitude  = $request->input('time_in_latitude');
            $longitude = $request->input('time_in_longitude');
            if (! $latitude || ! $longitude) {
                return response()->json([
                    'message' => 'Location is required before clocking in.'
                ], 422);
            }
        }

        // Geofence Handling
        $usedFenceId = null;
        if ($settings->geofencing_enabled) {
            $buffer    =  $settings->geofence_buffer;
            $today     = Carbon::today()->toDateString();

            // a) Branch‐level fences
            $branchId  = optional($user->employmentDetail)->branch_id;
            $branchIds = $branchId
                ? Geofence::where('branch_id', $branchId)->pluck('id')->toArray()
                : [];

            // b) User‐specific overrides
            $gu        = GeofenceUser::where('user_id', $user->id)->get();
            $manualIds = $gu->where('assignment_type', 'manual')->pluck('geofence_id')->toArray();
            $exemptIds = $gu->where('assignment_type', 'exempt')->pluck('geofence_id')->toArray();

            // c) Combine manual + (branch minus exempt)
            $allowedIds = array_unique(array_merge(
                $manualIds,
                array_diff($branchIds, $exemptIds)
            ));

            // d) Fetch “active” fences that either never expire (expiration_date IS NULL)
            $fences = Geofence::whereIn('id', $allowedIds)
                ->where('status', 'active')
                ->where(function ($q) use ($today) {
                    $q->whereNull('expiration_date')
                        ->orWhereDate('expiration_date', '>=', $today);
                })
                ->get();

            Log::debug('Fetched fences detail (post-update):', $fences->map->only(['id', 'status', 'expiration_date'])->toArray());
            Log::debug('Active + unexpired fences count (post-update)', ['count' => $fences->count()]);

            if ($fences->isEmpty()) {
                return response()->json(['message' => 'No active geofence available for you.'], 403);
            }

            // e) Haversine check with buffer
            $inside = false;
            foreach ($fences as $f) {
                $dist = $this->haversineDistance(
                    $latitude,
                    $longitude,
                    $f->latitude,
                    $f->longitude
                );
                $effective = $f->geofence_radius + $buffer + $accuracy;
                if ($dist <= $effective) {
                    $inside     = true;
                    $usedFenceId = $f->id;
                    break;
                }
            }

            // 3-strike fallback key
            $cacheKey = "geofence_attempts:{$user->id}";

            if ($inside) {
                // ✅ Inside → clear any old counters
                Cache::forget($cacheKey);
            } else {

                // 1) If geotagging itself is OFF → block immediately
                if (! $settings->geotagging_enabled) {
                    return response()->json([
                        'message' => 'Location is required before clocking in. Please enable geotagging.',
                    ], 422);
                }

                // 2) Geotagging ON → do we allow fallback?
                if ($settings->geofence_allowed_geotagging) {
                    // increment attempt count (10-minute window)
                    $attempts = Cache::get($cacheKey, 0) + 1;
                    Cache::put($cacheKey, $attempts, now()->addMinutes(10));

                    if ($attempts < 2) {
                        return response()->json([
                            'message' => "Weak signal detected. Please try again. Attempts left: " . (2 - $attempts)
                        ], 403);
                    }

                    // 3rd failure: clear counter so the 4th try is allowed
                    Cache::forget($cacheKey);
                } else {
                    // 3) Strict mode: never allow outside
                    return response()->json([
                        'message'          => 'You are outside the permitted area.',
                        'distance'         => round($dist, 2),
                        'effective_radius' => round($effective, 2),
                    ], 403);
                }
            }
        }

        // Device Detection
        $agent    = new Agent();
        $device   = $agent->device()    ?: 'UnknownDevice';
        $platform = $agent->platform()  ?: 'UnknownOS';
        $browser  = $agent->browser()   ?: 'UnknownBrowser';
        $version  = $agent->version($browser) ?: '';

        // Holiday Check
        $exception = HolidayException::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('holiday', function ($q) use ($today, $todayMonthDay) {
                // optionally scope exception to only today’s holiday(s)
                $q->where(function ($q1) use ($today, $todayMonthDay) {
                    $q1->where('recurring', true)
                        ->where('month_day', $todayMonthDay);
                })->orWhere(function ($q2) use ($today) {
                    $q2->where('recurring', false)
                        ->where('date', $today);
                });
            })
            ->first();

        if ($exception) {
            $isHoliday = false;
            $holidayId = null;
        } else {
            // 2️⃣ No exception → find today’s holiday (if any)
            $holiday = Holiday::where('status', 'active')
                ->where(function ($q) use ($today, $todayMonthDay) {
                    $q->where(function ($q2) use ($todayMonthDay) {
                        $q2->where('recurring', true)
                            ->where('month_day', $todayMonthDay);
                    })
                        ->orWhere(function ($q3) use ($today) {
                            $q3->where('recurring', false)
                                ->where('date', $today);
                        });
                })
                ->first();

            $isHoliday = (bool) $holiday;
            $holidayId = $holiday?->id;
        }

        // Create Attendance
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'shift_id' => $nextAssignment->shift_id, // Will be null for rest days
            'shift_assignment_id' => $nextAssignment->id,
            'geofence_id' => $usedFenceId ?? null,
            'attendance_date' => $today,
            'date_time_in' => $now,
            'status' => $status,
            'total_late_minutes' => $totalLateMinutes,
            'clock_in_method' => $request->input('clock_in_method') === 'Timora Mobile App' ? 'Timora Mobile App' : $device,
            'time_in_photo_path' => $photoPath,
            'time_in_latitude' => $latitude,
            'time_in_longitude' => $longitude,
            'late_status_box' => $lateReason,
            'is_rest_day' => $nextAssignment->is_rest_day, // ✅ Set rest day flag
            'is_holiday' => $isHoliday ?? false,
            'holiday_id' => $holidayId ?? null,
        ]);

        // Shift Name
        $shiftName = $nextAssignment->is_rest_day
            ? 'Rest Day'
            : ($shift->name ?? 'Unknown Shift');

        if ($nextAssignment->is_rest_day) {
            $message = 'Rest Day Clock-In successful. Your hours will be calculated based on your actual work time.';
        } else {
            // Use safe interpolation for the shift name
            $message = $attendance->is_holiday
                ? "Holiday Clock-In successful for {$shiftName}"
                : "Clock-In successful for {$shiftName}";
        }

        return response()->json([
            'message' => $message,
            'data' => $attendance,
        ]);
    }

    // Geofence Helper function (Earth Radius)
    protected function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Force floats
        $lat1 = (float) $lat1;
        $lon1 = (float) $lon1;
        $lat2 = (float) $lat2;
        $lon2 = (float) $lon2;

        $earthRadius = 6371000.0; // metres

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $rLat1 = deg2rad($lat1);
        $rLat2 = deg2rad($lat2);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos($rLat1)
            * cos($rLat2)
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Start a break for the current shift.
     *
     * Allows an employee to start a break during their active shift. Only one break is allowed per shift.
     *
     * @param \Illuminate\Http\Request $request
     * @bodyParam break_type string Optional. Type of break (e.g., "lunch", "rest"). Example: "lunch"
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Break started successfully for Morning Shift.",
     *   "data": {
     *     "attendance_id": 123,
     *     "shift_id": 1,
     *     "shift_name": "Morning Shift",
     *     "break_type": "lunch",
     *     "break_in": "12:00:00",
     *     "max_break_minutes": 60
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "You must be clocked in to start a break."
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "You have already completed your break for this shift. Only one break is allowed per shift."
     * }
     */
    public function breakIn(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        // ✅ ENHANCED: Find current attendance using shift-based logic
        $currentAttendance = $this->findActiveAttendanceByShift($user->id);

        // Additional validation: Ensure current time is within shift hours
        if ($currentAttendance && $currentAttendance->shift) {
            if (!$this->isWithinShiftHours($currentAttendance->shift, $now)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Break is only allowed during your shift hours (' . 
                               $currentAttendance->shift->start_time . ' - ' . 
                               $currentAttendance->shift->end_time . ').'
                ], 403);
            }
        }

        if (!$currentAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'You must be clocked in to start a break.'
            ], 403);
        }

        // ✅ ENHANCED: Check if user already took ANY break for this specific shift
        if ($currentAttendance->break_in) {
            if ($currentAttendance->break_out) {
                // Break already completed for this shift
                return response()->json([
                    'success' => false,
                    'message' => 'You have already completed your break for this shift (' . 
                               ($currentAttendance->shift->name ?? 'Current Shift') . '). Only one break is allowed per shift.'
                ], 403);
            } else {
                // Break currently active for this shift
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active break for this shift (' . 
                               ($currentAttendance->shift->name ?? 'Current Shift') . '). Please end your current break first.'
                ], 403);
            }
        }

        // ✅ ADDITIONAL: Double-check for any other attendance records with the same shift_id that have breaks
        if ($currentAttendance->shift_id) {
            $existingBreakInShift = Attendance::where('user_id', $user->id)
                ->where('shift_id', $currentAttendance->shift_id)
                ->whereNotNull('break_in')
                ->where('id', '!=', $currentAttendance->id) // Exclude current record
                ->exists();

            if ($existingBreakInShift) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already used your break for this shift (' . 
                               ($currentAttendance->shift->name ?? 'Current Shift') . '). Only one break is allowed per shift.'
                ], 403);
            }
        }

        // Get shift break minutes
        $shift = $currentAttendance->shift;
        $maxBreakMinutes = $shift ? $shift->break_minutes : 0;

        if ($maxBreakMinutes <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Break time is not allowed for this shift.'
            ], 403);
        }

        // ✅ FIXED: Update attendance record with break_in (reset break_late to 0)
        $currentAttendance->update([
            'break_in' => $now,
            'break_late' => 0, // Reset break late for new break
        ]);

        Log::info('Break started', [
            'user_id' => $user->id,
            'attendance_id' => $currentAttendance->id,
            'shift_id' => $currentAttendance->shift_id,
            'shift_name' => $shift->name ?? 'Unknown Shift',
            'break_type' => $request->break_type,
            'break_in' => $now->toDateTimeString(),
            'max_break_minutes' => $maxBreakMinutes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Break started successfully for ' . ($shift->name ?? 'current shift') . '.',
            'data' => [
                'attendance_id' => $currentAttendance->id,
                'shift_id' => $currentAttendance->shift_id,
                'shift_name' => $shift->name ?? 'Current Shift',
                'break_type' => $request->break_type,
                'break_in' => $currentAttendance->break_in ? $currentAttendance->break_in->format('H:i:s') : null,
                'max_break_minutes' => $maxBreakMinutes
            ]
        ]);
    }

    /**
     * End a break for the current shift.
     *
     * Allows an employee to end their active break during the current shift. Calculates break duration and flags if the break exceeded the allowed time.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Break ended successfully for Morning Shift.",
     *   "data": {
     *     "attendance_id": 123,
     *     "shift_id": 1,
     *     "shift_name": "Morning Shift",
     *     "break_in": "12:00:00",
     *     "break_out": "12:45:00",
     *     "duration_minutes": 45,
     *     "break_late_minutes": 15,
     *     "max_break_minutes": 30
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "No active break found for your current shift."
     * }
     */
    public function breakOut(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        // ✅ ENHANCED: Find active attendance with break using shift-based logic
        $currentAttendance = $this->findActiveAttendanceByShift($user->id);

        // Validate that there's an active break
        if ($currentAttendance && (!$currentAttendance->break_in || $currentAttendance->break_out)) {
            $currentAttendance = null; // Reset if no active break found
        }

        // Additional validation: Ensure current time is within shift hours
        if ($currentAttendance && $currentAttendance->shift) {
            if (!$this->isWithinShiftHours($currentAttendance->shift, $now)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Break can only be ended during your shift hours (' . 
                               $currentAttendance->shift->start_time . ' - ' . 
                               $currentAttendance->shift->end_time . ').'
                ], 403);
            }
        }

        if (!$currentAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'No active break found for your current shift.'
            ], 404);
        }

        // Calculate break duration
        $breakDuration = $currentAttendance->break_in->diffInMinutes($now);
        $maxBreakMinutes = $currentAttendance->shift ? $currentAttendance->shift->break_minutes : 0;

        // Calculate break late (if any)
        $breakLate = 0;
        if ($breakDuration > $maxBreakMinutes) {
            $breakLate = $breakDuration - $maxBreakMinutes;
        }

        // Update attendance record
        $currentAttendance->update([
            'break_out' => $now,
            'break_late' => $breakLate,
        ]);

        $shift = $currentAttendance->shift;

        Log::info('Break ended', [
            'user_id' => $user->id,
            'attendance_id' => $currentAttendance->id,
            'shift_id' => $currentAttendance->shift_id, // ✅ Added shift_id for clarity
            'shift_name' => $shift->name ?? 'Unknown Shift',
            'break_out' => $now->toDateTimeString(),
            'duration_minutes' => $breakDuration,
            'break_late_minutes' => $breakLate,
            'max_break_minutes' => $maxBreakMinutes
        ]);

        $shiftName = $shift->name ?? 'current shift';
        $message = $breakLate > 0
            ? "Break ended for {$shiftName}. You exceeded the allowed break time by {$breakLate} minutes."
            : "Break ended successfully for {$shiftName}.";

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'attendance_id' => $currentAttendance->id,
                'shift_id' => $currentAttendance->shift_id,
                'shift_name' => $shiftName,
                'break_in' => $currentAttendance->break_in ? $currentAttendance->break_in->format('H:i:s') : null,
                'break_out' => $currentAttendance->break_out ? $currentAttendance->break_out->format('H:i:s') : null,
                'duration_minutes' => $breakDuration,
                'break_late_minutes' => $breakLate,
                'max_break_minutes' => $maxBreakMinutes
            ]
        ]);
    }

    // BREAK STATUS
    public function breakStatus()
    {
        $user = Auth::user();
        $now = Carbon::now();

        // ✅ ENHANCED: Use shift-based logic to find current active attendance
        $currentAttendance = $this->findActiveAttendanceByShift($user->id);

        // Additional validation: Check if current time is within shift hours
        if ($currentAttendance && $currentAttendance->shift) {
            if (!$this->isWithinShiftHours($currentAttendance->shift, $now)) {
                return response()->json([
                    'success' => true,
                    'has_active_break' => false,
                    'is_within_shift_hours' => false,
                    'message' => 'Current time is outside shift hours (' . 
                               $currentAttendance->shift->start_time . ' - ' . 
                               $currentAttendance->shift->end_time . ').',
                    'data' => null
                ]);
            }
        }

        if (!$currentAttendance) {
            return response()->json([
                'success' => true,
                'has_active_break' => false,
                'message' => 'No active attendance found.',
                'data' => null
            ]);
        }

        $shift = $currentAttendance->shift;
        $maxBreakMinutes = $shift ? $shift->break_minutes : 0;

        // ✅ NEW: Check if break is already completed
        if ($currentAttendance->break_in && $currentAttendance->break_out) {
            return response()->json([
                'success' => true,
                'has_active_break' => false,
                'break_completed' => true,
                'message' => 'Break already completed for this shift.',
                'data' => [
                    'attendance_id' => $currentAttendance->id,
                    'shift_id' => $currentAttendance->shift_id,
                    'shift_name' => $shift->name ?? 'Current Shift',
                    'break_in' => $currentAttendance->break_in ? $currentAttendance->break_in->format('H:i:s') : null,
                    'break_out' => $currentAttendance->break_out ? $currentAttendance->break_out->format('H:i:s') : null,
                    'break_completed' => true,
                    'max_break_minutes' => $maxBreakMinutes
                ]
            ]);
        }

        // ✅ EXISTING: Check if break is currently active
        if ($currentAttendance->break_in && !$currentAttendance->break_out) {
            $currentDuration = $currentAttendance->break_in->diffInMinutes(Carbon::now());
            $remainingMinutes = max(0, $maxBreakMinutes - $currentDuration);

            return response()->json([
                'success' => true,
                'has_active_break' => true,
                'break_completed' => false,
                'data' => [
                    'attendance_id' => $currentAttendance->id,
                    'shift_id' => $currentAttendance->shift_id,
                    'shift_name' => $shift->name ?? 'Current Shift',
                    'break_in' => $currentAttendance->break_in ? $currentAttendance->break_in->format('H:i:s') : null,
                    'current_duration' => $currentDuration,
                    'max_break_minutes' => $maxBreakMinutes,
                    'remaining_minutes' => $remainingMinutes,
                    'is_overtime' => $currentDuration > $maxBreakMinutes
                ]
            ]);
        }

        // ✅ NEW: No break taken yet - allow break
        return response()->json([
            'success' => true,
            'has_active_break' => false,
            'break_completed' => false,
            'message' => 'No break taken yet for this shift.',
            'data' => [
                'attendance_id' => $currentAttendance->id,
                'shift_id' => $currentAttendance->shift_id,
                'shift_name' => $shift->name ?? 'Current Shift',
                'max_break_minutes' => $maxBreakMinutes,
                'break_available' => $maxBreakMinutes > 0
            ]
        ]);
    }

    // Overtime Create
    private function createAutomaticOvertime($attendance, $extraMinutes, $user)
    {
        // ✅ FIX: Validate attendance has required data
        if (!$attendance->date_time_in || !$attendance->date_time_out) {
            Log::error('Cannot create automatic overtime: Missing clock-in or clock-out time', [
                'attendance_id' => $attendance->id,
                'date_time_in' => $attendance->date_time_in,
                'date_time_out' => $attendance->date_time_out
            ]);
            return null;
        }

        // ✅ FIX: Check if shift exists and has maximum_allowed_hours
        if (!$attendance->shift || !$attendance->shift->maximum_allowed_hours) {
            Log::error('Cannot create automatic overtime: Missing shift or maximum_allowed_hours', [
                'attendance_id' => $attendance->id,
                'shift_id' => $attendance->shift_id,
                'has_shift' => $attendance->shift ? 'yes' : 'no',
                'max_hours' => $attendance->shift?->maximum_allowed_hours
            ]);
            return null;
        }

        // Calculate overtime start time (after max allowed hours)
        $overtimeStart = $attendance->date_time_in->copy()->addMinutes($attendance->shift->maximum_allowed_hours * 60);
        $overtimeEnd = $attendance->date_time_out->copy();

        // ✅ Validate overtime period
        if ($overtimeStart->gte($overtimeEnd)) {
            Log::warning('Invalid overtime period: Start time is after or equal to end time', [
                'attendance_id' => $attendance->id,
                'overtime_start' => $overtimeStart->toDateTimeString(),
                'overtime_end' => $overtimeEnd->toDateTimeString()
            ]);
            return null;
        }

        // Calculate night diff for overtime period only
        $overtimeNightDiffMinutes = 0;
        $currentWorkStart = $overtimeStart->copy();
        $currentWorkEnd = $overtimeEnd->copy();

        while ($currentWorkStart->lt($currentWorkEnd)) {
            $dayStart = $currentWorkStart->copy()->startOfDay();
            $nightStart = $dayStart->copy()->setTime(22, 0, 0);
            $nightEnd = $dayStart->copy()->addDay()->setTime(6, 0, 0);

            $workPeriodStart = max($currentWorkStart->timestamp, $nightStart->timestamp);
            $workPeriodEnd = min($currentWorkEnd->timestamp, $nightEnd->timestamp);

            if ($workPeriodEnd > $workPeriodStart) {
                $dayNightDiffMinutes = round(($workPeriodEnd - $workPeriodStart) / 60, 6);
                $overtimeNightDiffMinutes += $dayNightDiffMinutes;
            }

            $currentWorkStart = $dayStart->copy()->addDay();
        }

        $overtimeNightDiffMinutes = max(0, floor($overtimeNightDiffMinutes));

        // Check if it's a rest day or holiday
        $isRestDay = $attendance->is_rest_day ?? false;
        $isHoliday = $attendance->is_holiday ?? false;

        // Create overtime record
        $overtime = Overtime::create([
            'user_id' => $user->id,
            'holiday_id' => $attendance->holiday_id ?? null,
            'overtime_date' => $attendance->attendance_date,
            'date_ot_in' => $overtimeStart,
            'date_ot_out' => $overtimeEnd,
            'total_ot_minutes' => $extraMinutes,
            'total_night_diff_minutes' => $overtimeNightDiffMinutes,
            'is_rest_day' => $isRestDay,
            'is_holiday' => $isHoliday,
            'status' => 'pending',
            'ot_login_type' => 'automatic',
            'reason' => 'Automatic overtime created from excess work hours beyond shift maximum limit',
            'current_step' => 0,
        ]);

        Log::info('✅ Automatic overtime created', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'overtime_id' => $overtime->id,
            'total_ot_minutes' => $extraMinutes,
            'total_night_diff_minutes' => $overtimeNightDiffMinutes,
            'is_rest_day' => $isRestDay,
            'is_holiday' => $isHoliday,
            'date_ot_in' => $overtimeStart->toDateTimeString(),
            'date_ot_out' => $overtimeEnd->toDateTimeString()
        ]);

        return $overtime;
    }

    /**
     * Clock out for the current shift.
     *
     * Allows an employee to clock out for their active shift, with support for geotagging, geofencing, photo capture, and automatic overtime calculation.
     *
     * @param \Illuminate\Http\Request $request
     * @bodyParam shift_id integer Optional. The shift ID to clock out from. Example: 1
     * @bodyParam time_out_photo file Required if photo capture is enabled. Photo for clock-out.
     * @bodyParam time_out_latitude float Required if geotagging/geofencing is enabled. Latitude for geotagging. Example: 14.5995
     * @bodyParam time_out_longitude float Required if geotagging/geofencing is enabled. Longitude for geotagging. Example: 120.9842
     * @bodyParam time_out_accuracy float Optional. Location accuracy in meters. Example: 5.0
     * @bodyParam clock_out_method string Optional. Device or method used for clock-out. Example: "Timora Mobile App"
     *
     * @response 200 {
     *   "message": "You have successfully clocked out.",
     *   "data": { "attendance_id": 123, ... },
     *   "overtime_created": {
     *     "overtime_id": 456,
     *     "total_ot_minutes": 90,
     *     "total_ot_formatted": "1 hr 30 min",
     *     "status": "pending",
     *     "is_rest_day": false,
     *     "is_holiday": false
     *   }
     * }
     * @response 403 {
     *   "message": "We could not find your clock-in record. Please make sure you have clocked in before trying to clock out."
     * }
     * @response 422 {
     *   "message": "A photo is required to clock out. Please upload your photo and try again."
     * }
     */
    public function employeeAttendanceClockOut(Request $request)
    {
        // Validate input with user-friendly messages
        $validator = Validator::make($request->all(), [
            'shift_id'           => 'nullable|integer',
            'time_out_photo'     => 'required_if:require_photo_capture,1|file|image',
            'time_out_latitude'  => 'required_if:geotagging_enabled,1|nullable',
            'time_out_longitude' => 'required_if:geotagging_enabled,1|nullable',
        ], [
            'shift_id.required'           => 'Please select your shift before clocking out.',
            'shift_id.integer'            => 'Invalid shift selected.',
            'time_out_photo.required_if'  => 'A photo is required to clock out. Please upload your photo.',
            'time_out_photo.file'         => 'Please upload a valid photo file.',
            'time_out_photo.image'        => 'The uploaded file must be an image.',
            'time_out_latitude.required_if'  => 'Please enable your location to clock out.',
            'time_out_longitude.required_if' => 'Please enable your location to clock out.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Clock-Out failed: ' . $validator->errors()->first(),
            ], 422);
        }

        $user     = Auth::user();
        $shiftId  = $request->input('shift_id');
        $settings = AttendanceSettings::first();
        $today    = Carbon::today()->toDateString();
        $now      = Carbon::now();

        // 2️⃣ Find the matching clock-in record (including night shifts from previous day)
        $attendanceQuery = Attendance::where('user_id', $user->id)
            ->where(function ($query) use ($today) {
                $yesterday = Carbon::yesterday()->toDateString();
                // Include today's attendance OR yesterday's attendance (for night shifts)
                $query->where('attendance_date', $today)
                    ->orWhere('attendance_date', $yesterday);
            })
            ->whereNotNull('date_time_in')
            ->whereNull('date_time_out');

        // If shift_id is provided, use it; otherwise find the latest unclosed attendance
        if ($shiftId) {
            $attendanceQuery->where('shift_id', $shiftId);
        }

        $attendance = $attendanceQuery->latest('date_time_in')->first();

        if (! $attendance) {
            return response()->json([
                'message' => 'We could not find your clock-in record. Please make sure you have clocked in before trying to clock out.'
            ], 403);
        }

        // Check if there's an ongoing break
        if ($attendance->break_in && !$attendance->break_out) {
            return response()->json([
                'message' => 'You cannot clock out while on break. Please end your break first before clocking out.'
            ], 403);
        }

        // Log if shift is flexible or not
        $isFlexible = $attendance->shift && $attendance->shift->is_flexible;

        // Security Check: Don't allow clock-out if next shift is already ongoing
        $todayDay = strtolower($now->format('D'));

        // Get all shift assignments for today
        $nextShiftAssignments = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->where('id', '!=', $attendance->shift_assignment_id) // Exclude current shift
            ->get()

            // Filter for today's assignments
            ->filter(function ($assignment) use ($today, $todayDay) {
                // Skip excluded dates
                if ($assignment->excluded_dates && in_array($today, $assignment->excluded_dates)) {
                    return false;
                }

                // Recurring assignments
                if ($assignment->type === 'recurring') {
                    $start = Carbon::parse($assignment->start_date);
                    $end   = $assignment->end_date ? Carbon::parse($assignment->end_date) : now();

                    return $start->lte($today)
                        && $end->gte($today)
                        && in_array($todayDay, $assignment->days_of_week);
                }

                // Custom assignments
                if ($assignment->type === 'custom') {
                    return in_array($today, $assignment->custom_dates ?? []);
                }

                return false;
            })

            // Filter for shifts that have already started
            ->filter(function ($assignment) use ($today, $now) {
                $shift = $assignment->shift;
                if (!$shift || !$shift->start_time || !$shift->end_time) {
                    return false;
                }

                // Skip flexible shifts for this check
                if ($shift->is_flexible) {
                    return false;
                }

                $shiftStart = Carbon::parse("{$today} {$shift->start_time}");
                $shiftEnd = Carbon::parse("{$today} {$shift->end_time}");

                // Handle night shifts that cross midnight
                if ($shiftEnd->lte($shiftStart)) {
                    $shiftEnd->addDay();
                }

                $gracePeriod = $shift->grace_period ?? 0;
                $shiftStartWithGrace = $shiftStart->copy()->addMinutes($gracePeriod);

                // Check if current time is within the shift window (start + grace to end)
                return $now->gte($shiftStartWithGrace) && $now->lte($shiftEnd);
            })

            // Check if user hasn't clocked in to this next shift yet
            ->filter(function ($assignment) use ($user, $today) {
                return !Attendance::where('user_id', $user->id)
                    ->where('shift_id', $assignment->shift_id)
                    ->where('shift_assignment_id', $assignment->id)
                    ->where('attendance_date', $today)
                    ->exists();
            });

        if ($nextShiftAssignments->isNotEmpty()) {
            $nextShift = $nextShiftAssignments->first();
            $nextShiftName = $nextShift->shift ? ($nextShift->shift->name ?? 'Next Shift') : 'Next Shift';
            $nextShiftStart = $nextShift->shift ? ($nextShift->shift->start_time ?? '') : '';

            return response()->json([
                'message' => "You can't clock out yet. Your next shift \"{$nextShiftName}\" (starts at {$nextShiftStart}) has already started and you haven't clocked in for it. Please clock in to your next shift first or ask your admin for help."
            ], 403);
        }


        // Photo capture (if required)
        $photoPath = null;
        if ($settings->require_photo_capture) {
            if (! $request->hasFile('time_out_photo')) {
                return response()->json([
                    'message' => 'A photo is required to clock out. Please upload your photo and try again.'
                ], 422);
            }
            $photoPath = $request
                ->file('time_out_photo')
                ->store('attendance_photos', 'public');
        }

        // Geotag inputs & validation (if enabled)
        $latitude  = null;
        $longitude = null;
        $accuracy  = 0;
        if ($settings->geotagging_enabled || $settings->geofencing_enabled) {
            $latitude  = $request->input('time_out_latitude');
            $longitude = $request->input('time_out_longitude');
            $accuracy  = (float) $request->input('time_out_accuracy', 0);

            if (! $latitude || ! $longitude) {
                return response()->json([
                    'message' => 'Your location is required to clock out. Please enable your location and try again.'
                ], 422);
            }
        }

        // Geofence enforcement (if enabled)
        $usedFenceId = null;
        $inside      = false;
        if ($settings->geofencing_enabled) {
            $buffer   = $settings->geofence_buffer;
            $cacheKey = "geofence_attempts_out:{$user->id}";

            // a) Gather allowed fence IDs
            $branchId  = optional($user->employmentDetail)->branch_id;
            $branchIds = $branchId
                ? Geofence::where('branch_id', $branchId)->pluck('id')->toArray()
                : [];

            $gu        = GeofenceUser::where('user_id', $user->id)->get();
            $manualIds = $gu->where('assignment_type', 'manual')->pluck('geofence_id')->toArray();
            $exemptIds = $gu->where('assignment_type', 'exempt')->pluck('geofence_id')->toArray();

            $allowedIds = array_unique(array_merge(
                $manualIds,
                array_diff($branchIds, $exemptIds)
            ));

            $fences = Geofence::whereIn('id', $allowedIds)
                ->where('status', 'active')
                ->where(function ($q) use ($today) {
                    $q->whereNull('expiration_date')
                        ->orWhereDate('expiration_date', '>=', $today);
                })
                ->get();

            if ($fences->isEmpty()) {
                return response()->json([
                    'message' => 'No permitted area is set for you. Please contact your admin for assistance.'
                ], 403);
            }

            // b) Haversine check
            foreach ($fences as $f) {
                $dist      = $this->haversineDistance(
                    $latitude,
                    $longitude,
                    $f->latitude,
                    $f->longitude
                );
                $effective = $f->geofence_radius + $buffer + $accuracy;

                if ($dist <= $effective) {
                    $inside      = true;
                    $usedFenceId = $f->id;
                    break;
                }
            }

            if ($inside) {
                Cache::forget($cacheKey);
            } else {
                if (! $settings->geotagging_enabled) {
                    return response()->json([
                        'message' => 'Your location is required to clock out. Please enable your location and try again.'
                    ], 422);
                }
                if ($settings->geofence_allowed_geotagging) {
                    $attempts = Cache::get($cacheKey, 0) + 1;
                    Cache::put($cacheKey, $attempts, now()->addMinutes(10));

                    if ($attempts < 2) {
                        return response()->json([
                            'message' => "We couldn't confirm your location. Please try again. Attempts left: " . (2 - $attempts)
                        ], 403);
                    }
                    Cache::forget($cacheKey);
                } else {
                    return response()->json([
                        'message'          => 'You are outside the allowed area for clocking out. Please move closer to the permitted area and try again.',
                        'distance'         => round($dist, 2),
                        'effective_radius' => round($effective, 2),
                    ], 403);
                }
            }
        }

        $start = $attendance->date_time_in; // e.g. 2025-06-04 21:00:00
        $end   = $now;                       // e.g. 2025-06-05 06:00:00

        $totalWorkedMinutes = $start->diffInMinutes($end);

        // Initialize break duration
        $breakDuration = 0;

        // Only use shift's configured break minutes if available
        if ($attendance->break_in) {
            // Only use shift's configured break minutes if available
            if ($attendance->shift && $attendance->shift->break_minutes > 0) {
                $breakDuration = $attendance->shift->break_minutes;

                // Deduct break duration from total worked minutes
                $totalWorkedMinutes = max(0, $totalWorkedMinutes - $breakDuration);
            }
        } else {
            Log::info('[ClockOut] No break was taken, not deducting break time', [
                'user_id' => $user->id,
                'attendance_id' => $attendance->id
            ]);
        }

        // Night diff runs from 10:00 PM to 6:00 AM next day only
        $nightDiffMinutes = 0;

        // Calculate night differential minutes for each day the work spans
        $currentWorkStart = $start->copy();
        $currentWorkEnd = $end->copy();

        while ($currentWorkStart->lt($currentWorkEnd)) {
            $dayStart = $currentWorkStart->copy()->startOfDay();

            // Night shift window: 22:00 to 06:00 next day
            $nightStart = $dayStart->copy()->setTime(22, 0, 0);
            $nightEnd = $dayStart->copy()->addDay()->setTime(6, 0, 0);

            // Find the overlap between work period and night period for this day
            $workPeriodStart = max($currentWorkStart->timestamp, $nightStart->timestamp);
            $workPeriodEnd = min($currentWorkEnd->timestamp, $nightEnd->timestamp);

            if ($workPeriodEnd > $workPeriodStart) {
                $dayNightDiffMinutes = round(($workPeriodEnd - $workPeriodStart) / 60, 6);
                $nightDiffMinutes += $dayNightDiffMinutes;
            }

            // Move to next day
            $currentWorkStart = $dayStart->copy()->addDay();
        }

        // Regular work minutes = total worked minutes - night differential minutes
        $nightDiffMinutes = max(0, floor($nightDiffMinutes));
        $regularMinutes = max(0, $totalWorkedMinutes - $nightDiffMinutes);

        // Apply maximum allowed hours cap only to regular work minutes
        $maxAllowedHours = $attendance->shift && $attendance->shift->maximum_allowed_hours
            ? $attendance->shift->maximum_allowed_hours
            : null;

        $extraMinutes = 0;
        $overtimeCreated = null;

        if ($maxAllowedHours) {
            $capInMin = $maxAllowedHours * 60;

            if ($regularMinutes > $capInMin) {
                $extraMinutes = $regularMinutes - $capInMin;

                // ✅ Check if shift allows automatic overtime creation
                if ($attendance->shift->allow_extra_hours) {
                    // ✅ FIX: Only create overtime AFTER attendance is updated with clock-out
                    // We'll do this after the $attendance->update() call below
                    Log::info('⏳ Extra hours detected - Will create automatic overtime after attendance update', [
                        'user_id' => $user->id,
                        'attendance_id' => $attendance->id,
                        'extra_minutes' => $extraMinutes,
                        'max_allowed_hours' => $maxAllowedHours,
                        'total_worked_minutes' => $regularMinutes
                    ]);
                } else {
                    Log::info('⚠️ Extra hours detected - Automatic overtime NOT created (disabled)', [
                        'user_id' => $user->id,
                        'attendance_id' => $attendance->id,
                        'extra_minutes' => $extraMinutes,
                        'max_allowed_hours' => $maxAllowedHours,
                        'allow_extra_hours' => false
                    ]);
                }

                // Cap the regular work minutes
                $regularMinutes = $capInMin;
            }
        }

        // Compute total undertime minutes
        $totalUndertime = 0;

        if ($attendance->shift_assignment_id && ! $isFlexible) {
            $shiftAssignment = $attendance->shiftAssignment()->with('shift')->first();
            if ($shiftAssignment && $shiftAssignment->shift && $shiftAssignment->shift->end_time) {
                $scheduledEnd = Carbon::parse(
                    $attendance->attendance_date->toDateString() . ' ' .
                        $shiftAssignment->shift->end_time
                );

                if ($end->lt($scheduledEnd)) {
                    $totalUndertime = $end->diffInMinutes($scheduledEnd);
                } else {
                    Log::info('[Undertime] No undertime: clock-out is on or after scheduled end', [
                        'actualEnd'    => $end->toDateTimeString(),
                        'scheduledEnd' => $scheduledEnd->toDateTimeString(),
                    ]);
                }
            }
        }

        // Update the attendance record
        $attendance->update([
            'date_time_out'             => $end,
            'time_out_photo_path'       => $photoPath,
            'time_out_latitude'         => $latitude,
            'time_out_longitude'        => $longitude,
            'within_geofence'           => $inside,
            'geofence_id'               => $usedFenceId,
            'clock_out_method'          => $request->input('clock_out_method', 'manual_web'),
            'total_work_minutes'        => $regularMinutes,
            'total_night_diff_minutes'  => $nightDiffMinutes,
            'total_undertime_minutes'   => $totalUndertime,
        ]);

        // Now create automatic overtime if needed
        if ($extraMinutes > 0 && $attendance->shift && $attendance->shift->allow_extra_hours) {
            // Refresh attendance to get latest data
            $attendance->refresh();

            $overtimeCreated = $this->createAutomaticOvertime($attendance, $extraMinutes, $user);

            if ($overtimeCreated) {
                Log::info('✅ Automatic overtime created successfully', [
                    'overtime_id' => $overtimeCreated->id,
                    'attendance_id' => $attendance->id
                ]);
            } else {
                Log::error('❌ Failed to create automatic overtime', [
                    'attendance_id' => $attendance->id,
                    'extra_minutes' => $extraMinutes
                ]);
            }
        }

        $message = 'You have successfully clocked out.';
        if ($overtimeCreated) {
            $extraHours = floor($extraMinutes / 60);
            $extraMins = $extraMinutes % 60;
            $extraFormatted = [];
            if ($extraHours > 0) $extraFormatted[] = "{$extraHours} hr";
            if ($extraMins > 0) $extraFormatted[] = "{$extraMins} min";
            $extraText = implode(' ', $extraFormatted);

            $message = "You have successfully clocked out. Extra hours ({$extraText}) have been automatically submitted as overtime and are pending approval.";
        }

        return response()->json([
            'message' => $message,
            'data'    => $attendance->fresh(),
            'overtime_created' => $overtimeCreated ? [
                'overtime_id' => $overtimeCreated->id,
                'total_ot_minutes' => $overtimeCreated->total_ot_minutes,
                'total_ot_formatted' => $overtimeCreated->total_ot_minutes_formatted,
                'status' => $overtimeCreated->status,
                'is_rest_day' => $overtimeCreated->is_rest_day,
                'is_holiday' => $overtimeCreated->is_holiday,
            ] : null,
        ]);
    }

    // Request Attendance Filter
    public function requestAttendanceFilter(Request $request)
    {

        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(15);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $status = $request->input('status');


        $query  = RequestAttendance::where('user_id',  $authUserId);

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

        if ($status) {
            $query->where('status', $status);
        }

        $attendances = $query->orderBy('request_date', 'desc')
            ->with([
                'latestApproval.attendanceApprover.personalInformation',
                'latestApproval.attendanceApprover.employmentDetail.department'
            ])
            ->get();

        foreach ($attendances as $req) {
            if ($latest = $req->latestApproval) {
                $approver = $latest->attendanceApprover;
                if ($approver && $approver->personalInformation) {
                    $pi = $approver->personalInformation;
                    $req->lastApproverName = trim("{$pi->first_name} {$pi->last_name}");
                    $req->lastApproverDept = optional($approver->employmentDetail->department)->department ?? '—';
                } else {
                    $req->lastApproverName = null;
                    $req->lastApproverDept = null;
                }
            } else {
                $req->lastApproverName = null;
                $req->lastApproverDept = null;
            }
        }

        $html = view('tenant.attendance.attendance.employeerequest_filter', compact('attendances', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    /**
     * Display the employee's attendance requests and summary.
     *
     * Shows the authenticated employee's attendance requests for the last 30 days, including summary statistics and approver information.
     *
     * @param \Illuminate\Http\Request $request
     * @queryParam dateRange string Optional. Date range in format "mm/dd/yyyy - mm/dd/yyyy". Example: "11/01/2025 - 11/27/2025"
     * @queryParam status string Optional. Filter requests by status (e.g., "pending", "approved", "rejected").
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Request Attendance Employee Index",
     *   "data": [
     *     {
     *       "id": 1,
     *       "request_date": "2025-11-01",
     *       "request_date_in": "2025-11-01 08:00:00",
     *       "request_date_out": "2025-11-01 17:00:00",
     *       "total_request_minutes": 540,
     *       "total_request_nd_minutes": 60,
     *       "reason": "Forgot to clock in",
     *       "status": "pending",
     *       "lastApproverName": "Juan Dela Cruz",
     *       "lastApproverDept": "HR"
     *     }
     *   ]
     * }
     */
    public function requestAttendanceIndex(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = Auth::guard('global')->check() ? null : ($authUser->id ?? null);
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(15);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $settings = AttendanceSettings::first();
        $today    = Carbon::today()->toDateString();
        $todayDay = strtolower(now()->format('D'));
        $now = Carbon::now();

        $attendances = RequestAttendance::where('user_id', $authUserId)
            ->whereBetween('request_date', [
                now()->subDays(29)->startOfDay(),
                now()->endOfDay()
            ])
            ->orderBy('request_date', 'desc')
            ->with([
                'user.personalInformation',
                'latestApproval.attendanceApprover.personalInformation',
                'latestApproval.attendanceApprover.employmentDetail.department'
            ])
            ->get();

        // Set approver info for each attendance
        foreach ($attendances as $req) {
            if ($latest = $req->latestApproval) {
                $approver = $latest->latestApproval;
                if ($approver && $approver->personalInformation) {
                    $pi = $approver->personalInformation;
                    $req->lastApproverName = trim("{$pi->first_name} {$pi->last_name}");
                    $req->lastApproverDept = optional($approver->employmentDetail->department)->department ?? '—';
                } else {
                    $req->lastApproverName = null;
                    $req->lastApproverDept = null;
                }
            } else {
                $req->lastApproverName = null;
                $req->lastApproverDept = null;
            }
        }

        // Subscription validation
        $subscription = Subscription::where('tenant_id', $authUser->tenant_id)->first();

        $nowDate = now()->startOfDay();
        $trialEnded = $subscription
            && $subscription->status === 'trial'
            && $subscription->trial_end
            && $nowDate->greaterThanOrEqualTo(Carbon::parse($subscription->trial_end)->startOfDay());

        $expired = $subscription && in_array($subscription->status, ['expired', 'inactive', 'cancelled']);

        $subBlocked = $trialEnded || $expired;
        $subBlockMessage = $trialEnded
            ? 'Your 7-day trial period has ended. Please contact your administrator.'
            : ($expired ? 'Your subscription has expired. Please contact your administrator.' : null);


        $latestAttendance = Attendance::where('user_id',  $authUserId)
            ->latest('date_time_in')
            ->first();

        $latest = Attendance::where('user_id',  $authUserId)
            ->where('attendance_date', $today)
            ->whereNotNull('date_time_in')
            ->latest('date_time_in')
            ->first();

        // Calculate total hours for the current week (Monday to Sunday)
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $weeklyAttendances = Attendance::where('user_id',  $authUserId)
            ->whereBetween('attendance_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

        $totalWeeklyMinutes = $weeklyAttendances->sum(function ($attendance) {
            return $attendance->total_work_minutes ?? 0;
        });

        $totalWeeklyHours = round($totalWeeklyMinutes / 60, 2);

        // Calculate total hours for the current month (1st to last day)
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $monthlyAttendances = Attendance::where('user_id',  $authUserId)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $totalMonthlyMinutes = $monthlyAttendances->sum(function ($attendance) {
            return $attendance->total_work_minutes ?? 0;
        });

        $totalMonthlyHours = round($totalMonthlyMinutes / 60, 2);

        // Night Diff For This Month
        $monthlyNightAttendance = Attendance::where('user_id',  $authUserId)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $totalMonthlyNightMinutes = $monthlyNightAttendance->sum(function ($attendance) {
            return $attendance->total_night_diff_minutes ?? 0;
        });

        $totalMonthlyNightHours = round($totalMonthlyNightMinutes / 60, 2);

        // Late Minutes for this month
        $monthlyLateAttendance = Attendance::where('user_id',  $authUserId)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $totalMonthlyLateMinutes = $monthlyLateAttendance->sum(function ($attendance) {
            return $attendance->total_late_minutes ?? 0;
        });

        // Fix: Use $totalMonthlyLateMinutes for formatting, not $totalMonthlyLateHours
        $totalMonthlyLateHours = round($totalMonthlyLateMinutes / 60, 2);

        // Undertime Minutes for this month
        $monthlyUndertimeAttendance = Attendance::where('user_id',  $authUserId)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $totalMonthlyUndertimeMinutes = $monthlyUndertimeAttendance->sum(function ($attendance) {
            return $attendance->total_undertime_minutes ?? 0;
        });

        $totalMonthlyUndertimeHours = round($totalMonthlyUndertimeMinutes / 60, 2);

        // format minutes as "X hr Y min"
        $formatMinutes = function ($minutes) {
            if ($minutes <= 0) {
                return '0 min';
            }
            $hours = intdiv($minutes, 60);
            $mins  = $minutes % 60;
            $parts = [];
            if ($hours > 0) {
                $parts[] = "{$hours} hr";
            }
            if ($mins > 0) {
                $parts[] = "{$mins} min";
            }
            return implode(' ', $parts);
        };

        $totalMonthlyHoursFormatted = $formatMinutes($totalMonthlyMinutes);
        $totalWeeklyHoursFormatted  = $formatMinutes($totalWeeklyMinutes);
        $totalMonthlyNightHoursFormatted = $formatMinutes($totalMonthlyNightMinutes);
        // Fix: Use $totalMonthlyLateMinutes for formatting
        $totalMonthlyLateHoursFormatted = $formatMinutes($totalMonthlyLateMinutes);
        $totalMonthlyUndertimeHoursFormatted = $formatMinutes($totalMonthlyUndertimeMinutes);

        $assignments = ShiftAssignment::with('shift')
            ->where('user_id',  $authUserId)
            ->get()

            // 1️⃣ Date/Day filter (recurring & custom)
            ->filter(function ($assignment) use ($today, $todayDay) {
                // skip excluded dates
                if ($assignment->excluded_dates && in_array($today, $assignment->excluded_dates)) {
                    return false;
                }

                // recurring
                if ($assignment->type === 'recurring') {
                    $start = Carbon::parse($assignment->start_date);
                    $end   = $assignment->end_date
                        ? Carbon::parse($assignment->end_date)
                        : now();
                    return $start->lte($today)
                        && $end->gte($today)
                        && in_array($todayDay, $assignment->days_of_week);
                }

                // custom
                if ($assignment->type === 'custom') {
                    return in_array($today, $assignment->custom_dates ?? []);
                }

                return false;
            })

            // 2️⃣ Time-window filter: drop shifts that have already ended
            ->filter(function ($assignment) use ($today, $now) {
                // ✅ UPDATED: Skip time validation for rest days
                if ($assignment->is_rest_day) {
                    return true;
                }

                $shift = $assignment->shift;

                // ✅ UPDATED: Keep null check for regular shifts only
                if (!$shift) {
                    return false;
                }

                // If flexible, always allow
                if ($shift->is_flexible) {
                    return true;
                }

                if (!$shift->start_time || !$shift->end_time) {
                    return true; // if missing times, skip this filter
                }

                $start = Carbon::parse("{$today} {$shift->start_time}");
                $end = Carbon::parse("{$today} {$shift->end_time}");

                // Handle night shifts that cross midnight
                if ($end->lte($start)) {
                    $end->addDay(); // Move end time to next day
                }

                // keep only if now is before or equal end time
                return $now->lte($end);
            })

            // 3️⃣ Sort by shift start time
            ->sortBy(function ($assignment) {
                // ✅ UPDATED: Prioritize rest days
                if ($assignment->is_rest_day) {
                    return '00:00:00';
                }
                return $assignment->shift ? ($assignment->shift->start_time ?? '99:99:99') : '99:99:99';
            });

        // ✅ UPDATED: Detect rest day and regular assignments
        $restDayAssignment = $assignments->firstWhere('is_rest_day', true);
        $regularAssignment = $assignments->firstWhere('is_rest_day', false);

        // ✅ UPDATED: Set flags
        $isRestDay = $restDayAssignment !== null;

        // ✅ UPDATED: Allow hasShift to be true if rest day is allowed
        $hasShift = $regularAssignment !== null || ($restDayAssignment && $settings->rest_day_time_in_allowed);

        // ✅ UPDATED: Get next assignment (prioritize rest day if allowed)
        $nextAssignment = $assignments->first(function ($assignment) use ($authUser, $today) {
            // ✅ Allow rest day assignments
            if ($assignment->is_rest_day) {
                return true; // Always return rest day if it exists
            }

            // ✅ For regular shifts, check if shift exists before checking attendance
            if (!$assignment->shift) {
                return false;
            }

            return !Attendance::where('user_id', $authUser->id)
                ->where('shift_id', $assignment->shift_id)
                ->where('shift_assignment_id', $assignment->id)
                ->where('attendance_date', $today)
                ->exists();
        });

        $currentActiveAssignment = null;
        if (!$nextAssignment) {
            // If no next assignment, check if user is currently clocked in to any shift
            $currentAttendance = Attendance::where('user_id', $authUser->id)
                ->where(function ($query) use ($today) {
                    $yesterday = Carbon::yesterday()->toDateString();
                    // Include today's attendance OR yesterday's attendance (for night shifts)
                    $query->where('attendance_date', $today)
                        ->orWhere('attendance_date', $yesterday);
                })
                ->whereNotNull('date_time_in')
                ->whereNull('date_time_out') // Currently clocked in
                ->latest('date_time_in')
                ->first();

            if ($currentAttendance && $currentAttendance->shiftAssignment) {
                $currentActiveAssignment = $currentAttendance->shiftAssignment;
            }
        }

        $assignmentForBreakManagement = $nextAssignment ?? $currentActiveAssignment;

        // ✅ UPDATED: Grace Period getter - safe access (return 0 for rest days)
        $gracePeriod = ($assignmentForBreakManagement && !$assignmentForBreakManagement->is_rest_day && $assignmentForBreakManagement->shift)
            ? ($assignmentForBreakManagement->shift->grace_period ?? 0)
            : 0;

        // ✅ UPDATED: Check if its Flexible Shift - treat rest days as flexible
        $isFlexible = $assignmentForBreakManagement
            ? ($assignmentForBreakManagement->is_rest_day || ($assignmentForBreakManagement->shift && $assignmentForBreakManagement->shift->is_flexible))
            : false;

        // API response
        if ($request->wantsJson()) {
            return response()->json([
                'status'    => true,
                'message'   => 'Request Attendance Employee Index',
                'data'      => $attendances,
            ]);
        }

        // Web response
        return view(
            'tenant.attendance.attendance.employeerequest',
            [
                'attendances' => $attendances,
                'latest' => $latestAttendance,
                'settings' => $settings,
                'nextAssignment'  => $nextAssignment,
                'latest' => $latest,
                'hasShift' => $hasShift,
                'totalWeeklyHours' => $totalWeeklyHours,
                'totalMonthlyHours' => $totalMonthlyHours,
                'totalMonthlyHoursFormatted' => $totalMonthlyHoursFormatted,
                'totalWeeklyHoursFormatted' => $totalWeeklyHoursFormatted,
                'totalMonthlyNightHours' => $totalMonthlyNightHours,
                'totalMonthlyNightHoursFormatted' => $totalMonthlyNightHoursFormatted,
                'totalMonthlyLateHoursFormatted' => $totalMonthlyLateHoursFormatted,
                'totalMonthlyUndertimeHoursFormatted' => $totalMonthlyUndertimeHoursFormatted,
                'permission' => $permission,
                'gracePeriod' => $gracePeriod,
                'isFlexible' => $isFlexible,
                'accessData' => $accessData,
                'currentActiveAssignment' => $currentActiveAssignment,
                'assignmentForBreakManagement' => $assignmentForBreakManagement,
                'isRestDay' => $isRestDay,
                'subBlocked' => $subBlocked,
                'subBlockMessage' => $subBlockMessage,
            ]
        );
    }

    // Request Attendance Employee Notification
    public function sendRequestAttendanceNotificationToApprover($authUser, $request_date)
    {
        $employment = $authUser->employmentDetail;
        $reportingToId = optional($employment)->reporting_to;
        $branchId = optional($employment)->branch_id;

        $requestor = trim(optional($authUser->personalInformation)->first_name . ' ' .
            optional($authUser->personalInformation)->last_name);

        $notifiedUser = null;
        if ($reportingToId) {
            $notifiedUser = User::find($reportingToId);
        } else {
            $steps = RequestAttendanceApproval::stepsForBranch($branchId);
            $firstStep = $steps->first();

            if ($firstStep) {

                if ($firstStep->approver_kind === 'department_head') {
                    $departmentHeadId = optional(optional($employment)->department)->head_of_department;

                    if ($departmentHeadId) {
                        $notifiedUser = User::find($departmentHeadId);
                    }
                } elseif ($firstStep->approver_kind === 'user') {
                    $approverUserId = $firstStep->approver_user_id;

                    if ($approverUserId) {
                        $notifiedUser = User::find($approverUserId);
                    }
                }
            }
        }
        if ($notifiedUser) {
            $message = "New attendance request from {$requestor}: {$request_date}. Pending your approval.";
            $notifiedUser->notify(new UserNotification($message));
        }
    }

    /**
     * Submit a new attendance request.
     *
     * Allows an employee to submit an attendance request for a specific date, including clock-in/out times, break minutes, night differential, reason, and optional file attachment.
     *
     * @param \Illuminate\Http\Request $request
     * @bodyParam request_date date required The date for the attendance request. Example: "2025-11-01"
     * @bodyParam request_date_in datetime required Clock-in date and time. Example: "2025-11-01 08:00:00"
     * @bodyParam request_date_out datetime required Clock-out date and time. Must be after clock-in. Example: "2025-11-01 17:00:00"
     * @bodyParam total_break_minutes integer Optional. Total break minutes. Example: 60
     * @bodyParam total_request_minutes integer required Total work minutes for this request. Example: 480
     * @bodyParam total_request_nd_minutes integer Optional. Night differential minutes. Example: 60
     * @bodyParam reason string Optional. Reason for the attendance request. Example: "Forgot to clock in"
     * @bodyParam file_attachment file Optional. Supporting document or file (max 2MB).
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Attendance request submitted successfully.",
     *   "data": {
     *     "id": 1,
     *     "request_date": "2025-11-01",
     *     "request_date_in": "2025-11-01 08:00:00",
     *     "request_date_out": "2025-11-01 17:00:00",
     *     "total_break_minutes": 60,
     *     "total_request_minutes": 480,
     *     "total_request_nd_minutes": 60,
     *     "reason": "Forgot to clock in",
     *     "file_attachment": "attendance_attachments/xyz.pdf"
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Global administrators are not authorized to submit attendance requests."
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Invalid user session. Please log in again."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Please select a date for your attendance request."
     * }
     */
    public function requestAttendance(Request $request)
    {

        $authUser = $this->authUser();
        // ✅ Block Global admin / global guard users
        if (Auth::guard('global')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Global administrators are not authorized to submit attendance requests.'
            ], 403);
        }

        // ✅ Ensure we have a valid tenant user
        $user = Auth::user();
        if (!$user || !$user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user session. Please log in again.'
            ], 401);
        }

        $input = $request->all();

        // Validation rules
        $rules = [
            'request_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $requestDate = Carbon::parse($value)->startOfDay();
                    $today = Carbon::today();

                    if ($requestDate->greaterThan($today)) {
                        $fail('Sorry, you cannot request attendance for future dates. Please select today\'s date or any previous date.');
                    }
                }
            ],
            'request_date_in' => 'required|date',
            'request_date_out' => 'required|date|after:request_date_in',
            'total_break_minutes' => 'nullable|integer|min:0',
            'total_request_minutes' => 'required|integer|min:0',
            'total_request_nd_minutes' => 'nullable|integer|min:0',
            'reason' => 'nullable|string|max:255',
            'file_attachment' => 'nullable|file|max:2048',
        ];

        // Custom validation messages
        $messages = [
            'request_date.required' => 'Please select a date for your attendance request.',
            'request_date.date' => 'Please enter a valid date.',
            'request_date_in.required' => 'Please provide your clock-in date and time.',
            'request_date_in.date' => 'Please enter a valid clock-in date and time.',
            'request_date_out.required' => 'Please provide your clock-out date and time.',
            'request_date_out.date' => 'Please enter a valid clock-out date and time.',
            'request_date_out.after' => 'Clock-out time must be after clock-in time.',
            'total_break_minutes.integer' => 'Break minutes must be a valid number.',
            'total_break_minutes.min' => 'Break minutes cannot be negative.',
            'total_request_minutes.required' => 'Please specify the total work minutes for this request.',
            'total_request_minutes.integer' => 'Total work minutes must be a valid number.',
            'total_request_minutes.min' => 'Total work minutes cannot be negative.',
            'total_request_nd_minutes.integer' => 'Night differential minutes must be a valid number.',
            'total_request_nd_minutes.min' => 'Night differential minutes cannot be negative.',
            'reason.string' => 'Please provide a valid reason for your request.',
            'reason.max' => 'Reason cannot exceed 255 characters. Please provide a shorter explanation.',
            'file_attachment.file' => 'Please upload a valid file.',
            'file_attachment.max' => 'File size cannot exceed 2MB. Please upload a smaller file.',
        ];

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        // File attachment handling (for API/multipart)
        $attachmentPath = null;
        if ($request->hasFile('file_attachment')) {
            $attachmentPath = $request->file('file_attachment')->store('attendance_attachments', 'public');
        }

        // Store to database
        $attendance = new RequestAttendance();
        $attendance->user_id = $user->id; // ✅ Use validated user ID
        $attendance->request_date = $input['request_date'];
        $attendance->request_date_in = $input['request_date_in'];
        $attendance->request_date_out = $input['request_date_out'];
        $attendance->total_break_minutes = $input['total_break_minutes'] ?? 0;
        $attendance->total_request_minutes = $input['total_request_minutes'];
        $attendance->total_request_nd_minutes = $input['total_request_nd_minutes'] ?? 0;
        $attendance->reason = $input['reason'] ?? null;
        $attendance->file_attachment = $attachmentPath;
        $attendance->save();

        $this->sendRequestAttendanceNotificationToApprover($authUser, $input['request_date']);


        // ✅ Optional: Log the action
        UserLog::create([
            'user_id'        => $user->id,
            'global_user_id' => null,
            'module'         => 'Attendance Request',
            'action'         => 'Create',
            'description'    => 'Created attendance request for date "' . $attendance->request_date . '"',
            'affected_id'    => $attendance->id,
            'old_data'       => null,
            'new_data'       => json_encode($attendance->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance request submitted successfully.',
            'data'    => $attendance
        ], 201);
    }

    /**
     * Edit an existing attendance request.
     *
     * Allows an employee to update an existing attendance request, including clock-in/out times, break minutes, night differential, reason, and optional file attachment. Approved requests cannot be edited.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id The ID of the attendance request to edit.
     * @bodyParam request_date date required The date for the attendance request. Example: "2025-11-01"
     * @bodyParam request_date_in datetime required Clock-in date and time. Example: "2025-11-01 08:00:00"
     * @bodyParam request_date_out datetime required Clock-out date and time. Must be after clock-in. Example: "2025-11-01 17:00:00"
     * @bodyParam total_break_minutes integer Optional. Total break minutes. Example: 60
     * @bodyParam total_request_minutes integer required Total work minutes for this request. Example: 480
     * @bodyParam total_request_nd_minutes integer Optional. Night differential minutes. Example: 60
     * @bodyParam reason string Optional. Reason for the attendance request. Example: "Forgot to clock in"
     * @bodyParam file_attachment file Optional. Supporting document or file (max 2MB).
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Attendance request updated successfully.",
     *   "data": {
     *     "id": 1,
     *     "request_date": "2025-11-01",
     *     "request_date_in": "2025-11-01 08:00:00",
     *     "request_date_out": "2025-11-01 17:00:00",
     *     "total_break_minutes": 60,
     *     "total_request_minutes": 480,
     *     "total_request_nd_minutes": 60,
     *     "reason": "Forgot to clock in",
     *     "file_attachment": "attendance_attachments/xyz.pdf"
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Approved attendance requests cannot be edited."
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error message."
     * }
     */
    public function requestAttendanceEdit(Request $request, $id)
    {
        $attendance = RequestAttendance::findOrFail($id);

        // Prevent editing if status is approved
        if ($attendance->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Approved attendance requests cannot be edited.'
            ], 403);
        }

        $rules = [
            'request_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $requestDate = Carbon::parse($value)->startOfDay();
                    $today = Carbon::today();

                    if ($requestDate->greaterThan($today)) {
                        $fail('Sorry, you cannot request attendance for future dates. Please select today\'s date or any previous date.');
                    }
                }
            ],
            'request_date_in'          => 'required|date',
            'request_date_out'         => 'required|date|after:request_date_in',
            'total_break_minutes'      => 'nullable|integer|min:0',
            'total_request_minutes'    => 'required|integer|min:0',
            'total_request_nd_minutes' => 'nullable|integer|min:0',
            'reason'                   => 'nullable|string',
            'file_attachment'          => 'nullable|file|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        // Save old data for logging
        $oldData = $attendance->toArray();

        // Handle file update (replace old file if new one uploaded)
        if ($request->hasFile('file_attachment')) {
            // Delete old file if exists
            if ($attendance->file_attachment) {
                Storage::disk('public')->delete($attendance->file_attachment);
            }
            $attachmentPath = $request->file('file_attachment')->store('attendance_attachments', 'public');
            $attendance->file_attachment = $attachmentPath;
        }

        $attendance->request_date             = $request->input('request_date');
        $attendance->request_date_in          = $request->input('request_date_in');
        $attendance->request_date_out         = $request->input('request_date_out');
        $attendance->total_break_minutes      = $request->input('total_break_minutes', 0);
        $attendance->total_request_minutes    = $request->input('total_request_minutes');
        $attendance->total_request_nd_minutes = $request->input('total_request_nd_minutes') !== null ? $request->input('total_request_nd_minutes') : 0;
        $attendance->reason                   = $request->input('reason');
        $attendance->save();

        // Prepare user log info
        $userId       = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'        => $userId,
            'global_user_id' => $globalUserId,
            'module'         => 'Attendance Request',
            'action'         => 'Update',
            'description'    => 'Updated attendance request with ID "' . $attendance->id . '"',
            'affected_id'    => $attendance->id,
            'old_data'       => json_encode($oldData),
            'new_data'       => json_encode($attendance->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance request updated successfully.',
            'data'    => $attendance
        ], 200);
    }

    /**
     * Delete an attendance request.
     *
     * Allows an employee to delete an existing attendance request. Approved requests cannot be deleted. Deletes any attached file as well.
     *
     * @param int $id The ID of the attendance request to delete.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Attendance request deleted successfully."
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Approved attendance requests cannot be deleted."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Attendance request not found."
     * }
     */
    public function requestAttendanceDelete($id)
    {
        $attendance = RequestAttendance::findOrFail($id);

        // Prevent deletion if status is approved
        if ($attendance->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Approved attendance requests cannot be deleted.'
            ], 403);
        }

        // Delete Existing File Attachment
        if ($attendance->file_attachment) {
            Storage::disk('public')->delete($attendance->file_attachment);
        }

        // Prepare user log info
        $userId       = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        // Save old data for logging
        $oldData = $attendance->toArray();

        // Delete the attendance request
        $attendance->delete();

        // UserLog for deletion
        UserLog::create([
            'user_id'        => $userId,
            'global_user_id' => $globalUserId,
            'module'         => 'Attendance Request',
            'action'         => 'Delete',
            'description'    => 'Deleted attendance request with ID "' . $id . '"',
            'affected_id'    => $id,
            'old_data'       => json_encode($oldData),
            'new_data'       => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance request deleted successfully.'
        ], 200);
    }

    /**
     * Check if the current time is within the shift's working hours.
     * This handles both regular shifts and night shifts that cross midnight.
     *
     * @param ShiftList $shift
     * @param Carbon $currentTime
     * @return bool
     */
    private function isWithinShiftHours($shift, $currentTime = null)
    {
        if (!$shift || !$shift->start_time || !$shift->end_time) {
            return false;
        }

        $now = $currentTime ?: Carbon::now();
        $startTime = Carbon::parse($shift->start_time);
        $endTime = Carbon::parse($shift->end_time);

        // Handle night shifts that cross midnight (e.g., 22:00 to 06:00)
        if ($endTime->lt($startTime)) {
            // Night shift - check if current time is after start time today OR before end time today
            return $now->gte($startTime) || $now->lte($endTime);
        }
        
        // Regular day shift - check if current time is between start and end
        return $now->gte($startTime) && $now->lte($endTime);
    }

    /**
     * Find current active attendance based on shift timing rather than just date.
     * This provides more accurate results for night shifts that cross midnight.
     *
     * @param int $userId
     * @param int|null $shiftId
     * @return Attendance|null
     */
    private function findActiveAttendanceByShift($userId, $shiftId = null)
    {
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        $now = Carbon::now();

        $query = Attendance::where('user_id', $userId)
            ->whereNotNull('date_time_in')
            ->whereNull('date_time_out');

        if ($shiftId) {
            // If specific shift ID provided, look for that shift
            $query->where('shift_id', $shiftId);
        }

        // Look in both today's and yesterday's records for night shifts
        $query->where(function ($subQuery) use ($today, $yesterday) {
            $subQuery->where('attendance_date', $today)
                ->orWhere('attendance_date', $yesterday);
        });

        $attendances = $query->with('shift')->get();

        // If no specific shift ID provided, find the attendance where current time is within shift hours
        if (!$shiftId && $attendances->count() > 1) {
            foreach ($attendances as $attendance) {
                if ($this->isWithinShiftHours($attendance->shift, $now)) {
                    return $attendance;
                }
            }
        }

        // Return the latest attendance if found
        return $attendances->sortByDesc('date_time_in')->first();
    }
}
