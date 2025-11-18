<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\ThirteenthMonthPay;
use App\Http\Controllers\DataAccessController;

class ThirteenthMonthPayExport
{
    protected $authUser;
    protected $filters;

    public function __construct($authUser, $filters = [])
    {
        $this->authUser = $authUser;
        $this->filters = $filters;
    }

    public function getData()
    {
        $tenantId = $this->authUser->tenant_id ?? null;

        $query = ThirteenthMonthPay::where('tenant_id', $tenantId)
            ->with([
                'user.personalInformation',
                'user.employmentDetail.branch',
                'user.employmentDetail.department',
                'user.employmentDetail.designation'
            ]);

        // Apply filters
        if (!empty($this->filters['dateRange'])) {
            try {
                [$start, $end] = explode(' - ', $this->filters['dateRange']);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();
                $query->whereBetween('payment_date', [$start, $end]);
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

        if (!empty($this->filters['year'])) {
            $query->where('year', $this->filters['year']);
        }

        // ✅ NEW: Add status filter
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('year', 'desc')
            ->orderBy('payment_date', 'desc')
            ->get();
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
            'Coverage Period',
            'Year',
            'Total Basic Pay',
            'Total Deductions',
            '13th Month Pay',
            'Payment Date',
            'Processed By',
            'Status',
            'Transaction Date'
        ];
    }

    public function formatRow($payroll, $index)
    {
        $user = $payroll->user;
        $personalInfo = $user->personalInformation;
        $employmentDetail = $user->employmentDetail;

        // Format coverage period
        $coveragePeriod = Carbon::create($payroll->from_year ?? $payroll->year, $payroll->from_month)->format('F Y') .
            ' - ' .
            Carbon::create($payroll->to_year ?? $payroll->year, $payroll->to_month)->format('F Y');

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
            $coveragePeriod,
            $payroll->year ?? 'N/A',
            number_format($payroll->total_basic_pay ?? 0, 2),
            number_format($payroll->total_deductions ?? 0, 2),
            number_format($payroll->total_thirteenth_month ?? 0, 2),
            $payroll->payment_date ?
                Carbon::parse($payroll->payment_date)->format('M d, Y') : 'N/A',
            $payroll->processor_name ?? 'N/A',
            ucfirst($payroll->status ?? 'N/A'),
            $payroll->created_at ?
                $payroll->created_at->format('M d, Y h:i A') : 'N/A'
        ];
    }

    /**
     * Get summary totals
     */
    public function getSummaryTotals($payrolls)
    {
        return [
            'total_employees' => $payrolls->count(),
            'total_basic_pay' => $payrolls->sum('total_basic_pay'),
            'total_deductions' => $payrolls->sum('total_deductions'),
            'total_thirteenth_month' => $payrolls->sum('total_thirteenth_month'),
        ];
    }
}
