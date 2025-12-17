<?php

namespace App\Http\Controllers\Tenant\Report;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Payroll;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PayrollReportController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function payrollReportIndex(Request $request)
    {
        $tenantId = $this->authUser()->tenant_id;
        $permission = PermissionHelper::get(54);
        // Get the date range from the request
        $dateRange = $request->input('date_range');
        $payrolls = Payroll::where('tenant_id', $tenantId)
            ->where('status', 'Paid')
            ->with('user.employmentDetail.branch')
            ->when($dateRange, function ($query) use ($dateRange) {
                $dates = explode(' - ', $dateRange);

                $startDate = Carbon::createFromFormat('d/m/Y', $dates[0])->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', $dates[1])->format('Y-m-d');

                return $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('payroll_period_start', [$startDate, $endDate])
                        ->orWhereBetween('payroll_period_end', [$startDate, $endDate]);
                });
            });

        // Filter by branch
        if ($request->has('branch_filter') && $request->branch_filter) {
            $payrolls = $payrolls->whereHas('user.employmentDetail.branch', function ($query) use ($request) {
                $query->where('id', $request->branch_filter);
            });
        }

        // Sort filter
        if ($request->has('sortby_filter') && $request->sortby_filter) {
            $sortBy = $request->sortby_filter;

            switch ($sortBy) {
                case 'ascending':
                    $payrolls = $payrolls->orderBy('payroll_period_start', 'asc');
                    break;
                case 'descending':
                    $payrolls = $payrolls->orderBy('payroll_period_start', 'desc');
                    break;
                case 'last_month':
                    $payrolls = $payrolls->whereBetween('payroll_period_start', [
                        now()->subMonth()->startOfMonth(),
                        now()->subMonth()->endOfMonth()
                    ]);
                    break;
                case 'last_7_days':
                    $payrolls = $payrolls->whereBetween('payroll_period_start', [
                        now()->subDays(7)->startOfDay(),
                        now()->endOfDay()
                    ]);
                    break;
                default:
                    break;
            }
        }

        // Group by user and aggregate the payroll data
        $payrollsGrouped = $payrolls->get()->groupBy('user_id')->map(function ($group) {
            return [
                'user' => $group->first()->user,
                'total_work_minutes' => $group->sum('total_worked_minutes'),
                'total_work_minutes_formatted' => $group->first()->getTotalWorkedMinutesFormattedAttribute(),
                'pay_period_start' => $group->min('payroll_period_start'),
                'pay_period_end' => $group->max('payroll_period_end'),
                'processor_name' => $group->first()->processor_name ?? 'Unknown Processor',
                'total_earnings' => $group->sum('total_earnings'),
                'total_deductions' => $group->sum('total_deductions'),

                // Earnings
                'basic_pay' => $group->sum('basic_pay'),
                'overtime_pay' => $group->sum('overtime_pay'),
                'overtime_night_diff_pay' => $group->sum('overtime_night_diff_pay'),
                'overtime_restday_pay' => $group->sum('overtime_restday_pay'),
                'leave_pay' => $group->sum('leave_pay'),
                'night_differential_pay' => $group->sum('night_differential_pay'),
                'holiday_pay' => $group->sum('holiday_pay'),
                'restday_pay' => $group->sum('restday_pay'),
                'earnings_breakdown' => $group->flatMap(function ($payroll) {
                    $earnings = is_string($payroll->earnings) ? json_decode($payroll->earnings, true) : $payroll->earnings;
                    return $earnings ?: [];
                })->groupBy('earning_type_id')->map(function ($items, $typeId) {
                    $totalAppliedAmount = collect($items)->sum('applied_amount');
                    $first = collect($items)->first();
                    return [
                        'earning_type_id' => $typeId,
                        'earning_type_name' => $first['earning_type_name'] ?? '',
                        'calculation_method' => $first['calculation_method'] ?? '',
                        'total_applied_amount' => $totalAppliedAmount,
                        'is_taxable' => $first['is_taxable'] ?? 0,
                        'frequency' => $first['frequency'] ?? '',
                        'status' => $first['status'] ?? '',
                    ];
                })->values(),

                // Deductions
                'sss_contribution' => $group->sum('sss_contribution'),
                'philhealth_contribution' => $group->sum('philhealth_contribution'),
                'pagibig_contribution' => $group->sum('pagibig_contribution'),
                'withholding_tax' => $group->sum('withholding_tax'),
                'late_deduction' => $group->sum('late_deduction'),
                'undertime_deduction' => $group->sum('undertime_deduction'),
                'absent_deduction' => $group->sum('absent_deduction'),
                'loan_deductions' => $group->sum('loan_deductions'),
                'deductions_breakdown' => $group->flatMap(function ($payroll) {
                    $deductions = is_string($payroll->deductions) ? json_decode($payroll->deductions, true) : $payroll->deductions;
                    return $deductions ?: [];
                })->groupBy('deduction_type_id')->map(function ($items, $typeId) {
                    $totalAppliedAmount = collect($items)->sum('applied_amount');
                    $first = collect($items)->first();
                    return [
                        'deduction_type_id' => $typeId,
                        'deduction_type_name' => $first['deduction_type_name'] ?? '',
                        'calculation_method' => $first['calculation_method'] ?? '',
                        'total_applied_amount' => $totalAppliedAmount,
                        'is_taxable' => $first['is_taxable'] ?? 0,
                        'frequency' => $first['frequency'] ?? '',
                        'status' => $first['status'] ?? '',
                    ];
                })->values(),

                // Salary
                'gross_pay' => $group->sum('gross_pay'),
                'net_salary' => $group->sum('net_salary'),

            ];
        });

        $branches = Branch::where('tenant_id', $tenantId)->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll report retrieved successfully.',
                'status' => 'success',
                'data' => [
                    'payrolls' => $payrollsGrouped->values(),
                    'branches' => $branches,
                    'filters' => [
                        'date_range' => $request->date_range ?? null,
                        'branch_filter' => $request->branch_filter ?? null,
                        'sortby_filter' => $request->sortby_filter ?? null,
                    ]
                ],
                'permission' => $permission
            ]);
        }

        return view('tenant.reports.payrollreport', compact('payrollsGrouped', 'branches','permission'));
    }
}
