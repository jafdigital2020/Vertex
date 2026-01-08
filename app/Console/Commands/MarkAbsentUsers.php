<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\LeaveRequest;
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
        $today     = Carbon::today()->toDateString();
        $dayOfWeek = strtolower(Carbon::today()->format('D'));
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
            // ✅ Check if shift is flexible
            $isFlexible = $assign->shift->is_flexible ?? false;

            // For flexible shifts: mark absent only at 11:59 PM
            if ($isFlexible) {
                $endOfDay = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $today . ' 23:59:00',
                    'Asia/Manila'
                );

                // Skip if it's not yet 11:59 PM
                if ($now->lt($endOfDay)) {
                    continue;
                }
            } else {
                // For regular shifts: check if shift has ended
                $startTime = $assign->shift->start_time;
                $endTime = $assign->shift->end_time;

                // Determine if this is a graveyard shift (crosses midnight)
                $isGraveyardShift = $endTime < $startTime;

                if ($isGraveyardShift) {
                    // For graveyard shifts (e.g., 10:00 PM - 6:00 AM)
                    // The shift ends on the next day
                    $shiftEnd = Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $today . ' ' . $endTime,
                        'Asia/Manila'
                    )->addDay();
                } else {
                    // For regular shifts (e.g., 8:00 AM - 5:00 PM)
                    // The shift ends on the same day
                    $shiftEnd = Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $today . ' ' . $endTime,
                        'Asia/Manila'
                    );
                }

                // skip if the shift hasn't ended yet
                if ($now->lt($shiftEnd)) {
                    continue;
                }
            }

            // ✅ Check if employee has an APPROVED OB for this date
            $hasApprovedOB = OfficialBusiness::where('user_id', $assign->user_id)
                ->whereDate('ob_date', $today)
                ->where('status', 'approved') // Only approved OB counts
                ->exists();

            if ($hasApprovedOB) {
                continue;
            }

            // Check if employee has an approved leave for this date
            $hasApprovedLeave = LeaveRequest::where('user_id', $assign->user_id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->exists();

            if ($hasApprovedLeave) {
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

        $this->info("Marked {$marked} absent record(s) on {$today}. Regular shifts marked after shift end, Flexible shifts marked at 11:59 PM.");
        return 0;
    }
}
