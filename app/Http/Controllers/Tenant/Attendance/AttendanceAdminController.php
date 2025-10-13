<?php

namespace App\Http\Controllers\Tenant\Attendance;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Holiday;
use App\Models\UserLog;
use App\Models\Overtime;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\BulkAttendance;
use App\Models\EmploymentDetail;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Jobs\BulkImportAttendanceJob;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AttendanceAdminController extends Controller
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

        $dateRange   = $request->input('dateRange');
        $branch      = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation');
        $status      = $request->input('status');

        $query  = $accessData['attendances'];

        $start = null;
        $end   = null;
        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end   = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('attendance_date', [$start, $end]);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Invalid date range format.'
                ]);
            }
        }
        if ($branch) {
            $query->whereHas('user.employmentDetail', fn($q) => $q->where('branch_id', $branch));
        }
        if ($department) {
            $query->whereHas('user.employmentDetail', fn($q) => $q->where('department_id', $department));
        }
        if ($designation) {
            $query->whereHas('user.employmentDetail', fn($q) => $q->where('designation_id', $designation));
        }
        if ($status) {
            $query->where('status', $status);
        }

        $userAttendances = $query->get();

        $baseTotals = Attendance::query()
            ->when($start && $end, fn($q) => $q->whereBetween('attendance_date', [$start, $end]))
            ->when($branch, fn($q) => $q->whereHas('user.employmentDetail', fn($edQ) => $edQ->where('branch_id', $branch)))
            ->when($department, fn($q) => $q->whereHas('user.employmentDetail', fn($edQ) => $edQ->where('department_id', $department)))
            ->when($designation, fn($q) => $q->whereHas('user.employmentDetail', fn($edQ) => $edQ->where('designation_id', $designation)))
            ->whereHas('user', function ($userQ) use ($tenantId) {
                $userQ->where('tenant_id', $tenantId)
                    ->whereHas('employmentDetail', fn($edQ) => $edQ->where('status', '1'));
            });

        $totalPresent = (clone $baseTotals)->whereIn('status', ['present', 'late'])->count();
        $totalLate    = (clone $baseTotals)->where('status', 'late')->count();
        $totalAbsent  = (clone $baseTotals)->where('status', 'absent')->count();

        // Render view
        $html = view('tenant.attendance.attendance.adminattendance_filter', compact(
            'userAttendances',
            'permission',
        ))->render();

        return response()->json([
            'status'       => 'success',
            'html'         => $html,
            'totalPresent' => $totalPresent,
            'totalLate'    => $totalLate,
            'totalAbsent'  => $totalAbsent,
        ]);
    }

    public function adminAttendanceIndex(Request $request)
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

        // Branch Users
        $authUserBranch = $authUser->employmentDetail->branch_id ?? null;
        $branchUsers = User::where('tenant_id', $tenantId)
            ->whereHas('employmentDetail', function ($query) use ($authUserBranch) {
                $query->where('branch_id', $authUserBranch)
                    ->where('status', '1');
            })
            ->get();

        $userAttendances = $accessData['attendances']
            ->where('attendance_date', Carbon::today()->toDateString())
            ->get();

        // All Attendance Data
        $userAllAttendances = Attendance::with([
            'user.employmentDetail',
            'user.personalInformation',
            'shift.branch'
            ])
            ->whereHas('user', function ($userQ) use ($tenantId) {
            $userQ->where('tenant_id', $tenantId)
            ->whereHas('employmentDetail', fn($edQ) => $edQ->where('status', '1'));
            })
            ->orderBy('attendance_date', 'desc')
            ->get();

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

        // Api Route
        if ($request->wantsJson()) {
            return response()->json([
                'status'         => true,
                'userAttendance' => $userAttendances,
                'total_present'   => $totalPresent,
                'total_late' => $totalLate,
                'total_absent' => $totalAbsent,
                'allData' => $userAllAttendances,
            ]);
        }

        // Web Route
        return view('tenant.attendance.attendance.adminattendance', [
            'userAttendances' => $userAttendances,
            'totalPresent'   => $totalPresent,
            'totalLate' => $totalLate,
            'totalAbsent' => $totalAbsent,
            'permission' => $permission,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,
            'branchUsers' => $branchUsers,
        ]);
    }

    // Add Attendance
    public function adminAttendanceCreate(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_ids'               => 'required|array',
                'user_ids.*'             => 'exists:users,id',
                'attendance_date'        => 'required|date',
                'date_time_in'           => 'required|date_format:Y-m-d\TH:i',
                'date_time_out'          => 'required|date_format:Y-m-d\TH:i',
                'total_late_minutes'     => 'nullable|integer',
                'total_work_minutes'     => 'nullable|integer',
                'total_night_diff_minutes' => 'nullable|integer',
                'status'                 => 'nullable|string',
            ]);


            $userIds = $validated['user_ids'];

            // Handle "Select All" option
            if (in_array('all', $userIds)) {

                $authUser = $this->authUser();
                $authUserBranch = $authUser->employmentDetail->branch_id ?? null;
                $tenantId = $authUser->tenant_id ?? null;

                $userIds = User::where('tenant_id', $tenantId)
                    ->whereHas('employmentDetail', function ($query) use ($authUserBranch) {
                        $query->where('branch_id', $authUserBranch)
                            ->where('status', '1');
                    })
                    ->pluck('id')
                    ->toArray();
            }

            $attendanceRecords = [];
            $skippedCount = 0;

            foreach ($userIds as $userId) {

                // Check if attendance already exists for this user and date
                $existingAttendance = Attendance::where('user_id', $userId)
                    ->where('attendance_date', $validated['attendance_date'])
                    ->first();

                if ($existingAttendance) {
                    $skippedCount++;
                    continue; // Skip if attendance already exists
                }

                try {
                    $attendance = Attendance::create([
                        'user_id'                  => $userId,
                        'attendance_date'          => $validated['attendance_date'],
                        'date_time_in'             => Carbon::parse($validated['date_time_in'])->format('H:i'),
                        'date_time_out'            => Carbon::parse($validated['date_time_out'])->format('H:i'),
                        'total_late_minutes'       => $validated['total_late_minutes'] ?? 0,
                        'total_work_minutes'       => $validated['total_work_minutes'] ?? 0,
                        'total_night_diff_minutes' => $validated['total_night_diff_minutes'] ?? 0,
                        'status'                   => $validated['status'] ?? 'present',
                    ]);

                    $attendanceRecords[] = $attendance;

                    // Logging for each record
                    $logUserId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
                    $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

                    UserLog::create([
                        'user_id'        => $logUserId,
                        'global_user_id' => $globalUserId,
                        'module'         => 'Attendance Management',
                        'action'         => 'Create',
                        'description'    => 'Created new attendance record.',
                        'affected_id'    => $attendance->id,
                        'old_data'       => null,
                        'new_data'       => json_encode($attendance->toArray()),
                    ]);

                    Log::info('User log created for attendance', [
                        'attendance_id' => $attendance->id,
                        'log_user_id' => $logUserId,
                        'global_user_id' => $globalUserId
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error creating attendance for user', [
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e; // Re-throw to be caught by outer catch
                }
            }

            $createdCount = count($attendanceRecords);

            return response()->json([
                'status'  => true,
                'message' => "Successfully created {$createdCount} attendance record(s)." .
                    ($skippedCount > 0 ? " {$skippedCount} record(s) were skipped (already exist)." : ""),
                'data'    => $attendanceRecords,
            ]);
        } catch (ValidationException $e) {
            Log::warning('Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Please check your input and try again.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating attendance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while creating the attendance record. Please try again later.',
            ], 500);
        }
    }


    // Edit and Update Attendance
    public function adminAttendanceEdit(Request $request, $id)
    {
        $permission = PermissionHelper::get(14);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have the permission to  update.'
            ], 403);
        }

        try {
            $data = $request->validate([
                'attendance_date'    => 'required|date',
                'date_time_in'       => 'required|date_format:H:i',
                'date_time_out'      => 'required|date_format:H:i',
                'total_late_minutes' => 'nullable|integer',
                'total_work_minutes' => 'nullable|integer',
                'total_night_diff_minutes' => 'nullable|integer',
                'status'             => 'nullable|string',
                'total_undertime_minutes' => 'nullable|integer',
            ]);

            $attendance = Attendance::findOrFail($id);
            $oldData = $attendance->toArray();

            // Set status to "edited"
            $data['status'] = 'edited';

            $attendance->update($data);

            // Logging
            $userId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
            $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Attendance Management',
                'action'         => 'Update',
                'description'    => 'Updated attendance record.',
                'affected_id'    => $attendance->id,
                'old_data'       => json_encode($oldData),
                'new_data'       => json_encode($attendance->toArray()),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Attendance updated successfully.',
                'data'    => $attendance,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Please check your input and try again.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'The attendance record you are trying to update was not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating attendance: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while updating the attendance. Please try again later.',
            ], 500);
        }
    }

    //Delete Attendance
    public function adminAttendanceDelete($id)
    {

        $permission = PermissionHelper::get(14);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        try {
            $attendance = Attendance::findOrFail($id);

            // Capture old data for logging
            $oldData = $attendance->toArray();

            $attendance->delete();

            // Logging
            $userId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
            $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Attendance Management',
                'action'         => 'Delete',
                'description'    => 'Deleted attendance record.',
                'affected_id'    => $attendance->id,
                'old_data'       => json_encode($oldData),
                'new_data'       => null,
            ]);

            return response()->json([
                'message' => 'Attendance deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Attendance record not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete attendance record: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to delete attendance record.',
            ], 500);
        }
    }

    // Import Attendance
    public function importAttendanceCSV(Request $request)
    {
        Log::info('Import Attendance CSV: Start', ['user_id' => Auth::id()]);

        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        Log::info('CSV file path', ['path' => $path]);

        $rows = array_map('str_getcsv', file($path));
        Log::info('CSV rows loaded', ['row_count' => count($rows)]);

        if (count($rows) < 1) {
            return back()->with('toastr_error', 'CSV file is empty.');
        }

        $header = array_map('trim', $rows[0]);
        unset($rows[0]);

        // Define required and optional headers
        $requiredHeaders = [
            'Employee ID',
            'Employee Name',
            'Date/Period',
            'Clock In',
            'Clock Out',
            'Regular Working Hours',
            'Regular ND Hours',
            'Regular OT Hours',
            'Regular OT + ND Hours',
            'Restday Work',
            'Restday OT',
            'Restday ND'
        ];

        // Check that all required headers are present (case-sensitive match)
        $missingRequired = [];
        foreach ($requiredHeaders as $req) {
            if (!in_array($req, $header)) {
                $missingRequired[] = $req;
            }
        }

        if (!empty($missingRequired)) {
            Log::warning('CSV header mismatch - missing required columns', [
                'missing' => $missingRequired,
                'actual_header' => $header
            ]);
            return back()->with('toastr_error', 'Missing required CSV headers: ' . implode(', ', $missingRequired));
        }

        // Load employee mapping
        $employeeMap = EmploymentDetail::pluck('user_id', 'employee_id');
        Log::info('Employee map loaded', ['count' => $employeeMap->count()]);

        $imported = 0;
        $skipped = 0;
        $skippedDetails = [];

        // Reusable minutes converter
        $toMinutes = function ($str) {
            $str = trim(strtolower((string)$str));
            if ($str === '' || $str === null || $str === 'null') {
                return 0;
            }

            // Match hours (hours, h, hr, hrs) and minutes (minutes, min, m, mins)
            $hrPattern = '(?:hours?|h|hrs?|hr)';
            $minPattern = '(?:minutes?|min|m|mins?)';

            // e.g. "2 hours 30 minutes", "2 hr 30 min", "2h 30m"
            if (preg_match('/^(\d+)\s*' . $hrPattern . '\s*(\d+)?\s*' . $minPattern . '?$/', $str, $m)) {
                return ((int)$m[1]) * 60 + ((int)($m[2] ?? 0));
            }
            // e.g. "2 hours", "2 hr", "2h", "2hrs"
            if (preg_match('/^(\d+)\s*' . $hrPattern . '$/', $str, $m)) {
                return ((int)$m[1]) * 60;
            }
            // e.g. "30 minutes", "30 min", "30m", "30mins"
            if (preg_match('/^(\d+)\s*' . $minPattern . '$/', $str, $m)) {
                return (int)$m[1];
            }
            // e.g. "2:30"
            if (preg_match('/^(\d+):(\d+)$/', $str, $m)) {
                return ((int)$m[1]) * 60 + ((int)$m[2]);
            }
            // e.g. "150"
            if (is_numeric($str)) {
                return (int)$str;
            }
            return 0;
        };

        foreach ($rows as $index => $row) {
            // Pad row to match header length (in case of missing columns)
            while (count($row) < count($header)) {
                $row[] = '';
            }
            $row = array_slice($row, 0, count($header)); // Truncate if too long

            $rowNumber = $index + 2;
            $data = array_combine($header, $row);

            $employeeId = trim($data['Employee ID']);
            $userId = $employeeMap[$employeeId] ?? null;

            if (!$userId) {
                $skipped++;
                $skippedDetails[] = "Row $rowNumber: Employee ID '{$employeeId}' not found.";
                Log::warning('Employee ID not found', ['row' => $rowNumber, 'employee_id' => $employeeId]);
                continue;
            }

            // Validate required fields
            $validator = Validator::make([
                'Employee ID' => $employeeId,
                'Date/Period' => $data['Date/Period'],
            ], [
                'Employee ID' => 'required',
                'Date/Period' => 'required',
            ]);

            if ($validator->fails()) {
                $skipped++;
                $skippedDetails[] = "Row $rowNumber: " . implode(', ', $validator->errors()->all());
                Log::warning('Validation failed', [
                    'row' => $rowNumber,
                    'errors' => $validator->errors()->all()
                ]);
                continue;
            }

            // Parse date
            try {
                $attendanceDate = Carbon::parse($data['Date/Period']);
            } catch (\Exception $e) {
                $skipped++;
                $skippedDetails[] = "Row $rowNumber: Invalid date format for Date/Period.";
                Log::warning('Invalid date format', [
                    'row' => $rowNumber,
                    'date' => $data['Date/Period']
                ]);
                continue;
            }
            $attendanceDateStr = $attendanceDate->toDateString();
            $monthDay = $attendanceDate->format('m-d');

            // Parse Clock In
            $dateTimeIn = null;
            if (!empty(trim($data['Clock In'] ?? ''))) {
                $clockInTime = trim($data['Clock In']);
                try {
                    $timeIn = Carbon::createFromFormat('H:i:s', $clockInTime);
                    $dateTimeIn = $attendanceDate->copy()->setTime($timeIn->hour, $timeIn->minute, $timeIn->second);
                } catch (\Exception $e) {
                    try {
                        $timeIn = Carbon::createFromFormat('h:i:s A', $clockInTime);
                        $dateTimeIn = $attendanceDate->copy()->setTime($timeIn->hour, $timeIn->minute, $timeIn->second);
                    } catch (\Exception $e2) {
                        try {
                            $timeIn = Carbon::createFromFormat('g:i A', $clockInTime);
                            $dateTimeIn = $attendanceDate->copy()->setTime($timeIn->hour, $timeIn->minute, $timeIn->second);
                        } catch (\Exception $e3) {
                            try {
                                $timeIn = Carbon::createFromFormat('g A', $clockInTime);
                                $dateTimeIn = $attendanceDate->copy()->setTime($timeIn->hour, $timeIn->minute, 0);
                            } catch (\Exception $e4) {
                                Log::warning('Could not parse Clock In time', [
                                    'row' => $rowNumber,
                                    'clock_in' => $clockInTime
                                ]);
                            }
                        }
                    }
                }
            }

            // Parse Clock Out
            $dateTimeOut = null;
            if (!empty(trim($data['Clock Out'] ?? ''))) {
                $clockOutTime = trim($data['Clock Out']);
                try {
                    $timeOut = Carbon::createFromFormat('H:i:s', $clockOutTime);
                    $dateTimeOut = $attendanceDate->copy()->setTime($timeOut->hour, $timeOut->minute, $timeOut->second);
                    if ($dateTimeIn && $dateTimeOut->lt($dateTimeIn)) {
                        $dateTimeOut->addDay();
                    }
                } catch (\Exception $e) {
                    try {
                        $timeOut = Carbon::createFromFormat('h:i:s A', $clockOutTime);
                        $dateTimeOut = $attendanceDate->copy()->setTime($timeOut->hour, $timeOut->minute, $timeOut->second);
                        if ($dateTimeIn && $dateTimeOut->lt($dateTimeIn)) {
                            $dateTimeOut->addDay();
                        }
                    } catch (\Exception $e2) {
                        try {
                            $timeOut = Carbon::createFromFormat('g:i A', $clockOutTime);
                            $dateTimeOut = $attendanceDate->copy()->setTime($timeOut->hour, $timeOut->minute, $timeOut->second);
                            if ($dateTimeIn && $dateTimeOut->lt($dateTimeIn)) {
                                $dateTimeOut->addDay();
                            }
                        } catch (\Exception $e3) {
                            try {
                                $timeOut = Carbon::createFromFormat('g A', $clockOutTime);
                                $dateTimeOut = $attendanceDate->copy()->setTime($timeOut->hour, $timeOut->minute, 0);
                                if ($dateTimeIn && $dateTimeOut->lt($dateTimeIn)) {
                                    $dateTimeOut->addDay();
                                }
                            } catch (\Exception $e4) {
                                Log::warning('Could not parse Clock Out time', [
                                    'row' => $rowNumber,
                                    'clock_out' => $clockOutTime
                                ]);
                            }
                        }
                    }
                }
            }

            // Holiday detection
            $holiday = Holiday::where(function ($q) use ($attendanceDateStr, $monthDay) {
                $q->where('date', $attendanceDateStr)
                    ->orWhere(function ($q2) use ($monthDay) {
                        $q2->whereNull('date')
                            ->where('month_day', $monthDay);
                    });
            })
                ->where('status', 1)
                ->first();

            $isHoliday = $holiday ? true : false;
            $holidayId = $holiday ? $holiday->id : null;

            // Attendance fields
            $totalWorkMinutes = $toMinutes($data['Regular Working Hours'] ?? null);
            $totalNightDiffMinutes = $toMinutes($data['Regular ND Hours'] ?? null);
            $attendanceRestday = strtolower(trim((string)($data['Restday Work'] ?? 'false'))) === 'true' || trim($data['Restday Work'] ?? '') === '1';

            // Overtime fields
            $totalOtMinutes = $toMinutes($data['Regular OT Hours'] ?? null);
            $totalOtNdMinutes = $toMinutes($data['Regular OT + ND Hours'] ?? null);

            $overtimeRestday = !empty($data['Restday OT']);
            $overtimeNdMinutes = $toMinutes($data['Restday ND'] ?? null);
            if ($overtimeNdMinutes > 0) {
                $totalOtNdMinutes += $overtimeNdMinutes;
            }

            // Optional: Late & Undertime Minutes
            $totalLateMinutes = 0;
            $totalUndertimeMinutes = 0;

            if (in_array('Late', $header)) {
                $totalLateMinutes = $toMinutes($data['Late'] ?? null);
            }

            if (in_array('Undertime', $header)) {
                $totalUndertimeMinutes = $toMinutes($data['Undertime'] ?? null);
            }

            // Save Attendance
            try {
                $attendance = Attendance::create([
                    'user_id'                   => $userId,
                    'attendance_date'           => $attendanceDateStr,
                    'date_time_in'              => $dateTimeIn,
                    'date_time_out'             => $dateTimeOut,
                    'status'                    => 'present',
                    'is_rest_day'               => $attendanceRestday,
                    'is_holiday'                => $isHoliday,
                    'holiday_id'                => $holidayId,
                    'total_work_minutes'        => $totalWorkMinutes,
                    'total_night_diff_minutes'  => $totalNightDiffMinutes,
                    'total_late_minutes'        => $totalLateMinutes,
                    'total_undertime_minutes'   => $totalUndertimeMinutes,
                    'created_at'                => now(),
                    'updated_at'                => now(),
                ]);
                Log::info('Attendance saved', [
                    'row' => $rowNumber,
                    'attendance_id' => $attendance->id,
                    'user_id' => $userId,
                    'attendance_date' => $attendanceDateStr,
                ]);
            } catch (\Exception $e) {
                $skipped++;
                $skippedDetails[] = "Row $rowNumber: Error saving attendance. " . $e->getMessage();
                Log::error('Error saving attendance', [
                    'row' => $rowNumber,
                    'error' => $e->getMessage()
                ]);
                continue;
            }

            // Save Overtime if applicable
            if ($totalOtMinutes > 0 || $totalOtNdMinutes > 0) {
                try {
                    $overtime = Overtime::create([
                        'user_id'                   => $userId,
                        'holiday_id'                => $holidayId,
                        'overtime_date'             => $attendanceDateStr,
                        'date_ot_in'                => null,
                        'date_ot_out'               => null,
                        'ot_in_photo_path'          => null,
                        'ot_out_photo_path'         => null,
                        'total_ot_minutes'          => $totalOtMinutes,
                        'is_rest_day'               => $overtimeRestday,
                        'is_holiday'                => $isHoliday,
                        'status'                    => 'approved',
                        'file_attachment'           => null,
                        'current_step'              => 1,
                        'offset_date'               => null,
                        'ot_login_type'             => 'import',
                        'total_night_diff_minutes'  => $totalOtNdMinutes,
                        'created_at'                => now(),
                        'updated_at'                => now(),
                    ]);
                    Log::info('Overtime saved', [
                        'row' => $rowNumber,
                        'overtime_id' => $overtime->id,
                        'user_id' => $userId,
                    ]);
                } catch (\Exception $e) {
                    $skipped++;
                    $skippedDetails[] = "Row $rowNumber: Error saving overtime. " . $e->getMessage();
                    Log::error('Error saving overtime', [
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                        'user_id' => $userId,
                    ]);
                    continue;
                }
            }

            $imported++;
            Log::info('Attendance and Overtime imported', [
                'row' => $rowNumber,
                'user_id' => $userId,
                'attendance_date' => $attendanceDateStr
            ]);
        }

        Log::info('Import Attendance CSV: Finished', [
            'imported' => $imported,
            'skipped' => $skipped
        ]);

        if ($imported > 0) {
            return back()->with('toastr_success', "$imported record(s) imported. $skipped skipped.")
                ->with('toastr_details', $skippedDetails);
        } else {
            return back()->with('toastr_error', "No records imported. $skipped skipped.")
                ->with('toastr_details', $skippedDetails);
        }
    }

    // Bulk Import Attendance
    public function bulkImportAttendanceCSV(Request $request)
    {
        // Validate the CSV file
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('csv_file')->store('imports');
        $tenantId = Auth::id();

        BulkImportAttendanceJob::dispatch($path, $tenantId);

        return back()->with('toastr_success', 'Import successfully queued. Please wait 5-10minutes and refresh the page.');
    }

    // Template
    public function downloadAttendanceTemplate()
    {
        $path = public_path('templates/attendance_template.csv');

        if (!file_exists($path)) {
            abort(404, 'Template file not found.');
        }

        return response()->download($path, 'attendance_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    // Template for Bulk Import
    public function downloadAttendanceBulkImportTemplate()
    {
        $path = public_path('templates/attendance_bulk_import_template_new.csv');

        if (!file_exists($path)) {
            abort(404, 'Template file not found.');
        }

        return response()->download($path, 'attendance_bulk_import_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    //Bulk Admin Attendance Index
    public function bulkAdminAttendanceFilter(Request $request)
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


        $query  = $accessData['bulkAttendances'];

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->where(function ($q) use ($start, $end) {
                    $q->where('date_from', '<=', $end)
                        ->where('date_to', '>=', $start);
                });
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

        $bulkAttendances = $query->get();

        $html = view('tenant.attendance.attendance.adminbulkattendance_filter', compact('bulkAttendances', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function bulkAdminAttendanceIndex(Request $request)
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

        // Filter user attendances based on tenant_id
        $bulkAttendances = $accessData['bulkAttendances']->get();

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

        // Api Route
        if ($request->wantsJson()) {
            return response()->json([
                'status'         => true,
                'total_present'   => $totalPresent,
                'total_late' => $totalLate,
                'total_absent' => $totalAbsent,
                'bulkAttendances' => $bulkAttendances,
            ]);
        }

        // Web Route
        return view('tenant.attendance.attendance.adminbulkattendance', [
            'totalPresent'   => $totalPresent,
            'totalLate' => $totalLate,
            'totalAbsent' => $totalAbsent,
            'permission' => $permission,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,
            'bulkAttendances' => $bulkAttendances
        ]);
    }

    // Bulk Attendance Edit
    public function bulkAttendanceEdit(Request $request, $id)
    {
        $permission = PermissionHelper::get(14);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have the permission to update.'
            ], 403);
        }
        try {
            DB::beginTransaction();

            $input = $request->isJson() ? $request->json()->all() : $request->all();

            $validator = Validator::make($input, [
                'date_from' => 'required|date',
                'date_to' => 'required|date|after_or_equal:date_from',
                'regular_working_days' => 'nullable|integer',
                'regular_working_hours' => 'nullable|integer',
                'regular_overtime_hours' => 'nullable|integer',
                'regular_nd_hours' => 'nullable|integer',
                'regular_nd_overtime_hours' => 'nullable|integer',
                'rest_day_work' => 'nullable|boolean',
                'rest_day_overtime' => 'nullable|boolean',
                'rest_day_nd' => 'nullable|boolean',
                'regular_holiday_hours' => 'nullable|integer',
                'special_holiday_hours' => 'nullable|integer',
                'regular_holiday_ot' => 'nullable|integer',
                'special_holiday_ot' => 'nullable|integer',
                'regular_holiday_nd' => 'nullable|integer',
                'special_holiday_nd' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            $bulkAttendance = BulkAttendance::findOrFail($id);

            $oldData = $bulkAttendance->toArray();

            $bulkAttendance->update([
                'date_from' => $data['date_from'],
                'date_to' => $data['date_to'],
                'regular_working_days' => $data['regular_working_days'] ?? null,
                'regular_working_hours' => $data['regular_working_hours'] ?? null,
                'regular_overtime_hours' => $data['regular_overtime_hours'] ?? null,
                'regular_nd_hours' => $data['regular_nd_hours'] ?? null,
                'regular_nd_overtime_hours' => $data['regular_nd_overtime_hours'] ?? null,
                'rest_day_work' => $data['rest_day_work'] ?? false,
                'rest_day_overtime' => $data['rest_day_overtime'] ?? false,
                'rest_day_nd' => $data['rest_day_nd'] ?? false,
                'regular_holiday_hours' => $data['regular_holiday_hours'] ?? null,
                'special_holiday_hours' => $data['special_holiday_hours'] ?? null,
                'regular_holiday_ot' => $data['regular_holiday_ot'] ?? null,
                'special_holiday_ot' => $data['special_holiday_ot'] ?? null,
                'regular_holiday_nd' => $data['regular_holiday_nd'] ?? null,
                'special_holiday_nd' => $data['special_holiday_nd'] ?? null,
            ]);

            Log::info("Bulk Attendance Updated:", ['data' => $bulkAttendance]);

            $userId = Auth::guard('web')->id();
            $globalUserId = Auth::guard('global')->id();

            UserLog::create([
                'user_id' => $userId,
                'global_user_id' => $globalUserId,
                'module' => 'Bulk Attendance Management',
                'action' => 'Update',
                'description' => 'Updated bulk attendance record.',
                'affected_id' => $bulkAttendance->id,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($bulkAttendance->toArray()),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Bulk attendance updated successfully!',
                'data' => $bulkAttendance,
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Bulk attendance record not found.',
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error updating bulk attendance", ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Bulk Attendance Delete
    public function bulkAttendanceDelete($id)
    {
        $permission = PermissionHelper::get(14);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have the permission to delete.'
            ], 403);
        }

        try {
            $bulkAttendance = BulkAttendance::findOrFail($id);
            $oldData = $bulkAttendance->toArray();
            $bulkAttendance->delete();

            // Logging
            $userId = Auth::guard('web')->check() ? Auth::guard('web')->id() : null;
            $globalUserId = Auth::guard('global')->check() ? Auth::guard('global')->id() : null;

            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Bulk Attendance Management',
                'action'         => 'Delete',
                'description'    => 'Deleted bulk attendance record.',
                'affected_id'    => $bulkAttendance->id,
                'old_data'       => json_encode($oldData),
                'new_data'       => null,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Bulk attendance deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bulk attendance record not found.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete bulk attendance record.',
            ], 500);
        }
    }
}
