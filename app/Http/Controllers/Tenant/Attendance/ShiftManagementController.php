<?php

namespace App\Http\Controllers\Tenant\Attendance;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use App\Models\UserLog;
use Carbon\CarbonPeriod;
use App\Models\ShiftList;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use App\Models\ShiftAssignment;
use App\Models\EmploymentDetail;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;

class ShiftManagementController extends Controller
{

    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    }
    public function shiftManagementFilter(Request $request)
    {
        $start = Carbon::createFromFormat('m/d/Y', $request->start_date)->startOfDay();
        $end = Carbon::createFromFormat('m/d/Y', $request->end_date)->endOfDay();

        $authUser = $this->authUser();
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $employeesQuery = $accessData['employees']->with('personalInformation');

        if ($request->branch_id) {
            $employeesQuery->whereHas('employmentDetail', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }

        if ($request->department_id) {
            $employeesQuery->whereHas('employmentDetail', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->designation_id) {
            $employeesQuery->whereHas('employmentDetail', function ($q) use ($request) {
                $q->where('designation_id', $request->designation_id);
            });
        }

        $employees = $employeesQuery->get();

        $dateRange = collect();
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateRange->push($date->copy());
        }

        $assignments = [];

        foreach ($employees as $emp) {
            $userAssignments = ShiftAssignment::with('shift')
                ->where('user_id', $emp->id)
                ->get();

            foreach ($dateRange as $date) {
                $dateStr = $date->toDateString();
                $dayOfWeek = strtolower($date->format('D'));
                $shiftsForDate = [];

                foreach ($userAssignments as $asgmt) {
                    $sd = Carbon::parse($asgmt->start_date);
                    $ed = $asgmt->end_date ? Carbon::parse($asgmt->end_date) : Carbon::now()->addYear();
                    $carbonDate = Carbon::parse($date);

                    if ($carbonDate->lt($sd) || $carbonDate->gt($ed)) continue;

                    if ($asgmt->type === 'recurring' && in_array($dayOfWeek, $asgmt->days_of_week ?? [])) {
                        if (!in_array($dateStr, $asgmt->excluded_dates ?? [])) {
                            $shiftsForDate[] = $asgmt->is_rest_day
                                ? ['assignment_id' => $asgmt->id, 'rest_day' => true]
                                : [
                                    'assignment_id' => $asgmt->id,
                                    'name' => $asgmt->shift->name,
                                    'start_time' => $asgmt->shift->start_time,
                                    'end_time' => $asgmt->shift->end_time,
                                ];
                        }
                    }

                    if ($asgmt->type === 'custom' && in_array($dateStr, $asgmt->custom_dates ?? [])) {
                        $shiftsForDate[] = $asgmt->is_rest_day
                            ? ['assignment_id' => $asgmt->id, 'rest_day' => true]
                            : [
                                'assignment_id' => $asgmt->id,
                                'name' => $asgmt->shift->name,
                                'start_time' => $asgmt->shift->start_time,
                                'end_time' => $asgmt->shift->end_time,
                            ];
                    }
                }

                $assignments[$emp->id][$dateStr] = $shiftsForDate;
            }
        }

        $html = view('tenant.attendance.shiftmanagement.shiftassignment_filter', compact('employees', 'dateRange', 'assignments'))->render();

        $dateData = $dateRange->map(fn($d) => [
            'full' => $d->format('Y-m-d'),
            'short' => $d->format('m/d/Y'),
            'day' => $d->format('D')
        ]);

        return response()->json([
            'html' => $html,
            'dateRange' => $dateData
        ]);
    }


    public function shiftIndex(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(16);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $shifts =  $accessData['shiftList']->get();

        $branches = $accessData['branches']->get();
        $departments  = $accessData['departments']->get();
        $designations = $accessData['designations']->get();
        $employees = $accessData['employees']->get();

        $startDate = $request->input('start_date', now()->startOfWeek()->toDateString());
        $endDate = $request->input('end_date', now()->endOfWeek()->toDateString());

        $assignments = [];

        $dateRange = CarbonPeriod::create($startDate, $endDate);

        foreach ($employees as $emp) {
            $userAssignments = ShiftAssignment::with('shift')
                ->where('user_id', $emp->id)
                ->get();

            foreach ($dateRange as $date) {
                $dateStr = $date->toDateString();
                $dayOfWeek = strtolower($date->format('D'));

                $shiftsForDate = [];

                foreach ($userAssignments as $asgmt) {
                    $sd = Carbon::parse($asgmt->start_date);
                    $ed = $asgmt->end_date ? Carbon::parse($asgmt->end_date) : Carbon::now()->addYear();

                    $carbonDate = Carbon::parse($date);
                    $carbonStart = Carbon::parse($sd);
                    $carbonEnd = $ed ? Carbon::parse($ed) : null;

                    if ($carbonEnd) {
                        if (!$carbonDate->between($carbonStart, $carbonEnd)) {
                            continue;
                        }
                    } else {
                        if ($carbonDate->lt($carbonStart)) {
                            continue;
                        }
                    }

                    if ($asgmt->type === 'recurring' && in_array($dayOfWeek, $asgmt->days_of_week)) {
                        $excludedDates = $asgmt->excluded_dates ?? [];

                        if (!in_array($dateStr, $excludedDates)) {
                            if ($asgmt->is_rest_day) {
                                $shiftsForDate[] = [
                                    'assignment_id' => $asgmt->id,
                                    'rest_day' => true,
                                ];
                            } else {
                                $shiftsForDate[] = [
                                    'assignment_id' => $asgmt->id,
                                    'name' => $asgmt->shift->name,
                                    'start_time' => $asgmt->shift->start_time,
                                    'end_time' => $asgmt->shift->end_time,
                                ];
                            }
                        }
                    }

                    if ($asgmt->type === 'custom') {
                        $customDates = $asgmt->custom_dates ?? [];

                        if (in_array($dateStr, $customDates)) {
                            if ($asgmt->is_rest_day) {
                                $shiftsForDate[] = [
                                    'assignment_id' => $asgmt->id,
                                    'rest_day' => true,
                                ];
                            } else {
                                $shiftsForDate[] = [
                                    'assignment_id' => $asgmt->id,
                                    'name' => $asgmt->shift->name,
                                    'start_time' => $asgmt->shift->start_time,
                                    'end_time' => $asgmt->shift->end_time,
                                ];
                            }
                        }
                    }
                }

                $assignments[$emp->id][$dateStr] = $shiftsForDate;
            }
        }

        return view('tenant.attendance.shiftmanagement.shiftassignment', [
            'employees' => $employees,
            'assignments' => $assignments,
            'dateRange' => iterator_to_array($dateRange),
            'shifts' => $shifts,
            'branches' => $branches,
            'designations' => $designations,
            'departments' => $departments,
            'permission' => $permission
        ]);
    }

    public function shiftListfilter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(16);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $branch = $request->input('branch');

        $query  = $accessData['shiftList'];

        if ($branch) {
            $query->where('branch_id', $branch);
        }

        $shifts = $query->get();

        $html = view('tenant.attendance.shiftmanagement.shiftlist_filter', compact('shifts', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function shiftList(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(16);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $shifts = ShiftList::where('tenant_id', $tenantId)
            ->get();

        $branches = $accessData['branches']->get();
        $designations = $accessData['designations']->get();
        $departments = $accessData['departments']->get();

        $employees = User::with([
            'employmenDetail',
            'personalInformation'
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'shifts' => $shifts,
                'branches' => $branches,
                'designations' => $designations,
                'departments' => $departments,
                'employees' => $employees,
            ]);
        }

        return view('tenant.attendance.shiftmanagement.shiftlist', [
            'shifts' => $shifts,
            'branches' => $branches,
            'designations' => $designations,
            'departments' => $departments,
            'employees' => $employees,
            'permission' => $permission
        ]);
    }

    // Shift Assignment (1)
    // public function shiftAssignmentCreate(Request $request)
    // {
    //     $validated = $request->validate([
    //         'user_id'      => 'required|array',
    //         'shift_id'     => 'required|array',
    //         'type'         => 'required|in:recurring,custom',
    //         'start_date'   => 'required|date',
    //         'end_date'     => 'nullable|date|after_or_equal:start_date',
    //         'is_rest_day'  => 'sometimes|boolean',
    //         'days_of_week' => 'required_if:type,recurring|array',
    //         'custom_dates' => 'required_if:type,custom|array',
    //         'override'     => 'nullable|boolean',
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         foreach ($validated['user_id'] as $userId) {
    //             foreach ($validated['shift_id'] as $shiftId) {
    //                 $newShift = ShiftList::find($shiftId);
    //                 $newStartTime = Carbon::parse($newShift->start_time);
    //                 $newEndTime = Carbon::parse($newShift->end_time);

    //                 $conflictingAssignments = collect();

    //                 if ($validated['type'] === 'custom') {
    //                     foreach ($validated['custom_dates'] as $date) {
    //                         $carbonDate = Carbon::parse($date);
    //                         $day = strtolower($carbonDate->format('D'));

    //                         $matches = ShiftAssignment::where('user_id', $userId)
    //                             ->where(function ($q) use ($date, $day) {
    //                                 $q->where(function ($sub) use ($date, $day) {
    //                                     $sub->where('type', 'recurring')
    //                                         ->where('start_date', '<=', $date)
    //                                         ->where(function ($r) use ($date) {
    //                                             $r->whereNull('end_date')->orWhere('end_date', '>=', $date);
    //                                         })
    //                                         ->whereJsonContains('days_of_week', $day)
    //                                         ->where(function ($s) use ($date) {
    //                                             $s->whereNull('excluded_dates')
    //                                                 ->orWhereJsonDoesntContain('excluded_dates', $date);
    //                                         });
    //                                 })->orWhere(function ($sub) use ($date) {
    //                                     $sub->where('type', 'custom')
    //                                         ->whereJsonContains('custom_dates', $date);
    //                                 });
    //                             })->get();

    //                         $conflictingAssignments = $conflictingAssignments->merge($matches);
    //                     }

    //                     $conflictingAssignments = $conflictingAssignments->unique('id');
    //                 } else {
    //                     $start = $validated['start_date'];
    //                     $end = $validated['end_date'] ?? $start;

    //                     $conflictingAssignments = ShiftAssignment::where('user_id', $userId)
    //                         ->where('start_date', '<=', $end)
    //                         ->where(function ($q) use ($start) {
    //                             $q->whereNull('end_date')->orWhere('end_date', '>=', $start);
    //                         })
    //                         ->where(function ($q) use ($validated) {
    //                             foreach ($validated['days_of_week'] as $day) {
    //                                 $q->orWhereJsonContains('days_of_week', strtolower($day));
    //                             }
    //                         })->get();
    //                 }

    //                 // Filter conflicts by time
    //                 $conflictingAssignments = $conflictingAssignments->filter(function ($conflict) use ($newStartTime, $newEndTime) {
    //                     $existingShift = ShiftList::find($conflict->shift_id);
    //                     $existingStartTime = Carbon::parse($existingShift->start_time);
    //                     $existingEndTime = Carbon::parse($existingShift->end_time);
    //                     return $newStartTime < $existingEndTime && $newEndTime > $existingStartTime;
    //                 });

    //                 Log::info("Conflicting shifts for user {$userId}: " . $conflictingAssignments->pluck('id')->join(', '));

    //                 if ($conflictingAssignments->count() > 0 && empty($validated['override'])) {
    //                     DB::rollBack();
    //                     $user = User::find($userId);

    //                     return response()->json([
    //                         'message' => "There is a conflict with existing shift(s) for {$user->personalInformation->name}. Do you want to override?",
    //                         'requires_override' => true,
    //                     ], 409);
    //                 }

    //                 if (!empty($validated['override']) && $conflictingAssignments->count() > 0) {
    //                     foreach ($conflictingAssignments as $conflict) {
    //                         if ($validated['type'] === 'recurring') {
    //                             $newDays = array_map('strtolower', $validated['days_of_week']);
    //                             $existingDays = $conflict->days_of_week;

    //                             $overlappingDays = array_intersect($existingDays, $newDays);

    //                             if (empty(array_diff($existingDays, $newDays))) {
    //                                 $conflict->delete();
    //                             } elseif (!empty($overlappingDays)) {
    //                                 $conflict->days_of_week = array_values(array_diff($existingDays, $newDays));
    //                                 $conflict->save();
    //                             }
    //                         } else {
    //                             if ($conflict->type === 'recurring') {
    //                                 $excluded = $conflict->excluded_dates ?? [];
    //                                 $excluded = is_array($excluded) ? $excluded : json_decode($excluded, true);
    //                                 $conflict->excluded_dates = array_values(array_unique(array_merge($excluded, $validated['custom_dates'])));
    //                                 $conflict->save();
    //                             } else {
    //                                 $conflictDates = $conflict->custom_dates;
    //                                 $datesToCheck = $validated['custom_dates'];
    //                                 $overlappingDates = array_intersect($conflictDates, $datesToCheck);

    //                                 if (!empty($overlappingDates)) {
    //                                     $existingShift = ShiftList::find($conflict->shift_id);
    //                                     $existingStartTime = Carbon::parse($existingShift->start_time);
    //                                     $existingEndTime = Carbon::parse($existingShift->end_time);

    //                                     if ($newStartTime < $existingEndTime && $newEndTime > $existingStartTime) {
    //                                         $conflict->delete();
    //                                         continue;
    //                                     }
    //                                 }

    //                                 $newDates = array_diff($conflictDates, $validated['custom_dates']);

    //                                 if (empty($newDates)) {
    //                                     $conflict->delete();
    //                                 } else {
    //                                     $conflict->custom_dates = array_values($newDates);
    //                                     $conflict->save();
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }

    //                 // Prevent duplicates
    //                 $alreadyExists = ShiftAssignment::where('user_id', $userId)
    //                     ->where('shift_id', $shiftId)
    //                     ->where('type', $validated['type'])
    //                     ->whereDate('start_date', $validated['start_date'])
    //                     ->when($validated['type'] === 'recurring', function ($q) use ($validated) {
    //                         foreach ($validated['days_of_week'] as $day) {
    //                             $q->orWhereJsonContains('days_of_week', strtolower($day));
    //                         }
    //                     })
    //                     ->when($validated['type'] === 'custom', function ($q) use ($validated) {
    //                         foreach ($validated['custom_dates'] as $cd) {
    //                             $q->orWhereJsonContains('custom_dates', $cd);
    //                         }
    //                     })
    //                     ->exists();

    //                 if ($alreadyExists) {
    //                     continue;
    //                 }

    //                 $data = [
    //                     'user_id'     => $userId,
    //                     'shift_id'    => $shiftId,
    //                     'type'        => $validated['type'],
    //                     'start_date'  => $validated['start_date'],
    //                     'end_date'    => $validated['end_date'] ?? null,
    //                     'is_rest_day' => $validated['is_rest_day'] ?? false,
    //                 ];

    //                 if ($validated['type'] === 'recurring') {
    //                     $data['days_of_week'] = array_map('strtolower', $validated['days_of_week']);
    //                     $data['custom_dates'] = [];
    //                 } else {
    //                     $data['days_of_week'] = [];
    //                     $data['custom_dates'] = $validated['custom_dates'];
    //                 }

    //                 $assignment = ShiftAssignment::create($data);

    //                 $userLogId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
    //                 $globalLogId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

    //                 UserLog::create([
    //                     'user_id'        => $userLogId,
    //                     'global_user_id' => $globalLogId,
    //                     'module'         => 'Shift Management',
    //                     'action'         => 'Create',
    //                     'description'    => "Created shift assignment (ID: {$assignment->id}) for user {$userId}",
    //                     'affected_id'    => $assignment->id,
    //                     'old_data'       => null,
    //                     'new_data'       => json_encode($assignment->toArray()),
    //                 ]);
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Shift assignments successfully created.',
    //             'data' => $assignment,
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('ShiftAssignment store error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

    //         return response()->json([
    //             'message' => 'Failed to create shift assignments.',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function shiftAssignmentCreate(Request $request)
    {

        $permission = PermissionHelper::get(16);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create or update.'
            ], 403);
        }

        $validated = $request->validate([
            'user_id'      => 'required|array',
            'shift_id'     => 'required_if:is_rest_day,false|array',
            'type'         => 'required|in:recurring,custom',
            'start_date'   => 'required|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'is_rest_day'  => 'sometimes|boolean',
            'days_of_week' => 'required_if:type,recurring|array',
            'custom_dates' => 'required_if:type,custom|array',
            'override'     => 'nullable|boolean',
            'skip_rest_check'  => 'nullable|boolean',
        ]);

        // Ensure custom_dates is always an array of individual date strings
        if ($validated['type'] === 'custom') {
            $rawCustomDates = $validated['custom_dates'];
            $dates = [];
            if (is_array($rawCustomDates)) {
                foreach ($rawCustomDates as $item) {
                    foreach (explode(',', $item) as $date) {
                        $date = trim($date);
                        if ($date !== '') {
                            $dates[] = $date;
                        }
                    }
                }
            } else {
                foreach (explode(',', $rawCustomDates) as $date) {
                    $date = trim($date);
                    if ($date !== '') {
                        $dates[] = $date;
                    }
                }
            }
            $validated['custom_dates'] = $dates;
            Log::info('shiftAssignmentCreate: custom_dates received', [
                'custom_dates' => $validated['custom_dates'],
                'count' => is_array($validated['custom_dates']) ? count($validated['custom_dates']) : 0,
                'raw' => $request->input('custom_dates'),
            ]);
        }

        try {
            DB::beginTransaction();
            $assignment = null;

            foreach ($validated['user_id'] as $userId) {
                if (!empty($validated['is_rest_day'])) {
                    $dates = $validated['type'] === 'custom' ? $validated['custom_dates'] : [];
                    $days = $validated['type'] === 'recurring' ? array_map('strtolower', $validated['days_of_week']) : [];

                    $existingAssignments = ShiftAssignment::where('user_id', $userId)->get();

                    foreach ($existingAssignments as $conflict) {
                        if ($validated['type'] === 'recurring' && $conflict->type === 'recurring') {
                            $original = $conflict->days_of_week;
                            $conflict->days_of_week = array_values(array_diff($original, $days));
                            if (empty($conflict->days_of_week)) {
                                $conflict->delete();
                            } else {
                                $conflict->save();
                            }
                        } elseif ($validated['type'] === 'custom') {
                            $excluded = $conflict->excluded_dates ?? [];
                            $excluded = is_array($excluded) ? $excluded : json_decode($excluded, true);
                            $conflict->excluded_dates = array_values(array_unique(array_merge($excluded, $dates)));
                            $conflict->save();
                        }
                    }

                    // Create the actual rest day assignment
                    $data = [
                        'user_id'     => $userId,
                        'shift_id'    => null,
                        'type'        => $validated['type'],
                        'start_date'  => $validated['start_date'],
                        'end_date'    => $validated['end_date'] ?? null,
                        'is_rest_day' => true,
                        'days_of_week' => $validated['type'] === 'recurring' ? $days : [],
                        'custom_dates' => $validated['type'] === 'custom' ? $dates : [],
                    ];

                    $assignment = ShiftAssignment::create($data);

                    UserLog::create([
                        'user_id'        => Auth::guard('web')->id(),
                        'global_user_id' => Auth::guard('global')->id(),
                        'module'         => 'Shift Management',
                        'action'         => 'Create',
                        'description'    => "Created REST DAY shift assignment for user {$userId}",
                        'affected_id'    => $assignment->id,
                        'old_data'       => null,
                        'new_data'       => json_encode($assignment->toArray()),
                    ]);

                    continue;
                }

                foreach ($validated['shift_id'] as $shiftId) {

                    Log::info('▶ entering shift loop', [
                        'user_id'         => $userId,
                        'shift_id'        => $shiftId,
                        'skip_rest_check' => $validated['skip_rest_check'] ?? null,
                        'override'        => $validated['override']        ?? null,
                    ]);

                    $newShift = ShiftList::find($shiftId);
                    $newStartTime = Carbon::parse($newShift->start_time);
                    $newEndTime = Carbon::parse($newShift->end_time);

                    $conflictingAssignments = collect();

                    $restDayConflicts = ShiftAssignment::where('user_id', $userId)
                        ->where('is_rest_day', true)
                        ->get();

                    Log::info('▶ restDayConflicts IDs', $restDayConflicts->pluck('id')->toArray());

                    if (
                        $restDayConflicts->isNotEmpty()
                        && empty($validated['override'])
                        && empty($validated['skip_rest_check'])
                    ) {
                        DB::rollBack();
                        return response()->json([
                            'message'           => 'This user already has a rest day scheduled on this date. Do you want to override it or skip?',
                            'requires_override' => true,
                        ], 409);
                    }

                    // Optional override cleanup
                    if (!empty($validated['override']) && $restDayConflicts->isNotEmpty()) {
                        foreach ($restDayConflicts as $restDay) {
                            $restDay->delete();
                        }
                    }

                    if ($validated['type'] === 'custom') {
                        Log::info('shiftAssignmentCreate: custom_dates inside shift_id loop', [
                            'custom_dates' => $validated['custom_dates'],
                            'count' => is_array($validated['custom_dates']) ? count($validated['custom_dates']) : 0,
                        ]);
                        foreach ($validated['custom_dates'] as $date) {
                            $carbonDate = Carbon::parse($date);
                            $day = strtolower($carbonDate->format('D'));

                            $matches = ShiftAssignment::where('user_id', $userId)
                                ->where('is_rest_day', false)
                                ->where(function ($q) use ($date, $day) {
                                    $q->where(function ($sub) use ($date, $day) {
                                        $sub->where('type', 'recurring')
                                            ->where('start_date', '<=', $date)
                                            ->where(function ($r) use ($date) {
                                                $r->whereNull('end_date')->orWhere('end_date', '>=', $date);
                                            })
                                            ->whereJsonContains('days_of_week', $day)
                                            ->where(function ($s) use ($date) {
                                                $s->whereNull('excluded_dates')
                                                    ->orWhereJsonDoesntContain('excluded_dates', $date);
                                            });
                                    })->orWhere(function ($sub) use ($date) {
                                        $sub->where('type', 'custom')
                                            ->whereJsonContains('custom_dates', $date);
                                    });
                                })->get();

                            $conflictingAssignments = $conflictingAssignments->merge($matches);
                        }

                        $conflictingAssignments = $conflictingAssignments->unique('id');
                    } else {
                        $startDate = Carbon::parse($validated['start_date']);
                        $endDate = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : null;

                        $conflictingAssignments = ShiftAssignment::where('user_id', $userId)
                            ->where('is_rest_day', false)           // ← kills all rest-day records
                            ->whereNotNull('shift_id')              // ← extra safety
                            ->where('start_date', '<=', $endDate ?? $startDate)
                            ->where(function ($q) use ($startDate) {
                                $q->whereNull('end_date')
                                    ->orWhere('end_date', '>=', $startDate);
                            })
                            ->where(function ($q) use ($validated) {
                                foreach ($validated['days_of_week'] as $day) {
                                    $q->orWhereJsonContains('days_of_week', strtolower($day));
                                }
                            })
                            ->get();

                        if (! empty($validated['skip_rest_check']) && $conflictingAssignments->count() > 0) {
                            // filter for true time-overlaps
                            $toDelete = $conflictingAssignments->filter(function ($conflict) use ($newStartTime, $newEndTime) {
                                $existing = ShiftList::find($conflict->shift_id);
                                if (! $existing) return false;
                                $existStart = Carbon::parse($existing->start_time);
                                $existEnd   = Carbon::parse($existing->end_time);
                                return $newStartTime < $existEnd && $newEndTime > $existStart;
                            });

                            // delete only the overlapping ones
                            foreach ($toDelete as $del) {
                                $del->delete();
                            }

                            // remove them from the in-memory list so the next check sees zero
                            $conflictingAssignments = $conflictingAssignments->diff($toDelete);
                        }


                        Log::debug(
                            'REAL‐SHIFT conflicts before filter:',
                            $conflictingAssignments
                                ->map(fn($a) => [
                                    'id'          => $a->id,
                                    'is_rest_day' => $a->is_rest_day,
                                    'shift_id'    => $a->shift_id,
                                ])
                                ->toArray()
                        );

                        // 👇 Add custom conflicts across recurring range
                        $customConflictDates = [];
                        $limitDate = $endDate ?? $startDate->copy()->addDays(30);

                        for ($date = $startDate->copy(); $date->lte($limitDate); $date->addDay()) {
                            $day = strtolower($date->format('D'));
                            if (in_array($day, array_map('strtolower', $validated['days_of_week']))) {
                                $customConflictDates[] = $date->format('Y-m-d');
                            }
                        }

                        $customConflicts = ShiftAssignment::where('user_id', $userId)
                            ->where('is_rest_day', false)           // ← again, drop rest-days
                            ->whereNotNull('shift_id')
                            ->where('type', 'custom')
                            ->where(function ($q) use ($customConflictDates) {
                                foreach ($customConflictDates as $date) {
                                    $q->orWhereJsonContains('custom_dates', $date);
                                }
                            })
                            ->get();

                        $conflictingAssignments = $conflictingAssignments->merge($customConflicts)->unique('id');
                    }

                    $conflictingAssignments = $conflictingAssignments->filter(function ($conflict) {
                        return !$conflict->is_rest_day && ! is_null($conflict->shift_id);
                    })->filter(function ($conflict) use ($newStartTime, $newEndTime) {
                        $existingShift = ShiftList::find($conflict->shift_id);
                        // just in case you have stray IDs
                        if (! $existingShift) {
                            return false;
                        }
                        $existingStart = Carbon::parse($existingShift->start_time);
                        $existingEnd   = Carbon::parse($existingShift->end_time);

                        return $newStartTime < $existingEnd
                            && $newEndTime   > $existingStart;
                    });

                    Log::info("Conflicting shifts for user {$userId}: " . $conflictingAssignments->pluck('id')->join(', '));

                    $conflictingAssignments = $conflictingAssignments
                        ->filter(fn($a) => ! $a->is_rest_day && $a->shift_id !== null);

                    if (
                        $conflictingAssignments->count() > 0
                        && empty($validated['override'])    // <- notice: no skip_rest_check here!
                    ) {
                        DB::rollBack();
                        $user = User::find($userId);

                        return response()->json([
                            'message'           => "There is a conflict with existing shift(s) for {$user->personalInformation->name}. Do you want to override?",
                            'requires_override' => true,
                        ], 409);
                    }

                    if (!empty($validated['override']) && $conflictingAssignments->count() > 0) {
                        foreach ($conflictingAssignments as $conflict) {
                            if ($validated['type'] === 'recurring' && $conflict->type === 'recurring') {
                                $newDays = array_map('strtolower', $validated['days_of_week']);
                                $existingDays = $conflict->days_of_week;
                                $overlappingDays = array_intersect($existingDays, $newDays);

                                if (empty(array_diff($existingDays, $newDays))) {
                                    $conflict->delete();
                                } elseif (!empty($overlappingDays)) {
                                    $conflict->days_of_week = array_values(array_diff($existingDays, $newDays));
                                    $conflict->save();
                                }
                            } else if ($validated['type'] === 'custom' && $conflict->type === 'recurring') {
                                $excluded = $conflict->excluded_dates ?? [];
                                $excluded = is_array($excluded) ? $excluded : json_decode($excluded, true);
                                $conflict->excluded_dates = array_values(array_unique(array_merge($excluded, $validated['custom_dates'])));
                                $conflict->save();
                            } else if ($validated['type'] === 'custom' && $conflict->type === 'custom') {
                                $conflictDates = $conflict->custom_dates;
                                $datesToCheck = $validated['custom_dates'];
                                $overlappingDates = array_intersect($conflictDates, $datesToCheck);

                                if (!empty($overlappingDates)) {
                                    $existingShift = ShiftList::find($conflict->shift_id);
                                    $existingStartTime = Carbon::parse($existingShift->start_time);
                                    $existingEndTime = Carbon::parse($existingShift->end_time);

                                    if ($newStartTime < $existingEndTime && $newEndTime > $existingStartTime) {
                                        $conflict->delete();
                                        continue;
                                    }
                                }

                                $newDates = array_diff($conflictDates, $validated['custom_dates']);

                                if (empty($newDates)) {
                                    $conflict->delete();
                                } else {
                                    $conflict->custom_dates = array_values($newDates);
                                    $conflict->save();
                                }
                            } else if ($validated['type'] === 'recurring' && $conflict->type === 'custom') {
                                $shouldDelete = false;

                                foreach ($conflict->custom_dates as $conflictDate) {
                                    $carbonDate = Carbon::parse($conflictDate);
                                    $dayOfWeek = strtolower($carbonDate->format('D'));

                                    $logTag = "[Recurring vs Custom] Date: {$conflictDate}, Conflict ID: {$conflict->id}";
                                    Log::info("{$logTag} - Checking override");

                                    $dayMatch = in_array($dayOfWeek, array_map('strtolower', $validated['days_of_week']));
                                    $dateMatch = $carbonDate->gte(Carbon::parse($validated['start_date'])) &&
                                        (empty($validated['end_date']) || $carbonDate->lte(Carbon::parse($validated['end_date'])));

                                    if ($dayMatch && $dateMatch) {
                                        $existingShift = ShiftList::find($conflict->shift_id);
                                        $existingStart = Carbon::parse($existingShift->start_time);
                                        $existingEnd = Carbon::parse($existingShift->end_time);

                                        if ($newStartTime < $existingEnd && $newEndTime > $existingStart) {
                                            $shouldDelete = true;
                                            break;
                                        }
                                    }
                                }

                                if ($shouldDelete) {
                                    Log::info("[Recurring vs Custom] Deleting custom shift {$conflict->id}.");
                                    $conflict->delete();
                                    continue;
                                }
                            }
                        }
                    }

                    $alreadyExists = ShiftAssignment::where('user_id', $userId)
                        ->where('shift_id', $shiftId)
                        ->where('type', $validated['type'])
                        ->whereDate('start_date', $validated['start_date'])
                        ->when($validated['type'] === 'recurring', function ($q) use ($validated) {
                            $q->whereJsonContains('days_of_week', array_map('strtolower', $validated['days_of_week']));
                        })
                        ->when($validated['type'] === 'custom', function ($q) use ($validated) {
                            $q->where(function ($query) use ($validated) {
                                foreach ($validated['custom_dates'] as $cd) {
                                    $query->orWhereJsonContains('custom_dates', $cd);
                                }
                            });
                        })
                        ->exists();

                    if ($alreadyExists) {
                        Log::info("Skipped duplicate shift for user {$userId}, shift {$shiftId}");
                        continue;
                    }

                    $excludedDates = [];
                    if (! empty($validated['skip_rest_check']) && $restDayConflicts->isNotEmpty()) {
                        foreach ($restDayConflicts as $rest) {
                            if ($rest->type === 'custom') {
                                // <— take every date the user explicitly set as a rest day
                                $excludedDates = array_merge($excludedDates, $rest->custom_dates);
                            } else {
                                // recurring rest-day: compute all calendar dates in the range
                                $excludedDates = array_merge(
                                    $excludedDates,
                                    $this->getRecurringDates(
                                        $rest->start_date,
                                        $rest->end_date ?? $rest->start_date,
                                        $rest->days_of_week
                                    )
                                );
                            }
                        }
                        // remove duplicates & reindex
                        $excludedDates = array_values(array_unique($excludedDates));
                        Log::info('🚫 excludedDates for new shift', $excludedDates);
                    }

                    $data = [
                        'user_id'     => $userId,
                        'shift_id'    => $shiftId,
                        'type'        => $validated['type'],
                        'start_date'  => $validated['start_date'],
                        'end_date'    => $validated['end_date'] ?? null,
                        'is_rest_day' => $validated['is_rest_day'] ?? false,
                        'days_of_week' => $validated['type'] === 'recurring' ? array_map('strtolower', $validated['days_of_week']) : [],
                        'custom_dates' => $validated['type'] === 'custom' ? $validated['custom_dates'] : [],
                        'excluded_dates' => $excludedDates,
                    ];

                    // Log the data to be saved for custom_dates
                    if ($validated['type'] === 'custom') {
                        Log::info('shiftAssignmentCreate: about to save custom_dates', [
                            'user_id' => $userId,
                            'shift_id' => $shiftId,
                            'custom_dates' => $data['custom_dates'],
                            'data' => $data,
                        ]);
                    }

                    $assignment = ShiftAssignment::create($data);
                    Log::info("Created new shift assignment: {$assignment->id}");

                    UserLog::create([
                        'user_id'        => Auth::guard('web')->id(),
                        'global_user_id' => Auth::guard('global')->id(),
                        'module'         => 'Shift Management',
                        'action'         => 'Create',
                        'description'    => "Created shift assignment (ID: {$assignment->id}) for user {$userId}",
                        'affected_id'    => $assignment->id,
                        'old_data'       => null,
                        'new_data'       => json_encode($assignment->toArray()),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Shift assignments successfully created.',
                'data' => $assignment ?? null,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ShiftAssignment store error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'message' => 'Failed to create shift assignments.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Given a start/end date and an array of weekday names (e.g. ['mon','wed','fri']),
     * return an array of Y-m-d strings for every matching date in that range.
     *
     * @param  string       $startDate   // e.g. '2025-05-01'
     * @param  string|null  $endDate     // e.g. '2025-06-01' or null
     * @param  string[]     $daysOfWeek  // lowercase day abbreviations: mon,tue,wed…
     * @return string[]                 // ['2025-05-04','2025-05-06',…]
     */


    private function getRecurringDates(string $startDate, ?string $endDate, array $daysOfWeek): array
    {
        $start   = Carbon::parse($startDate)->startOfDay();
        // if no endDate, arbitrarily cap at +1 year—or require end_date in validation
        $end     = $endDate
            ? Carbon::parse($endDate)->endOfDay()
            : $start->copy()->addYear();

        $period  = CarbonPeriod::create($start, '1 day', $end);
        $matches = [];

        foreach ($period as $date) {
            // compare day abbreviations, e.g. 'Mon' → 'mon'
            if (in_array(strtolower($date->format('D')), $daysOfWeek, true)) {
                $matches[] = $date->format('Y-m-d');
            }
        }

        return $matches;
    }

    public function shiftListCreate(Request $request)
    {
        $permission = PermissionHelper::get(16);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:shift_lists,name',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'break_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get tenant_id depending on user type (User or GlobalUser)
        if (Auth::guard('web')->check()) {
            $tenantId = Auth::guard('web')->user()->tenant_id ?? null;
        } elseif (Auth::guard('global')->check()) {
            $tenantId = Auth::guard('global')->user()->tenant_id ?? null;
        } else {
            $tenantId = null;
        }

        // Create the shift list entry
        try {
            $shift = ShiftList::create([
                'tenant_id' => $tenantId,
                'branch_id' => $request->branch_id,
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'break_minutes' => $request->break_minutes ?? 0,
                'notes' => $request->notes,
                'created_by_id' => Auth::user()->id,
                'created_by_type' => get_class(Auth::user()),
            ]);

            // Logging Start
            $userId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            // ✨ Log the action
            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Shift Management',
                'action'         => 'Create',
                'description'    => 'Created new shift: ' . $shift->name,
                'affected_id'    => $shift->id,
                'old_data'       => null,
                'new_data'       => json_encode($shift->toArray()),
            ]);


            return response()->json([
                'message' => 'Shift created successfully!',
                'data' => $shift,
            ], 201);
        } catch (Exception $e) {
            Log::error('Shift creation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while saving the shift.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Shift Update
    public function shiftListUpdate(Request $request, $id)
    {

        $permission = PermissionHelper::get(16);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
            'name' => 'required|string|max:255',
            'break_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ], [
            'branch_id.required' => 'Please select branch'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $shift = ShiftList::findOrFail($id);

            $oldData = $shift->toArray();

            $shift->update([
                'branch_id' => $request->branch_id,
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'break_minutes' => $request->break_minutes,
                'notes' => $request->notes,
                'updated_by_type' => Auth::guard('web')->check() ? 'App\Models\User' : 'App\Models\GlobalUser',
                'updated_by_id' => Auth::id(),
            ]);

            // Logging Start
            $userId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            // ✨ Log the action
            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module' => 'Shift List',
                'action' => 'Update',
                'description' => 'Updated shift list: ' . $shift->name,
                'affected_id' => $shift->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($shift->toArray()),
            ]);

            return response()->json([
                'message' => 'Shift updated successfully.',
                'data' => $shift
            ], 200);
        } catch (\Exception $e) {
            Log::error('Shift update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to update shift.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete Shift
    public function shiftListDelete($id)
    {
        $permission = PermissionHelper::get(16);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }
        try {
            $shift = ShiftList::findOrFail($id);

            if ($shift->assignments()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete shift. It is currently assigned to users.'
                ], 400);
            }

            $oldData = $shift->toArray();

            // Logging Start
            $userId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            // ✨ Log the action
            UserLog::create([
                'user_id' => $userId,
                'global_user_id' => $globalUserId,
                'module' => 'Shift List',
                'action' => 'Delete',
                'description' => "Deleted shift list: " . $shift->name,
                'affected_id' => $id,
                'old_data' => json_encode($oldData),
                'new_data' => null,
            ]);

            $shift->delete();

            return response()->json([
                'message' => 'Shift deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Shift deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to delete shift.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDesignationsByDepartments(Request $request)
    {

        $authUser = $this->authUser();
        $departmentIds = $request->query('department_ids', []);

        // if no departments selected, return empty list
        if (empty($departmentIds)) {
            return response()->json([], 200);
        }
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $designations = $accessData['designations']->whereIn('department_id', $departmentIds)
            ->get();

        return response()->json($designations);
    }

    public function getDepartmentsAndEmployeesByBranches(Request $request)
    {

        $authUser = $this->authUser();
        $branchIds = $request->query('branch_ids', []);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        // if no branches selected, return empty collections
        if (empty($branchIds)) {
            Log::info('No branch IDs provided — returning empty arrays.');
            return response()->json([
                'departments' => [],
                'employees'   => [],
                'shifts'      => [],
            ], 200);
        }

        $departments = $accessData['departments']->whereIn('branch_id', $branchIds)->get();

        $employees = EmploymentDetail::whereIn('branch_id', $branchIds)
            ->where('status', '1')
            ->with('user.personalInformation')
            ->get();

        $shifts = ShiftList::whereIn('branch_id', $branchIds)
            ->orWhereNull('branch_id')
            ->get();

        return response()->json([
            'departments' => $departments,
            'employees'   => $employees,
            'shifts'      => $shifts,
        ]);
    }
}
