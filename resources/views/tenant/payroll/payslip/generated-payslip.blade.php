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
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Payroll
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Generated Payslip</li>
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
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" id="bulkDownloadPayslip">
                                        <i class="ti ti-download me-1"></i>Download
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
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class=" form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select" style="width:150px;"
                                onchange="filter()">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select" style="width:150px;"
                                onchange="filter()">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select" style="width:150px;"
                                onchange="filter()">
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
                                                <input class="form-check-input payroll-checkbox" type="checkbox">
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
                                                        class="fs-12">{{ $payslip->user->employmentDetail->department->department_name }}</span>
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
                        $("#generatedPayslipsTable").DataTable().destroy();
                        $('#generatedPayslipsTableBody').html(response.html);
                        $("#generatedPayslipsTable").DataTable();
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
@endpush
