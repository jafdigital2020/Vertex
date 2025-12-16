<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Http\Controllers\DataAccessController;

class PayrollExport
{
    protected $authUser;
    protected $filters;

    public function __construct($authUser, $filters = [])
    {
        $this->authUser = $authUser;
        $this->filters = $filters;
    }

    /**
     * Convert minutes to hours and minutes format
     */
    private function formatMinutesToHours($minutes)
    {
        if (!$minutes) return '0 hrs 0 mins';

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return $hours . ' hrs ' . $remainingMinutes . ' mins';
    }

    public function getData()
    {
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($this->authUser);

        $query = $accessData['payrolls'];

        // Apply filters
        if (!empty($this->filters['dateRange'])) {
            try {
                [$start, $end] = explode(' - ', $this->filters['dateRange']);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();
                
                // Filter by payroll period instead of transaction_date to include rich seeder data
                $query->where(function($q) use ($start, $end) {
                    $q->whereBetween('payroll_period_start', [$start, $end])
                      ->orWhereBetween('payroll_period_end', [$start, $end])
                      ->orWhere(function($subQ) use ($start, $end) {
                          $subQ->where('payroll_period_start', '<=', $start)
                               ->where('payroll_period_end', '>=', $end);
                      });
                });
            } catch (\Exception $e) {
                // Invalid date format, skip filter
            }
        }

        if (!empty($this->filters['branch'])) {
            $query->whereHas('user.employmentDetail', function ($q) {
                $q->where('branch_id', $this->filters['branch']);
            });
        }

        if (!empty($this->filters['department'])) {
            $query->whereHas('user.employmentDetail', function ($q) {
                $q->where('department_id', $this->filters['department']);
            });
        }

        if (!empty($this->filters['designation'])) {
            $query->whereHas('user.employmentDetail', function ($q) {
                $q->where('designation_id', $this->filters['designation']);
            });
        }

        $results = $query->with([
            'user.personalInformation',
            'user.employmentDetail.branch',
            'user.employmentDetail.department',
            'user.employmentDetail.designation'
        ])
        // Order by richness of data - prefer records with earnings/allowances/deminimis
        ->orderByRaw('CASE WHEN (earnings IS NOT NULL AND earnings != "[]" AND earnings != "") OR (allowance IS NOT NULL AND allowance != "[]" AND allowance != "") OR (deminimis IS NOT NULL AND deminimis != "[]" AND deminimis != "") THEN 0 ELSE 1 END')
        ->orderBy('created_at', 'desc')
        ->get();

        // Remove duplicates by user_id, keeping the richest data for each user
        $uniqueResults = collect();
        $seenUserIds = [];
        
        foreach ($results as $payroll) {
            if (!in_array($payroll->user_id, $seenUserIds)) {
                $uniqueResults->push($payroll);
                $seenUserIds[] = $payroll->user_id;
            }
        }
        
        return $uniqueResults;
    }

    public function getHeaders()
    {
        return [
            'No.',
            'Employee ID',
            'Employee Name',
            'Branch',
            'Department',
            'Designation',
            'Payroll Type',
            'Payroll Period',
            'Period Start',
            'Period End',
            'Total Hours',
            'Total Late',
            'Total Undertime',
            'Overtime Hours',
            'Night Diff Hours',
            'Basic Pay',
            'Holiday Pay',
            'Overtime Pay',
            'Night Diff Pay',
            'Rest Day Pay',
            'Overtime Rest Day Pay',
            'Leave Pay',
            'Allowances',
            'Other Earnings',
            'Gross Pay',
            'Total Earnings',
            'Late Deduction',
            'Undertime Deduction',
            'Absent Deduction',
            'SSS Contribution',
            'PhilHealth Contribution',
            'Pag-IBIG Contribution',
            'Withholding Tax',
            'Loan Deductions',
            'Other Deductions',
            'Total Deductions',
            'Taxable Income',
            'Net Salary',
            'Payment Date',
            'Status',
            'Processed Date'
        ];
    }

