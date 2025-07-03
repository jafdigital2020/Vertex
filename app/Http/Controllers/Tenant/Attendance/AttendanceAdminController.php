<?php

namespace App\Http\Controllers\Tenant\Attendance;

use Exception;
use Carbon\Carbon;
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
        return Auth::guard('web')->user();
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


        $query  = $accessData['attendances'];

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

        $html = view('tenant.attendance.attendance.adminattendance_filter', compact('userAttendances', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
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

        $userAttendances = $accessData['attendances']
        ->where('attendance_date', Carbon::today()->toDateString())
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
            'designations' => $designations
        ]);
    }

    // Edit and Update Attendance
    public function adminAttendanceEdit(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'attendance_date'    => 'required|date',
                'date_time_in'       => 'required|date_format:H:i',
                'date_time_out'      => 'required|date_format:H:i',
                'total_late_minutes' => 'nullable|integer',
                'total_work_minutes' => 'nullable|integer',
                'status'             => 'nullable|string',
            ]);

            $attendance = Attendance::findOrFail($id);
            $oldData = $attendance->toArray();

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

        $header = array_map('trim', $rows[0]);
        unset($rows[0]);

        $expectedHeader = [
            'Employee ID',
            'Employee Name',
            'Date/Period',
            'Regular Working Hours',
            'Regular OT Hours',
            'Regular ND Hours',
            'Regular OT + ND Hours',
            'Restday Work',
            'Restday OT',
            'Restday ND'
        ];

        if ($header !== $expectedHeader) {
            Log::warning('CSV header mismatch', ['header' => $header, 'expected' => $expectedHeader]);
            return back()->with('toastr_error', 'CSV headers do not match the required format.')
                ->with('toastr_details', []);
        }

        $employeeMap = EmploymentDetail::pluck('user_id', 'employee_id');
        Log::info('Employee map loaded', ['count' => $employeeMap->count()]);

        $imported = 0;
        $skipped = 0;
        $skippedDetails = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = array_combine($header, $row);

            $employeeId = trim($data['Employee ID']);
            $userId = $employeeMap[$employeeId] ?? null;

            if (! $userId) {
                $skipped++;
                $skippedDetails[] = "Row $rowNumber: Employee ID '{$employeeId}' not found.";
                Log::warning('Employee ID not found', ['row' => $rowNumber, 'employee_id' => $employeeId]);
                continue;
            }

            // Validate required fields
            $validator = Validator::make([
                'Employee ID'   => $employeeId,
                'Date/Period'   => $data['Date/Period'],
            ], [
                'Employee ID'   => 'required',
                'Date/Period'   => 'required',
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

            // Holiday detection (same for attendance and overtime)
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

            // Convert hours to minutes
            $toMinutes = function ($str) {
                $str = trim(strtolower((string)$str));
                if ($str === '' || $str === null) return 0;
                if (preg_match('/^(\d+)\s*hr[s]?\s*(\d+)?\s*min[s]?$/', $str, $m)) {
                    return ((int)$m[1]) * 60 + ((int)($m[2] ?? 0));
                }
                if (preg_match('/^(\d+)\s*hr[s]?$/', $str, $m)) {
                    return ((int)$m[1]) * 60;
                }
                if (preg_match('/^(\d+)\s*min[s]?$/', $str, $m)) {
                    return (int)$m[1];
                }
                if (preg_match('/^(\d+):(\d+)$/', $str, $m)) {
                    return ((int)$m[1]) * 60 + ((int)$m[2]);
                }
                if (is_numeric($str)) {
                    return (int)$str;
                }
                return 0;
            };

            // Attendance fields
            $totalWorkMinutes = $toMinutes($data['Regular Working Hours'] ?? null);
            $totalNightDiffMinutes = $toMinutes($data['Regular ND Hours'] ?? null);
            $attendanceRestday = strtolower(trim((string)($data['Restday Work'] ?? 'false'))) === 'true' || trim($data['Restday Work'] ?? '') === '1';

            // Overtime fields
            $totalOtMinutes = $toMinutes($data['Regular OT Hours'] ?? null);
            $totalOtNdMinutes = $toMinutes($data['Regular OT + ND Hours'] ?? null);

            // Overtime restday logic: if Restday OT has value, set is_rest_day true for OT
            $overtimeRestday = !empty($data['Restday OT']);
            // Overtime ND logic: if Restday ND has value, add to total_night_diff_minutes for OT
            $overtimeNdMinutes = $toMinutes($data['Restday ND'] ?? null);
            if ($overtimeNdMinutes > 0) {
                $totalOtNdMinutes += $overtimeNdMinutes;
            }

            // Save Attendance
            try {
                $attendance = Attendance::create([
                    'user_id'                  => $userId,
                    'attendance_date'          => $attendanceDateStr,
                    'date_time_in'             => null,
                    'date_time_out'            => null,
                    'status'                   => 'present',
                    'is_rest_day'              => $attendanceRestday,
                    'is_holiday'               => $isHoliday,
                    'holiday_id'               => $holidayId,
                    'total_work_minutes'       => $totalWorkMinutes,
                    'total_night_diff_minutes' => $totalNightDiffMinutes,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]);
                Log::info('Attendance saved', [
                    'row' => $rowNumber,
                    'attendance_id' => $attendance->id,
                    'user_id' => $userId,
                    'attendance_date' => $attendanceDateStr
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

            // Save Overtime
            try {

                $overtime = Overtime::create([
                    'user_id'                  => $userId,
                    'holiday_id'               => $holidayId,
                    'overtime_date'            => $attendanceDateStr,
                    'date_ot_in'               => null,
                    'date_ot_out'              => null,
                    'ot_in_photo_path'         => null,
                    'ot_out_photo_path'        => null,
                    'total_ot_minutes'         => $totalOtMinutes,
                    'is_rest_day'              => $overtimeRestday,
                    'is_holiday'               => $isHoliday,
                    'status'                   => 'approved',
                    'file_attachment'          => null,
                    'current_step'             => 1,
                    'offset_date'              => null,
                    'ot_login_type'            => 'import',
                    'total_night_diff_minutes' => $totalOtNdMinutes,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]);
                Log::info('Overtime saved', [
                    'row' => $rowNumber,
                    'overtime_id' => $overtime->id,
                    'user_id' => $userId,
                    'overtime_date' => $attendanceDateStr
                ]);
            } catch (\Exception $e) {
                $skipped++;
                $skippedDetails[] = "Row $rowNumber: Error saving overtime. " . $e->getMessage();
                Log::error('Error saving overtime', [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                    'user_id' => $userId,
                    'holiday_id' => $holidayId,
                    'overtime_date' => $attendanceDateStr,
                    'total_ot_minutes' => $totalOtMinutes,
                    'is_rest_day' => $overtimeRestday,
                    'is_holiday' => $isHoliday,
                    'total_night_diff_minutes' => $totalOtNdMinutes
                ]);
                continue;
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
        $path = public_path('templates/attendance_theos_template.csv');

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
        $path = public_path('templates/attendance_bulk_import_template.csv');

        if (!file_exists($path)) {
            abort(404, 'Template file not found.');
        }

        return response()->download($path, 'attendance_bulk_import_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    //Bulk Admin Attendance Index
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
        $bulkAttendances = BulkAttendance::whereHas('user', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId)
                ->whereHas('employmentDetail', fn($edQ) => $edQ->where('status', '1'));
        })
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
