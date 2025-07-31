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
                                    <i class="ti ti-file-export me-1"></i>Export
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                                class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                                class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
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
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Payroll Form Process</h5>
                        </div>
                        <div class="card-body">
                            <form id="payrollProcessForm" class="row g-4">
                                <!-- Payroll Details Section -->
                                <div class="col-xl-5">
                                    <div class="mb-3 row align-items-center">
                                        <label for="payrollType" class="col-sm-4 col-form-label">Payroll Type</label>
                                        <div class="col-sm-8">
                                            <select class="form-select" name="payroll_type" id="payrollType" required>
                                                <option value="" disabled selected>Select</option>
                                                <option value="normal_payroll">Normal Payroll</option>
                                                <option value="13th_month">13th Month</option>
                                                <option value="final_pay">Final Pay</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 row align-items-center">
                                        <label for="yearSelect" class="col-sm-4 col-form-label">Year</label>
                                        <div class="col-sm-8">
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
                                    </div>
                                    <div class="mb-3 row align-items-center">
                                        <label for="monthSelect" class="col-sm-4 col-form-label">Month</label>
                                        <div class="col-sm-8">
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
                                    <div class="mb-3 row align-items-center">
                                        <label for="startDate" class="col-sm-4 col-form-label">Start Date</label>
                                        <div class="col-sm-8">
                                            <input type="date" class="form-control" name="start_date" id="startDate"
                                                required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row align-items-center">
                                        <label for="endDate" class="col-sm-4 col-form-label">End Date</label>
                                        <div class="col-sm-8">
                                            <input type="date" class="form-control" name="end_date" id="endDate"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assignment Section -->
                                <div class="col-xl-5">
                                    <div class="mb-3 row align-items-center">
                                        <label for="transactionDate" class="col-sm-4 col-form-label">Transaction
                                            Date</label>
                                        <div class="col-sm-8">
                                            <input type="date" class="form-control" name="transaction_date"
                                                id="transactionDate" value="{{ $currentDate }}" required>
                                        </div>
                                    </div>

                                    <div class="mb-3 row align-items-center">
                                        <label for="assignmentType" class="col-sm-4 col-form-label">Assignment Type</label>
                                        <div class="col-sm-8">
                                            <select name="assignment_type" id="assignmentType" class="form-select"
                                                required>
                                                <option value="">Select</option>
                                                <option value="payroll_batch">Payroll Batch</option>
                                                <option value="manual">Manual</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Payroll Batch --}}
                                    <div class="payroll-batch" style="display: none;">
                                        <div class="mb-3 row align-items-center">
                                            <label class="col-sm-4 col-form-label">Select Payroll Batch</label>
                                            <div class="col-sm-8">
                                                <select name="payroll_batch_id" id="payrollBatchId" class="form-select">
                                                    <option value="" disabled selected>Select Payroll Batch</option>
                                                    @foreach ($payrollBatches as $payrollBatch)
                                                        <option value="{{ $payrollBatch->id }}">{{ $payrollBatch->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Manual Assigning --}}
                                    <div class="manual-assigning" style="display: none;">
                                        <div class="mb-3 row align-items-center">
                                            <label for="payrollProcessBranchId"
                                                class="col-sm-4 col-form-label">Branch</label>
                                            <div class="col-sm-8">
                                                <select name="branch_id[]" id="payrollProcessBranchId"
                                                    class="form-select select2 branch-select" multiple required>
                                                    <option value="">All Branch</option>
                                                    @foreach ($branches as $branch)
                                                        <option value="{{ $branch->id }}">{{ $branch->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 row align-items-center">
                                            <label for="payrollProcessDepartmentId"
                                                class="col-sm-4 col-form-label">Department</label>
                                            <div class="col-sm-8">
                                                <select name="department_id[]" id="payrollProcessDepartmentId"
                                                    class="form-select select2 department-select" multiple required>
                                                    <option value="">All Department</option>
                                                    @foreach ($departments as $department)
                                                        <option value="{{ $department->id }}">
                                                            {{ $department->department_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 row align-items-center">
                                            <label for="payrollProcessDesignationId"
                                                class="col-sm-4 col-form-label">Designation</label>
                                            <div class="col-sm-8">
                                                <select name="designation_id[]" id="payrollProcessDesignationId"
                                                    class="form-select select2 designation-select" multiple required>
                                                    <option value="">All Designation</option>
                                                    @foreach ($designations as $designation)
                                                        <option value="{{ $designation->id }}">
                                                            {{ $designation->designation_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 row align-items-center">
                                            <label for="payrollProcessUserId"
                                                class="col-sm-4 col-form-label">Employee</label>
                                            <div class="col-sm-8">
                                                <select name="user_id[]" id="payrollProcessUserId"
                                                    class="form-select select2 employee-select" multiple required>
                                                    <option value="">All Employee</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Government Mandates Section -->
                                <div class="col-xl-2">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <div class="mb-3">
                                                <label class="form-label mb-2">SSS</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="sss_option"
                                                            id="sssYes" value="yes" required>
                                                        <label class="form-check-label" for="sssYes">Yes</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="sss_option"
                                                            id="sssNo" value="no" required>
                                                        <label class="form-check-label" for="sssNo">No</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="sss_option"
                                                            id="sssFull" value="full" required>
                                                        <label class="form-check-label" for="sssFull">Full</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label mb-2">PhilHealth</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="philhealth_option" id="philhealthYes" value="yes"
                                                            required>
                                                        <label class="form-check-label" for="philhealthYes">Yes</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="philhealth_option" id="philhealthNo" value="no"
                                                            required>
                                                        <label class="form-check-label" for="philhealthNo">No</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="philhealth_option" id="philhealthFull" value="full"
                                                            required>
                                                        <label class="form-check-label" for="philhealthFull">Full</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label mb-2">Pag-IBIG</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="pagibig_option" id="pagibigYes" value="yes"
                                                            required>
                                                        <label class="form-check-label" for="pagibigYes">Yes</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="pagibig_option" id="pagibigNo" value="no" required>
                                                        <label class="form-check-label" for="pagibigNo">No</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="pagibig_option" id="pagibigFull" value="full"
                                                            required>
                                                        <label class="form-check-label" for="pagibigFull">Full</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="form-label mb-2">Cut-off Period</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="cutoff_period" id="cutoffOne" value="1" required>
                                                        <label class="form-check-label" for="cutoffOne">1</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="cutoff_period" id="cutoffTwo" value="2" required>
                                                        <label class="form-check-label" for="cutoffTwo">2</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="cutoff_period" id="cutoffWeekly" value="weekly"
                                                            required>
                                                        <label class="form-check-label" for="cutoffWeekly">Weekly</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        @if (in_array('Create', $permission))
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="ti ti-settings me-1"></i>
                                                Process Payroll
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hide --}}
            @if ($payrolls->count() > 0 || $payrolls->where('status', 'Pending')->count() > 0)
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Processed</h5>
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


                            <div class="dropdown">
                                <a href="javascript:void(0);"
                                    class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    Sort By : Last 7 Days
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Recently Added</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Ascending</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Desending</a>
                                    </li>
                                </ul>
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
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
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
                                                        <span class="fs-12"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $payroll->user->employmentDetail->branch->name ?? '' }}</td>
                                            <td>₱{{ number_format($payroll->total_deductions, 2) }}</td>
                                            <td>₱{{ number_format($payroll->total_earnings, 2) }}</td>
                                            <td class="text-danger">₱{{ number_format($payroll->net_salary, 2) }}</td>
                                            <td>
                                                <div class="action-icon d-inline-flex">
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
                                                        title="Edit"><i class="ti ti-edit"></i></a>
                                                    <a href="javascript:void(0);" class="btn-delete"
                                                        data-bs-toggle="modal" data-bs-target="#delete_payroll"
                                                        data-id="{{ $payroll->id }}"
                                                        data-name="{{ $payroll->user->personalInformation->full_name }}"
                                                        title="Delete"><i class="ti ti-trash"></i></a>
                                                </div>
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

    <!-- Modal -->
    <div class="modal fade" id="edit_payroll" tabindex="-1" aria-labelledby="payrollModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="max-width: 85%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="payrollModalLabel">Payroll Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editPayrollForm" enctype="multipart/form-data">
                        <!-- Payroll Details -->
                        <div class="row">
                            <input type="hidden" id="payroll_id" name="payroll_id">
                            <div class="col-md-3 mb-4">
                                <label for="payroll_type" class="form-label">Payroll Type</label>
                                <input type="text" class="form-control" id="payroll_type" name="payroll_type">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label for="payroll_period" class="form-label">Payroll Period</label>
                                <input type="text" class="form-control" id="payroll_period" name="payroll_period">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label for="payroll_period_start" class="form-label">Payroll Period Start</label>
                                <input type="date" class="form-control" id="payroll_period_start"
                                    name="payroll_period_start">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label for="payroll_period_end" class="form-label">Payroll Period End</label>
                                <input type="date" class="form-control" id="payroll_period_end"
                                    name="payroll_period_end">
                            </div>
                        </div>
                        <!-- Time Tracking Fields -->
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label for="total_worked_minutes" class="form-label">Worked Minutes</label>
                                <input type="number" class="form-control" id="total_worked_minutes"
                                    name="total_worked_minutes">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="total_late_minutes" class="form-label">Late Minutes</label>
                                <input type="number" class="form-control" id="total_late_minutes"
                                    name="total_late_minutes">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="total_undertime_minutes" class="form-label">Undertime Minutes</label>
                                <input type="number" class="form-control" id="total_undertime_minutes"
                                    name="total_undertime_minutes">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="total_overtime_minutes" class="form-label">Overtime Minutes</label>
                                <input type="number" class="form-control" id="total_overtime_minutes"
                                    name="total_overtime_minutes">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="total_night_differential_minutes" class="form-label">Night Differential
                                    Minutes</label>
                                <input type="number" class="form-control" id="total_night_differential_minutes"
                                    name="total_night_differential_minutes">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="total_overtime_night_differential_minutes" class="form-label">OT Night
                                    Differential Minutes</label>
                                <input type="number" class="form-control" id="total_overtime_night_differential_minutes"
                                    name="total_overtime_night_differential_minutes">
                            </div>
                        </div>
                        <!-- Pay Breakdown -->
                        <h4 class="mb-3 text-primary">Pay Breakdown</h4>
                        <div class="row">
                            <div class="col-md-3 mb-4">
                                <label for="holiday_pay" class="form-label">Holiday Pay</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="holiday_pay" name="holiday_pay"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label for="leave_pay" class="form-label">Leave Pay</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="leave_pay" name="leave_pay"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label for="overtime_pay" class="form-label">Overtime Pay</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="overtime_pay" name="overtime_pay"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label for="night_differential_pay" class="form-label">Night Differential Pay</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="night_differential_pay"
                                        name="night_differential_pay" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label for="overtime_night_differential_pay" class="form-label">Overtime Night
                                    Differential Pay</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="overtime_night_differential_pay"
                                        name="overtime_night_differential_pay" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label for="late_deduction" class="form-label">Late Deduction</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="late_deduction" name="late_deduction"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label for="undertime_deduction" class="form-label">Undertime Deduction</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="undertime_deduction"
                                        name="undertime_deduction" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label for="absent_deduction" class="form-label">Absent Deduction</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="absent_deduction"
                                        name="absent_deduction" step="0.01">
                                </div>
                            </div>
                        </div>

                        <!-- Earnings Section -->
                        <h4 id="earnings_heading" class="mb-3 text-primary">Earnings</h4>
                        <div id="earnings_fields" class="row"></div>

                        <!-- Deductions Section -->
                        <h4 id="deductions_heading" class="mb-3 text-primary">Deductions</h4>
                        <div id="deductions_fields" class="row"></div>

                        <!-- Deminimis Section -->
                        <h4 id="deminimis_heading" class="mb-3 text-primary">Deminimis Benefits</h4>
                        <div id="deminimis_fields" class="row"></div>

                        <!-- Government Mandates -->
                        <h4 class="mb-3 text-primary">Government Mandates Fields</h4>
                        <div class="row">
                            <div class="col-md-3 mb-4">
                                <label for="sss_contribution" class="form-label">SSS Contribution</label>
                                <input type="number" class="form-control" id="sss_contribution" name="sss_contribution"
                                    step="0.01">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label for="philhealth_contribution" class="form-label">PhilHealth Contribution</label>
                                <input type="number" class="form-control" id="philhealth_contribution"
                                    name="philhealth_contribution" step="0.01">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label for="pagibig_contribution" class="form-label">PagIBIG Contribution</label>
                                <input type="number" class="form-control" id="pagibig_contribution"
                                    name="pagibig_contribution" step="0.01">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label for="withholding_tax" class="form-label">Withholding Tax</label>
                                <input type="number" class="form-control" id="withholding_tax" name="withholding_tax"
                                    step="0.01">
                            </div>
                        </div>
                        <!-- Salary Breakdown -->
                        <h5 class="mb-3 text-primary">Salary Breakdown</h5>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="total_earnings" class="form-label">Total Earnings</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="total_earnings" name="total_earnings"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="total_deduction" class="form-label">Total Deduction</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="total_deduction"
                                        name="total_deductions" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="basic_pay" class="form-label">Basic Pay</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="basic_pay" name="basic_pay"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="gross_pay" class="form-label">Gross Pay</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="gross_pay" name="gross_pay"
                                        step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="net_salary" class="form-label">Net Salary</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control text-danger" id="net_salary"
                                        name="net_salary" step="0.01">
                                </div>
                            </div>
                        </div>
                        <!-- Payment Information -->
                        <h4 class="mb-3 text-primary">Payment Information</h4>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="payment_date" class="form-label">Payment Date</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Processed By</label>
                                <input type="text" class="form-control" id="processed_by" name="processed_by"
                                    readonly>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Payroll</button>
                            </div>
                        </div>
                    </form>
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
        $('#payrollProcessForm').on('submit', function(e) {
            e.preventDefault();

            const pagibigOption = $("input[name='pagibig_option']:checked").val();
            if (!pagibigOption) {
                toastr.error("Please select a Pag-IBIG option before processing payroll.");
                return;
            }

            const sssOption = $("input[name='sss_option']:checked").val();
            if (!sssOption) {
                toastr.error("Please select an SSS option before processing payroll.");
                return;
            }

            const philhealthOption = $("input[name='philhealth_option']:checked").val();
            if (!philhealthOption) {
                toastr.error("Please select a PhilHealth option before processing payroll.");
                return;
            }

            const cutoffPeriod = $("input[name='cutoff_period']:checked").val();
            if (!cutoffPeriod) {
                toastr.error("Please select a Cut-off Period before processing payroll.");
                return;
            }

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
                success: function(res) {
                    toastr.success("Payroll has been processed successfully!");
                    // setTimeout(() => {
                    //     window.location.reload();
                    // }, 1000);
                },
                error: function(err) {
                    if (err.responseJSON && err.responseJSON.message) {
                        toastr.error(err.responseJSON.message);
                    } else {
                        toastr.error("An error occurred while processing payroll.");
                    }
                }
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
            let ids = $('.payroll-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

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

            // ---- EARNINGS JSON FIELD (with auto-fix for html-encoded attributes) ----
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
                    console.log('Update success response:', res);
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
@endpush
