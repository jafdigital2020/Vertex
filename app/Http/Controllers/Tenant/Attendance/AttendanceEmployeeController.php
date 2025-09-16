<?php

namespace App\Http\Controllers\Tenant\Attendance;

use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\UserLog;
use App\Models\Geofence;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;

class AttendanceEmployeeController extends Controller
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
        $authUserId = $authUser->id;
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(15);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $status = $request->input('status');


        $query  = Attendance::where('user_id', $authUserId);


        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('attendance_date', [$start, $end]);
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

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->get();

        $html = view('tenant.attendance.attendance.employeeattendance_filter', compact('attendances', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function employeeAttendanceIndex(Request $request)
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

        $attendances = Attendance::where('user_id',  $authUserId)
            ->where('attendance_date', Carbon::today()->toDateString())
            ->orderBy('attendance_date', 'desc')
            ->get();

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
        $totalMonthlyLateHoursFormatted = $formatMinutes($totalMonthlyLateMinutes);
        $totalMonthlyUndertimeHoursFormatted = $formatMinutes($totalMonthlyUndertimeMinutes);


        $assignments = ShiftAssignment::with('shift')
            ->where('user_id', $authUserId)
            ->get()

            // 1️⃣ Date/Day filter (recurring & custom)
            ->filter(function ($assignment) use ($today, $todayDay) {
                // ✅ FIX: Check if shift exists before proceeding
                if (!$assignment->shift) {
                    Log::warning('ShiftAssignment has no related shift', [
                        'assignment_id' => $assignment->id,
                        'user_id' => $assignment->user_id,
                        'shift_id' => $assignment->shift_id
                    ]);
                    return false; // Skip assignments with missing shifts
                }

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
                $shift = $assignment->shift;

                // ✅ FIX: Additional safety check for shift existence
                if (!$shift) {
                    return false; // Skip if shift doesn't exist
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
                // ✅ FIX: Safe access to shift properties
                return $assignment->shift ? ($assignment->shift->start_time ?? '00:00:00') : '99:99:99';
            });

        $hasShift = $assignments->isNotEmpty();

        // Check if today is a rest day
        $isRestDay = false;
        $restDayAssignment = $assignments->firstWhere('is_rest_day', true);
        if ($restDayAssignment) {
            $isRestDay = true;
        }

        $nextAssignment = $assignments->first(function ($assignment) use ($authUser, $today) {
            // ✅ FIX: Check if shift exists before checking attendance
            if (!$assignment->shift) {
                return false; // Skip assignments with missing shifts
            }

            return !Attendance::where('user_id', $authUser->id)
                ->where('shift_id', $assignment->shift_id)
                ->where('shift_assignment_id', $assignment->id)
                ->where('attendance_date', $today)
                ->exists();
        });

        // Grace Period getter - safe access
        $gracePeriod = $nextAssignment && $nextAssignment->shift
            ? ($nextAssignment->shift->grace_period ?? 0)
            : 0;

        // Check if its Flexible Shift - safe access
        $isFlexible = $nextAssignment && $nextAssignment->shift
            ? ($nextAssignment->shift->is_flexible ?? false)
            : false;

        // API response
        if ($request->wantsJson()) {
            return response()->json([
                'status'    => true,
                'message'   => 'Attendance Employee Index',
                'data'      => $attendances,
                'latest' => $latestAttendance,
                'settings' => $settings,
                'nextAssignment'  => $nextAssignment,
                'latest' => $latest,
                'hasShift' => $hasShift,
                'permission' => $permission,
                'gracePeriod' => $gracePeriod,
                'isFlexible' => $isFlexible,
                'isRestDay' => $isRestDay,
                'subscription' => $subscription,
                'subBlocked' => $subBlocked,
                'subBlockMessage' => $subBlockMessage,
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
            ]
        );
    }

    // Clock IN
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
            return response()->json([
                'status' => 'error',
                'message' => 'Your 7-day trial period has ended. Please contact your administrator.'
            ], 403);
        }

        if (
            $subscription &&
            $subscription->status === 'expired'
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your subscription has expired. Please contact your administrator.'
            ], 403);
        }

        // 1. Get all shift assignments for today
        $assignments = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->get()

            // 1️⃣ Date/Day filter (recurring & custom)
            ->filter(function ($assignment) use ($today, $todayDay) {
                // ✅ FIX: Check if shift exists before proceeding
                if (!$assignment->shift) {
                    Log::warning('ShiftAssignment has no related shift during clock-in', [
                        'assignment_id' => $assignment->id,
                        'user_id' => $assignment->user_id,
                        'shift_id' => $assignment->shift_id
                    ]);
                    return false; // Skip assignments with missing shifts
                }

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
                $shift = $assignment->shift;

                // ✅ FIX: Additional safety check for shift existence
                if (!$shift) {
                    return false; // Skip if shift doesn't exist
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

            //  Sort by shift start time (flexible shifts will have null, so sort last)
            ->sortBy(function ($assignment) {
                // ✅ FIX: Safe access to shift properties
                return $assignment->shift ? ($assignment->shift->start_time ?? '99:99:99') : '99:99:99';
            });

        if ($assignments->isEmpty()) {
            return response()->json(['message' => 'No active shift today.'], 403);
        }

        // Check if it's a rest day today (even if shift matches)
        $restDayAssignment = $assignments->firstWhere('is_rest_day', true);
        if ($restDayAssignment) {
            return response()->json(['message' => 'Today is marked as a rest day.'], 403);
        }

        // Check existing attendance for today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('attendance_date', $today)
            ->latest('date_time_in')
            ->first();

        if ($existingAttendance && !$existingAttendance->date_time_out) {
            return response()->json(['message' => 'You already clocked in your current shift and haven\'t clocked out.'], 403);
        }

        // Find the next available shift assignment
        $nextAssignment = $assignments->first(function ($assignment) use ($user, $today) {
            $alreadyClocked = Attendance::where('user_id', $user->id)
                ->where('shift_id', $assignment->shift_id)
                ->where('shift_assignment_id', $assignment->id)
                ->where('attendance_date', $today)
                ->exists();

            return !$alreadyClocked;
        });

        // ADDITION: Check if already time in for this shift
        if ($nextAssignment) {
            $alreadyTimeIn = Attendance::where('user_id', $user->id)
                ->where('shift_id', $nextAssignment->shift_id)
                ->where('shift_assignment_id', $nextAssignment->id)
                ->where('attendance_date', $today)
                ->exists();

            if ($alreadyTimeIn) {
                return response()->json(['message' => 'You have already time in for this shift.'], 403);
            }
        }

        if (!$nextAssignment) {
            return response()->json(['message' => 'All shifts already clocked in today.'], 403);
        }

        $isFlexible = $nextAssignment->shift && $nextAssignment->shift->is_flexible;

        // Grace Period & Late Computation
        $graceMin    = $isFlexible ? 0 : ($nextAssignment->shift->grace_period ?? 0);
        $shiftStart  = $isFlexible ? null : Carbon::parse("{$today} {$nextAssignment->shift->start_time}");
        $lateMinutes = 0;

        if (! $isFlexible && $shiftStart && $now->greaterThan($shiftStart)) {
            $lateMinutes = $shiftStart->diffInMinutes($now);
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
            if (! $request->filled('late_status_reason')) {
                return response()->json([
                    'message' => 'Please provide a reason for being late.'
                ], 422);
            }
            $lateReason = $request->input('late_status_reason');
        }

        // Require Photo
        $photoPath = null;
        if ($settings->require_photo_capture) {
            if (! $request->hasFile('time_in_photo')) {
                return response()->json([
                    'message' => 'Photo is required before clock-in.'
                ], 422);
            }
            $photoPath = $request
                ->file('time_in_photo')
                ->store('attendance_photos', 'public');
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
            'user_id'             => $user->id,
            'shift_id'            => $nextAssignment->shift_id,
            'shift_assignment_id' => $nextAssignment->id,
            'geofence_id'         => $usedFenceId,
            'attendance_date'     => $today,
            'date_time_in'        => $now,
            'status'              => $status,
            'total_late_minutes'  => $totalLateMinutes,
            'clock_in_method'     => $device,
            'time_in_photo_path'  => $photoPath,
            'time_in_latitude'    => $latitude,
            'time_in_longitude'   => $longitude,
            'late_status_box'     => $lateReason,
            'is_holiday'          => $isHoliday,
            'holiday_id'          => $holidayId,
        ]);

        // Shift Name
        $shiftName = $nextAssignment->shift->name;

        $message = $attendance->is_holiday
            ? "Holiday Clock-In successful for “{$shiftName}”"
            : "Clock-In successful for “{$shiftName}”";

        return response()->json([
            'message' => $message,
            'data'    => $attendance,
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

    // Clock OUT
    public function employeeAttendanceClockOut(Request $request)
    {
        // 1️⃣ Validate input
        $request->validate([
            'shift_id'           => 'nullable|integer',
            'time_out_photo'     => 'required_if:require_photo_capture,1|file|image',
            'time_out_latitude'  => 'required_if:geotagging_enabled,1|nullable',
            'time_out_longitude' => 'required_if:geotagging_enabled,1|nullable',
        ]);

        $user     = Auth::user();
        $shiftId  = $request->input('shift_id');
        $settings = AttendanceSettings::first();
        $today    = Carbon::today()->toDateString();
        $now      = Carbon::now();

        // 2️⃣ Find the matching clock-in record
        $attendanceQuery = Attendance::where('user_id', $user->id)
            ->whereNotNull('date_time_in')
            ->whereNull('date_time_out');

        // If shift_id is provided, use it; otherwise find the latest unclosed attendance
        if ($shiftId) {
            $attendanceQuery->where('shift_id', $shiftId);
        }

        $attendance = $attendanceQuery->latest('date_time_in')->first();

        if (! $attendance) {
            return response()->json([
                'message' => 'We could not find a clock-in record for this shift. Please make sure you have clocked in before trying to clock out.'
            ], 403);
        }

        // Log if shift is flexible or not
        $isFlexible = $attendance->shift && $attendance->shift->is_flexible;
        Log::info('[ClockOut] Shift flexibility check', [
            'attendance_id' => $attendance->id,
            'is_flexible'   => $isFlexible,
        ]);

        // 2.5️⃣ Security Check: Don't allow clock-out if next shift is already ongoing
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

        // Log next shift information
        Log::info('[ClockOut] Next shift check', [
            'user_id' => $user->id,
            'current_attendance_id' => $attendance->id,
            'next_shift_assignments_count' => $nextShiftAssignments->count(),
            'next_shift_assignments' => $nextShiftAssignments->map(function ($assignment) {
                return [
                    'assignment_id' => $assignment->id,
                    'shift_id' => $assignment->shift_id,
                    'shift_name' => $assignment->shift->name ?? 'N/A',
                    'shift_start_time' => $assignment->shift->start_time ?? 'N/A',
                    'is_flexible' => $assignment->shift->is_flexible ?? false,
                    'grace_period' => $assignment->shift->grace_period ?? 0
                ];
            })->toArray()
        ]);

        if ($nextShiftAssignments->isNotEmpty()) {
            $nextShift = $nextShiftAssignments->first();
            $nextShiftName = $nextShift->shift ? ($nextShift->shift->name ?? 'Next Shift') : 'Next Shift';
            $nextShiftStart = $nextShift->shift ? ($nextShift->shift->start_time ?? '') : '';

            Log::warning('[ClockOut] Blocked due to ongoing next shift', [
                'user_id' => $user->id,
                'current_attendance_id' => $attendance->id,
                'next_shift_name' => $nextShiftName,
                'next_shift_start' => $nextShiftStart,
                'next_shift_assignment_id' => $nextShift->id
            ]);

            return response()->json([
                'message' => "Cannot clock out. Your next shift \"{$nextShiftName}\" (starts at {$nextShiftStart}) is already ongoing and you haven't clocked in yet. Please clock in to your next shift first or contact your adminstrator."
            ], 403);
        }


        // 3️⃣ Photo capture (if required)
        $photoPath = null;
        if ($settings->require_photo_capture) {
            if (! $request->hasFile('time_out_photo')) {
                return response()->json([
                    'message' => 'A photo is required to complete your clock-out. Please upload a photo and try again.'
                ], 422);
            }
            $photoPath = $request
                ->file('time_out_photo')
                ->store('attendance_photos', 'public');
        }

        // 4️⃣ Geotag inputs & validation (if enabled)
        $latitude  = null;
        $longitude = null;
        $accuracy  = 0;
        if ($settings->geotagging_enabled || $settings->geofencing_enabled) {
            $latitude  = $request->input('time_out_latitude');
            $longitude = $request->input('time_out_longitude');
            $accuracy  = (float) $request->input('time_out_accuracy', 0);

            if (! $latitude || ! $longitude) {
                return response()->json([
                    'message' => 'Your location is required to clock out. Please enable location services and try again.'
                ], 422);
            }
        }

        // 5️⃣ Geofence enforcement (if enabled)
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
                    'message' => 'There is no active permitted area set for you. Please contact your administrator.'
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
                        'message' => 'Your location is required to clock out. Please enable location services and try again.'
                    ], 422);
                }
                if ($settings->geofence_allowed_geotagging) {
                    $attempts = Cache::get($cacheKey, 0) + 1;
                    Cache::put($cacheKey, $attempts, now()->addMinutes(10));

                    if ($attempts < 2) {
                        return response()->json([
                            'message' => "We could not confirm your location. Please try again. Attempts left: " . (2 - $attempts)
                        ], 403);
                    }
                    Cache::forget($cacheKey);
                } else {
                    return response()->json([
                        'message'          => 'You are currently outside the allowed area for clocking out. Please move closer to the permitted area and try again.',
                        'distance'         => round($dist, 2),
                        'effective_radius' => round($effective, 2),
                    ], 403);
                }
            }
        }

        $start = $attendance->date_time_in; // e.g. 2025-06-04 21:00:00
        $end   = $now;                       // e.g. 2025-06-05 06:00:00

        // 6️⃣ Calculate night differential and regular work minutes
        // Night diff runs from 10:00 PM to 6:00 AM next day only
        $nightDiffMinutes = 0;
        $totalWorkedMinutes = $start->diffInMinutes($end);

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

        if ($maxAllowedHours) {
            $capInMin = $maxAllowedHours * 60;
            if ($regularMinutes > $capInMin) {
                $regularMinutes = $capInMin;
            }
        }

        // 7️⃣ Compute total undertime minutes
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

                    Log::info('[Undertime] Under time detected', [
                        'actualEnd'         => $end->toDateTimeString(),
                        'scheduledEnd'      => $scheduledEnd->toDateTimeString(),
                        'totalUndertimeMin' => $totalUndertime,
                    ]);
                } else {
                    Log::info('[Undertime] No undertime: clock-out is on or after scheduled end', [
                        'actualEnd'    => $end->toDateTimeString(),
                        'scheduledEnd' => $scheduledEnd->toDateTimeString(),
                    ]);
                }
            }
        }

        // 8️⃣ Update the attendance record
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

        return response()->json([
            'message' => 'You have successfully clocked out.',
            'data'    => $attendance->fresh(),
        ]);
    }

    // Request Attendance
    public function requestAttendance(Request $request)
    {
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
        $attendance->user_id = Auth::id();
        $attendance->request_date = $input['request_date'];
        $attendance->request_date_in = $input['request_date_in'];
        $attendance->request_date_out = $input['request_date_out'];
        $attendance->total_break_minutes = $input['total_break_minutes'] ?? 0;
        $attendance->total_request_minutes = $input['total_request_minutes'];
        $attendance->total_request_nd_minutes = $input['total_request_nd_minutes'] ?? 0;
        $attendance->reason = $input['reason'] ?? null;
        $attendance->file_attachment = $attachmentPath;
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Attendance request submitted successfully.',
            'data'    => $attendance
        ], 201);
    }

    // Request Attendance Index
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
            ->get();

        $html = view('tenant.attendance.attendance.employeerequest_filter', compact('attendances', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

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
            ->get();

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
                $shift = $assignment->shift;
                if (! $shift->start_time || ! $shift->end_time) {
                    return true; // if missing times, skip this filter
                }
                $start = Carbon::parse("{$today} {$shift->start_time}");
                $end   = Carbon::parse("{$today} {$shift->end_time}");
                // keep only if now is before or equal end time
                return $now->lte($end);
            })

            // 3️⃣ Sort by shift start time
            ->sortBy(fn($a) => $a->shift->start_time ?? '00:00:00');

        $hasShift = $assignments->isNotEmpty();

        $nextAssignment = $assignments->first(function ($assignment) use ($authUser, $today) {
            return ! Attendance::where('user_id', $authUser->id)
                ->where('shift_id', $assignment->shift_id)
                ->where('shift_assignment_id', $assignment->id)
                ->where('attendance_date', $today)
                ->exists();
        });

        // API response
        if ($request->wantsJson()) {
            return response()->json([
                'status'    => true,
                'message'   => 'Attendance Employee Index',
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
                'permission' => $permission
            ]
        );
    }

    // Request Attendance (Edit/Update)
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
            'request_date'             => 'required|date',
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

    // Request Attendance (Delete)
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
}
