<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use App\Models\Holiday;
use App\Models\Payroll;
use App\Models\UserLog;
use App\Models\Overtime;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\UserEarning;
use App\Models\LeaveRequest;
use App\Models\SalaryRecord;
use Illuminate\Http\Request;
use App\Models\UserAllowance;
use App\Models\UserDeduction;
use App\Models\UserDeminimis;
use App\Exports\PayrollExport;
use App\Models\BulkAttendance;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\HolidayException;
use App\Helpers\PermissionHelper;
use App\Models\DeminimisBenefits;
use App\Models\EmployeeBankDetail;
use App\Models\ThirteenthMonthPay;
use Illuminate\Support\Facades\DB;
use App\Models\WithholdingTaxTable;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\MandatesContribution;
use App\Models\PayrollBatchSettings;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PhilhealthContribution;
use App\Http\Controllers\DataAccessController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PayrollController extends Controller
{

    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    // Process Index
    public function payrollProcessIndex(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(24);
        $tenantId = $authUser->tenant_id ?? null;
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get();
        $deminimisBenefits = DeminimisBenefits::pluck('name', 'id')->map(function ($name) {
            return ucwords(str_replace('_', ' ', $name));
        });

        $payrolls = $accessData['payrolls']->get();
        $payrollBatches = PayrollBatchSettings::where('tenant_id', $tenantId)->get();

        $thirteenthMonthPayrolls = ThirteenthMonthPay::where('tenant_id', $tenantId)
            ->where('status', 'Pending')
            ->with(['user.personalInformation', 'user.employmentDetail.branch'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll Process Index',
                'data' => $payrolls,
                'payrollBatches' => $payrollBatches,
                'thirteenthMonthPayrolls' => $thirteenthMonthPayrolls,
            ]);
        }

        return view('tenant.payroll.process', compact('branches', 'departments', 'designations', 'payrolls', 'deminimisBenefits', 'permission', 'payrollBatches', 'thirteenthMonthPayrolls'));
    }

    // payroll process filter

    public function payrollProcessIndexFilter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(24);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation');

        $query  =  $accessData['payrolls'];

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('transaction_date', [$start, $end]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date range format.'
                ]);
            }
        }

        if ($branch) {
            $query->whereHas('user.employmentDetail', function ($q) use ($branch) {
                $q->where('branch_id', $branch);
            });
        }
        if ($department) {
            $query->whereHas('user.employmentDetail', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
        if ($designation) {
            $query->whereHas('user.employmentDetail', function ($q) use ($designation) {
                $q->where('designation_id', $designation);
            });
        }

        $payrolls = $query->get();
        $html = view('tenant.payroll.process_filter', compact('payrolls', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }


    // Payroll Process Store
    public function payrollProcessStore(Request $request)
    {

        $authUser = $this->authUser();
        $permission = PermissionHelper::get(24);
        $tenantId = $authUser->tenant_id ?? null;

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
        }

        $data = $request->validate([
            'user_id'    => 'required|array',
            'user_id.*'  => 'integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'transaction_date' => 'nullable|date',
        ]);

        $pagibigOption = $request->input('pagibig_option');
        $sssOption = $request->input('sss_option');
        $philhealthOption = $request->input('philhealth_option');
        $cuttoffOption = $request->input('cutoff_period');
        $payrollType = $request->input('payroll_type', 'normal_payroll');
        $payrollMonth = $request->input('month', null);
        $payrollYear = $request->input('year', null);
        $payrollPeriod = $request->input('payroll_period', null);
        $paymentDate = $request->input('payment_date', now()->toDateString());

        if ($payrollType === 'normal_payroll') {
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
            $userAllowances = $this->calculateAllowance($data['user_id'], $data, $salaryData);
            $userDeductions = $this->calculateDeduction($data['user_id'], $data, $salaryData);
            $basicPay = $this->calculateBasicPay($data['user_id'], $data, $salaryData);
            $grossPay = $this->calculateGrossPay($data['user_id'], $data, $salaryData);
            $sssContributions = $this->calculateSSSContribution($data['user_id'], $data, $salaryData, $sssOption, $cuttoffOption);
            $philhealthContributions = $this->calculatePhilhealthContribution($data['user_id'], $data, $salaryData, $philhealthOption, $cuttoffOption);
            $pagibigContributions = $this->calculatePagibigContribution($data['user_id'], $data, $salaryData, $pagibigOption);
            $withholdingTax = $this->calculateWithholdingTax($data['user_id'], $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cuttoffOption);
            $leavePay = $this->calculateLeavePay($data['user_id'], $data, $salaryData);
            $deminimisBenefits = $this->calculateUserDeminimis($data['user_id'], $data, $salaryData);
            $totalDeductions = $this->calculateTotalDeductions($data['user_id'], $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cuttoffOption);
            $totalEarnings = $this->calculateTotalEarnings($data['user_id'], $data, $salaryData);
            $netPay = $this->calculateNetPay($data['user_id'], $basicPay, $totalEarnings, $totalDeductions);
            $thirteenthMonth = $this->calculateThirteenthMonthPay($data['user_id'], $data, $salaryData);

            // Save computed payroll for each user
            foreach ($data['user_id'] as $userId) {
                $payroll = Payroll::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'user_id' => $userId,
                        'payroll_period_start' => $data['start_date'],
                        'payroll_period_end' => $data['end_date'],
                        'payroll_type' => $payrollType,
                        'transaction_date' => $data['transaction_date'],
                    ],
                    [
                        'payroll_period' => $payrollPeriod,
                        'payroll_month' => $payrollMonth,
                        'payroll_year' => $payrollYear,
                        'total_worked_minutes' => $totals['work'][$userId] ?? 0,
                        'total_late_minutes' => $totals['late'][$userId] ?? 0,
                        'total_undertime_minutes' => $totals['undertime'][$userId] ?? 0,
                        'total_overtime_minutes' => $overtimePay[$userId]['total_ot_minutes'] ?? 0,
                        'total_night_differential_minutes' => $totals['night_diff'][$userId] ?? 0,
                        'total_overtime_night_diff_minutes' => $overtimeNightDiffPay[$userId]['total_night_diff_minutes'] ?? 0,
                        'total_worked_days' => $totals['work_days'][$userId] ?? 0,
                        'total_absent_days' => $totals['absent'][$userId] ?? 0,

                        // Pay breakdown
                        'holiday_pay' => $holidayInfo[$userId]['holiday_pay_amount'] ?? 0,
                        'leave_pay' => $leavePay[$userId]['total_leave_pay'] ?? 0,
                        'overtime_pay' => ($overtimePay[$userId]['ordinary_pay'] ?? 0) + ($overtimePay[$userId]['holiday_pay'] ?? 0),

                        'night_differential_pay' => ($nightDiffInfo[$userId]['ordinary_pay'] ?? 0) + ($nightDiffInfo[$userId]['rest_day_pay'] ?? 0)
                            + ($nightDiffInfo[$userId]['holiday_pay'] ?? 0)
                            + ($nightDiffInfo[$userId]['holiday_rest_day_pay'] ?? 0),

                        'overtime_night_diff_pay' => ($overtimeNightDiffPay[$userId]['ordinary_pay'] ?? 0) +
                            ($overtimeNightDiffPay[$userId]['rest_day_pay'] ?? 0) +
                            ($overtimeNightDiffPay[$userId]['holiday_pay'] ?? 0) +
                            ($overtimeNightDiffPay[$userId]['holiday_rest_day_pay'] ?? 0) +
                            ($overtimeNightDiffPay[$userId]['holiday_rest_day_pay'] ?? 0),

                        'late_deduction' => $deductions['lateDeductions'][$userId] ?? 0,
                        'overtime_restday_pay' => ($overtimePay[$userId]['rest_day_pay'] ?? 0) + ($overtimePay[$userId]['holiday_rest_day_pay'] ?? 0),
                        'undertime_deduction' => $deductions['undertimeDeductions'][$userId] ?? 0,
                        'absent_deduction' => $deductions['absentDeductions'][$userId] ?? 0,
                        'earnings' => isset($userEarnings[$userId]['earning_details']) ? json_encode($userEarnings[$userId]['earning_details']) : null,
                        'total_earnings' => $totalEarnings[$userId]['total_earnings'] ?? 0,
                        'allowance' => isset($userAllowances[$userId]['allowance_details']) ? json_encode($userAllowances[$userId]['allowance_details']) : null,
                        'taxable_income' => 0,

                        // De Minimis
                        'deminimis' => isset($deminimisBenefits[$userId]['details']) ? json_encode($deminimisBenefits[$userId]['details']) : null,

                        // Deductions
                        'sss_contribution' => $sssContributions[$userId]['employee_total'] ?? 0,
                        'sss_contribution_employer' => $sssContributions[$userId]['employer_total'] ?? 0,
                        'philhealth_contribution' => $philhealthContributions[$userId]['employee_total'] ?? 0,
                        'philhealth_contribution_employer' => $philhealthContributions[$userId]['employer_total'] ?? 0,
                        'pagibig_contribution' => $pagibigContributions[$userId]['employee_total'] ?? 0,
                        'pagibig_contribution_employer' => $pagibigContributions[$userId]['employer_total'] ?? 0,
                        'withholding_tax' => $withholdingTax[$userId]['withholding_tax'] ?? 0,
                        'loan_deductions' => null,
                        'deductions' => isset($userDeductions[$userId]['deduction_details']) ? json_encode($userDeductions[$userId]['deduction_details']) : null,
                        'total_deductions' => $totalDeductions[$userId]['total_deductions'] ?? 0,

                        // Salary Breakdown
                        'basic_pay' => $basicPay[$userId]['basic_pay'] ?? 0,
                        'gross_pay' => $grossPay[$userId]['gross_pay'] ?? 0,
                        'net_salary' => $netPay[$userId]['net_pay'] ?? 0,

                        // 13th month
                        'thirteenth_month_pay' => $thirteenthMonth[$userId]['thirteenth_month'] ?? 0,

                        // Payment Info
                        'payment_date' => $paymentDate,
                        'processor_type' => Auth::user() ? get_class(Auth::user()) : null,
                        'processor_id' => Auth::id(),
                        'status' => 'Pending',
                        'remarks' => null,
                    ]
                );
            }

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
                'message'           => 'Payroll processed and saved.',
            ]);
        } else {
            return response()->json([
                'message' => 'Payroll type not supported yet.',
            ], 422);
        }
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

        $overtimes = Overtime::with(['user'])
            ->whereIn('user_id', $data['user_id'])
            ->whereBetween('overtime_date', [$start, $end])
            ->where('status', 'approved')
            ->whereHas('user', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->orderBy('overtime_date')
            ->get();

        return $overtimes;
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
            ->where('status', 'approved')
            ->whereHas('user', fn($q) => $q->where('tenant_id', $tenantId));

        $work = (clone $base)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_work_minutes) as total'))
            ->pluck('total', 'user_id')->toArray();
        $late = (clone $base)
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

        $nightDiffOt = (clone $baseOt)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(total_night_diff_minutes) as total'))
            ->pluck('total', 'user_id')->toArray();

        $result = [
            'work'        => $work,
            'late'        => $late,
            'undertime'   => $undertime,
            'night_diff'  => $nightDiff,
            'absent'      => $absent,
            'work_days'   => $workDays,
            'workOt'      => $workOt,
        ];

        return $result;
    }

    // Get Salary Data
    protected function getSalaryData(array $userIds)
    {
        return SalaryRecord::whereIn('user_id', $userIds)
            ->where('is_active', 1)
            ->get()
            ->mapWithKeys(function ($r) {
                // Get worked_days_per_year from salaryDetail
                $workedDays = $r->user->salaryDetail->worked_days_per_year ?? null;

                // If null or 0, get from branch
                if (empty($workedDays) && $r->user->employmentDetail && $r->user->employmentDetail->branch) {
                    $branch = $r->user->employmentDetail->branch;
                    if (isset($branch->worked_days_per_year)) {
                        if ($branch->worked_days_per_year === 'custom' && isset($branch->custom_worked_days)) {
                            $workedDays = $branch->custom_worked_days;
                        } else {
                            $workedDays = $branch->worked_days_per_year;
                        }
                    }
                }

                // Default to 0 if still not set
                $workedDays = $workedDays ?? 0;

                return [
                    $r->user_id => [
                        'basic_salary'         => $r->basic_salary,
                        'salary_type'          => $r->salary_type,
                        'worked_days_per_year' => $workedDays,
                    ]
                ];
            });
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
            ->map(fn(\Carbon\Carbon $d) => $d->format('m-d'))
            ->unique()->values()->all();

        $hols = Holiday::where(function($query) use ($start, $end, $monthDays) {
                $query->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function($q) use ($monthDays) {
                        $q->where('recurring', 1)->whereIn('month_day', $monthDays);
                    });
            })
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
                $perMin = $basic / 60;
                $dailyRate = $basic * 8;
            } elseif ($stype === 'daily_rate') {
                $perMin = ($basic / 8) / 60;
                $dailyRate = $basic;
            } elseif ($stype === 'monthly_fixed') {
                $dailyRate = $wpy > 0 ? ($basic * 12) / $wpy : 0;
                $perMin = ($dailyRate / 8) / 60;
            } else {
                $sd = ($data['workDays'][$id] ?? 0) + ($data['absent'][$id] ?? 0);
                $dailyRate = $sd > 0 ? ($basic / $sd) : 0;
                $perMin = ($dailyRate / 8) / 60;
            }

            $holDays = $holWork = $payTotal = 0;
            $breakdown = [];

            foreach ($hols as $h) {
                if (in_array($id, $exceptions->get($h->id, []))) continue;

                // For recurring holidays, match by month_day (MM-DD format)
                // For non-recurring holidays, match by exact date
                $att = $attendances->first(function ($a) use ($id, $h) {
                    if ($a->user_id != $id) return false;
                    
                    if ($h->recurring && $h->month_day) {
                        // Match by month-day format
                        return $a->attendance_date->format('m-d') == $h->month_day;
                    } else {
                        // Match by exact date
                        return $a->attendance_date->toDateString() == Carbon::parse($h->date)->toDateString();
                    }
                });

                $worked = (bool)$att;
                $mins   = $worked ? $att->total_work_minutes : 0;
                $pay    = 0;
                $perMinPay = 0;

                if ($worked) {
                    if ($stype === 'hourly_rate') {
                        // For hourly, pay only for actual minutes worked, no multiplier
                        $pay = $perMin * $mins;
                        $perMinPay = $perMin;
                    } elseif ($stype === 'daily_rate') {
                        // For daily, pay for actual minutes worked, no multiplier
                        $pay = $perMin * $mins;
                        $perMinPay = $perMin;
                    } elseif ($stype === 'monthly_fixed') {
                        // For monthly_fixed, pay per minute for actual minutes worked on holiday
                        $pay = $perMin * $mins;
                        $perMinPay = $perMin;
                    } else {
                        // For other types, fallback to daily
                        $pay = $perMin * $mins;
                        $perMinPay = $perMin;
                    }
                    $holWork++;
                } else {
                    if ($h->type === 'regular') {
                        $pay = $stype === 'hourly_rate'
                            ? $perMin * 480
                            : $dailyRate;
                        $perMinPay = $perMin;
                        $holDays++;
                    } elseif (
                        in_array($h->type, ['special-non-working', 'special-working'])
                        && $stype === 'monthly_fixed'
                    ) {
                        $pay = $dailyRate;
                        $perMinPay = $perMin;
                        $holDays++;
                    }
                }
                $payTotal += $pay;
                
                // Determine the actual holiday date for display
                $holidayDate = $h->date;
                if ($h->recurring && $h->month_day) {
                    // For recurring holidays, find the actual date within the payroll period
                    foreach ($period as $periodDate) {
                        if ($periodDate->format('m-d') == $h->month_day) {
                            $holidayDate = $periodDate->toDateString();
                            break;
                        }
                    }
                }
                
                $breakdown[] = [
                    'holiday_id' => $h->id,
                    'holiday_date' => $holidayDate,
                    'holiday_type' => $h->type,
                    'worked' => $worked,
                    'minutes_worked' => $mins,
                    'pay' => round($pay, 2),
                    'per_min' => round($perMinPay, 6),
                ];
            }
            $result[$id] = [
                'holiday_days' => $holDays,
                'holiday_work_days' => $holWork,
                'holiday_pay_amount' => round($payTotal, 2),
                'breakdown' => $breakdown,
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
            ->whereBetween(DB::raw('DATE(attendance_date)'), [$start->toDateString(), $end->toDateString()])
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
            $sal   = $salaryData->get($id, ['basic_salary' => 0, 'salary_type' => 'hourly_rate', 'worked_days_per_year' => 0]);
            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];
            $wpy   = $sal['worked_days_per_year'] ?? 0;
            $ord  = $ordMins->get($id, 0);
            $rst  = $rstMins->get($id, 0);
            $mOrd = $multipliers['ordinary'] ?? 0;
            $mRst = $multipliers['rest_day'] ?? 0;

            $payOrd = $payRst = 0;
            $holPay = 0;

            // Add monthly_fixed computation
            if ($stype === 'monthly_fixed') {
                // Get per minute rate using worked_days_per_year
                $perMin = 0;
                if ($wpy > 0) {
                    $dailyRate = ($basic * 12) / $wpy;
                    $perMin = ($dailyRate / 8) / 60;
                }
                $payOrd = round($perMin * $ord * $mOrd, 2);
                $payRst = round($perMin * $rst * $mRst, 2);
            } elseif (in_array($stype, ['hourly_rate', 'daily_rate']) && ($ord + $rst) > 0) {
                $perMin = $stype === 'hourly_rate' ? $basic / 60 : ($basic / 8) / 60;
                $payOrd = round($perMin * $ord * $mOrd, 2);
                $payRst = round($perMin * $rst * $mRst, 2);
            }

            // Holiday only
            if (in_array($stype, ['hourly_rate', 'daily_rate', 'monthly_fixed'])) {
                if ($stype === 'monthly_fixed' && $wpy > 0) {
                    $dailyRate = ($basic * 12) / $wpy;
                    $perMin = ($dailyRate / 8) / 60;
                } else {
                    $perMin = $stype === 'hourly_rate' ? $basic / 60 : ($basic / 8) / 60;
                }

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
                    }

                    $holNDMin   = min($holidayMin, $att->total_night_diff_minutes);
                    $holNDMin   = max(0, (int) $holNDMin);

                    $usedNDMin = $holNDMin + $nextHolNDMin;

                    $ordNDMin   = $att->total_night_diff_minutes - $usedNDMin;
                    $ordNDMin   = max(0, (int) $ordNDMin);

                    $multKey      = $hType === 'regular' ? 'regular_holiday' : 'special_holiday';
                    $hMult        = $multipliers[$multKey] ?? 0;

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

                    Log::debug('Night Differential: Holiday+RestDay Multiplier', [
                        'user_id' => $id,
                        'holiday_type' => $hType,
                        'holiday_multiplier' => $hMult,
                        'mult_key' => $multKey,
                    ]);

                    $holPay += round($perMin * $holNDMin * $hMult, 2);

                    if ($nextHolNDMin > 0 && $nextHMult > 0) {
                        $holPay += round($perMin * $nextHolNDMin * $nextHMult, 2);
                    }

                    if ($ordNDMin > 0) {
                        $payOrd += round($perMin * $ordNDMin * $mOrd, 2);
                    }

                    Log::debug('Night Differential: Holiday+RestDay', [
                        'user_id' => $id,
                        'holiday_type' => $hType,
                        'holiday_minutes' => $holNDMin,
                        'next_holiday_minutes' => $nextHolNDMin,
                        'ordinary_minutes' => $ordNDMin,
                        'holiday_multiplier' => $hMult,
                        'next_holiday_multiplier' => $nextHMult,
                        'payOrd' => $payOrd,
                        'holPay' => $holPay,
                    ]);
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
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->whereNotNull('offset_date')
                        ->whereBetween(DB::raw('DATE(offset_date)'), [$start->toDateString(), $end->toDateString()]);
                })
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->whereNull('offset_date')
                            ->whereBetween(DB::raw('DATE(overtime_date)'), [$start->toDateString(), $end->toDateString()]);
                    });
            })
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
            $sal   = $salaryData->get($id, ['basic_salary' => 0, 'salary_type' => 'hourly_rate', 'worked_days_per_year' => 0]);
            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];
            $wpy   = $sal['worked_days_per_year'];

            $ord  = $ordMins->get($id, 0);
            $mOrd = $otMultipliers['ordinary'] ?? 0;
            $mRst = $otMultipliers['rest_day'] ?? 0;
            $rd  = $otRdMins->get($id, 0);

            $payOrd = 0;
            $payRd = 0;
            $payHol = 0;
            $payRdHol = 0;

            // Per-minute rate calculation for all types
            if ($stype === 'hourly_rate') {
                $perMin = $basic / 60;
            } elseif ($stype === 'daily_rate') {
                $perMin = ($basic / 8) / 60;
            } elseif ($stype === 'monthly_fixed') {
                // For monthly_fixed, compute daily rate based on worked_days_per_year
                $dailyRate = $wpy > 0 ? ($basic * 12) / $wpy : 0;
                $perMin = ($dailyRate / 8) / 60;
            } else {
                // Fallback: treat as daily
                $perMin = ($basic / 8) / 60;
            }

            // Normal overtime pay
            if (in_array($stype, ['hourly_rate', 'daily_rate', 'monthly_fixed']) && $ord > 0) {
                $payOrd = round($perMin * $ord * $mOrd, 2);
            }

            // Rest day overtime pay
            if (in_array($stype, ['hourly_rate', 'daily_rate', 'monthly_fixed']) && $rd > 0) {
                $payRd = round($perMin * $rd * $mRst, 2);
            }

            // Holiday overtime pay
            if (in_array($stype, ['hourly_rate', 'daily_rate', 'monthly_fixed']) && $otHol->has($id)) {
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

                    // Use Carbon parsing to ensure correct date comparison
                    $otOvertimeDate = $ot->overtime_date ? Carbon::parse($ot->overtime_date) : null;
                    $otOffsetDate = $ot->offset_date ? Carbon::parse($ot->offset_date) : null;

                    // Only include if the date falls within the range
                    $include = false;
                    if ($otOffsetDate && $otOffsetDate->between($start, $end)) {
                        $include = true;
                    } elseif ($otOvertimeDate && $otOvertimeDate->between($start, $end)) {
                        $include = true;
                    }

                    if (!$include) {
                        continue;
                    }

                    $pay = round($perMin * $ot->total_ot_minutes * $multiplier, 2);

                    $payHol += $pay;
                }
            }

            // Holiday + Rest Day overtime pay
            if (in_array($stype, ['hourly_rate', 'daily_rate', 'monthly_fixed']) && $otHolRst->has($id)) {
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

                    // Use Carbon parsing to ensure correct date comparison
                    $otOvertimeDate = $ot->overtime_date ? Carbon::parse($ot->overtime_date) : null;
                    $otOffsetDate = $ot->offset_date ? Carbon::parse($ot->offset_date) : null;

                    // Only include if the date falls within the range
                    $include = false;
                    if ($otOffsetDate && $otOffsetDate->between($start, $end)) {
                        $include = true;
                    } elseif ($otOvertimeDate && $otOvertimeDate->between($start, $end)) {
                        $include = true;
                    }

                    if (!$include) {
                        continue;
                    }

                    $pay = round($perMin * $ot->total_ot_minutes * $multiplier, 2);

                    $payRdHol += $pay;
                }
            }

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
            $sal   = $salaryData->get($id, ['basic_salary' => 0, 'salary_type' => 'hourly_rate', 'worked_days_per_year' => 0]);
            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];
            $wpy   = $sal['worked_days_per_year'] ?? 0;
            $ord  = $ordinaryMins->get($id, 0);
            $mOrd = $otMultipliers['ordinary'] ?? 0;
            $rst  = $restdayMins->get($id, 0);
            $mRst = $otMultipliers['rest_day'] ?? 0;

            $payOrd = 0;
            $payRst = 0;
            $payHol = 0;
            $payHolRst = 0; // Add holiday + restday pay

            // Calculate per minute rate
            if ($stype === 'hourly_rate') {
                $perMin = $basic / 60;
            } elseif ($stype === 'daily_rate') {
                $perMin = ($basic / 8) / 60;
            } elseif ($stype === 'monthly_fixed') {
                $dailyRate = $wpy > 0 ? ($basic * 12) / $wpy : 0;
                $perMin = ($dailyRate / 8) / 60;
            } else {
                $perMin = ($basic / 8) / 60;
            }

            // Calculate ordinary overtime pay
            if (in_array($stype, ['hourly_rate', 'daily_rate', 'monthly_fixed']) && $ord > 0) {
                $payOrd = round($perMin * $ord * $mOrd, 2);
            }

            // Calculate rest day overtime pay
            if (in_array($stype, ['hourly_rate', 'daily_rate', 'monthly_fixed']) && $rst > 0) {
                $payRst = round($perMin * $rst * $mRst, 2);
            }

            // Calculate holiday overtime pay
            if (in_array($stype, ['hourly_rate', 'daily_rate', 'monthly_fixed'])) {
                foreach ($holidayMins->get($id, collect()) as $att) {
                    $holidayOrig = optional($att->holiday)->date;
                    $hType = optional($att->holiday)->type;

                    $attIn = Carbon::parse($att->date_ot_in);
                    $attOut = Carbon::parse($att->date_ot_out);

                    // Night diff window: 22:00:00 to 06:00:00 next day
                    $nightStart = $attIn->copy()->setTime(22, 0, 0);
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
                    }

                    $usedNDMin = $holNDMin + $nextHolNDMin;
                    $ordNDMin = $nightDiffMinutes - $usedNDMin;
                    $ordNDMin = max(0, (int) $ordNDMin);

                    $multKey = $hType === 'regular' ? 'regular_holiday' : 'special_holiday';
                    $hMult = $otMultipliers[$multKey] ?? 0;

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
                    }

                    $usedNDMin = $holNDMin + $nextHolNDMin;
                    $ordNDMin = $nightDiffMinutes - $usedNDMin;
                    $ordNDMin = max(0, (int) $ordNDMin);

                    $multKey = $hType === 'regular'
                        ? 'regular_holiday_rest_day'
                        : 'special_holiday_rest_day';
                    $hMult = $otMultipliers[$multKey] ?? 0;
                    $payHolRst += round($perMin * $holNDMin * $hMult, 2);

                    if ($nextHolNDMin > 0 && $nextHMult > 0) {
                        $payHolRst += round($perMin * $nextHolNDMin * $nextHMult, 2);
                    }

                    if ($ordNDMin > 0) {
                        $payOrd += round($perMin * $ordNDMin * $mOrd, 2);
                    }
                }
            }

            $payHol = round($payHol, 2);
            $payHolRst = round($payHolRst, 2);

            // Add to result
            $result[$id] = [
                'ordinary_pay' => $payOrd,
                'rest_day_pay' => $payRst,
                'holiday_pay' => $payHol,
                'holiday_rest_day_pay' => $payHolRst, //  holiday + restday
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

    // Calculate Allowance (Dynamic Allowance)
    protected function calculateAllowance(array $userIds, array $data, $salaryData)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();

        // Get all user allowances with their allowance types
        $allowances = UserAllowance::whereIn('user_id', $userIds)
            ->where('status', 'active')
            ->where(function ($q) use ($start, $end) {
                $q->where('effective_start_date', '<=', $end)
                    ->where(function ($q2) use ($start) {
                        $q2->whereNull('effective_end_date')
                            ->orWhere('effective_end_date', '>=', $start);
                    });
            })
            ->with('allowance')
            ->get();

        // Get total work minutes and work days for each user
        $tenantId = Auth::user()->tenant_id ?? null;
        $totals = $this->sumMinutes($tenantId, $data);

        // Preload attendances for all users in the period
        $attendances = $this->getAttendances($tenantId, $data)->groupBy('user_id');

        $result = [];
        foreach ($userIds as $id) {
            $userAllowance = $allowances->where('user_id', $id);
            $total = 0;
            $details = [];
            $total_work_minutes = $totals['work'][$id] ?? 0;
            $total_work_days = $totals['work_days'][$id] ?? 0;

            foreach ($userAllowance as $allowance) {
                // Use override if enabled, otherwise use Allowance model
                $overrideEnabled = $allowance->override_enabled == 1 || $allowance->override_enabled === true;
                $allowanceModel = $allowance->allowance;

                if (!$allowanceModel) continue;

                // Calculation basis and amount
                $calculation_basis = $overrideEnabled
                    ? $allowance->calculation_basis
                    : $allowanceModel->calculation_basis;

                $amount = $overrideEnabled
                    ? $allowance->override_amount
                    : $allowanceModel->amount;

                // Frequency logic
                $include = false;
                if ($allowance->frequency == 'every_payroll') {
                    $include = true;
                } elseif ($allowance->frequency == 'one_time') {
                    if ($allowance->effective_start_date && Carbon::parse($allowance->effective_start_date)->between($start, $end)) $include = true;
                } elseif ($allowance->frequency == 'every_other') {
                    $payrollNumber = $start->diffInWeeks(Carbon::create(2020, 1, 1));
                    if ($payrollNumber % 2 == 0) $include = true;
                }

                if (!$include) continue;

                // Compute final amount based on calculation_basis
                $finalAmount = 0;
                if ($calculation_basis === 'fixed') {
                    $finalAmount = $amount;
                } elseif ($calculation_basis === 'per_attended_day') {
                    // Adjust attendance days based on effective_start_date/effective_end_date
                    $effectiveStart = $allowance->effective_start_date
                        ? Carbon::parse($allowance->effective_start_date)->startOfDay()
                        : $start;
                    $effectiveEnd = $allowance->effective_end_date
                        ? Carbon::parse($allowance->effective_end_date)->endOfDay()
                        : $end;

                    // The actual period to consider is the overlap of payroll period and allowance effective period
                    $periodStart = $effectiveStart->greaterThan($start) ? $effectiveStart : $start;
                    $periodEnd = $effectiveEnd->lessThan($end) ? $effectiveEnd : $end;

                    // Count attended days in this period
                    $userAtt = $attendances->get($id, collect());
                    $attendedDays = $userAtt->filter(function ($att) use ($periodStart, $periodEnd) {
                        return $att->attendance_date >= $periodStart && $att->attendance_date <= $periodEnd && $att->status !== 'absent';
                    })->count();

                    $finalAmount = $amount * $attendedDays;
                } elseif ($calculation_basis === 'per_attended_hour') {
                    // Adjust attendance minutes based on effective_start_date/effective_end_date
                    $effectiveStart = $allowance->effective_start_date
                        ? Carbon::parse($allowance->effective_start_date)->startOfDay()
                        : $start;
                    $effectiveEnd = $allowance->effective_end_date
                        ? Carbon::parse($allowance->effective_end_date)->endOfDay()
                        : $end;

                    $periodStart = $effectiveStart->greaterThan($start) ? $effectiveStart : $start;
                    $periodEnd = $effectiveEnd->lessThan($end) ? $effectiveEnd : $end;

                    $userAtt = $attendances->get($id, collect());
                    $attendedMinutes = $userAtt->filter(function ($att) use ($periodStart, $periodEnd) {
                        return $att->attendance_date >= $periodStart && $att->attendance_date <= $periodEnd && $att->status !== 'absent';
                    })->sum('total_work_minutes');

                    $hours = $attendedMinutes / 60;
                    $finalAmount = $amount * $hours;
                } else {
                    $finalAmount = $amount; // fallback
                }


                $details[] = [
                    'allowance_id'           => $allowanceModel->id,
                    'allowance_name'         => $allowanceModel->allowance_name,
                    'calculation_basis'      => $calculation_basis,
                    'amount'                 => $amount,
                    'is_taxable'             => $allowanceModel->is_taxable,
                    'apply_to_all_employees' => $allowanceModel->apply_to_all_employees,
                    'description'            => $allowanceModel->description,
                    'user_amount_override'   => $allowance->override_amount,
                    'override_enabled'       => $overrideEnabled,
                    'applied_amount'         => round($finalAmount, 2),
                    'frequency'              => $allowance->frequency,
                    'status'                 => $allowance->status,
                ];

                $total += $finalAmount;
            }

            $result[$id] = [
                'total_allowance'    => round($total, 2),
                'allowance_details'  => $details,
                'total_work_minutes' => $total_work_minutes,
                'total_work_days'    => $total_work_days,
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

                // Use days_requested field for leave days
                $leaveDays = $leave->days_requested ?? 0;

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
            }

            $result[$userId] = [
                'total_leave_pay' => round($totalLeavePay, 2),
                'leaves' => $leaveDetails,
            ];
        }

        return $result;
    }

    // SSS Contribution Calculation
    protected function calculateSSSContribution(array $userIds, array $data, $salaryData, $sssOption, $cutoffOption)
    {
        // Preload user branch SSS contribution type, template, and fixed amount + salary details
        $users = User::with(['employmentDetail.branch', 'salaryDetail'])->whereIn('id', $userIds)->get()->keyBy('id');

        $result = [];
        foreach ($userIds as $userId) {
            $user = $users[$userId] ?? null;
            $branch = $user && $user->employmentDetail ? $user->employmentDetail->branch : null;
            $salaryDetail = $user ? $user->salaryDetail : null;

            $sssType = $branch->sss_contribution_type ?? null;
            $sssTemplateYear = $branch->sss_contribution_template ?? null;
            $fixedSssAmount = $branch->fixed_sss_amount ?? null;


            // Try to get worked_days_per_year from salaryData
            $workedDaysPerYear = $salaryData->get($userId)['worked_days_per_year'] ?? null;
            $workedDaysSource = 'salary_data';

            // If null or 0, get from branch
            if ((is_null($workedDaysPerYear) || $workedDaysPerYear == 0) && $branch) {
                if (isset($branch->worked_days_per_year)) {
                    if ($branch->worked_days_per_year === 'custom' && isset($branch->custom_worked_days)) {
                        $workedDaysPerYear = $branch->custom_worked_days;
                        $workedDaysSource = 'branch_custom';
                    } else {
                        $workedDaysPerYear = $branch->worked_days_per_year;
                        $workedDaysSource = 'branch';
                    }
                }
            }

            // Get the gross pay and basic pay
            $grossPay = $this->calculateGrossPay([$userId], $data, $salaryData);
            $basicPay = $this->calculateBasicPay([$userId], $data, $salaryData);

            // Default to 0 if not found
            $result[$userId] = [
                'employer_total' => 0,
                'employee_total' => 0,
                'total_contribution' => 0,
                'worked_days_per_year' => $workedDaysPerYear,
            ];

            // ✅ ENHANCED: Handle manual override logic
            $finalSssEmployeeAmount = 0;

            // Check if branch is set to "manual" - then check user's salary detail
            if ($sssType === 'manual' && $salaryDetail) {
                $userSssContributionType = $salaryDetail->sss_contribution ?? null;


                if ($userSssContributionType === 'system') {
                    // ✅ User chose system computation - calculate normally

                    // Calculate monthly salary equivalent based on salary type
                    $monthlySalaryEquivalent = 0;
                    $stype = $salaryData->get($userId)['salary_type'] ?? null;
                    $basicSalary = $salaryData->get($userId)['basic_salary'] ?? 0;

                    if ($stype === 'monthly_fixed') {
                        $monthlySalaryEquivalent = $basicSalary;
                    } elseif ($stype === 'daily_rate') {
                        if ($workedDaysPerYear > 0) {
                            $monthlySalaryEquivalent = ($basicSalary * $workedDaysPerYear) / 12;
                        } else {
                            $monthlySalaryEquivalent = $basicSalary * 22;
                        }
                    } elseif ($stype === 'hourly_rate') {
                        if ($workedDaysPerYear > 0) {
                            $dailyEquivalent = $basicSalary * 8;
                            $monthlySalaryEquivalent = ($dailyEquivalent * $workedDaysPerYear) / 12;
                        } else {
                            $monthlySalaryEquivalent = $basicSalary * 8 * 22;
                        }
                    } else {
                        $monthlySalaryEquivalent = $grossPay[$userId]['gross_pay'] ?? 0;
                    }

                    // Get SSS table for the selected year (template)
                    $sssTableQuery = DB::table('sss_contribution_tables');
                    if ($sssTemplateYear) {
                        $sssTableQuery->where('year', $sssTemplateYear);
                    }
                    $sssTable = $sssTableQuery->get();

                    // Find SSS contribution based on monthly salary equivalent
                    $sssContribution = $sssTable->first(function ($item) use ($monthlySalaryEquivalent) {
                        return $monthlySalaryEquivalent >= $item->range_from && $monthlySalaryEquivalent <= $item->range_to;
                    });

                    $finalSssEmployeeAmount = $sssContribution ? $sssContribution->employee_total : 0;
                } elseif ($userSssContributionType === 'manual') {
                    // ✅ User chose manual - use override amount
                    $finalSssEmployeeAmount = $salaryDetail->sss_contribution_override ?? 0;

                    Log::info('SSS Contribution: User chose manual computation', [
                        'user_id' => $userId,
                        'sss_override_amount' => $finalSssEmployeeAmount,
                    ]);
                } else {
                    // No SSS contribution type set for user - default to 0
                    $finalSssEmployeeAmount = 0;
                    Log::info('SSS Contribution: No SSS contribution type set for user', ['user_id' => $userId]);
                }
            } else {
                // ✅ Branch is NOT manual OR no salary detail - use system/fixed computation
                Log::info('SSS Contribution: Using branch computation (not manual)', [
                    'user_id' => $userId,
                    'branch_sss_type' => $sssType,
                ]);

                // Calculate monthly salary equivalent based on salary type
                $monthlySalaryEquivalent = 0;
                $stype = $salaryData->get($userId)['salary_type'] ?? null;
                $basicSalary = $salaryData->get($userId)['basic_salary'] ?? 0;

                if ($stype === 'monthly_fixed') {
                    $monthlySalaryEquivalent = $basicSalary;
                } elseif ($stype === 'daily_rate') {
                    if ($workedDaysPerYear > 0) {
                        $monthlySalaryEquivalent = ($basicSalary * $workedDaysPerYear) / 12;
                    } else {
                        $monthlySalaryEquivalent = $basicSalary * 22;
                    }
                } elseif ($stype === 'hourly_rate') {
                    if ($workedDaysPerYear > 0) {
                        $dailyEquivalent = $basicSalary * 8;
                        $monthlySalaryEquivalent = ($dailyEquivalent * $workedDaysPerYear) / 12;
                    } else {
                        $monthlySalaryEquivalent = $basicSalary * 8 * 22;
                    }
                } else {
                    $monthlySalaryEquivalent = $grossPay[$userId]['gross_pay'] ?? 0;
                }

                if ($sssType === 'fixed') {
                    // ✅ Fixed amount from branch
                    $finalSssEmployeeAmount = $fixedSssAmount ?? 0;
                } else {
                    // ✅ System computation (default)
                    // Get SSS table for the selected year (template)
                    $sssTableQuery = DB::table('sss_contribution_tables');
                    if ($sssTemplateYear) {
                        $sssTableQuery->where('year', $sssTemplateYear);
                    }
                    $sssTable = $sssTableQuery->get();

                    // Find SSS contribution based on monthly salary equivalent
                    $sssContribution = $sssTable->first(function ($item) use ($monthlySalaryEquivalent) {
                        return $monthlySalaryEquivalent >= $item->range_from && $monthlySalaryEquivalent <= $item->range_to;
                    });

                    $finalSssEmployeeAmount = $sssContribution ? $sssContribution->employee_total : 0;
                }
            }

            // ✅ Apply sssOption logic
            if ($sssOption === 'no') {
                // No SSS deduction
                if ($cutoffOption == 1) {
                    \App\Models\MandatesContribution::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'year' => Carbon::parse($data['start_date'])->year,
                            'month' => Carbon::parse($data['start_date'])->month,
                            'cutoff_period' => 1,
                        ],
                        [
                            'basic_pay' => $basicPay[$userId]['basic_pay'] ?? 0,
                            'gross_pay' => $grossPay[$userId]['gross_pay'] ?? 0,
                            'sss_contribution' => 0,
                            'status' => 'pending',
                        ]
                    );
                }

                $result[$userId] = [
                    'employer_total' => 0,
                    'employee_total' => 0,
                    'total_contribution' => 0,
                    'worked_days_per_year' => $workedDaysPerYear,
                ];
            } elseif ($sssOption === 'yes') {
                // ✅ Semi-monthly: divide by 2
                $employeeTotal = round($finalSssEmployeeAmount / 2, 2);
                $employerTotal = round($finalSssEmployeeAmount / 2, 2); // Assuming employer = employee
                $totalContribution = round($finalSssEmployeeAmount, 2);

                $result[$userId] = [
                    'employer_total' => $employerTotal,
                    'employee_total' => $employeeTotal,
                    'total_contribution' => $totalContribution,
                    'worked_days_per_year' => $workedDaysPerYear,
                ];
            } elseif ($sssOption === 'full') {
                // ✅ Full monthly amount
                $year = Carbon::parse($data['start_date'])->year;
                $month = Carbon::parse($data['start_date'])->month;

                if ($cutoffOption == 1) {
                    \App\Models\MandatesContribution::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'year' => $year,
                            'month' => $month,
                            'cutoff_period' => 1,
                        ],
                        [
                            'basic_pay' => $basicPay[$userId]['basic_pay'] ?? 0,
                            'gross_pay' => $grossPay[$userId]['gross_pay'] ?? 0,
                            'sss_contribution' => $finalSssEmployeeAmount,
                            'status' => 'complete',
                        ]
                    );

                    $result[$userId] = [
                        'employer_total' => $finalSssEmployeeAmount,
                        'employee_total' => $finalSssEmployeeAmount,
                        'total_contribution' => $finalSssEmployeeAmount * 2,
                        'worked_days_per_year' => $workedDaysPerYear,
                    ];
                } elseif ($cutoffOption == 2) {
                    // For cutoff 2, check if cutoff 1 exists
                    $mandate1 = \App\Models\MandatesContribution::where([
                        'user_id' => $userId,
                        'year' => $year,
                        'month' => $month,
                        'cutoff_period' => 1,
                    ])->first();

                    if (!$mandate1) {
                        // If no cutoff 1, apply full SSS on cutoff 2
                        \App\Models\MandatesContribution::updateOrCreate(
                            [
                                'user_id' => $userId,
                                'year' => $year,
                                'month' => $month,
                                'cutoff_period' => 2,
                            ],
                            [
                                'basic_pay' => $basicPay[$userId]['basic_pay'] ?? 0,
                                'gross_pay' => $grossPay[$userId]['gross_pay'] ?? 0,
                                'sss_contribution' => $finalSssEmployeeAmount,
                                'status' => 'complete',
                            ]
                        );

                        $result[$userId] = [
                            'employer_total' => $finalSssEmployeeAmount,
                            'employee_total' => $finalSssEmployeeAmount,
                            'total_contribution' => $finalSssEmployeeAmount * 2,
                            'worked_days_per_year' => $workedDaysPerYear,
                        ];
                    } else {
                        // If cutoff 1 exists, set cutoff 2 to 0 (since full amount was already deducted in cutoff 1)
                        \App\Models\MandatesContribution::updateOrCreate(
                            [
                                'user_id' => $userId,
                                'year' => $year,
                                'month' => $month,
                                'cutoff_period' => 2,
                            ],
                            [
                                'basic_pay' => $basicPay[$userId]['basic_pay'] ?? 0,
                                'gross_pay' => $grossPay[$userId]['gross_pay'] ?? 0,
                                'sss_contribution' => 0,
                                'status' => 'complete',
                            ]
                        );

                        $result[$userId] = [
                            'employer_total' => 0,
                            'employee_total' => 0,
                            'total_contribution' => 0,
                            'worked_days_per_year' => $workedDaysPerYear,
                        ];
                    }
                }
            }

            Log::info('SSS Contribution: Final result for user', [
                'user_id' => $userId,
                'result' => $result[$userId],
            ]);
        }

        Log::info('SSS Contribution: Calculation completed', [
            'total_users_processed' => count($userIds),
            'results' => $result,
        ]);

        return $result;
    }

    // Philhealth Contribution Calculation
    protected function calculatePhilhealthContribution(array $userIds, array $data, $salaryData, $philhealthOption, $cutoffOption)
    {
        // Preload user branch PhilHealth configuration and salary details
        $users = User::with(['employmentDetail.branch', 'salaryDetail'])->whereIn('id', $userIds)->get()->keyBy('id');

        // ✅ Order by max_salary DESC to get highest bracket first
        $philhealthTable = PhilhealthContribution::orderBy('max_salary', 'desc')->get();

        $result = [];
        foreach ($userIds as $userId) {

            $user = $users[$userId] ?? null;
            $branch = $user && $user->employmentDetail ? $user->employmentDetail->branch : null;
            $salaryDetail = $user ? $user->salaryDetail : null;

            $philhealthType = $branch->philhealth_contribution_type ?? 'system';
            $fixedPhilhealthAmount = $branch->fixed_philhealth_amount ?? 0;

            // ✅ Apply philhealthOption logic first
            if ($philhealthOption === 'no') {

                $result[$userId] = [
                    'employer_total' => 0,
                    'employee_total' => 0,
                    'total_contribution' => 0,
                ];
                continue;
            }

            // ✅ Calculate monthly salary equivalent
            $monthlySalaryEquivalent = 0;
            $stype = $salaryData->get($userId)['salary_type'] ?? 'monthly_fixed';
            $basicSalary = $salaryData->get($userId)['basic_salary'] ?? 0;
            $workedDaysPerYear = $salaryData->get($userId)['worked_days_per_year'] ?? 0;

            // Get worked_days_per_year from branch if not in salary data
            if ($workedDaysPerYear <= 0 && $branch) {
                if (isset($branch->worked_days_per_year)) {
                    if ($branch->worked_days_per_year === 'custom' && isset($branch->custom_worked_days)) {
                        $workedDaysPerYear = $branch->custom_worked_days;
                    } else {
                        $workedDaysPerYear = $branch->worked_days_per_year;
                    }
                }
            }

            // Convert salary to monthly equivalent
            if ($stype === 'monthly_fixed') {
                $monthlySalaryEquivalent = $basicSalary;
            } elseif ($stype === 'daily_rate') {
                if ($workedDaysPerYear > 0) {
                    $monthlySalaryEquivalent = ($basicSalary * $workedDaysPerYear) / 12;
                } else {
                    $monthlySalaryEquivalent = $basicSalary * 22;
                }
            } elseif ($stype === 'hourly_rate') {
                if ($workedDaysPerYear > 0) {
                    $dailyEquivalent = $basicSalary * 8;
                    $monthlySalaryEquivalent = ($dailyEquivalent * $workedDaysPerYear) / 12;
                } else {
                    $monthlySalaryEquivalent = $basicSalary * 8 * 22;
                }
            }

            // ✅ Determine final PhilHealth amounts
            $finalPhilhealthEmployeeAmount = 0;
            $finalPhilhealthEmployerAmount = 0;
            $finalPhilhealthTotalAmount = 0;

            // ✅ HELPER FUNCTION: Find PhilHealth bracket or use highest ceiling
            $findPhilhealthContribution = function ($salary) use ($philhealthTable) {
                // First, try to find exact bracket match
                $contribution = $philhealthTable->first(function ($item) use ($salary) {
                    return $salary >= $item->min_salary && $salary <= $item->max_salary;
                });

                // ✅ If no match found, check if salary exceeds highest ceiling
                if (!$contribution) {
                    $highestBracket = $philhealthTable->first(); // Already ordered DESC by max_salary

                    if ($salary > $highestBracket->max_salary) {
                        // ✅ Salary exceeds ceiling - use highest bracket
                        Log::info('PhilHealth: Salary exceeds ceiling, using highest bracket', [
                            'salary' => $salary,
                            'highest_ceiling' => $highestBracket->max_salary,
                            'employee_share' => $highestBracket->employee_share,
                        ]);
                        return $highestBracket;
                    }

                    // ✅ If salary is below minimum, use lowest bracket
                    $lowestBracket = $philhealthTable->sortBy('min_salary')->first();
                    if ($salary < $lowestBracket->min_salary) {

                        return $lowestBracket;
                    }
                }

                return $contribution;
            };

            // Handle manual override logic for branch = manual
            if ($philhealthType === 'manual' && $salaryDetail) {
                $userPhilhealthContributionType = $salaryDetail->philhealth_contribution ?? 'system';

                if ($userPhilhealthContributionType === 'system') {
                    // ✅ User chose system computation - use helper function
                    $philhealthContribution = $findPhilhealthContribution($monthlySalaryEquivalent);

                    if ($philhealthContribution) {
                        $finalPhilhealthEmployeeAmount = $philhealthContribution->employee_share;
                        $finalPhilhealthEmployerAmount = $philhealthContribution->employer_share;
                        $finalPhilhealthTotalAmount = $philhealthContribution->monthly_premium;
                    }

                } elseif ($userPhilhealthContributionType === 'manual') {
                    // User chose manual - use override amount
                    $finalPhilhealthEmployeeAmount = $salaryDetail->philhealth_contribution_override ?? 0;
                    $finalPhilhealthEmployerAmount = $finalPhilhealthEmployeeAmount;
                    $finalPhilhealthTotalAmount = $finalPhilhealthEmployeeAmount * 2;

                }
            } elseif ($philhealthType === 'fixed') {
                // Branch uses fixed amount
                $finalPhilhealthEmployeeAmount = $fixedPhilhealthAmount;
                $finalPhilhealthEmployerAmount = $fixedPhilhealthAmount;
                $finalPhilhealthTotalAmount = $fixedPhilhealthAmount * 2;

            } else {
                // ✅ Default: system computation - use helper function
                $philhealthContribution = $findPhilhealthContribution($monthlySalaryEquivalent);

                if ($philhealthContribution) {
                    $finalPhilhealthEmployeeAmount = $philhealthContribution->employee_share;
                    $finalPhilhealthEmployerAmount = $philhealthContribution->employer_share;
                    $finalPhilhealthTotalAmount = $philhealthContribution->monthly_premium;
                }
            }

            // ✅ Apply philhealthOption scaling
            if ($philhealthOption === 'yes') {
                // Semi-monthly: divide by 2
                $employeeTotal = round($finalPhilhealthEmployeeAmount / 2, 2);
                $employerTotal = round($finalPhilhealthEmployerAmount / 2, 2);
                $totalContribution = round($finalPhilhealthTotalAmount / 2, 2);

                $result[$userId] = [
                    'employer_total' => $employerTotal,
                    'employee_total' => $employeeTotal,
                    'total_contribution' => $totalContribution,
                ];
            } elseif ($philhealthOption === 'full') {
                // Full monthly amount
                $result[$userId] = [
                    'employer_total' => round($finalPhilhealthEmployerAmount, 2),
                    'employee_total' => round($finalPhilhealthEmployeeAmount, 2),
                    'total_contribution' => round($finalPhilhealthTotalAmount, 2),
                ];
            }

        }

        return $result;
    }

    // Pagibig Contribution Calculation
    protected function calculatePagibigContribution(array $userIds, array $data, $salaryData, $pagibigOption)
    {
        // Preload user branch Pag-IBIG contribution type and fixed amount + salary details
        $users = User::with(['employmentDetail.branch', 'salaryDetail'])->whereIn('id', $userIds)->get()->keyBy('id');

        $result = [];
        foreach ($userIds as $userId) {
            // Default to 0 if not found
            $result[$userId] = [
                'employee_total' => 0,
                'employer_total' => 0,
                'total_contribution' => 0,
            ];

            // If "no", always 0
            if ($pagibigOption === 'no') {
                continue;
            }

            $user = $users[$userId] ?? null;
            $branch = $user && $user->employmentDetail ? $user->employmentDetail->branch : null;
            $salaryDetail = $user ? $user->salaryDetail : null;
            $pagibigType = $branch && isset($branch->pagibig_contribution_type) ? $branch->pagibig_contribution_type : 'system';

            $amount = 200; // Default Pag-IBIG monthly contribution


            // ✅ FIXED: Handle manual override properly
            if ($pagibigType === 'manual' && $salaryDetail) {
                $userPagibigType = $salaryDetail->pagibig_contribution ?? 'system';

                if ($userPagibigType === 'manual') {
                    // Use override amount from salary detail
                    $amount = $salaryDetail->pagibig_contribution_override ?? 200;
                } else {
                    // User chose system - use 200
                    $amount = 200;
                }

                //  Apply full/semi-monthly logic
                if ($pagibigOption === 'full') {
                    // Full monthly amount - no division
                    $result[$userId] = [
                        'employee_total' => round($amount, 2),
                        'employer_total' => round($amount, 2),
                        'total_contribution' => round($amount, 2),
                    ];
                } else {
                    // Semi-monthly (yes) - divide by 2
                    $result[$userId] = [
                        'employee_total' => round($amount / 2, 2),
                        'employer_total' => round($amount / 2, 2),
                        'total_contribution' => round($amount / 2, 2),
                    ];
                }

                continue;
            }

            // ✅ Handle system computation
            if ($pagibigType === 'system') {
                $amount = 200;

                if ($pagibigOption === 'full') {
                    $result[$userId] = [
                        'employee_total' => round($amount, 2),
                        'employer_total' => round($amount, 2),
                        'total_contribution' => round($amount, 2),
                    ];
                } else {
                    // Semi-monthly
                    $result[$userId] = [
                        'employee_total' => round($amount / 2, 2),
                        'employer_total' => round($amount / 2, 2),
                        'total_contribution' => round($amount / 2, 2),
                    ];
                }
            }

            // ✅ Handle fixed computation
            elseif ($pagibigType === 'fixed') {
                $fixedAmount = $branch->fixed_pagibig_amount ?? 200;

                if ($pagibigOption === 'full') {
                    $result[$userId] = [
                        'employee_total' => round($fixedAmount, 2),
                        'employer_total' => round($fixedAmount, 2),
                        'total_contribution' => round($fixedAmount, 2),
                    ];
                } else {
                    // Semi-monthly
                    $result[$userId] = [
                        'employee_total' => round($fixedAmount / 2, 2),
                        'employer_total' => round($fixedAmount / 2, 2),
                        'total_contribution' => round($fixedAmount / 2, 2),
                    ];
                }
            }
        }

        return $result;
    }

    // Withholding Tax Calculation
    protected function calculateWithholdingTax(array $userIds, array $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cuttoffOption)
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
        $sss = $this->calculateSSSContribution($userIds, $data, $salaryData, $sssOption, $cuttoffOption);
        $philhealth = $this->calculatePhilhealthContribution($userIds, $data, $salaryData, $philhealthOption, $cuttoffOption);
        $pagibig = $this->calculatePagibigContribution($userIds, $data, $salaryData, $pagibigOption);

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

                    Log::info('Withholding Tax: Tax calculation steps', [
                        'user_id' => $userId,
                        'step_4_total2' => $total2,
                        'step_5_total3' => $total3,
                        'step_6_withholding_tax' => $withholdingTax,
                    ]);

                    $result[$userId] = [
                        'taxable_income' => round($total1, 2),
                        'withholding_tax' => round($withholdingTax, 2),
                    ];
                } else {
                    // No matching tax row, withholding tax is 0

                    $result[$userId] = [
                        'taxable_income' => round($total1, 2),
                        'withholding_tax' => 0,
                    ];
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
                } else {
                    Log::warning('Withholding Tax: Fixed type but no amount set', [
                        'user_id' => $userId,
                        'fixed_amount' => $branch->fixed_withholding_tax_amount ?? null,
                    ]);
                }
            } else {
                Log::info('Withholding Tax: No tax type or unsupported type', [
                    'user_id' => $userId,
                    'tax_type' => $taxType,
                ]);
            }

            Log::info('Withholding Tax: Final result', [
                'user_id' => $userId,
                'taxable_income' => $result[$userId]['taxable_income'],
                'withholding_tax' => $result[$userId]['withholding_tax'],
            ]);
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



            $result[$userId] = [
                'total_deminimis' => round($total, 2),
                'details' => $details,
            ];
        }



        return $result;
    }

    // Total Deductions
    protected function calculateTotalDeductions(array $userIds, array $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cuttoffOption)
    {
        // Get dynamic deductions (UserDeduction)
        $dynamicDeductions = $this->calculateDeduction($userIds, $data, $salaryData);

        // Get system deductions (late, undertime, absent)
        $tenantId = Auth::user()->tenant_id ?? null;
        $totals = $this->sumMinutes($tenantId, $data);
        $systemDeductions = $this->calculateDeductions($userIds, $totals, $salaryData);

        // Get SSS, PhilHealth, and Pag-IBIG contributions
        $sss = $this->calculateSSSContribution($userIds, $data, $salaryData, $sssOption, $cuttoffOption);
        $philhealth = $this->calculatePhilhealthContribution($userIds, $data, $salaryData, $philhealthOption, $cuttoffOption);
        $pagibig = $this->calculatePagibigContribution($userIds, $data, $salaryData, $pagibigOption);
        $withholdingTax = $this->calculateWithholdingTax($userIds, $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cuttoffOption);

        $result = [];
        foreach ($userIds as $id) {
            $dynamicTotal = $dynamicDeductions[$id]['deductions'] ?? 0;
            $late = $systemDeductions['lateDeductions'][$id] ?? 0;
            $undertime = $systemDeductions['undertimeDeductions'][$id] ?? 0;
            $absent = $systemDeductions['absentDeductions'][$id] ?? 0;

            $sssAmt = $sss[$id]['employee_total'] ?? 0;
            $philhealthAmt = $philhealth[$id]['employee_total'] ?? 0;
            $pagibigAmt = $pagibig[$id]['employee_total'] ?? 0;
            $withholdingTaxAmt = $withholdingTax[$id]['withholding_tax'] ?? 0;

            $total = $dynamicTotal + $late + $undertime + $absent + $sssAmt + $philhealthAmt + $pagibigAmt + $withholdingTaxAmt;

            $result[$id] = [
                'total_deductions' => round($total, 2),
                'dynamic_deductions' => round($dynamicTotal, 2),
                'late_deduction' => round($late, 2),
                'undertime_deduction' => round($undertime, 2),
                'absent_deduction' => round($absent, 2),
                'sss_deduction' => round($sssAmt, 2),
                'philhealth_deduction' => round($philhealthAmt, 2),
                'pagibig_deduction' => round($pagibigAmt, 2),
                'withholding_tax' => round($withholdingTaxAmt, 2),
                'deduction_details' => $dynamicDeductions[$id]['deduction_details'] ?? [],
            ];
        }


        return $result;
    }

    // Total Earnings
    protected function calculateTotalEarnings(array $userIds, array $data, $salaryData)
    {
        // Get overtime pay, night differential, holiday pay, deminimis, and earnings
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
        $allowance = $this->calculateAllowance($userIds, $data, $salaryData);

        $result = [];
        foreach ($userIds as $userId) {
            $overtime = $overtimePay[$userId] ?? [];
            $nightDiff = $nightDiffPay[$userId] ?? [];
            $overtimeNightDiff = $overtimeNightDiffPay[$userId] ?? [];
            $holiday = $holidayPay[$userId]['holiday_pay_amount'] ?? 0;
            $leave = $leavePay[$userId]['total_leave_pay'] ?? 0;
            $deminimisTotal = $deminimis[$userId]['total_deminimis'] ?? 0;
            $earningsTotal = $earnings[$userId]['earnings'] ?? 0;
            $earningsDetails = $earnings[$userId]['earning_details'] ?? [];
            $allowanceTotal = $allowance[$userId]['total_allowance'] ?? 0;
            $totalEarnings = $holiday + $leave + $deminimisTotal + $earningsTotal + $allowanceTotal
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
        }
        return $result;
    }

    // 13th Month Pay Calculation
    protected function calculateThirteenthMonthPay(array $userIds, array $data, $salaryData)
    {
        // Get basic pay data
        $basicPayData = $this->calculateBasicPay($userIds, $data, $salaryData);

        // Get attendance totals for deductions
        $tenantId = Auth::user()->tenant_id ?? null;
        $totals = $this->sumMinutes($tenantId, $data);

        // Get deductions (late, undertime, absent)
        $deductions = $this->calculateDeductions($userIds, $totals, $salaryData);

        // Get paid leave
        $leavePayData = $this->calculateLeavePay($userIds, $data, $salaryData);

        $result = [];
        foreach ($userIds as $userId) {
            $basicPay = $basicPayData[$userId]['basic_pay'] ?? 0;
            $late = $deductions['lateDeductions'][$userId] ?? 0;
            $undertime = $deductions['undertimeDeductions'][$userId] ?? 0;
            $absent = $deductions['absentDeductions'][$userId] ?? 0;
            $paidLeave = $leavePayData[$userId]['total_leave_pay'] ?? 0;

            $thirteenthMonth = round(($basicPay + $paidLeave - $late - $undertime - $absent) / 12, 2);

            $result[$userId] = [
                'basic_pay' => $basicPay,
                'paid_leave' => $paidLeave,
                'late_deduction' => $late,
                'undertime_deduction' => $undertime,
                'absent_deduction' => $absent,
                'thirteenth_month' => $thirteenthMonth,
            ];
        }

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

    // Delete Payroll
    public function deletePayroll($payrollId)
    {
        $payroll = Payroll::find($payrollId);
        if (!$payroll) {
            return response()->json(['message' => 'Payroll not found'], 404);
        }

        $oldData = $payroll->toArray();

        MandatesContribution::where('payroll_id', $payrollId)->delete();

        $payroll->delete();

        // Logging
        $userId = Auth::guard('web')->id();
        $globalUserId = Auth::guard('global')->id();

        UserLog::create([
            'user_id'         => $userId,
            'global_user_id'  => $globalUserId,
            'module'          => 'Payroll',
            'action'          => 'Delete',
            'description'     => 'Deleted Payroll ID "' . $payrollId . '"',
            'affected_id'     => $payrollId,
            'old_data'        => json_encode($oldData),
            'new_data'        => null,
        ]);

        return response()->json(['message' => 'Payroll deleted successfully']);
    }

    // Update/Edit Payroll
    public function updatePayroll(Request $request, $id)
    {
        // Validation
        $validated = $request->validate([
            // Basic fields
            'payroll_type' => 'nullable|string|max:255',
            'payroll_period' => 'nullable|string|max:255',
            'payroll_period_start' => 'nullable|date',
            'payroll_period_end' => 'nullable|date',
            'total_worked_minutes' => 'nullable',
            'total_late_minutes' => 'nullable|numeric',
            'total_undertime_minutes' => 'nullable|numeric',
            'total_overtime_minutes' => 'nullable|numeric',
            'total_night_differential_minutes' => 'nullable|numeric',
            'total_overtime_night_differential_minutes' => 'nullable|numeric',

            'holiday_pay' => 'nullable|numeric',
            'leave_pay' => 'nullable|numeric',
            'overtime_pay' => 'nullable|numeric',
            'night_differential_pay' => 'nullable|numeric',
            'overtime_night_differential_pay' => 'nullable|numeric',
            'overtime_restday_pay' => 'nullable|numeric',
            'late_deduction' => 'nullable|numeric',
            'undertime_deduction' => 'nullable|numeric',
            'absent_deduction' => 'nullable|numeric',

            'earnings' => 'nullable|array',
            'deductions' => 'nullable|array',
            'deminimis_amounts' => 'nullable|array',

            'sss_contribution' => 'nullable|numeric',
            'philhealth_contribution' => 'nullable|numeric',
            'pagibig_contribution' => 'nullable|numeric',
            'withholding_tax' => 'nullable|numeric',
            'basic_pay' => 'nullable|numeric',
            'gross_pay' => 'nullable|numeric',
            'net_salary' => 'nullable|numeric',
            'payment_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        $payroll = Payroll::findOrFail($id);

        // Helper function to convert time string to minutes
        $convertToMinutes = function ($timeString) {
            if (is_numeric($timeString)) {
                return $timeString; // Already in minutes
            }

            $timeString = strtolower(trim($timeString));
            $totalMinutes = 0;

            // Match patterns like "79hrs 30mins", "79hr 30min", "79 hrs 30 mins", etc.
            if (preg_match('/(\d+)\s*hrs?\s*(\d+)\s*mins?/i', $timeString, $matches)) {
                $hours = (int)$matches[1];
                $minutes = (int)$matches[2];
                $totalMinutes = ($hours * 60) + $minutes;
            }
            // Match patterns like "79hrs", "79hr", "79 hrs", etc. (hours only)
            elseif (preg_match('/(\d+)\s*hrs?/i', $timeString, $matches)) {
                $hours = (int)$matches[1];
                $totalMinutes = $hours * 60;
            }
            // Match patterns like "30mins", "30min", "30 mins", etc. (minutes only)
            elseif (preg_match('/(\d+)\s*mins?/i', $timeString, $matches)) {
                $totalMinutes = (int)$matches[1];
            }
            // If it's just a number, assume it's already in minutes
            elseif (is_numeric($timeString)) {
                $totalMinutes = (int)$timeString;
            }

            return $totalMinutes;
        };

        // Assign fields
        $payroll->payroll_type = $request->input('payroll_type');
        $payroll->payroll_period = $request->input('payroll_period');
        $payroll->payroll_period_start = $request->input('payroll_period_start');
        $payroll->payroll_period_end = $request->input('payroll_period_end');

        // Convert total_worked_minutes to minutes if it's in time format
        $workedMinutesInput = $request->input('total_worked_minutes');
        $payroll->total_worked_minutes = $convertToMinutes($workedMinutesInput);

        $payroll->total_late_minutes = $request->input('total_late_minutes');
        $payroll->total_undertime_minutes = $request->input('total_undertime_minutes');
        $payroll->total_overtime_minutes = $request->input('total_overtime_minutes');
        $payroll->total_night_differential_minutes = $request->input('total_night_differential_minutes');
        $payroll->total_overtime_night_diff_minutes = $request->input('total_overtime_night_differential_minutes');

        $payroll->holiday_pay = $request->input('holiday_pay');
        $payroll->leave_pay = $request->input('leave_pay');
        $payroll->overtime_pay = $request->input('overtime_pay');
        $payroll->night_differential_pay = $request->input('night_differential_pay');
        $payroll->overtime_night_diff_pay = $request->input('overtime_night_differential_pay');
        $payroll->overtime_restday_pay = $request->input('overtime_restday_pay');
        $payroll->late_deduction = $request->input('late_deduction');
        $payroll->undertime_deduction = $request->input('undertime_deduction');
        $payroll->absent_deduction = $request->input('absent_deduction');

        $payroll->total_earnings = $request->input('total_earnings');
        $payroll->total_deductions = $request->input('total_deductions');
        $payroll->sss_contribution = $request->input('sss_contribution');
        $payroll->philhealth_contribution = $request->input('philhealth_contribution');
        $payroll->pagibig_contribution = $request->input('pagibig_contribution');
        $payroll->withholding_tax = $request->input('withholding_tax');
        $payroll->basic_pay = $request->input('basic_pay');
        $payroll->gross_pay = $request->input('gross_pay');
        $payroll->net_salary = $request->input('net_salary');
        $payroll->payment_date = $request->input('payment_date');

        // Handle JSON fields (earnings, deductions, deminimis)
        if ($request->has('earnings')) {
            $oldEarnings = [];
            if (is_array($payroll->earnings)) {
                $oldEarnings = $payroll->earnings;
            } elseif (is_string($payroll->earnings)) {
                $oldEarnings = json_decode((string)$payroll->earnings, true) ?? [];
            }
            $updates = $request->input('earnings');

            $merged = [];
            foreach ($oldEarnings as $old) {
                $id = $old['earning_type_id'];
                if (isset($updates[$id])) {
                    // Merge with updates, keeping all fields
                    $old = array_merge($old, $updates[$id]);
                }
                $merged[] = $old;
            }
            $payroll->earnings = json_encode($merged);
        }

        // DEDUCTIONS: keep all fields, not just applied_amount
        if ($request->has('deductions')) {
            $oldDeductions = [];
            if (is_array($payroll->deductions)) {
                $oldDeductions = $payroll->deductions;
            } elseif (is_string($payroll->deductions)) {
                $oldDeductions = json_decode((string)$payroll->deductions, true) ?? [];
            }
            $updates = $request->input('deductions');

            $merged = [];
            foreach ($oldDeductions as $old) {
                $id = $old['deduction_type_id'];
                if (isset($updates[$id])) {

                    $old = array_merge($old, $updates[$id]);
                }
                $merged[] = $old;
            }
            $payroll->deductions = json_encode($merged);
        }

        // DEMINIMIS: deminimis_amounts[deminimis_benefit_id] = amount
        if ($request->has('deminimis_amounts')) {
            $deminimisData = [];
            foreach ($request->input('deminimis_amounts') as $benefit_id => $amount) {
                $deminimisData[] = [
                    'deminimis_benefit_id' => $benefit_id,
                    'amount' => $amount,
                ];
            }
            $payroll->deminimis = json_encode($deminimisData);
        }

        $payroll->save();

        return response()->json([
            'success' => true,
            'message' => 'Payroll updated successfully!',
        ]);
    }

    // Bulk Delete Payroll
    public function bulkDeletePayroll(Request $request)
    {
        $payrollIds = $request->input('payroll_ids', []);
        if (empty($payrollIds)) {
            return response()->json(['message' => 'No payroll IDs provided'], 400);
        }

        // Proceed with bulk deletion
        Payroll::whereIn('id', $payrollIds)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payroll records deleted successfully!',
        ]);
    }

    // Bulk Generate Payroll Payslips
    public function bulkGeneratePayslips(Request $request)
    {
        $payrollIds = $request->input('payroll_ids', []);
        if (empty($payrollIds)) {
            return response()->json(['message' => 'No payroll IDs provided'], 400);
        }

        // Update all selected payrolls' status to "Payslip"
        Payroll::whereIn('id', $payrollIds)->update(['status' => 'Paid']);

        return response()->json([
            'success' => true,
            'message' => 'Selected payrolls marked as Paid!',
        ]);
    }

    // Bulk Generate Bank Reports
    public function bulkGenerateBankReports(Request $request)
    {
        $payrollIds = $request->input('payroll_ids', []);
        if (empty($payrollIds)) {
            return response()->json(['message' => 'No payroll IDs provided'], 400);
        }

        // Get payrolls with user_id and net_salary
        $payrolls = Payroll::whereIn('id', $payrollIds)->get();

        // Get user bank details
        $userIds = $payrolls->pluck('user_id')->unique()->toArray();
        $bankDetails = EmployeeBankDetail::whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        // Prepare CSV data
        $csvHeader = ['Account Name', 'Account Number', 'Amount', 'Remarks'];
        $csvRows = [];
        foreach ($payrolls as $payroll) {
            $bank = $bankDetails->get($payroll->user_id);
            if (!$bank) continue;

            $csvRows[] = [
                $bank->account_name,
                $bank->account_number,
                $payroll->net_salary,
                '', // Remarks blank
            ];
        }

        // Generate CSV string
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $csvHeader);
        foreach ($csvRows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        // Return CSV as download response
        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bank_report.csv"',
        ]);
    }

    /**
     * Export Payroll as Excel (Updated with time totals)
     */
    public function exportExcel(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(24);

        if (!in_array('Export', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to export.'
            ], 403);
        }

        try {
            // Get filters from request
            $filters = [
                'branch' => $request->input('branch'),
                'department' => $request->input('department'),
                'designation' => $request->input('designation'),
                'dateRange' => $request->input('dateRange')
            ];

            $exporter = new PayrollExport($authUser, $filters);
            $payrolls = $exporter->getData();

            if ($payrolls->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No payroll records found for export.'
                ], 404);
            }

            $summaryTotals = $exporter->getSummaryTotals($payrolls);
            $insights = $exporter->getPayrollInsights($payrolls);

            // Create new Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('Payroll System')
                ->setTitle('Payroll Report')
                ->setSubject('Payroll Export')
                ->setDescription('Payroll records export');

            // Create professional header section
            $sheet->setCellValue('A1', 'PAYROLL REGISTER - DETAILED REPORT');
            $sheet->mergeCells('A1:AO1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18)->setColor(new Color(Color::COLOR_WHITE));
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2E4057');

            // Add company/report information
            $sheet->setCellValue('A2', 'Generated: ' . now()->format('F d, Y h:i A'));
            $sheet->setCellValue('AK2', 'Report Type: Comprehensive Payroll Analysis');
            $sheet->mergeCells('A2:AJ2');
            $sheet->mergeCells('AK2:AO2');
            $sheet->getStyle('A2:AO2')->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('AK2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Add filter information with better formatting
            $currentRow = 3;
            if (!empty($filters['dateRange']) || !empty($filters['branch']) || !empty($filters['department'])) {
                $sheet->setCellValue('A' . $currentRow, 'FILTER CRITERIA:');
                $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(11);
                $currentRow++;

                if (!empty($filters['dateRange'])) {
                    $sheet->setCellValue('A' . $currentRow, 'Period: ' . $filters['dateRange']);
                    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
                    $currentRow++;
                }
                if (!empty($filters['branch'])) {
                    $sheet->setCellValue('A' . $currentRow, 'Branch: ' . $filters['branch']);
                    $currentRow++;
                }
                if (!empty($filters['department'])) {
                    $sheet->setCellValue('A' . $currentRow, 'Department: ' . $filters['department']);
                    $currentRow++;
                }
                $currentRow++; // Extra spacing
            }

            // Add professional section headers with grouped columns
            $headerRow = $currentRow + 1;
            $headers = $exporter->getHeaders();

            // Create section headers for better organization
            $sectionHeaders = [
                'A' => ['text' => 'EMPLOYEE INFORMATION', 'range' => 'A' . $headerRow . ':J' . $headerRow, 'color' => 'FF34495E'],
                'K' => ['text' => 'TIME TRACKING', 'range' => 'K' . $headerRow . ':O' . $headerRow, 'color' => 'FF16A085'],
                'P' => ['text' => 'EARNINGS BREAKDOWN', 'range' => 'P' . $headerRow . ':Z' . $headerRow, 'color' => 'FF27AE60'],
                'AA' => ['text' => 'DEDUCTIONS BREAKDOWN', 'range' => 'AA' . $headerRow . ':AJ' . $headerRow, 'color' => 'FFE74C3C'],
                'AK' => ['text' => 'FINAL AMOUNTS', 'range' => 'AK' . $headerRow . ':AO' . $headerRow, 'color' => 'FF8E44AD']
            ];

            foreach ($sectionHeaders as $column => $section) {
                $sheet->setCellValue($column . $headerRow, $section['text']);
                $sheet->mergeCells($section['range']);
                $sheet->getStyle($section['range'])->getFont()->setBold(true)->setSize(11)->setColor(new Color(Color::COLOR_WHITE));
                $sheet->getStyle($section['range'])->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($section['color']);
                $sheet->getStyle($section['range'])->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // Add detailed column headers
            $detailHeaderRow = $headerRow + 1;
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $detailHeaderRow, $header);
                $sheet->getStyle($col . $detailHeaderRow)->getFont()->setBold(true)->setSize(9);
                $sheet->getStyle($col . $detailHeaderRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF8F9FA');
                $sheet->getStyle($col . $detailHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($col . $detailHeaderRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($col . $detailHeaderRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $col++;
            }

            // Add data with professional formatting
            $row = $detailHeaderRow + 1;
            foreach ($payrolls as $index => $payroll) {
                $data = $exporter->formatRow($payroll, $index);
                $col = 'A';
                foreach ($data as $colIndex => $value) {
                    $sheet->setCellValue($col . $row, $value);

                    // Apply alternating row colors for better readability
                    if ($index % 2 == 0) {
                        $sheet->getStyle($col . $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFAFBFC');
                    }

                    // Center align number column, date columns, and time columns
                    if ($col == 'A' || in_array($colIndex, [8, 9, 10, 11, 12, 13, 14, 37, 38, 39])) {
                        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // Right align all monetary amount columns with number formatting
                    if (in_array($colIndex, [15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36])) {
                        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                    }

                    // Add borders for professional appearance
                    $sheet->getStyle($col . $row)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new Color('FFE0E0E0'));

                    $col++;
                }
                $row++;
            }

            // Add professional summary section
            $summaryRow = $row + 2; // Extra spacing

            // Summary header with enhanced styling
            $sheet->setCellValue('A' . $summaryRow, 'PAYROLL SUMMARY TOTALS');
            $sheet->mergeCells('A' . $summaryRow . ':AO' . $summaryRow);
            $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(14)->setColor(new Color(Color::COLOR_WHITE));
            $sheet->getStyle('A' . $summaryRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2C3E50');
            $sheet->getStyle('A' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Add employee count summary
            $summaryRow++;
            $sheet->setCellValue('A' . $summaryRow, 'Total Employees Processed:');
            $sheet->setCellValue('C' . $summaryRow, $summaryTotals['total_employees']);
            $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true);
            $sheet->getStyle('C' . $summaryRow)->getFont()->setBold(true)->getColor()->setARGB('FF2980B9');
            $sheet->getStyle('C' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $summaryRow++;

            // Map columns K through AJ for detailed summary
            $summaryColumns = [
                'K' => $summaryTotals['total_worked_hours_formatted'],
                'L' => $summaryTotals['total_late_hours_formatted'],
                'M' => $summaryTotals['total_undertime_hours_formatted'],
                'N' => $summaryTotals['total_overtime_hours_formatted'] ?? '0 hrs 0 mins',
                'O' => $summaryTotals['total_night_diff_hours_formatted'] ?? '0 hrs 0 mins',
                'P' => number_format($summaryTotals['total_basic_pay'], 2),
                'Q' => number_format($summaryTotals['total_holiday_pay'], 2),
                'R' => number_format($summaryTotals['total_overtime_pay'], 2),
                'S' => number_format($summaryTotals['total_night_differential_pay'], 2),
                'T' => number_format($summaryTotals['total_restday_pay'], 2),
                'U' => number_format($summaryTotals['total_overtime_restday_pay'], 2),
                'V' => number_format($summaryTotals['total_leave_pay'], 2),
                'W' => number_format($summaryTotals['total_allowances'], 2),
                'X' => number_format($summaryTotals['total_other_earnings'], 2),
                'Y' => number_format($summaryTotals['total_gross_pay'], 2),
                'Z' => number_format($summaryTotals['total_earnings'], 2),
                'AA' => number_format($summaryTotals['total_late_deduction'], 2),
                'AB' => number_format($summaryTotals['total_undertime_deduction'], 2),
                'AC' => number_format($summaryTotals['total_absent_deduction'], 2),
                'AD' => number_format($summaryTotals['total_sss_contribution'], 2),
                'AE' => number_format($summaryTotals['total_philhealth_contribution'], 2),
                'AF' => number_format($summaryTotals['total_pagibig_contribution'], 2),
                'AG' => number_format($summaryTotals['total_withholding_tax'], 2),
                'AH' => number_format($summaryTotals['total_loan_deductions'], 2),
                'AI' => number_format($summaryTotals['total_other_deductions'], 2),
                'AJ' => number_format($summaryTotals['total_deductions'], 2),
                'AK' => number_format($summaryTotals['total_taxable_income'], 2),
                'AL' => number_format($summaryTotals['total_net_pay'], 2)
            ];

            foreach ($summaryColumns as $column => $value) {
                $sheet->setCellValue($column . $summaryRow, $value);
            }

            $sheet->getStyle('K' . $summaryRow . ':AL' . $summaryRow)->getFont()->setBold(true);
            $sheet->getStyle('K' . $summaryRow . ':O' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('P' . $summaryRow . ':AL' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('K' . $summaryRow . ':AL' . $summaryRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE7E6E6');

            // Add employee count
            $countRow = $summaryRow + 1;
            $sheet->setCellValue('A' . $countRow, 'Total Employees: ' . $summaryTotals['total_employees']);
            $sheet->mergeCells('A' . $countRow . ':AO' . $countRow);
            $sheet->getStyle('A' . $countRow)->getFont()->setBold(true);

            // Auto-size columns
            foreach (range('A', 'Z') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            foreach (['AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO'] as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Add borders to data area
            $lastRow = $row - 1;
            $sheet->getStyle('A' . $headerRow . ':AO' . $lastRow)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            // Add borders to summary
            $sheet->getStyle('A' . $summaryRow . ':AL' . $summaryRow)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            // Create Professional Accounting Summary Sheet
            $accountingSheet = $spreadsheet->createSheet(1);
            $accountingSheet->setTitle('Financial Analysis');

            // Professional header
            $accountingSheet->setCellValue('A1', 'PAYROLL FINANCIAL ANALYSIS & COST BREAKDOWN');
            $accountingSheet->mergeCells('A1:F1');
            $accountingSheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new Color(Color::COLOR_WHITE));
            $accountingSheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2E4057');
            $accountingSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Add report metadata
            $accountingSheet->setCellValue('A2', 'Generated: ' . now()->format('F d, Y h:i A'));
            $accountingSheet->setCellValue('D2', 'Total Employees: ' . $summaryTotals['total_employees']);
            $accountingSheet->getStyle('A2:F2')->getFont()->setBold(true);
            $accountingSheet->mergeCells('A2:C2');
            $accountingSheet->mergeCells('D2:F2');

            // Enhanced Earnings Analysis
            $accountingSheet->setCellValue('A4', 'EARNINGS ANALYSIS');
            $accountingSheet->mergeCells('A4:C4');
            $accountingSheet->getStyle('A4')->getFont()->setBold(true)->setSize(12)->setColor(new Color(Color::COLOR_WHITE));
            $accountingSheet->getStyle('A4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF27AE60');
            $accountingSheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $earningsData = [
                ['EARNINGS CATEGORY', 'AMOUNT (PHP)', 'PERCENTAGE', 'COST PER EMPLOYEE'],
                ['Basic Pay', $summaryTotals['total_basic_pay'], round(($summaryTotals['total_basic_pay'] / max($summaryTotals['total_gross_pay'], 1)) * 100, 2) . '%', round($summaryTotals['total_basic_pay'] / max($summaryTotals['total_employees'], 1), 2)],
                ['Holiday Pay', $summaryTotals['total_holiday_pay'], round(($summaryTotals['total_holiday_pay'] / max($summaryTotals['total_gross_pay'], 1)) * 100, 2) . '%', round($summaryTotals['total_holiday_pay'] / max($summaryTotals['total_employees'], 1), 2)],
                ['Overtime Pay', $summaryTotals['total_overtime_pay'], round(($summaryTotals['total_overtime_pay'] / max($summaryTotals['total_gross_pay'], 1)) * 100, 2) . '%', round($summaryTotals['total_overtime_pay'] / max($summaryTotals['total_employees'], 1), 2)],
                ['Night Differential', $summaryTotals['total_night_differential_pay'], round(($summaryTotals['total_night_differential_pay'] / max($summaryTotals['total_gross_pay'], 1)) * 100, 2) . '%', round($summaryTotals['total_night_differential_pay'] / max($summaryTotals['total_employees'], 1), 2)],
                ['Rest Day Pay', $summaryTotals['total_restday_pay'], round(($summaryTotals['total_restday_pay'] / max($summaryTotals['total_gross_pay'], 1)) * 100, 2) . '%', round($summaryTotals['total_restday_pay'] / max($summaryTotals['total_employees'], 1), 2)],
                ['Overtime Rest Day', $summaryTotals['total_overtime_restday_pay'], round(($summaryTotals['total_overtime_restday_pay'] / max($summaryTotals['total_gross_pay'], 1)) * 100, 2) . '%', round($summaryTotals['total_overtime_restday_pay'] / max($summaryTotals['total_employees'], 1), 2)],
                ['Leave Pay', $summaryTotals['total_leave_pay'], round(($summaryTotals['total_leave_pay'] / max($summaryTotals['total_gross_pay'], 1)) * 100, 2) . '%', round($summaryTotals['total_leave_pay'] / max($summaryTotals['total_employees'], 1), 2)],
                ['Allowances', $summaryTotals['total_allowances'], round(($summaryTotals['total_allowances'] / max($summaryTotals['total_gross_pay'], 1)) * 100, 2) . '%', round($summaryTotals['total_allowances'] / max($summaryTotals['total_employees'], 1), 2)],
                ['Other Earnings', $summaryTotals['total_other_earnings'], round(($summaryTotals['total_other_earnings'] / max($summaryTotals['total_gross_pay'], 1)) * 100, 2) . '%', round($summaryTotals['total_other_earnings'] / max($summaryTotals['total_employees'], 1), 2)],
                ['TOTAL GROSS PAY', $summaryTotals['total_gross_pay'], '100.00%', round($summaryTotals['total_gross_pay'] / max($summaryTotals['total_employees'], 1), 2)],
            ];

            $row = 4;
            foreach ($earningsData as $rowData) {
                $col = 'A';
                foreach ($rowData as $value) {
                    $accountingSheet->setCellValue($col . $row, $value);
                    if ($row == 4) {
                        $accountingSheet->getStyle($col . $row)->getFont()->setBold(true);
                    }
                    if ($col == 'B' && $row > 4) {
                        $accountingSheet->setCellValue($col . $row, number_format($value, 2));
                    }
                    $col++;
                }
                $row++;
            }

            $deductionsStartRow = $row + 2;
            $accountingSheet->setCellValue('A' . $deductionsStartRow, 'DEDUCTIONS BREAKDOWN');
            $accountingSheet->getStyle('A' . $deductionsStartRow)->getFont()->setBold(true);

            $deductionsData = [
                ['Category', 'Amount', 'Percentage'],
                ['Late Deductions', $summaryTotals['total_late_deduction'], round(($summaryTotals['total_late_deduction'] / $summaryTotals['total_deductions']) * 100, 2) . '%'],
                ['Undertime Deductions', $summaryTotals['total_undertime_deduction'], round(($summaryTotals['total_undertime_deduction'] / $summaryTotals['total_deductions']) * 100, 2) . '%'],
                ['Absent Deductions', $summaryTotals['total_absent_deduction'], round(($summaryTotals['total_absent_deduction'] / $summaryTotals['total_deductions']) * 100, 2) . '%'],
                ['SSS Contributions', $summaryTotals['total_sss_contribution'], round(($summaryTotals['total_sss_contribution'] / $summaryTotals['total_deductions']) * 100, 2) . '%'],
                ['PhilHealth Contributions', $summaryTotals['total_philhealth_contribution'], round(($summaryTotals['total_philhealth_contribution'] / $summaryTotals['total_deductions']) * 100, 2) . '%'],
                ['Pag-IBIG Contributions', $summaryTotals['total_pagibig_contribution'], round(($summaryTotals['total_pagibig_contribution'] / $summaryTotals['total_deductions']) * 100, 2) . '%'],
                ['Withholding Tax', $summaryTotals['total_withholding_tax'], round(($summaryTotals['total_withholding_tax'] / $summaryTotals['total_deductions']) * 100, 2) . '%'],
                ['Loan Deductions', $summaryTotals['total_loan_deductions'], round(($summaryTotals['total_loan_deductions'] / $summaryTotals['total_deductions']) * 100, 2) . '%'],
                ['Other Deductions', $summaryTotals['total_other_deductions'], round(($summaryTotals['total_other_deductions'] / $summaryTotals['total_deductions']) * 100, 2) . '%'],
                ['TOTAL DEDUCTIONS', $summaryTotals['total_deductions'], '100.00%'],
            ];

            $row = $deductionsStartRow + 1;
            foreach ($deductionsData as $rowData) {
                $col = 'A';
                foreach ($rowData as $value) {
                    $accountingSheet->setCellValue($col . $row, $value);
                    if ($row == $deductionsStartRow + 1) {
                        $accountingSheet->getStyle($col . $row)->getFont()->setBold(true);
                    }
                    if ($col == 'B' && $row > $deductionsStartRow + 1) {
                        $accountingSheet->setCellValue($col . $row, number_format($value, 2));
                    }
                    $col++;
                }
                $row++;
            }

            // Auto-size columns for accounting sheet
            foreach (range('A', 'D') as $col) {
                $accountingSheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Create Insights & Analytics Sheet
            $insightsSheet = $spreadsheet->createSheet(2);
            $insightsSheet->setTitle('HR Analytics');

            // Professional header for insights
            $insightsSheet->setCellValue('A1', 'PAYROLL INSIGHTS & HR ANALYTICS');
            $insightsSheet->mergeCells('A1:D1');
            $insightsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new Color(Color::COLOR_WHITE));
            $insightsSheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF8E44AD');
            $insightsSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Add insights data
            $insightsData = [
                ['METRIC', 'VALUE', 'BENCHMARK', 'STATUS'],
                ['Average Salary per Employee', 'PHP ' . number_format($insights['average_salary_per_employee'], 2), 'Industry Avg', $insights['average_salary_per_employee'] > 25000 ? 'Above Average' : 'Below Average'],
                ['Average Gross per Employee', 'PHP ' . number_format($insights['average_gross_per_employee'], 2), 'Target: 30K+', $insights['average_gross_per_employee'] > 30000 ? 'Target Met' : 'Below Target'],
                ['Overtime Percentage', $insights['overtime_percentage'] . '%', 'Target: <15%', $insights['overtime_percentage'] < 15 ? 'Optimal' : 'High'],
                ['Deduction Rate', $insights['deduction_rate'] . '%', 'Typical: 25-35%', ($insights['deduction_rate'] >= 25 && $insights['deduction_rate'] <= 35) ? 'Normal' : 'Review Needed'],
                ['Take-home Rate', $insights['take_home_rate'] . '%', 'Target: 65%+', $insights['take_home_rate'] >= 65 ? 'Good' : 'Low'],
                ['Government Contributions', 'PHP ' . number_format($insights['government_contributions_total'], 2), 'Compliance', 'Compliant'],
                ['Average Hours per Employee', $insights['average_hours_per_employee'] . ' hrs', 'Standard: 160-180', ($insights['average_hours_per_employee'] >= 160 && $insights['average_hours_per_employee'] <= 180) ? 'Standard' : 'Review'],
                ['Productivity Score', $insights['productivity_score'] . '%', 'Target: 85%+', $insights['productivity_score'] >= 85 ? 'Excellent' : 'Improvement Needed'],
                ['Basic Pay Percentage', $insights['basic_pay_percentage'] . '%', 'Typical: 70-80%', 'Normal Range'],
                ['Labor Cost Analysis', 'PHP ' . number_format($insights['total_labor_cost'], 2), 'Total Cost', 'Complete']
            ];

            $row = 3;
            foreach ($insightsData as $rowIndex => $rowData) {
                $col = 'A';
                foreach ($rowData as $value) {
                    $insightsSheet->setCellValue($col . $row, $value);
                    if ($rowIndex == 0) {
                        $insightsSheet->getStyle($col . $row)->getFont()->setBold(true);
                        $insightsSheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF34495E');
                        $insightsSheet->getStyle($col . $row)->getFont()->setColor(new Color(Color::COLOR_WHITE));
                    }
                    $insightsSheet->getStyle($col . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $col++;
                }
                $row++;
            }

            // Auto-size columns for insights sheet
            foreach (range('A', 'D') as $col) {
                $insightsSheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Create Detailed Earnings & Benefits Sheet
            $earningsSheet = $spreadsheet->createSheet(3);
            $earningsSheet->setTitle('Detailed Earnings & Benefits');

            // Header
            $earningsSheet->setCellValue('A1', 'DETAILED EARNINGS & BENEFITS BREAKDOWN');
            $earningsSheet->mergeCells('A1:O1');
            $earningsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new Color(Color::COLOR_WHITE));
            $earningsSheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF39C12');
            $earningsSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 3;

            foreach ($payrolls as $payroll) {
                $earnings = is_string($payroll->earnings) ? json_decode($payroll->earnings, true) : ($payroll->earnings ?? []);
                $deminimis = is_string($payroll->deminimis) ? json_decode($payroll->deminimis, true) : ($payroll->deminimis ?? []);

                // Enhance deminimis data with benefit names if missing
                if (!empty($deminimis)) {
                    $deminimisBenefits = \App\Models\DeminimisBenefits::all()->keyBy('id');
                    foreach ($deminimis as &$benefit) {
                        if (empty($benefit['benefit_name']) && !empty($benefit['deminimis_benefit_id'])) {
                            $deminimisBenefit = $deminimisBenefits->get($benefit['deminimis_benefit_id']);
                            if ($deminimisBenefit) {
                                $benefit['benefit_name'] = $deminimisBenefit->name;
                                $benefit['description'] = $benefit['description'] ?? 'No description available';
                            }
                        }
                    }
                }

                // Only show employees with additional earnings or benefits
                if (!empty($earnings) || !empty($deminimis)) {
                    $user = $payroll->user;
                    $personalInfo = $user->personalInformation;

                    // Employee header
                    $earningsSheet->setCellValue('A' . $row, $personalInfo->last_name . ', ' . $personalInfo->first_name . ' (' . ($user->employmentDetail->employee_id ?? 'N/A') . ')');
                    $earningsSheet->mergeCells('A' . $row . ':O' . $row);
                    $earningsSheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
                    $earningsSheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8F4F8');
                    $row++;

                    // Earnings Section
                    if (!empty($earnings)) {
                        $earningsSheet->setCellValue('A' . $row, 'Additional Earnings:');
                        $earningsSheet->getStyle('A' . $row)->getFont()->setBold(true)->setColor(new Color('FF27AE60'));
                        $row++;

                        // Earnings headers
                        $headers = ['Earning Type', 'Type ID', 'Method', 'Default Amount', 'Override Amount', 'Applied Amount', 'Taxable', 'Frequency', 'Status'];
                        $col = 'A';
                        foreach ($headers as $header) {
                            $earningsSheet->setCellValue($col . $row, $header);
                            $earningsSheet->getStyle($col . $row)->getFont()->setBold(true);
                            $earningsSheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FA');
                            $col++;
                        }
                        $row++;

                        // Earnings data
                        foreach ($earnings as $earning) {
                            $earningsSheet->setCellValue('A' . $row, $earning['earning_type_name'] ?? 'Unknown');
                            $earningsSheet->setCellValue('B' . $row, $earning['earning_type_id'] ?? 'N/A');
                            $earningsSheet->setCellValue('C' . $row, ucfirst($earning['calculation_method'] ?? 'N/A'));
                            $earningsSheet->setCellValue('D' . $row, 'PHP ' . number_format($earning['default_amount'] ?? 0, 2));
                            $earningsSheet->setCellValue('E' . $row, 'PHP ' . number_format($earning['user_amount_override'] ?? 0, 2));
                            $earningsSheet->setCellValue('F' . $row, 'PHP ' . number_format($earning['applied_amount'] ?? 0, 2));
                            $earningsSheet->setCellValue('G' . $row, ($earning['is_taxable'] ?? 1) ? 'Yes' : 'No');
                            $earningsSheet->setCellValue('H' . $row, ucfirst($earning['frequency'] ?? 'N/A'));
                            $earningsSheet->setCellValue('I' . $row, ucfirst($earning['status'] ?? 'active'));

                            // Highlight applied amount
                            $earningsSheet->getStyle('F' . $row)->getFont()->setBold(true);
                            $earningsSheet->getStyle('F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8F5E8');

                            $row++;
                        }
                        $row++; // Space
                    }

                    // De minimis Section
                    if (!empty($deminimis)) {
                        $earningsSheet->setCellValue('A' . $row, 'De Minimis Benefits:');
                        $earningsSheet->getStyle('A' . $row)->getFont()->setBold(true)->setColor(new Color('FF9B59B6'));
                        $row++;

                        // De minimis headers
                        $headers = ['Benefit Name', 'Amount', 'Description', 'Type', 'Status'];
                        $col = 'A';
                        foreach ($headers as $header) {
                            $earningsSheet->setCellValue($col . $row, $header);
                            $earningsSheet->getStyle($col . $row)->getFont()->setBold(true);
                            $earningsSheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3E5F5');
                            $col++;
                        }
                        $row++;

                        // De minimis data
                        foreach ($deminimis as $benefit) {
                            $earningsSheet->setCellValue('A' . $row, $benefit['benefit_name'] ?? $benefit['name'] ?? 'Unknown');
                            $earningsSheet->setCellValue('B' . $row, 'PHP ' . number_format($benefit['amount'] ?? 0, 2));
                            $earningsSheet->setCellValue('C' . $row, $benefit['description'] ?? 'N/A');
                            $earningsSheet->setCellValue('D' . $row, $benefit['type'] ?? 'N/A');
                            $earningsSheet->setCellValue('E' . $row, $benefit['status'] ?? 'active');

                            // Highlight amount
                            $earningsSheet->getStyle('B' . $row)->getFont()->setBold(true);
                            $earningsSheet->getStyle('B' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3E5F5');

                            $row++;
                        }
                        $row++; // Space
                    }

                    // Allowances Section
                    $allowances = is_string($payroll->allowance) ? json_decode($payroll->allowance, true) : ($payroll->allowance ?? []);
                    if (!empty($allowances)) {
                        $earningsSheet->setCellValue('A' . $row, 'Allowances Breakdown:');
                        $earningsSheet->getStyle('A' . $row)->getFont()->setBold(true)->setColor(new Color('FF008080'));
                        $row++;

                        // Allowances headers
                        $allowanceHeaders = ['Allowance Type', 'Amount'];
                        $col = 'A';
                        foreach ($allowanceHeaders as $header) {
                            $earningsSheet->setCellValue($col . $row, $header);
                            $earningsSheet->getStyle($col . $row)->getFont()->setBold(true);
                            $earningsSheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FA');
                            $col++;
                        }
                        $row++;

                        // Allowances data
                        foreach ($allowances as $allowance) {
                            $earningsSheet->setCellValue('A' . $row, $allowance['type'] ?? 'Unknown');
                            $earningsSheet->setCellValue('B' . $row, 'PHP ' . number_format($allowance['amount'] ?? 0, 2));

                            // Highlight amount
                            $earningsSheet->getStyle('B' . $row)->getFont()->setBold(true);
                            $earningsSheet->getStyle('B' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0F2F1');

                            $row++;
                        }
                        $row++; // Space
                    }

                    // Loan Deductions Section  
                    $loanDeductions = is_string($payroll->loan_deductions) ? json_decode($payroll->loan_deductions, true) : ($payroll->loan_deductions ?? []);
                    if (!empty($loanDeductions)) {
                        $earningsSheet->setCellValue('A' . $row, 'Loan Deductions:');
                        $earningsSheet->getStyle('A' . $row)->getFont()->setBold(true)->setColor(new Color('FFB53654'));
                        $row++;

                        // Loan headers
                        $loanHeaders = ['Loan Type', 'Amount', 'Status'];
                        $col = 'A';
                        foreach ($loanHeaders as $header) {
                            $earningsSheet->setCellValue($col . $row, $header);
                            $earningsSheet->getStyle($col . $row)->getFont()->setBold(true);
                            $earningsSheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FA');
                            $col++;
                        }
                        $row++;

                        // Loan deductions data
                        foreach ($loanDeductions as $loan) {
                            $earningsSheet->setCellValue('A' . $row, $loan['deduction_type'] ?? $loan['type'] ?? 'Unknown');
                            $earningsSheet->setCellValue('B' . $row, 'PHP ' . number_format($loan['amount'] ?? 0, 2));
                            $earningsSheet->setCellValue('C' . $row, $loan['status'] ?? 'Active');

                            // Highlight amount
                            $earningsSheet->getStyle('B' . $row)->getFont()->setBold(true);
                            $earningsSheet->getStyle('B' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFCE4EC');

                            $row++;
                        }
                        $row++; // Space
                    }

                    $row += 2; // Extra space between employees
                }
            }

            // Auto-size columns for earnings sheet
            foreach (range('A', 'O') as $col) {
                $earningsSheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Create Excel file
            $writer = new Xlsx($spreadsheet);
            $fileName = 'payroll-report-' . now()->format('Y-m-d-His') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);

            $writer->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Payroll Excel Export Failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to export Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export Payroll as PDF (Updated with time totals)
     */
    public function exportPDF(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(24);

        if (!in_array('Export', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to export.'
            ], 403);
        }

        // Get filters from request
        $filters = [
            'branch' => $request->input('branch'),
            'department' => $request->input('department'),
            'designation' => $request->input('designation'),
            'dateRange' => $request->input('dateRange')
        ];

        $exporter = new PayrollExport($authUser, $filters);
        $payrolls = $exporter->getData();
        $summaryTotals = $exporter->getSummaryTotals($payrolls);
        $insights = $exporter->getPayrollInsights($payrolls);

        $data = [
            'payrolls' => $payrolls,
            'summaryTotals' => $summaryTotals,
            'insights' => $insights,
            'filters' => $filters,
            'generatedDate' => now()->format('F d, Y h:i A'),
            'exporter' => $exporter,
            'headers' => $exporter->getHeaders(),
            'totals' => $summaryTotals
        ];

        $pdf = Pdf::loadView('tenant.payroll.exports.pdf', $data);
        $pdf->setPaper('A4', 'landscape');

        // Optimize PDF for smaller file size
        $pdf->setOption('dpi', 96);
        $pdf->setOption('image-quality', 75);
        $pdf->setOption('enable-local-file-access', true);

        $filename = 'Payroll_Report_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }
}
