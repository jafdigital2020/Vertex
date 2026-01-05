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
use App\Helpers\ErrorLogger;
use App\Traits\ResponseTimingTrait;

class ShiftManagementController extends Controller
{
    use ResponseTimingTrait; 

   private function logShiftManagementError(
        string $errorType,
        string $message,
        Request $request,
        ?float $startTime = null,
        ?array $responseData = null
    ): void {
        try {
            $processingTime = null;
            $timingData = null;

            if ($responseData && isset($responseData['timing'])) {
                $timingData = $responseData['timing'];
                $processingTime = $timingData['server_processing_time_ms'] ?? null;
            } elseif ($startTime) {
                $timingData = $this->getTimingData($startTime);
                $processingTime = $timingData ? $timingData['server_processing_time_ms'] : null;
            }

            $errorMessage = sprintf("[%s] %s", $errorType, $message);

            // Get authenticated user
            $authUser = $this->authUser();

            // ===== DEBUG LOG START =====
            Log::debug('logPayrollError - Auth User & Tenant Info', [
                'auth_user_id' => $authUser?->id,
                'auth_user_tenant_id' => $authUser?->tenant_id,
                'tenant_loaded' => isset($authUser->tenant),
                'tenant_name_from_relation' => $authUser->tenant?->tenant_name ?? null,
            ]);

            $clientName = $authUser->tenant?->tenant_name ?? 'Unknown Tenant';
            $clientId   = $authUser->tenant?->id ?? null;

            Log::debug('logPayrollError - Sending to ErrorLogger', [
                'client_name' => $clientName,
                'client_id' => $clientId,
                'error_message' => $errorMessage,
            ]);
            // ===== DEBUG LOG END =====

            // Log to remote system
            ErrorLogger::logToRemoteSystem(
                $errorMessage,
                $clientName,
                $clientId,
                $timingData
            );

            // Local Laravel log
            Log::error($errorType, [
                'clean_message' => $message,
                'full_error' => $responseData['full_error'] ?? null,
                'user_id' => $authUser->id ?? null,
                'client_name' => $clientName,
                'client_id' => $clientId,
                'processing_time_ms' => $processingTime,
                'url' => $request->fullUrl(),
                'request_data' => $request->except(['password', 'token', 'api_key'])
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log error', [
                'original_error' => $message,
                'logging_error' => $e->getMessage()
            ]);
        }
    }


