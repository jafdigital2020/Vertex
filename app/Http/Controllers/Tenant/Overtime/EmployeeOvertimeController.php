<?php

namespace App\Http\Controllers\Tenant\Overtime;

use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\UserLog;
use App\Models\Overtime;
use Illuminate\Http\Request;
use App\Models\HolidayException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class EmployeeOvertimeController extends Controller
{
    public function overtimeEmployeeIndex(Request $request)
    {
        $authUserId = Auth::user()->id;
        $overtimes = Overtime::where('user_id', $authUserId)
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('overtime_date', 'desc')
            ->get();

        // Requests count
        $pendingRequests = $overtimes->where('status', 'pending')->count();
        $approvedRequests = $overtimes->where('status', 'approved')->count();
        $rejectedRequests = $overtimes->where('status', 'rejected')->count();

        // API
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'This endpoint is not available for JSON requests.',
                'status' => 'error',
            ], 400);
        }

        // Web
        return view('tenant.overtime.employeeovertime', [
            'overtimes' => $overtimes,
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'rejectedRequests' => $rejectedRequests,
        ]);
    }

    // Manual Overtime Create
    public function overtimeEmployeeManualCreate(Request $request)
    {
        $authUserId = Auth::user()->id;

        // Validation
        $request->validate([
            'overtime_date'      => 'required|date',
            'date_ot_in'         => 'required|date',
            'date_ot_out'        => 'required|date|after_or_equal:date_ot_in',
            'total_ot_minutes'   => 'nullable|numeric',
            'file_attachment'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // max 5MB
            'offset_date'        => 'nullable|date',
        ]);

        // Check if an overtime exists for this user & date
        $existing = Overtime::where('user_id', $authUserId)
            ->whereDate('overtime_date', $request->overtime_date)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an overtime entry for this date.',
            ], 422);
        }

        // File upload
        $filePath = null;
        if ($request->hasFile('file_attachment')) {
            $filePath = $request->file('file_attachment')->store('overtime_attachments', 'public');
        }

        $overtime = Overtime::create([
            'user_id'           => $authUserId,
            'overtime_date'     => $request->overtime_date,
            'date_ot_in'        => $request->date_ot_in,
            'date_ot_out'       => $request->date_ot_out,
            'total_ot_minutes'  => $request->total_ot_minutes,
            'file_attachment'   => $filePath,
            'offset_date'       => $request->offset_date,
            'status'            => 'pending',
            'ot_login_type'    => 'manual',
        ]);

        // Logging Start
        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'    => $userId,
            'global_user_id' => $globalUserId,
            'module'     => 'Employee Overtime',
            'action'     => 'add_overtime',
            'description' => 'Added manual overtime, ID: ' . $overtime->id,
            'affected_id' => $overtime->id,
            'old_data'   => null,
            'new_data'   => json_encode($overtime->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime added successfully.',
            'data'    => $overtime,
        ]);
    }

    // Manual Overtime Edit
    public function overtimeEmployeeManualUpdate(Request $request, $id)
    {
        $authUserId = Auth::id();

        $request->validate([
            'overtime_date'      => 'required|date',
            'date_ot_in'         => 'required|date',
            'date_ot_out'        => 'required|date|after:date_ot_in',
            'total_ot_minutes'   => 'required|numeric',
            'file_attachment'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'offset_date'        => 'nullable|date',
        ]);

        $overtime = Overtime::findOrFail($id);

        if ($overtime->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'This overtime entry is already approved and cannot be edited.',
            ], 403);
        }

        // Prevent duplicate for same user & date, excluding this record
        $exists = Overtime::where('user_id', $authUserId)
            ->whereDate('overtime_date', $request->overtime_date)
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an overtime entry for this date.',
            ], 422);
        }

        // Save old data for logging
        $oldData = $overtime->toArray();

        // Handle file upload if new file
        if ($request->hasFile('file_attachment')) {
            $filePath = $request->file('file_attachment')->store('overtime_attachments', 'public');
            $overtime->file_attachment = $filePath;
        }

        $overtime->overtime_date = $request->overtime_date;
        $overtime->date_ot_in = $request->date_ot_in;
        $overtime->date_ot_out = $request->date_ot_out;
        $overtime->total_ot_minutes = $request->total_ot_minutes;
        $overtime->offset_date = $request->offset_date;

        $overtime->save();

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'    => $userId,
            'global_user_id' => $globalUserId,
            'module'     => 'Employee Overtime',
            'action'     => 'edit_overtime',
            'description' => 'Edited manual overtime, ID: ' . $overtime->id,
            'affected_id' => $overtime->id,
            'old_data'   => json_encode($oldData),
            'new_data'   => json_encode($overtime->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime updated successfully.',
            'data'    => $overtime,
        ]);
    }

    // Manual Overtime Delete
    public function overtimeEmployeeManualDelete($id)
    {
        $overtime = Overtime::findOrFail($id);

        if ($overtime->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'This overtime entry is already approved and cannot be deleted.',
            ], 403);
        }

        $overtime->delete();

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'    => $userId,
            'global_user_id' => $globalUserId,
            'module'     => 'Employee Overtime',
            'action'     => 'delete_overtime',
            'description' => 'Deleted manual overtime, ID: ' . $overtime->id,
            'affected_id' => $overtime->id,
            'old_data'   => json_encode($overtime->toArray()),
            'new_data'   => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime deleted successfully.',
        ]);
    }

    // Clock in Overtime
    public function overtimeEmployeeClockIn(Request $request)
    {
        $authUserId = Auth::id();
        $todayMonthDay = Carbon::today()->format('m-d');
        $today = Carbon::today()->toDateString();
        $now = now();

        $request->validate([
            'file_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'offset_date'     => 'nullable|date',
        ]);

        // Check if an overtime exists for this user & date
        $existing = Overtime::where('user_id', $authUserId)
            ->whereDate('overtime_date', $now->toDateString())
            ->whereNull('date_ot_out')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an overtime entry for this date.',
            ], 422);
        }

        $filePath = null;
        if ($request->hasFile('file_attachment')) {
            $filePath = $request->file('file_attachment')->store('overtime_attachments', 'public');
        }

        // Holiday Check
        $exception = HolidayException::where('user_id', $authUserId)
            ->where('status', 'active')
            ->whereHas('holiday', function ($q) use ($today, $todayMonthDay) {
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


        $overtime = Overtime::create([
            'user_id'           => $authUserId,
            'overtime_date'     => $now->toDateString(),
            'date_ot_in'        => $now->toDateTimeString(),
            'status'            => 'pending',
            'ot_login_type'     => 'ot_clock_in',
            'file_attachment'   => $filePath,
            'offset_date'      => $request->offset_date,
            'is_holiday'       => $isHoliday,
            'holiday_id'       => $holidayId,
        ]);

        // Logging Start
        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'    => $userId,
            'global_user_id' => $globalUserId,
            'module'     => 'Employee Overtime',
            'action'     => 'clock_in_overtime',
            'description' => 'Clocked in for overtime, ID: ' . $overtime->id,
            'affected_id' => $overtime->id,
            'old_data'   => null,
            'new_data'   => json_encode($overtime->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime clocked in successfully.',
            'data'    => $overtime,
        ]);
    }

    // Clock out Overtime
    public function overtimeEmployeeClockOut(Request $request)
    {
        $authUserId = Auth::id();
        $now = now();

        // Find the latest open overtime entry for today (clocked in, not clocked out)
        $overtime = Overtime::where('user_id', $authUserId)
            ->whereDate('overtime_date', $now->toDateString())
            ->whereNull('date_ot_out')
            ->latest('date_ot_in')
            ->first();

        if (!$overtime) {
            return response()->json([
                'success' => false,
                'message' => 'No open overtime entry to clock out from.',
            ], 404);
        }

        $overtime->date_ot_out = $now->toDateTimeString();

        // Calculate minutes
        $in = \Carbon\Carbon::parse($overtime->date_ot_in);
        $out = \Carbon\Carbon::parse($overtime->date_ot_out);
        $minutes = $in->diffInMinutes($out);
        $overtime->total_ot_minutes = $minutes;

        $overtime->save();

        // Logging Start
        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id'    => $userId,
            'global_user_id' => $globalUserId,
            'module'     => 'Employee Overtime',
            'action'     => 'clock_out_overtime',
            'description' => 'Clocked out from overtime, ID: ' . $overtime->id,
            'affected_id' => $overtime->id,
            'old_data'   => null,
            'new_data'   => json_encode($overtime->toArray()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime clock-out successful.',
            'data'    => $overtime
        ]);
    }
}
