<?php $page = 'thirteenth-month-admin-payslips'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Thirteenth Month Payslip</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Thirteenth Month Payslip</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">

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
                                                class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                                class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
                                    </li>
                                </ul>
                            </div>
                        </div> --}}


                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- 13th Month Pay Analytics -->
            <div class="row">
                <div class="d-flex mb-3 align-items-center gap-2">
                    <label class="fw-semibold me-2">Filter by Year:</label>
                    <select id="analytics-year" class="form-select form-select-sm w-auto">
                        <option value="">All Years</option>
                        @for ($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- 13th Month Pay Statistics -->
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-pink-img">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <span
                                            class="avatar avatar-md rounded-circle bg-white d-flex align-items-center justify-content-center">
                                            <i class="ti ti-calendar-dollar text-pink fs-18"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-1">My 13th Month Pay</p>
                                    <h4 class="mb-0 text-success" id="total-13th-month-amount">₱0.00</h4>
                                    <small class="text-muted">All Periods</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-yellow-img">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <span
                                            class="avatar avatar-md rounded-circle bg-white d-flex align-items-center justify-content-center">
                                            <i class="ti ti-cash text-warning fs-18"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-1">My Total Basic Pay</p>
                                    <h4 class="mb-0 text-primary" id="total-basic-pay">₱0.00</h4>
                                    <small class="text-muted">All Periods</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-blue-img">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <span
                                            class="avatar avatar-md rounded-circle bg-white d-flex align-items-center justify-content-center">
                                            <i class="ti ti-files text-info fs-18"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-1">Total Records</p>
                                    <h4 class="mb-0 text-info" id="total-employees-paid">0</h4>
                                    <small class="text-muted">Matches Filter</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-green-img">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <span
                                            class="avatar avatar-md rounded-circle bg-white d-flex align-items-center justify-content-center">
                                            <i class="ti ti-calculator text-success fs-18"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-1">Average Per Record</p>
                                    <h4 class="mb-0 text-warning" id="average-per-employee">₱0.00</h4>
                                    <small class="text-muted">Calculated</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <!-- 13th Month Pay Distribution by Year -->
                <div class="col-xl-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header border-0 pb-0">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="me-2"><i class="ti ti-chart-bar text-success"></i></span>
                                    <h5 class="mb-0">13th Month Pay by Year</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <div id="thirteenth-month-year-chart"></div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Breakdown Chart -->
                <div class="col-xl-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header border-0 pb-0">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="me-2"><i class="ti ti-chart-line text-primary"></i></span>
                                    <h5 class="mb-0">Monthly Breakdown Trend</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <div id="thirteenth-month-status-chart"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Page Links --}}
            <div class="payroll-btns mb-3">
                <a href="{{ route('generatedPayslipIndex') }}" class="btn btn-white  border me-2">Generated
                    Payslips</a>
                <a href="{{ route('thirteenthMonthPayslipadminIndex') }}"
                    class="btn btn-white active border me-2">Thirteenth Month Payslips</a>
            </div>

            <!-- Generated Payslip list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Thirteenth Month Payslips</h5>
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

                        <div class="form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select"
                                onchange="filter()" style="width:150px;">
                                <option value="" selected>All Branches</option>
                                {{-- @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach --}}
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select"
                                onchange="filter()" style="width:150px;">
                                <option value="" selected>All Departments</option>
                                {{-- @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach --}}
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select"
                                onchange="filter()" style="width:150px;">
                                <option value="" selected>All Designations</option>
                                {{-- @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                @endforeach --}}
                            </select>
                        </div>

                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable" id="thirteenthMonthPayslipsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Employee</th>
                                    <th class="text-center">Branch</th>
                                    <th class="text-center">Covered Period</th>
                                    <th class="text-center">Year</th>
                                    <th class="text-center">13th Month Pay</th>
                                    <th class="text-center">Processed By:</th>
                                    <th class="text-center">Transaction Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="thirteenthMonthPayslipsTableBody">
                                @foreach ($thirteenthMonthPayslips as $payslip)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input select-item" type="checkbox"
                                                    data-id="{{ $payslip->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-md">
                                                    <img src="{{ asset('storage/' . ($payslip->user->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                                                        class="img-fluid rounded-circle" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0">
                                                        <a href="#">
                                                            {{ $payslip->user->personalInformation->last_name ?? '' }}
                                                            {{ $payslip->user->personalInformation->suffix ?? '' }},
                                                            {{ $payslip->user->personalInformation->first_name ?? '' }}
                                                            {{ $payslip->user->personalInformation->middle_name ?? '' }}
                                                        </a>
                                                    </p>
                                                    <span
                                                        class="fs-12">{{ $payslip->user->employmentDetail->department->department_name ?? '' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $payslip->user->employmentDetail->branch->name }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::create($payslip->from_year ?? $payslip->year, $payslip->from_month)->format('F Y') }}
                                            -
                                            {{ \Carbon\Carbon::create($payslip->to_year ?? $payslip->year, $payslip->to_month)->format('F Y') }}
                                        </td>
                                        <td>{{ $payslip->year }}</td>
                                        <td class="text-success fw-bold">
                                            ₱{{ number_format($payslip->total_thirteenth_month, 2) }}</td>
                                        <td>{{ $payslip->processor_name ?? '' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($payslip->payment_date)->format('M d, Y') }}</td>
                                        <td>
                                            @if ($payslip->status === 'Released')
                                                <span class="badge badge-soft-success">Released</span>
                                            @elseif($payslip->status === 'Approved')
                                                <span class="badge badge-soft-warning">Paid</span>
                                            @else
                                                <span class="badge badge-soft-secondary">{{ $payslip->status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                <a href="{{ route('thirteenthMonthPayslipView', $payslip->id) }}"
                                                    class="me-2 edit-payroll-btn" title="View Payslip">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <a href="#" class="btn-revert" data-bs-toggle="modal"
                                                    data-bs-target="#revert_payslip" data-id="{{ $payslip->id }}"
                                                    data-name="{{ $payslip->user->personalInformation->full_name }}"
                                                    title="Edit/Rollback"><i class="ti ti-repeat"></i></a>

                                                <a href="javascript:void(0);" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_payslip" title="Delete"
                                                    data-id="{{ $payslip->id }}"
                                                    data-name="{{ $payslip->user->personalInformation->full_name }}"><i
                                                        class="ti ti-trash"></i></a>

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

    <!-- Delete Payslip Modal -->
    <div class="modal fade" id="delete_payslip">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3">
                        <i class="ti ti-trash-x fs-36"></i>
                    </span>
                    <h4 class="mb-1">Confirm Delete</h4>
                    <p class="mb-3">
                        Are you sure you want to delete this payslip for <strong><span id="payslipPlaceholder"></span></strong>? This can’t be undone.
                    </p>
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</a>
                        <a href="javascript:void(0);" class="btn btn-danger" id="userPayslipConfirmBtn">Yes, Delete</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revert Payslip Modal -->
    <div class="modal fade" id="revert_payslip" tabindex="-1" aria-labelledby="revertPayslipLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <span class="avatar avatar-xl bg-transparent-info text-info mb-3">
                        <i class="ti ti-repeat fs-36"></i>
                    </span>
                    <h4 class="mb-1">Confirm Revert</h4>
                    <p class="mb-3">
                        Are you sure you want to revert this payslip for <strong><span id="payslipRevertPlaceholder"></span></strong>? This can’t be undone.
                    </p>
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</a>
                        <a href="javascript:void(0);" class="btn btn-info" id="revertPayslipConfirmBtn">Yes, Revert</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- ApexCharts --}}
    <script>
        $(document).ready(function() {
            let thirteenthMonthData = @json($thirteenthMonthPayslips);

            // Check if ApexCharts is loaded
            if (typeof ApexCharts === 'undefined') {
                console.error('ApexCharts is not loaded!');
                return;
            }

            // Function to calculate analytics
            function calculateAnalytics(year = '') {
                let filteredData = thirteenthMonthData;

                // Filter by year if selected
                if (year) {
                    filteredData = thirteenthMonthData.filter(item => item.year == year);
                }

                // Calculate totals
                let total13thMonth = filteredData.reduce((sum, item) => sum + parseFloat(item
                    .total_thirteenth_month || 0), 0);
                let totalBasicPay = filteredData.reduce((sum, item) => sum + parseFloat(item.total_basic_pay || 0),
                    0);
                let employeesPaid = filteredData.length;
                let averagePerEmployee = employeesPaid > 0 ? total13thMonth / employeesPaid : 0;

                // Update display
                $('#total-13th-month-amount').text('₱' + total13thMonth.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#total-basic-pay').text('₱' + totalBasicPay.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#total-employees-paid').text(employeesPaid);
                $('#average-per-employee').text('₱' + averagePerEmployee.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                return filteredData;
            }

            // Function to render 13th Month Pay by Year chart
            function renderYearChart(data) {

                const yearData = {};
                data.forEach(item => {
                    const year = item.year;
                    if (!yearData[year]) {
                        yearData[year] = 0;
                    }
                    yearData[year] += parseFloat(item.total_thirteenth_month || 0);
                });

                const years = Object.keys(yearData).sort();
                const amounts = years.map(year => yearData[year]);

                const options = {
                    series: [{
                        name: '13th Month Pay',
                        data: amounts
                    }],
                    chart: {
                        type: 'bar',
                        height: 300,
                        toolbar: {
                            show: false
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            borderRadius: 5,
                            dataLabels: {
                                position: 'top'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return '₱' + val.toLocaleString('en-US', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            });
                        },
                        offsetY: -20,
                        style: {
                            fontSize: '12px',
                            colors: ['#304758']
                        }
                    },
                    colors: ['#28a745'],
                    xaxis: {
                        categories: years,
                        title: {
                            text: 'Year'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Amount (₱)'
                        },
                        labels: {
                            formatter: function(val) {
                                return '₱' + val.toLocaleString('en-US', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return '₱' + val.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                };

                const chartElement = document.querySelector("#thirteenth-month-year-chart");
                if (chartElement) {
                    $('#thirteenth-month-year-chart').empty();
                    const chart = new ApexCharts(chartElement, options);
                    chart.render();
                    console.log('Year chart rendered successfully');
                } else {
                    console.error('Year chart element not found');
                }
            }

            // Function to render monthly breakdown trend chart
            function renderStatusChart(data) {
                // Aggregate monthly data from all records
                const monthlyData = {};

                data.forEach(item => {
                    if (item.monthly_breakdown && Array.isArray(item.monthly_breakdown)) {
                        item.monthly_breakdown.forEach(month => {
                            const monthKey = month.month_name; // e.g., "January 2024"
                            if (!monthlyData[monthKey]) {
                                monthlyData[monthKey] = 0;
                            }
                            monthlyData[monthKey] += parseFloat(month.thirteenth_month_contribution || 0);
                        });
                    }
                });

                // Sort months chronologically
                const sortedMonths = Object.keys(monthlyData).sort((a, b) => {
                    const [monthA, yearA] = a.split(' ');
                    const [monthB, yearB] = b.split(' ');
                    const dateA = new Date(yearA, new Date(Date.parse(monthA + " 1, 2000")).getMonth());
                    const dateB = new Date(yearB, new Date(Date.parse(monthB + " 1, 2000")).getMonth());
                    return dateA - dateB;
                });

                const amounts = sortedMonths.map(month => monthlyData[month]);

                const options = {
                    series: [{
                        name: 'Monthly Contribution',
                        data: amounts
                    }],
                    chart: {
                        type: 'area',
                        height: 300,
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    colors: ['#4472C4'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.7,
                            opacityTo: 0.3,
                            stops: [0, 90, 100]
                        }
                    },
                    xaxis: {
                        categories: sortedMonths,
                        labels: {
                            rotate: -45,
                            rotateAlways: true,
                            style: {
                                fontSize: '10px'
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Amount (₱)'
                        },
                        labels: {
                            formatter: function(val) {
                                return '₱' + val.toLocaleString('en-US', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return '₱' + val.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e7e7e7',
                        row: {
                            colors: ['#f3f3f3', 'transparent'],
                            opacity: 0.5
                        }
                    }
                };

                const chartElement = document.querySelector("#thirteenth-month-status-chart");
                if (chartElement) {
                    $('#thirteenth-month-status-chart').empty();
                    const chart = new ApexCharts(chartElement, options);
                    chart.render();
                    console.log('Monthly breakdown chart rendered successfully');
                } else {
                    console.error('Monthly breakdown chart element not found');
                }
            }

            // Initialize analytics
            function initializeAnalytics(year = '') {
                const filteredData = calculateAnalytics(year);

                if (filteredData.length === 0) {
                    console.warn('No data available for charts');
                    return;
                }

                renderYearChart(filteredData);
                renderStatusChart(filteredData);
            }

            // Year filter change event
            $('#analytics-year').on('change', function() {
                const selectedYear = $(this).val();
                initializeAnalytics(selectedYear);
            });

            // Initial load
            initializeAnalytics();
        });
    </script>

    {{-- Delete 13th Month Payslip --}}
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

                // Close modal
                let modal = bootstrap.Modal.getInstance(document.getElementById('delete_payslip'));
                if (modal) modal.hide();

                fetch(`/api/thirteenth-month-payslip/delete/${payslipDeleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${authToken}`,
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            toastr.success(data.message || '13th Month Payslip deleted successfully.');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            toastr.error(data.message || 'Failed to delete 13th month payslip.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toastr.error("Failed to delete payslip. Please try again.");
                    });
            });
        });
    </script>

    {{-- Revert 13th Month Payslip --}}
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

                // Close modal
                let modal = bootstrap.Modal.getInstance(document.getElementById('revert_payslip'));
                if (modal) modal.hide();

                fetch(`/api/thirteenth-month-payslip/revert/${payslipRevertId}`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${authToken}`,
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            toastr.success(data.message || '13th Month Payslip reverted successfully.');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            toastr.error(data.message || 'Failed to revert 13th month payslip.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toastr.error("Failed to revert payslip. Please try again.");
                    });
            });
        });
    </script>

    {{-- Bulk Actions --}}
    <script>
        $(document).ready(function() {
            // Select/Deselect All
            $('#select-all').on('change', function() {
                $('.select-item').prop('checked', $(this).prop('checked'));
            });

            // Update select-all checkbox based on individual selections
            $(document).on('change', '.select-item', function() {
                const allChecked = $('.select-item:checked').length === $('.select-item').length;
                $('#select-all').prop('checked', allChecked);
            });

            // Bulk Delete
            $(document).on('click', '#bulkDeletePayroll', function() {
                let ids = $('.select-item:checked').map(function() {
                    return $(this).data('id');
                }).get();

                if (ids.length === 0) {
                    toastr.warning('Please select at least one 13th month payslip to delete.');
                    return;
                }

                if (!confirm('Are you sure you want to delete the selected 13th month payslip(s)?')) {
                    return;
                }

                $.ajax({
                    url: '/api/thirteenth-month-payslip/bulk-delete',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    data: {
                        payslip_ids: ids
                    },
                    success: function(res) {
                        toastr.success(res.message || 'Selected 13th month payslip(s) deleted successfully.');
                        setTimeout(() => window.location.reload(), 1000);
                    },
                    error: function(err) {
                        toastr.error(err.responseJSON?.message ||
                            'An error occurred while deleting 13th month payslip(s).');
                    }
                });
            });

            // Bulk Revert
            $(document).on('click', '#bulkRevertPayslip', function() {
                let ids = $('.select-item:checked').map(function() {
                    return $(this).data('id');
                }).get();

                if (ids.length === 0) {
                    toastr.warning('Please select at least one 13th month payslip to revert.');
                    return;
                }

                if (!confirm('Are you sure you want to revert the selected 13th month payslip(s) to pending status?')) {
                    return;
                }

                $.ajax({
                    url: '/api/thirteenth-month-payslip/bulk-revert',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    data: {
                        payslip_ids: ids
                    },
                    success: function(res) {
                        toastr.success(res.message ||
                            'Selected 13th month payslip(s) reverted to pending status successfully.');
                        setTimeout(() => window.location.reload(), 1000);
                    },
                    error: function(err) {
                        toastr.error(err.responseJSON?.message ||
                            'An error occurred while reverting 13th month payslip(s).');
                    }
                });
            });
        });
    </script>

@endpush
