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
                // reuse $dailyRate computed above
                $absDed = round($dailyRate * $absentDays->get($userId, 0), 2);
            }
            $absentDeductions[$userId] = $absDed;
        }

        // 5) --- NEW: Holiday pay logic with logging ---
        // a) Load holidays in range
        $period = CarbonPeriod::create($start->toDateString(), $end->toDateString());
        $monthDays = collect($period)
            ->map(fn($d) => $d->format('m-d'))
            ->unique()
            ->values()
            ->all();

        // 2) I-query ang Holiday model:
        $holidayRecords = Holiday::where(function ($q) use ($start, $end) {
            $q->whereBetween('date', [
                $start->toDateString(),
                $end->toDateString()
            ]);
        })
            ->orWhere(function ($q) use ($monthDays) {
                $q->where('recurring', 1)
                    ->whereIn('month_day', $monthDays);
            })
            ->get(['id', 'date', 'type', 'month_day', 'recurring']);

        $holidayIds = $holidayRecords->pluck('id')->all();

        // b) Load exceptions grouped by holiday_id
        $holidayExceptions = HolidayException::whereIn('holiday_id', $holidayIds)
            ->whereIn('user_id', $data['user_id'])
            ->get(['holiday_id', 'user_id'])
            ->groupBy('holiday_id')
            ->map(fn($rows) => $rows->pluck('user_id')->all());

        // c) Compute holiday pay per user
        $holidayInfo = collect();
        foreach ($data['user_id'] as $userId) {
            // prepare
            $sal       = $salaryData->get($userId, ['basic_salary' => 0, 'salary_type' => 'monthly_fixed', 'worked_days_per_year' => 0]);
            $basic     = $sal['basic_salary'];
            $stype     = $sal['salary_type'];
            $wpy       = $sal['worked_days_per_year'] ?? 0;

            // determine dailyRate
            if ($stype === 'hourly_rate') {
                $dailyRate = $basic * 8;
            } elseif ($stype === 'daily_rate') {
                $dailyRate = $basic;
            } elseif ($stype === 'monthly_fixed') {
                $dailyRate = $wpy > 0 ? ($basic * 12) / $wpy : 0;
            } else {
                $schedDays = ($absentDays->get($userId, 0) + $workDays->get($userId, 0));
                $dailyRate = $schedDays > 0 ? ($basic / $schedDays) : 0;
            }

            // init counters
            $holDays    = 0;
            $holWorkDays = 0;
            $payAmount  = 0;

            foreach ($holidayRecords as $h) {
                $hDate = $h->date;
                $hType = $h->type;
                $exceptUsers = $holidayExceptions->get($h->id, []);

                if (in_array($userId, $exceptUsers)) {
                    continue;
                }

                $worked = $attendances
                    ->where('user_id', $userId)
                    ->contains(fn($att) => $att->attendance_date->toDateString() === $hDate);

                if ($worked) {
                    $holWorkDays++;
                    $payAmount += $dailyRate * 1.3;
                } else {
                    if ($hType === 'regular') {
                        $holDays++;
                        $payAmount += $dailyRate;
                    } elseif (
                        in_array($hType, ['special-non-working', 'special-working'])
                        && $stype === 'monthly_fixed'
                    ) {
                        $holDays++;
                        $payAmount += $dailyRate;
                    }
                }
            }

            $holidayInfo[$userId] = [
                'holiday_days'       => $holDays,
                'holiday_work_days'  => $holWorkDays,
                'holiday_pay_amount' => round($payAmount, 2),
            ];

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
