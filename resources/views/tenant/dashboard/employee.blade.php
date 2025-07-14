<?php $page = 'employee-dashboard'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee Dashboard</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Dashboard
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee Dashboard</li>
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
                    <div class="input-icon w-120 position-relative mb-2">
                        <span class="input-icon-addon">
                            <i class="ti ti-calendar text-gray-9"></i>
                        </span>
                        <input type="text" class="form-control datetimepicker"
                            value="{{ \Carbon\Carbon::now()->format('d/m/Y') }}">
                    </div>
                    <div class="ms-2 head-icons">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Notification for Leave/Overtime/OB --}}
            @foreach ($allNotifications as $notif)
                @php
                    $notifKey = $notif['type'] . '_' . $notif['main_date'] . '_' . $notif['status'];
                @endphp
                <div class="alert bg-secondary-transparent alert-dismissible fade show mb-4" id="notif-{{ $notifKey }}"
                    data-notif-key="{{ $notifKey }}" style="display: none;">
                    Your {{ $notif['label'] }} on
                    <strong>{{ \Carbon\Carbon::parse($notif['main_date'])->format('jS F Y') }}</strong>
                    has been
                    <strong class="{{ $notif['status'] == 'approved' ? 'text-success' : 'text-danger' }}">
                        {{ ucfirst($notif['status']) }}
                    </strong>!
                    <button type="button" class="btn-close fs-14" data-bs-dismiss="alert" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            @endforeach

            <div class="row">
                <div class="col-xl-4 d-flex">
                    <div class="card position-relative flex-fill">
                        <div class="card-header bg-dark">
                            <div class="d-flex align-items-center">
                                @if ($authUser->personalInformation && $authUser->personalInformation->profile_picture)
                                    <span class="avatar avatar-lg avatar-rounded border border-white flex-shrink-0 me-2">
                                        <img src="{{ asset('storage/' . $authUser->personalInformation->profile_picture) }}" class="img-fluid rounded-circle" alt="img">
                                    </span>
                                @else
                                    <span class="avatar avatar-lg avatar-rounded border border-white flex-shrink-0 me-2">
                                        <img src="{{ URL::asset('build/img/users/user-01.jpg') }}" alt="Img">
                                    </span>
                                @endif
                                <div>
                                    @if (Auth::check() && optional($authUser->personalInformation)->full_name)
                                        <h5 class="text-white mb-1">{{ $authUser->personalInformation->full_name }}</h5>
                                    @else
                                        <h5 class="text-white mb-1">No Name Available</h5>
                                    @endif
                                    <div class="d-flex align-items-center">
                                        <p class="text-white fs-12 mb-0">
                                            {{ $authUser->employmentDetail->designation->designation_name ?? 'No Designation' }}
                                        </p>
                                        <span class="mx-1"><i class="ti ti-point-filled text-primary"></i></span>
                                        <p class="fs-12">
                                            {{ $authUser->employmentDetail->department->department_name ?? 'No Department' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="d-block mb-1 fs-13">Employee ID</span>
                                <p class="text-gray-9">
                                    {{ $authUser->employmentDetail->employee_id ?? 'N/A' }}</p>
                            </div>
                            <div class="mb-2">
                                <span class="d-block mb-1 fs-13">Phone Number</span>
                                <p class="text-gray-9">
                                    {{ $authUser->personalInformation->phone_number ?? 'No Phone Number' }}</p>
                            </div>
                            <div class="mb-2">
                                <span class="d-block mb-1 fs-13">Email Address</span>
                                <p class="text-gray-9">{{ $authUser->email ?? 'No Email Address' }}</p>
                            </div>
                            <div class="mb-2">
                                <span class="d-block mb-1 fs-13">Reporting To</span>
                                <p class="text-gray-9">
                                    {{ $authUser->employmentDetail->department->head->personalInformation->full_name ?? 'No Reporting To' }}
                                </p>
                            </div>
                            <div>
                                <span class="d-block mb-1 fs-13">Joined on</span>
                                <p class="text-gray-9">
                                    {{ optional(optional($authUser)->employmentDetail)?->date_hired ? \Carbon\Carbon::parse(optional($authUser->employmentDetail)->date_hired)->format('F d, Y') : 'No Date' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Attendance Data --}}

                @php
                    $months = [
                        1 => 'January',
                        2 => 'February',
                        3 => 'March',
                        4 => 'April',
                        5 => 'May',
                        6 => 'June',
                        7 => 'July',
                        8 => 'August',
                        9 => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                    ];
                    $currentMonth = now()->month;
                    $currentYear = now()->year;

                    $yearList = [];
                    for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                        $yearList[] = $y;
                    }
                @endphp

                <div class="col-xl-5 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                                <h5 class="mb-0 me-3">Attendance Details</h5>
                                <div class="d-flex gap-2 align-items-center">
                                    <select id="attendance_month" class="form-select form-select-sm" style="width: 114px;">
                                        @foreach ($months as $num => $label)
                                            <option value="{{ $num }}"
                                                {{ $currentMonth == $num ? 'selected' : '' }}>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <select id="attendance_year" class="form-select form-select-sm" style="width: 100px;">
                                        @for ($y = $currentYear; $y >= $currentYear - 5; $y--)
                                            <option value="{{ $y }}"
                                                {{ $currentYear == $y ? 'selected' : '' }}>
                                                {{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="mb-3">
                                            <p class="d-flex align-items-center"><i
                                                    class="ti ti-circle-filled fs-8 text-dark me-1"></i>
                                                <span class="text-gray-9 fw-semibold me-1" id="present-count">0</span>
                                                On Time
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <p class="d-flex align-items-center"><i
                                                    class="ti ti-circle-filled fs-8 text-success me-1"></i>
                                                <span class="text-gray-9 fw-semibold me-1" id="late-count">0</span>
                                                Late
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <p class="d-flex align-items-center"><i
                                                    class="ti ti-circle-filled fs-8 text-primary me-1"></i>
                                                <span class="text-gray-9 fw-semibold me-1" id="undertime-count">0</span>
                                                Undertime
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <p class="d-flex align-items-center"><i
                                                    class="ti ti-circle-filled fs-8 text-danger me-1"></i>
                                                <span class="text-gray-9 fw-semibold me-1" id="absent-count">0</span>
                                                Absent
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-md-end">
                                        <div id="attendance_chart"
                                            style="width:90%; min-width:90px; margin-top: 10px; margin-bottom: 10px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- /Attendance Data --}}

                {{-- Leave Details --}}
                <div class="col-xl-3 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                                <h5>Leave Details</h5>
                                <div class="dropdown">
                                    <a href="javascript:void(0);" id="leave-year-dropdown"
                                        class="btn btn-white border btn-sm d-inline-flex align-items-center"
                                        data-bs-toggle="dropdown" data-selected-year="{{ $currentYear }}">
                                        <i class="ti ti-calendar me-1"></i>
                                        <span id="leave-year-label">{{ $currentYear }}</span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end p-3" id="leave-year-menu">
                                        @foreach ($yearList as $year)
                                            <li>
                                                <a href="javascript:void(0);"
                                                    class="dropdown-item rounded-1 leave-year-option {{ $year == $currentYear ? 'active' : '' }}"
                                                    data-year="{{ $year }}">
                                                    {{ $year }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Total Leaves</span>
                                        <h4 id="total-leaves">0</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Pending</span>
                                        <h4 id="total-pending">0</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Approved</span>
                                        <h4 id="total-approved">0</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Rejected</span>
                                        <h4 id="total-rejected">0</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Worked Days</span>
                                        <h4 id="worked-days">0</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-4">
                                        <span class="d-block mb-1">Absents</span>
                                        <h4 id="absents">0</h4>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div>
                                        <a href="{{ route('leave-employees') }}" class="btn btn-dark w-100">Apply New
                                            Leave</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BDAY --}}
                <div class="col-xl-4 d-flex">
                    <div class="flex-fill">
                        <div class="card card-bg-5 bg-dark mb-3">
                            <div class="card-body">
                                <div class="text-center">
                                    <h5 class="text-white mb-4">Team Birthday</h5>

                                    @foreach ($branchBirthdayEmployees as $employee)
                                        <span class="avatar avatar-xl avatar-rounded mb-2">
                                            <img src="{{ $employee['profile_picture'] }}"
                                                alt="{{ $employee['full_name'] }}">
                                        </span>
                                        <div class="mb-3">
                                            <h6 class="text-white fw-medium mb-1">{{ $employee['full_name'] }}</h6>
                                            <p>{{ $employee['designation'] }}</p>
                                        </div>
                                    @endforeach

                                    @if ($branchBirthdayEmployees->isNotEmpty())
                                        <a href="#" class="btn btn-sm btn-primary">Send Wishes</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card bg-secondary mb-3">
                            <div class="card-body d-flex align-items-center justify-content-between p-3">
                                <div>
                                    <h5 class="text-white mb-1">Leave Policy</h5>
                                    <p class="text-white">Last Updated : Today</p>
                                </div>
                                <a href="#" class="btn btn-white btn-sm px-3">View All</a>
                            </div>
                        </div>
                        <div class="card bg-warning">
                            <div class="card-body d-flex align-items-center justify-content-between p-3">
                                <div>
                                    <h5 class="mb-1">Next Holiday</h5>
                                    @if ($upcomingHoliday)
                                        <p class="text-gray-9">
                                            {{ $upcomingHoliday->name }},
                                            @if ($upcomingHoliday->date)
                                                {{ \Carbon\Carbon::parse($upcomingHoliday->date)->format('d M Y') }}
                                            @elseif ($upcomingHoliday->month_day)
                                                {{ \Carbon\Carbon::createFromFormat('m-d', $upcomingHoliday->month_day)->setYear(now()->year)->format('d M Y') }}
                                            @endif
                                        </p>
                                    @else
                                        <p class="text-gray-9">No upcoming holidays.</p>
                                    @endif
                                </div>
                                <a href="{{ url('holidays') }}" class="btn btn-white btn-sm px-3">View All</a>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- /BDAY --}}
            </div>
        </div>

        {{-- Footer Company --}}
        @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.alert[data-notif-key]').forEach(function(alert) {
                var key = alert.getAttribute('data-notif-key');
                if (!localStorage.getItem('notif_dismissed_' + key)) {
                    alert.style.display = '';
                }
                alert.querySelector('.btn-close').addEventListener('click', function() {
                    localStorage.setItem('notif_dismissed_' + key, '1');
                });
            });
        });
    </script>

    <script>
        let attendanceChart = null;

        // Helper to prepare series and decide if all zero
        function prepareAttendanceSeries(data) {
            let series = [
                data.circleData.present,
                data.circleData.late,
                data.circleData.undertime,
                data.circleData.absent
            ];
            let isAllZero = series.every(x => x === 0);
            // If all are zero, put a 1 in "On Time" just for display, set a flag
            return {
                series: isAllZero ? [0, 0, 0, 0] : series,
                isAllZero: isAllZero
            };
        }

        function renderAttendanceChart(series, isAllZero) {
            // Destroy chart if it exists to allow re-initialization (needed for label change)
            if (attendanceChart) {
                attendanceChart.destroy();
            }
            attendanceChart = new ApexCharts(document.querySelector("#attendance_chart"), {
                series: series,
                chart: {
                    type: 'donut',
                    height: 240
                },
                labels: ['On Time', 'Late', 'Undertime', 'Absent'],
                colors: ['#22C55E', '#EF4444', '#2563EB', '#222'],
                legend: {
                    show: false
                },
                dataLabels: {
                    enabled: false
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: isAllZero ? 'No Data' : 'Total',
                                    fontSize: '16px'
                                }
                            }
                        }
                    }
                }
            });
            attendanceChart.render();
        }

        function fetchAttendanceAnalytics(month, year) {
            fetch(`/employee-dashboard/attendance-analytics?month=${month}&year=${year}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('present-count').textContent = data.circleData.present;
                    document.getElementById('late-count').textContent = data.circleData.late;
                    document.getElementById('undertime-count').textContent = data.circleData.undertime;
                    document.getElementById('absent-count').textContent = data.circleData.absent;

                    // Prepare the series and flag
                    const {
                        series,
                        isAllZero
                    } = prepareAttendanceSeries(data);

                    // Render or re-render the donut
                    renderAttendanceChart(series, isAllZero);
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            let monthSel = document.getElementById('attendance_month');
            let yearSel = document.getElementById('attendance_year');
            fetchAttendanceAnalytics(monthSel.value, yearSel.value);

            monthSel.addEventListener('change', function() {
                fetchAttendanceAnalytics(monthSel.value, yearSel.value);
            });
            yearSel.addEventListener('change', function() {
                fetchAttendanceAnalytics(monthSel.value, yearSel.value);
            });
        });
    </script>

    {{-- Leave Analytics --}}
    <script>
        function fetchLeaveAnalytics(year) {
            fetch(`/employee-dashboard/leave-analytics?year=${year}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('total-leaves').textContent = data.totalLeaves;
                    document.getElementById('total-pending').textContent = data.totalPending;
                    document.getElementById('total-approved').textContent = data.totalApproved;
                    document.getElementById('total-rejected').textContent = data.totalRejected;
                    document.getElementById('worked-days').textContent = data.workedDays;
                    document.getElementById('absents').textContent = data.absents;
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            let dropdownBtn = document.getElementById('leave-year-dropdown');
            let yearMenu = document.getElementById('leave-year-menu');
            let yearLabel = document.getElementById('leave-year-label');

            // Default fetch
            fetchLeaveAnalytics(dropdownBtn.dataset.selectedYear);

            // Event: Year click in dropdown
            yearMenu.querySelectorAll('.leave-year-option').forEach(function(option) {
                option.addEventListener('click', function() {
                    // Update dropdown label
                    yearLabel.textContent = this.dataset.year;
                    dropdownBtn.dataset.selectedYear = this.dataset.year;

                    // Set active state
                    yearMenu.querySelectorAll('.leave-year-option').forEach(x => x.classList.remove(
                        'active'));
                    this.classList.add('active');

                    // Fetch analytics
                    fetchLeaveAnalytics(this.dataset.year);
                });
            });
        });
    </script>
@endpush
