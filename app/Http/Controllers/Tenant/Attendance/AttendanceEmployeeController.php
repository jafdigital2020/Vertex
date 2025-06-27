<?php

namespace App\Http\Controllers\Tenant\Attendance;

use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\Geofence;
use App\Models\Attendance;
use Jenssegers\Agent\Agent;
use App\Models\GeofenceUser;
use Illuminate\Http\Request;
use App\Models\ShiftAssignment;
use App\Models\HolidayException;
use App\Helpers\PermissionHelper;
use App\Models\AttendanceSettings;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\DataAccessController;

class AttendanceEmployeeController extends Controller
{
    public function authUser() {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    }

      public function filter(Request $request){

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

        if($status){
            $query->where('status', $status);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->get();

        $html = view('tenant.attendance.attendance.employeeattendance_filter', compact('attendances','permission'))->render();
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

        $attendances = Attendance::where('user_id', $authUserId)
            ->orderBy('attendance_date', 'desc')
            ->get();

        $latestAttendance = Attendance::where('user_id',  $authUserId)
            ->latest('date_time_in')
            ->first();

        $latest = Attendance::where('user_id', $authUserId)
            ->where('attendance_date', $today)
            ->whereNotNull('date_time_in')
            ->latest('date_time_in')
            ->first();

        // Calculate total hours for the current week (Monday to Sunday)
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $weeklyAttendances = Attendance::where('user_id', $authUser && $authUser->id ? $authUser->id : null)
            ->whereBetween('attendance_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

        $totalWeeklyMinutes = $weeklyAttendances->sum(function ($attendance) {
            return $attendance->total_work_minutes ?? 0;
        });

        $totalWeeklyHours = round($totalWeeklyMinutes / 60, 2);

        // Calculate total hours for the current month (1st to last day)
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $monthlyAttendances = Attendance::where('user_id', $authUser && $authUser->id ? $authUser->id : null)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $totalMonthlyMinutes = $monthlyAttendances->sum(function ($attendance) {
            return $attendance->total_work_minutes ?? 0;
        });

        $totalMonthlyHours = round($totalMonthlyMinutes / 60, 2);

        // Night Diff For This Month
        $monthlyNightAttendance = Attendance::where('user_id', $authUser && $authUser->id ? $authUser->id : null)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $totalMonthlyNightMinutes = $monthlyNightAttendance->sum(function ($attendance) {
            return $attendance->total_night_diff_minutes ?? 0;
        });

        $totalMonthlyNightHours = round($totalMonthlyNightMinutes / 60, 2);

        // Late Minutes for this month
        $monthlyLateAttendance = Attendance::where('user_id', $authUser && $authUser->id ? $authUser->id : null)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $totalMonthlyLateMinutes = $monthlyLateAttendance->sum(function ($attendance) {
            return $attendance->total_late_minutes ?? 0;
        });

        $totalMonthlyLateHours = round($totalMonthlyLateMinutes / 60, 2);

        // Undertime Minutes for this month
        $monthlyUndertimeAttendance = Attendance::where('user_id', $authUser && $authUser->id ? $authUser->id : null)
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
        $totalMonthlyLateHoursFormatted = $formatMinutes($totalMonthlyLateHours);
        $totalMonthlyUndertimeHoursFormatted = $formatMinutes($totalMonthlyUndertimeHours);

        $assignments = ShiftAssignment::with('shift')
            ->where('user_id', $authUser && $authUser->id ? $authUser->id : null)
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
                'permission' => $permission
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
        $settings    = AttendanceSettings::first();

        // 1. Get all shift assignments for today
        $assignments = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
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

            //  Sort by shift start time
            ->sortBy(fn($a) => $a->shift->start_time ?? '00:00:00');

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

        if (!$nextAssignment) {
            return response()->json(['message' => 'All shifts already clocked in today.'], 403);
        }

        // Grace Period Computation
        $graceMin    = $settings->grace_period ?? 0;
        $shiftStart  = Carbon::parse("{$today} {$nextAssignment->shift->start_time}");
        $lateMinutes = 0;

        if ($now->greaterThan($shiftStart)) {
            $lateMinutes = $shiftStart->diffInMinutes($now);
        }

        if ($lateMinutes > $graceMin) {
            $status            = 'late';
            $totalLateMinutes  = $lateMinutes;
        } else {
            $status            = 'present';
            $totalLateMinutes  = 0;
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

    // Clock-OUT
    // public function employeeAttendanceClockOut(Request $request)
    // {
    //     $request->validate([
    //         'shift_id'            => 'required|integer',
    //         'time_out_photo'      => 'required_if:require_photo_capture,1|file|image',
    //         'time_out_latitude'   => 'required_if:geotagging_enabled,1|nullable',
    //         'time_out_longitude'  => 'required_if:geotagging_enabled,1|nullable',
    //     ]);

    //     $user     = Auth::user();
    //     $shiftId  = $request->input('shift_id');
    //     $settings = AttendanceSettings::first();
    //     $today    = Carbon::today()->toDateString();
    //     $now      = Carbon::now();

    //     // 1️⃣ Find the matching clock-in
    //     $attendance = Attendance::where('user_id', $user->id)
    //         ->where('shift_id', $shiftId)
    //         ->where('attendance_date', $today)
    //         ->whereNotNull('date_time_in')
    //         ->whereNull('date_time_out')
    //         ->latest('date_time_in')
    //         ->first();

    //     if (! $attendance) {
    //         return response()->json([
    //             'message' => 'No matching clock-in found for that shift.'
    //         ], 403);
    //     }

    //     // 2️⃣ Photo
    //     $photoPath = null;
    //     if ($settings->require_photo_capture) {
    //         if (! $request->hasFile('time_out_photo')) {
    //             return response()->json([
    //                 'message' => 'Photo is required before clock-out.'
    //             ], 422);
    //         }
    //         $photoPath = $request
    //             ->file('time_out_photo')
    //             ->store('attendance_photos', 'public');
    //     }

    //     // 3️⃣ Geotag inputs & accuracy
    //     $latitude  = null;
    //     $longitude = null;
    //     $accuracy  = 0;
    //     if ($settings->geotagging_enabled || $settings->geofencing_enabled) {
    //         $latitude  = $request->input('time_out_latitude');
    //         $longitude = $request->input('time_out_longitude');
    //         $accuracy  = (float) $request->input('time_out_accuracy', 0);

    //         if (! $latitude || ! $longitude) {
    //             return response()->json([
    //                 'message' => 'Location is required before clock-out.'
    //             ], 422);
    //         }
    //     }

    //     // 4️⃣ Geofence enforcement with 3-strike fallback
    //     $usedFenceId    = null;
    //     if ($settings->geofencing_enabled) {
    //         $buffer   = $settings->geofence_buffer;
    //         $cacheKey = "geofence_attempts_out:{$user->id}";

    //         // a) Branch + user fences
    //         $branchId  = optional($user->employmentDetail)->branch_id;
    //         $branchIds = $branchId
    //             ? Geofence::where('branch_id', $branchId)->pluck('id')->toArray()
    //             : [];

    //         $gu        = GeofenceUser::where('user_id', $user->id)->get();
    //         $manualIds = $gu->where('assignment_type', 'manual')->pluck('geofence_id')->toArray();
    //         $exemptIds = $gu->where('assignment_type', 'exempt')->pluck('geofence_id')->toArray();

    //         $allowedIds = array_unique(array_merge(
    //             $manualIds,
    //             array_diff($branchIds, $exemptIds)
    //         ));

    //         $fences = Geofence::whereIn('id', $allowedIds)
    //             ->where('status', 'active')
    //             ->where(function ($q) use ($today) {
    //                 $q->whereNull('expiration_date')
    //                     ->orWhereDate('expiration_date', '>=', $today);
    //             })
    //             ->get();

    //         if ($fences->isEmpty()) {
    //             return response()->json([
    //                 'message' => 'No active geofence available for you.'
    //             ], 403);
    //         }

    //         // b) Haversine check
    //         $inside    = false;
    //         $dist      = null;
    //         $effective = null;

    //         foreach ($fences as $f) {
    //             $dist = $this->haversineDistance(
    //                 $latitude,
    //                 $longitude,
    //                 $f->latitude,
    //                 $f->longitude
    //             );
    //             $effective = $f->geofence_radius + $buffer + $accuracy;

    //             if ($dist <= $effective) {
    //                 $inside      = true;
    //                 $usedFenceId = $f->id;
    //                 break;
    //             }
    //         }

    //         if ($inside) {
    //             // ✅ inside: clear any prior “outside” tries
    //             Cache::forget($cacheKey);
    //         } else {
    //             // ❌ outside: enforce or fallback

    //             // 1) geotagging OFF → block
    //             if (! $settings->geotagging_enabled) {
    //                 return response()->json([
    //                     'message' => 'Location is required before clocking out.'
    //                 ], 422);
    //             }

    //             // 2) fallback allowed?
    //             if ($settings->geofence_allowed_geotagging) {
    //                 $attempts = Cache::get($cacheKey, 0) + 1;
    //                 Cache::put($cacheKey, $attempts, now()->addMinutes(10));

    //                 if ($attempts < 3) {
    //                     return response()->json([
    //                         'message' => "Weak signal detected. Please try again. Attempts left: " . (3 - $attempts)
    //                     ], 403);
    //                 }

    //                 // 3rd failure → clear counter; allow 4th and onward
    //                 Cache::forget($cacheKey);
    //             } else {
    //                 // strict: always block
    //                 return response()->json([
    //                     'message'          => 'You are outside the permitted area.',
    //                     'distance'         => round($dist, 2),
    //                     'effective_radius' => round($effective, 2),
    //                 ], 403);
    //             }
    //         }
    //     }

    //     // 5️⃣ Compute worked minutes
    //     $workedMinutes = $attendance->date_time_in->diffInMinutes($now);
    //     if ($settings->maximum_allowed_hours) {
    //         $workedMinutes = min($workedMinutes, $settings->maximum_allowed_hours * 60);
    //     }

    //     // 6️⃣ Update attendance
    //     $attendance->update([
    //         'date_time_out'       => $now,
    //         'time_out_photo_path' => $photoPath,
    //         'time_out_latitude'   => $latitude,
    //         'time_out_longitude'  => $longitude,
    //         'within_geofence'     => $inside,
    //         'geofence_id'         => $usedFenceId,
    //         'clock_out_method'    => $request->input('clock_out_method', 'manual_web'),
    //         'total_work_minutes'  => $workedMinutes,
    //     ]);

    //     return response()->json([
    //         'message' => 'Clock-Out successful.',
    //         'data'    => $attendance->fresh(),
    //     ]);
    // }

    public function employeeAttendanceClockOut(Request $request)
    {
        // 1️⃣ Validate input
        $request->validate([
            'shift_id'           => 'required|integer',
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
        $attendance = Attendance::where('user_id', $user->id)
            ->where('shift_id', $shiftId)
            ->where('attendance_date', $today)
            ->whereNotNull('date_time_in')
            ->whereNull('date_time_out')
            ->latest('date_time_in')
            ->first();

        if (! $attendance) {
            return response()->json([
                'message' => 'No matching clock-in found for that shift.'
            ], 403);
        }

        // 3️⃣ Photo capture (if required)
        $photoPath = null;
        if ($settings->require_photo_capture) {
            if (! $request->hasFile('time_out_photo')) {
                return response()->json([
                    'message' => 'Photo is required before clock-out.'
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
                    'message' => 'Location is required before clock-out.'
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
                    'message' => 'No active geofence available for you.'
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
                        'message' => 'Location is required before clocking out.'
                    ], 422);
                }
                if ($settings->geofence_allowed_geotagging) {
                    $attempts = Cache::get($cacheKey, 0) + 1;
                    Cache::put($cacheKey, $attempts, now()->addMinutes(10));

                    if ($attempts < 3) {
                        return response()->json([
                            'message' => "Weak signal detected. Please try again. Attempts left: " . (3 - $attempts)
                        ], 403);
                    }
                    Cache::forget($cacheKey);
                } else {
                    return response()->json([
                        'message'          => 'You are outside the permitted area.',
                        'distance'         => round($dist, 2),
                        'effective_radius' => round($effective, 2),
                    ], 403);
                }
            }
        }

        $start = $attendance->date_time_in; // e.g. 2025-06-04 21:00:00
        $end   = $now;                       // e.g. 2025-06-05 06:00:00

        // a) Define loop bounds: midnight of start and midnight of end
        $startDay = $start->copy()->startOfDay();
        $endDay   = $end->copy()->startOfDay();

        // b) Overlap helper between two intervals
        $calcOverlap = function (Carbon $aStart, Carbon $aEnd, Carbon $bStart, Carbon $bEnd) {
            $overlapStart = $aStart->greaterThan($bStart) ? $aStart : $bStart;
            $overlapEnd   = $aEnd->lessThan($bEnd) ? $aEnd : $bEnd;
            if ($overlapStart->lt($overlapEnd)) {
                return $overlapEnd->diffInMinutes($overlapStart);
            }
            return 0;
        };

        // c) Sum overlaps for every 22:00→06:00 window intersecting [start, end]
        $nightDiffTotal = 0;
        for (
            $cursor = $startDay->copy()->subDay();
            $cursor->lte($endDay);
            $cursor->addDay()
        ) {
            $windowStart = $cursor->copy()->setTime(22, 0, 0);         // 22:00 of $cursor
            $windowEnd   = $cursor->copy()->addDay()->setTime(6, 0, 0); // 06:00 of $cursor+1

            $nightDiffTotal += $calcOverlap($start, $end, $windowStart, $windowEnd);
        }

        // d) Raw total worked minutes (full span)
        $rawWorked = $start->diffInMinutes($end);

        // e) Compute “regular” minutes = rawWorked – nightDiffTotal
        $regularMinutesRaw = $rawWorked - $nightDiffTotal;
        if ($regularMinutesRaw < 0) {
            $regularMinutesRaw = 0;
        }

        // f) Cap “regular” minutes by maximum_allowed_hours if set
        if ($settings->maximum_allowed_hours) {
            $capInMin = $settings->maximum_allowed_hours * 60;
            $regularMinutes = min($regularMinutesRaw, $capInMin);
        } else {
            $regularMinutes = $regularMinutesRaw;
        }

        // g) If clock-in ≥ 22:00, then everything is night-diff; regular = 0
        $firstWindowStart = $startDay->copy()->setTime(22, 0, 0);
        if ($start->greaterThanOrEqualTo($firstWindowStart)) {
            $regularMinutes = 0;
            $nightDiffTotal = $rawWorked;
        }

        // f) Compute undertime: compare clock-out with scheduled shift end_time
        // 7️⃣ Compute total undertime minutes
        //    Undertime = scheduled end_time – actual clock-out, if clock-out is before scheduled end.
        $totalUndertime = 0;
        Log::info('[Undertime] Compute start', [
            'attendance_id' => $attendance->id,
            'start'         => $start->toDateTimeString(),
            'end'           => $end->toDateTimeString(),
        ]);

        if ($attendance->shift_assignment_id) {
            $shiftAssignment = $attendance->shiftAssignment()->with('shift')->first();
            if ($shiftAssignment && $shiftAssignment->shift && $shiftAssignment->shift->end_time) {
                // 1) I-build ang scheduledEnd gamit ang attendance_date at shift end_time
                $scheduledEnd = Carbon::parse(
                    $attendance->attendance_date->toDateString() . ' ' .
                        $shiftAssignment->shift->end_time
                );

                Log::info('[Undertime] Scheduled end_time for shift', [
                    'shift_id'     => $shiftAssignment->shift->id,
                    'scheduledEnd' => $scheduledEnd->toDateTimeString(),
                ]);

                // 2) Kapag ang actual clock-out ($end) ay mas maaga sa scheduledEnd, may undertime
                if ($end->lt($scheduledEnd)) {
                    // Dito, palaging positive ang diffInMinutes kung first arg < second arg
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
            } else {
                Log::info('[Undertime] No shift or end_time found for assignment', [
                    'shift_assignment_id' => $attendance->shift_assignment_id,
                ]);
            }
        } else {
            Log::info('[Undertime] No shift_assignment_id on attendance', [
                'attendance_id' => $attendance->id,
            ]);
        }

        // 7️⃣ Update the attendance record
        $attendance->update([
            'date_time_out'             => $end,
            'time_out_photo_path'       => $photoPath,
            'time_out_latitude'         => $latitude,
            'time_out_longitude'        => $longitude,
            'within_geofence'           => $inside,
            'geofence_id'               => $usedFenceId,
            'clock_out_method'          => $request->input('clock_out_method', 'manual_web'),
            'total_work_minutes'        => $regularMinutes,
            'total_night_diff_minutes'  => $nightDiffTotal,
            'total_undertime_minutes' => $totalUndertime,
        ]);

        return response()->json([
            'message' => 'Clock-Out successful.',
            'data'    => $attendance->fresh(),
        ]);
    }
}