    public function formatRow($payroll, $index)
    {
        $user = $payroll->user;
        $personalInfo = $user->personalInformation;
        $employmentDetail = $user->employmentDetail;

        // Decode JSON fields if needed
        $allowances = is_string($payroll->allowance) ? json_decode($payroll->allowance, true) : ($payroll->allowance ?? []);
        $earnings = is_string($payroll->earnings) ? json_decode($payroll->earnings, true) : ($payroll->earnings ?? []);
        $loanDeductions = is_string($payroll->loan_deductions) ? json_decode($payroll->loan_deductions, true) : ($payroll->loan_deductions ?? []);
        $deductions = is_string($payroll->deductions) ? json_decode($payroll->deductions, true) : ($payroll->deductions ?? []);
        $deminimis = is_string($payroll->deminimis) ? json_decode($payroll->deminimis, true) : ($payroll->deminimis ?? []);

        $totalAllowances = collect($allowances)->sum('amount') ?? 0;
        $otherEarnings = collect($earnings)->sum('applied_amount') ?? 0;
        $totalLoanDeductions = collect($loanDeductions)->sum('amount') ?? 0;
        $otherDeductions = collect($deductions)->sum('amount') ?? 0;

        return [
            $index + 1,
            $user->employmentDetail->employee_id ?? 'N/A',
            ($personalInfo->last_name ?? '') .
            ($personalInfo->suffix ? ' ' . $personalInfo->suffix : '') .
            ', ' . ($personalInfo->first_name ?? '') .
            ' ' . ($personalInfo->middle_name ?? ''),
            $employmentDetail->branch->name ?? 'N/A',
            $employmentDetail->department->department_name ?? 'N/A',
            $employmentDetail->designation->designation_name ?? 'N/A',
            ucfirst(str_replace('_', ' ', $payroll->payroll_type ?? 'N/A')),
            $payroll->payroll_period ?? 'N/A',
            $payroll->payroll_period_start ?
                Carbon::parse($payroll->payroll_period_start)->format('M d, Y') : 'N/A',
            $payroll->payroll_period_end ?
                Carbon::parse($payroll->payroll_period_end)->format('M d, Y') : 'N/A',
            // Time breakdown
            $this->formatMinutesToHours($payroll->total_worked_minutes ?? 0),
            $this->formatMinutesToHours($payroll->total_late_minutes ?? 0),
            $this->formatMinutesToHours($payroll->total_undertime_minutes ?? 0),
            $this->formatMinutesToHours($payroll->total_overtime_minutes ?? 0),
            $this->formatMinutesToHours($payroll->total_night_differential_minutes ?? 0),
            // Earnings breakdown
            number_format($payroll->basic_pay ?? 0, 2),
            number_format($payroll->holiday_pay ?? 0, 2),
            number_format($payroll->overtime_pay ?? 0, 2),
            number_format($payroll->night_differential_pay ?? 0, 2),
            number_format($payroll->restday_pay ?? 0, 2),
            number_format($payroll->overtime_restday_pay ?? 0, 2),
            number_format($payroll->leave_pay ?? 0, 2),
            number_format($totalAllowances, 2),
            number_format($otherEarnings, 2),
            number_format($payroll->gross_pay ?? 0, 2),
            number_format($payroll->total_earnings ?? 0, 2),
            // Deductions breakdown
            number_format($payroll->late_deduction ?? 0, 2),
            number_format($payroll->undertime_deduction ?? 0, 2),
            number_format($payroll->absent_deduction ?? 0, 2),
            number_format($payroll->sss_contribution ?? 0, 2),
            number_format($payroll->philhealth_contribution ?? 0, 2),
            number_format($payroll->pagibig_contribution ?? 0, 2),
            number_format($payroll->withholding_tax ?? 0, 2),
            number_format($totalLoanDeductions, 2),
            number_format($otherDeductions, 2),
            number_format($payroll->total_deductions ?? 0, 2),
            number_format($payroll->taxable_income ?? 0, 2),
            number_format($payroll->net_salary ?? 0, 2),
            $payroll->payment_date ?
                Carbon::parse($payroll->payment_date)->format('M d, Y') : 'N/A',
            ucfirst($payroll->status ?? 'N/A'),
            $payroll->created_at ?
                $payroll->created_at->format('M d, Y h:i A') : 'N/A'
        ];
    }

