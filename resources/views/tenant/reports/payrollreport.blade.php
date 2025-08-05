<?php $page = 'payroll-reports'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Payroll Reports</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Payroll Reports</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <div>
                            <div class="dropdown">
                                <a href="javascript:void(0);"
                                    class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <i class="ti ti-file-export me-1"></i>Export
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                            <i class="ti ti-file-type-pdf me-1"></i>Export as PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" id="exportToExcel" class="dropdown-item rounded-1">
                                            <i class="ti ti-file-type-xls me-1"></i>Export as Excel
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- Filters --}}
                        <form id="payrollReportFilterForm" method="GET" action="{{ url()->current() }}"
                            class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="form-group mb-0">
                                <div class="input-icon-end position-relative">
                                    <input type="text" id="dateRange_filter" name="date_range"
                                        class="form-control date-range bookingrange-filtered"
                                        placeholder="dd/mm/yyyy - dd/mm/yyyy" value="{{ request('date_range') }}">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-chevron-down"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <select name="branch_filter" id="branch_filter" class="select2 form-select">
                                    <option value="" selected>All Branches</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ request('branch_filter') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <select name="sortby_filter" id="sortby_filter" class="select2 form-select">
                                    <option value="" selected>All Sort By</option>
                                    <option value="ascending"
                                        {{ request('sortby_filter') == 'ascending' ? 'selected' : '' }}>Ascending</option>
                                    <option value="descending"
                                        {{ request('sortby_filter') == 'descending' ? 'selected' : '' }}>Descending</option>
                                    <option value="last_month"
                                        {{ request('sortby_filter') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                                    <option value="last_7_days"
                                        {{ request('sortby_filter') == 'last_7_days' ? 'selected' : '' }}>Last 7 days
                                    </option>
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-search"></i> Search
                                </button>
                            </div>
                        </form>

                    </div>

                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>

            @if (request()->has('date_range') || request()->has('branch_filter') || request()->has('sortby_filter'))
                {{-- Statistics --}}
                @php
                    $firstUserGroup = $payrollsGrouped->first();
                    $pay_period_start = $payrollsGrouped->min('pay_period_start');
                    $pay_period_end = $payrollsGrouped->max('pay_period_end');
                    $total_earnings = $payrollsGrouped->sum('total_earnings');
                    $total_deductions = $payrollsGrouped->sum('total_deductions');
                    $gross_pay = $payrollsGrouped->sum('gross_pay');
                    $net_pay = $payrollsGrouped->sum('net_pay');
                    $basic_pay = $payrollsGrouped->sum('basic_pay');
                    $prepared_by = $firstUserGroup['processor_name'] ?? 'Unknown Processor';
                @endphp

                <div class="printable-area">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table style="border-collapse: collapse; width: 350px; border: 1px solid ;">
                                    <tr>
                                        <td class="bg-primary text-white fw-medium"
                                            style="padding: 8px 16px; width: 140px; border: 1px solid ;">
                                            Pay Period Start</td>
                                        <td id="pay_period_start"
                                            style="background: #e5e6e8; padding: 8px 12px; border: 1px solid ;">
                                            {{ $pay_period_start }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bg-primary text-white fw-medium"
                                            style="padding: 8px 16px; border: 1px solid ;">
                                            Pay Period End</td>
                                        <td id="pay_period_end"
                                            style="background: #e5e6e8; padding: 8px 12px; border: 1px solid ;">
                                            {{ $pay_period_end }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bg-primary text-white fw-medium"
                                            style="padding: 8px 16px; border: 1px solid ;">
                                            Total Earnings</td>
                                        <td id="total_earnings"
                                            style="background: #e5e6e8; padding: 8px 12px; border: 1px solid ;">
                                            {{ number_format($total_earnings, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bg-primary text-white fw-medium"
                                            style="padding: 8px 16px; border: 1px solid ;">
                                            Total Deductions</td>
                                        <td id="total_deductions"
                                            style="background: #e5e6e8; padding: 8px 12px; border: 1px solid ;">
                                            {{ number_format($total_deductions, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bg-primary text-white fw-medium"
                                            style="padding: 8px 16px; border: 1px solid ;">
                                            Prepared By</td>
                                        <td id="prepared_by"
                                            style="background: #e5e6e8; padding: 8px 12px; border: 1px solid ;">
                                            {{ $prepared_by }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Table Summary --}}
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h5 class="mb-0">Payroll Summary</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="custom-datatable-filter table-responsive px-3 mt-3">
                                <table class="table datatable-filtered mb-0" id="payrollTable">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="bg-primary text-white align-middle"
                                                style="border: 1px solid black;">BRANCH</th>
                                            <th rowspan="2" class="bg-primary text-white align-middle"
                                                style="border: 1px solid black;">EMPLOYEE NAME</th>
                                            <th rowspan="2" class="bg-primary text-white align-middle"
                                                style="border: 1px solid black;">WORKED HOURS</th>
                                            <th colspan="8" class="text-center align-middle"
                                                style="border: 1px solid;">
                                                EARNINGS</th>
                                            <th colspan="7" class="text-center align-middle"
                                                style="border: 1px solid;">
                                                DEDUCTIONS</th>
                                            <th rowspan="2" class="bg-primary text-white align-middle"
                                                style="border: 1px solid;">GROSS PAY </th>
                                            <th rowspan="2" class="bg-success text-white align-middle"
                                                style="border: 1px solid;">NET PAY</th>
                                        </tr>
                                        <tr>
                                            {{-- Earnings --}}
                                            <th class="align-middle" style="border: 1px solid;">Basic Salary</th>
                                            <th class="align-middle" style="border: 1px solid;">Overtime Pay</th>
                                            <th class="align-middle" style="border: 1px solid;">Overtime Night Diff</th>
                                            <th class="align-middle" style="border: 1px solid;">Leave Pay</th>
                                            <th class="align-middle" style="border: 1px solid;">Night Differential</th>
                                            <th class="align-middle" style="border: 1px solid;">Holiday Pay</th>
                                            <th class="align-middle" style="border: 1px solid;">Rest Day Pay</th>
                                            <th class="align-middle" style="border: 1px solid;">Other Earnings</th>

                                            {{-- Deductions --}}
                                            <th class="align-middle" style="border: 1px solid;">SSS</th>
                                            <th class="align-middle" style="border: 1px solid;">PhilHealth</th>
                                            <th class="align-middle" style="border: 1px solid;">Pag-IBIG</th>
                                            <th class="align-middle" style="border: 1px solid;">Tax</th>
                                            <th class="align-middle" style="border: 1px solid;">Late/Undertime</th>
                                            <th class="align-middle" style="border: 1px solid;">Absent</th>
                                            <th class="align-middle" style="border: 1px solid;">Other Deductions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($payrollsGrouped as $userId => $group)
                                            @php
                                                $otherEarningsTotal = 0;
                                                if (
                                                    isset($group['earnings_breakdown']) &&
                                                    count($group['earnings_breakdown'])
                                                ) {
                                                    foreach ($group['earnings_breakdown'] as $earning) {
                                                        $otherEarningsTotal += $earning['total_applied_amount'] ?? 0;
                                                    }
                                                }

                                                $otherDeductionsTotal = 0;

                                                if (
                                                    isset($group['deductions_breakdown']) &&
                                                    count($group['deductions_breakdown'])
                                                ) {
                                                    foreach ($group['deductions_breakdown'] as $deduction) {
                                                        $otherDeductionsTotal +=
                                                            $deduction['total_applied_amount'] ?? 0;
                                                    }
                                                }

                                                $otherDeductionsTotal = 0;
                                            @endphp

                                            <tr>
                                                <td style="border: 1px solid;">
                                                    {{ $group['user']->employmentDetail->branch->name ?? '' }}</td>
                                                <td style="border: 1px solid;">
                                                    <div class="d-flex align-items-center">
                                                        <a href="{{ url('employee-details') }}" class="avatar avatar-md"
                                                            data-bs-toggle="modal" data-bs-target="#view_details"><img
                                                                src="{{ asset('storage/' . ($group['user']->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                                                                class="img-fluid rounded-circle" alt="img"></a>
                                                        <div class="ms-2">
                                                            <p class="text-dark mb-0"><a
                                                                    href="{{ url('employee-details') }}"
                                                                    data-bs-toggle="modal" data-bs-target="#view_details">
                                                                    {{ $group['user']->personalInformation->last_name ?? '' }}
                                                                    {{ $group['user']->personalInformation->suffix ?? '' }},
                                                                    {{ $group['user']->personalInformation->first_name ?? '' }}
                                                                    {{ $group['user']->personalInformation->middle_name ?? '' }}</a>
                                                            </p>
                                                            <span
                                                                class="fs-12">{{ $group['user']->employmentDetail->department->department_name ?? '' }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="border: 1px solid;">
                                                    {{ $group['total_work_minutes_formatted'] ?? 0 }}
                                                </td>

                                                {{-- Earnings --}}
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['basic_pay'], 2) }}
                                                </td>
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['overtime_pay'] + $group['overtime_restday_pay'] + $group['overtime_night_diff_pay'], 2) }}
                                                </td>
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['overtime_night_diff_pay'], 2) }}
                                                </td>
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['leave_pay'], 2) }}
                                                </td>
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['night_differential_pay'], 2) }}
                                                </td>
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['holiday_pay'], 2) }}
                                                </td>
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['restday_pay'], 2) }}
                                                </td>

                                                <td style="border: 1px solid;">
                                                    {{ number_format($otherEarningsTotal, 2) }}
                                                </td>

                                                {{-- Deductions --}}
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['sss_contribution'], 2) }}
                                                </td>
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['philhealth_contribution'], 2) }}
                                                </td>
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['pagibig_contribution'], 2) }}
                                                </td>
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['withholding_tax'], 2) }}
                                                </td>
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['late_deduction'] + $group['undertime_deduction'], 2) }}
                                                </td>
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['absent_deduction'], 2) }}
                                                </td>
                                                <td style="border: 1px solid;">
                                                    {{ number_format($otherDeductionsTotal, 2) }}
                                                </td>

                                                {{-- Gross Pay --}}
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['gross_pay'], 2) }}
                                                </td>
                                                {{-- Net Pay --}}
                                                <td style="border: 1px solid;">
                                                    {{ number_format($group['net_salary'], 2) }}
                                                </td>
                                            </tr>
                                        @endforeach

                                        <!-- Totals Row -->
                                        <tr class="fw-bold align-middle" style="background: #f4f6fa;">
                                            <td class="text-primary fw-bold text-center"
                                                style="border: 1px solid; vertical-align: middle;">TOTAL</td>
                                            <td style="border: 1px solid; background: #f4f6fa;"></td>

                                            @php
                                                $totalMinutes = $payrollsGrouped->sum('total_work_minutes');
                                                $hours = intdiv($totalMinutes, 60);
                                                $mins = $totalMinutes % 60;
                                                $parts = [];
                                                if ($hours > 0) {
                                                    $hourLabel = $hours === 1 ? 'hr' : 'hrs';
                                                    $parts[] = "{$hours} {$hourLabel}";
                                                }
                                                if ($mins > 0) {
                                                    $minLabel = $mins === 1 ? 'min' : 'mins';
                                                    $parts[] = "{$mins} {$minLabel}";
                                                }
                                                $totalWorkedFormatted = count($parts) ? implode(' ', $parts) : '0 min';
                                            @endphp

                                            <td style="border: 1px solid; background: #f4f6fa;">
                                                {{ $totalWorkedFormatted }}
                                            </td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">₱
                                                {{ number_format($payrollsGrouped->sum('basic_pay'), 2) }}</td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">
                                                ₱
                                                {{ number_format($payrollsGrouped->sum('overtime_pay') + $payrollsGrouped->sum('overtime_restday_pay'), 2) }}
                                            </td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">₱
                                                {{ number_format($payrollsGrouped->sum('overtime_night_diff_pay'), 2) }}
                                            </td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">₱
                                                {{ number_format($payrollsGrouped->sum('leave_pay'), 2) }}</td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">₱
                                                {{ number_format($payrollsGrouped->sum('night_differential_pay'), 2) }}
                                            </td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">₱
                                                {{ number_format($payrollsGrouped->sum('holiday_pay'), 2) }}</td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">₱
                                                {{ number_format($payrollsGrouped->sum('restday_pay'), 2) }}</td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">₱
                                                {{ number_format($payrollsGrouped->sum('other_earnings'), 2) }}</td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">₱
                                                {{ number_format($payrollsGrouped->sum('sss_contribution'), 2) }}</td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">₱
                                                {{ number_format($payrollsGrouped->sum('philhealth_contribution'), 2) }}
                                            </td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">₱
                                                {{ number_format($payrollsGrouped->sum('pagibig_contribution'), 2) }}</td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">₱
                                                {{ number_format($payrollsGrouped->sum('withholding_tax'), 2) }}</td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">₱
                                                {{ number_format($payrollsGrouped->sum('late_deduction') + $payrollsGrouped->sum('undertime_deduction'), 2) }}
                                            </td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">₱
                                                {{ number_format($payrollsGrouped->sum('absent_deduction'), 2) }}</td>
                                            <td class="text-end" style="border: 1px solid; background: #f4f6fa;">₱
                                                {{ number_format($payrollsGrouped->sum('other_deductions'), 2) }}</td>
                                            <td class="text-end fw-bold" style="border: 1px solid; background: #eaf7ea;">₱
                                                {{ number_format($payrollsGrouped->sum('gross_pay'), 2) }}</td>
                                            <td class="text-end fw-bold text-success"
                                                style="border: 1px solid; background: #eaf7ea;">₱
                                                {{ number_format($payrollsGrouped->sum('net_salary'), 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info text-center my-5">
                    <i class="ti ti-info-circle fs-2 mb-2"></i>
                    <div class="fw-bold">Please use the filters above to generate payroll report.</div>
                </div>
            @endif


        </div>
        @include('layout.partials.footer-company')

    </div>

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <!-- Include SheetJS library for Excel export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        if ($('.bookingrange-filtered').length > 0) {
            // Set default to "This Month"
            var start = moment().startOf('month');
            var end = moment().endOf('month');

            function booking_range(start, end) {
                $('.bookingrange-filtered span').html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
                $('.bookingrange-filtered').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
            }

            $('.bookingrange-filtered').daterangepicker({
                startDate: start,
                endDate: end,
                locale: {
                    format: 'DD/MM/YYYY'
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                    'Next Year': [moment().add(1, 'year').startOf('year'), moment().add(1, 'year').endOf('year')]
                }
            }, booking_range);

            booking_range(start, end);
        }
    </script>

    <script>
        document.getElementById('exportToExcel').addEventListener('click', function() {
            // Create a new workbook
            const wb = XLSX.utils.book_new();

            // Get the table
            const table = document.getElementById('payrollTable');

            // Create data array for Excel
            const data = [];

            // Add headers - first row
            const headerRow1 = ['BRANCH', 'EMPLOYEE NAME', 'WORKED HOURS'];
            // Add EARNINGS columns
            for (let i = 0; i < 8; i++) {
                headerRow1.push('EARNINGS');
            }
            // Add DEDUCTIONS columns
            for (let i = 0; i < 7; i++) {
                headerRow1.push('DEDUCTIONS');
            }
            headerRow1.push('GROSS PAY', 'NET PAY');
            data.push(headerRow1);

            // Add headers - second row
            const headerRow2 = ['', '', ''];
            headerRow2.push('Basic Salary', 'Overtime Pay', 'Overtime Night Diff', 'Leave Pay',
                'Night Differential', 'Holiday Pay', 'Rest Day Pay', 'Other Earnings');
            headerRow2.push('SSS', 'PhilHealth', 'Pag-IBIG', 'Tax', 'Late/Undertime', 'Absent', 'Other Deductions');
            headerRow2.push('', '');
            data.push(headerRow2);

            // Get table rows (skip header rows)
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const rowData = [];
                const cells = row.querySelectorAll('td');

                cells.forEach((cell, index) => {
                    let cellText = cell.textContent.trim();

                    // For employee name column, extract just the text without HTML
                    if (index === 1) {
                        const nameElement = cell.querySelector('p a');
                        if (nameElement) {
                            cellText = nameElement.textContent.trim();
                        }
                    }

                    // Clean up currency symbols and formatting
                    cellText = cellText.replace('₱', '').replace(/,/g, '');

                    rowData.push(cellText);
                });

                data.push(rowData);
            });

            // Create worksheet from data
            const ws = XLSX.utils.aoa_to_sheet(data);

            // Set column widths
            const colWidths = [{
                    wch: 15
                }, // Branch
                {
                    wch: 25
                }, // Employee Name
                {
                    wch: 12
                }, // Worked Hours
                {
                    wch: 12
                }, {
                    wch: 12
                }, {
                    wch: 15
                }, {
                    wch: 12
                }, {
                    wch: 15
                }, {
                    wch: 12
                }, {
                    wch: 12
                }, {
                    wch: 12
                }, // Earnings
                {
                    wch: 10
                }, {
                    wch: 12
                }, {
                    wch: 10
                }, {
                    wch: 10
                }, {
                    wch: 15
                }, {
                    wch: 10
                }, {
                    wch: 15
                }, // Deductions
                {
                    wch: 12
                }, {
                    wch: 12
                } // Gross Pay, Net Pay
            ];
            ws['!cols'] = colWidths;

            // Merge cells for header groups
            ws['!merges'] = [{
                    s: {
                        r: 0,
                        c: 3
                    },
                    e: {
                        r: 0,
                        c: 10
                    }
                }, // EARNINGS
                {
                    s: {
                        r: 0,
                        c: 11
                    },
                    e: {
                        r: 0,
                        c: 17
                    }
                }, // DEDUCTIONS
                {
                    s: {
                        r: 0,
                        c: 0
                    },
                    e: {
                        r: 1,
                        c: 0
                    }
                }, // BRANCH
                {
                    s: {
                        r: 0,
                        c: 1
                    },
                    e: {
                        r: 1,
                        c: 1
                    }
                }, // EMPLOYEE NAME
                {
                    s: {
                        r: 0,
                        c: 2
                    },
                    e: {
                        r: 1,
                        c: 2
                    }
                }, // WORKED HOURS
                {
                    s: {
                        r: 0,
                        c: 18
                    },
                    e: {
                        r: 1,
                        c: 18
                    }
                }, // GROSS PAY
                {
                    s: {
                        r: 0,
                        c: 19
                    },
                    e: {
                        r: 1,
                        c: 19
                    }
                } // NET PAY
            ];

            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Payroll Report');

            // Generate filename with current date
            const today = new Date();
            const filename =
                `Payroll_Report_${today.getFullYear()}-${(today.getMonth()+1).toString().padStart(2,'0')}-${today.getDate().toString().padStart(2,'0')}.xlsx`;

            // Save the file
            XLSX.writeFile(wb, filename);
        });
    </script>
@endpush
