<?php $page = 'sss-reports'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">SSS Reports</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">SSS Reports</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <div>
                            @if(in_array('Export', $permission))
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
                            @endif
                        </div>

                        {{-- Filters --}}
                        <form id="sssReportFilterForm" method="GET" action="{{ url()->current() }}"
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
                            @if(in_array('Read', $permission) || in_array('Create', $permission))
                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-search"></i> Search
                                </button>
                            </div>
                            @endif
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

            @if ($selectedBranch)
                <!-- Header Section -->
                <div class="mb-4 p-4 rounded shadow-sm bg-white border" id="reportHeader">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ asset('build/img/sss-logo.png') }}" alt="SSS Logo" style="height:48px;"
                            class="me-3">
                        <div>
                            <h3 class="fw-bold mb-0 text-primary">SSS REPORT</h3>
                            <small class="text-muted">Social Security System - Employee Summary</small>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <span class="fw-semibold text-secondary me-2">Branch Name:</span>
                                <span class="fs-13 text-dark">{{ $selectedBranch->name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="sssReportTable">
                        <thead>
                            <tr>
                                <th rowspan="3" class="text-center align-middle border border-dark">SEQ NO</th>
                                <th rowspan="3" class="text-center align-middle border border-dark">SSS NUMBER</th>
                                <th colspan="3" class="text-center border border-dark">NAME OF EMPLOYEES</th>
                                <th rowspan="3" class="text-center align-middle border border-dark">EMPLOYEE SHARE</th>
                                <th rowspan="3" class="text-center align-middle border border-dark">EMPLOYER SHARE</th>
                                <th rowspan="3" class="text-center align-middle border border-dark">TOTAL</th>
                            </tr>
                            <tr>
                                <th rowspan="2" class="text-center align-middle border border-dark">(Last Name)</th>
                                <th rowspan="2" class="text-center align-middle border border-dark">(First Name)</th>
                                <th rowspan="2" class="text-center align-middle border border-dark">(Middle Name)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payrollsGrouped as $userId => $group)
                                @php
                                    $employeeShare = $group['sss_contribution'] ?? 0;
                                    $employerShare = $group['sss_contribution_employer'] ?? 0;
                                    $totalShare = $employeeShare + $employerShare;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $group['user']->governmentDetail->sss_number ?? '' }}</td>
                                    <td>{{ $group['user']->personalInformation->last_name ?? '' }}</td>
                                    <td>{{ $group['user']->personalInformation->first_name ?? '' }}</td>
                                    <td>{{ $group['user']->personalInformation->middle_name ?? '' }}</td>
                                    <td class="text-end">{{ number_format($employeeShare, 2) }}</td>
                                    <td class="text-end">{{ number_format($employerShare, 2) }}</td>
                                    <td class="text-end">{{ number_format($totalShare, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            @php
                                $totalEmployeeShare = $payrollsGrouped->sum(function ($group) {
                                    return $group['sss_contribution'] ?? 0;
                                });
                                $totalEmployerShare = $payrollsGrouped->sum(function ($group) {
                                    return $group['sss_contribution_employer'] ?? 0;
                                });
                                $grandTotalShare = $totalEmployeeShare + $totalEmployerShare;
                            @endphp
                            <tr class="fw-bold bg-light">
                                <td colspan="5" class="text-center border border-dark">TOTAL</td>
                                <td class="text-end border border-dark">{{ number_format($totalEmployeeShare, 2) }}</td>
                                <td class="text-end border border-dark">{{ number_format($totalEmployerShare, 2) }}</td>
                                <td class="text-end border border-dark">{{ number_format($grandTotalShare, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center my-5">
                    <i class="ti ti-info-circle fs-2 mb-2"></i>
                    <div class="fw-bold">Please use the filters above to generate report.</div>
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
            const wb = XLSX.utils.book_new();
            const table = document.getElementById('sssReportTable');
            if (!table) return;

            const data = [];

            // Header rows
            data.push([
                'SEQ NO',
                'SSS NUMBER',
                'NAME OF EMPLOYEES', '', '',
                'EMPLOYEE SHARE',
                'EMPLOYER SHARE',
                'TOTAL'
            ]);
            data.push([
                '', '',
                '(Last Name)', '(First Name)', '(Middle Name)',
                '', '', ''
            ]);

            // Table body rows
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const rowData = [];
                cells.forEach(cell => {
                    rowData.push(cell.textContent.trim());
                });
                while (rowData.length < 8) rowData.push('');
                data.push(rowData);
            });

            // Table footer (totals)
            const tfootRows = table.querySelectorAll('tfoot tr');
            let footerRowIndex = null;
            tfootRows.forEach(tfoot => {
                const cells = tfoot.querySelectorAll('td');
                const footerData = [];
                cells.forEach(cell => {
                    footerData.push(cell.textContent.trim());
                });
                while (footerData.length < 8) footerData.push('');
                if (footerData.some(cell => cell !== '')) {
                    data.push(footerData);
                    footerRowIndex = data.length - 1;
                }
            });

            // Create worksheet from data
            const ws = XLSX.utils.aoa_to_sheet(data);

            // Merge cells for header groups and TOTAL row
            ws['!merges'] = [
                { s: { r: 0, c: 2 }, e: { r: 0, c: 4 } } // Merge "NAME OF EMPLOYEES"
            ];
            if (footerRowIndex !== null) {
                ws['!merges'].push({ s: { r: footerRowIndex, c: 0 }, e: { r: footerRowIndex, c: 4 } }); // Merge "TOTAL" in footer
            }

            // Set column widths
            ws['!cols'] = [
                { wch: 10 },  // SEQ NO
                { wch: 15 },  // SSS NUMBER
                { wch: 18 },  // Last Name
                { wch: 18 },  // First Name
                { wch: 18 },  // Middle Name
                { wch: 18 },  // EMPLOYEE SHARE
                { wch: 18 },  // EMPLOYER SHARE
                { wch: 18 }   // TOTAL
            ];

            XLSX.utils.book_append_sheet(wb, ws, 'SSS Report');

            const today = new Date();
            const filename =
                `SSS_Report_${today.getFullYear()}-${(today.getMonth()+1).toString().padStart(2,'0')}-${today.getDate().toString().padStart(2,'0')}.xlsx`;

            XLSX.writeFile(wb, filename);
        });
    </script>
@endpush
