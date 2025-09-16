<?php
// filepath: app/Exports/PayrollExport.php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\User;
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
                $query->whereBetween('transaction_date', [$start, $end]);
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

        return $query->with([
            'user.personalInformation',
            'user.employmentDetail.branch',
            'user.employmentDetail.department',
            'user.employmentDetail.designation'
        ])->get();
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
            'Basic Pay',
            'Gross Pay',
            'Total Earnings',
            'Total Deductions',
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

        return [
            $index + 1,
            $user->employee_id ?? 'N/A',
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
            // ✅ NEW: Total Hours (converted from minutes)
            $this->formatMinutesToHours($payroll->total_worked_minutes ?? 0),
            // ✅ NEW: Total Late (converted from minutes)
            $this->formatMinutesToHours($payroll->total_late_minutes ?? 0),
            // ✅ NEW: Total Undertime (converted from minutes)
            $this->formatMinutesToHours($payroll->total_undertime_minutes ?? 0),
            number_format($payroll->basic_pay ?? 0, 2),
            number_format($payroll->gross_pay ?? 0, 2),
            number_format($payroll->total_earnings ?? 0, 2),
            number_format($payroll->total_deductions ?? 0, 2),
            number_format($payroll->net_salary ?? 0, 2),
            $payroll->payment_date ?
                Carbon::parse($payroll->payment_date)->format('M d, Y') : 'N/A',
            ucfirst($payroll->status ?? 'N/A'),
            $payroll->created_at ?
                $payroll->created_at->format('M d, Y h:i A') : 'N/A'
        ];
    }

    /**
     * Get summary totals including time totals
     */
    public function getSummaryTotals($payrolls)
    {
        return [
            'total_employees' => $payrolls->count(),
            'total_earnings' => $payrolls->sum('total_earnings'),
            'total_deductions' => $payrolls->sum('total_deductions'),
            'total_net_pay' => $payrolls->sum('net_salary'),
            'total_basic_pay' => $payrolls->sum('basic_pay'),
            'total_gross_pay' => $payrolls->sum('gross_pay'),
            // ✅ NEW: Time totals
            'total_worked_minutes' => $payrolls->sum('total_worked_minutes'),
            'total_late_minutes' => $payrolls->sum('total_late_minutes'),
            'total_undertime_minutes' => $payrolls->sum('total_undertime_minutes'),
            'total_overtime_minutes' => $payrolls->sum('total_overtime_minutes'),
            // Formatted versions
            'total_worked_hours_formatted' => $this->formatMinutesToHours($payrolls->sum('total_worked_minutes')),
            'total_late_hours_formatted' => $this->formatMinutesToHours($payrolls->sum('total_late_minutes')),
            'total_undertime_hours_formatted' => $this->formatMinutesToHours($payrolls->sum('total_undertime_minutes')),
            'total_overtime_hours_formatted' => $this->formatMinutesToHours($payrolls->sum('total_overtime_minutes')),
        ];
    }
}
