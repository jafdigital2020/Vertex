<?php

namespace App\Http\Controllers\Tenant\Attendance;

use Carbon\Carbon;
use App\Models\UserLog;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AttendanceAdminController extends Controller
{
    public function adminAttendanceIndex(Request $request)
    {
        $orgCode = Auth::user()->organization_code;
        $today = Carbon::today()->toDateString();

        // Fetch the organization and only have active status
        $userAttendances = Attendance::with('user.employmentDetail')
            ->whereHas('user', function ($userQ) use ($orgCode) {
                $userQ->where('organization_code', $orgCode)
                    ->whereHas('employmentDetail', function ($edQ) {
                        $edQ->where('status', 'active');
                    });
            })
            ->get();

        // Total Present for today
        $totalPresent = Attendance::whereDate('attendance_date', $today)
            ->where('status', 'present')
            ->whereHas('user', function ($userQ) use ($orgCode) {
                $userQ->where('organization_code', $orgCode)
                    ->whereHas('employmentDetail', fn($edQ) => $edQ->where('status', 'active'));
            })
            ->count();

        // Total Late for today
        $totalLate = Attendance::whereDate('attendance_date', $today)
            ->where('status', 'late')
            ->whereHas('user', function ($userQ) use ($orgCode) {
                $userQ->where('organization_code', $orgCode)
                    ->whereHas('employmentDetail', fn($edQ) => $edQ->where('status', 'active'));
            })
            ->count();

        // Total Absent
        $totalAbsent = Attendance::whereDate('attendance_date', $today)
            ->where('status', 'absent')
            ->whereHas('user', function ($userQ) use ($orgCode) {
                $userQ->where('organization_code', $orgCode)
                    ->whereHas('employmentDetail', fn($edQ) => $edQ->where('status', 'active'));
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
}
