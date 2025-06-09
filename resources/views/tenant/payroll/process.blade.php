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
                                <!-- Payroll Type, Year, Month -->
                                <div class="col-xl-6">
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



                                <!-- Branch, Department, Designation, Employee -->
                                <div class="col-xl-6">
                                    <div class="mb-3 row align-items-center">
                                        <label for="transactionDate" class="col-sm-4 col-form-label">Transaction
                                            Date</label>
                                        <div class="col-sm-8">
                                            <input type="date" class="form-control" name="transaction_date"
                                                id="transactionDate" value="{{ $currentDate }}" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row align-items-center">
                                        <label for="payrollProcessBranchId" class="col-sm-4 col-form-label">Branch</label>
                                        <div class="col-sm-8">
                                            <select name="branch_id[]" id="payrollProcessBranchId"
                                                class="form-select select2 branch-select" multiple required>
                                                <option value="">All Branch</option>
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
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
                                        <label for="payrollProcessUserId" class="col-sm-4 col-form-label">Employee</label>
                                        <div class="col-sm-8">
                                            <select name="user_id[]" id="payrollProcessUserId"
                                                class="form-select select2 employee-select" multiple required>
                                                <option value="">All Employee</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary px-4">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            {{-- Hide --}}
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Processed</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">

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
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Deductions</th>
                                    <th>Earnings</th>
                                    <th>Net Pay</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
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
@endpush
