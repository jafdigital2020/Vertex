<?php

namespace App\Http\Controllers\Tenant\Attendance;

use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\UserLog;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\EmploymentDetail;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\DataAccessController;
class AttendanceAdminController extends Controller
{   
    
    public function authUser() {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        } 
        return Auth::guard('web')->user();
    } 

    public function filter(Request $request){
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
        if($branch){
            $query->whereHas('user.employmentDetail', function ($q) use ($branch) {
                $q->where('branch_id', $branch);
            });
        } 
        if($department){
            $query->whereHas('user.employmentDetail', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
        if($designation){
            $query->whereHas('user.employmentDetail', function ($q) use ($designation) {
                $q->where('designation_id', $designation);
            });
        }
        if($status){
            $query->where('status', $status);
        }
        
        $userAttendances = $query->get();

        $html = view('tenant.attendance.attendance.adminattendance_filter', compact('userAttendances','permission'))->render();
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
 
        $userAttendances = $accessData['attendances']->get();

        // Total Present for today
        $totalPresent = Attendance::whereDate('attendance_date', $today)
            ->where('status', 'present')
            ->whereHas('user', function ($userQ) use ($tenantId) {
                $userQ->where('tenant_id', $tenantId)
                    ->whereHas('employmentDetail', fn($edQ) => $edQ->where('status', 'active'));
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

        $expectedHeader = ['Employee ID', 'Attendance Date', 'Time In', 'Time Out', 'Rest Day'];

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

            $isRestDay = strtolower(trim($data['Rest Day']));

            // Accept any date format, validate only presence
            $validator = Validator::make([
                'Attendance Date' => $data['Attendance Date'],
                'Time In'         => $data['Time In'],
                'Time Out'        => $data['Time Out'],
                'Rest Day'        => $isRestDay,
            ], [
                'Attendance Date' => 'required',
                'Time In'         => 'required|string',
                'Time Out'        => 'required|string',
                'Rest Day'        => 'required|in:true,false',
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

            try {
                // Try to parse date and time in any format
                try {
                    $attendanceDate = Carbon::parse($data['Attendance Date']);
                } catch (\Exception $e) {
                    $skipped++;
                    $skippedDetails[] = "Row $rowNumber: Invalid date format for Attendance Date.";
                    Log::warning('Invalid date format', [
                        'row' => $rowNumber,
                        'attendance_date' => $data['Attendance Date']
                    ]);
                    continue;
                }

                try {
                    $in = Carbon::parse($data['Attendance Date'] . ' ' . $data['Time In']);
                } catch (\Exception $e) {
                    try {
                        $in = Carbon::parse($attendanceDate->toDateString() . ' ' . $data['Time In']);
                    } catch (\Exception $e2) {
                        $skipped++;
                        $skippedDetails[] = "Row $rowNumber: Invalid time format for Time In.";
                        Log::warning('Invalid time in format', [
                            'row' => $rowNumber,
                            'time_in' => $data['Time In']
                        ]);
                        continue;
                    }
                }

                try {
                    $out = Carbon::parse($data['Attendance Date'] . ' ' . $data['Time Out']);
                } catch (\Exception $e) {
                    try {
                        $out = Carbon::parse($attendanceDate->toDateString() . ' ' . $data['Time Out']);
                    } catch (\Exception $e2) {
                        $skipped++;
                        $skippedDetails[] = "Row $rowNumber: Invalid time format for Time Out.";
                        Log::warning('Invalid time out format', [
                            'row' => $rowNumber,
                            'time_out' => $data['Time Out']
                        ]);
                        continue;
                    }
                }

                if ($out->lessThanOrEqualTo($in)) {
                    $out->addDay();
                }

                // Night diff logic: 22:00 - 06:00
                $ndStart = $in->copy()->setTime(22, 0);
                $ndEnd   = $ndStart->copy()->addDay()->setTime(6, 0);

                $nightStart = $in > $ndStart ? $in : $ndStart;
                $nightEnd   = $out < $ndEnd ? $out : $ndEnd;

                $ndMinutes = ($nightStart < $nightEnd) ? $nightStart->diffInMinutes($nightEnd) : 0;

                // Total work minutes should NOT include night diff
                $totalWorkMinutes = $in->diffInMinutes($out) - $ndMinutes;
                if ($totalWorkMinutes < 0) {
                    $totalWorkMinutes = 0;
                }

                // Holiday detection
                $attendanceDateStr = $attendanceDate->toDateString();
                $monthDay = $attendanceDate->format('m-d');
                $holiday = Holiday::where(function($q) use ($attendanceDateStr, $monthDay) {
                        $q->where('date', $attendanceDateStr)
                          ->orWhere(function($q2) use ($monthDay) {
                              $q2->whereNull('date')
                                 ->where('month_day', $monthDay);
                          });
                    })
                    ->where('status', 1)
                    ->first();

                $isHoliday = $holiday ? true : false;
                $holidayId = $holiday ? $holiday->id : null;

                Attendance::create([
                    'user_id'                  => $userId,
                    'attendance_date'          => $attendanceDateStr,
                    'date_time_in'             => $in,
                    'date_time_out'            => $out,
                    'status'                   => 'present',
                    'is_rest_day'              => $isRestDay === 'true',
                    'is_holiday'               => $isHoliday,
                    'holiday_id'               => $holidayId,
                    'total_work_minutes'       => $totalWorkMinutes,
                    'total_night_diff_minutes' => $ndMinutes,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]);

                $imported++;
                Log::info('Attendance imported', [
                    'row' => $rowNumber,
                    'user_id' => $userId,
                    'attendance_date' => $attendanceDateStr
                ]);
            } catch (\Exception $e) {
                $skipped++;
                $skippedDetails[] = "Row $rowNumber: Error saving. " . $e->getMessage();
                Log::error('Error saving attendance', [
                    'row' => $rowNumber,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Import Attendance CSV: Finished', [
            'imported' => $imported,
            'skipped' => $skipped
        ]);

        if ($imported > 0) {
            return back()->with('toastr_success', "$imported attendance(s) imported. $skipped skipped.")
                ->with('toastr_details', $skippedDetails);
        } else {
            return back()->with('toastr_error', "No records imported. $skipped skipped.")
                ->with('toastr_details', $skippedDetails);
        }
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
}
