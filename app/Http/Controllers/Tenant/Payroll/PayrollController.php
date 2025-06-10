<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Holiday;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\SalaryRecord;
use Illuminate\Http\Request;
use App\Models\HolidayException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    // Process Index
    public function payrollProcessIndex(Request $request)
    {
        $branches = Branch::all();
        $departments = Department::all();
        $designations = Designation::all();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll Process Index',
                'data' => []
            ]);
        }

        return view('tenant.payroll.process', compact('branches', 'departments', 'designations'));
    }

    // Payroll Process Store
    public function payrollProcessStore(Request $request)
    {
        $data = $request->validate([
            'user_id'    => 'required|array',
            'user_id.*'  => 'integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $start    = Carbon::parse($data['start_date'])->startOfDay();
        $end      = Carbon::parse($data['end_date'])->endOfDay();

        // Base query to reuse
        $baseQuery = Attendance::with(['user', 'shift'])
            ->whereIn('user_id', $data['user_id'])
            ->whereBetween('attendance_date', [$start, $end])
            ->whereHas('user', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });

        // Fetch attendances
        $attendances = (clone $baseQuery)
            ->orderBy('attendance_date')
            ->get();

        // Sum total_work_minutes per user
        $totalWorkMinutes = (clone $baseQuery)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_work_minutes) as total_minutes'))
            ->pluck('total_minutes', 'user_id');

        // Sum total_late_minutes per user
        $totalLateMinutes = (clone $baseQuery)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_late_minutes) as total_late_minutes'))
            ->pluck('total_late_minutes', 'user_id');

        // Sum total_undertime per user
        $totalUndertimeMinutes = (clone $baseQuery)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_undertime_minutes) as total_undertime_minutes'))
            ->pluck('total_undertime_minutes', 'user_id');

        // Sum Total Night Differential Minutes per user
        $totalNightDiffMinutes = (clone $baseQuery)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_night_diff_minutes) as total_night_diff_minutes'))
            ->pluck('total_night_diff_minutes', 'user_id');

        // Total Absent
        $absentDays = (clone $baseQuery)
            ->where('status', 'absent')
            ->groupBy('user_id')
            ->select('user_id', DB::raw('COUNT(*) as absent_count'))
            ->pluck('absent_count', 'user_id');

        // Total Work Days
        $workDays = (clone $baseQuery)
            ->where('status', '!=', 'absent')
            ->groupBy('user_id')
            ->select('user_id', DB::raw('COUNT(*) as work_days'))
            ->pluck('work_days', 'user_id');

        // Fetch Salary Records
        $salaryData = SalaryRecord::whereIn('user_id', $data['user_id'])
            ->where('is_active', 1)
            ->get()
            ->mapWithKeys(fn($rec) => [
                $rec->user_id => [
                    'basic_salary' => $rec->basic_salary,
                    'salary_type'  => $rec->salary_type,
                    'worked_days_per_year'   => $rec->user->salaryDetail->worked_days_per_year ?? 0,
                ]
            ]);

        $lateDeductions      = collect();
        $undertimeDeductions = collect();
        $absentDeductions    = collect();
        $perMinRate = 0;

        // Late and Undertime Deductions Calculation
        foreach ($data['user_id'] as $userId) {
            $lateMins = $totalLateMinutes->get($userId, 0);
            $undertimeMins = $totalUndertimeMinutes->get($userId, 0);
            $sal      = $salaryData->get($userId, [
                'basic_salary'         => 0,
                'salary_type'          => 'monthly',
                'worked_days_per_year' => 0,
            ]);
            $basic    = $sal['basic_salary'];
            $stype    = $sal['salary_type'];

            if ($stype === 'hourly_rate') {
                $perMinRate = $basic / 60;
            } elseif ($stype === 'daily_rate') {
                $perMinRate = ($basic / 8) / 60;
            } elseif ($stype === 'monthly_fixed') {
                $wpy = $sal['worked_days_per_year'];
                $annualSalary = $basic * 12;
                $dailyRate    = $wpy > 0 ? $annualSalary / $wpy : 0;
                $perHourRate  = $dailyRate / 8;
                $perMinRate   = $perHourRate / 60;
            } else {
                $schedDays = ($absentDays->get($userId, 0) + $workDays->get($userId, 0));
                $minutesInPeriod = $schedDays * 8 * 60;
                $perMinRate = $minutesInPeriod > 0
                    ? ($basic / $minutesInPeriod)
                    : 0;
            }

            $lateDeductions[$userId] = round($perMinRate * $lateMins, 2);
            $undertimeDeductions[$userId] = round($perMinRate * $undertimeMins, 2);

            // Absent Deductions
            $absDed = 0;
            if ($stype === 'monthly_fixed') {
                $absDed = round($dailyRate * $absentDays->get($userId, 0), 2);
            }
            $absentDeductions[$userId] = $absDed;
        }

        // Holiday pay logic
        $period    = CarbonPeriod::create($start->toDateString(), $end->toDateString());
        $monthDays = collect($period)
            ->map(fn($d) => $d->format('m-d'))
            ->unique()
            ->values()
            ->all();

        // b) Fetch holidays: fixed-date OR recurring
        $holidayRecords = Holiday::where(function ($q) use ($start, $end) {
            $q->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        })
            ->orWhere(function ($q) use ($monthDays) {
                $q->where('recurring', 1)
                    ->whereIn('month_day', $monthDays);
            })
            ->get(['id', 'date', 'type', 'month_day', 'recurring']);

        Log::info('Fetched Holidays (fixed & recurring)', [
            'range'       => [$start->toDateString(), $end->toDateString()],
            'month_days'  => $monthDays,
            'holidays'    => $holidayRecords->toArray(),
        ]);

        // Fetch exceptions for those holidays
        $holidayExceptions = HolidayException::whereIn('holiday_id', $holidayRecords->pluck('id'))
            ->whereIn('user_id', $data['user_id'])
            ->get(['holiday_id', 'user_id'])
            ->groupBy('holiday_id')
            ->map(fn($rows) => $rows->pluck('user_id')->all());

        Log::info('Fetched Holiday Exceptions', [
            'exceptions' => $holidayExceptions->toArray(),
        ]);

        // Compute per-user holiday pay
        $holidayInfo = collect();

        foreach ($data['user_id'] as $userId) {
            // salary & dailyRate setup
            $sal   = $salaryData->get($userId, [
                'basic_salary'         => 0,
                'salary_type'          => 'monthly_fixed',
                'worked_days_per_year' => 0,
            ]);
            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];
            $wpy   = $sal['worked_days_per_year'] ?? 0;

            // determine dailyRate
            if ($stype === 'hourly_rate') {
                $dailyRate = $basic * 8;
            } elseif ($stype === 'daily_rate') {
                $dailyRate = $basic;
            } elseif ($stype === 'monthly_fixed') {
                $dailyRate = $wpy > 0
                    ? ($basic * 12) / $wpy
                    : 0;
            } else {
                // fallback for non-fixed monthly types
                $schedDays  = ($absentDays->get($userId, 0) + $workDays->get($userId, 0));
                $dailyRate  = $schedDays > 0
                    ? ($basic / $schedDays)
                    : 0;
            }

            Log::info("User {$userId} holiday dailyRate computed", [
                'salary_type' => $stype,
                'daily_rate'  => $dailyRate,
            ]);

            $holDays     = 0;
            $holWorkDays = 0;
            $payAmount   = 0;

            foreach ($holidayRecords as $h) {
                $hDate       = $h->date;
                $hType       = $h->type;
                $exceptUsers = $holidayExceptions->get($h->id, []);

                // log initial evaluation
                Log::info("Evaluating holiday #{$h->id} for user {$userId}", [
                    'date'         => $hDate,
                    'type'         => $hType,
                    'recurring'    => (bool)$h->recurring,
                    'month_day'    => $h->month_day,
                    'is_exception' => in_array($userId, $exceptUsers),
                ]);

                // skip if excepted
                if (in_array($userId, $exceptUsers)) {
                    Log::info(" → skipped (exception)");
                    continue;
                }

                // find attendance record on that date
                $att = $attendances->first(
                    fn($a) =>
                    $a->user_id === $userId
                        && $a->attendance_date->toDateString() === $hDate
                );
                $worked = (bool)$att;

                // WORKED ON HOLIDAY
                if ($worked) {
                    // special holiday worked
                    if (in_array($hType, ['special-non-working', 'special-working'])) {
                        if ($stype === 'hourly_rate') {
                            // use actual minutes and 130% rate
                            $mins        = $att->total_work_minutes;
                            $perMinRate  = $basic / 60;
                            $linePay     = $perMinRate * $mins * 1.3;
                            $holWorkDays++;
                            $payAmount  += $linePay;

                            Log::info(" → worked special holiday (hourly)", [
                                'minutes'     => $mins,
                                'per_min'     => round($perMinRate, 4),
                                'line_pay'    => round($linePay, 2),
                            ]);
                        } else {
                            // daily or monthly: full dailyRate ×130%
                            $linePay    = $dailyRate * 1.3;
                            $holWorkDays++;
                            $payAmount += $linePay;

                            Log::info(" → worked special holiday", [
                                'daily_rate' => $dailyRate,
                                'line_pay'   => round($linePay, 2),
                            ]);
                        }
                    }
                    // regular holiday worked → 100%
                    else {
                        $linePay    = $dailyRate * 1.0;
                        $holWorkDays++;
                        $payAmount += $linePay;

                        Log::info(" → worked regular holiday", [
                            'daily_rate' => $dailyRate,
                            'line_pay'   => round($linePay, 2),
                        ]);
                    }
                }
                // ABSENT ON HOLIDAY
                else {
                    // regular holiday → 100%
                    if ($hType === 'regular') {
                        $linePay    = $dailyRate * 1.0;
                        $holDays++;
                        $payAmount += $linePay;

                        Log::info(" → absent regular holiday", [
                            'daily_rate' => $dailyRate,
                            'line_pay'   => round($linePay, 2),
                        ]);
                    }
                    // special holiday absent → only monthly_fixed gets 100%
                    elseif (
                        in_array($hType, ['special-non-working', 'special-working'])
                        && $stype === 'monthly_fixed'
                    ) {
                        $linePay    = $dailyRate * 1.0;
                        $holDays++;
                        $payAmount += $linePay;

                        Log::info(" → absent special holiday (monthly_fixed)", [
                            'daily_rate' => $dailyRate,
                            'line_pay'   => round($linePay, 2),
                        ]);
                    } else {
                        Log::info(" → absent special holiday (no pay)");
                    }
                }
            }

            // store per-user summary
            $holidayInfo[$userId] = [
                'holiday_days'       => $holDays,
                'holiday_work_days'  => $holWorkDays,
                'holiday_pay_amount' => round($payAmount, 2),
            ];

            Log::info("Computed holiday info for user {$userId}", $holidayInfo[$userId]);

            $holidayPayTotals = $holidayInfo->mapWithKeys(fn($info, $userId) => [
                $userId => $info['holiday_pay_amount']
            ]);

            Log::info('Total Holiday Pay per User', [
                'holiday_pay_totals' => $holidayPayTotals->toArray()
            ]);
        }

        Log::info('Payroll Process Store', [
            'tenant_id'    => $tenantId,
            'user_ids'     => $data['user_id'],
            'start_date'   => $data['start_date'],
            'end_date'     => $data['end_date'],
            'attendances'  => $attendances->count(),
            'totals'       => $totalWorkMinutes->toArray(),
            'late_totals'  => $totalLateMinutes->toArray(),
            'undertime_totals' => $totalUndertimeMinutes->toArray(),
            'night_diff_totals' => $totalNightDiffMinutes->toArray(),
            'absent days' => $absentDays->toArray(),
            'work_days' => $workDays->toArray(),
            'salary_data'   => $salaryData->toArray(),
            'late_deductions' => $lateDeductions->toArray(),
            'undertime_deductions' => $undertimeDeductions->toArray(),
            'absent_deductions' => $absentDeductions->toArray(),
            'holiday_info' => $holidayInfo->toArray(),
        ]);

        return response()->json([
            'attendances' => $attendances,
            'totals'      => $totalWorkMinutes,
            'late_totals' => $totalLateMinutes,
        ]);
    }
}