    public function authUser()
    {
        $user = null;
        
        if (Auth::guard('global')->check()) {
            $user = Auth::guard('global')->user();
        } else {
            $user = Auth::guard('web')->user();
        }
        
        // Load tenant relationship if user exists
        if ($user) {
            $user->load('tenant');
        }
        
        return $user;
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

        $html = view('tenant.attendance.shiftmanagement.shiftList_filter', compact('shifts', 'permission'))->render();
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
        $shifts = $accessData['shiftList']->where('tenant_id', $tenantId)->get();
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

    // Shift Assignment
    public function shiftAssignmentCreate(Request $request)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
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

                    // Find and remove all conflicting regular shift assignments for rest day dates
                    if ($validated['type'] === 'custom') {
                        // Find all shift assignments that conflict with these custom rest days
                        $conflictingShifts = ShiftAssignment::where('user_id', $userId)
                            ->where('is_rest_day', false)
                            ->get();

                        foreach ($conflictingShifts as $conflict) {
                            if ($conflict->type === 'custom') {
                                // For custom shifts, remove any dates that match our rest days
                                $remainingDates = array_diff($conflict->custom_dates ?? [], $dates);
                                if (empty($remainingDates)) {
                                    $conflict->delete();
                                    Log::info("Deleted custom shift {$conflict->id} as all dates now fall on rest days");
                                } else {
                                    $conflict->custom_dates = array_values($remainingDates);
                                    $conflict->save();
                                    Log::info("Updated custom shift {$conflict->id} to exclude rest days");
                                }
                            } else if ($conflict->type === 'recurring') {
                                // For recurring shifts, add rest day dates to excluded_dates
                                $conflictDates = [];

                                foreach ($dates as $restDate) {
                                    $carbonDate = Carbon::parse($restDate);
                                    $dayOfWeek = strtolower($carbonDate->format('D'));

                                    // Check if this date falls on a recurring shift day
                                    if (
                                        in_array($dayOfWeek, $conflict->days_of_week ?? []) &&
                                        $carbonDate->gte(Carbon::parse($conflict->start_date)) &&
                                        ($conflict->end_date === null || $carbonDate->lte(Carbon::parse($conflict->end_date)))
                                    ) {
                                        $conflictDates[] = $restDate;
                                    }
                                }

                                if (!empty($conflictDates)) {
                                    $excludedDates = $conflict->excluded_dates ?? [];
                                    $conflict->excluded_dates = array_values(array_unique(array_merge($excludedDates, $conflictDates)));
                                    $conflict->save();
                                    Log::info("Updated recurring shift {$conflict->id} to exclude rest days", [
                                        'excluded_dates' => $conflictDates
                                    ]);
                                }
                            }
                        }
                    } else if ($validated['type'] === 'recurring') {
                        // For recurring rest days, handle conflicts with all shifts
                        $conflictingShifts = ShiftAssignment::where('user_id', $userId)
                            ->where('is_rest_day', false)
                            ->get();

                        $startDate = Carbon::parse($validated['start_date']);
                        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : null;

                        foreach ($conflictingShifts as $conflict) {
                            if ($conflict->type === 'recurring') {
                                // Find overlap in days of week
                                $overlappingDays = array_intersect($conflict->days_of_week ?? [], $days);

                                if (!empty($overlappingDays)) {
                                    // Calculate which dates will be rest days
                                    $restDates = $this->getRecurringDates(
                                        $validated['start_date'],
                                        $validated['end_date'],
                                        $overlappingDays
                                    );

                                    // Add these dates to excluded_dates
                                    $excludedDates = $conflict->excluded_dates ?? [];
                                    $conflict->excluded_dates = array_values(array_unique(array_merge($excludedDates, $restDates)));
                                    $conflict->save();
                                    Log::info("Added rest days to recurring shift's excluded dates", [
                                        'shift_id' => $conflict->id,
                                        'rest_days' => count($restDates)
                                    ]);
                                }
                            } else if ($conflict->type === 'custom') {
                                // For custom shifts, check each date if it falls on a rest day
                                $datesToRemove = [];

                                foreach ($conflict->custom_dates ?? [] as $customDate) {
                                    $carbonDate = Carbon::parse($customDate);
                                    $dayOfWeek = strtolower($carbonDate->format('D'));

                                    // Check if this custom date falls on a recurring rest day
                                    if (
                                        in_array($dayOfWeek, $days) &&
                                        $carbonDate->gte($startDate) &&
                                        ($endDate === null || $carbonDate->lte($endDate))
                                    ) {
                                        $datesToRemove[] = $customDate;
                                    }
                                }

                                if (!empty($datesToRemove)) {
                                    $remainingDates = array_diff($conflict->custom_dates ?? [], $datesToRemove);
                                    if (empty($remainingDates)) {
                                        $conflict->delete();
                                        Log::info("Deleted custom shift {$conflict->id} as all dates now fall on rest days");
                                    } else {
                                        $conflict->custom_dates = array_values($remainingDates);
                                        $conflict->save();
                                        Log::info("Updated custom shift {$conflict->id} to exclude rest days", [
                                            'removed_dates' => $datesToRemove
                                        ]);
                                    }
                                }
                            }
                        }
                    }

                    // ✅ FIXED: Better conflict detection and resolution for rest days
                    $existingRestDays = ShiftAssignment::where('user_id', $userId)
                        ->where('is_rest_day', true)
                        ->get();

                    foreach ($existingRestDays as $existingRest) {
                        if ($validated['type'] === 'custom' && $existingRest->type === 'custom') {
                            // Custom vs Custom: Remove overlapping dates from existing
                            $overlappingDates = array_intersect($existingRest->custom_dates ?? [], $dates);
                            if (!empty($overlappingDates)) {
                                $remainingDates = array_diff($existingRest->custom_dates ?? [], $overlappingDates);
                                if (empty($remainingDates)) {
                                    $existingRest->delete();
                                } else {
                                    $existingRest->custom_dates = array_values($remainingDates);
                                    $existingRest->save();
                                }
                            }
                        } elseif ($validated['type'] === 'custom' && $existingRest->type === 'recurring') {
                            // Custom vs Recurring: Add custom dates to recurring's excluded_dates
                            $conflictingDates = [];
                            foreach ($dates as $date) {
                                $carbonDate = Carbon::parse($date);
                                $dayOfWeek = strtolower($carbonDate->format('D'));

                                if (
                                    $carbonDate->gte(Carbon::parse($existingRest->start_date)) &&
                                    ($existingRest->end_date === null || $carbonDate->lte(Carbon::parse($existingRest->end_date))) &&
                                    in_array($dayOfWeek, $existingRest->days_of_week ?? []) &&
                                    !in_array($date, $existingRest->excluded_dates ?? [])
                                ) {
                                    $conflictingDates[] = $date;
                                }
                            }

                            if (!empty($conflictingDates)) {
                                $currentExcluded = $existingRest->excluded_dates ?? [];
                                $existingRest->excluded_dates = array_values(array_unique(array_merge($currentExcluded, $conflictingDates)));
                                $existingRest->save();
                            }
                        } elseif ($validated['type'] === 'recurring' && $existingRest->type === 'custom') {
                            // ✅ FIXED: Recurring vs Custom - Remove conflicting custom dates
                            $conflictingDates = [];
                            foreach ($existingRest->custom_dates ?? [] as $customDate) {
                                $carbonDate = Carbon::parse($customDate);
                                $dayOfWeek = strtolower($carbonDate->format('D'));

                                // Check if custom date falls on the new recurring rest day
                                if (
                                    in_array($dayOfWeek, $days) &&
                                    $carbonDate->gte(Carbon::parse($validated['start_date'])) &&
                                    ($validated['end_date'] === null || $carbonDate->lte(Carbon::parse($validated['end_date'])))
                                ) {
                                    $conflictingDates[] = $customDate;
                                }
                            }

                            if (!empty($conflictingDates)) {
                                $remainingDates = array_diff($existingRest->custom_dates ?? [], $conflictingDates);
                                if (empty($remainingDates)) {
                                    // ✅ DELETE the custom rest day assignment if no dates remain
                                    $existingRest->delete();
                                    Log::info("Deleted custom rest day assignment {$existingRest->id} - all dates conflict with new recurring rest day");
                                } else {
                                    // Update with remaining dates
                                    $existingRest->custom_dates = array_values($remainingDates);
                                    $existingRest->save();
                                    Log::info("Updated custom rest day assignment {$existingRest->id} - removed conflicting dates", [
                                        'removed_dates' => $conflictingDates,
                                        'remaining_dates' => $remainingDates
                                    ]);
                                }
                            }
                        } elseif ($validated['type'] === 'recurring' && $existingRest->type === 'recurring') {
                            // Recurring vs Recurring: Remove overlapping days from existing
                            $overlappingDays = array_intersect($existingRest->days_of_week ?? [], $days);
                            if (!empty($overlappingDays)) {
                                $remainingDays = array_diff($existingRest->days_of_week ?? [], $overlappingDays);
                                if (empty($remainingDays)) {
                                    $existingRest->delete();
                                } else {
                                    $existingRest->days_of_week = array_values($remainingDays);
                                    $existingRest->save();
                                }
                            }
                        }
                    }

                    // ✅ ONLY create new rest day assignment after cleaning up conflicts
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

                    continue; // Move to next user
                }

