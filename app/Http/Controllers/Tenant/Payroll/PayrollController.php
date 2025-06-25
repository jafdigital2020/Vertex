<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use App\Models\Holiday;
use App\Models\Overtime;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\UserEarning;
use App\Models\LeaveRequest;
use App\Models\SalaryRecord;
use Illuminate\Http\Request;
use App\Models\UserDeduction;
use App\Models\UserDeminimis;
use App\Models\HolidayException;
use Illuminate\Support\Facades\DB;
use App\Models\WithholdingTaxTable;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PhilhealthContribution;

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

        $salaryData = $this->getSalaryData($data['user_id']);

        $deductions = $this->calculateDeductions($data['user_id'], $totals, $salaryData);

        $holidayInfo = $this->calculateHolidayPay($attendances, $data, $salaryData);

        $nightDiffInfo = $this->calculateNightDifferential($data['user_id'], $data, $salaryData);

        $overtimePay = $this->calculateOvertimePay($data['user_id'], $data, $salaryData);

        $overtimeNightDiffPay = $this->calculateOvertimeNightDiffPay($data['user_id'], $data, $salaryData);

        $userEarnings = $this->calculateEarnings($data['user_id'], $data, $salaryData);

        $userDeductions = $this->calculateDeductions($data['user_id'], $data, $salaryData);
        Log::info('💸 Computed user deductions', $userDeductions);

        $basicPay = $this->calculateBasicPay($data['user_id'], $data, $salaryData);
        Log::info('💵 Computed basic pay', $basicPay);

        $grossPay = $this->calculateGrossPay($data['user_id'], $data, $salaryData);
        Log::info('💰 Computed gross pay', $grossPay);

        $sssContributions = $this->calculateSSSContribution($data['user_id'], $data, $salaryData);
        Log::info('🧾 Computed SSS contributions', $sssContributions);

        $philhealthContributions = $this->calculatePhilhealthContribution($data['user_id'], $data, $salaryData);
        Log::info('🩺 Computed PhilHealth contributions', $philhealthContributions);

        // Get the pagibig_option from the request (default to 'yes' if not set)
        $pagibigOption = $request->input('pagibig_option', 'yes');
        $pagibigContributions = $this->calculatePagibigContribution($data['user_id'], $data, $salaryData);

        // Adjust Pag-IBIG contributions based on user selection
        if ($pagibigOption === 'no') {
            foreach ($pagibigContributions as &$contribution) {
            $contribution['employee_total'] = 0;
            $contribution['total_contribution'] = 0;
            }
            unset($contribution);
            Log::info('🏠 Pag-IBIG contributions set to 0 (No option selected)', $pagibigContributions);
        } elseif ($pagibigOption === 'full') {
            foreach ($pagibigContributions as &$contribution) {
            $contribution['employee_total'] = 200;
            $contribution['total_contribution'] = 200;
            }
            unset($contribution);
            Log::info('🏠 Pag-IBIG contributions set to 200 (Full option selected)', $pagibigContributions);
        } else {
            Log::info('🏠 Computed Pag-IBIG contributions', $pagibigContributions);
        }

        $withholdingTax = $this->calculateWithholdingTax($data['user_id'], $data, $salaryData);
        Log::info('💰 Computed withholding tax', $withholdingTax);

        $leavePay = $this->calculateLeavePay($data['user_id'], $data, $salaryData);
        Log::info('🏖️ Computed leave pay', $leavePay);

        $deminimisBenefits = $this->calculateUserDeminimis($data['user_id'], $data, $salaryData);
        Log::info('🎁 Computed deminimis benefits', $deminimisBenefits);

        $totalDeductions = $this->calculateTotalDeductions($data['user_id'], $data, $salaryData);
        Log::info('📉 Computed total deductions', $totalDeductions);

        $totalEarnings = $this->calculateTotalEarnings($data['user_id'], $data, $salaryData);
        Log::info('📈 Computed total earnings', $totalEarnings);

        $netPay = $this->calculateNetPay($data['user_id'], $basicPay, $totalEarnings, $totalDeductions);
        Log::info('💵 Computed net pay', $netPay);

        UserEarning::whereIn('user_id', $data['user_id'])
            ->where('frequency', 'one_time')
            ->where('status', 'active')
            ->whereBetween('effective_start_date', [
                Carbon::parse($data['start_date'])->startOfDay(),
                Carbon::parse($data['end_date'])->endOfDay()
            ])
            ->update(['status' => 'completed']);

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
                'holiday_rest_day_pay' => $payRdHol,
            ];
        }
        return $result;
    }

    // Calculate Overtime and Night Differential Pay
    protected function calculateOvertimeNightDiffPay(array $userIds, array $data, $salaryData)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();

        $otMultipliers = DB::table('ot_tables')->pluck('night_differential_overtime', 'type');

        $otBase = Overtime::whereIn('user_id', $userIds)
            ->whereBetween('overtime_date', [$start, $end])
            ->where('status', 'approved')
            ->where('total_night_diff_minutes', '>', 0);

        $ordinaryMins = (clone $otBase)
            ->where('is_rest_day', false)
            ->where('is_holiday', false)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_night_diff_minutes) as mins'))
            ->pluck('mins', 'user_id');

        $restdayMins = (clone $otBase)
            ->where('is_rest_day', true)
            ->where('is_holiday', false)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_night_diff_minutes) as mins'))
            ->pluck('mins', 'user_id');

        $holidayMins = (clone $otBase)
            ->where('is_holiday', true)
            ->where('is_rest_day', false)
            ->with('holiday')
            ->get()
            ->groupBy('user_id');

        // Holiday + Rest Day
        $holidayRstMins = (clone $otBase)
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
            $ord  = $ordinaryMins->get($id, 0);
            $mOrd = $otMultipliers['ordinary'] ?? 0;
            $rst  = $restdayMins->get($id, 0);
            $mRst = $otMultipliers['rest_day'] ?? 0;

            $payOrd = 0;
            $payRst = 0;
            $payHol = 0;

            // Calculate ordinary overtime pay
            if (in_array($stype, ['hourly_rate', 'daily_rate']) && $ord > 0) {
                $perMin = $stype === 'hourly_rate' ? $basic / 60 : ($basic / 8) / 60;
                $payOrd = round($perMin * $ord * $mOrd, 2);
            }

            // Calculate rest day overtime pay
            if (in_array($stype, ['hourly_rate', 'daily_rate']) && $rst > 0) {
                $perMin = $stype === 'hourly_rate' ? $basic / 60 : ($basic / 8) / 60;
                $payRst = round($perMin * $rst * $mRst, 2);
            }

            // Calculate holiday overtime pay
            if (in_array($stype, ['hourly_rate', 'daily_rate'])) {
                $perMin = $stype === 'hourly_rate' ? $basic / 60 : ($basic / 8) / 60;

                foreach ($holidayMins->get($id, collect()) as $att) {
                    $holidayOrig = optional($att->holiday)->date;
                    $hType = optional($att->holiday)->type;

                    $attIn = Carbon::parse($att->date_ot_in);
                    $attOut = Carbon::parse($att->date_ot_out);

                    // Night diff window: 22:00:00 to 06:00:00 next day
                    $nightStart = $attIn->copy()->setTime(22, 0, 0);
                    if ($attIn->gt($nightStart)) {
                        $nightStart = $attIn->copy()->setTime(22, 0, 0);
                    } else {
                        $nightStart = $attIn->copy()->setTime(22, 0, 0);
                    }
                    $nightEnd = $nightStart->copy()->addDay()->setTime(6, 0, 0);

                    // Clamp night diff window to attendance window
                    $ndStart = $attIn->greaterThan($nightStart) ? $attIn : $nightStart;
                    $ndEnd = $attOut->lessThan($nightEnd) ? $attOut : $nightEnd;

                    $nightDiffMinutes = $ndStart->lt($ndEnd) ? $ndStart->diffInMinutes($ndEnd) : 0;
                    $nightDiffMinutes = max(0, min($nightDiffMinutes, $att->total_night_diff_minutes));

                    // Holiday window
                    $holidayStart = Carbon::parse($holidayOrig)->setYear($attIn->year)->startOfDay();
                    $holidayEnd = $holidayStart->copy()->addDay()->startOfDay();

                    // Overlap night diff with holiday
                    $holNDStart = $ndStart->greaterThan($holidayStart) ? $ndStart : $holidayStart;
                    $holNDEnd = $ndEnd->lessThan($holidayEnd) ? $ndEnd : $holidayEnd;
                    $holNDMin = $holNDStart->lt($holNDEnd) ? $holNDStart->diffInMinutes($holNDEnd) : 0;
                    $holNDMin = max(0, min($holNDMin, $nightDiffMinutes));

                    // Next holiday overlap
                    $nextDay = $holidayEnd->copy();
                    $nextHoliday = Holiday::where('date', $nextDay->toDateString())->first();

                    $nextHolNDMin = 0;
                    $nextHMult = 0;
                    if ($nextHoliday) {
                        $nextHolidayStart = $nextDay->copy();
                        $nextHolidayEnd = $nextDay->copy()->addHours(6);

                        $nextNDStart = $ndStart->greaterThan($nextHolidayStart) ? $ndStart : $nextHolidayStart;
                        $nextNDEnd = $ndEnd->lessThan($nextHolidayEnd) ? $ndEnd : $nextHolidayEnd;
                        $nextHolidayMin = $nextNDStart->lt($nextNDEnd) ? $nextNDStart->diffInMinutes($nextNDEnd) : 0;
                        $nextHolidayMin = max(0, $nextHolidayMin);
                        $nextHolNDMin = min($nextHolidayMin, $nightDiffMinutes - $holNDMin);
                        $nextHolNDMin = max(0, (int) $nextHolNDMin);
                        $nextMultKey = $nextHoliday->type === 'regular' ? 'regular_holiday' : 'special_holiday';
                        $nextHMult = $otMultipliers[$nextMultKey] ?? 0;
                        Log::info("Night diff multiplier used for user $id (next holiday: $nextMultKey): $nextHMult");
                    }

                    $usedNDMin = $holNDMin + $nextHolNDMin;
                    $ordNDMin = $nightDiffMinutes - $usedNDMin;
                    $ordNDMin = max(0, (int) $ordNDMin);

                    $multKey = $hType === 'regular' ? 'regular_holiday' : 'special_holiday';
                    $hMult = $otMultipliers[$multKey] ?? 0;
                    Log::info("Night diff multiplier used for user $id (holiday: $multKey): $hMult");
                    Log::info("User $id holiday ND mins: holNDMin=$holNDMin, nextHolNDMin=$nextHolNDMin, ordNDMin=$ordNDMin, totalNDMin=$nightDiffMinutes");

                    $payHol += round($perMin * $holNDMin * $hMult, 2);

                    if ($nextHolNDMin > 0 && $nextHMult > 0) {
                        $payHol += round($perMin * $nextHolNDMin * $nextHMult, 2);
                    }

                    if ($ordNDMin > 0) {
                        $payOrd += round($perMin * $ordNDMin * $mOrd, 2);
                    }
                }

                // Holiday + Rest Day
                foreach ($holidayRstMins->get($id, collect()) as $att) {
                    $holidayOrig = optional($att->holiday)->date;
                    $hType = optional($att->holiday)->type;

                    $attIn = Carbon::parse($att->date_ot_in);
                    $attOut = Carbon::parse($att->date_ot_out);

                    // Night diff window: 22:00:00 to 06:00:00 next day
                    $nightStart = $attIn->copy()->setTime(22, 0, 0);
                    $nightEnd = $nightStart->copy()->addDay()->setTime(6, 0, 0);

                    $ndStart = $attIn->greaterThan($nightStart) ? $attIn : $nightStart;
                    $ndEnd = $attOut->lessThan($nightEnd) ? $attOut : $nightEnd;

                    $nightDiffMinutes = $ndStart->lt($ndEnd) ? $ndStart->diffInMinutes($ndEnd) : 0;
                    $nightDiffMinutes = max(0, min($nightDiffMinutes, $att->total_night_diff_minutes));

                    // Holiday window
                    $holidayStart = Carbon::parse($holidayOrig)->setYear($attIn->year)->startOfDay();
                    $holidayEnd = $holidayStart->copy()->addDay()->startOfDay();

                    // Overlap night diff with holiday
                    $holNDStart = $ndStart->greaterThan($holidayStart) ? $ndStart : $holidayStart;
                    $holNDEnd = $ndEnd->lessThan($holidayEnd) ? $ndEnd : $holidayEnd;
                    $holNDMin = $holNDStart->lt($holNDEnd) ? $holNDStart->diffInMinutes($holNDEnd) : 0;
                    $holNDMin = max(0, min($holNDMin, $nightDiffMinutes));

                    // Next holiday overlap
                    $nextDay = $holidayEnd->copy();
                    $nextHoliday = Holiday::where('date', $nextDay->toDateString())->first();

                    $nextHolNDMin = 0;
                    $nextHMult = 0;
                    if ($nextHoliday) {
                        $nextHolidayStart = $nextDay->copy();
                        $nextHolidayEnd = $nextDay->copy()->addHours(6);

                        $nextNDStart = $ndStart->greaterThan($nextHolidayStart) ? $ndStart : $nextHolidayStart;
                        $nextNDEnd = $ndEnd->lessThan($nextHolidayEnd) ? $ndEnd : $nextHolidayEnd;
                        $nextHolidayMin = $nextNDStart->lt($nextNDEnd) ? $nextNDStart->diffInMinutes($nextNDEnd) : 0;
                        $nextHolidayMin = max(0, $nextHolidayMin);
                        $nextHolNDMin = min($nextHolidayMin, $nightDiffMinutes - $holNDMin);
                        $nextHolNDMin = max(0, (int) $nextHolNDMin);
                        $nextMultKey = $nextHoliday->type === 'regular'
                            ? 'regular_holiday_rest_day'
                            : 'special_holiday_rest_day';
                        $nextHMult = $otMultipliers[$nextMultKey] ?? 0;
                        Log::info("Night diff multiplier used for user $id (next holiday+rest: $nextMultKey): $nextHMult");
                    }

                    $usedNDMin = $holNDMin + $nextHolNDMin;
                    $ordNDMin = $nightDiffMinutes - $usedNDMin;
                    $ordNDMin = max(0, (int) $ordNDMin);

                    $multKey = $hType === 'regular'
                        ? 'regular_holiday_rest_day'
                        : 'special_holiday_rest_day';
                    $hMult = $otMultipliers[$multKey] ?? 0;
                    Log::info("Night diff multiplier used for user $id (holiday+rest: $multKey): $hMult");
                    Log::info("User $id holiday+rest ND mins: holNDMin=$holNDMin, nextHolNDMin=$nextHolNDMin, ordNDMin=$ordNDMin, totalNDMin=$nightDiffMinutes");

                    $payHol += round($perMin * $holNDMin * $hMult, 2);

                    if ($nextHolNDMin > 0 && $nextHMult > 0) {
                        $payHol += round($perMin * $nextHolNDMin * $nextHMult, 2);
                    }

                    if ($ordNDMin > 0) {
                        $payOrd += round($perMin * $ordNDMin * $mOrd, 2);
                    }
                }
            }

            $payHol = round($payHol, 2);
            // Log result for user
            Log::info("Overtime night diff pay computed for user $id: $payOrd");

            // Add to result
            $result[$id] = [
                'ordinary_pay' => $payOrd,
                'rest_day_pay' => $payRst,
                'holiday_pay' => $payHol,
                'total_night_diff_minutes' => $ord,
            ];
        }

        return $result;
    }

    // Calculate Earnings (Dynamic Earnings)
    protected function calculateEarnings(array $userIds, array $data, $salaryData)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();

        // Get all user earnings with their earning types
        $earnings = UserEarning::whereIn('user_id', $userIds)
            ->where('status', 'active')
            ->where(function ($q) use ($start, $end) {
                $q->where('effective_start_date', '<=', $end)
                    ->where(function ($q2) use ($start) {
                        $q2->whereNull('effective_end_date')
                            ->orWhere('effective_end_date', '>=', $start);
                    });
            })
            ->with('earningType')
            ->get();

        $result = [];
        foreach ($userIds as $id) {
            $userEarnings = $earnings->where('user_id', $id);
            $total = 0;
            $details = [];

            foreach ($userEarnings as $earning) {
                $eType = $earning->earningType;
                if (!$eType) continue; // skip if type is missing

                // Use user-defined amount or fallback to earning type's default
                $amount = $earning->amount > 0 ? $earning->amount : ($eType->default_amount ?? 0);

                if ($eType->calculation_method == 'percentage') {
                    // percentage: default_amount is base, amount is %
                    $baseValue = $eType->default_amount ?? 0;
                    $percent = $earning->amount ?? 0;
                    $finalAmount = $baseValue * ($percent / 100);
                } else { // 'fixed'
                    // fixed: use user override if set, else default_amount
                    $finalAmount = isset($earning->amount) && $earning->amount !== null && $earning->amount > 0
                        ? $earning->amount
                        : ($eType->default_amount ?? 0);
                }

                // Frequency logic
                $include = false;
                if ($earning->frequency == 'every_payroll') {
                    $include = true;
                } elseif ($earning->frequency == 'one_time') {
                    if ($earning->effective_start_date->between($start, $end)) $include = true;
                } elseif ($earning->frequency == 'every_other') {
                    $payrollNumber = $start->diffInWeeks(Carbon::create(2020, 1, 1));
                    if ($payrollNumber % 2 == 0) $include = true;
                }

                if ($include) {
                    $total += $finalAmount;
                    $details[] = [
                        'earning_type_id'     => $eType->id,
                        'earning_type_name'   => $eType->name,
                        'calculation_method'  => $eType->calculation_method,
                        'default_amount'      => $eType->default_amount,
                        'is_taxable'          => $eType->is_taxable,
                        'apply_to_all_employees' => $eType->apply_to_all_employees,
                        'description'         => $eType->description,
                        'user_amount_override' => $earning->amount,
                        'applied_amount'      => round($finalAmount, 2),
                        'frequency'           => $earning->frequency,
                        'status'              => $earning->status,
                    ];
                }
            }

            $result[$id] = [
                'earnings' => round($total, 2),
                'earning_details' => $details,
            ];
        }

        return $result;
    }

    // Calculate Deductions (Dynamic Deductions)
    protected function calculateDeduction(array $userIds, array $data, $salaryData)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();

        // Get all user earnings with their earning types
        $deductions = UserDeduction::whereIn('user_id', $userIds)
            ->where('status', 'active')
            ->where(function ($q) use ($start, $end) {
                $q->where('effective_start_date', '<=', $end)
                    ->where(function ($q2) use ($start) {
                        $q2->whereNull('effective_end_date')
                            ->orWhere('effective_end_date', '>=', $start);
                    });
            })
            ->with('deductionType')
            ->get();

        $result = [];
        foreach ($userIds as $id) {
            $userDeductions = $deductions->where('user_id', $id);
            $total = 0;
            $details = [];

            foreach ($userDeductions as $deduction) {
                $dType = $deduction->deductionType;
                if (!$dType) continue; // skip if type is missing

                // Use user-defined amount or fallback to deduction type's default
                $amount = $deduction->amount > 0 ? $deduction->amount : ($dType->default_amount ?? 0);

                if ($dType->calculation_method == 'percentage') {
                    // percentage: default_amount is base, amount is %
                    $baseValue = $dType->default_amount ?? 0;
                    $percent = $deduction->amount ?? 0;
                    $finalAmount = $baseValue * ($percent / 100);
                } else { // 'fixed'
                    // fixed: use user override if set, else default_amount
                    $finalAmount = isset($deduction->amount) && $deduction->amount !== null && $deduction->amount > 0
                        ? $deduction->amount
                        : ($dType->default_amount ?? 0);
                }

                // Frequency logic
                $include = false;
                if ($deduction->frequency == 'every_payroll') {
                    $include = true;
                } elseif ($deduction->frequency == 'one_time') {
                    if ($deduction->effective_start_date->between($start, $end)) $include = true;
                } elseif ($deduction->frequency == 'every_other') {
                    $payrollNumber = $start->diffInWeeks(Carbon::create(2020, 1, 1));
                    if ($payrollNumber % 2 == 0) $include = true;
                }

                if ($include) {
                    $total += $finalAmount;
                    $details[] = [
                        'deduction_type_id'     => $dType->id,
                        'deduction_type_name'   => $dType->name,
                        'calculation_method'  => $dType->calculation_method,
                        'default_amount'      => $dType->default_amount,
                        'is_taxable'          => $dType->is_taxable,
                        'apply_to_all_employees' => $dType->apply_to_all_employees,
                        'description'         => $dType->description,
                        'user_amount_override' => $deduction->amount,
                        'applied_amount'      => round($finalAmount, 2),
                        'frequency'           => $deduction->frequency,
                        'status'              => $deduction->status,
                    ];
                }
            }

            $result[$id] = [
                'deductions' => round($total, 2),
                'deduction_details' => $details,
            ];
        }

        return $result;
    }

    // Basic Pay Calculation
    protected function calculateBasicPay(array $userIds, array $data, $salaryData)
    {
        // Use sumMinutes to get work minutes and work days
        $tenantId = Auth::user()->tenant_id ?? null;
        $totals = $this->sumMinutes($tenantId, $data);

        // Preload employment details and branch for all users
        $users = User::with(['employmentDetail.branch'])->whereIn('id', $userIds)->get()->keyBy('id');

        $result = [];
        foreach ($userIds as $id) {
            $sal = $salaryData->get($id, ['basic_salary' => 0, 'salary_type' => 'hourly_rate', 'worked_days_per_year' => 0]);
            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];
            $wpy   = $sal['worked_days_per_year'];

            $workMinutes = $totals['work'][$id] ?? 0;
            $workDays = $totals['work_days'][$id] ?? 0;

            // Get salary computation type from branch
            $salaryComputationType = null;
            if (isset($users[$id]) && $users[$id]->employmentDetail && $users[$id]->employmentDetail->branch) {
                $salaryComputationType = $users[$id]->employmentDetail->branch->salary_computation_type;
            }

            Log::info("Salary type for user $id: $stype");
            Log::info("Branch salary computation type for user $id: $salaryComputationType");
            Log::info("Total work minutes for user $id: $workMinutes");

            if ($stype === 'hourly_rate') {
                // Hourly rate: based on total work minutes
                $basicPay = round(($basic / 60) * $workMinutes, 2);
            } elseif ($stype === 'daily_rate') {
                // Daily rate: based on total work days
                $basicPay = round($basic * $workDays, 2);
            } elseif ($stype === 'monthly_fixed') {
                // Monthly salary: computation depends on branch setting
                if ($salaryComputationType === 'actual_days') {
                    // Compute based on actual worked days
                    $dailyRate = $wpy > 0 ? ($basic * 12) / $wpy : 0;
                    $basicPay = round($dailyRate * $workDays, 2);
                } elseif ($salaryComputationType === 'semi-monthly') {
                    // Semi-monthly: divide monthly salary by 2
                    $basicPay = round($basic / 2, 2);
                } else {
                    // Default: just use the basic salary (monthly)
                    $basicPay = round($basic, 2);
                }
            } else {
                // Fallback: treat as daily
                $basicPay = round($basic * $workDays, 2);
            }

            Log::info("Basic pay calculated for user $id: $basicPay");

            $result[$id] = [
                'basic_pay' => $basicPay,
            ];
        }

        return $result;
    }

    // Gross Pay computation
    public function calculateGrossPay(array $userIds, array $data, $salaryData)
    {
        // Calculate basic pay
        $basicPay = $this->calculateBasicPay($userIds, $data, $salaryData);

        // Calculate earnings
        $earnings = $this->calculateEarnings($userIds, $data, $salaryData);

        // Calculate Holiday Pay
        $holidayPay = $this->calculateHolidayPay(
            $this->getAttendances(Auth::user()->tenant_id, $data),
            $data,
            $salaryData
        );

        // Calculate Overtime Pay
        $overtimePay = $this->calculateOvertimePay($userIds, $data, $salaryData);

        // Calculate Night Differential Pay
        $nightDiffPay = $this->calculateNightDifferential($userIds, $data, $salaryData);

        // Calculate Overtime Night Differential Pay
        $overtimeNightDiffPay = $this->calculateOvertimeNightDiffPay($userIds, $data, $salaryData);

        // Calculate Leave Pay
        $leavePay = $this->calculateLeavePay($userIds, $data, $salaryData);

        $result = [];

        foreach ($userIds as $id) {
            $basic = $basicPay[$id]['basic_pay'] ?? 0;
            $earningTotal = $earnings[$id]['earnings'] ?? 0;
            $holidayTotal = $holidayPay[$id]['holiday_pay_amount'] ?? 0;
            $leaveTotal = $leavePay[$id]['total_leave_pay'] ?? 0;

            $otOrd = $overtimePay[$id]['ordinary_pay'] ?? 0;
            $otRd = $overtimePay[$id]['rest_day_pay'] ?? 0;
            $otHol = $overtimePay[$id]['holiday_pay'] ?? 0;
            $otHolRd = $overtimePay[$id]['holiday_rest_day_pay'] ?? 0;

            $ndOrd = $nightDiffPay[$id]['ordinary_pay'] ?? 0;
            $ndRd = $nightDiffPay[$id]['rest_day_pay'] ?? 0;
            $ndHol = $nightDiffPay[$id]['holiday_pay'] ?? 0;

            $otNdOrd = $overtimeNightDiffPay[$id]['ordinary_pay'] ?? 0;
            $otNdRd = $overtimeNightDiffPay[$id]['rest_day_pay'] ?? 0;
            $otNdHol = $overtimeNightDiffPay[$id]['holiday_pay'] ?? 0;

            // Sum up all pay components, including leave pay
            $grossPay = $basic
                + $earningTotal
                + $holidayTotal
                + $leaveTotal
                + $otOrd
                + $otRd
                + $otHol
                + $otHolRd
                + $ndOrd
                + $ndRd
                + $ndHol
                + $otNdOrd
                + $otNdRd
                + $otNdHol;

            // Logging for debugging
            Log::info("Gross pay calculation for user $id", [
                'basic_pay' => $basic,
                'earnings' => $earningTotal,
                'holiday_pay' => $holidayTotal,
                'leave_pay' => $leaveTotal,
                'overtime' => [
                    'ordinary' => $otOrd,
                    'rest_day' => $otRd,
                    'holiday' => $otHol,
                    'holiday_rest_day' => $otHolRd,
                ],
                'night_diff' => [
                    'ordinary' => $ndOrd,
                    'rest_day' => $ndRd,
                    'holiday' => $ndHol,
                ],
                'overtime_night_diff' => [
                    'ordinary' => $otNdOrd,
                    'rest_day' => $otNdRd,
                    'holiday' => $otNdHol,
                ],
                'gross_pay' => $grossPay,
            ]);

            $result[$id] = [
                'basic_pay' => round($basic, 2),
                'earnings' => round($earningTotal, 2),
                'holiday_pay' => round($holidayTotal, 2),
                'leave_pay' => round($leaveTotal, 2),
                'overtime_pay' => [
                    'ordinary' => round($otOrd, 2),
                    'rest_day' => round($otRd, 2),
                    'holiday' => round($otHol, 2),
                    'holiday_rest_day' => round($otHolRd, 2),
                ],
                'night_diff_pay' => [
                    'ordinary' => round($ndOrd, 2),
                    'rest_day' => round($ndRd, 2),
                    'holiday' => round($ndHol, 2),
                ],
                'overtime_night_diff_pay' => [
                    'ordinary' => round($otNdOrd, 2),
                    'rest_day' => round($otNdRd, 2),
                    'holiday' => round($otNdHol, 2),
                ],
                'gross_pay' => round($grossPay, 2),
            ];
        }

        // Log the gross pay for each user individually
        foreach ($result as $userId => $payData) {
            Log::info("Individual gross pay for user $userId", ['gross_pay' => $payData['gross_pay']]);
        }

        return $result;
    }

    // Leave Computation
    protected function calculateLeavePay(array $userIds, array $data, $salaryData)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();

        // Get all user leaves with leaveType relation
        $leaves = LeaveRequest::whereIn('user_id', $userIds)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end]);
            })
            ->with('leaveType')
            ->get();

        $result = [];

        foreach ($userIds as $userId) {
            $userLeaves = $leaves->where('user_id', $userId);
            $sal = $salaryData->get($userId, [
                'basic_salary' => 0,
                'salary_type' => 'hourly_rate',
                'worked_days_per_year' => 0,
            ]);
            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];
            $wpy   = $sal['worked_days_per_year'];

            $totalLeavePay = 0;
            $leaveDetails = [];

            foreach ($userLeaves as $leave) {
                // Only process paid leaves
                if (!$leave->leaveType || !$leave->leaveType->is_paid) {
                    continue;
                }

                $leaveType = $leave->leaveType->name ?? 'Unknown';

                // Calculate leave days (inclusive)
                $leaveStart = Carbon::parse($leave->start_date)->startOfDay();
                $leaveEnd = Carbon::parse($leave->end_date)->endOfDay();
                $leaveDays = $leaveStart->diffInDaysFiltered(function (Carbon $date) {
                    // Optionally skip weekends/holidays here if needed
                    return true;
                }, $leaveEnd) + 1;

                // Compute pay for this leave
                if ($stype === 'hourly_rate') {
                    // Assume 8 hours per day
                    $leavePay = round(($basic / 60) * 8 * 60 * $leaveDays, 2);
                } elseif ($stype === 'daily_rate') {
                    $leavePay = round($basic * $leaveDays, 2);
                } elseif ($stype === 'monthly_fixed') {
                    $dailyRate = $wpy > 0 ? ($basic * 12) / $wpy : 0;
                    $leavePay = round($dailyRate * $leaveDays, 2);
                } else {
                    $leavePay = 0;
                }

                $totalLeavePay += $leavePay;

                $leaveDetails[] = [
                    'id' => $leave->id,
                    'type' => $leaveType,
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    'leave_days' => $leaveDays,
                    'leave_pay' => $leavePay,
                ];

                // Log leave pay per leave
                Log::info("Leave pay computed for user $userId, leave #{$leave->id} ({$leaveType}): $leavePay");
            }

            // Log total leave pay for user
            Log::info("Total leave pay for user $userId: $totalLeavePay");

            $result[$userId] = [
                'total_leave_pay' => round($totalLeavePay, 2),
                'leaves' => $leaveDetails,
            ];
        }

        return $result;
    }

    // SSS Contribution Calculation
    protected function calculateSSSContribution(array $userIds, array $data, $salaryData)
    {
        // Preload user branch SSS contribution type and fixed amount
        $users = User::with(['employmentDetail.branch', 'salaryDetail'])->whereIn('id', $userIds)->get()->keyBy('id');
        $sssTable = DB::table('sss_contribution_tables')->get();

        // Get the gross pay
        $grossPay = $this->calculateGrossPay($userIds, $data, $salaryData);

        $result = [];
        foreach ($userIds as $userId) {
            $user = $users[$userId] ?? null;
            $branch = $user && $user->employmentDetail ? $user->employmentDetail->branch : null;
            $sssType = $branch && isset($branch->sss_contribution_type) ? $branch->sss_contribution_type : null;

            // Default to 0 if not found
            $result[$userId] = [
                'employer_total' => 0,
                'employee_total' => 0,
                'total_contribution' => 0,
            ];

            if ($sssType === 'system') {
                $salary = $grossPay[$userId]['gross_pay'] ?? 0;

                // Find the applicable SSS contribution based on salary
                $sssContribution = $sssTable->first(function ($item) use ($salary) {
                    return $salary >= $item->range_from && $salary <= $item->range_to;
                });

                if ($sssContribution) {
                    $result[$userId] = [
                        'employer_total' => $sssContribution->employer_total,
                        'employee_total' => $sssContribution->employee_total,
                        'total_contribution' => $sssContribution->total_contribution,
                    ];
                    Log::info("SSS Contribution for user $userId", [
                        'employer_share' => $sssContribution->employer_total,
                        'employee_share' => $sssContribution->employee_total,
                        'total' => $sssContribution->total_contribution,
                        'salary' => $salary,
                    ]);
                }
            } elseif ($sssType === 'fixed') {
                $fixedAmount = $branch->fixed_sss_amount ?? 0;
                $salaryComputation = $branch->salary_computation_type ?? null;

                $amount = $fixedAmount;
                if ($salaryComputation === 'semi-monthly') {
                    $amount = $fixedAmount / 2;
                }
                // For monthly, don't divide

                $result[$userId] = [
                    'employer_total' => 0,
                    'employee_total' => $amount,
                    'total_contribution' => $amount,
                ];
                Log::info("Fixed SSS Contribution for user $userId", [
                    'employee_share' => $amount,
                    'salary_computation_type' => $salaryComputation,
                    'fixed_amount' => $fixedAmount,
                ]);
            } elseif ($sssType === 'manual') {
                $salaryDetail = $user->salaryDetail ?? null;
                $salaryComputation = $branch->salary_computation_type ?? null;

                if ($salaryDetail && isset($salaryDetail->sss_contribution)) {
                    if ($salaryDetail->sss_contribution === 'system') {
                        // Use system computation as above
                        $salary = $grossPay[$userId]['gross_pay'] ?? 0;
                        $sssContribution = $sssTable->first(function ($item) use ($salary) {
                            return $salary >= $item->range_from && $salary <= $item->range_to;
                        });

                        if ($sssContribution) {
                            $result[$userId] = [
                                'employer_total' => $sssContribution->employer_total,
                                'employee_total' => $sssContribution->employee_total,
                                'total_contribution' => $sssContribution->total_contribution,
                            ];
                            Log::info("Manual-SSS (system) Contribution for user $userId", [
                                'employer_share' => $sssContribution->employer_total,
                                'employee_share' => $sssContribution->employee_total,
                                'total' => $sssContribution->total_contribution,
                                'salary' => $salary,
                            ]);
                        }
                    } elseif ($salaryDetail->sss_contribution === 'manual') {
                        $override = $salaryDetail->sss_contribution_override ?? 0;
                        $amount = $override;
                        if ($salaryComputation === 'semi-monthly') {
                            $amount = $override / 2;
                        }
                        // For monthly, don't divide

                        $result[$userId] = [
                            'employer_total' => 0,
                            'employee_total' => $amount,
                            'total_contribution' => $amount,
                        ];
                        Log::info("Manual-SSS (manual) Contribution for user $userId", [
                            'employee_share' => $amount,
                            'salary_computation_type' => $salaryComputation,
                            'override_amount' => $override,
                        ]);
                    }
                }
            }
        }

        return $result;
    }

    // Philhealth Contribution Calculation
    protected function calculatePhilhealthContribution(array $userIds, array $data, $salaryData)
    {
        // Preload user branch PhilHealth contribution type and fixed amount
        $users = User::with(['employmentDetail.branch', 'salaryDetail'])->whereIn('id', $userIds)->get()->keyBy('id');

        // Use the Eloquent model instead of DB::table
        $philhealthTable = PhilhealthContribution::all();

        // Get the basic pay
        $basicPay = $this->calculateBasicPay($userIds, $data, $salaryData);

        $result = [];
        foreach ($userIds as $userId) {
            $user = $users[$userId] ?? null;
            $branch = $user && $user->employmentDetail ? $user->employmentDetail->branch : null;
            $philhealthType = $branch && isset($branch->philhealth_contribution_type) ? $branch->philhealth_contribution_type : null;

            // Default to 0 if not found
            $result[$userId] = [
                'employer_total' => 0,
                'employee_total' => 0,
                'total_contribution' => 0,
            ];

            // Use basic pay instead of gross pay
            $salary = $basicPay[$userId]['basic_pay'] ?? 0;

            if ($philhealthType === 'system') {
                // Find the applicable PhilHealth contribution based on salary
                $philhealthContribution = $philhealthTable->first(function ($item) use ($salary) {
                    return $salary >= $item->min_salary && $salary <= $item->max_salary;
                });

                if ($philhealthContribution) {
                    $result[$userId] = [
                        'employer_total' => round($philhealthContribution->employer_share, 2),
                        'employee_total' => round($philhealthContribution->employee_share, 2),
                        'total_contribution' => round($philhealthContribution->monthly_premium, 2),
                    ];
                    Log::info("PhilHealth Contribution for user $userId", [
                        'employer_share' => round($philhealthContribution->employer_share, 2),
                        'employee_share' => round($philhealthContribution->employee_share, 2),
                        'total' => round($philhealthContribution->monthly_premium, 2),
                        'salary' => round($salary, 2),
                    ]);
                }
            } elseif ($philhealthType === 'fixed') {
                // Fixed amount logic
                if (
                    $branch &&
                    isset($branch->fixed_philhealth_amount) &&
                    $branch->fixed_philhealth_amount > 0
                ) {
                    $fixedAmount = $branch->fixed_philhealth_amount;
                    $salaryComputation = $branch->salary_computation_type ?? null;

                    $amount = $fixedAmount;
                    if ($salaryComputation === 'semi-monthly') {
                        $amount = $fixedAmount / 2;
                    }
                    // For monthly, don't divide

                    $result[$userId] = [
                        'employer_total' => 0,
                        'employee_total' => round($amount, 2),
                        'total_contribution' => round($amount, 2),
                    ];
                    Log::info("Fixed PhilHealth Contribution for user $userId", [
                        'employee_share' => round($amount, 2),
                        'salary_computation_type' => $salaryComputation,
                        'fixed_amount' => round($fixedAmount, 2),
                    ]);
                }
            } elseif ($philhealthType === 'manual') {
                $salaryDetail = $user->salaryDetail ?? null;
                $salaryComputation = $branch->salary_computation_type ?? null;
                if ($salaryDetail && isset($salaryDetail->philhealth_contribution)) {
                    if ($salaryDetail->philhealth_contribution === 'system') {
                        // Use system computation as above
                        $philhealthContribution = $philhealthTable->first(function ($item) use ($salary) {
                            return $salary >= $item->min_salary && $salary <= $item->max_salary;
                        });
                        if ($philhealthContribution) {
                            $result[$userId] = [
                                'employer_total' => round($philhealthContribution->employer_share, 2),
                                'employee_total' => round($philhealthContribution->employee_share, 2),
                                'total_contribution' => round($philhealthContribution->monthly_premium, 2),
                            ];
                            Log::info(
                                "Manual-PhilHealth (system) Contribution for user $userId",
                                [
                                    'employer_share' => round($philhealthContribution->employer_share, 2),
                                    'employee_share' => round($philhealthContribution->employee_share, 2),
                                    'total' => round($philhealthContribution->monthly_premium, 2),
                                ]
                            );
                        }
                    }
                }
            }
        }

        return $result;
    }

    // Pagibig Contribution Calculation
    protected function calculatePagibigContribution(array $userIds, array $data, $salaryData)
    {
        // Preload user branch Pag-IBIG contribution type and fixed amount
        $users = User::with(['employmentDetail.branch', 'salaryDetail'])->whereIn('id', $userIds)->get()->keyBy('id');

        $result = [];
        foreach ($userIds as $userId) {
            $user = $users[$userId] ?? null;
            $branch = $user && $user->employmentDetail ? $user->employmentDetail->branch : null;
            $pagibigType = $branch && isset($branch->pagibig_contribution_type) ? $branch->pagibig_contribution_type : null;

            // Default to 0 if not found
            $result[$userId] = [
                'employee_total' => 0,
                'total_contribution' => 0,
            ];

            $amount = 200; // Default Pag-IBIG monthly contribution

            if ($pagibigType === 'system') {
                $salaryComputation = $branch->salary_computation_type ?? null;
                if ($salaryComputation === 'semi-monthly') {
                    $amount = 200 / 2;
                }
                // For monthly, don't divide
                $result[$userId] = [
                    'employee_total' => round($amount, 2),
                    'total_contribution' => round($amount, 2),
                ];
                Log::info("Pag-IBIG Contribution for user $userId (system)", [
                    'employee_total' => round($amount, 2),
                    'salary_computation_type' => $salaryComputation,
                ]);
            } elseif ($pagibigType === 'fixed') {
                $fixedAmount = $branch->fixed_pagibig_amount ?? 0;
                $salaryComputation = $branch->salary_computation_type ?? null;
                $amount = $fixedAmount;
                if ($salaryComputation === 'semi-monthly') {
                    $amount = $fixedAmount / 2;
                }
                $result[$userId] = [
                    'employee_total' => round($amount, 2),
                    'total_contribution' => round($amount, 2),
                ];
                Log::info("Pag-IBIG Contribution for user $userId (fixed)", [
                    'employee_total' => round($amount, 2),
                    'salary_computation_type' => $salaryComputation,
                    'fixed_amount' => round($fixedAmount, 2),
                ]);
            } elseif ($pagibigType === 'manual') {
                $salaryDetail = $user->salaryDetail ?? null;
                $salaryComputation = $branch->salary_computation_type ?? null;
                if ($salaryDetail && isset($salaryDetail->pagibig_contribution)) {
                    if ($salaryDetail->pagibig_contribution === 'system') {
                        $amount = 200;
                        if ($salaryComputation === 'semi-monthly') {
                            $amount = 200 / 2;
                        }
                        $result[$userId] = [
                            'employee_total' => round($amount, 2),
                            'total_contribution' => round($amount, 2),
                        ];
                        Log::info("Pag-IBIG Contribution for user $userId (manual-system)", [
                            'employee_total' => round($amount, 2),
                            'salary_computation_type' => $salaryComputation,
                        ]);
                    } elseif ($salaryDetail->pagibig_contribution === 'manual') {
                        $override = $salaryDetail->pagibig_contribution_override ?? 0;
                        $amount = $override;
                        if ($salaryComputation === 'semi-monthly') {
                            $amount = $override / 2;
                        }
                        $result[$userId] = [
                            'employee_total' => round($amount, 2),
                            'total_contribution' => round($amount, 2),
                        ];
                        Log::info("Pag-IBIG Contribution for user $userId (manual-manual)", [
                            'employee_total' => round($amount, 2),
                            'salary_computation_type' => $salaryComputation,
                            'override_amount' => round($override, 2),
                        ]);
                    }
                }
            }
        }

        return $result;
    }

    // Withholding Tax Calculation
    protected function calculateWithholdingTax(array $userIds, array $data, $salaryData)
    {
        // Preload user branch tax type and fixed amount
        $users = User::with(['employmentDetail.branch', 'salaryDetail'])->whereIn('id', $userIds)->get()->keyBy('id');

        // Get pay components
        $basicPay = $this->calculateBasicPay($userIds, $data, $salaryData);
        $overtimePay = $this->calculateOvertimePay($userIds, $data, $salaryData);
        $nightDiffPay = $this->calculateNightDifferential($userIds, $data, $salaryData);
        $overtimeNightDiffPay = $this->calculateOvertimeNightDiffPay($userIds, $data, $salaryData);
        $holidayPay = $this->calculateHolidayPay(
            $this->getAttendances(Auth::user()->tenant_id, $data),
            $data,
            $salaryData
        );
        $leavePay = $this->calculateLeavePay($userIds, $data, $salaryData);
        $deductions = $this->calculateDeductions($userIds, $data, $salaryData);

        // Mandates
        $sss = $this->calculateSSSContribution($userIds, $data, $salaryData);
        $philhealth = $this->calculatePhilhealthContribution($userIds, $data, $salaryData);
        $pagibig = $this->calculatePagibigContribution($userIds, $data, $salaryData);

        $result = [];
        foreach ($userIds as $userId) {
            $user = $users[$userId] ?? null;
            $branch = $user && $user->employmentDetail ? $user->employmentDetail->branch : null;
            $taxType = $branch && isset($branch->withholding_tax_type) ? $branch->withholding_tax_type : null;

            $result[$userId] = [
                'taxable_income' => 0,
                'withholding_tax' => 0,
            ];

            if ($taxType === 'system') {
                // Determine frequency based on salary_computation_type, not system_computation_type
                $frequency = 'monthly';
                if ($branch && isset($branch->salary_computation_type)) {
                    $type = strtolower($branch->salary_computation_type);
                    if ($type === 'semi-monthly') {
                        $frequency = 'semi-monthly';
                    } elseif ($type === 'weekly') {
                        $frequency = 'weekly';
                    } elseif ($type === 'monthly') {
                        $frequency = 'monthly';
                    }
                }

                // Get correct tax table
                $taxTable = WithholdingTaxTable::where('frequency', $frequency)->get();

                // Compute basic salary
                $basic = $basicPay[$userId]['basic_pay'] ?? 0;
                $ot = ($overtimePay[$userId]['ordinary_pay'] ?? 0)
                    + ($overtimePay[$userId]['rest_day_pay'] ?? 0)
                    + ($overtimePay[$userId]['holiday_pay'] ?? 0)
                    + ($overtimePay[$userId]['holiday_rest_day_pay'] ?? 0);
                $nd = ($nightDiffPay[$userId]['ordinary_pay'] ?? 0)
                    + ($nightDiffPay[$userId]['rest_day_pay'] ?? 0)
                    + ($nightDiffPay[$userId]['holiday_pay'] ?? 0);
                $otnd = ($overtimeNightDiffPay[$userId]['ordinary_pay'] ?? 0)
                    + ($overtimeNightDiffPay[$userId]['rest_day_pay'] ?? 0)
                    + ($overtimeNightDiffPay[$userId]['holiday_pay'] ?? 0);
                $holiday = $holidayPay[$userId]['holiday_pay_amount'] ?? 0;
                $leave = $leavePay[$userId]['total_leave_pay'] ?? 0;

                // Deductions
                $late = $deductions['lateDeductions'][$userId] ?? 0;
                $undertime = $deductions['undertimeDeductions'][$userId] ?? 0;
                $absent = $deductions['absentDeductions'][$userId] ?? 0;

                // Get salary type for this user
                $salaryType = $salaryData->get($userId)['salary_type'] ?? null;

                // Step 1: basic salary
                // For daily_rate and hourly_rate, include leave pay; for monthly_fixed, do not include leave pay
                if ($salaryType === 'monthly_fixed') {
                    $basicSalary = $basic + $ot + $nd + $otnd + $holiday - $late - $undertime - $absent;
                } else {
                    $basicSalary = $basic + $ot + $nd + $otnd + $holiday + $leave - $late - $undertime - $absent;
                }

                // Step 2: mandates
                $sssAmt = $sss[$userId]['employee_total'] ?? 0;
                $philhealthAmt = $philhealth[$userId]['employee_total'] ?? 0;
                $pagibigAmt = $pagibig[$userId]['employee_total'] ?? 0;
                $mandatesTotal = $sssAmt + $philhealthAmt + $pagibigAmt;

                // Step 3: total 1
                $total1 = $basicSalary - $mandatesTotal;

                // Find correct tax row
                $taxRow = $taxTable->first(function ($row) use ($total1) {
                    return $total1 >= $row->range_from && $total1 <= $row->range_to;
                });

                if ($taxRow) {
                    $fix = $taxRow->fix ?? 0;
                    $rate = $taxRow->rate ?? 0;
                    $range2 = $taxRow->range_to ?? 0;

                    // Step 4: total 2
                    $total2 = $total1 - $taxRow->range_from;
                    // Step 5: total 3
                    $total3 = $total2 * $rate;
                    // Step 6: withholding tax
                    $withholdingTax = $total3 + $fix;

                    $result[$userId] = [
                        'taxable_income' => round($total1, 2),
                        'withholding_tax' => round($withholdingTax, 2),
                    ];
                    Log::info("Withholding Tax for user $userId (system)", [
                        'frequency' => $frequency,
                        'basic_salary' => round($basicSalary, 2),
                        'mandates_total' => round($mandatesTotal, 2),
                        'taxable_income' => round($total1, 2),
                        'fix' => $fix,
                        'rate' => $rate,
                        'range_from' => $taxRow->range_from,
                        'range_to' => $taxRow->range_to,
                        'total2' => round($total2, 2),
                        'total3' => round($total3, 2),
                        'withholding_tax' => round($withholdingTax, 2),
                    ]);
                } else {
                    // No matching tax row, withholding tax is 0
                    $result[$userId] = [
                        'taxable_income' => round($total1, 2),
                        'withholding_tax' => 0,
                    ];
                    Log::info("Withholding Tax for user $userId (system) - No matching tax row", [
                        'frequency' => $frequency,
                        'taxable_income' => round($total1, 2),
                    ]);
                }
            } elseif ($taxType === 'fixed') {
                if ($branch && isset($branch->fixed_withholding_tax_amount) && $branch->fixed_withholding_tax_amount > 0) {
                    $fixedAmount = $branch->fixed_withholding_tax_amount;
                    $salaryComputation = $branch->salary_computation_type ?? null;

                    $amount = round($fixedAmount, 2);
                    if ($salaryComputation === 'semi-monthly') {
                        $amount = round($fixedAmount / 2, 2);
                    }
                    $result[$userId] = [
                        'taxable_income' => round($amount, 2),
                        'withholding_tax' => round($fixedAmount, 2),
                    ];
                    Log::info("Withholding Tax for user $userId (fixed)", [
                        'taxable_income' => round($amount, 2),
                        'withholding_tax' => round($fixedAmount, 2),
                    ]);
                }
            }
        }

        return $result;
    }

    // User Deminimis
    protected function calculateUserDeminimis(array $userIds, array $data, $salaryData)
    {
        // Get payroll period
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->endOfDay();

        // Get all active user deminimis within the payroll period
        $deminimis = UserDeminimis::whereIn('user_id', $userIds)
            ->where('status', 'active')
            ->whereBetween('benefit_date', [$start, $end])
            ->get();

        $result = [];
        foreach ($userIds as $userId) {
            $userDeminimis = $deminimis->where('user_id', $userId);
            $total = $userDeminimis->sum('amount');
            $details = $userDeminimis->map(function ($item) {
                return [
                    'deminimis_benefit_id' => $item->deminimis_benefit_id,
                    'amount' => $item->amount,
                    'benefit_date' => $item->benefit_date,
                    'taxable_excess' => $item->taxable_excess,
                    'status' => $item->status,
                ];
            })->values()->all();

            // Log per-user deminimis details
            Log::info("Deminimis computed for user $userId", [
                'total_deminimis' => round($total, 2),
                'details' => $details,
            ]);

            $result[$userId] = [
                'total_deminimis' => round($total, 2),
                'details' => $details,
            ];
        }

        // Log summary for all users
        Log::info('Deminimis summary for all users', $result);

        return $result;
    }

    // Total Deductions
    protected function calculateTotalDeductions(array $userIds, array $data, $salaryData)
    {
        // Get dynamic deductions (UserDeduction)
        $dynamicDeductions = $this->calculateDeduction($userIds, $data, $salaryData);

        // Get system deductions (late, undertime, absent)
        $tenantId = Auth::user()->tenant_id ?? null;
        $totals = $this->sumMinutes($tenantId, $data);
        $systemDeductions = $this->calculateDeductions($userIds, $totals, $salaryData);

        // Get SSS, PhilHealth, and Pag-IBIG contributions
        $sss = $this->calculateSSSContribution($userIds, $data, $salaryData);
        $philhealth = $this->calculatePhilhealthContribution($userIds, $data, $salaryData);
        $pagibig = $this->calculatePagibigContribution($userIds, $data, $salaryData);

        $result = [];
        foreach ($userIds as $id) {
            $dynamicTotal = $dynamicDeductions[$id]['deductions'] ?? 0;
            $late = $systemDeductions['lateDeductions'][$id] ?? 0;
            $undertime = $systemDeductions['undertimeDeductions'][$id] ?? 0;
            $absent = $systemDeductions['absentDeductions'][$id] ?? 0;

            $sssAmt = $sss[$id]['employee_total'] ?? 0;
            $philhealthAmt = $philhealth[$id]['employee_total'] ?? 0;
            $pagibigAmt = $pagibig[$id]['employee_total'] ?? 0;

            $total = $dynamicTotal + $late + $undertime + $absent + $sssAmt + $philhealthAmt + $pagibigAmt;

            $result[$id] = [
                'total_deductions' => round($total, 2),
                'dynamic_deductions' => round($dynamicTotal, 2),
                'late_deduction' => round($late, 2),
                'undertime_deduction' => round($undertime, 2),
                'absent_deduction' => round($absent, 2),
                'sss_deduction' => round($sssAmt, 2),
                'philhealth_deduction' => round($philhealthAmt, 2),
                'pagibig_deduction' => round($pagibigAmt, 2),
                'deduction_details' => $dynamicDeductions[$id]['deduction_details'] ?? [],
            ];
        }

        // Log summary for all users
        Log::info('Total deductions summary for all users', $result);

        return $result;
    }

    // Total Earnings
    protected function calculateTotalEarnings(array $userIds, array $data, $salaryData)
    {
        // Get  overtime pay, night differential, and holiday pay
        $overtimePay = $this->calculateOvertimePay($userIds, $data, $salaryData);
        $nightDiffPay = $this->calculateNightDifferential($userIds, $data, $salaryData);
        $overtimeNightDiffPay = $this->calculateOvertimeNightDiffPay($userIds, $data, $salaryData);
        $holidayPay = $this->calculateHolidayPay(
            $this->getAttendances(Auth::user()->tenant_id, $data),
            $data,
            $salaryData
        );
        $leavePay = $this->calculateLeavePay($userIds, $data, $salaryData);
        $deminimis = $this->calculateUserDeminimis($userIds, $data, $salaryData);
        $earnings = $this->calculateEarnings($userIds, $data, $salaryData);

        $result = [];
        foreach ($userIds as $userId) {
            $overtime = $overtimePay[$userId] ?? [];
            $nightDiff = $nightDiffPay[$userId] ?? [];
            $overtimeNightDiff = $overtimeNightDiffPay[$userId] ?? [];
            $holiday = $holidayPay[$userId]['holiday_pay_amount'] ?? 0;
            $leave = $leavePay[$userId]['total_leave_pay'] ?? 0;
            $deminimisTotal = $deminimis[$userId]['total_deminimis'] ?? 0;
            $earningsTotal = $earnings[$userId]['total_earnings'] ?? 0;
            $earningsDetails = $earnings[$userId]['earnings'] ?? [];
            $totalEarnings = $holiday + $leave + $deminimisTotal + $earningsTotal
                + ($overtime['ordinary_pay'] ?? 0)
                + ($overtime['rest_day_pay'] ?? 0)
                + ($overtime['holiday_pay'] ?? 0)
                + ($overtime['holiday_rest_day_pay'] ?? 0)
                + ($nightDiff['ordinary_pay'] ?? 0)
                + ($nightDiff['rest_day_pay'] ?? 0)
                + ($nightDiff['holiday_pay'] ?? 0)
                + ($overtimeNightDiff['ordinary_pay'] ?? 0)
                + ($overtimeNightDiff['rest_day_pay'] ?? 0)
                + ($overtimeNightDiff['holiday_pay'] ?? 0);
            $result[$userId] = [
                'total_earnings' => round($totalEarnings, 2),
                'holiday_pay' => round($holiday, 2),
                'leave_pay' => round($leave, 2),
                'deminimis' => round($deminimisTotal, 2),
                'earnings' => round($earningsTotal, 2),
                'earnings_details' => $earningsDetails,
                'overtime' => [
                    'ordinary_pay' => round(
                        $overtime['ordinary_pay'] ?? 0,
                        2
                    ),
                    'rest_day_pay' => round(
                        $overtime['rest_day_pay'] ?? 0,
                        2
                    ),
                    'holiday_pay' => round(
                        $overtime['holiday_pay'] ?? 0,
                        2
                    ),
                    'holiday_rest_day_pay' => round($overtime['holiday_rest_day_pay'] ?? 0, 2),
                ],
                'night_differential' => [
                    'ordinary_pay' => round($nightDiff['ordinary_pay'] ?? 0, 2),
                    'rest_day_pay' => round($nightDiff['rest_day_pay'] ?? 0, 2),
                    'holiday_pay' => round($nightDiff['holiday_pay'] ?? 0, 2),
                ],
                'overtime_night_differential' => [
                    'ordinary_pay' => round($overtimeNightDiff['ordinary_pay'] ?? 0, 2),
                    'rest_day_pay' => round($overtimeNightDiff['rest_day_pay'] ?? 0, 2),
                    'holiday_pay' => round($overtimeNightDiff['holiday_pay'] ?? 0, 2),
                ],
            ];
            // Log earnings details for each user
            Log::info("Total earnings computed for user $userId", [
                'total_earnings' => round($totalEarnings, 2),
                'holiday_pay' => round($holiday, 2),
                'leave_pay' => round($leave, 2),
                'deminimis' => round($deminimisTotal, 2),
                'earnings' => round($earningsTotal, 2),
                'earnings_details' => $earningsDetails,
            ]);
        }
        // Log summary for all users
        Log::info('Total earnings summary for all users', $result);
        return $result;
    }

    // Net Pay Calculation
    protected function calculateNetPay($userIds, $basicPayData, $earningsData, $deductionsData)
    {
        $results = [];
        foreach ($userIds as $userId) {
            // Get basic salary
            $basicSalary = $basicPayData[$userId]['basic_pay'] ?? 0;

            // Get total earnings
            $totalEarnings = $earningsData[$userId]['total_earnings'] ?? 0;

            // Get total deductions
            $totalDeductions = $deductionsData[$userId]['total_deductions'] ?? 0;

            // Net pay = basic salary + total earnings - total deductions
            $netPay = round($basicSalary + $totalEarnings - $totalDeductions, 2);

            $results[$userId] = [
                'basic_salary' => $basicSalary,
                'total_earnings' => $totalEarnings,
                'total_deductions' => $totalDeductions,
                'net_pay' => $netPay,
            ];
        }
        return $results;
    }
}
