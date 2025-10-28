<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessZktecoAttendance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $attendanceLog;

    public function __construct(AttendanceLog $attendanceLog)
    {
        $this->attendanceLog = $attendanceLog;
    }

    public function handle()
    {
        if (!$this->attendanceLog->user_id) {
            Log::warning('No user found for attendance log', ['log_id' => $this->attendanceLog->id]);
            return;
        }

        $user = User::find($this->attendanceLog->user_id);
        $date = $this->attendanceLog->check_time->format('Y-m-d');

        // Find or create attendance record
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'attendance_date' => $date,
            ],
            [
                'status' => 'present',
            ]
        );

        // Update based on status (in/out)
        if ($this->attendanceLog->status === 'in') {
            // Clock IN
            if (!$attendance->date_time_in) {
                $attendance->date_time_in = $this->attendanceLog->check_time;
                $attendance->clock_in_method = 'biometric';
            } else {
                // Multiple login
                $multipleLogin = $attendance->multiple_login ?? [];
                $multipleLogin[] = [
                    'in' => $this->attendanceLog->check_time->toDateTimeString(),
                ];
                $attendance->multiple_login = $multipleLogin;
            }
        } else {
            // Clock OUT
            $attendance->date_time_out = $this->attendanceLog->check_time;
            $attendance->clock_out_method = 'biometric';

            // Calculate work minutes if both in and out exist
            if ($attendance->date_time_in && $attendance->date_time_out) {
                $attendance->total_work_minutes = $attendance->date_time_in
                    ->diffInMinutes($attendance->date_time_out);
            }
        }

        $attendance->save();

        Log::info('Attendance processed from ZKTeco', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'log_id' => $this->attendanceLog->id,
        ]);
    }
}
