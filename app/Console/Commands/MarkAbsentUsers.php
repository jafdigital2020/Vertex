<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\ShiftAssignment;
use Illuminate\Console\Command;
use App\Models\OfficialBusiness;
use Illuminate\Support\Facades\Auth;

class MarkAbsentUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature   = 'attendance:mark-absent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark absent per shift if no attendance record exists once a shift has ended.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today     = Carbon::today()->toDateString();          // e.g. "2025-05-19"
        $dayOfWeek = strtolower(Carbon::today()->format('D')); // "Mon" → "mon"
        $now       = Carbon::now('Asia/Manila');

        // 1. Fetch all non-rest shift assignments for today, for users whose employmentDetail.status = 'active'
        $assignments = ShiftAssignment::with(['user.employmentDetail', 'shift'])
            ->whereHas(
                'user',
                fn($q) =>
                $q->whereHas(
                    'employmentDetail',
                    fn($ed) =>
                    $ed->where('status', '1')
                )
            )
            ->where('is_rest_day', false)
            ->where(function ($q) use ($today, $dayOfWeek) {
                // recurring
                $q->where(function ($r) use ($today, $dayOfWeek) {
                    $r->where('type', 'recurring')
                        ->where('start_date', '<=', $today)
                        ->where(fn($e) => $e->whereNull('end_date')->orWhere('end_date', '>=', $today))
                        ->whereJsonContains('days_of_week', [$dayOfWeek]);
                })
                    // custom
                    ->orWhere(function ($c) use ($today) {
                        $c->where('type', 'custom')
                            ->whereJsonContains('custom_dates', [$today])
                            ->whereJsonDoesntContain('excluded_dates', [$today]);
                    });
            })
            ->get();

        $marked = 0;

        foreach ($assignments as $assign) {
            // build full DateTime for this shift's end time today
            $shiftEnd = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $today . ' ' . $assign->shift->end_time,
                'Asia/Manila'
            );

            // skip if the shift hasn't ended yet
            if ($now->lt($shiftEnd)) {
                continue;
            }

            // ✅ Check if employee has an APPROVED OB for this date
            $hasApprovedOB = OfficialBusiness::where('user_id', $assign->user_id)
                ->whereDate('ob_date', $today)
                ->where('status', 'approved') // Only approved OB counts
                ->exists();

            if ($hasApprovedOB) {
                // ⏩ Skip marking absent because OB is approved
                continue;
            }


            // skip if attendance already exists for this shift today
            if (Attendance::where('shift_assignment_id', $assign->id)
                ->whereDate('attendance_date', $today)
                ->exists()
            ) {
                continue;
            }

            // create absent record
            Attendance::create([
                'user_id'             => $assign->user_id,
                'shift_id'            => $assign->shift_id,
                'shift_assignment_id' => $assign->id,
                'attendance_date'     => $today,
                'status'              => 'absent',
                'date_time_in'        => null,
                'date_time_out'       => null,
                'total_late_minutes'  => 0,
                'total_work_minutes'  => 0,
            ]);

            $marked++;
        }

        $this->info("Marked {$marked} absent record(s) for shifts ended by {$now->format('H:i')} on {$today}.");
        return 0;
    }
}