                foreach ($validated['shift_id'] as $shiftId) {

                    Log::info('▶ entering shift loop', [
                        'user_id'         => $userId,
                        'shift_id'        => $shiftId,
                        'skip_rest_check' => $validated['skip_rest_check'] ?? null,
                        'override'        => $validated['override']        ?? null,
                    ]);

                    $newShift = ShiftList::find($shiftId);
                    $isNewFlexible = $newShift->is_flexible ?? false;
                    $newStartTime = Carbon::parse($newShift->start_time);
                    $newEndTime = Carbon::parse($newShift->end_time);

                    $conflictingAssignments = collect();

                    $restDayConflicts = ShiftAssignment::where('user_id', $userId)
                        ->where('is_rest_day', true)
                        ->get();

                    // Filter rest day conflicts to only check dates that will actually be affected
                    $actualRestDayConflicts = collect();
                    foreach ($restDayConflicts as $restDay) {
                        $hasConflict = false;

                        if ($validated['type'] === 'custom') {
                            // Check if any of the custom dates conflict with this rest day
                            foreach ($validated['custom_dates'] as $customDate) {
                                if ($restDay->type === 'custom') {
                                    if (in_array($customDate, $restDay->custom_dates ?? [])) {
                                        $hasConflict = true;
                                        break;
                                    }
                                } elseif ($restDay->type === 'recurring') {
                                    $carbonDate = Carbon::parse($customDate);
                                    $dayOfWeek = strtolower($carbonDate->format('D'));

                                    if (
                                        $carbonDate->gte(Carbon::parse($restDay->start_date)) &&
                                        ($restDay->end_date === null || $carbonDate->lte(Carbon::parse($restDay->end_date))) &&
                                        in_array($dayOfWeek, $restDay->days_of_week ?? []) &&
                                        !in_array($customDate, $restDay->excluded_dates ?? [])
                                    ) {
                                        $hasConflict = true;
                                        break;
                                    }
                                }
                            }
                        } elseif ($validated['type'] === 'recurring') {
                            // Check if any of the recurring days conflict with this rest day
                            $newDays = array_map('strtolower', $validated['days_of_week']);

                            if ($restDay->type === 'recurring') {
                                $overlappingDays = array_intersect($restDay->days_of_week ?? [], $newDays);
                                if (!empty($overlappingDays)) {
                                    $hasConflict = true;
                                }
                            } elseif ($restDay->type === 'custom') {
                                // Check if any custom rest days fall on the new recurring days
                                foreach ($restDay->custom_dates ?? [] as $customDate) {
                                    $carbonDate = Carbon::parse($customDate);
                                    $dayOfWeek = strtolower($carbonDate->format('D'));

                                    if (
                                        in_array($dayOfWeek, $newDays) &&
                                        $carbonDate->gte(Carbon::parse($validated['start_date'])) &&
                                        ($validated['end_date'] === null || $carbonDate->lte(Carbon::parse($validated['end_date'])))
                                    ) {
                                        $hasConflict = true;
                                        break;
                                    }
                                }
                            }
                        }

                        if ($hasConflict) {
                            $actualRestDayConflicts->push($restDay);
                        }
                    }

                    Log::info('▶ actualRestDayConflicts IDs', $actualRestDayConflicts->pluck('id')->toArray());

                    if (
                        $actualRestDayConflicts->isNotEmpty()
                        && empty($validated['override'])
                        && empty($validated['skip_rest_check'])
                    ) {
                        DB::rollBack();
                        return response()->json([
                            'message'           => 'This user already has a rest day scheduled on this date. Do you want to override it or skip?',
                            'requires_override' => true,
                        ], 409);
                    }

                    // FOR SHIFT ASSIGNMENTS - Handle rest day conflicts by exclusion, not deletion
                    if (!empty($validated['skip_rest_check']) && $actualRestDayConflicts->isNotEmpty()) {
                        foreach ($actualRestDayConflicts as $restDay) {
                            if ($validated['type'] === 'custom') {
                                // For custom shift assignments, add only conflicting dates to excluded_dates
                                foreach ($validated['custom_dates'] as $customDate) {
                                    $shouldExclude = false;

                                    if ($restDay->type === 'custom') {
                                        if (in_array($customDate, $restDay->custom_dates ?? [])) {
                                            $shouldExclude = true;
                                        }
                                    } elseif ($restDay->type === 'recurring') {
                                        $carbonDate = Carbon::parse($customDate);
                                        $dayOfWeek = strtolower($carbonDate->format('D'));

                                        if (
                                            $carbonDate->gte(Carbon::parse($restDay->start_date)) &&
                                            ($restDay->end_date === null || $carbonDate->lte(Carbon::parse($restDay->end_date))) &&
                                            in_array($dayOfWeek, $restDay->days_of_week ?? []) &&
                                            !in_array($customDate, $restDay->excluded_dates ?? [])
                                        ) {
                                            $shouldExclude = true;
                                        }
                                    }

                                    if ($shouldExclude) {
                                        $currentExcluded = $restDay->excluded_dates ?? [];
                                        if (!in_array($customDate, $currentExcluded)) {
                                            $restDay->excluded_dates = array_values(array_merge($currentExcluded, [$customDate]));
                                            $restDay->save();
                                        }
                                    }
                                }
                            } elseif ($validated['type'] === 'recurring') {
                                // For recurring shift assignments, add overlapping dates to excluded_dates
                                if ($restDay->type === 'recurring') {
                                    $overlappingDays = array_intersect($restDay->days_of_week ?? [], array_map('strtolower', $validated['days_of_week']));
                                    if (!empty($overlappingDays)) {
                                        $excludedDates = $this->getRecurringDates(
                                            max($validated['start_date'], $restDay->start_date),
                                            min(
                                                $validated['end_date'] ?? Carbon::now()->addYear()->format('Y-m-d'),
                                                $restDay->end_date ?? Carbon::now()->addYear()->format('Y-m-d')
                                            ),
                                            $overlappingDays
                                        );

                                        $currentExcluded = $restDay->excluded_dates ?? [];
                                        $restDay->excluded_dates = array_values(array_unique(array_merge($currentExcluded, $excludedDates)));
                                        $restDay->save();
                                    }
                                } elseif ($restDay->type === 'custom') {
                                    // Check which custom dates fall on the new recurring days and exclude them
                                    $datesToExclude = [];
                                    $newDays = array_map('strtolower', $validated['days_of_week']);

                                    foreach ($restDay->custom_dates ?? [] as $customDate) {
                                        $carbonDate = Carbon::parse($customDate);
                                        $dayOfWeek = strtolower($carbonDate->format('D'));

                                        if (
                                            in_array($dayOfWeek, $newDays) &&
                                            $carbonDate->gte(Carbon::parse($validated['start_date'])) &&
                                            ($validated['end_date'] === null || $carbonDate->lte(Carbon::parse($validated['end_date'])))
                                        ) {
                                            $datesToExclude[] = $customDate;
                                        }
                                    }

                                    if (!empty($datesToExclude)) {
                                        $remainingDates = array_diff($restDay->custom_dates ?? [], $datesToExclude);
                                        if (empty($remainingDates)) {
                                            $restDay->delete();
                                        } else {
                                            $restDay->custom_dates = array_values($remainingDates);
                                            $restDay->save();
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Optional override cleanup - only if explicitly requested
                    if (!empty($validated['override']) && $actualRestDayConflicts->isNotEmpty()) {
                        foreach ($actualRestDayConflicts as $restDay) {
                            $restDay->delete();
                        }
                    }

                    // Handle flexible vs fixed shift conflicts automatically
                    $allExistingShifts = ShiftAssignment::where('user_id', $userId)
                        ->where('is_rest_day', false)
                        ->whereNotNull('shift_id')
                        ->with('shift')
                        ->get();

                    foreach ($allExistingShifts as $existingAssignment) {
                        $existingShift = $existingAssignment->shift;
                        if (!$existingShift) continue;

                        $existingIsFlexible = $existingShift->is_flexible ?? false;

                        // Skip if both are same type (flexible vs flexible or fixed vs fixed)
                        // These will be handled by the normal conflict logic below
                        if ($isNewFlexible === $existingIsFlexible) {
                            continue;
                        }

                        // Check if there's overlap in dates/days
                        $hasOverlap = false;

                        if ($validated['type'] === 'custom' && $existingAssignment->type === 'custom') {
                            $overlappingDates = array_intersect($existingAssignment->custom_dates ?? [], $validated['custom_dates']);
                            $hasOverlap = !empty($overlappingDates);

                            if ($hasOverlap) {
                                $remainingDates = array_diff($existingAssignment->custom_dates ?? [], $validated['custom_dates']);
                                if (empty($remainingDates)) {
                                    $existingAssignment->delete();
                                    Log::info("Auto-deleted existing " . ($existingIsFlexible ? "flexible" : "fixed") . " custom shift {$existingAssignment->id} - overridden by new " . ($isNewFlexible ? "flexible" : "fixed") . " shift");
                                } else {
                                    $existingAssignment->custom_dates = array_values($remainingDates);
                                    $existingAssignment->save();
                                    Log::info("Auto-updated existing " . ($existingIsFlexible ? "flexible" : "fixed") . " custom shift {$existingAssignment->id} - removed overlapping dates");
                                }
                            }
                        } elseif ($validated['type'] === 'recurring' && $existingAssignment->type === 'recurring') {
                            $newDays = array_map('strtolower', $validated['days_of_week']);
                            $overlappingDays = array_intersect($existingAssignment->days_of_week ?? [], $newDays);
                            $hasOverlap = !empty($overlappingDays);

                            if ($hasOverlap) {
                                $remainingDays = array_diff($existingAssignment->days_of_week ?? [], $newDays);
                                if (empty($remainingDays)) {
                                    $existingAssignment->delete();
                                    Log::info("Auto-deleted existing " . ($existingIsFlexible ? "flexible" : "fixed") . " recurring shift {$existingAssignment->id} - overridden by new " . ($isNewFlexible ? "flexible" : "fixed") . " shift");
                                } else {
                                    $existingAssignment->days_of_week = array_values($remainingDays);
                                    $existingAssignment->save();
                                    Log::info("Auto-updated existing " . ($existingIsFlexible ? "flexible" : "fixed") . " recurring shift {$existingAssignment->id} - removed overlapping days");
                                }
                            }
                        } elseif ($validated['type'] === 'custom' && $existingAssignment->type === 'recurring') {
                            // Check if custom dates fall on recurring days
                            foreach ($validated['custom_dates'] as $customDate) {
                                $carbonDate = Carbon::parse($customDate);
                                $dayOfWeek = strtolower($carbonDate->format('D'));

                                if (in_array($dayOfWeek, $existingAssignment->days_of_week ?? [])) {
                                    $hasOverlap = true;
                                    $currentExcluded = $existingAssignment->excluded_dates ?? [];
                                    if (!in_array($customDate, $currentExcluded)) {
                                        $existingAssignment->excluded_dates = array_values(array_merge($currentExcluded, [$customDate]));
                                        $existingAssignment->save();
                                        Log::info("Auto-excluded date {$customDate} from existing " . ($existingIsFlexible ? "flexible" : "fixed") . " recurring shift {$existingAssignment->id}");
                                    }
                                }
                            }
                        } elseif ($validated['type'] === 'recurring' && $existingAssignment->type === 'custom') {
                            // Check if custom dates fall on new recurring days
                            $newDays = array_map('strtolower', $validated['days_of_week']);
                            $datesToRemove = [];

                            foreach ($existingAssignment->custom_dates ?? [] as $customDate) {
                                $carbonDate = Carbon::parse($customDate);
                                $dayOfWeek = strtolower($carbonDate->format('D'));

                                if (in_array($dayOfWeek, $newDays)) {
                                    $hasOverlap = true;
                                    $datesToRemove[] = $customDate;
                                }
                            }

                            if (!empty($datesToRemove)) {
                                $remainingDates = array_diff($existingAssignment->custom_dates ?? [], $datesToRemove);
                                if (empty($remainingDates)) {
                                    $existingAssignment->delete();
                                    Log::info("Auto-deleted existing " . ($existingIsFlexible ? "flexible" : "fixed") . " custom shift {$existingAssignment->id} - all dates overridden");
                                } else {
                                    $existingAssignment->custom_dates = array_values($remainingDates);
                                    $existingAssignment->save();
                                    Log::info("Auto-updated existing " . ($existingIsFlexible ? "flexible" : "fixed") . " custom shift {$existingAssignment->id} - removed conflicting dates");
                                }
                            }
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
                            ->where('is_rest_day', false)
                            ->whereNotNull('shift_id')
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
                            $toDelete = $conflictingAssignments->filter(function ($conflict) use ($newStartTime, $newEndTime, $isNewFlexible) {
                                $existing = ShiftList::find($conflict->shift_id);
                                if (! $existing) return false;

                                // If new shift is flexible, override any existing flexible shifts automatically
                                if ($isNewFlexible && $existing->is_flexible) {
                                    return true;
                                }

                                $existStart = Carbon::parse($existing->start_time);
                                $existEnd   = Carbon::parse($existing->end_time);
                                return $newStartTime < $existEnd && $newEndTime > $existStart;
                            });

                            foreach ($toDelete as $del) {
                                $del->delete();
                            }

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

                        $customConflictDates = [];
                        $limitDate = $endDate ?? $startDate->copy()->addDays(30);

                        for ($date = $startDate->copy(); $date->lte($limitDate); $date->addDay()) {
                            $day = strtolower($date->format('D'));
                            if (in_array($day, array_map('strtolower', $validated['days_of_week']))) {
                                $customConflictDates[] = $date->format('Y-m-d');
                            }
                        }

                        $customConflicts = ShiftAssignment::where('user_id', $userId)
                            ->where('is_rest_day', false)
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

                    $conflictingAssignments = $conflictingAssignments->filter(function ($conflict) use ($newStartTime, $newEndTime, $isNewFlexible) {
                        if ($conflict->is_rest_day || is_null($conflict->shift_id)) {
                            return false;
                        }

                        $existingShift = ShiftList::find($conflict->shift_id);
                        if (! $existingShift) {
                            return false;
                        }

                        // If new shift is flexible, override any existing flexible shifts automatically
                        if ($isNewFlexible && $existingShift->is_flexible) {
                            return true;
                        }

                        $existingStart = Carbon::parse($existingShift->start_time);
                        $existingEnd   = Carbon::parse($existingShift->end_time);

                        return $newStartTime < $existingEnd && $newEndTime > $existingStart;
                    });

                    Log::info("Conflicting shifts for user {$userId}: " . $conflictingAssignments->pluck('id')->join(', '));

                    $conflictingAssignments = $conflictingAssignments
                        ->filter(fn($a) => ! $a->is_rest_day && $a->shift_id !== null);

                    if (
                        $conflictingAssignments->count() > 0
                        && empty($validated['override'])
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

                    $data = [
                        'user_id'     => $userId,
                        'shift_id'    => $shiftId,
                        'type'        => $validated['type'],
                        'start_date'  => $validated['start_date'],
                        'end_date'    => $validated['end_date'] ?? null,
                        'is_rest_day' => $validated['is_rest_day'] ?? false,
                        'days_of_week' => $validated['type'] === 'recurring' ? array_map('strtolower', $validated['days_of_week']) : [],
                        'custom_dates' => $validated['type'] === 'custom' ? $validated['custom_dates'] : [],
                        'excluded_dates' => [],
                    ];

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

            $cleanMessage = "Failed to create shift assignments.";

            $this->logShiftManagementError(
                '[ERROR_CREATING_SHIFT_ASSIGNMENTS]',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
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
        $startTime = microtime(true);
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(16);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:shift_lists,name',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'break_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500',
            'maximum_allowed_hours' => 'nullable|integer|min:0',
            'grace_period' => 'nullable|integer|min:0',
            'is_flexible' => 'nullable|boolean',
            'allowed_minutes_before_clock_in' => 'nullable|integer|min:0',
            'allow_extra_hours' => 'nullable|boolean',
        ]);

        $isFlexible = $request->has('is_flexible') ? $request->is_flexible : false;
        $allowExtraHours = $request->has('allow_extra_hours') ? $request->allow_extra_hours : false;

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $tenantId = Auth::user()->tenant_id ?? null;

        // Create the shift list entry
        try {
            $shift = ShiftList::create([
                'tenant_id' => $tenantId,
                'branch_id' => $request->branch_id,
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'maximum_allowed_hours' => $request->maximum_allowed_hours ?? 0,
                'grace_period' => $request->grace_period ?? 0,
                'break_minutes' => $request->break_minutes ?? 0,
                'is_flexible' => $isFlexible,
                'notes' => $request->notes,
                'allowed_minutes_before_clock_in' => $request->allowed_minutes_before_clock_in ?? 0,
                'allow_extra_hours' => $allowExtraHours,
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
            DB::rollBack();

            $cleanMessage = "An error occurred while saving the shift. Please try again later.";

            $this->logShiftManagementError(
                '[ERROR_CREATING_SHIFT]',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
        }
    }

    // Shift Update
    public function shiftListUpdate(Request $request, $id)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(16);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'break_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500',
            'maximum_allowed_hours' => 'nullable|integer|min:0',
            'grace_period' => 'nullable|integer|min:0',
            'is_flexible' => 'nullable|boolean',
            'allowed_minutes_before_clock_in' => 'nullable|integer|min:0',
            'allow_extra_hours' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $shift = ShiftList::findOrFail($id);

            // Flexible check
            $isFlexible = $request->has('is_flexible') ? $request->is_flexible : false;

            $allowExtraHours = $request->has('allow_extra_hours') ? $request->allow_extra_hours : false;

            $oldData = $shift->toArray();

            $shift->update([
                'branch_id' => $request->branch_id,
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'is_flexible' => $isFlexible,
                'maximum_allowed_hours' => $request->maximum_allowed_hours,
                'grace_period' => $request->grace_period,
                'break_minutes' => $request->break_minutes,
                'notes' => $request->notes,
                'allowed_minutes_before_clock_in' => $request->allowed_minutes_before_clock_in ?? 0,
                'allow_extra_hours' => $allowExtraHours,
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
            DB::rollBack();

            $cleanMessage = "Failed to update shift. Please try again later.";

            $this->logShiftManagementError(
                'Shift update error',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
        }
    }

    // Delete Shift
    public function shiftListDelete(Request $request, $id)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
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
            DB::rollBack();

            $cleanMessage = "Failed to delete shift. Please try again later.";

            $this->logShiftManagementError(
                '[ERROR_DELETING_SHIFT]',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
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

    // Bulk Delete Shift Assignments
    public function bulkDeleteShiftAssignments(Request $request)
    {
        
        $startTime = microtime(true);
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(16);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid user selection',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $userIds = $request->user_ids;
            $deletedCount = 0;

            foreach ($userIds as $userId) {
                // Get all shift assignments for this user
                $assignments = ShiftAssignment::where('user_id', $userId)->get();

                foreach ($assignments as $assignment) {
                    $oldData = $assignment->toArray();

                    // Log each deletion
                    UserLog::create([
                        'user_id' => Auth::guard('web')->id(),
                        'global_user_id' => Auth::guard('global')->id(),
                        'module' => 'Shift Management',
                        'action' => 'Bulk Delete',
                        'description' => "Deleted all shift assignments for user {$userId}",
                        'affected_id' => $assignment->id,
                        'old_data' => json_encode($oldData),
                        'new_data' => null,
                    ]);

                    $assignment->delete();
                    $deletedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Successfully deleted {$deletedCount} shift assignment(s) for " . count($userIds) . " user(s).",
                'deleted_count' => $deletedCount,
                'user_count' => count($userIds)
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();

            $cleanMessage = "Failed to delete shift assignments. Please try again later.";

            $this->logShiftManagementError(
                '[ERROR_BULK_DELETING_SHIFT_ASSIGNMENTS]',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
        }
    }
}
