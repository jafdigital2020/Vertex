<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConsolidateAttendanceForDate implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Carbon $forDate) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tz = config('app.timezone', 'Asia/Manila');
        $date = $this->forDate->copy()->startOfDay();
        $start = $date->copy();
        $end   = $date->copy()->endOfDay();

        $byUser = AttendanceLog::whereBetween('check_time', [$start, $end])
            ->whereNotNull('employee_id')
            ->orderBy('check_time')
            ->get()
            ->groupBy('employee_id');

        foreach ($byUser as $emp => $logs) {
            $punches = $logs->pluck('check_time')->sort()->values();
            if ($punches->isEmpty()) continue;

            $timeIn  = $punches->first();
            $timeOut = $punches->count() > 1 ? $punches->last() : null;

            // extras (simple handling)
            $extras = $punches->slice(1, max(0, $punches->count() - 2));
            $extraIns = [];
            $extraOuts = [];

            foreach ($extras as $i => $dt) {
                if ($i % 2 == 0) $extraIns[] = $dt->toDateTimeString();
                else $extraOuts[] = $dt->toDateTimeString();
            }

            $totalWork = 0;
            if ($timeIn && $timeOut && $timeOut->gt($timeIn)) {
                $totalWork = $timeIn->diffInMinutes($timeOut);
            }

            // map employee -> user_id
            $userId = $logs->first()->user_id ?? null;

            // upsert into your existing attendance table
            $attendance = Attendance::firstOrNew([
                'user_id' => $userId,
                'attendance_date' => $date->toDateString(),
            ]);

            $attendance->date_time_in  = $timeIn;
            $attendance->date_time_out = $timeOut;
            $attendance->multiple_login  = array_merge($attendance->multiple_login ?? [], $extraIns);
            $attendance->multiple_logout = array_merge($attendance->multiple_logout ?? [], $extraOuts);
            $attendance->status = 'present';
            $attendance->total_work_minutes = $totalWork;
            $attendance->save();
        }
    }
}
