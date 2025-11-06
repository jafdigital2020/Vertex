<?php $page = 'generated-payslips'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Generated Payslip</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Generated Payslip</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if (in_array('Export', $permission))
                        {{-- <div class="me-2 mb-2">
                            <div class="dropdown">
                                <a href="javascript:void(0);"
                                    class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <i class="ti ti-file-export me-1"></i>Export
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                                class="ti ti-file-type-pdf me-1"></i>Download Template</a>
                                    </li>

                                </ul>
                            </div>
                        </div> --}}
                    @endif

                    @if (in_array('Create', $permission))
                        <div class="mb-2 d-flex gap-2">
                            <a href="#" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                                data-bs-target="#upload_payslip">
                                <i class="ti ti-file-upload me-2"></i> Upload Payslip
                            </a>
                        </div>
                    @endif

                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="row">

                <div class="d-flex mb-2 align-items-center gap-2">
                    <select id="summary-range" class="form-select form-select-sm w-auto">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                    <select id="summary-year" class="form-select form-select-sm w-auto"></select>
                    <select id="summary-month" class="form-select form-select-sm w-auto"></select>
                </div>

                <!-- Total Exponses -->
                <div class="col-xl-6 d-flex">
                    <div class="row flex-fill">
                        <div class="col-md-6 d-flex">
                            <div class="card flex-fill">
                                <div class="card-body">
                                    <div
                                        class="d-flex align-items-center justify-content-between bg-light border rounded p-2 mb-2">
                                        <div>
                                            <span class="fs-14 fw-normal text-truncate mb-1">Total Earnings</span>
                                            <h5 id="total-earnings">₱0.00</h5>
                                        </div>
                                        <a href="#"
                                            class="avatar avatar-md avatar-rounded bg-transparent-primary border border-primary">
                                            <span class="text-primary"><i class="ti ti-brand-shopee"></i></span>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex">
                            <div class="card flex-fill">
                                <div class="card-body">
                                    <div
                                        class="d-flex align-items-center justify-content-between bg-light border rounded p-2 mb-2">
                                        <div>
                                            <span class="fs-14 fw-normal text-truncate mb-1">Total Deductions</span>
                                            <h5 id="total-deductions">₱0.00</h5>
                                        </div>
                                        <a href="#"
                                            class="avatar avatar-md avatar-rounded bg-transparent-danger border border-danger">
                                            <span class="text-danger"><i class="ti ti-brand-shopee"></i></span>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 d-flex">
                            <div class="card flex-fill">
                                <div class="card-body">
                                    <div
                                        class="d-flex align-items-center justify-content-between bg-light border rounded p-2 mb-2">
                                        <div class="">
                                            <span class="fs-14 fw-normal text-truncate mb-1">Total Payroll</span>
                                            <h5 id="total-payroll"></h5>
                                        </div>
                                        <a href="#"
                                            class="avatar avatar-md avatar-rounded bg-transparent-success border border-success">
                                            <span class="text-success"><i class="ti ti-brand-shopee"></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex">
                            <div class="card flex-fill">
                                <div class="card-body">
                                    <div
                                        class="d-flex align-items-center justify-content-between bg-light border rounded p-2 mb-2">
                                        <div class="">
                                            <span class="fs-14 fw-normal text-truncate mb-1">Total Net Pay</span>
                                            <h5 id="total-net-pay">₱0.00</h5>
                                        </div>
                                        <a href="#"
                                            class="avatar avatar-md avatar-rounded bg-transparent-skyblue border border-skyblue">
                                            <span class="text-skyblue"><i class="ti ti-brand-shopee"></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Total Exponses -->

                <!-- Total Exponses -->
                <div class="col-xl-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header border-0 pb-0">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="d-flex align-items-center ">
                                    <span class="me-2"><i class="ti ti-chart-area-line text-danger"></i></span>
                                    <h5 id="total-payroll">Payroll</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body py-0">
                            <div id="payslip-chart"></div>
                        </div>
                    </div>
                </div>
                <!-- /Total Exponses -->
            </div>

            {{-- Page Links --}}
            <div class="payroll-btns mb-3">
                <a href="{{ route('generatedPayslipIndex') }}" class="btn btn-white active border me-2">Generated
                    Payslips</a>
                <a href="{{ route('thirteenthMonthPayslipadminIndex') }}" class="btn btn-white  border me-2">Thirteenth
                    Month Payslips</a>
            </div>

            <!-- Generated Payslip list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Generated Payslips</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="dropdown me-2">
                            <button class="btn btn-primary dropdown-toggle" type="button" id="bulkActionsDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Bulk Actions
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="bulkActionsDropdown">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" id="bulkRevertPayslip">
                                        <i class="ti ti-repeat me-1"></i>Revert to Pending
                                    </a>
                                </li>
                                {{-- <li>
                                    <a class="dropdown-item" href="javascript:void(0);" id="bulkDownloadPayslip">
                                        <i class="ti ti-download me-1"></i>Download
                                    </a>
                                </li> --}}
                                <li>
                                    <a class="dropdown-item text-danger" href="javascript:void(0);"
                                        id="bulkDeletePayroll">
                                        <i class="ti ti-trash me-1"></i>Delete
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select"
                                onchange="filter()" style="width:150px;">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select"
                                onchange="filter()" style="width:150px;">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select"
                                onchange="filter()" style="width:150px;">
                                <option value="" selected>All Designations</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable" id="generatedPayslipsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Employee</th>
                                    <th class="text-center">Branch</th>
                                    <th class="text-center">Payroll Period</th>
                                    <th class="text-center">Total Earnings</th>
                                    <th class="text-center">Total Deductions</th>
                                    <th class="text-center">Net Pay</th>
                                    <th class="text-center">Processed By:</th>
                                    <th class="text-center">Trasanction Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="generatedPayslipsTableBody">
                                @foreach ($payslips as $payslip)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input payroll-checkbox" type="checkbox"
                                                    value="{{ $payslip->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-md" data-bs-toggle="modal"
                                                    data-bs-target="#view_details"><img
                                                        src="{{ asset('storage/' . ($payslip->user->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                                                        class="img-fluid rounded-circle" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
                                                            data-bs-target="#view_details">
                                                            {{ $payslip->user->personalInformation->last_name ?? '' }}
                                                            {{ $payslip->user->personalInformation->suffix ?? '' }},
                                                            {{ $payslip->user->personalInformation->first_name ?? '' }}
                                                            {{ $payslip->user->personalInformation->middle_name ?? '' }}</a>
                                                    </p>
                                                    <span
                                                        class="fs-12">{{ $payslip->user->employmentDetail->department->department_name ?? '' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">{{ $payslip->user->employmentDetail->branch->name ?? '' }}
                                        </td>
                                        <td class="text-center">
                                            @if ($payslip->payroll_period_start && $payslip->payroll_period_end)
                                                {{ $payslip->payroll_period_start }} - {{ $payslip->payroll_period_end }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-center">₱{{ number_format($payslip->total_earnings, 2) }}</td>
                                        <td class="text-center">₱{{ number_format($payslip->total_deductions, 2) }}</td>
                                        <td class="text-danger text-center">₱{{ number_format($payslip->net_salary, 2) }}
                                        </td>
                                        <td class="text-center">{{ $payslip->processor_name }}</td>
                                        <td class="text-center">{{ $payslip->payment_date }}</td>
                                        <td class="text-center">
                                            @if ($payslip->status === 'Paid')
                                                <span
                                                    class="badge d-inline-flex align-items-center badge-xs badge-success">
                                                    <i class="ti ti-point-filled me-1"></i>{{ $payslip->status ?? 'N/A' }}
                                                </span>
                                            @else
                                                <span class="badge d-inline-flex align-items-center badge-xs badge-danger">
                                                    <i class="ti ti-point-filled me-1"></i>{{ $payslip->status ?? 'N/A' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                <a href="{{ route('generatedPayslips', $payslip->id) }}"
                                                    class="me-2 edit-payroll-btn" title="View Payslip">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                @if (in_array('Update', $permission))
                                                    <a href="#" class="btn-revert" data-bs-toggle="modal"
                                                        data-bs-target="#revert_payslip" data-id="{{ $payslip->id }}"
                                                        data-name="{{ $payslip->user->personalInformation->full_name }}"
                                                        title="Edit/Rollback"><i class="ti ti-repeat"></i></a>
                                                @endif
                                                @if (in_array('Delete', $permission))
                                                    <a href="javascript:void(0);" class="btn-delete"
                                                        data-bs-toggle="modal" data-bs-target="#delete_payslip"
                                                        title="Delete" data-id="{{ $payslip->id }}"
                                                        data-name="{{ $payslip->user->personalInformation->full_name }}"><i
                                                            class="ti ti-trash"></i></a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /Generated Payslip list -->

        </div>


        @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    <!-- Upload Payslip Modal -->
    <div class="modal fade" id="upload_payslip" tabindex="-1" aria-labelledby="uploadPayslipLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadPayslipLabel">
                        <i class="ti ti-upload me-2"></i>Upload Previous Payslips
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Instructions -->
                    <div class="alert alert-info mb-3">
                        <h6 class="alert-heading"><i class="ti ti-info-circle me-2"></i>Instructions</h6>
                        <ol class="mb-0 ps-3">
                            <li>Download the CSV template below</li>
                            <li>Fill in your payroll data:
                                <ul class="mt-1">
                                    <li><strong>Employee ID</strong> - Will be matched to system records</li>
                                    <li><strong>Payroll Month</strong> - Can be name (e.g., "January") or number (1-12)</li>
                                    <li><strong>Payroll Year</strong> - Must be 4-digit year (e.g., 2024)</li>
                                    <li><strong>Dates</strong> - Can be in any format (2024-01-15, 01/15/2024, Jan 15 2024,
                                        etc.)</li>
                                </ul>
                            </li>
                            <li>Upload the completed CSV file</li>
                        </ol>
                        <div class="alert alert-info mt-2 mb-0">
                            <strong>Supported Date Formats:</strong> YYYY-MM-DD, MM/DD/YYYY, DD/MM/YYYY, Month DD YYYY, etc.
                        </div>
                    </div>

                    <!-- Download Template -->
                    <div class="mb-3">
                        <a href="{{ route('downloadPayslipTemplate') }}" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-download me-1"></i>Download CSV Template
                        </a>
                    </div>

                    <!-- Upload Form -->
                    <form id="uploadPayslipForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="payslip_file" class="form-label">
                                Select CSV File <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control" id="payslip_file" name="payslip_file"
                                accept=".csv,.txt" required>
                            <div class="form-text text-danger">
                                <strong>Required columns:</strong> Employee ID, Payroll Month, Payroll Year, Payroll Period
                                Start, Payroll Period End, Payment Date, Transaction Date, Basic Pay, Gross Pay, Net Salary
                            </div>
                        </div>

                        <!-- File Preview -->
                        <div id="filePreview" class="mb-3" style="display: none;">
                            <div class="alert alert-secondary">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-file-text fs-4 me-3"></i>
                                    <div>
                                        <h6 class="mb-0" id="fileName"></h6>
                                        <small class="text-muted" id="fileInfo"></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress -->
                        <div id="uploadProgress" class="mb-3" style="display: none;">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    style="width: 100%">
                                    Processing...
                                </div>
                            </div>
                            <p class="text-muted mt-2 mb-0">
                                <i class="ti ti-loader"></i> <span id="progressMessage">Uploading and processing
                                    payslips...</span>
                            </p>
                        </div>

                        <!-- Success Message -->
                        <div id="successAlert" class="alert alert-success" style="display: none;">
                            <i class="ti ti-check me-2"></i><span id="successMessage"></span>
                        </div>

                        <!-- Error Message -->
                        <div id="errorAlert" class="alert alert-danger" style="display: none;">
                            <i class="ti ti-alert-triangle me-2"></i><span id="errorMessage"></span>
                        </div>

                        <!-- Error Details Table -->
                        <div id="errorDetails" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-danger">
                                        <tr>
                                            <th>Row</th>
                                            <th>Employee ID</th>
                                            <th>Error</th>
                                        </tr>
                                    </thead>
                                    <tbody id="errorTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-light" id="uploadBtn">
                        <i class="ti ti-upload me-1"></i>Upload & Process
                    </button>
                </div>
            </div>
        </div>
    </div>

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    {{-- Payroll Chart --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        $('#dateRange_filter').on('apply.daterangepicker', function(ev, picker) {
            filter();
        });

        function filter() {
            var dateRange = $('#dateRange_filter').val();
            var branch = $('#branch_filter').val();
            var department = $('#department_filter').val();
            var designation = $('#designation_filter').val();

            $.ajax({
                url: '{{ route('generatedPayslipIndex-filter') }}',
                type: 'GET',
                data: {
                    branch: branch,
                    department: department,
                    designation: designation,
                    dateRange: dateRange,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#generatedPayslipsTable').DataTable().destroy();
                        $('#generatedPayslipsTableBody').html(response.html);
                        $('#generatedPayslipsTable').DataTable();
                    } else if (response.status === 'error') {
                        toastr.error(response.message || 'Something went wrong.');
                    }
                },
                error: function(xhr) {
                    let message = 'An unexpected error occurred.';
                    if (xhr.status === 403) {
                        message = 'You are not authorized to perform this action.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    toastr.error(message);
                }
            });
        }
    </script>

    <script>
        function populateDropdown($select, items, placeholder = 'Select') {
            $select.empty();
            $select.append(`<option value="">All ${placeholder}</option>`);
            items.forEach(item => {
                $select.append(`<option value="${item.id}">${item.name}</option>`);
            });
        }

        $(document).ready(function() {

            $('#branch_filter').on('input', function() {
                const branchId = $(this).val();

                $.get('/api/filter-from-branch', {
                    branch_id: branchId
                }, function(res) {
                    if (res.status === 'success') {
                        populateDropdown($('#department_filter'), res.departments, 'Departments');
                        populateDropdown($('#designation_filter'), res.designations,
                            'Designations');
                    }
                });
            });


            $('#department_filter').on('input', function() {
                const departmentId = $(this).val();
                const branchId = $('#branch_filter').val();

                $.get('/api/filter-from-department', {
                    department_id: departmentId,
                    branch_id: branchId,
                }, function(res) {
                    if (res.status === 'success') {
                        if (res.branch_id) {
                            $('#branch_filter').val(res.branch_id).trigger('change');
                        }
                        populateDropdown($('#designation_filter'), res.designations,
                            'Designations');
                    }
                });
            });

            $('#designation_filter').on('change', function() {
                const designationId = $(this).val();
                const branchId = $('#branch_filter').val();
                const departmentId = $('#department_filter').val();

                $.get('/api/filter-from-designation', {
                    designation_id: designationId,
                    branch_id: branchId,
                    department_id: departmentId
                }, function(res) {
                    if (res.status === 'success') {
                        if (designationId === '') {
                            populateDropdown($('#designation_filter'), res.designations,
                                'Designations');
                        } else {
                            $('#branch_filter').val(res.branch_id).trigger('change');
                            $('#department_filter').val(res.department_id).trigger('change');
                        }
                    }
                });
            });

        });
    </script>

    {{-- Payroll Summaries --}}
    <script>
        $(document).ready(function() {
            // --- 1. Populate Year & Month Dropdowns ---
            const $year = $('#summary-year');
            const $month = $('#summary-month');
            const $range = $('#summary-range');

            let currentYear = (new Date()).getFullYear();
            let currentMonth = (new Date()).getMonth() + 1; // JS months are 0-based, so +1
            let startYear = 2020;

            $year.empty();
            for (let y = currentYear; y >= startYear; y--) {
                $year.append(`<option value="${y}">${y}</option>`);
            }
            $year.val(currentYear); // set AFTER populating

            $month.empty();
            const months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            months.forEach(function(m, idx) {
                $month.append(`<option value="${idx + 1}">${m}</option>`);
            });
            $month.val(currentMonth);

            // --- 2. Show/hide month dropdown ---
            function updateMonthVisibility() {
                if ($range.val() === 'monthly') {
                    $month.show();
                } else {
                    $month.hide();
                }
            }
            updateMonthVisibility();

            $range.on('change', updateMonthVisibility);

            // --- 3. Fetch and Update Summary Cards ---
            function fetchPayrollSummary() {
                let range = $range.val();
                let year = $year.val();
                let month = $month.val();

                let params = {
                    range: range,
                    year: year
                };
                if (range === 'monthly') params.month = month;

                $.get('/api/payroll/generated-payslips/payroll-summary', params, function(res) {
                    $('#total-earnings').text('₱' + parseFloat(res.totalEarnings).toLocaleString(
                        undefined, {
                            minimumFractionDigits: 2
                        }));
                    $('#total-deductions').text('₱' + parseFloat(res.totalDeductions).toLocaleString(
                        undefined, {
                            minimumFractionDigits: 2
                        }));
                    // Add this if you have a net salary card
                    $('#total-net-pay').text('₱' + parseFloat(res.totalNetSalary).toLocaleString(
                        undefined, {
                            minimumFractionDigits: 2
                        }));

                    $('#total-payroll').text('Payrolls: ' + res.totalPayrollCount);
                    // Optionally update a sub-label:
                    let label = (range === 'yearly') ?
                        res.year :
                        `${months[res.month - 1]} ${res.year}`;
                    $('#summary-label').text(label);

                    // --- 4. Fetch and Render Chart ---
                    fetchPayrollChart(range, year, month);
                });
            }

            // --- 5. Fetch and Render Chart (ApexCharts) ---
            function fetchPayrollChart(range, year, month) {
                let params = {
                    range: range,
                    year: year
                };
                if (range === 'monthly') params.month = month;

                $.get('/api/payroll/generated-payslips/payroll-chart', params, function(response) {
                    // response should have: months (x-axis) and totals (y-axis)
                    var options = {
                        chart: {
                            type: 'area',
                            height: 270
                        },
                        series: [{
                            name: 'Net Salary',
                            data: response.totals
                        }],
                        xaxis: {
                            categories: response.months
                        },
                        colors: ['#ff6384'],
                        dataLabels: {
                            enabled: false
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.5,
                                opacityTo: 0.1
                            }
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2
                        },
                        tooltip: {
                            y: {
                                formatter: function(val) {
                                    return "₱ " + parseFloat(val).toLocaleString();
                                }
                            }
                        }
                    };
                    if (window.payslipChart) window.payslipChart.destroy();
                    window.payslipChart = new ApexCharts(document.querySelector("#payslip-chart"), options);
                    window.payslipChart.render();
                });
            }

            // --- 6. Listen for changes and refresh ---
            $range.on('change', fetchPayrollSummary);
            $year.on('change', fetchPayrollSummary);
            $month.on('change', fetchPayrollSummary);

            // --- 7. Initial load ---
            fetchPayrollSummary();
        });
    </script>

    {{-- Delete Payslip/Payroll --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let payslipDeleteId = null;
            const userPayslipConfirmBtn = document.getElementById('userPayslipConfirmBtn');
            const payslipPlaceholder = document.getElementById('payslipPlaceholder');

            // Use delegation to listen for delete button clicks
            document.addEventListener('click', function(e) {
                const button = e.target.closest('.btn-delete');
                if (!button) return;

                payslipDeleteId = button.getAttribute('data-id');
                const payslipName = button.getAttribute('data-name');

                if (payslipPlaceholder) {
                    payslipPlaceholder.textContent = payslipName;
                }
            });

            // Confirm delete
            userPayslipConfirmBtn?.addEventListener('click', function() {
                if (!payslipDeleteId) return;

                fetch(`/api/payroll/generated-payslips/delete/${payslipDeleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${authToken}`,
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Payslip deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_payslip'));
                            deleteModal.hide();
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message || "Error deleting payslip.");
                            });
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        toastr.error("Server error.");
                    });
            });
        });
    </script>

    {{-- Revert Payslip/Payroll --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let payslipRevertId = null;
            const revertPayslipConfirmBtn = document.getElementById('revertPayslipConfirmBtn');
            const payslipRevertPlaceholder = document.getElementById('payslipRevertPlaceholder');

            document.addEventListener('click', function(e) {
                const revertButton = e.target.closest('.btn-revert');
                if (!revertButton) return;

                payslipRevertId = revertButton.getAttribute('data-id');
                const payslipRevertName = revertButton.getAttribute('data-name');

                if (payslipRevertPlaceholder) {
                    payslipRevertPlaceholder.textContent = payslipRevertName;
                }
            });

            revertPayslipConfirmBtn?.addEventListener('click', function() {
                if (!payslipRevertId) return;

                fetch(`/api/payroll/generated-payslips/revert/${payslipRevertId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${authToken}`,
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Payslip reverted to pending status successfully.");

                            const revertModal = bootstrap.Modal.getInstance(document.getElementById(
                                'revert_payslip'));
                            revertModal.hide();
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message || "Error reverting payslip.");
                            });
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        toastr.error("Server error.");
                    });
            });
        });
    </script>

    {{-- Bulk Actions --}}
    <script>
        // Bulk Delete
        $(document).on('click', '#bulkDeletePayroll', function() {
            let ids = $('.payroll-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (ids.length === 0) {
                toastr.warning('Please select at least one payslip to delete.');
                return;
            }

            if (!confirm('Are you sure you want to delete the selected payslip(s)?')) {
                return;
            }

            $.ajax({
                url: '/api/payroll/generated-payslips/bulk-delete',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                data: {
                    payroll_ids: ids
                },
                success: function(res) {
                    toastr.success('Selected payslip(s) deleted successfully.');
                    setTimeout(() => window.location.reload(), 1000);
                },
                error: function(err) {
                    toastr.error('An error occurred while deleting payslip(s).');
                }
            });
        });

        // Bulk Revert
        $(document).on('click', '#bulkRevertPayslip', function() {
            let ids = $('.payroll-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (ids.length === 0) {
                toastr.warning('Please select at least one payslip to revert.');
                return;
            }

            if (!confirm('Are you sure you want to revert the selected payslip(s) to pending status?')) {
                return;
            }

            $.ajax({
                url: '/api/payroll/generated-payslips/bulk-revert',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                data: {
                    payroll_ids: ids
                },
                success: function(res) {
                    toastr.success('Selected payslip(s) reverted to pending status successfully.');
                    setTimeout(() => window.location.reload(), 1000);
                },
                error: function(err) {
                    toastr.error('An error occurred while reverting payslip(s).');
                }
            });
        });

        // Upload Payslip Functionality
        let statusCheckInterval = null;

        $('#payslip_file').on('change', function() {
            const file = this.files[0];
            if (file) {
                const fileSize = (file.size / 1024).toFixed(2);
                $('#fileName').text(file.name);
                $('#fileInfo').text(`Size: ${fileSize} KB`);
                $('#filePreview').show();
            } else {
                $('#filePreview').hide();
            }
        });

        $('#uploadBtn').on('click', function() {
            const formData = new FormData($('#uploadPayslipForm')[0]);
            const file = $('#payslip_file')[0].files[0];

            if (!file) {
                toastr.error('Please select a CSV file before uploading.');
                return;
            }

            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                toastr.error('Your file is too large. Please upload a file smaller than 10MB.');
                return;
            }

            if (!file.name.match(/\.(csv|txt)$/i)) {
                toastr.error('Please upload a CSV file. Other file types are not supported.');
                return;
            }

            // Hide previous messages
            $('#successAlert, #errorAlert, #errorDetails').hide();

            // Show progress
            $('#uploadProgress').show();
            $('#uploadBtn').prop('disabled', true).html('<i class="ti ti-loader me-1"></i> Uploading...');

            $.ajax({
                url: '{{ route('uploadPayslips') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#progressMessage').text('File uploaded. Processing ' + response.total_rows +
                            ' records...');
                        startStatusCheck();
                    } else {
                        showError(response.message || 'The upload failed. Please try again.');
                        resetUploadState();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message ||
                        'Something went wrong while uploading your file. Please try again.';
                    showError(message);
                    resetUploadState();
                }
            });
        });

        function startStatusCheck() {
            statusCheckInterval = setInterval(function() {
                $.ajax({
                    url: '{{ route('checkImportStatus') }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'success') {
                            clearInterval(statusCheckInterval);
                            showSuccess(response.message);
                            resetUploadState();
                            setTimeout(() => window.location.reload(), 2000);
                        } else if (response.status === 'completed_with_errors') {
                            clearInterval(statusCheckInterval);
                            showSuccess(response.message);
                            displayErrors(response.failed_rows);
                            resetUploadState();
                        } else if (response.status === 'failed') {
                            clearInterval(statusCheckInterval);
                            showError(response.message);
                            resetUploadState();
                        } else {
                            $('#progressMessage').text(response.message);
                        }
                    },
                    error: function() {
                        clearInterval(statusCheckInterval);
                        showError(
                            'We could not check the status of your import. Please refresh the page to see if your payslips were imported.'
                            );
                        resetUploadState();
                    }
                });
            }, 3000); // Check every 3 seconds
        }

        function displayErrors(errors) {
            if (!errors || errors.length === 0) return;

            const tbody = $('#errorTableBody');
            tbody.empty();

            errors.forEach(function(error) {
                const row = `
                    <tr>
                        <td>${error.row}</td>
                        <td>${error.employee_id || 'N/A'}</td>
                        <td class="text-danger">${error.error}</td>
                    </tr>
                `;
                tbody.append(row);
            });

            $('#errorDetails').show();
        }

        function showSuccess(message) {
            $('#successMessage').text(message);
            $('#successAlert').show();
            $('#uploadProgress').hide();
            toastr.success(message);
        }

        function showError(message) {
            $('#errorMessage').text(message);
            $('#errorAlert').show();
            $('#uploadProgress').hide();
            toastr.error(message);
        }

        function resetUploadState() {
            $('#uploadBtn').prop('disabled', false).html('<i class="ti ti-upload me-1"></i>Upload & Process');
            $('#uploadProgress').hide();
        }

        // Reset modal on close
        $('#upload_payslip').on('hidden.bs.modal', function() {
            if (statusCheckInterval) {
                clearInterval(statusCheckInterval);
            }
            $('#uploadPayslipForm')[0].reset();
            $('#filePreview, #successAlert, #errorAlert, #errorDetails, #uploadProgress').hide();
            resetUploadState();
        });
    </script>
@endpush
