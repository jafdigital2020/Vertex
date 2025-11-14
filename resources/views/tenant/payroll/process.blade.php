<?php $page = 'payroll-process'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">


            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Payroll Process</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Payroll Process</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if (in_array('Export', $permission))
                        <div class="me-2 mb-2">
                            <div class="dropdown">
                                <a href="javascript:void(0);"
                                    class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <i class="ti ti-file-export me-1"></i>Payroll Export
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1 export-pdf-btn">
                                            <i class="ti ti-file-type-pdf me-1"></i>Export as PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1 export-excel-btn">
                                            <i class="ti ti-file-type-xls me-1"></i>Export as Excel
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="me-2 mb-2">
                            <div class="dropdown">
                                <a href="javascript:void(0);"
                                    class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <i class="ti ti-file-export me-1"></i>13th Month Export
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);"
                                            class="dropdown-item rounded-1 thirteenth-export-pdf-btn">
                                            <i class="ti ti-file-type-pdf me-1"></i>Export as PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);"
                                            class="dropdown-item rounded-1 thirteenth-export-excel-btn">
                                            <i class="ti ti-file-type-xls me-1"></i>Export as Excel
                                        </a>
                                    </li>
                                </ul>
                            </div>
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

            @php
                $currentYear = date('Y');
                $currentMonth = date('n');
                $currentDate = date('Y-m-d');
            @endphp

            <!-- Page Content -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-gradient-primary text-white py-3">
                            <h5 class="card-title mb-0 fw-semibold">
                                <i class="ti ti-calculator me-2"></i>Payroll Processing Form
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form id="payrollProcessForm">
                                <div class="row g-4">
                                    <!-- Left Column - Payroll Details -->
                                    <div class="col-xl-4">
                                        <div class="border-end pe-xl-4">
                                            <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom">
                                                <i class="ti ti-file-invoice me-2"></i>Payroll Details
                                            </h6>

                                            <div class="mb-3">
                                                <label for="payrollType" class="form-label fw-medium">Payroll Type <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select form-select-lg" name="payroll_type"
                                                    id="payrollType" required>
                                                    <option value="" disabled selected>Select Payroll Type</option>
                                                    <option value="normal_payroll">Normal Payroll</option>
                                                    <option value="13th_month">13th Month Pay</option>
                                                    {{-- <option value="final_pay">Final Pay</option> --}}
                                                </select>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="yearSelect" class="form-label fw-medium">Year <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-select" name="year" id="yearSelect" required>
                                                        <option value="" disabled>Select Year</option>
                                                        @for ($year = $currentYear - 5; $year <= $currentYear + 5; $year++)
                                                            <option value="{{ $year }}"
                                                                {{ $year == $currentYear ? 'selected' : '' }}>
                                                                {{ $year }}
                                                            </option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="monthSelect" class="form-label fw-medium">Month <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-select" name="month" id="monthSelect" required>
                                                        <option value="" disabled>Select Month</option>
                                                        @foreach (range(1, 12) as $month)
                                                            <option value="{{ $month }}"
                                                                {{ $month == $currentMonth ? 'selected' : '' }}>
                                                                {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Payroll Type Specific Fields -->
                                            <div id="dateFieldsContainer">
                                                <!-- Normal Payroll: Period Start/End -->
                                                <div id="periodDateFields">
                                                    <div class="mb-3">
                                                        <label for="startDate" class="form-label fw-medium">Period Start
                                                            <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" name="start_date"
                                                            id="startDate">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="endDate" class="form-label fw-medium">Period End <span
                                                                class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" name="end_date"
                                                            id="endDate">
                                                    </div>
                                                </div>

                                                <!-- 13th Month: From/To Month -->
                                                <div id="monthRangeFields" style="display: none;">
                                                    <div class="alert alert-info mb-3">
                                                        <i class="ti ti-info-circle me-2"></i>
                                                        <small>Select the 12-month coverage period for 13th month pay.
                                                            Example: December 2024 to November 2025.</small>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="fromYear" class="form-label fw-medium">From Year
                                                                <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="from_year" id="fromYear">
                                                                <option value="">Select Year</option>
                                                                @for ($year = $currentYear - 5; $year <= $currentYear + 1; $year++)
                                                                    <option value="{{ $year }}"
                                                                        {{ $year == $currentYear - 1 ? 'selected' : '' }}>
                                                                        {{ $year }}
                                                                    </option>
                                                                @endfor
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="fromMonth" class="form-label fw-medium">From Month
                                                                <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="from_month" id="fromMonth">
                                                                <option value="">Select Month</option>
                                                                @foreach (range(1, 12) as $month)
                                                                    <option value="{{ $month }}"
                                                                        {{ $month == 12 ? 'selected' : '' }}>
                                                                        {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="toYear" class="form-label fw-medium">To Year
                                                                <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="to_year" id="toYear">
                                                                <option value="">Select Year</option>
                                                                @for ($year = $currentYear - 5; $year <= $currentYear + 1; $year++)
                                                                    <option value="{{ $year }}"
                                                                        {{ $year == $currentYear ? 'selected' : '' }}>
                                                                        {{ $year }}
                                                                    </option>
                                                                @endfor
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="toMonth" class="form-label fw-medium">To Month
                                                                <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="to_month" id="toMonth">
                                                                <option value="">Select Month</option>
                                                                @foreach (range(1, 12) as $month)
                                                                    <option value="{{ $month }}"
                                                                        {{ $month == 11 ? 'selected' : '' }}>
                                                                        {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="transactionDate" class="form-label fw-medium">Transaction Date
                                                    <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="transaction_date"
                                                    id="transactionDate" value="{{ $currentDate }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Middle Column - Assignment -->
                                    <div class="col-xl-5">
                                        <div class="border-end pe-xl-4">
                                            <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom">
                                                <i class="ti ti-users me-2"></i>Employee Assignment
                                            </h6>

                                            <div class="mb-3">
                                                <label for="assignmentType" class="form-label fw-medium">Assignment Type
                                                    <span class="text-danger">*</span></label>
                                                <select name="assignment_type" id="assignmentType"
                                                    class="form-select form-select-lg" required>
                                                    <option value="">Select Assignment Type</option>
                                                    <option value="payroll_batch">Payroll Batch</option>
                                                    <option value="manual">Manual Selection</option>
                                                </select>
                                            </div>

                                            {{-- Payroll Batch --}}
                                            <div class="payroll-batch" style="display: none;">
                                                <div class="alert alert-info py-2 mb-3">
                                                    <i class="ti ti-info-circle me-1"></i>
                                                    <small>Select a pre-configured payroll batch</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-medium">Payroll Batch <span
                                                            class="text-danger">*</span></label>
                                                    <select name="payroll_batch_id" id="payrollBatchId"
                                                        class="form-select">
                                                        <option value="" disabled selected>Select Payroll Batch
                                                        </option>
                                                        @foreach ($payrollBatches as $payrollBatch)
                                                            <option value="{{ $payrollBatch->id }}">
                                                                {{ $payrollBatch->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Manual Assigning --}}
                                            <div class="manual-assigning" style="display: none;">
                                                <div class="alert alert-info py-2 mb-3">
                                                    <i class="ti ti-info-circle me-1"></i>
                                                    <small>Filter employees by branch, department, and designation</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="payrollProcessBranchId"
                                                        class="form-label fw-medium">Branch <span
                                                            class="text-danger">*</span></label>
                                                    <select name="branch_id[]" id="payrollProcessBranchId"
                                                        class="form-select select2 branch-select" multiple required>
                                                        <option value="">All Branches</option>
                                                        @foreach ($branches as $branch)
                                                            <option value="{{ $branch->id }}">{{ $branch->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="payrollProcessDepartmentId"
                                                        class="form-label fw-medium">Department <span
                                                            class="text-danger">*</span></label>
                                                    <select name="department_id[]" id="payrollProcessDepartmentId"
                                                        class="form-select select2 department-select" multiple required>
                                                        <option value="">All Departments</option>
                                                        @foreach ($departments as $department)
                                                            <option value="{{ $department->id }}">
                                                                {{ $department->department_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="payrollProcessDesignationId"
                                                        class="form-label fw-medium">Designation <span
                                                            class="text-danger">*</span></label>
                                                    <select name="designation_id[]" id="payrollProcessDesignationId"
                                                        class="form-select select2 designation-select" multiple required>
                                                        <option value="">All Designations</option>
                                                        @foreach ($designations as $designation)
                                                            <option value="{{ $designation->id }}">
                                                                {{ $designation->designation_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="payrollProcessUserId"
                                                        class="form-label fw-medium">Employee <span
                                                            class="text-danger">*</span></label>
                                                    <select name="user_id[]" id="payrollProcessUserId"
                                                        class="form-select select2 employee-select" multiple required>
                                                        <option value="">All Employees</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Column - Government Mandates & Actions -->
                                    <div class="col-xl-3">
                                        <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom">
                                            <i class="ti ti-shield-check me-2"></i>Government Contributions
                                        </h6>

                                        <div class="mb-4">
                                            <label class="form-label fw-medium d-block mb-2">SSS Contribution <span
                                                    class="text-danger">*</span></label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="sss_option" id="sssYes"
                                                    value="yes" required>
                                                <label class="btn btn-outline-secondary" for="sssYes">Half</label>

                                                <input type="radio" class="btn-check" name="sss_option" id="sssNo"
                                                    value="no" required>
                                                <label class="btn btn-outline-secondary" for="sssNo">None</label>

                                                <input type="radio" class="btn-check" name="sss_option" id="sssFull"
                                                    value="full" required>
                                                <label class="btn btn-outline-secondary" for="sssFull">Full</label>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-medium d-block mb-2">PhilHealth Contribution <span
                                                    class="text-danger">*</span></label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="philhealth_option"
                                                    id="philhealthYes" value="yes" required>
                                                <label class="btn btn-outline-secondary" for="philhealthYes">Half</label>

                                                <input type="radio" class="btn-check" name="philhealth_option"
                                                    id="philhealthNo" value="no" required>
                                                <label class="btn btn-outline-secondary" for="philhealthNo">None</label>

                                                <input type="radio" class="btn-check" name="philhealth_option"
                                                    id="philhealthFull" value="full" required>
                                                <label class="btn btn-outline-secondary" for="philhealthFull">Full</label>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-medium d-block mb-2">Pag-IBIG Contribution <span
                                                    class="text-danger">*</span></label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="pagibig_option"
                                                    id="pagibigYes" value="yes" required>
                                                <label class="btn btn-outline-secondary" for="pagibigYes">Half</label>

                                                <input type="radio" class="btn-check" name="pagibig_option"
                                                    id="pagibigNo" value="no" required>
                                                <label class="btn btn-outline-secondary" for="pagibigNo">None</label>

                                                <input type="radio" class="btn-check" name="pagibig_option"
                                                    id="pagibigFull" value="full" required>
                                                <label class="btn btn-outline-secondary" for="pagibigFull">Full</label>
                                            </div>
                                        </div>

                                        <div class="mb-4 pb-3 border-bottom">
                                            <label class="form-label fw-medium d-block mb-2">Cut-off Period <span
                                                    class="text-danger">*</span></label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="cutoff_period"
                                                    id="cutoffOne" value="1" required>
                                                <label class="btn btn-outline-secondary" for="cutoffOne">1st Half</label>

                                                <input type="radio" class="btn-check" name="cutoff_period"
                                                    id="cutoffTwo" value="2" required>
                                                <label class="btn btn-outline-secondary" for="cutoffTwo">2nd Half</label>

                                                {{-- <input type="radio" class="btn-check" name="cutoff_period"
                                                    id="cutoffWeekly" value="weekly" required disabled>
                                                <label class="btn btn-outline-secondary" for="cutoffWeekly">Weekly</label> --}}
                                            </div>
                                        </div>

                                        @if (in_array('Create', $permission))
                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary btn-lg py-3"
                                                    id="processPayrollBtn" disabled>
                                                    <!-- Default State -->
                                                    <span class="btn-text">
                                                        <i class="ti ti-checks me-2"></i>
                                                        Process Payroll
                                                    </span>
                                                    <!-- Loading State (hidden by default) -->
                                                    <span class="btn-loading d-none">
                                                        <span class="spinner-border spinner-border-sm me-2" role="status"
                                                            aria-hidden="true"></span>
                                                        Processing payroll, please wait...
                                                    </span>
                                                </button>
                                                <small class="text-muted text-center mt-2">
                                                    <i class="ti ti-info-circle me-1"></i>
                                                    All fields marked with <span class="text-danger">*</span> are required
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Normal Payroll Table --}}
            @if ($payrolls->count() > 0 || $payrolls->where('status', 'Pending')->count() > 0)
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5 class="mb-0 fw-semibold">Processed Payroll</h5>
                        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">


                            <!-- Bulk Actions Dropdown -->
                            <div class="dropdown me-2">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                    id="bulkActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    Bulk Actions
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="bulkActionsDropdown">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" id="bulkGeneratePayslip">
                                            <i class="ti ti-file-invoice me-1"></i>Generate Payslip
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" id="bulkBankReport">
                                            <i class="ti ti-file-invoice me-1"></i>Bank Report
                                        </a>
                                    </li>
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
                                    <input type="text" class="form-control date-range bookingrange-filtered"
                                        placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-chevron-down"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group me-2" style="max-width:200px;">
                                <select name="branch_filter" id="branch_filter" class="select2 form-select"
                                    oninput="filter()" style="width:150px;">
                                    <option value="" selected>All Branches</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group me-2">
                                <select name="department_filter" id="department_filter" class="select2 form-select"
                                    oninput="filter()" style="width:150px;">
                                    <option value="" selected>All Departments</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group me-2">
                                <select name="designation_filter" id="designation_filter" class="select2 form-select"
                                    oninput="filter()" style="width:150px;">
                                    <option value="" selected>All Designations</option>
                                    @foreach ($designations as $designation)
                                        <option value="{{ $designation->id }}">{{ $designation->designation_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="custom-datatable-filter table-responsive">
                            <table class="table datatable" id="payrollTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="no-sort">
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox" id="select-all">
                                            </div>
                                        </th>
                                        <th>Employee</th>
                                        <th>Branch</th>
                                        <th>Total Deductions</th>
                                        <th>Total Earnings</th>
                                        <th>Net Pay</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="payrollTableBody">
                                    @foreach ($payrolls as $payroll)
                                        <tr>
                                            <td>
                                                <div class="form-check form-check-md">
                                                    <input class="form-check-input payroll-checkbox" type="checkbox"
                                                        value="{{ $payroll->id }}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <a href="#" class="avatar avatar-md" data-bs-toggle="modal"
                                                        data-bs-target="#view_details"><img
                                                            src="{{ asset('storage/' . ($payroll->user->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                                                            class="img-fluid rounded-circle" alt="img"></a>
                                                    <div class="ms-2">
                                                        <p class="text-dark mb-0"><a href="#"
                                                                data-bs-toggle="modal" data-bs-target="#view_details">
                                                                {{ $payroll->user->personalInformation->last_name ?? '' }}
                                                                {{ $payroll->user->personalInformation->suffix ?? '' }},
                                                                {{ $payroll->user->personalInformation->first_name ?? '' }}
                                                                {{ $payroll->user->personalInformation->middle_name ?? '' }}</a>
                                                        </p>
                                                        <span
                                                            class="fs-12">{{ $payroll->user->employmentDetail->department->department_name ?? '' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $payroll->user->employmentDetail->branch->name ?? '' }}</td>
                                            <td>₱{{ number_format($payroll->total_deductions, 2) }}</td>
                                            <td>₱{{ number_format($payroll->total_earnings, 2) }}</td>
                                            <td class="text-danger">₱{{ number_format($payroll->net_salary, 2) }}</td>
                                            <td>
                                                @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                                    <div class="action-icon d-inline-flex">
                                                        @if (in_array('Update', $permission))
                                                            <a href="#" class="me-2 edit-payroll-btn"
                                                                data-bs-toggle="modal" data-bs-target="#edit_payroll"
                                                                data-id="{{ $payroll->id }}"
                                                                data-payroll-type="{{ $payroll->payroll_type }}"
                                                                data-payroll-period="{{ $payroll->payroll_period }}"
                                                                data-payroll-period-start="{{ $payroll->payroll_period_start }}"
                                                                data-payroll-period-end="{{ $payroll->payroll_period_end }}"
                                                                data-total-worked-minutes="{{ $payroll->total_worked_minutes }}"
                                                                data-total-late-minutes="{{ $payroll->total_late_minutes }}"
                                                                data-total-undertime-minutes="{{ $payroll->total_undertime_minutes }}"
                                                                data-total-overtime-minutes="{{ $payroll->total_overtime_minutes }}"
                                                                data-total-night-differential-minutes="{{ $payroll->total_night_differential_minutes }}"
                                                                data-total-overtime-night-diff-minutes="{{ $payroll->total_overtime_night_diff_minutes }}"
                                                                data-total-worked-days="{{ $payroll->total_worked_days }}"
                                                                data-total-absent-days="{{ $payroll->total_absent_days }}"
                                                                data-holiday-pay="{{ $payroll->holiday_pay }}"
                                                                data-leave-pay="{{ $payroll->leave_pay }}"
                                                                data-overtime-pay="{{ $payroll->overtime_pay }}"
                                                                data-night-differential-pay="{{ $payroll->night_differential_pay }}"
                                                                data-overtime-night-diff-pay="{{ $payroll->overtime_night_diff_pay }}"
                                                                data-late-deduction="{{ $payroll->late_deduction }}"
                                                                data-overtime-restday-pay="{{ $payroll->overtime_restday_pay }}"
                                                                data-undertime-deduction="{{ $payroll->undertime_deduction }}"
                                                                data-absent-deduction="{{ $payroll->absent_deduction }}"
                                                                data-earnings="{{ $payroll->earnings }}"
                                                                data-total-earnings="{{ $payroll->total_earnings }}"
                                                                data-allowance="{{ $payroll->allowance }}"
                                                                data-taxable-income="{{ $payroll->taxable_income }}"
                                                                data-deminimis="{{ $payroll->deminimis }}"
                                                                data-sss-contribution="{{ $payroll->sss_contribution }}"
                                                                data-philhealth-contribution="{{ $payroll->philhealth_contribution }}"
                                                                data-pagibig-contribution="{{ $payroll->pagibig_contribution }}"
                                                                data-withholding-tax="{{ $payroll->withholding_tax }}"
                                                                data-loan-deductions="{{ htmlspecialchars(json_encode($payroll->loan_deductions), ENT_QUOTES, 'UTF-8') }}"
                                                                data-deductions="{{ $payroll->deductions }}"
                                                                data-total-deductions="{{ $payroll->total_deductions }}"
                                                                data-basic-pay="{{ $payroll->basic_pay }}"
                                                                data-gross-pay="{{ $payroll->gross_pay }}"
                                                                data-net-salary="{{ $payroll->net_salary }}"
                                                                data-payment-date="{{ $payroll->payment_date }}"
                                                                data-status="{{ $payroll->status }}"
                                                                data-remarks="{{ $payroll->remarks }}"
                                                                data-processed-by="{{ $payroll->processor_name }}"
                                                                data-work-formatted="{{ $payroll->total_worked_minutes_formatted }}"
                                                                title="Edit"><i class="ti ti-edit"></i></a>
                                                        @endif
                                                        @if (in_array('Delete', $permission))
                                                            <a href="javascript:void(0);" class="btn-delete"
                                                                data-bs-toggle="modal" data-bs-target="#delete_payroll"
                                                                data-id="{{ $payroll->id }}"
                                                                data-name="{{ $payroll->user->personalInformation->full_name }}"
                                                                title="Delete"><i class="ti ti-trash"></i></a>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 13th Month Pay Pending Table --}}
            @if ($thirteenthMonthPayrolls->count() > 0)
                <div class="card mt-4">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="ti ti-calendar-dollar me-2"></i>13th Month Pay - Pending
                        </h5>
                        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                            <!-- Bulk Actions Dropdown -->
                            <div class="dropdown me-2">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                    id="bulk13thMonthActions" data-bs-toggle="dropdown" aria-expanded="false">
                                    Bulk Actions
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="bulk13thMonthActions">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);"
                                            id="bulkGenerate13thMonthPayslip">
                                            <i class="ti ti-file-invoice me-1"></i>Generate Payslip
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                                            id="bulkDelete13thMonth">
                                            <i class="ti ti-trash me-1"></i>Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>

                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="custom-datatable-filter table-responsive">
                            <table class="table" id="thirteenthMonthTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="no-sort">
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox" id="select-all-13th">
                                            </div>
                                        </th>
                                        <th>Employee</th>
                                        <th>Coverage Period</th>
                                        <th>Year</th>
                                        <th>Total Basic Pay</th>
                                        <th>Total Deductions</th>
                                        <th>13th Month Pay</th>
                                        <th>Payment Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="thirteenthMonthTableBody">
                                    @foreach ($thirteenthMonthPayrolls as $thirteenthMonth)
                                        <tr>
                                            <td>
                                                <div class="form-check form-check-md">
                                                    <input class="form-check-input thirteenth-month-checkbox"
                                                        type="checkbox" value="{{ $thirteenthMonth->id }}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <a href="#" class="avatar avatar-md">
                                                        <img src="{{ asset('storage/' . ($thirteenthMonth->user->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                                                            class="img-fluid rounded-circle" alt="img">
                                                    </a>
                                                    <div class="ms-2">
                                                        <p class="text-dark mb-0">
                                                            <a href="#">
                                                                {{ $thirteenthMonth->user->personalInformation->last_name ?? '' }}
                                                                {{ $thirteenthMonth->user->personalInformation->suffix ?? '' }},
                                                                {{ $thirteenthMonth->user->personalInformation->first_name ?? '' }}
                                                                {{ $thirteenthMonth->user->personalInformation->middle_name ?? '' }}
                                                            </a>
                                                        </p>
                                                        <span
                                                            class="fs-12">{{ $thirteenthMonth->user->employmentDetail->department->department_name ?? '' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::create($thirteenthMonth->from_year ?? $thirteenthMonth->year, $thirteenthMonth->from_month)->format('F Y') }}
                                                -
                                                {{ \Carbon\Carbon::create($thirteenthMonth->to_year ?? $thirteenthMonth->year, $thirteenthMonth->to_month)->format('F Y') }}
                                            </td>
                                            <td>{{ $thirteenthMonth->year }}</td>
                                            <td>₱{{ number_format($thirteenthMonth->total_basic_pay, 2) }}</td>
                                            <td>₱{{ number_format($thirteenthMonth->total_deductions, 2) }}</td>
                                            <td class="text-success fw-bold">
                                                ₱{{ number_format($thirteenthMonth->total_thirteenth_month, 2) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($thirteenthMonth->payment_date)->format('M d, Y') }}
                                            </td>
                                            <td>
                                                @if ($thirteenthMonth->status === 'Pending')
                                                    <span class="badge badge-soft-warning">Pending</span>
                                                @elseif($thirteenthMonth->status === 'Paid')
                                                    <span class="badge badge-soft-success">Paid</span>
                                                @else
                                                    <span
                                                        class="badge badge-soft-secondary">{{ $thirteenthMonth->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                                    <div class="action-icon d-inline-flex">
                                                        @if (in_array('Update', $permission))
                                                            <a href="#" class="me-2 view-13th-month-btn"
                                                                data-bs-toggle="modal" data-bs-target="#view_13th_month"
                                                                data-id="{{ $thirteenthMonth->id }}"
                                                                data-user-name="{{ $thirteenthMonth->user->personalInformation->full_name ?? 'Unknown' }}"
                                                                data-coverage-start="{{ \Carbon\Carbon::create($thirteenthMonth->from_year ?? $thirteenthMonth->year, $thirteenthMonth->from_month)->format('F Y') }}"
                                                                data-coverage-end="{{ \Carbon\Carbon::create($thirteenthMonth->to_year ?? $thirteenthMonth->year, $thirteenthMonth->to_month)->format('F Y') }}"
                                                                data-year="{{ $thirteenthMonth->year }}"
                                                                data-total-basic-pay="{{ $thirteenthMonth->total_basic_pay }}"
                                                                data-total-deductions="{{ $thirteenthMonth->total_deductions }}"
                                                                data-total-thirteenth-month="{{ $thirteenthMonth->total_thirteenth_month }}"
                                                                data-payment-date="{{ $thirteenthMonth->payment_date }}"
                                                                data-status="{{ $thirteenthMonth->status }}"
                                                                data-monthly-breakdown="{{ json_encode($thirteenthMonth->monthly_breakdown) }}"
                                                                data-processor-name="{{ $thirteenthMonth->processor_name }}"
                                                                title="View Details">
                                                                <i class="ti ti-eye"></i>
                                                            </a>
                                                        @endif
                                                        @if (in_array('Delete', $permission))
                                                            <a href="javascript:void(0);" class="btn-delete-13th"
                                                                data-bs-toggle="modal" data-bs-target="#delete_13th_month"
                                                                data-id="{{ $thirteenthMonth->id }}"
                                                                data-name="{{ $thirteenthMonth->user->personalInformation->full_name }}"
                                                                title="Delete">
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Edit Normal Payroll Modal --}}
    <div class="modal fade" id="edit_payroll" tabindex="-1" aria-labelledby="payrollModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-xl">
                <div class="modal-header bg-gradient bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center shadow-sm"
                            style="width:48px; height:48px; border:2px solid rgba(0,0,0,0.06);">
                            <i class="ti ti-file-invoice fs-20 text-primary" aria-hidden="true" title="Payroll"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-white" id="payrollModalLabel">Payroll Edit</h5>
                            <small class="opacity-75">Edit employee pay details</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <form id="editPayrollForm" enctype="multipart/form-data">
                        <!-- Hidden ID -->
                        <input type="hidden" id="payroll_id" name="payroll_id">

                        <!-- Payroll Details Section -->
                        <div class="card mb-4 border-light shadow-sm">
                            <div class="card-header bg-transparent border-bottom">
                                <h6 class="card-title mb-0 text-primary fw-semibold">
                                    <i class="ti ti-calendar-event me-2"></i>Payroll Period Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="payroll_type" class="form-label fw-semibold">Payroll Type</label>
                                        <input type="text" class="form-control form-control-sm" id="payroll_type"
                                            name="payroll_type" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="payroll_period" class="form-label fw-semibold">Payroll Period</label>
                                        <input type="text" class="form-control form-control-sm" id="payroll_period"
                                            name="payroll_period" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="payroll_period_start" class="form-label fw-semibold">Period
                                            Start</label>
                                        <input type="date" class="form-control form-control-sm"
                                            id="payroll_period_start" name="payroll_period_start">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="payroll_period_end" class="form-label fw-semibold">Period End</label>
                                        <input type="date" class="form-control form-control-sm"
                                            id="payroll_period_end" name="payroll_period_end">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Time Tracking Section -->
                        <div class="card mb-4 border-light shadow-sm">
                            <div class="card-header bg-transparent border-bottom">
                                <h6 class="card-title mb-0 text-primary fw-semibold">
                                    <i class="ti ti-clock me-2"></i>Time Tracking & Attendance
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label for="displayTotalMinutes" class="form-label fw-semibold">Worked
                                            Hours</label>
                                        <input type="text" name="total_worked_minutes"
                                            class="form-control form-control-sm text-center" id="displayTotalMinutes"
                                            readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="total_late_minutes" class="form-label fw-semibold">Late (min)</label>
                                        <input type="number" class="form-control form-control-sm"
                                            id="total_late_minutes" name="total_late_minutes">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="total_undertime_minutes" class="form-label fw-semibold">Undertime
                                            (min)</label>
                                        <input type="number" class="form-control form-control-sm"
                                            id="total_undertime_minutes" name="total_undertime_minutes">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="total_overtime_minutes" class="form-label fw-semibold">Overtime
                                            (min)</label>
                                        <input type="number" class="form-control form-control-sm"
                                            id="total_overtime_minutes" name="total_overtime_minutes">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="total_night_differential_minutes" class="form-label fw-semibold">Night
                                            Diff (min)</label>
                                        <input type="number" class="form-control form-control-sm"
                                            id="total_night_differential_minutes" name="total_night_differential_minutes">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="total_overtime_night_differential_minutes"
                                            class="form-label fw-semibold">OT Night Diff (min)</label>
                                        <input type="number" class="form-control form-control-sm"
                                            id="total_overtime_night_differential_minutes"
                                            name="total_overtime_night_differential_minutes">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pay Breakdown Section -->
                        <div class="card mb-4 border-light shadow-sm">
                            <div class="card-header bg-transparent border-bottom">
                                <h6 class="card-title mb-0 text-primary fw-semibold">
                                    <i class="ti ti-moneybag me-2"></i>Pay Breakdown
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="holiday_pay" class="form-label fw-semibold">Holiday Pay</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">₱</span>
                                            <input type="number" class="form-control text-end" id="holiday_pay"
                                                name="holiday_pay" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="leave_pay" class="form-label fw-semibold">Leave Pay</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">₱</span>
                                            <input type="number" class="form-control text-end" id="leave_pay"
                                                name="leave_pay" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="overtime_pay" class="form-label fw-semibold">Overtime Pay</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">₱</span>
                                            <input type="number" class="form-control text-end" id="overtime_pay"
                                                name="overtime_pay" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="night_differential_pay" class="form-label fw-semibold">Night Diff
                                            Pay</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">₱</span>
                                            <input type="number" class="form-control text-end"
                                                id="night_differential_pay" name="night_differential_pay" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="overtime_restday_pay" class="form-label fw-semibold">OT Restday
                                            Pay</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">₱</span>
                                            <input type="number" class="form-control text-end" id="overtime_restday_pay"
                                                name="overtime_restday_pay" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="overtime_night_differential_pay" class="form-label fw-semibold">OT
                                            Night Diff Pay</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">₱</span>
                                            <input type="number" class="form-control text-end"
                                                id="overtime_night_differential_pay"
                                                name="overtime_night_differential_pay" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="late_deduction" class="form-label fw-semibold">Late Deduction</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light text-danger">₱</span>
                                            <input type="number" class="form-control text-end text-danger"
                                                id="late_deduction" name="late_deduction" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="undertime_deduction" class="form-label fw-semibold">Undertime
                                            Deduction</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light text-danger">₱</span>
                                            <input type="number" class="form-control text-end text-danger"
                                                id="undertime_deduction" name="undertime_deduction" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="absent_deduction" class="form-label fw-semibold">Absent
                                            Deduction</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light text-danger">₱</span>
                                            <input type="number" class="form-control text-end text-danger"
                                                id="absent_deduction" name="absent_deduction" step="0.01">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Sections (Earnings, Allowances, etc.) -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="card border-light shadow-sm">
                                    <div class="card-header bg-transparent border-bottom">
                                        <h6 class="card-title mb-0 text-primary fw-semibold">
                                            <i class="ti ti-gift me-2"></i>Earnings
                                        </h6>
                                    </div>
                                    <div class="card-body p-3" id="earnings_fields"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-light shadow-sm">
                                    <div class="card-header bg-transparent border-bottom">
                                        <h6 class="card-title mb-0 text-primary fw-semibold">
                                            <i class="ti ti-wallet me-2"></i>Allowances
                                        </h6>
                                    </div>
                                    <div class="card-body p-3" id="allowance_fields"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="card border-light shadow-sm">
                                    <div class="card-header bg-transparent border-bottom">
                                        <h6 class="card-title mb-0 text-primary fw-semibold">
                                            <i class="ti ti-minus me-2"></i>Deductions
                                        </h6>
                                    </div>
                                    <div class="card-body p-3" id="deductions_fields"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-light shadow-sm">
                                    <div class="card-header bg-transparent border-bottom">
                                        <h6 class="card-title mb-0 text-primary fw-semibold">
                                            <i class="ti ti-badge me-2"></i>Deminimis Benefits
                                        </h6>
                                    </div>
                                    <div class="card-body p-3" id="deminimis_fields"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Government Mandates Section -->
                        <div class="card mb-4 border-light shadow-sm">
                            <div class="card-header bg-transparent border-bottom">
                                <h6 class="card-title mb-0 text-primary fw-semibold">
                                    <i class="ti ti-shield-check me-2"></i>Government Mandates
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="sss_contribution" class="form-label fw-semibold">SSS
                                            Contribution</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">₱</span>
                                            <input type="number" class="form-control text-end" id="sss_contribution"
                                                name="sss_contribution" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="philhealth_contribution" class="form-label fw-semibold">PhilHealth
                                            Contribution</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">₱</span>
                                            <input type="number" class="form-control text-end"
                                                id="philhealth_contribution" name="philhealth_contribution"
                                                step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="pagibig_contribution" class="form-label fw-semibold">PagIBIG
                                            Contribution</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">₱</span>
                                            <input type="number" class="form-control text-end" id="pagibig_contribution"
                                                name="pagibig_contribution" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="withholding_tax" class="form-label fw-semibold">Withholding
                                            Tax</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">₱</span>
                                            <input type="number" class="form-control text-end" id="withholding_tax"
                                                name="withholding_tax" step="0.01">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Salary Summary Section -->
                        <div class="card mb-4 border-light shadow-sm">
                            <div class="card-header bg-transparent border-bottom">
                                <h6 class="card-title mb-0 text-primary fw-semibold">
                                    <i class="ti ti-calculator me-2"></i>Salary Summary
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="basic_pay" class="form-label fw-semibold">Basic Pay</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">₱</span>
                                            <input type="number" class="form-control form-control-sm text-end"
                                                id="basic_pay" name="basic_pay" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="gross_pay" class="form-label fw-semibold">Gross Pay</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">₱</span>
                                            <input type="number"
                                                class="form-control form-control-sm text-end fw-bold text-primary"
                                                id="gross_pay" name="gross_pay" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-md-4">
                                        <label for="total_earnings" class="form-label fw-semibold">Total Earnings</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-success bg-opacity-10 text-success">₱</span>
                                            <input type="number" class="form-control text-end text-success fw-bold"
                                                id="total_earnings" name="total_earnings" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="total_deductions" class="form-label fw-semibold">Total
                                            Deductions</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-danger bg-opacity-10 text-danger">₱</span>
                                            <input type="number" class="form-control text-end text-danger fw-bold"
                                                id="total_deduction" name="total_deductions" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="net_salary" class="form-label fw-semibold">Net Salary</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-primary bg-opacity-10 text-white">₱</span>
                                            <input type="number" class="form-control text-end text-primary fw-bold"
                                                id="net_salary" name="net_salary" step="0.01">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="card mb-4 border-light shadow-sm">
                            <div class="card-header bg-transparent border-bottom">
                                <h6 class="card-title mb-0 text-primary fw-semibold">
                                    <i class="ti ti-credit-card me-2"></i>Payment Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="payment_date" class="form-label fw-semibold">Payment Date</label>
                                        <input type="date" class="form-control form-control-sm" id="payment_date"
                                            name="payment_date">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="processed_by" class="form-label fw-semibold">Processed By</label>
                                        <input type="text" class="form-control form-control-sm" id="processed_by"
                                            name="processed_by" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer bg-light border-0">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="ti ti-x me-1"></i>Cancel
                        </button>
                        <button type="submit" form="editPayrollForm" class="btn btn-primary px-4">
                            <i class="ti ti-check me-1"></i>Save Payroll
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- View 13th Month Modal --}}
    <div class="modal fade" id="view_13th_month" tabindex="-1" aria-labelledby="view13thMonthLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div title="13th Month Pay" role="img" aria-label="13th Month Pay"
                                class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                style="width:48px; height:48px; border:2px solid rgba(0,0,0,0.06);">
                                <i class="ti ti-calendar-dollar fs-20 text-primary" aria-hidden="true"></i>
                            </div>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-white" id="view13thMonthLabel">13th Month Pay Details
                            </h5>
                            <small class="opacity-75">Comprehensive compensation breakdown</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body py-4">
                    <!-- Header Cards -->
                    <div class="row g-2 mb-2">
                        <div class="col-xl-6">
                            <div class="card h-100 border-light shadow-sm">
                                <div class="card-header bg-transparent border-bottom py-2">
                                    <h6 class="card-title mb-0 text-primary fw-semibold">
                                        <i class="ti ti-user me-2"></i>Employee Information
                                    </h6>
                                </div>
                                <div class="card-body py-2 px-3">
                                    <div class="row g-2">
                                        <div class="col-6 col-sm-6">
                                            <small class="text-muted d-block mb-1">Employee Name</small>
                                            <p class="mb-0 fw-medium text-truncate" id="modal_user_name">-</p>
                                        </div>

                                        <div class="col-6 col-sm-6">
                                            <small class="text-muted d-block mb-1">Year</small>
                                            <p class="mb-0 fw-medium text-truncate" id="modal_year">-</p>
                                        </div>

                                        <div class="col-6 col-sm-6">
                                            <small class="text-muted d-block mb-1">Coverage Period</small>
                                            <p class="mb-0 fw-medium text-truncate" id="modal_coverage">-</p>
                                        </div>

                                        <div class="col-6 col-sm-6">
                                            <small class="text-muted d-block mb-1">Payment Date</small>
                                            <p class="mb-0 fw-medium text-truncate" id="modal_payment_date">-</p>
                                        </div>

                                        <div class="col-6 col-sm-6">
                                            <small class="text-muted d-block mb-1">Status</small>
                                            <p class="mb-0">
                                                <span id="modal_status"
                                                    class="badge rounded-pill py-1 px-2 d-inline-block"></span>
                                            </p>
                                        </div>

                                        <div class="col-6 col-sm-6">
                                            <small class="text-muted d-block mb-1">Processed By</small>
                                            <p class="mb-0 fw-medium text-truncate" id="modal_processor">-</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card h-100 border-light shadow-sm">
                                <div class="card-header bg-transparent border-bottom py-2">
                                    <h6 class="card-title mb-0 text-primary fw-semibold">
                                        <i class="ti ti-calculator me-2"></i>Financial Summary
                                    </h6>
                                </div>
                                <div class="card-body py-2 px-3">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="bg-light rounded-3 p-2 text-center">
                                                <small class="text-muted d-block">Total Basic Pay</small>
                                                <h6 class="mb-0 fw-bold text-primary" id="modal_total_basic_pay">₱0.00
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-light rounded-3 p-2 text-center">
                                                <small class="text-muted d-block">Total Deductions</small>
                                                <h6 class="mb-0 fw-bold text-danger" id="modal_total_deductions">₱0.00
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div
                                                class="bg-gradient bg-primary bg-opacity-10 rounded-3 p-3 border-start border-primary border-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <small class="text-white d-block">Final 13th Month Pay</small>
                                                        <h5 class="mb-0 fw-bold text-success" id="modal_total_13th">
                                                            ₱0.00
                                                        </h5>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                                            style="width:48px; height:48px; box-shadow: 0 2px 6px rgba(0,0,0,0.08);">
                                                            <i class="ti ti-currency-peso fs-20" aria-hidden="true"></i>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Breakdown Section -->
                    <div class="card border-light shadow-sm">
                        <div class="card-header bg-transparent border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0 text-primary fw-semibold">
                                    <i class="ti ti-list-details me-2"></i>Monthly Breakdown
                                </h6>

                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="fw-semibold text-muted">Month</th>
                                            <th class="fw-semibold text-muted text-center">Payroll Count</th>
                                            <th class="fw-semibold text-muted text-end">Basic Pay</th>
                                            <th class="fw-semibold text-muted text-end">Leave Pay</th>
                                            <th class="fw-semibold text-muted text-end">Late</th>
                                            <th class="fw-semibold text-muted text-end">Undertime</th>
                                            <th class="fw-semibold text-muted text-end">Absent</th>
                                            <th class="fw-semibold text-muted text-end">13th Month</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal_monthly_breakdown" class="align-middle">
                                        <!-- Dynamic rows will be inserted here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete 13th Month Modal --}}
    <div class="modal fade" id="delete_13th_month" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <span
                        class="avatar avatar-xl bg-transparent-danger text-danger mb-3 d-inline-flex align-items-center justify-content-center">
                        <i class="ti ti-trash-x fs-36"></i>
                    </span>
                    <h4 class="mb-1">Confirm Delete</h4>
                    <p class="mb-3">
                        Are you sure you want to delete <strong><span id="thirteenthMonthPlaceholder"></span></strong>'s
                        13th month pay? This can’t be undone.
                    </p>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="thirteenthMonthConfirmDeleteBtn">Yes,
                            Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 13th Month Export Status Selection Modal --}}
    <div class="modal fade" id="thirteenth_month_export_modal" tabindex="-1"
        aria-labelledby="thirteenthMonthExportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center"
                            style="width:40px; height:40px;">
                            <i class="ti ti-file-export fs-18 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-white" id="thirteenthMonthExportModalLabel">Export 13th Month
                                Pay</h5>
                            <small class="opacity-75">Choose export options</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <input type="hidden" id="export_format_type" value="">

                    <div class="mb-3">
                        <label class="form-label fw-semibold mb-2">
                            <i class="ti ti-filter me-2"></i>Filter by Status
                        </label>
                        <select class="form-select form-select-lg" id="export_status_dropdown">
                            <option value="" selected>All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Released">Released</option>
                        </select>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="ti ti-info-circle me-2"></i>
                        <small>Your current filters will be applied to the export.</small>
                    </div>
                </div>

                <div class="modal-footer bg-light border-0">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="ti ti-x me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary px-4" id="confirm_export_button">
                            <i class="ti ti-download me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    {{-- Assigning Type --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const assignmentTypeSelect = document.getElementById('assignmentType');
            const payrollBatchDiv = document.querySelector('.payroll-batch');
            const manualAssigningDiv = document.querySelector('.manual-assigning');
            const payrollBatchSelect = document.getElementById('payrollBatchId');
            const branchSelect = document.getElementById('payrollProcessBranchId');
            const departmentSelect = document.getElementById('payrollProcessDepartmentId');
            const designationSelect = document.getElementById('payrollProcessDesignationId');
            const userSelect = document.getElementById('payrollProcessUserId');

            // Function to clear all fields
            function clearFields() {
                payrollBatchSelect.selectedIndex = 0;
                branchSelect.selectedIndex = 0;
                departmentSelect.selectedIndex = 0;
                designationSelect.selectedIndex = 0;
                userSelect.selectedIndex = 0;
            }

            // Event listener for assignment type change
            assignmentTypeSelect.addEventListener('change', function() {
                // Clear fields
                clearFields();

                if (assignmentTypeSelect.value === 'payroll_batch') {
                    // Show payroll batch div and hide manual assigning div
                    payrollBatchDiv.style.display = 'block';
                    manualAssigningDiv.style.display = 'none';
                } else if (assignmentTypeSelect.value === 'manual') {
                    // Show manual assigning div and hide payroll batch div
                    payrollBatchDiv.style.display = 'none';
                    manualAssigningDiv.style.display = 'block';
                } else {
                    // Hide both divs if nothing is selected
                    payrollBatchDiv.style.display = 'none';
                    manualAssigningDiv.style.display = 'none';
                }
            });
        });
    </script>

    {{-- Filter --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const authToken = localStorage.getItem('token');

            // — Helper: if user picks the empty‐value “All” option, auto-select every real option
            function handleSelectAll($sel) {
                const vals = $sel.val() || [];
                if (vals.includes('')) {
                    const all = $sel.find('option')
                        .map((i, opt) => $(opt).val())
                        .get()
                        .filter(v => v !== '');
                    $sel.val(all).trigger('change');
                    return true;
                }
                return false;
            }

            // — Rebuild Employee list based on selected Departments & Designations
            function updateEmployeeSelect(container) {
                const allEmps = container.data('employees') || [];
                const deptIds = container.find('.department-select').val() || [];
                const desigIds = container.find('.designation-select').val() || [];

                const filtered = allEmps.filter(emp => {
                    if (deptIds.length && !deptIds.includes(String(emp.department_id))) return false;
                    if (desigIds.length && !desigIds.includes(String(emp.designation_id))) return false;
                    return true;
                });

                let opts = '<option value="">All Employee</option>';
                filtered.forEach(emp => {
                    const u = emp.user?.personal_information;
                    if (u) {
                        opts += `<option value="${emp.user.id}">${u.last_name}, ${u.first_name}</option>`;
                    }
                });

                container.find('.employee-select')
                    .html(opts)
                    .trigger('change');
            }

            // — Branch change → fetch Depts, Emps & Shifts
            $(document).on('change', '.branch-select', function() {
                const $this = $(this);
                if (handleSelectAll($this)) return;

                const branchIds = $this.val() || [];
                const container = $this.closest('form');
                const depSel = container.find('.department-select');
                const desSel = container.find('.designation-select');
                const empSel = container.find('.employee-select');

                // reset downstream
                depSel.html('<option value="">All Department</option>').trigger('change');
                desSel.html('<option value="">All Designation</option>').trigger('change');
                empSel.html('<option value="">All Employee</option>').trigger('change');
                container.removeData('employees');

                if (!branchIds.length) return;

                $.ajax({
                    url: '/api/shift-management/get-branch-data?' + $.param({
                        branch_ids: branchIds
                    }),
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Authorization': 'Bearer ' + authToken
                    },
                    success(data) {
                        // populate Departments
                        let dOpts = '<option value="">All Department</option>';
                        data.departments.forEach(d => {
                            dOpts +=
                                `<option value="${d.id}">${d.department_name}</option>`;
                        });
                        depSel.html(dOpts).trigger('change');

                        // cache & render Employees
                        container.data('employees', data.employees || []);
                        updateEmployeeSelect(container);
                    },
                    error() {
                        alert('Failed to fetch branch data.');
                    }
                });
            });

            // — Department change → fetch Designations & re-filter Employees
            $(document).on('change', '.department-select', function() {
                const $this = $(this);
                if (handleSelectAll($this)) return;

                const deptIds = $this.val() || [];
                const container = $this.closest('form');
                const desSel = container.find('.designation-select');

                desSel.html('<option value="">All Designation</option>').trigger('change');
                updateEmployeeSelect(container);

                if (!deptIds.length) return;

                $.ajax({
                    url: '/api/shift-management/get-designations?' + $.param({
                        department_ids: deptIds
                    }),
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Authorization': 'Bearer ' + authToken
                    },
                    success(data) {
                        let o = '<option value="">All Designation</option>';
                        data.forEach(d => {
                            o += `<option value="${d.id}">${d.designation_name}</option>`;
                        });
                        desSel.html(o).trigger('change');
                    },
                    error() {
                        alert('Failed to fetch designations.');
                    }
                });
            });

            // — Designation change → re-filter Employees
            $(document).on('change', '.designation-select', function() {
                const $this = $(this);
                if (handleSelectAll($this)) return;
                updateEmployeeSelect($this.closest('form'));
            });

            // — Employee “All Employee” handler
            $(document).on('change', '.employee-select', function() {
                handleSelectAll($(this));
            });
        });
    </script>

    {{-- Payroll Process --}}
    <script>
        $(document).ready(function() {
            const $form = $('#payrollProcessForm');
            const $btn = $('#processPayrollBtn');
            const $btnText = $btn.find('.btn-text');
            const $btnLoading = $btn.find('.btn-loading');

            // Toggle date fields based on payroll type
            $('#payrollType').on('change', function() {
                const payrollType = $(this).val();

                if (payrollType === '13th_month') {
                    // Show month range, hide period dates
                    $('#periodDateFields').hide();
                    $('#monthRangeFields').show();

                    // Remove required from period dates
                    $('#startDate, #endDate').removeAttr('required');

                    // Add required to month/year range
                    $('#fromYear, #fromMonth, #toYear, #toMonth').attr('required', 'required');

                    // Hide government mandates AND remove required attribute
                    $('[name="pagibig_option"]').removeAttr('required').closest('.mb-4').hide();
                    $('[name="sss_option"]').removeAttr('required').closest('.mb-4').hide();
                    $('[name="philhealth_option"]').removeAttr('required').closest('.mb-4').hide();
                    $('[name="cutoff_period"]').removeAttr('required').closest('.mb-4').hide();

                } else {
                    // Show period dates, hide month range
                    $('#periodDateFields').show();
                    $('#monthRangeFields').hide();

                    // Add required to period dates
                    $('#startDate, #endDate').attr('required', 'required');

                    // Remove required from month/year range
                    $('#fromYear, #fromMonth, #toYear, #toMonth').removeAttr('required');

                    // Show government mandates AND restore required attribute
                    $('[name="pagibig_option"]').attr('required', 'required').closest('.mb-4').show();
                    $('[name="sss_option"]').attr('required', 'required').closest('.mb-4').show();
                    $('[name="philhealth_option"]').attr('required', 'required').closest('.mb-4').show();
                    $('[name="cutoff_period"]').attr('required', 'required').closest('.mb-4').show();
                }

                validateForm();
            });

            // Function to validate form and enable/disable button
            function validateForm() {
                const payrollType = $('#payrollType').val();
                const year = $('#yearSelect').val();
                const month = $('#monthSelect').val();
                const transactionDate = $('#transactionDate').val();
                const assignmentType = $('#assignmentType').val();

                let dateValid = false;

                if (payrollType === '13th_month') {
                    // For 13th month, check year + month for both from and to
                    const fromYear = $('#fromYear').val();
                    const fromMonth = $('#fromMonth').val();
                    const toYear = $('#toYear').val();
                    const toMonth = $('#toMonth').val();
                    dateValid = fromYear && fromMonth && toYear && toMonth;

                    // Optional: Validate that "to" date is after "from" date
                    if (dateValid) {
                        const fromDate = new Date(fromYear, fromMonth - 1);
                        const toDate = new Date(toYear, toMonth - 1);
                        if (toDate < fromDate) {
                            dateValid = false;
                        }
                    }
                } else {
                    // For normal payroll, check start_date and end_date
                    const startDate = $('#startDate').val();
                    const endDate = $('#endDate').val();
                    dateValid = startDate && endDate;
                }

                // Check assignment type specific fields
                let assignmentValid = false;
                if (assignmentType === 'payroll_batch') {
                    assignmentValid = $('#payrollBatchId').val() !== '';
                } else if (assignmentType === 'manual') {
                    const branchId = $('#payrollProcessBranchId').val();
                    const departmentId = $('#payrollProcessDepartmentId').val();
                    const designationId = $('#payrollProcessDesignationId').val();
                    const userId = $('#payrollProcessUserId').val();
                    assignmentValid = branchId && departmentId && designationId && userId;
                }

                // For 13th month, skip government mandates validation
                let mandatesValid = true;
                if (payrollType !== '13th_month') {
                    const pagibigOption = $("input[name='pagibig_option']:checked").val();
                    const sssOption = $("input[name='sss_option']:checked").val();
                    const philhealthOption = $("input[name='philhealth_option']:checked").val();
                    const cutoffPeriod = $("input[name='cutoff_period']:checked").val();
                    mandatesValid = pagibigOption && sssOption && philhealthOption && cutoffPeriod;
                }

                // Enable button if all required fields are filled
                const isValid = payrollType && year && month && dateValid &&
                    transactionDate && assignmentType && assignmentValid && mandatesValid;

                $btn.prop('disabled', !isValid);
            }

            // Trigger validation on form field changes
            $form.on('change input', 'select, input', function() {
                validateForm();
            });

            // Initial validation check
            validateForm();

            $form.on('submit', function(e) {
                e.preventDefault();

                const payrollType = $('#payrollType').val();

                // === VALIDATION - Skip government mandates for 13th month ===
                if (payrollType !== '13th_month') {
                    const pagibigOption = $("input[name='pagibig_option']:checked").val();
                    const sssOption = $("input[name='sss_option']:checked").val();
                    const philhealthOption = $("input[name='philhealth_option']:checked").val();
                    const cutoffPeriod = $("input[name='cutoff_period']:checked").val();

                    if (!pagibigOption || !sssOption || !philhealthOption || !cutoffPeriod) {
                        toastr.error("Please complete all required government contribution options.");
                        return;
                    }
                }

                // === SHOW LOADING STATE ===
                $btn.prop('disabled', true);
                $btnText.addClass('d-none');
                $btnLoading.removeClass('d-none');

                let formData = new FormData(this);

                $.ajax({
                    url: '/api/payroll/process/',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    timeout: 60000,
                    success: function(res) {
                        if (payrollType === '13th_month') {
                            toastr.success("13th Month Pay has been processed successfully!");
                        } else {
                            toastr.success("Payroll has been processed successfully!");
                        }
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    },
                    error: function(err) {
                        // === REVERT TO NORMAL BUTTON ===
                        $btnLoading.addClass('d-none');
                        $btnText.removeClass('d-none');
                        validateForm(); // Re-validate to set proper button state

                        let message = "An error occurred while processing payroll.";
                        if (err.responseJSON?.message) {
                            message = err.responseJSON.message;
                        } else if (err.status === 0) {
                            message = "Network error. Please check your connection.";
                        } else if (err.status === 408 || err.statusText === 'timeout') {
                            message = "Request timed out. Please try again.";
                        }

                        toastr.error(message);
                    }
                });
            });
        });
    </script>

    {{-- Delete Payroll --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let payrollDeleteId = null;
            const payrollConfirmDeleteBtn = document.getElementById('payrollConfirmDeleteBtn');
            const payrollPlaceholder = document.getElementById('payrollPlaceholder');

            // Use delegation to listen for delete button clicks
            document.addEventListener('click', function(e) {
                const button = e.target.closest('.btn-delete');
                if (!button) return;

                payrollDeleteId = button.getAttribute('data-id');
                const payrollName = button.getAttribute('data-name');

                if (payrollPlaceholder) {
                    payrollPlaceholder.textContent = payrollName;
                }
            });

            // Confirm delete
            payrollConfirmDeleteBtn?.addEventListener('click', function() {
                if (!payrollDeleteId) return;

                fetch(`/api/payroll/delete/${payrollDeleteId}`, {
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
                            toastr.success("Payroll deleted successfully.");
                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_payroll'));
                            deleteModal.hide();
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message || "Error deleting payroll.");
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

    {{-- Bulk Process --}}
    <script>
        // Select/Deselect all checkboxes
        $(document).on('change', '#select-all', function() {
            $('.payroll-checkbox').prop('checked', this.checked);
        });

        // If any checkbox is unchecked, uncheck the select-all
        $(document).on('change', '.payroll-checkbox', function() {
            if (!this.checked) {
                $('#select-all').prop('checked', false);
            } else if ($('.payroll-checkbox:checked').length === $('.payroll-checkbox').length) {
                $('#select-all').prop('checked', true);
            }
        });

        // Bulk Generate Payslip
        $(document).on('click', '#bulkGeneratePayslip', function() {
            let ids = $('.payroll-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (ids.length === 0) {
                toastr.warning('Please select at least one payroll to generate payslip.');
                return;
            }

            if (!confirm('Are you sure you want to generate payslips for the selected payroll(s)?')) {
                return;
            }

            $.ajax({
                url: '/api/payroll/bulk-generate-payslip',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                data: {
                    payroll_ids: ids
                },
                success: function(res) {
                    toastr.success('Payslips generated successfully.');
                    setTimeout(() => window.location.reload(), 1000);
                },
                error: function(err) {
                    toastr.error('An error occurred while generating payslips.');
                }
            });

        });

        // Bulk Delete Payroll
        $(document).on('click', '#bulkDeletePayroll', function() {
            let ids = $('.payroll-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (ids.length === 0) {
                toastr.warning('Please select at least one payroll to delete.');
                return;
            }

            if (!confirm('Are you sure you want to delete the selected payroll(s)?')) {
                return;
            }

            $.ajax({
                url: '/api/payroll/bulk-delete',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                data: {
                    payroll_ids: ids
                },
                success: function(res) {
                    toastr.success('Selected payroll(s) deleted successfully.');
                    setTimeout(() => window.location.reload(), 1000);
                },
                error: function(err) {
                    toastr.error('An error occurred while deleting payroll(s).');
                }
            });
        });

        // Bulk Bank Report
        $(document).on('click', '#bulkBankReport', function() {
            let ids = [];
            let table = $('#payrollTable').DataTable();

            table.rows().every(function() {
                let row = this.node();
                let checkbox = $(row).find('.payroll-checkbox');
                if (checkbox.is(':checked')) {
                    ids.push(checkbox.val());
                }
            });

            if (ids.length === 0) {
                toastr.warning('Please select at least one payroll to generate bank report.');
                return;
            }

            if (!confirm('Are you sure you want to generate bank report for the selected payroll(s)?')) {
                return;
            }

            $.ajax({
                url: '/api/payroll/bulk-generate-bank-reports',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                data: {
                    payroll_ids: ids
                },
                success: function(res) {
                    toastr.success('Bank report generated successfully.');
                    window.location.href = window.URL.createObjectURL(new Blob([res], {
                        type: 'text/csv'
                    }));
                },
                error: function(err) {
                    toastr.error('An error occurred while generating bank report.');
                }
            });
        });
    </script>

    {{-- Modal Populate --}}
    <script>
        // Deminimis Benefits (Mapping)
        const deminimisBenefits = @json($deminimisBenefits);

        // Parse JSON safely
        function parseJSONSafe(data) {
            if (!data) return [];
            try {
                return JSON.parse(data);
            } catch {
                return [];
            }
        }

        // Decode HTML entities (for double-quoted data attributes with htmlspecialchars)
        function htmlDecode(input) {
            var e = document.createElement('textarea');
            e.innerHTML = input;
            return e.value;
        }

        $(document).on('click', '.edit-payroll-btn', function() {
            const $btn = $(this);

            const payrollId = $btn.data('id');

            if (!payrollId) {
                toastr.error("Invalid payroll ID.");
                return;
            }

            // Populate all modal fields
            $('#payroll_id').val(payrollId);
            $('#payroll_type').val($btn.data('payroll-type'));
            $('#payroll_period').val($btn.data('payroll-period'));
            $('#payroll_period_start').val($btn.data('payroll-period-start'));
            $('#payroll_period_end').val($btn.data('payroll-period-end'));

            $('#total_worked_minutes').val($btn.data('total-worked-minutes'));
            $('#total_late_minutes').val($btn.data('total-late-minutes'));
            $('#total_undertime_minutes').val($btn.data('total-undertime-minutes'));
            $('#total_overtime_minutes').val($btn.data('total-overtime-minutes'));
            $('#total_night_differential_minutes').val($btn.data('total-night-differential-minutes'));
            $('#total_overtime_night_differential_minutes').val($btn.data('total-overtime-night-diff-minutes'));

            $('#holiday_pay').val($btn.data('holiday-pay'));
            $('#leave_pay').val($btn.data('leave-pay'));
            $('#overtime_pay').val($btn.data('overtime-pay'));
            $('#night_differential_pay').val($btn.data('night-differential-pay'));
            $('#overtime_night_differential_pay').val($btn.data('overtime-night-diff-pay'));
            $('#overtime_restday_pay').val($btn.data('overtime-restday-pay'));
            $('#late_deduction').val($btn.data('late-deduction'));
            $('#undertime_deduction').val($btn.data('undertime-deduction'));
            $('#absent_deduction').val($btn.data('absent-deduction'));

            $('#sss_contribution').val($btn.data('sss-contribution'));
            $('#philhealth_contribution').val($btn.data('philhealth-contribution'));
            $('#pagibig_contribution').val($btn.data('pagibig-contribution'));
            $('#withholding_tax').val($btn.data('withholding-tax'));

            $('#total_earnings').val($btn.data('total-earnings'));
            $('#total_deduction').val($btn.data('total-deductions'));
            $('#basic_pay').val($btn.data('basic-pay'));
            $('#gross_pay').val($btn.data('gross-pay'));
            $('#net_salary').val($btn.data('net-salary'));
            $('#payment_date').val($btn.data('payment-date'));
            $('#processed_by').val($btn.data('processed-by'));
            $('#displayTotalMinutes').val($btn.data('work-formatted'));

            // ---- DEMINIMIS JSON FIELD (with auto-fix for html-encoded attributes) ----
            let raw = $btn.attr('data-deminimis');
            let decodedRaw = htmlDecode(raw);
            let deminimisArr = parseJSONSafe(decodedRaw);
            if (!deminimisArr.length) {
                deminimisArr = parseJSONSafe(raw);
            }

            let html = '';
            if (Array.isArray(deminimisArr) && deminimisArr.length) {
                deminimisArr.forEach((item, idx) => {
                    const benefitName = deminimisBenefits[item.deminimis_benefit_id] ||
                        `Unknown (${item.deminimis_benefit_id})`;
                    html += `
                <div class="col-md-3 mb-3">
                    <label class="form-label">${benefitName}</label>
                    <input type="number" step="0.01" class="form-control"
                        name="deminimis_amounts[${item.deminimis_benefit_id}]"
                        value="${item.amount}">
                </div>
            `;
                });
                $('#deminimis_heading').show();
                $('#deminimis_fields').show().html(html);
            } else {
                $('#deminimis_heading').hide();
                $('#deminimis_fields').hide().html('');
            }

            // ---- EARNINGS JSON FIELD (with auto-fix for html-encoded attributes) ---- //
            let earningsRaw = $btn.attr('data-earnings');
            let earningsDecoded = htmlDecode(earningsRaw);
            let earningsArr = parseJSONSafe(earningsDecoded);
            if (!earningsArr.length) {
                earningsArr = parseJSONSafe(earningsRaw);
            }

            let earningsHtml = '';
            if (Array.isArray(earningsArr) && earningsArr.length) {
                earningsArr.forEach(function(item, idx) {
                    earningsHtml += `
            <div class="col-md-3 mb-3">
                <label class="form-label">${item.earning_type_name}</label>
                <input type="number" step="0.01" class="form-control"
                    name="earnings[${item.earning_type_id}][applied_amount]"
                    value="${item.applied_amount}">
            </div>
        `;
                });
                $('#earnings_heading').show();
                $('#earnings_fields').show().html(earningsHtml);
            } else {
                $('#earnings_heading').hide();
                $('#earnings_fields').hide().html('');
            }

            // ---- ALLOWANCE JSON FIELD (with auto-fix for html-encoded attributes) ---- //
            let allowanceRaw = $btn.attr('data-allowance');
            let allowanceDecoded = htmlDecode(allowanceRaw);
            let allowanceArr = parseJSONSafe(allowanceDecoded);
            if (!allowanceArr.length) {
                allowanceArr = parseJSONSafe(allowanceRaw);
            }

            let allowanceHtml = '';
            if (Array.isArray(allowanceArr) && allowanceArr.length) {
                allowanceArr.forEach(function(item, idx) {
                    // Use allowance_id for the input name to match your JSON structure
                    allowanceHtml += `
            <div class="col-md-3 mb-3">
                <label class="form-label">${item.allowance_name}</label>
                <input type="number" step="0.01" class="form-control"
                    name="allowances[${item.allowance_id}][applied_amount]"
                    value="${item.applied_amount}">
            </div>
        `;
                });
                $('#allowance_heading').show();
                $('#allowance_fields').show().html(allowanceHtml);
            } else {
                $('#allowance_heading').hide();
                $('#allowance_fields').hide().html('');
            }

            // ---- DEDUCTIONS JSON FIELD (with auto-fix for html-encoded attributes) ----
            let deductionsRaw = $btn.attr('data-deductions');
            let deductionsDecoded = htmlDecode(deductionsRaw);
            let deductionsArr = parseJSONSafe(deductionsDecoded);
            if (!deductionsArr.length) {
                deductionsArr = parseJSONSafe(deductionsRaw);
            }

            let deductionsHtml = '';
            if (Array.isArray(deductionsArr) && deductionsArr.length) {
                deductionsArr.forEach(function(item, idx) {
                    deductionsHtml += `
                <div class="col-md-3 mb-3">
                    <label class="form-label">${item.deduction_type_name}</label>
                    <input type="number" step="0.01" class="form-control"
                        name="deductions[${item.deduction_type_id}][applied_amount]"
                        value="${item.applied_amount}">
                </div>
            `;
                });
                $('#deductions_heading').show();
                $('#deductions_fields').show().html(deductionsHtml);
            } else {
                $('#deductions_heading').hide();
                $('#deductions_fields').hide().html('');
            }
        });
    </script>

    {{-- Edit Form Submission --}}
    <script>
        $('#editPayrollForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const payrollId = $('#payroll_id').val();
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            const authToken = localStorage.getItem('token');

            $.ajax({
                url: '/api/payroll/update/' + payrollId,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Authorization': 'Bearer ' + authToken
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    toastr.success("Payroll has been updated successfully!");
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                },
                error: function(err) {
                    console.error('Update error response:', err);
                    if (err.responseJSON && err.responseJSON.message) {
                        toastr.error(err.responseJSON.message);
                    } else {
                        toastr.error("An error occurred while updating payroll.");
                    }
                }
            });
        });
    </script>

    {{-- Date Range Filter --}}
    <script>
        if ($('.bookingrange-filtered').length > 0) {
            var start = moment().startOf('month');
            var end = moment().endOf('month');

            function booking_range(start, end) {
                $('.bookingrange-filtered span').html(start.format('M/D/YYYY') + ' - ' + end.format('M/D/YYYY'));
            }

            $('.bookingrange-filtered').daterangepicker({
                startDate: start,
                endDate: end,
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

        $('#dateRange_filter').on('apply.daterangepicker', function(ev, picker) {
            filter();
        });

        function filter() {
            const branch = $('#branch_filter').val();
            const department = $('#department_filter').val();
            const designation = $('#designation_filter').val();
            const dateRange = $('#dateRange_filter').val();

            $.ajax({
                url: '{{ route('payroll-process-filter') }}',
                type: 'GET',
                data: {
                    branch,
                    department,
                    designation,
                    dateRange,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#payrollTable').DataTable().destroy();
                        $('#payrollTableBody').html(response.html);
                        $('#payrollTable').DataTable();
                    } else {
                        toastr.error(response.message || 'Something went wrong.');
                    }
                },
                error: function(xhr) {
                    let message = 'An unexpected error occurred.';
                    if (xhr.status === 403) {
                        message = 'You are not authorized to perform this action.';
                    } else if (xhr.responseJSON?.message) {
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

    {{-- Export to PDF/Excel --}}
    <script>
        // Export functionality
        $(document).on('click', '.export-pdf-btn', function(e) {
            e.preventDefault();
            exportData('pdf');
        });

        $(document).on('click', '.export-excel-btn', function(e) {
            e.preventDefault();
            exportData('excel');
        });

        function exportData(format) {
            // Get current filters
            const branch = $('#branch_filter').val();
            const department = $('#department_filter').val();
            const designation = $('#designation_filter').val();
            const dateRange = $('#dateRange_filter').val();

            // Build URL with filters
            const baseUrl = format === 'pdf' ? '/payroll/export-pdf' : '/payroll/export-excel';
            const params = new URLSearchParams();

            if (branch) params.append('branch', branch);
            if (department) params.append('department', department);
            if (designation) params.append('designation', designation);
            if (dateRange) params.append('dateRange', dateRange);

            const url = baseUrl + (params.toString() ? '?' + params.toString() : '');

            // Show loading message
            toastr.info(`Generating ${format.toUpperCase()} export... Please wait.`);

            // Use AJAX for better error handling
            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data, status, xhr) {
                    // Create blob and download
                    const blob = new Blob([data]);
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');

                    // Get filename from response header or create default
                    let filename =
                        `payroll_export_${new Date().toISOString().split('T')[0]}.${format === 'pdf' ? 'pdf' : 'xlsx'}`;
                    const disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        filename = disposition.split('filename=')[1].replace(/"/g, '');
                    }

                    link.href = downloadUrl;
                    link.download = filename;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    // Clean up
                    window.URL.revokeObjectURL(downloadUrl);

                    toastr.success(`${format.toUpperCase()} export completed successfully!`);
                },
                error: function(xhr) {
                    let message = 'Export failed. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    toastr.error(message);
                }
            });
        }
    </script>

    {{-- 13th Month Export to PDF/Excel --}}
    <script>
        // Store the export format when button is clicked
        let selectedExportFormat = null;

        // Show modal when export button is clicked
        $(document).on('click', '.thirteenth-export-pdf-btn', function(e) {
            e.preventDefault();
            selectedExportFormat = 'pdf';
            $('#export_format_type').val('pdf');
            $('#export_status_dropdown').val(''); // Reset dropdown
            $('#thirteenth_month_export_modal').modal('show');
        });

        $(document).on('click', '.thirteenth-export-excel-btn', function(e) {
            e.preventDefault();
            selectedExportFormat = 'excel';
            $('#export_format_type').val('excel');
            $('#export_status_dropdown').val(''); // Reset dropdown
            $('#thirteenth_month_export_modal').modal('show');
        });

        // ✅ NEW: Handle confirm button click (reads from dropdown)
        $(document).on('click', '#confirm_export_button', function() {
            const status = $('#export_status_dropdown').val(); // Get selected status from dropdown
            const format = selectedExportFormat;

            // Hide modal
            $('#thirteenth_month_export_modal').modal('hide');

            // Show selected option feedback
            const statusText = status === '' ? 'All Status' : status;
            toastr.info(`Exporting ${statusText} 13th Month Pay as ${format.toUpperCase()}...`);

            // Trigger export with selected status
            exportThirteenthMonthData(format, status);
        });

        function exportThirteenthMonthData(format, status = '') {
            // Get current filters
            const branch = $('#branch_filter').val();
            const department = $('#department_filter').val();
            const designation = $('#designation_filter').val();
            const dateRange = $('#dateRange_filter').val();
            const year = $('#yearSelect').val();

            // Build URL with filters
            const baseUrl = format === 'pdf' ? '/api/13th-month-pay/export/pdf' : '/api/13th-month-pay/export/excel';
            const params = new URLSearchParams();

            if (branch) params.append('branch', branch);
            if (department) params.append('department', department);
            if (designation) params.append('designation', designation);
            if (dateRange) params.append('dateRange', dateRange);
            if (year) params.append('year', year);

            // Add status filter
            if (status !== '') {
                params.append('status', status);
            }

            const url = baseUrl + (params.toString() ? '?' + params.toString() : '');

            // Show loading message
            const statusText = status === '' ? 'All Status' : status;
            toastr.info(`Generating ${statusText} 13th Month ${format.toUpperCase()} export... Please wait.`, '', {
                timeOut: 0,
                extendedTimeOut: 0,
                closeButton: true
            });

            // Use AJAX for better error handling
            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data, statusResponse, xhr) {
                    // Clear loading message
                    toastr.clear();

                    // Check if response is empty
                    if (data.size === 0) {
                        toastr.warning(`No ${statusText} 13th month pay records found to export.`);
                        return;
                    }

                    // Create blob and download
                    const blob = new Blob([data]);
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');

                    // Get filename from response header or create default
                    const statusSuffix = status === '' ? 'all' : status.toLowerCase();
                    let filename =
                        `13th-month-pay-${statusSuffix}-${new Date().toISOString().split('T')[0]}.${format === 'pdf' ? 'pdf' : 'xlsx'}`;

                    const disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        filename = disposition.split('filename=')[1].replace(/"/g, '');
                    }

                    link.href = downloadUrl;
                    link.download = filename;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    // Clean up
                    window.URL.revokeObjectURL(downloadUrl);

                    toastr.success(
                        `${statusText} 13th Month ${format.toUpperCase()} export completed successfully!`);
                },
                error: function(xhr) {
                    // Clear loading message
                    toastr.clear();

                    let message = 'Export failed. Please try again.';

                    // Try to read error message from blob
                    if (xhr.responseText) {
                        try {
                            const errorData = JSON.parse(xhr.responseText);
                            if (errorData.message) {
                                message = errorData.message;
                            }
                        } catch (e) {
                            // If not JSON, check if it's a blob error
                            const reader = new FileReader();
                            reader.onload = function() {
                                try {
                                    const errorData = JSON.parse(reader.result);
                                    if (errorData.message) {
                                        toastr.error(errorData.message);
                                        return;
                                    }
                                } catch (parseError) {
                                    // Continue with default message
                                }
                                toastr.error(message);
                            };
                            reader.readAsText(xhr.response);
                            return;
                        }
                    }

                    toastr.error(message);
                }
            });
        }
    </script>

    {{-- 13th Month Table --}}
    <script>
        $(document).ready(function() {
            // Initialize 13th month table datatable (check if not already initialized)
            if ($('#thirteenthMonthTable').length > 0) {
                if (!$.fn.DataTable.isDataTable('#thirteenthMonthTable')) {
                    $('#thirteenthMonthTable').DataTable({
                        ordering: true,
                        searching: true,
                        paging: true,
                        info: true
                    });
                }
            }

            // Select/Deselect all 13th month checkboxes
            $(document).on('change', '#select-all-13th', function() {
                $('.thirteenth-month-checkbox').prop('checked', this.checked);
            });

            // Uncheck select-all if any checkbox is unchecked
            $(document).on('change', '.thirteenth-month-checkbox', function() {
                if (!this.checked) {
                    $('#select-all-13th').prop('checked', false);
                } else if ($('.thirteenth-month-checkbox:checked').length === $(
                        '.thirteenth-month-checkbox').length) {
                    $('#select-all-13th').prop('checked', true);
                }
            });

            // View 13th Month Details Modal
            $(document).on('click', '.view-13th-month-btn', function() {
                const $btn = $(this);

                $('#modal_user_name').text($btn.data('user-name'));
                $('#modal_coverage').text($btn.data('coverage-start') + ' - ' + $btn.data('coverage-end'));
                $('#modal_year').text($btn.data('year'));
                $('#modal_total_basic_pay').text('₱' + parseFloat($btn.data('total-basic-pay'))
                    .toLocaleString('en-US', {
                        minimumFractionDigits: 2
                    }));
                $('#modal_total_deductions').text('₱' + parseFloat($btn.data('total-deductions'))
                    .toLocaleString('en-US', {
                        minimumFractionDigits: 2
                    }));
                $('#modal_total_13th').text('₱' + parseFloat($btn.data('total-thirteenth-month'))
                    .toLocaleString('en-US', {
                        minimumFractionDigits: 2
                    }));
                $('#modal_payment_date').text(new Date($btn.data('payment-date')).toLocaleDateString(
                    'en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    }));

                const status = $btn.data('status');
                const statusBadge = $('#modal_status');
                statusBadge.removeClass('badge-soft-warning badge-soft-success badge-soft-secondary');
                if (status === 'Pending') {
                    statusBadge.addClass('badge-soft-warning');
                } else if (status === 'Paid') {
                    statusBadge.addClass('badge-soft-success');
                } else {
                    statusBadge.addClass('badge-soft-secondary');
                }
                statusBadge.text(status);

                $('#modal_processor').text($btn.data('processor-name'));

                // Monthly Breakdown
                const breakdown = $btn.data('monthly-breakdown');
                let rows = '';
                if (Array.isArray(breakdown)) {
                    // Sort breakdown by year and month in chronological order
                    breakdown.sort((a, b) => {
                        // First compare by year
                        if (a.year !== b.year) {
                            return a.year - b.year;
                        }
                        // If same year, compare by month
                        return a.month - b.month;
                    });

                    breakdown.forEach(month => {
                        // Avoid duplicating the year if month.month_name already contains it
                        let monthName = month.month_name || '';
                        const yearStr = String(month.year || '');
                        const containsYear = /\b\d{4}\b/.test(monthName) || monthName.includes(
                            yearStr);
                        const monthYear = containsYear ? monthName.trim() :
                            `${monthName} ${yearStr}`.trim();

                        rows += `
                    <tr>
                        <td class="fw-medium">${monthYear}</td>
                        <td class="text-center">${month.payroll_count}</td>
                        <td class="text-end">₱${parseFloat(month.basic_pay || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td class="text-end">₱${parseFloat(month.leave_pay || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td class="text-end text-danger">₱${parseFloat(month.late_deduction || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td class="text-end text-danger">₱${parseFloat(month.undertime_deduction || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td class="text-end text-danger">₱${parseFloat(month.absent_deduction || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td class="text-end text-success fw-bold">₱${parseFloat(month.thirteenth_month_contribution || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    </tr>
                `;
                    });
                }
                $('#modal_monthly_breakdown').html(rows);
            });

            // Delete 13th Month Pay
            let thirteenthMonthDeleteId = null;
            $(document).on('click', '.btn-delete-13th', function() {
                thirteenthMonthDeleteId = $(this).data('id');
                const name = $(this).data('name');
                $('#thirteenthMonthPlaceholder').text(name);
            });

            $('#thirteenthMonthConfirmDeleteBtn').on('click', function() {
                if (!thirteenthMonthDeleteId) return;

                $.ajax({
                    url: `/api/13th-month-pay/delete/${thirteenthMonthDeleteId}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    success: function(res) {
                        toastr.success('13th Month Pay deleted successfully.');
                        const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                            'delete_13th_month'));
                        deleteModal.hide();
                        setTimeout(() => window.location.reload(), 1000);
                    },
                    error: function(err) {
                        toastr.error('Error deleting 13th month pay.');
                    }
                });
            });

            // Bulk Generate 13th Month Payslip
            $(document).on('click', '#bulkGenerate13thMonthPayslip', function() {
                let ids = $('.thirteenth-month-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (ids.length === 0) {
                    toastr.warning('Please select at least one 13th month pay to generate payslip.');
                    return;
                }

                if (!confirm('Are you sure you want to Approved and Released this 13th month pay?')) {
                    return;
                }

                $.ajax({
                    url: '/api/13th-month-pay/bulk-generate-payslip',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    data: {
                        thirteenth_month_ids: ids
                    },
                    success: function(res) {
                        toastr.success('Selected 13th month pay marked as Paid.');
                        setTimeout(() => window.location.reload(), 1000);
                    },
                    error: function(err) {
                        toastr.error('An error occurred while generating payslips.');
                    }
                });
            });

            // Bulk Delete 13th Month
            $(document).on('click', '#bulkDelete13thMonth', function() {
                let ids = $('.thirteenth-month-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (ids.length === 0) {
                    toastr.warning('Please select at least one 13th month pay to delete.');
                    return;
                }

                if (!confirm('Are you sure you want to delete the selected 13th month pay records?')) {
                    return;
                }

                $.ajax({
                    url: '/api/13th-month-pay/bulk-delete',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    data: {
                        thirteenth_month_ids: ids
                    },
                    success: function(res) {
                        toastr.success('Selected 13th month pay deleted successfully.');
                        setTimeout(() => window.location.reload(), 1000);
                    },
                    error: function(err) {
                        toastr.error('An error occurred while deleting 13th month pay.');
                    }
                });
            });
        });
    </script>
@endpush
