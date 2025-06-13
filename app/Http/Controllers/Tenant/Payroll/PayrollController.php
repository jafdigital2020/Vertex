<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Holiday;
use App\Models\Overtime;
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

        $tenantId   = Auth::user()->tenant_id;

        $attendances = $this->getAttendances($tenantId, $data);

        $overtimes = $this->getOvertime($tenantId, $data);

        $totals = $this->sumMinutes($tenantId, $data);
        Log::info('📊 Computed attendance and overtime totals', $totals);

        $salaryData = $this->getSalaryData($data['user_id']);

        $deductions = $this->calculateDeductions($data['user_id'], $totals, $salaryData);

        $holidayInfo = $this->calculateHolidayPay($attendances, $data, $salaryData);

        $nightDiffInfo = $this->calculateNightDifferential($data['user_id'], $data, $salaryData);
        Log::info('🌙 Computed night differential pay', $nightDiffInfo);

        $overtimePay = $this->calculateOvertimePay($data['user_id'], $data, $salaryData);
        Log::info('⏰ Computed overtime pay', $overtimePay);

        return response()->json([
            'attendances'       => $attendances,
            'totals'            => $totals['work'],
            'late_totals'       => $totals['late'],
            'undertime_totals'  => $totals['undertime'],
            'night_diff_totals' => $totals['night_diff'],
            'absent_days'       => $totals['absent'],
            'work_days'         => $totals['work_days'],
            'deductions'        => $deductions,
            'holiday'           => $holidayInfo,
            'night_diff_pay'    => $nightDiffInfo,
            'overtimes'         => $overtimes,
        ]);
    }

    // Attendance Getter
    protected function getAttendances(int $tenantId, array $data)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();

        return Attendance::with(['user', 'shift'])
            ->whereIn('user_id', $data['user_id'])
            ->whereBetween('attendance_date', [$start, $end])
            ->whereHas('user', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->orderBy('attendance_date')
            ->get();
    }

    // Overtime Getter
    protected function getOvertime(int $tenantId, array $data)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();

        return Overtime::with(['user'])
            ->whereIn('user_id', $data['user_id'])
            ->whereBetween('overtime_date', [$start, $end])
            ->whereHas('user', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->orderBy('overtime_date')
            ->get();
    }

    // Sum Minutes
    protected function sumMinutes(int $tenantId, array $data)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();

        $base = Attendance::whereIn('user_id', $data['user_id'])
            ->whereBetween('attendance_date', [$start, $end])
            ->whereHas('user', fn($q) => $q->where('tenant_id', $tenantId));

        $baseOt = Overtime::whereIn('user_id', $data['user_id'])
            ->whereBetween('overtime_date', [$start, $end])
            ->whereHas('user', fn($q) => $q->where('tenant_id', $tenantId));

        $work      = (clone $base)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_work_minutes) as total'))
            ->pluck('total', 'user_id')->toArray();
        $late      = (clone $base)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_late_minutes) as total'))
            ->pluck('total', 'user_id')->toArray();
        $undertime = (clone $base)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_undertime_minutes) as total'))
            ->pluck('total', 'user_id')->toArray();
        $nightDiff = (clone $base)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_night_diff_minutes) as total'))
            ->pluck('total', 'user_id')->toArray();
        $absent    = (clone $base)
            ->where('status', 'absent')
            ->groupBy('user_id')
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'user_id')->toArray();
        $workDays  = (clone $base)
            ->where('status', '!=', 'absent')
            ->groupBy('user_id')
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'user_id')->toArray();

        $workOt = (clone $baseOt)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_ot_minutes) as total'))
            ->pluck('total', 'user_id')->toArray();

        return [
            'work'        => $work,
            'late'        => $late,
            'undertime'   => $undertime,
            'night_diff'  => $nightDiff,
            'absent'      => $absent,
            'work_days'   => $workDays,
            'workOt'      => $workOt,
        ];
    }

    // Get Salary Data
    protected function getSalaryData(array $userIds)
    {
        return SalaryRecord::whereIn('user_id', $userIds)
            ->where('is_active', 1)
            ->get()
            ->mapWithKeys(fn($r) => [
                $r->user_id => [
                    'basic_salary'         => $r->basic_salary,
                    'salary_type'          => $r->salary_type,
                    'worked_days_per_year' => $r->user->salaryDetail->worked_days_per_year ?? 0,
                ]
            ]);
    }

    // Calculate Deductions (Late, Undertime, Absent)
    protected function calculateDeductions(array $userIds, array $totals, $salaryData)
    {
        $lateDeductions      = [];
        $undertimeDeductions = [];
        $absentDeductions    = [];

        foreach ($userIds as $id) {
            $late  = $totals['late'][$id] ?? 0;
            $under = $totals['undertime'][$id] ?? 0;
            $sal   = $salaryData->get($id, [
                'basic_salary'         => 0,
                'salary_type'          => 'monthly_fixed',
                'worked_days_per_year' => 0,
            ]);

            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];

            // determine per-minute rate
            if ($stype === 'hourly_rate') {
                $perMin = $basic / 60;
            } elseif ($stype === 'daily_rate') {
                $perMin = ($basic / 8) / 60;
            } elseif ($stype === 'monthly_fixed') {
                $wpy          = $sal['worked_days_per_year'];
                $annualSalary = $basic * 12;
                $dailyRate    = $wpy > 0 ? $annualSalary / $wpy : 0;
                $perMin       = ($dailyRate / 8) / 60;
            } else {
                $sched = ($totals['absent'][$id] ?? 0) + ($totals['workDays'][$id] ?? 0);
                $mins  = $sched * 8 * 60;
                $perMin = $mins > 0 ? ($basic / $mins) : 0;
            }

            $lateDeductions[$id]      = round($perMin * $late, 2);
            $undertimeDeductions[$id] = round($perMin * $under, 2);

            // absent only for monthly_fixed
            $absDed = 0;
            if ($stype === 'monthly_fixed') {
                $absDed = round($dailyRate * ($totals['absent'][$id] ?? 0), 2);
            }
            $absentDeductions[$id] = $absDed;
        }

        return compact('lateDeductions', 'undertimeDeductions', 'absentDeductions');
    }

    // Calculate Holiday Pay
    protected function calculateHolidayPay($attendances, array $data, $salaryData)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();
        $period = CarbonPeriod::create($start->toDateString(), $end->toDateString());
        $monthDays = collect($period)
            ->map(fn($d) => $d->format('m-d'))
            ->unique()->values()->all();

        $hols = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orWhere(fn($q) => $q->where('recurring', 1)->whereIn('month_day', $monthDays))
            ->get(['id', 'date', 'type', 'month_day', 'recurring']);

        $exceptions = HolidayException::whereIn('holiday_id', $hols->pluck('id'))
            ->whereIn('user_id', $data['user_id'])
            ->get(['holiday_id', 'user_id'])
            ->groupBy('holiday_id')
            ->map(fn($rows) => $rows->pluck('user_id')->all());

        $result = [];
        foreach ($data['user_id'] as $id) {
            $sal = $salaryData->get($id, [
                'basic_salary'         => 0,
                'salary_type'          => 'monthly_fixed',
                'worked_days_per_year' => 0,
            ]);
            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];
            $wpy   = $sal['worked_days_per_year'];

            if ($stype === 'hourly_rate') {
                $dailyRate = $basic * 8;
            } elseif ($stype === 'daily_rate') {
                $dailyRate = $basic;
            } elseif ($stype === 'monthly_fixed') {
                $dailyRate = $wpy > 0 ? ($basic * 12) / $wpy : 0;
            } else {
                $sd = ($data['workDays'][$id] ?? 0) + ($data['absent'][$id] ?? 0);
                $dailyRate = $sd > 0 ? ($basic / $sd) : 0;
            }

            $holDays = $holWork = $payTotal = 0;

            foreach ($hols as $h) {
                if (in_array($id, $exceptions->get($h->id, []))) continue;
                $att = $attendances->firstWhere(
                    fn($a) =>
                    $a->user_id === $id && $a->attendance_date->toDateString() === $h->date
                );
                $worked = (bool)$att;
                $mins   = $worked ? $att->total_work_minutes : 0;
                $pay    = 0;

                if ($worked) {
                    if (in_array($stype, ['hourly_rate', 'daily_rate'])) {
                        $perMin = $stype === 'hourly_rate' ? $basic / 60 : ($basic / 8) / 60;
                        $pay = $perMin * $mins * 1.3;
                    } else {
                        $pay = $dailyRate * 1.3;
                    }
                    $holWork++;
                } else {
                    if ($h->type === 'regular') {
                        $pay = $stype === 'hourly_rate'
                            ? ($basic / 60) * 480 : $dailyRate;
                        $holDays++;
                    } elseif (
                        in_array($h->type, ['special-non-working', 'special-working'])
                        && $stype === 'monthly_fixed'
                    ) {
                        $pay = $dailyRate;
                        $holDays++;
                    }
                }
                $payTotal += $pay;
            }
            $result[$id] = [
                'holiday_days' => $holDays,
                'holiday_work_days' => $holWork,
                'holiday_pay_amount' => round($payTotal, 2),
            ];
        }

        return $result;
    }

    // Calculate Night Differential Pay
    protected function calculateNightDifferential(array $userIds, array $data, $salaryData)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();

        $multipliers = DB::table('ot_tables')->pluck('night_differential', 'type');

        $ndBase = Attendance::whereIn('user_id', $userIds)
            ->whereBetween('attendance_date', [$start, $end])
            ->where('total_night_diff_minutes', '>', 0);

        $ordMins = (clone $ndBase)
            ->where('is_rest_day', false)
            ->where('is_holiday', false)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_night_diff_minutes) as mins'))
            ->pluck('mins', 'user_id');

        $rstMins = (clone $ndBase)
            ->where('is_rest_day', true)
            ->where('is_holiday', false)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_night_diff_minutes) as mins'))
            ->pluck('mins', 'user_id');

        $holAtts = Attendance::whereIn('user_id', $userIds)
            ->whereBetween('attendance_date', [$start, $end])
            ->where('total_night_diff_minutes', '>', 0)
            ->where('is_holiday', true)
            ->where('is_rest_day', false)
            ->with('holiday')
            ->get()
            ->groupBy('user_id');

        // New: Holiday + Rest Day attendances
        $holRstAtts = Attendance::whereIn('user_id', $userIds)
            ->whereBetween('attendance_date', [$start, $end])
            ->where('total_night_diff_minutes', '>', 0)
            ->where('is_holiday', true)
            ->where('is_rest_day', true)
            ->with('holiday')
            ->get()
            ->groupBy('user_id');

        $result = [];
        foreach ($userIds as $id) {
            $sal   = $salaryData->get($id, ['basic_salary' => 0, 'salary_type' => 'hourly_rate']);
            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];
            $ord  = $ordMins->get($id, 0);
            $rst  = $rstMins->get($id, 0);
            $mOrd = $multipliers['ordinary'] ?? 0;
            $mRst = $multipliers['rest_day'] ?? 0;

            // Log multipliers for ordinary and rest day
            Log::info("Night diff multiplier used for user $id (ordinary): $mOrd");
            Log::info("Night diff multiplier used for user $id (rest_day): $mRst");

            $payOrd = $payRst = 0;
            $holPay = 0;

            if (in_array($stype, ['hourly_rate', 'daily_rate']) && ($ord + $rst) > 0) {
                $perMin = $stype === 'hourly_rate' ? $basic / 60 : ($basic / 8) / 60;
                $payOrd = round($perMin * $ord * $mOrd, 2);
                $payRst = round($perMin * $rst * $mRst, 2);
            }

            // Holiday only
            if (in_array($stype, ['hourly_rate', 'daily_rate'])) {
                $perMin = $stype === 'hourly_rate' ? $basic / 60 : ($basic / 8) / 60;

                foreach ($holAtts->get($id, collect()) as $att) {
                    $holidayOrig = optional($att->holiday)->date;
                    $hType = optional($att->holiday)->type;

                    $attIn = Carbon::parse($att->date_time_in);
                    $attOut = Carbon::parse($att->date_time_out);

                    $holidayStart = Carbon::parse($holidayOrig)->setYear($attIn->year)->startOfDay();
                    $holidayEnd = $holidayStart->copy()->addDay()->startOfDay();

                    $nextDay = $holidayEnd->copy();
                    $nextHoliday = Holiday::where('date', $nextDay->toDateString())->first();

                    if ($nextHoliday) {
                        $nextHolidayStart = $nextDay->copy();
                        $nextHolidayEnd = $nextDay->copy()->addHours(6);
                    } else {
                        $nextHolidayStart = null;
                        $nextHolidayEnd = null;
                    }

                    $startOverlap = $attIn->greaterThan($holidayStart) ? $attIn : $holidayStart;
                    $endOverlap   = $attOut->lessThan($holidayEnd) ? $attOut : $holidayEnd;

                    $holidayMin = $startOverlap->lt($endOverlap) ? $startOverlap->diffInMinutes($endOverlap) : 0;
                    $holidayMin = max(0, $holidayMin);

                    $nextHolidayMin = 0;
                    $nextHolNDMin = 0;
                    $nextHMult = 0;
                    if ($nextHolidayStart && $attOut->gt($nextHolidayStart)) {
                        $nextStartOverlap = $attIn->greaterThan($nextHolidayStart) ? $attIn : $nextHolidayStart;
                        $nextEndOverlap = $attOut->lessThan($nextHolidayEnd) ? $attOut : $nextHolidayEnd;
                        $nextHolidayMin = $nextStartOverlap->lt($nextEndOverlap) ? $nextStartOverlap->diffInMinutes($nextEndOverlap) : 0;
                        $nextHolidayMin = max(0, $nextHolidayMin);
                        $nextHolNDMin = min($nextHolidayMin, $att->total_night_diff_minutes - $holidayMin);
                        $nextHolNDMin = max(0, (int) $nextHolNDMin);
                        $nextMultKey = $nextHoliday->type === 'regular' ? 'regular_holiday' : 'special_holiday';
                        $nextHMult = $multipliers[$nextMultKey] ?? 0;
                        // Log multiplier for next holiday
                        Log::info("Night diff multiplier used for user $id (next holiday: $nextMultKey): $nextHMult");
                    }

                    $holNDMin   = min($holidayMin, $att->total_night_diff_minutes);
                    $holNDMin   = max(0, (int) $holNDMin);

                    $usedNDMin = $holNDMin + $nextHolNDMin;

                    $ordNDMin   = $att->total_night_diff_minutes - $usedNDMin;
                    $ordNDMin   = max(0, (int) $ordNDMin);

                    $multKey      = $hType === 'regular' ? 'regular_holiday' : 'special_holiday';
                    $hMult        = $multipliers[$multKey] ?? 0;
                    // Log multiplier for holiday
                    Log::info("Night diff multiplier used for user $id (holiday: $multKey): $hMult");

                    $holPay += round($perMin * $holNDMin * $hMult, 2);

                    if ($nextHolNDMin > 0 && $nextHMult > 0) {
                        $holPay += round($perMin * $nextHolNDMin * $nextHMult, 2);
                    }

                    if ($ordNDMin > 0) {
                        $payOrd += round($perMin * $ordNDMin * $mOrd, 2);
                    }
                }

                // Holiday + Rest Day
                foreach ($holRstAtts->get($id, collect()) as $att) {
                    $holidayOrig = optional($att->holiday)->date;
                    $hType = optional($att->holiday)->type;

                    $attIn = Carbon::parse($att->date_time_in);
                    $attOut = Carbon::parse($att->date_time_out);

                    $holidayStart = Carbon::parse($holidayOrig)->setYear($attIn->year)->startOfDay();
                    $holidayEnd = $holidayStart->copy()->addDay()->startOfDay();

                    $nextDay = $holidayEnd->copy();
                    $nextHoliday = Holiday::where('date', $nextDay->toDateString())->first();

                    if ($nextHoliday) {
                        $nextHolidayStart = $nextDay->copy();
                        $nextHolidayEnd = $nextDay->copy()->addHours(6);
                    } else {
                        $nextHolidayStart = null;
                        $nextHolidayEnd = null;
                    }

                    $startOverlap = $attIn->greaterThan($holidayStart) ? $attIn : $holidayStart;
                    $endOverlap   = $attOut->lessThan($holidayEnd) ? $attOut : $holidayEnd;

                    $holidayMin = $startOverlap->lt($endOverlap) ? $startOverlap->diffInMinutes($endOverlap) : 0;
                    $holidayMin = max(0, $holidayMin);

                    $nextHolidayMin = 0;
                    $nextHolNDMin = 0;
                    $nextHMult = 0;
                    if ($nextHolidayStart && $attOut->gt($nextHolidayStart)) {
                        $nextStartOverlap = $attIn->greaterThan($nextHolidayStart) ? $attIn : $nextHolidayStart;
                        $nextEndOverlap = $attOut->lessThan($nextHolidayEnd) ? $attOut : $nextHolidayEnd;
                        $nextHolidayMin = $nextStartOverlap->lt($nextEndOverlap) ? $nextStartOverlap->diffInMinutes($nextEndOverlap) : 0;
                        $nextHolidayMin = max(0, $nextHolidayMin);
                        $nextHolNDMin = min($nextHolidayMin, $att->total_night_diff_minutes - $holidayMin);
                        $nextHolNDMin = max(0, (int) $nextHolNDMin);
                        $nextMultKey = $nextHoliday->type === 'regular'
                            ? 'regular_holiday_rest_day'
                            : 'special_holiday_rest_day';
                        $nextHMult = $multipliers[$nextMultKey] ?? 0;
                        // Log multiplier for next holiday + rest day
                        Log::info("Night diff multiplier used for user $id (next holiday+rest: $nextMultKey): $nextHMult");
                    }

                    $holNDMin   = min($holidayMin, $att->total_night_diff_minutes);
                    $holNDMin   = max(0, (int) $holNDMin);

                    $usedNDMin = $holNDMin + $nextHolNDMin;

                    $ordNDMin   = $att->total_night_diff_minutes - $usedNDMin;
                    $ordNDMin   = max(0, (int) $ordNDMin);

                    $multKey = $hType === 'regular'
                        ? 'regular_holiday_rest_day'
                        : 'special_holiday_rest_day';
                    $hMult = $multipliers[$multKey] ?? 0;
                    // Log multiplier for holiday + rest day
                    Log::info("Night diff multiplier used for user $id (holiday+rest: $multKey): $hMult");

                    $holPay += round($perMin * $holNDMin * $hMult, 2);

                    if ($nextHolNDMin > 0 && $nextHMult > 0) {
                        $holPay += round($perMin * $nextHolNDMin * $nextHMult, 2);
                    }

                    if ($ordNDMin > 0) {
                        $payOrd += round($perMin * $ordNDMin * $mOrd, 2);
                    }
                }
            }

            $holPay = round($holPay, 2);

            $result[$id] = [
                'ordinary_pay' => $payOrd,
                'rest_day_pay' => $payRst,
                'holiday_pay' => $holPay,
            ];
        }

        return $result;
    }

    // Calculate Overtime Pay
    protected function calculateOvertimePay(array $userIds, array $data, $salaryData)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();

        $otMultipliers = DB::table('ot_tables')->pluck('overtime', 'type');

        $otBase = Overtime::whereIn('user_id', $userIds)
            ->whereBetween('overtime_date', [$start, $end])
            ->where('status', 'approved')
            ->where('total_ot_minutes', '>', 0);

        // Ordinary
        $ordMins = (clone $otBase)
            ->where('is_rest_day', false)
            ->where('is_holiday', false)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_ot_minutes) as mins'))
            ->pluck('mins', 'user_id');

        // Rest Day
        $otRdMins = (clone $otBase)
            ->where('is_rest_day', true)
            ->where('is_holiday', false)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_ot_minutes) as mins'))
            ->pluck('mins', 'user_id');

        // Holiday
        $otHol = (clone $otBase)
            ->where('is_holiday', true)
            ->where('is_rest_day', false)
            ->with('holiday')
            ->get()
            ->groupBy('user_id');

        // Holiday + Rest Day
        $otHolRst = (clone $otBase)
            ->where('is_holiday', true)
            ->where('is_rest_day', true)
            ->with('holiday')
            ->get()
            ->groupBy('user_id');


        $result = [];
        foreach ($userIds as $id) {
            $sal   = $salaryData->get($id, ['basic_salary' => 0, 'salary_type' => 'hourly_rate']);
            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];
            $ord  = $ordMins->get($id, 0);
            $mOrd = $otMultipliers['ordinary'] ?? 0;
            $mRst = $otMultipliers['rest_day'] ?? 0;
            $rd  = $otRdMins->get($id, 0);

            $payOrd = 0;
            $payRd = 0;
            $payHol = 0;
            $payRdHol = 0;

            // Normal overtime pay
            if (in_array($stype, ['hourly_rate', 'daily_rate']) && $ord > 0) {
                $perMin = $stype === 'hourly_rate' ? $basic / 60 : ($basic / 8) / 60;
                $payOrd = round($perMin * $ord * $mOrd, 2);
            }

            // Rest day overtime pay
            if (in_array($stype, ['hourly_rate', 'daily_rate']) && $rd > 0) {
                $perMin = $stype === 'hourly_rate' ? $basic / 60 : ($basic / 8) / 60;
                $payRd = round($perMin * $rd * $mRst, 2);
            }

            // Holiday overtime pay
            if (in_array($stype, ['hourly_rate', 'daily_rate']) && $otHol->has($id)) {
                $perMin = $stype === 'hourly_rate' ? $basic / 60 : ($basic / 8) / 60;
                foreach ($otHol[$id] as $ot) {
                    $holidayType = optional($ot->holiday)->type;
                    // Determine multiplier key based on holiday type
                    if ($holidayType === 'regular') {
                        $multKey = 'regular_holiday';
                    } elseif ($holidayType === 'special-non-working') {
                        $multKey = 'special_holiday';
                    } elseif ($holidayType === 'special-working') {
                        $multKey = 'special_holiday';
                    } else {
                        $multKey = 'holiday'; // fallback
                    }
                    $multiplier = $otMultipliers[$multKey] ?? 0;
                    $payHol += round($perMin * $ot->total_ot_minutes * $multiplier, 2);
                }
            }

            // Holiday + Rest Day overtime pay
            if (in_array($stype, ['hourly_rate', 'daily_rate']) && $otHolRst->has($id)) {
                $perMin = $stype === 'hourly_rate' ? $basic / 60 : ($basic / 8) / 60;
                foreach ($otHolRst[$id] as $ot) {
                    $holidayType = optional($ot->holiday)->type;
                    // Determine multiplier key based on holiday type + rest day
                    if ($holidayType === 'regular') {
                        $multKey = 'regular_holiday_rest_day';
                    } elseif ($holidayType === 'special-non-working') {
                        $multKey = 'special_holiday_rest_day';
                    } elseif ($holidayType === 'special-working') {
                        $multKey = 'special_holiday_rest_day';
                    } else {
                        $multKey = 'holiday_rest_day'; // fallback
                    }
                    $multiplier = $otMultipliers[$multKey] ?? 0;
                    $payRdHol += round($perMin * $ot->total_ot_minutes * $multiplier, 2);
                }
            }

            // Log result for user
            Log::info("Overtime pay computed for user $id: $payOrd");

            // Add to result
            $result[$id] = [
                'ordinary_pay' => $payOrd,
                'rest_day_pay' => $payRd,
                'total_ot_minutes' => $ord,
                'holiday_pay' => $payHol,
            ];
        }
        return $result;
    }
}
