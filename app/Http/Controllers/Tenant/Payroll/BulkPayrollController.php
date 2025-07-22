<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Holiday;
use App\Models\Payroll;
use Carbon\CarbonPeriod;
use App\Models\UserEarning;
use App\Models\LeaveRequest;
use App\Models\SalaryRecord;
use Illuminate\Http\Request;
use App\Models\UserDeduction;
use App\Models\UserDeminimis;
use App\Models\BulkAttendance;
use App\Models\HolidayException;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use App\Models\WithholdingTaxTable;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PhilhealthContribution;

class BulkPayrollController extends Controller
{
    public function processBulkPayroll(Request $request)
    {
        $authUser = Auth::user();
        $tenantId = $authUser->tenant_id ?? null;

        $data = $request->validate([
            'user_id'    => 'required|array',
            'user_id.*'  => 'integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $pagibigOption = $request->input('pagibig_option');
        $sssOption = $request->input('sss_option');
        $philhealthOption = $request->input('philhealth_option');
        $cutoffOption = $request->input('cutoff_period');
        $payrollType = $request->input('payroll_type', 'normal_payroll');
        $payrollPeriod = $request->input('payroll_period', null);
        $paymentDate = $request->input('payment_date', now()->toDateString());


        if ($payrollType === 'bulk_attendance_payroll') {
            $attendances = $this->getBulkAttendances($tenantId, $data);
            $salaryData = $this->getSalaryData($data['user_id']);
            $bulkAttendanceData = $this->getBulkAttendanceData($tenantId, $data);
            $holidayPay = $this->calculateBulkHolidayPay($bulkAttendanceData, $data, $salaryData);
            $nightDiff = $this->calculateBulkNightDifferential($bulkAttendanceData, $data, $salaryData);
            $otNightDiff = $this->calculateBulkOvertimeNightDiffPay($bulkAttendanceData, $data, $salaryData);
            $earnings = $this->calculateBulkEarnings($bulkAttendanceData, $data, $salaryData);
            $deductions = $this->calculateBulkDeductions($bulkAttendanceData, $data, $salaryData);
            $leavePay = $this->calculateBulkLeavePay($bulkAttendanceData, $data, $salaryData);
            $basicPay = $this->calculateBulkBasicPay($bulkAttendanceData, $data, $salaryData);
            $grossPay = $this->calculateBulkGrossPay($bulkAttendanceData, $data, $salaryData);
            $sssContributions = $this->calculateBulkSSSContribution($bulkAttendanceData, $data, $salaryData, $sssOption, $cutoffOption);
            $philhealthContributions = $this->calculateBulkPhilhealthContribution($bulkAttendanceData, $data, $salaryData, $philhealthOption, $cutoffOption);
            $pagibigContributions = $this->calculateBulkPagibigContribution($bulkAttendanceData, $data, $salaryData, $pagibigOption, $cutoffOption);
            $withholdingTax = $this->calculateBulkWithholdingTax($bulkAttendanceData, $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cutoffOption);
            $totalDeductions = $this->calculateBulkTotalDeductions($bulkAttendanceData, $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cutoffOption);
            $totalEarnings = $this->calculateBulkTotalEarnings($bulkAttendanceData, $data, $salaryData);
            $thirteenthMonth = $this->calculateBulkThirteenthMonthPay($bulkAttendanceData, $data, $salaryData);
            $netPay = $this->calculateBulkNetPay($bulkAttendanceData, $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cutoffOption);

            // Save computed payroll for each user (Bulk columns mapping)
            foreach ($data['user_id'] as $userId) {
            $payroll = Payroll::updateOrCreate(
                [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'payroll_period_start' => $data['start_date'],
                'payroll_period_end' => $data['end_date'],
                'payroll_type' => $payrollType,
                ],
                [
                'payroll_period' => $payrollPeriod,
                // Bulk columns mapping
                'total_worked_minutes' => $bulkAttendanceData[$userId]['regular_working_minutes'] ?? 0,
                'total_worked_days' => $bulkAttendanceData[$userId]['regular_working_days'] ?? 0,
                'total_overtime_minutes' => $bulkAttendanceData[$userId]['regular_overtime_minutes'] ?? 0,
                'total_night_differential_minutes' => $bulkAttendanceData[$userId]['regular_nd_minutes'] ?? 0,
                'total_overtime_night_diff_minutes' => $bulkAttendanceData[$userId]['regular_nd_overtime_minutes'] ?? 0,
                // Add other bulk columns as needed

                // Pay breakdown
                'holiday_pay' => $holidayPay[$userId]['holiday_pay_amount'] ?? 0,
                'leave_pay' => $leavePay[$userId]['total_leave_pay'] ?? 0,
                'overtime_pay' =>
                    ($grossPay[$userId]['overtime_pay']['ordinary'] ?? 0)
                    + ($grossPay[$userId]['overtime_pay']['holiday'] ?? 0)
                    + ($grossPay[$userId]['overtime_pay']['holiday_rest_day'] ?? 0),
                'night_differential_pay' => ($grossPay[$userId]['night_diff_pay']['ordinary'] ?? 0) + ($grossPay[$userId]['night_diff_pay']['rest_day'] ?? 0) + ($grossPay[$userId]['night_diff_pay']['holiday'] ?? 0),
                'overtime_night_diff_pay' => ($grossPay[$userId]['overtime_night_diff_pay']['ordinary'] ?? 0) + ($grossPay[$userId]['overtime_night_diff_pay']['rest_day'] ?? 0) + ($grossPay[$userId]['overtime_night_diff_pay']['holiday'] ?? 0),

                // Deductions
                'late_deduction' => $deductions[$userId]['late_deduction'] ?? 0,
                'undertime_deduction' => $deductions[$userId]['undertime_deduction'] ?? 0,
                'absent_deduction' => $deductions[$userId]['absent_deduction'] ?? 0,
                'earnings' => isset($earnings[$userId]['earning_details']) ? json_encode($earnings[$userId]['earning_details']) : null,
                'total_earnings' => $totalEarnings[$userId]['total_earnings'] ?? 0,
                'taxable_income' => $withholdingTax[$userId]['taxable_income'] ?? 0,

                // De Minimis
                'deminimis' => null, // Add if you have deminimis logic for bulk

                // Mandates
                'sss_contribution' => $sssContributions[$userId]['employee_total'] ?? 0,
                'philhealth_contribution' => $philhealthContributions[$userId]['employee_total'] ?? 0,
                'pagibig_contribution' => $pagibigContributions[$userId]['employee_total'] ?? 0,
                'withholding_tax' => $withholdingTax[$userId]['withholding_tax'] ?? 0,
                'loan_deductions' => null, // Add loan logic if needed
                'deductions' => isset($deductions[$userId]['deduction_details']) ? json_encode($deductions[$userId]['deduction_details']) : null,
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
            'bulk_attendance_data' => $bulkAttendanceData,
            'deductions'        => $deductions,
            'holiday'           => $holidayPay,
            'night_diff_pay'    => $nightDiff,
            'overtimes'         => $grossPay,
            'message'           => 'Payroll processed and saved.',
            ]);
        } else {
            return response()->json([
            'message' => 'Payroll type not supported yet.',
            ], 422);
        }
    }


    // =================== BULK PAYROLL OPERATIONS =================== //

    // Get Salary Data
    protected function getSalaryData(array $userIds)
    {
        $salaryRecords = SalaryRecord::whereIn('user_id', $userIds)
            ->where('is_active', 1)
            ->get()
            ->keyBy('user_id');

        // Preload users with employmentDetail.branch and salaryDetail
        $users = User::with(['employmentDetail.branch', 'salaryDetail'])
            ->whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        $result = [];

        foreach ($userIds as $userId) {
            $salaryRecord = $salaryRecords->get($userId);
            $user = $users->get($userId);

            if ($salaryRecord) {
                // Get worked_days_per_year from salaryDetail
                $workedDays = $user->salaryDetail->worked_days_per_year ?? null;

                // If null or 0, get from branch
                if (empty($workedDays) && $user->employmentDetail && $user->employmentDetail->branch) {
                    $branch = $user->employmentDetail->branch;
                    if (isset($branch->worked_days_per_year)) {
                        if ($branch->worked_days_per_year === 'custom' && isset($branch->custom_worked_days)) {
                            $workedDays = $branch->custom_worked_days;
                        } else {
                            $workedDays = $branch->worked_days_per_year;
                        }
                    }
                }

                $workedDays = $workedDays ?? 0;

                $salaryArr = [
                    'basic_salary'         => $salaryRecord->basic_salary,
                    'salary_type'          => $salaryRecord->salary_type,
                    'worked_days_per_year' => $workedDays,
                ];
            } else {
                // No salary record, get from branch
                $branch = $user && $user->employmentDetail ? $user->employmentDetail->branch : null;
                $basicSalary = $branch->basic_salary ?? 0;
                $salaryType = $branch->salary_type ?? 'monthly_fixed';

                // worked_days_per_year logic
                $workedDays = $user->salaryDetail->worked_days_per_year ?? null;
                if (empty($workedDays) && $branch) {
                    if (isset($branch->worked_days_per_year)) {
                        if ($branch->worked_days_per_year === 'custom' && isset($branch->custom_worked_days)) {
                            $workedDays = $branch->custom_worked_days;
                        } else {
                            $workedDays = $branch->worked_days_per_year;
                        }
                    }
                }
                $workedDays = $workedDays ?? 0;

                $salaryArr = [
                    'basic_salary'         => $basicSalary,
                    'salary_type'          => $salaryType,
                    'worked_days_per_year' => $workedDays,
                ];
            }

            $result[$userId] = $salaryArr;
        }

        return collect($result);
    }

    // Attendance Getter for Bulk Attendance
    protected function getBulkAttendances(int $tenantId, array $data)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();

        $bulkAttendances = BulkAttendance::with(['user'])
            ->whereIn('user_id', $data['user_id'])
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->where('date_from', '<=', $end)
                        ->where('date_to', '>=', $start);
                });
            })
            ->whereHas('user', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->orderBy('date_from')
            ->get();

        return $bulkAttendances;
    }

    // Bulk Attendance Data Getter
    protected function getBulkAttendanceData(int $tenantId, array $data)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->endOfDay();

        $bulkAttendances = BulkAttendance::whereIn('user_id', $data['user_id'])
            ->where(fn($query) => $query->where('date_from', '<=', $end)->where('date_to', '>=', $start))
            ->whereHas('user', fn($q) => $q->where('tenant_id', $tenantId))
            ->get();

        $result = [];

        // Define mappings of DB columns to result keys and conversion (hours to minutes)
        $fields = [
            'regular_working_hours'         => 'regular_working_minutes',
            'regular_overtime_hours'        => 'regular_overtime_minutes',
            'regular_nd_hours'              => 'regular_nd_minutes',
            'regular_nd_overtime_hours'     => 'regular_nd_overtime_minutes',
            'rest_day_work'                 => 'rest_day_work_minutes',
            'rest_day_ot'                   => 'rest_day_ot_minutes',
            'rest_day_nd'                   => 'rest_day_nd_minutes',
            'regular_holiday_hours'         => 'regular_holiday_minutes',
            'special_holiday_hours'         => 'special_holiday_minutes',
            'regular_holiday_ot'            => 'regular_holiday_ot_minutes',
            'special_holiday_ot'            => 'special_holiday_ot_minutes',
            'regular_holiday_nd'            => 'regular_holiday_nd_minutes',
            'special_holiday_nd'            => 'special_holiday_nd_minutes',
        ];

        foreach ($bulkAttendances as $row) {
            $uid = $row->user_id;

            // Initialize if user not set
            if (!isset($result[$uid])) {
                foreach ($fields as $targetField) {
                    $result[$uid][$targetField] = 0;
                }
                $result[$uid]['regular_working_days'] = 0;
                $result[$uid]['total_minutes'] = 0;
            }

            // Aggregate data
            $result[$uid]['regular_working_days'] += floatval($row->regular_working_days ?? 0);

            foreach ($fields as $dbField => $targetField) {
                $hours = floatval($row->$dbField ?? 0);
                $minutes = $hours * 60;
                $result[$uid][$targetField] += $minutes;
                $result[$uid]['total_minutes'] += $minutes;
            }
        }

        return $result;
    }

    //  Holiday Pay Computation
    protected function calculateBulkHolidayPay($bulkAttendanceData, array $data, $salaryData)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();
        $period = CarbonPeriod::create($start->toDateString(), $end->toDateString());
        $monthDays = collect($period)->map(fn($d) => $d->format('m-d'))->unique()->values()->all();

        // Get all holidays in period (needed for breakdown and to handle exceptions)
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
            // Salary data handling (works with both array/collect)
            $sal = is_array($salaryData) && isset($salaryData[$id])
                ? $salaryData[$id]
                : (method_exists($salaryData, 'get') ? $salaryData->get($id, [
                    'basic_salary'         => 0,
                    'salary_type'          => 'monthly_fixed',
                    'worked_days_per_year' => 0,
                ]) : [
                    'basic_salary'         => 0,
                    'salary_type'          => 'monthly_fixed',
                    'worked_days_per_year' => 0,
                ]);

            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];
            $wpy   = $sal['worked_days_per_year'];

            // Per-minute and daily rate, same logic as normal
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
                $dailyRate = 0;
                $perMin = 0;
            }

            // Get bulk columns for user
            $bulk = $bulkAttendanceData[$id] ?? [];

            // Bulk columns (minutes already * 60 sa pagcompute mo)
            $regularHolidayMins = $bulk['regular_holiday_minutes'] ?? 0;
            $specialHolidayMins = $bulk['special_holiday_minutes'] ?? 0;

            // Calculate pay
            $regularHolidayPay = $perMin * $regularHolidayMins;
            $specialHolidayPay = $perMin * $specialHolidayMins;

            $payTotal = $regularHolidayPay + $specialHolidayPay;

            $breakdown = [
                [
                    'type' => 'regular',
                    'minutes' => $regularHolidayMins,
                    'per_min' => $perMin,
                    'pay' => round($regularHolidayPay, 2),
                ],
                [
                    'type' => 'special',
                    'minutes' => $specialHolidayMins,
                    'per_min' => $perMin,
                    'pay' => round($specialHolidayPay, 2),
                ],
            ];

            $result[$id] = [
                'holiday_pay_amount' => round($payTotal, 2),
                'breakdown' => $breakdown,
            ];
        }
        return $result;
    }

    // Night Diff Computation
    protected function calculateBulkNightDifferential($bulkAttendanceData, array $data, $salaryData)
    {
        // Get multipliers from ot_tables
        $multipliers = DB::table('ot_tables')->pluck('night_differential', 'type');

        $result = [];
        foreach ($data['user_id'] as $id) {
            // Salary data handling
            $sal = is_array($salaryData) && isset($salaryData[$id])
                ? $salaryData[$id]
                : (method_exists($salaryData, 'get') ? $salaryData->get($id, [
                    'basic_salary'         => 0,
                    'salary_type'          => 'monthly_fixed',
                    'worked_days_per_year' => 0,
                ]) : [
                    'basic_salary'         => 0,
                    'salary_type'          => 'monthly_fixed',
                    'worked_days_per_year' => 0,
                ]);

            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];
            $wpy   = $sal['worked_days_per_year'] ?? 0;

            // Per-minute computation
            if ($stype === 'monthly_fixed' && $wpy > 0) {
                $dailyRate = ($basic * 12) / $wpy;
                $perMin = ($dailyRate / 8) / 60;
            } elseif ($stype === 'hourly_rate') {
                $perMin = $basic / 60;
            } elseif ($stype === 'daily_rate') {
                $perMin = ($basic / 8) / 60;
            } else {
                $perMin = 0;
            }

            $bulk = $bulkAttendanceData[$id] ?? [];

            // Get mins per type from bulk columns
            $ordinaryMins   = $bulk['regular_nd_minutes'] ?? 0;
            $restDayMins    = $bulk['rest_day_nd_minutes'] ?? 0;
            $regHolMins     = $bulk['regular_holiday_nd_minutes'] ?? 0;
            $specHolMins    = $bulk['special_holiday_nd_minutes'] ?? 0;

            // Multiplier
            $mOrd  = $multipliers['ordinary'] ?? 0;
            $mRst  = $multipliers['rest_day'] ?? 0;
            $mRegH = $multipliers['regular_holiday'] ?? 0;
            $mSpecH = $multipliers['special_holiday'] ?? 0;

            // Compute pay
            $payOrd   = round($perMin * $ordinaryMins * $mOrd, 2);
            $payRst   = round($perMin * $restDayMins * $mRst, 2);
            $payRegH  = round($perMin * $regHolMins * $mRegH, 2);
            $paySpecH = round($perMin * $specHolMins * $mSpecH, 2);

            $result[$id] = [
                'ordinary_pay'   => $payOrd,
                'rest_day_pay'   => $payRst,
                'holiday_pay'    => $payRegH + $paySpecH,
                'breakdown' => [
                    'ordinary_minutes' => $ordinaryMins,
                    'rest_day_minutes' => $restDayMins,
                    'regular_holiday_minutes' => $regHolMins,
                    'special_holiday_minutes' => $specHolMins,
                    'ordinary_pay'   => $payOrd,
                    'rest_day_pay'   => $payRst,
                    'regular_holiday_pay' => $payRegH,
                    'special_holiday_pay' => $paySpecH,
                ]
            ];
        }

        return $result;
    }

    // Overtime Night Differential Computation
    protected function calculateBulkOvertimeNightDiffPay($bulkAttendanceData, array $data, $salaryData)
    {
        $multipliers = DB::table('ot_tables')->pluck('night_differential_overtime', 'type');

        $result = [];
        foreach ($data['user_id'] as $id) {
            // Salary data handling
            $sal = isset($salaryData[$id]) ? $salaryData[$id] : [
                'basic_salary' => 0,
                'salary_type' => 'hourly_rate',
                'worked_days_per_year' => 0,
            ];

            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];
            $wpy   = $sal['worked_days_per_year'] ?? 0;

            // Per-minute computation
            if ($stype === 'monthly_fixed' && $wpy > 0) {
                $dailyRate = ($basic * 12) / $wpy;
                $perMin = ($dailyRate / 8) / 60;
            } elseif ($stype === 'hourly_rate') {
                $perMin = $basic / 60;
            } elseif ($stype === 'daily_rate') {
                $perMin = ($basic / 8) / 60;
            } else {
                $perMin = 0;
            }

            $bulk = $bulkAttendanceData[$id] ?? [];

            // Get mins per type from bulk columns
            $ordinaryMins   = $bulk['regular_nd_overtime_minutes'] ?? 0;
            $restDayMins    = $bulk['rest_day_nd_overtime_minutes'] ?? 0;
            $regHolMins     = $bulk['regular_holiday_nd_overtime_minutes'] ?? 0;
            $specHolMins    = $bulk['special_holiday_nd_overtime_minutes'] ?? 0;

            // Multiplier
            $mOrd  = $multipliers['ordinary'] ?? 0;
            $mRst  = $multipliers['rest_day'] ?? 0;
            $mRegH = $multipliers['regular_holiday'] ?? 0;
            $mSpecH = $multipliers['special_holiday'] ?? 0;

            // Compute pay
            $payOrd   = round($perMin * $ordinaryMins * $mOrd, 2);
            $payRst   = round($perMin * $restDayMins * $mRst, 2);
            $payRegH  = round($perMin * $regHolMins * $mRegH, 2);
            $paySpecH = round($perMin * $specHolMins * $mSpecH, 2);

            $result[$id] = [
                'ordinary_pay'   => $payOrd,
                'rest_day_pay'   => $payRst,
                'holiday_pay'    => $payRegH + $paySpecH, // Sum of all holiday types
                'breakdown' => [
                    'ordinary_minutes' => $ordinaryMins,
                    'rest_day_minutes' => $restDayMins,
                    'regular_holiday_minutes' => $regHolMins,
                    'special_holiday_minutes' => $specHolMins,
                    'ordinary_pay'   => $payOrd,
                    'rest_day_pay'   => $payRst,
                    'regular_holiday_pay' => $payRegH,
                    'special_holiday_pay' => $paySpecH,
                ]
            ];
        }

        return $result;
    }

    // Overtime Pay
    public function calculateBulkOvertimePay($bulkAttendanceData, array $data, $salaryData)
    {
        // Get multipliers from ot_tables
        $otMultipliers = DB::table('ot_tables')->pluck('overtime', 'type');

        $result = [];
        foreach ($data['user_id'] as $id) {
            // Salary data handling
            $sal = is_array($salaryData) && isset($salaryData[$id])
                ? $salaryData[$id]
                : (method_exists($salaryData, 'get') ? $salaryData->get($id, [
                    'basic_salary' => 0,
                    'salary_type'  => 'hourly_rate',
                    'worked_days_per_year' => 0,
                ]) : [
                    'basic_salary' => 0,
                    'salary_type'  => 'hourly_rate',
                    'worked_days_per_year' => 0,
                ]);

            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];
            $wpy   = $sal['worked_days_per_year'];

            // Get overtime data for the user from bulk attendance data
            $bulk = $bulkAttendanceData[$id] ?? [];
            $ord = $bulk['regular_ot_minutes'] ?? 0;
            $rd  = $bulk['rest_day_ot_minutes'] ?? 0;
            $hol = $bulk['regular_holiday_ot_minutes'] ?? 0;
            $holRst = $bulk['regular_holiday_ot_minutes'] ?? 0;  // Holiday + Rest day overtime pay

            // Multiplier
            $mOrd = $otMultipliers['ordinary'] ?? 0;
            $mRst = $otMultipliers['rest_day'] ?? 0;
            $mRegH = $otMultipliers['regular_holiday'] ?? 0;
            $mSpecH = $otMultipliers['special_holiday'] ?? 0;

            // Compute per-minute rate
            if ($stype === 'hourly_rate') {
                $perMin = $basic / 60;
            } elseif ($stype === 'daily_rate') {
                $perMin = ($basic / 8) / 60;
            } elseif ($stype === 'monthly_fixed') {
                $dailyRate = $wpy > 0 ? ($basic * 12) / $wpy : 0;
                $perMin = ($dailyRate / 8) / 60;
            } else {
                $perMin = 0;
            }

            // Calculate pay for different types
            $payOrd = round($perMin * $ord * $mOrd, 2);      // Ordinary overtime pay
            $payRd = round($perMin * $rd * $mRst, 2);        // Rest day overtime pay
            $payHol = round($perMin * $hol * $mRegH, 2);     // Holiday overtime pay
            $payRdHol = round($perMin * $holRst * $mSpecH, 2); // Holiday + Rest day overtime pay

            $result[$id] = [
                'ordinary_pay' => $payOrd,
                'rest_day_pay' => $payRd,
                'holiday_pay' => $payHol,
                'holiday_rest_day_pay' => $payRdHol,
                'total_ot_minutes' => $ord + $rd + $hol + $holRst,
            ];
        }

        return $result;
    }

    // Earnings Computation (Dynamic)
    protected function calculateBulkEarnings($bulkAttendanceData, array $data, $salaryData)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();

        // Get all user earnings with their earning types
        $earnings = UserEarning::whereIn('user_id', $data['user_id'])
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

        foreach ($data['user_id'] as $id) {
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

    // Deductions Computation (Dynamic)
    protected function calculateBulkDeductions($bulkAttendanceData, array $data, $salaryData)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();

        // Get all user deductions with their deduction types
        $deductions = UserDeduction::whereIn('user_id', $data['user_id'])
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

        foreach ($data['user_id'] as $id) {
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
                        'calculation_method'    => $dType->calculation_method,
                        'default_amount'        => $dType->default_amount,
                        'is_taxable'            => $dType->is_taxable,
                        'apply_to_all_employees' => $dType->apply_to_all_employees,
                        'description'           => $dType->description,
                        'user_amount_override' => $deduction->amount,
                        'applied_amount'        => round($finalAmount, 2),
                        'frequency'             => $deduction->frequency,
                        'status'                => $deduction->status,
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

    // Leave Pay
    public function calculateBulkLeavePay($bulkAttendanceData, array $data, $salaryData)
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end   = Carbon::parse($data['end_date'])->endOfDay();

        // Get all user leaves with leaveType relation
        $leaves = LeaveRequest::whereIn('user_id', $data['user_id'])
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end]);
            })
            ->with('leaveType')
            ->get();

        $result = [];

        foreach ($data['user_id'] as $userId) {
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
            }

            $result[$userId] = [
                'total_leave_pay' => round($totalLeavePay, 2),
                'leaves' => $leaveDetails,
            ];
        }

        return $result;
    }

    // Basic Salary Computation
    protected function calculateBulkBasicPay($bulkAttendanceData, array $data, $salaryData)
    {
        // Preload employment details and branch for all users
        $tenantId = Auth::user()->tenant_id ?? null;
        $users = User::with(['employmentDetail.branch'])->whereIn('id', $data['user_id'])->get()->keyBy('id');

        $result = [];
        foreach ($data['user_id'] as $id) {
            $sal = is_array($salaryData) && isset($salaryData[$id])
                ? $salaryData[$id]
                : (method_exists($salaryData, 'get') ? $salaryData->get($id, [
                    'basic_salary'         => 0,
                    'salary_type'          => 'hourly_rate',
                    'worked_days_per_year' => 0,
                ]) : [
                    'basic_salary'         => 0,
                    'salary_type'          => 'hourly_rate',
                    'worked_days_per_year' => 0,
                ]);

            $basic = $sal['basic_salary'];
            $stype = $sal['salary_type'];
            $wpy   = $sal['worked_days_per_year'];

            // Get work minutes and work days for this user from bulk attendance data
            $workMinutes = $bulkAttendanceData[$id]['regular_working_minutes'] ?? 0;
            $workDays = $bulkAttendanceData[$id]['regular_working_days'] ?? 0;

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

    // Gross Pay Computation
    public function calculateBulkGrossPay($bulkAttendanceData, array $data, $salaryData)
    {
        // Calculate basic pay
        $basicPay = $this->calculateBulkBasicPay($bulkAttendanceData, $data, $salaryData);
        // Calculate earnings
        $earnings = $this->calculateBulkEarnings($bulkAttendanceData, $data, $salaryData);

        // Calculate Holiday Pay
        $holidayPay = $this->calculateBulkHolidayPay(
            $bulkAttendanceData,
            $data,
            $salaryData
        );

        // Calculate Overtime Pay
        $overtimePay = $this->calculateBulkOvertimePay($bulkAttendanceData, $data, $salaryData);

        // Calculate Night Differential Pay
        $nightDiffPay = $this->calculateBulkNightDifferential($bulkAttendanceData, $data, $salaryData);

        // Calculate Overtime Night Differential Pay
        $overtimeNightDiffPay = $this->calculateBulkOvertimeNightDiffPay($bulkAttendanceData, $data, $salaryData);

        // Calculate Leave Pay
        $leavePay = $this->calculateBulkLeavePay($data['user_id'], $data, $salaryData);

        $result = [];

        foreach ($data['user_id'] as $id) {
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

    // SSS Contribution Computation
    public function calculateBulkSSSContribution($bulkAttendanceData, array $data, $salaryData, $sssOption, $cutoffOption)
    {
        // Preload user branch SSS contribution type, template, and fixed amount
        $users = User::with(['employmentDetail.branch', 'salaryDetail'])->whereIn('id', $data['user_id'])->get()->keyBy('id');

        $result = [];
        foreach ($data['user_id'] as $userId) {
            $user = $users[$userId] ?? null;
            $branch = $user && $user->employmentDetail ? $user->employmentDetail->branch : null;
            $sssType = $branch->sss_contribution_type ?? null;
            $sssTemplateYear = $branch->sss_contribution_template ?? null;

            // Try to get worked_days_per_year from salaryData
            $workedDaysPerYear = $salaryData->get($userId)['worked_days_per_year'] ?? null;

            // If null or 0, get from branch
            if ((is_null($workedDaysPerYear) || $workedDaysPerYear == 0) && $branch) {
                if (isset($branch->worked_days_per_year)) {
                    if ($branch->worked_days_per_year === 'custom' && isset($branch->custom_worked_days)) {
                        $workedDaysPerYear = $branch->custom_worked_days;
                    } else {
                        $workedDaysPerYear = $branch->worked_days_per_year;
                    }
                }
            }

            // Get SSS table for the selected year (template)
            $sssTableQuery = DB::table('sss_contribution_tables');
            if ($sssTemplateYear) {
                $sssTableQuery->where('year', $sssTemplateYear);
            }
            $sssTable = $sssTableQuery->get();

            // Get the gross pay and basic pay for bulk
            $grossPay = $this->calculateBulkGrossPay($bulkAttendanceData, $data, $salaryData);
            $basicPay = $this->calculateBulkBasicPay($bulkAttendanceData, $data, $salaryData);

            // Default to 0 if not found
            $result[$userId] = [
                'employer_total' => 0,
                'employee_total' => 0,
                'total_contribution' => 0,
                'worked_days_per_year' => $workedDaysPerYear,
            ];

            // If sssOption is "no", always 0
            if ($sssOption === 'no') {
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
                continue;
            }

            // If sssOption is "full"
            if ($sssOption === 'full') {
                $year = Carbon::parse($data['start_date'])->year;
                $month = Carbon::parse($data['start_date'])->month;

                if ($cutoffOption == 1) {
                    // Get monthly_fixed salary data of the user
                    $monthlySalary = 0;
                    $stype = $salaryData->get($userId)['salary_type'] ?? null;
                    if ($stype === 'monthly_fixed') {
                        $monthlySalary = $salaryData->get($userId)['basic_salary'] ?? 0;
                    } else {
                        $monthlySalary = $grossPay[$userId]['gross_pay'] ?? 0;
                    }
                    // Find SSS contribution
                    $sssContribution = $sssTable->first(function ($item) use ($monthlySalary) {
                        return $monthlySalary >= $item->range_from && $monthlySalary <= $item->range_to;
                    });
                    $sssValue = $sssContribution ? $sssContribution->employee_total : 0;
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
                            'sss_contribution' => $sssValue,
                            'status' => 'complete',
                        ]
                    );
                    $result[$userId] = [
                        'employer_total' => $sssContribution->employer_total ?? 0,
                        'employee_total' => $sssValue,
                        'total_contribution' => $sssContribution->total_contribution ?? 0,
                        'worked_days_per_year' => $workedDaysPerYear,
                    ];
                    continue;
                } elseif ($cutoffOption == 2) {
                    // Get gross_pay for cutoff 1 (pending or complete)
                    $mandate1 = \App\Models\MandatesContribution::where([
                        'user_id' => $userId,
                        'year' => $year,
                        'month' => $month,
                        'cutoff_period' => 1,
                    ])->first();

                    // If cutoff 1 does not exist, compute based on monthly_fixed salary
                    if (!$mandate1) {
                        $monthlySalary = 0;
                        $stype = $salaryData->get($userId)['salary_type'] ?? null;
                        if ($stype === 'monthly_fixed') {
                            $monthlySalary = $salaryData->get($userId)['basic_salary'] ?? 0;
                        } else {
                            $monthlySalary = $grossPay[$userId]['gross_pay'] ?? 0;
                        }
                        $sssContribution = $sssTable->first(function ($item) use ($monthlySalary) {
                            return $monthlySalary >= $item->range_from && $monthlySalary <= $item->range_to;
                        });
                        $sssValue = $sssContribution ? $sssContribution->employee_total : 0;
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
                                'sss_contribution' => $sssValue,
                                'status' => 'complete',
                            ]
                        );
                        $result[$userId] = [
                            'employer_total' => $sssContribution->employer_total ?? 0,
                            'employee_total' => $sssValue,
                            'total_contribution' => $sssContribution->total_contribution ?? 0,
                            'worked_days_per_year' => $workedDaysPerYear,
                        ];
                        continue;
                    }

                    $gross1 = $mandate1 ? $mandate1->gross_pay : 0;
                    $gross2 = $grossPay[$userId]['gross_pay'] ?? 0;
                    $sumGross = $gross1 + $gross2;

                    // Find SSS contribution for the sum
                    $sssContribution = $sssTable->first(function ($item) use ($sumGross) {
                        return $sumGross >= $item->range_from && $sumGross <= $item->range_to;
                    });
                    $sssValue = $sssContribution ? $sssContribution->employee_total : 0;

                    // Save for cutoff 2 with total gross pay and computed SSS
                    $mandate2 = \App\Models\MandatesContribution::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'year' => $year,
                            'month' => $month,
                            'cutoff_period' => 2,
                        ],
                        [
                            'basic_pay' => $basicPay[$userId]['basic_pay'] ?? 0,
                            'gross_pay' => $sumGross, // Save total gross pay of cutoff 1 and 2
                            'sss_contribution' => $sssValue, // Save computed SSS for total gross
                            'status' => 'complete',
                        ]
                    );
                    // Update cutoff 1 status to complete and update gross_pay and sss_contribution if needed
                    if ($mandate1) {
                        $mandate1->status = 'complete';
                        $mandate1->gross_pay = $gross1;
                        $mandate1->sss_contribution = 0; // SSS is only on cutoff 2
                        $mandate1->save();
                    }
                    $result[$userId] = [
                        'employer_total' => $sssContribution->employer_total ?? 0,
                        'employee_total' => $sssValue,
                        'total_contribution' => $sssContribution->total_contribution ?? 0,
                        'worked_days_per_year' => $workedDaysPerYear,
                    ];
                    continue;
                }
            }

            // If sssOption is "yes" (default/original logic)
            if ($sssOption === 'yes') {
                if ($sssType === 'system') {
                    $salary = $grossPay[$userId]['gross_pay'] ?? 0;
                    $sssContribution = $sssTable->first(function ($item) use ($salary) {
                        return $salary >= $item->range_from && $salary <= $item->range_to;
                    });

                    if ($sssContribution) {
                        $result[$userId] = [
                            'employer_total' => $sssContribution->employer_total,
                            'employee_total' => $sssContribution->employee_total,
                            'total_contribution' => $sssContribution->total_contribution,
                            'worked_days_per_year' => $workedDaysPerYear,
                        ];
                    }
                } elseif ($sssType === 'fixed') {
                    $fixedAmount = $branch->fixed_sss_amount ?? 0;
                    $salaryComputation = $branch->salary_computation_type ?? null;
                    $amount = $fixedAmount;
                    if ($salaryComputation === 'semi-monthly') {
                        $amount = $fixedAmount / 2;
                    }
                    $result[$userId] = [
                        'employer_total' => 0,
                        'employee_total' => $amount,
                        'total_contribution' => $amount,
                        'worked_days_per_year' => $workedDaysPerYear,
                    ];
                } elseif ($sssType === 'manual') {
                    $salaryDetail = $user->salaryDetail ?? null;
                    $salaryComputation = $branch->salary_computation_type ?? null;
                    if ($salaryDetail && isset($salaryDetail->sss_contribution)) {
                        if ($salaryDetail->sss_contribution === 'system') {
                            $salary = $grossPay[$userId]['gross_pay'] ?? 0;
                            $sssContribution = $sssTable->first(function ($item) use ($salary) {
                                return $salary >= $item->range_from && $salary <= $item->range_to;
                            });

                            if ($sssContribution) {
                                $result[$userId] = [
                                    'employer_total' => $sssContribution->employer_total,
                                    'employee_total' => $sssContribution->employee_total,
                                    'total_contribution' => $sssContribution->total_contribution,
                                    'worked_days_per_year' => $workedDaysPerYear,
                                ];
                            }
                        } elseif ($salaryDetail->sss_contribution === 'manual') {
                            $override = $salaryDetail->sss_contribution_override ?? 0;
                            $amount = $override;
                            if ($salaryComputation === 'semi-monthly') {
                                $amount = $override / 2;
                            }
                            $result[$userId] = [
                                'employer_total' => 0,
                                'employee_total' => $amount,
                                'total_contribution' => $amount,
                                'worked_days_per_year' => $workedDaysPerYear,
                            ];
                        }
                    }
                }
            }
        }

        return $result;
    }

    // Philhealth Contribution Computation
    public function calculateBulkPhilhealthContribution($bulkAttendanceData, array $data, $salaryData, $philhealthOption, $cutoffOption)
    {
        // Preload user branch PhilHealth contribution type and fixed amount
        $users = User::with(['employmentDetail.branch', 'salaryDetail'])->whereIn('id', $data['user_id'])->get()->keyBy('id');
        $philhealthTable = PhilhealthContribution::all();

        // Get the basic pay and gross pay
        $basicPay = $this->calculateBulkBasicPay($bulkAttendanceData, $data, $salaryData);
        $grossPay = $this->calculateBulkGrossPay($bulkAttendanceData, $data, $salaryData);

        $result = [];
        foreach ($data['user_id'] as $userId) {
            $user = $users[$userId] ?? null;
            $branch = $user && $user->employmentDetail ? $user->employmentDetail->branch : null;
            $philhealthType = $branch && isset($branch->philhealth_contribution_type) ? $branch->philhealth_contribution_type : null;

            // Default to 0 if not found
            $result[$userId] = [
                'employer_total' => 0,
                'employee_total' => 0,
                'total_contribution' => 0,
            ];

            // If philhealthOption is "no", always 0 and save to MandatesContribution if cutoffOption == 1
            if ($philhealthOption === 'no') {
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
                            'philhealth_contribution' => 0,
                            'status' => 'pending',
                        ]
                    );
                }

                continue;
            }

            // If philhealthOption is "full"
            if ($philhealthOption === 'full') {
                $year = Carbon::parse($data['start_date'])->year;
                $month = Carbon::parse($data['start_date'])->month;

                if ($cutoffOption == 1) {
                    $monthlySalary = 0;
                    $stype = $salaryData->get($userId)['salary_type'] ?? null;
                    if ($stype === 'monthly_fixed') {
                        $monthlySalary = $salaryData->get($userId)['basic_salary'] ?? 0;
                    } else {
                        $monthlySalary = $grossPay[$userId]['gross_pay'] ?? 0;
                    }
                    $philhealthContribution = $philhealthTable->first(function ($item) use ($monthlySalary) {
                        return $monthlySalary >= $item->min_salary && $monthlySalary <= $item->max_salary;
                    });
                    $philhealthValue = $philhealthContribution ? $philhealthContribution->employee_share : 0;
                    \App\Models\MandatesContribution::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'year' => $year,
                            'month' => $month,
                            'cutoff_period' => 1,
                        ],
                        [
                            'basic_pay' => $basicPay[$userId]['basic_pay'] ?? 0,
                            'philhealth_contribution' => $philhealthValue,
                            'status' => 'complete',
                        ]
                    );

                    $result[$userId] = [
                        'employer_total' => $philhealthContribution->employer_share ?? 0,
                        'employee_total' => $philhealthContribution->employee_share ?? 0,
                        'total_contribution' => $philhealthValue,
                    ];
                    continue;
                } elseif ($cutoffOption == 2) {
                    $mandate1 = \App\Models\MandatesContribution::where([
                        'user_id' => $userId,
                        'year' => $year,
                        'month' => $month,
                        'cutoff_period' => 1,
                    ])->first();

                    if (!$mandate1) {
                        $monthlySalary = 0;
                        $stype = $salaryData->get($userId)['salary_type'] ?? null;
                        if ($stype === 'monthly_fixed') {
                            $monthlySalary = $salaryData->get($userId)['basic_salary'] ?? 0;
                        } else {
                            $monthlySalary = $grossPay[$userId]['gross_pay'] ?? 0;
                        }
                        $philhealthContribution = $philhealthTable->first(function ($item) use ($monthlySalary) {
                            return $monthlySalary >= $item->min_salary && $monthlySalary <= $item->max_salary;
                        });
                        $philhealthValue = $philhealthContribution ? $philhealthContribution->employee_share : 0;
                        // Create new record for cutoff 2
                        \App\Models\MandatesContribution::updateOrCreate(
                            [
                                'user_id' => $userId,
                                'year' => $year,
                                'month' => $month,
                                'cutoff_period' => 2,
                            ],
                            [
                                'basic_pay' => $basicPay[$userId]['basic_pay'] ?? 0,
                                'philhealth_contribution' => $philhealthValue,
                                'status' => 'complete',
                            ]
                        );

                        $result[$userId] = [
                            'employer_total' => $philhealthContribution->employer_share ?? 0,
                            'employee_total' => $philhealthContribution->employee_share ?? 0,
                            'total_contribution' => $philhealthValue,
                        ];
                        continue;
                    }

                    $basic1 = $mandate1 ? $mandate1->basic_pay : 0;
                    $basic2 = $basicPay[$userId]['basic_pay'] ?? 0;
                    $sumBasic = $basic1 + $basic2;

                    $philhealthContribution = $philhealthTable->first(function ($item) use ($sumBasic) {
                        return $sumBasic >= $item->min_salary && $sumBasic <= $item->max_salary;
                    });
                    $philhealthValue = $philhealthContribution ? $philhealthContribution->employee_share : 0;

                    // Save for cutoff 2 with total basic pay and computed PhilHealth
                    \App\Models\MandatesContribution::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'year' => $year,
                            'month' => $month,
                            'cutoff_period' => 2,
                        ],
                        [
                            'basic_pay' => $sumBasic,
                            'philhealth_contribution' => $philhealthValue,
                            'status' => 'complete',
                        ]
                    );
                    // Update cutoff 1 status to complete and set contribution to 0
                    if ($mandate1) {
                        $mandate1->status = 'complete';
                        $mandate1->basic_pay = $basic1;
                        $mandate1->philhealth_contribution = 0;
                        $mandate1->save();
                    }

                    $result[$userId] = [
                        'employer_total' => $philhealthContribution->employer_share ?? 0,
                        'employee_total' => $philhealthContribution->employee_share ?? 0,
                        'total_contribution' => $philhealthValue,
                    ];
                    continue;
                }
            }

            // If philhealthOption is "yes" (default/original logic)
            if ($philhealthOption === 'yes') {
                if ($philhealthType === 'system') {
                    $salary = $basicPay[$userId]['basic_pay'] ?? 0;
                    $philhealthContribution = $philhealthTable->first(function ($item) use ($salary) {
                        return $salary >= $item->min_salary && $salary <= $item->max_salary;
                    });

                    if ($philhealthContribution) {
                        $result[$userId] = [
                            'employer_total' => round($philhealthContribution->employer_share, 2),
                            'employee_total' => round($philhealthContribution->employee_share, 2),
                            'total_contribution' => round($philhealthContribution->monthly_premium, 2),
                        ];
                    }
                } elseif ($philhealthType === 'fixed') {
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

                        $result[$userId] = [
                            'employer_total' => 0,
                            'employee_total' => round($amount, 2),
                            'total_contribution' => round($amount, 2),
                        ];
                    }
                } elseif ($philhealthType === 'manual') {
                    $salaryDetail = $user->salaryDetail ?? null;
                    $salaryComputation = $branch->salary_computation_type ?? null;
                    if ($salaryDetail && isset($salaryDetail->philhealth_contribution)) {
                        if ($salaryDetail->philhealth_contribution === 'system') {
                            $salary = $basicPay[$userId]['basic_pay'] ?? 0;
                            $philhealthContribution = $philhealthTable->first(function ($item) use ($salary) {
                                return $salary >= $item->min_salary && $salary <= $item->max_salary;
                            });
                            if ($philhealthContribution) {
                                $result[$userId] = [
                                    'employer_total' => round($philhealthContribution->employer_share, 2),
                                    'employee_total' => round($philhealthContribution->employee_share, 2),
                                    'total_contribution' => round($philhealthContribution->monthly_premium, 2),
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    // Pagibig Contribution Computation
    public function calculateBulkPagibigContribution($bulkAttendanceData, array $data, $salaryData, $pagibigOption, $cutoffOption)
    {
        // Preload user branch Pag-IBIG contribution type and fixed amount
        $users = User::with(['employmentDetail.branch', 'salaryDetail'])->whereIn('id', $data['user_id'])->get()->keyBy('id');

        $result = [];
        foreach ($data['user_id'] as $userId) {
            // Default to 0 if not found
            $result[$userId] = [
                'employee_total' => 0,
                'total_contribution' => 0,
            ];

            // If "no", always 0
            if ($pagibigOption === 'no') {
                $result[$userId] = [
                    'employee_total' => 0,
                    'total_contribution' => 0,
                ];
                continue;
            }

            // If "full", always 200 (do not divide for semi-monthly)
            if ($pagibigOption === 'full') {
                $result[$userId] = [
                    'employee_total' => 200,
                    'total_contribution' => 200,
                ];
                continue;
            }

            // If "yes" or not set, use the normal computation
            $user = $users[$userId] ?? null;
            $branch = $user && $user->employmentDetail ? $user->employmentDetail->branch : null;
            $pagibigType = $branch && isset($branch->pagibig_contribution_type) ? $branch->pagibig_contribution_type : null;

            $amount = 200; // Default Pag-IBIG monthly contribution

            if ($pagibigType === 'system') {
                $salaryComputation = $branch->salary_computation_type ?? null;
                if ($salaryComputation === 'semi-monthly') {
                    $amount = 200 / 2;
                }
                $result[$userId] = [
                    'employee_total' => round($amount, 2),
                    'total_contribution' => round($amount, 2),
                ];
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
                    }
                }
            }
        }

        return $result;
    }

    // Withholding Tax Computation
    public function calculateBulkWithholdingTax($bulkAttendanceData, array $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cutoffOption)
    {
        // Preload user branch tax type and fixed amount
        $users = User::with(['employmentDetail.branch', 'salaryDetail'])->whereIn('id', $data['user_id'])->get()->keyBy('id');

        // Get pay components
        $basicPay = $this->calculateBulkBasicPay($bulkAttendanceData, $data, $salaryData);
        $overtimePay = $this->calculateBulkOvertimePay($data['user_id'], $data, $salaryData);
        $nightDiffPay = $this->calculateBulkNightDifferential($bulkAttendanceData, $data, $salaryData);
        $overtimeNightDiffPay = $this->calculateBulkOvertimeNightDiffPay($data['user_id'], $data, $salaryData);
        $holidayPay = $this->calculateBulkHolidayPay($bulkAttendanceData, $data, $salaryData);
        $leavePay = $this->calculateBulkLeavePay($data['user_id'], $data, $salaryData);
        $deductions = $this->calculateBulkDeductions($data['user_id'], $data, $salaryData);

        // Mandates
        $sss = $this->calculateBulkSSSContribution($data['user_id'], $data, $salaryData, $sssOption, $cutoffOption);
        $philhealth = $this->calculateBulkPhilhealthContribution($data['user_id'], $data, $salaryData, $philhealthOption, $cutoffOption);
        $pagibig = $this->calculateBulkPagibigContribution($data['user_id'], $data, $salaryData, $pagibigOption, $cutoffOption);

        $result = [];
        foreach ($data['user_id'] as $userId) {
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
                }
            }
        }

        return $result;
    }

    // User Deminimis Computation
    public function calculateBulkDeminimis(array $data)
    {
        // Get payroll period
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->endOfDay();

        // Get all active user deminimis within the payroll period
        $deminimis = UserDeminimis::whereIn('user_id', $data['user_id'])
            ->where('status', 'active')
            ->whereBetween('benefit_date', [$start, $end])
            ->get();

        $result = [];

        foreach ($data['user_id'] as $userId) {
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

    // Total Deductions Computation
    public function calculateBulkTotalDeductions(array $bulkAttendanceData, array $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cutoffOption)
    {
        // Get dynamic deductions (UserDeduction)
        $dynamicDeductions = $this->calculateBulkDeductions($bulkAttendanceData, $data, $salaryData);

        // Get system deductions (from bulk attendance)
        $tenantId = Auth::user()->tenant_id ?? null;
        $totals = $this->getBulkAttendanceData($tenantId, $data);

        // Get SSS, PhilHealth, and Pag-IBIG contributions
        $sss = $this->calculateBulkSSSContribution($bulkAttendanceData, $data, $salaryData, $sssOption, $cutoffOption);
        $philhealth = $this->calculateBulkPhilhealthContribution($bulkAttendanceData, $data, $salaryData, $philhealthOption, $cutoffOption);
        $pagibig = $this->calculateBulkPagibigContribution($bulkAttendanceData, $data, $salaryData, $pagibigOption, $cutoffOption);
        $withholdingTax = $this->calculateBulkWithholdingTax($bulkAttendanceData, $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cutoffOption);

        $result = [];

        foreach ($data['user_id'] as $id) {
            $dynamicTotal = $dynamicDeductions[$id]['deductions'] ?? 0;

            // SSS, PhilHealth, Pag-IBIG, and Withholding Tax deductions
            $sssAmt = $sss[$id]['employee_total'] ?? 0;
            $philhealthAmt = $philhealth[$id]['employee_total'] ?? 0;
            $pagibigAmt = $pagibig[$id]['employee_total'] ?? 0;
            $withholdingTaxAmt = $withholdingTax[$id]['withholding_tax'] ?? 0;

            // Calculating the total deductions
            $total = $dynamicTotal + $sssAmt + $philhealthAmt + $pagibigAmt + $withholdingTaxAmt;

            // Preparing the result
            $result[$id] = [
                'total_deductions' => round($total, 2),
                'dynamic_deductions' => round($dynamicTotal, 2),
                'sss_deduction' => round($sssAmt, 2),
                'philhealth_deduction' => round($philhealthAmt, 2),
                'pagibig_deduction' => round($pagibigAmt, 2),
                'withholding_tax' => round($withholdingTaxAmt, 2),
                'deduction_details' => $dynamicDeductions[$id]['deduction_details'] ?? [],
            ];
        }

        return $result;
    }

    // Total Earnings Computation
    public function calculateBulkTotalEarnings(array $bulkAttendanceData, array $data, $salaryData)
    {
        // Get overtime pay, night differential, and holiday pay for bulk users
        $overtimePay = $this->calculateBulkOvertimePay($bulkAttendanceData, $data, $salaryData);
        $nightDiffPay = $this->calculateBulkNightDifferential($bulkAttendanceData, $data, $salaryData);
        $overtimeNightDiffPay = $this->calculateBulkOvertimeNightDiffPay($bulkAttendanceData, $data, $salaryData);
        $holidayPay = $this->calculateBulkHolidayPay(
            $bulkAttendanceData,
            $data,
            $salaryData
        );
        $leavePay = $this->calculateBulkLeavePay($bulkAttendanceData, $data, $salaryData);
        $deminimis = $this->calculateBulkDeminimis($data);
        $earnings = $this->calculateBulkEarnings($bulkAttendanceData, $data, $salaryData);

        $result = [];

        foreach ($data['user_id'] as $userId) {
            $overtime = $overtimePay[$userId] ?? [];
            $nightDiff = $nightDiffPay[$userId] ?? [];
            $overtimeNightDiff = $overtimeNightDiffPay[$userId] ?? [];
            $holiday = $holidayPay[$userId]['holiday_pay_amount'] ?? 0;
            $leave = $leavePay[$userId]['total_leave_pay'] ?? 0;
            $deminimisTotal = $deminimis[$userId]['total_deminimis'] ?? 0;
            $earningsTotal = $earnings[$userId]['earnings'] ?? 0;
            $earningsDetails = $earnings[$userId]['earning_details'] ?? [];

            // Calculate total earnings
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
                    'ordinary_pay' => round($overtime['ordinary_pay'] ?? 0, 2),
                    'rest_day_pay' => round($overtime['rest_day_pay'] ?? 0, 2),
                    'holiday_pay' => round($overtime['holiday_pay'] ?? 0, 2),
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

    // Thirteenth Month Pay Computation
    protected function calculateBulkThirteenthMonthPay(array $bulkAttendanceData, array $data, $salaryData)
    {
        // Get basic pay data
        $basicPayData = $this->calculateBulkBasicPay($bulkAttendanceData, $data, $salaryData);

        // Get attendance totals for deductions
        $tenantId = Auth::user()->tenant_id ?? null;
        $totals = $this->getBulkAttendanceData($tenantId, $data);

        // Get paid leave
        $leavePayData = $this->calculateBulkLeavePay($bulkAttendanceData, $data, $salaryData);

        $result = [];
        foreach ($data['user_id'] as $userId) {
            $basicPay = $basicPayData[$userId]['basic_pay'] ?? 0;
            // You may need to compute late, undertime, absent deductions from $totals
            $late = $totals[$userId]['late_minutes'] ?? 0;
            $undertime = $totals[$userId]['undertime_minutes'] ?? 0;
            $absent = $totals[$userId]['absent_minutes'] ?? 0;
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

    // Net Pay Computation
    protected function calculateBulkNetPay(array $bulkAttendanceData, array $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cutoffOption)
    {
        // Calculate basic pay
        $basicPayData = $this->calculateBulkBasicPay($bulkAttendanceData, $data, $salaryData);

        // Calculate total earnings
        $earningsData = $this->calculateBulkTotalEarnings($bulkAttendanceData, $data, $salaryData);

        // Calculate total deductions
        $deductionsData = $this->calculateBulkTotalDeductions($bulkAttendanceData, $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cutoffOption);

        $results = [];
        foreach ($data['user_id'] as $userId) {
            $basicSalary = $basicPayData[$userId]['basic_pay'] ?? 0;
            $totalEarnings = $earningsData[$userId]['total_earnings'] ?? 0;
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
