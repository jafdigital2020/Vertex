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
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #12515D 0%, #2A9D8F 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">My 13th Month Pay</p>
                                <h2 id="total-13th-month-amount" class="mb-1 fw-bold text-white mt-3" style="font-size:22px;">
                                    ₱0.00
                                </h2>
                                <small class="text-white-75">All Periods</small>
                            </div>

                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-calendar-dollar" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div
                                        style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-calendar-dollar" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #b53654 0%, #f2848c 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">My Total Basic Pay</p>
                                <h2 id="total-basic-pay" class="mb-1 fw-bold text-white mt-3" style="font-size:22px;">
                                    ₱0.00
                                </h2>
                                <small class="text-white-75">All Periods</small>
                            </div>

                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-cash" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div
                                        style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-cash" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #ed7464 0%, #f9c6b8 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">Total Records</p>
                                <h2 id="total-employees-paid" class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                    {{ str_pad($totalRecords ?? 0, 2, '0', STR_PAD_LEFT) }}
                                </h2>
                                <small class="text-white-75">Matches Filter</small>
                            </div>

                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-files" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div
                                        style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-files" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card text-white position-relative overflow-hidden"
                        style="border-radius:10px; background: linear-gradient(135deg, #ffb400 0%, #e68a00 100%); min-height:120px;">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div class="me-3" style="z-index:3;">
                                <p class="fs-12 fw-medium mb-1 text-white-75">Average Per Record</p>
                                <h2 id="average-per-employee" class="mb-1 fw-bold text-white mt-3" style="font-size:22px;">
                                    ₱0.00
                                </h2>
                                <small class="text-white-75">Calculated</small>
                            </div>

                            <!-- Right icon circle group -->
                            <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                <div
                                    style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-calculator" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                </div>
                                <div
                                    style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                    <div
                                        style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                        <i class="ti ti-calculator" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                    </div>
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
                                    <h5 class="mb-0">My 13th Month Pay by Year</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <div id="thirteenth-month-year-chart"></div>
                        </div>
                    </div>
                </div>

                <!-- 13th Month Pay Status Distribution -->
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
                <a href="{{ route('user-payslip') }}" class="btn btn-white border me-2">Payslip</a>
                <a href="{{ route('thirteenthMonthPayslipIndex') }}" class="btn btn-white active border me-2">Thirteenth
                    Month Payslips</a>
            </div>

            <!-- Generated Payslip list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Thirteenth Month Payslips</h5>

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
    {{-- ApexCharts --}}
    <script>
        $(document).ready(function() {
            let thirteenthMonthData = @json($thirteenthMonthPayslips);

            // Check if ApexCharts is loaded
            if (typeof ApexCharts === 'undefined') {
                console.error('ApexCharts is not loaded!');
                return;
            }

            // Function to calculate analytics (for authenticated user only)
            function calculateAnalytics(year = '') {
                let filteredData = thirteenthMonthData;

                // Filter by year if selected
                if (year) {
                    filteredData = thirteenthMonthData.filter(item => item.year == year);
                }

                // ✅ Calculate from monthly_breakdown
                let totalBasicPay = 0;
                let totalLeavePay = 0;
                let totalLateDeduction = 0;
                let totalUndertimeDeduction = 0;
                let totalAbsentDeduction = 0;

                // Loop through all payslips and their monthly breakdowns
                filteredData.forEach(payslip => {
                    if (payslip.monthly_breakdown && Array.isArray(payslip.monthly_breakdown)) {
                        payslip.monthly_breakdown.forEach(month => {
                            totalBasicPay += parseFloat(month.basic_pay || 0);
                            totalLeavePay += parseFloat(month.leave_pay || 0);
                            totalLateDeduction += parseFloat(month.late_deduction || 0);
                            totalUndertimeDeduction += parseFloat(month.undertime_deduction || 0);
                            totalAbsentDeduction += parseFloat(month.absent_deduction || 0);
                        });
                    }
                });

                // ✅ Calculate Net Basic Pay: Basic Pay + Leave Pay - All Deductions
                let netBasicPay = totalBasicPay + totalLeavePay - totalLateDeduction -
                                  totalUndertimeDeduction - totalAbsentDeduction;

                // Calculate totals for this user only
                let total13thMonth = filteredData.reduce((sum, item) => sum + parseFloat(item
                    .total_thirteenth_month || 0), 0);
                let totalRecords = filteredData.length;
                let averagePerRecord = totalRecords > 0 ? total13thMonth / totalRecords : 0;

                // Update display
                $('#total-13th-month-amount').text('₱' + total13thMonth.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#total-basic-pay').text('₱' + netBasicPay.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#total-employees-paid').text(totalRecords);
                $('#average-per-employee').text('₱' + averagePerRecord.toLocaleString('en-US', {
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
@endpush