    /**
     * Get summary totals including time totals and detailed breakdowns
     */
    public function getSummaryTotals($payrolls)
    {
        $totalAllowances = 0;
        $totalOtherEarnings = 0;
        $totalLoanDeductions = 0;
        $totalOtherDeductions = 0;

        foreach ($payrolls as $payroll) {
            $allowances = is_string($payroll->allowance) ? json_decode($payroll->allowance, true) : ($payroll->allowance ?? []);
            $earnings = is_string($payroll->earnings) ? json_decode($payroll->earnings, true) : ($payroll->earnings ?? []);
            $loanDeductions = is_string($payroll->loan_deductions) ? json_decode($payroll->loan_deductions, true) : ($payroll->loan_deductions ?? []);
            $deductions = is_string($payroll->deductions) ? json_decode($payroll->deductions, true) : ($payroll->deductions ?? []);

            $totalAllowances += collect($allowances)->sum('amount');
            $totalOtherEarnings += collect($earnings)->sum('applied_amount');
            $totalLoanDeductions += collect($loanDeductions)->sum('amount');
            $totalOtherDeductions += collect($deductions)->sum('amount');
        }

        return [
            'total_employees' => $payrolls->count(),
            // Time totals
            'total_worked_minutes' => $payrolls->sum('total_worked_minutes'),
            'total_late_minutes' => $payrolls->sum('total_late_minutes'),
            'total_undertime_minutes' => $payrolls->sum('total_undertime_minutes'),
            'total_overtime_minutes' => $payrolls->sum('total_overtime_minutes'),
            'total_night_diff_minutes' => $payrolls->sum('total_night_differential_minutes'),
            // Formatted time versions
            'total_worked_hours_formatted' => $this->formatMinutesToHours($payrolls->sum('total_worked_minutes')),
            'total_late_hours_formatted' => $this->formatMinutesToHours($payrolls->sum('total_late_minutes')),
            'total_undertime_hours_formatted' => $this->formatMinutesToHours($payrolls->sum('total_undertime_minutes')),
            'total_overtime_hours_formatted' => $this->formatMinutesToHours($payrolls->sum('total_overtime_minutes')),
            'total_night_diff_hours_formatted' => $this->formatMinutesToHours($payrolls->sum('total_night_differential_minutes')),
            // Earnings breakdown
            'total_basic_pay' => $payrolls->sum('basic_pay'),
            'total_holiday_pay' => $payrolls->sum('holiday_pay'),
            'total_overtime_pay' => $payrolls->sum('overtime_pay'),
            'total_night_differential_pay' => $payrolls->sum('night_differential_pay'),
            'total_restday_pay' => $payrolls->sum('restday_pay'),
            'total_overtime_restday_pay' => $payrolls->sum('overtime_restday_pay'),
            'total_leave_pay' => $payrolls->sum('leave_pay'),
            'total_allowances' => $totalAllowances,
            'total_other_earnings' => $totalOtherEarnings,
            'total_gross_pay' => $payrolls->sum('gross_pay'),
            'total_earnings' => $payrolls->sum('total_earnings'),
            // Deductions breakdown
            'total_late_deduction' => $payrolls->sum('late_deduction'),
            'total_undertime_deduction' => $payrolls->sum('undertime_deduction'),
            'total_absent_deduction' => $payrolls->sum('absent_deduction'),
            'total_sss_contribution' => $payrolls->sum('sss_contribution'),
            'total_philhealth_contribution' => $payrolls->sum('philhealth_contribution'),
            'total_pagibig_contribution' => $payrolls->sum('pagibig_contribution'),
            'total_withholding_tax' => $payrolls->sum('withholding_tax'),
            'total_loan_deductions' => $totalLoanDeductions,
            'total_other_deductions' => $totalOtherDeductions,
            'total_deductions' => $payrolls->sum('total_deductions'),
            // Final totals
            'total_taxable_income' => $payrolls->sum('taxable_income'),
            'total_net_pay' => $payrolls->sum('net_salary'),
        ];
    }

    /**
     * Get comprehensive payroll insights for reporting
     */
    public function getPayrollInsights($payrolls)
    {
        $summaryTotals = $this->getSummaryTotals($payrolls);
        
        return [
            // Cost Analysis
            'average_salary_per_employee' => $summaryTotals['total_employees'] > 0 ? 
                round($summaryTotals['total_net_pay'] / $summaryTotals['total_employees'], 2) : 0,
            'average_gross_per_employee' => $summaryTotals['total_employees'] > 0 ? 
                round($summaryTotals['total_gross_pay'] / $summaryTotals['total_employees'], 2) : 0,
            'total_labor_cost' => $summaryTotals['total_gross_pay'],
            
            // Efficiency Metrics
            'overtime_percentage' => $summaryTotals['total_gross_pay'] > 0 ? 
                round(($summaryTotals['total_overtime_pay'] / $summaryTotals['total_gross_pay']) * 100, 2) : 0,
            'deduction_rate' => $summaryTotals['total_gross_pay'] > 0 ? 
                round(($summaryTotals['total_deductions'] / $summaryTotals['total_gross_pay']) * 100, 2) : 0,
            'take_home_rate' => $summaryTotals['total_gross_pay'] > 0 ? 
                round(($summaryTotals['total_net_pay'] / $summaryTotals['total_gross_pay']) * 100, 2) : 0,
                
            // Compliance Metrics
            'government_contributions_total' => 
                $summaryTotals['total_sss_contribution'] + 
                $summaryTotals['total_philhealth_contribution'] + 
                $summaryTotals['total_pagibig_contribution'],
            'government_contribution_rate' => $summaryTotals['total_gross_pay'] > 0 ? 
                round((($summaryTotals['total_sss_contribution'] + 
                       $summaryTotals['total_philhealth_contribution'] + 
                       $summaryTotals['total_pagibig_contribution']) / $summaryTotals['total_gross_pay']) * 100, 2) : 0,
            
            // Time Analysis
            'average_hours_per_employee' => $summaryTotals['total_employees'] > 0 ? 
                round($summaryTotals['total_worked_minutes'] / $summaryTotals['total_employees'] / 60, 2) : 0,
            'productivity_score' => $summaryTotals['total_worked_minutes'] > 0 ? 
                round((($summaryTotals['total_worked_minutes'] - $summaryTotals['total_late_minutes'] - $summaryTotals['total_undertime_minutes']) / $summaryTotals['total_worked_minutes']) * 100, 2) : 100,
                
            // Financial Health Indicators
            'basic_pay_percentage' => $summaryTotals['total_gross_pay'] > 0 ? 
                round(($summaryTotals['total_basic_pay'] / $summaryTotals['total_gross_pay']) * 100, 2) : 0,
            'allowances_percentage' => $summaryTotals['total_gross_pay'] > 0 ? 
                round(($summaryTotals['total_allowances'] / $summaryTotals['total_gross_pay']) * 100, 2) : 0,
        ];
    }

    /**
     * Format earnings breakdown for display
     */
    private function formatEarningsBreakdown($earnings)
    {
        if (is_string($earnings)) {
            $earnings = json_decode($earnings, true);
        }
        if (empty($earnings)) {
            return 'None';
        }

        $breakdown = [];
        foreach ($earnings as $earning) {
            $earningType = $earning['earning_type_name'] ?? 'Unknown';
            $amount = $earning['applied_amount'] ?? $earning['default_amount'] ?? 0;
            $method = $earning['calculation_method'] ?? '';
            $taxable = ($earning['is_taxable'] ?? 1) ? 'Taxable' : 'Non-taxable';
            
            // Format: "Earning Name: PHP Amount (Method, Tax Status)"
            $earningDetail = $earningType . ': PHP ' . number_format($amount, 2) . ' (' . ucfirst($method) . ', ' . $taxable . ')';
            $breakdown[] = $earningDetail;
        }

        return implode(' | ', $breakdown);
    }

    /**
     * Format deminimis breakdown for display
     */
    private function formatDeminimisBreakdown($deminimis)
    {
        if (is_string($deminimis)) {
            $deminimis = json_decode($deminimis, true);
        }
        if (empty($deminimis)) {
            return 'None';
        }

        $breakdown = [];
        foreach ($deminimis as $item) {
            $itemName = $item['benefit_name'] ?? $item['name'] ?? 'Unknown';
            $amount = $item['amount'] ?? 0;
            $breakdown[] = $itemName . ': PHP ' . number_format($amount, 2);
        }

        return implode(' | ', $breakdown);
    }

    /**
     * Get all unique earning types across all payroll records
     */
    public function getAllEarningTypes($payrolls)
    {
        $earningTypes = [];
        foreach ($payrolls as $payroll) {
            $earnings = is_string($payroll->earnings) ? json_decode($payroll->earnings, true) : ($payroll->earnings ?? []);
            foreach ($earnings as $earning) {
                $earningType = $earning['earning_type_name'] ?? 'Unknown';
                if (!in_array($earningType, $earningTypes)) {
                    $earningTypes[] = $earningType;
                }
            }
        }
        return $earningTypes;
    }

    /**
     * Get all unique deminimis types across all payroll records
     */
    public function getAllDeminimisTypes($payrolls)
    {
        $deminimisTypes = [];
        foreach ($payrolls as $payroll) {
            $deminimis = is_string($payroll->deminimis) ? json_decode($payroll->deminimis, true) : ($payroll->deminimis ?? []);
            foreach ($deminimis as $item) {
                $itemName = $item['benefit_name'] ?? $item['name'] ?? 'Unknown';
                if (!in_array($itemName, $deminimisTypes)) {
                    $deminimisTypes[] = $itemName;
                }
            }
        }
        return $deminimisTypes;
    }

    /**
     * Get specific earning amount for employee by earning type
     */
    public function getEarningAmount($payroll, $earningType)
    {
        $earnings = is_string($payroll->earnings) ? json_decode($payroll->earnings, true) : ($payroll->earnings ?? []);
        foreach ($earnings as $earning) {
            if (($earning['earning_type_name'] ?? '') === $earningType) {
                return $earning['applied_amount'] ?? $earning['default_amount'] ?? 0;
            }
        }
        return 0;
    }

    /**
     * Get specific deminimis amount for employee by type
     */
    public function getDeminimisAmount($payroll, $deminimisType)
    {
        $deminimis = is_string($payroll->deminimis) ? json_decode($payroll->deminimis, true) : ($payroll->deminimis ?? []);
        foreach ($deminimis as $item) {
            if (($item['benefit_name'] ?? $item['name'] ?? '') === $deminimisType) {
                return $item['amount'] ?? 0;
            }
        }
        return 0;
    }

    /**
     * Get detailed earnings breakdown for comprehensive display
     */
    public function getDetailedEarningsBreakdown($payroll)
    {
        $earnings = is_string($payroll->earnings) ? json_decode($payroll->earnings, true) : ($payroll->earnings ?? []);
        if (empty($earnings)) {
            return [];
        }

        $details = [];
        foreach ($earnings as $earning) {
            $details[] = [
                'type_id' => $earning['earning_type_id'] ?? null,
                'name' => $earning['earning_type_name'] ?? 'Unknown',
                'method' => $earning['calculation_method'] ?? 'N/A',
                'default_amount' => number_format($earning['default_amount'] ?? 0, 2),
                'override_amount' => number_format($earning['user_amount_override'] ?? 0, 2),
                'applied_amount' => number_format($earning['applied_amount'] ?? 0, 2),
                'is_taxable' => ($earning['is_taxable'] ?? 1) ? 'Yes' : 'No',
                'frequency' => $earning['frequency'] ?? 'N/A',
                'status' => $earning['status'] ?? 'active'
            ];
        }

        return $details;
    }

    /**
     * Create a simple earnings summary for table display
     */
    public function getSimpleEarningsDisplay($earnings)
    {
        if (is_string($earnings)) {
            $earnings = json_decode($earnings, true);
        }
        if (empty($earnings)) {
            return 'None';
        }

        $simple = [];
        foreach ($earnings as $earning) {
            $name = $earning['earning_type_name'] ?? 'Unknown';
            $amount = $earning['applied_amount'] ?? $earning['default_amount'] ?? 0;
            $simple[] = $name . ': ₱' . number_format($amount, 2);
        }

        return implode('; ', $simple);
    }

    /**
     * Get earnings summary with tax information
     */
    public function getEarningsWithTaxInfo($earnings)
    {
        if (is_string($earnings)) {
            $earnings = json_decode($earnings, true);
        }
        if (empty($earnings)) {
            return ['taxable_total' => 0, 'non_taxable_total' => 0, 'breakdown' => 'None'];
        }

        $taxableTotal = 0;
        $nonTaxableTotal = 0;
        $breakdown = [];

        foreach ($earnings as $earning) {
            $amount = $earning['applied_amount'] ?? $earning['default_amount'] ?? 0;
            $name = $earning['earning_type_name'] ?? 'Unknown';
            $isTaxable = $earning['is_taxable'] ?? 1;

            if ($isTaxable) {
                $taxableTotal += $amount;
                $breakdown[] = $name . ': ₱' . number_format($amount, 2) . ' (T)';
            } else {
                $nonTaxableTotal += $amount;
                $breakdown[] = $name . ': ₱' . number_format($amount, 2) . ' (NT)';
            }
        }

        return [
            'taxable_total' => $taxableTotal,
            'non_taxable_total' => $nonTaxableTotal,
            'breakdown' => implode(' | ', $breakdown)
        ];
    }
}