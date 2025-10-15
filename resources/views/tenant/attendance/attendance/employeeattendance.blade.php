<?php $page = 'attendance-employee'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee Attendance</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee Attendance</li>
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
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                                class="ti ti-file-type-xls me-1"></i>Download Template </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#request_attendance"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Request
                            Attendance</a>
                    </div>
                    <div class="ms-2 head-icons">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            @php
                $hour = now()->hour;
                if ($hour < 12) {
                    $greeting = 'Good Morning';
                } elseif ($hour < 18) {
                    $greeting = 'Good Afternoon';
                } else {
                    $greeting = 'Good Evening';
                }

                $user = Auth::guard('web')->user() ?? Auth::guard('global')->user();

                $name = $user?->personalInformation->first_name ?? ($user?->username ?? 'Guest');
            @endphp

            <div class="row">

                <div class="col-xl-3 col-lg-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body">
                            <div class="mb-3 text-center">
                                <h6 class="fw-medium text-gray-5 mb-2">{{ $greeting }}, {{ $name }}</h6>
                                <h4 id="live-clock" class="mb-0 fw-semibold"></h4>
                            </div>
                            <div class="attendance-circle-progress mx-auto mb-3" data-value='65'>
                                <span class="progress-left">
                                    <span class="progress-bar border-success"></span>
                                </span>
                                <span class="progress-right">
                                    <span class="progress-bar border-success"></span>
                                </span>
                                <div class="avatar avatar-xxl avatar-rounded">
                                    <img src="{{ asset(
                                        Auth::check() && Auth::user()->personalInformation->profile_picture
                                            ? 'storage/' . Auth::user()->personalInformation->profile_picture
                                            : 'build/img/profiles/avatar-27.jpg',
                                    ) }}"
                                        alt="Img">
                                </div>
                            </div>

                            <div class="text-center">


                                <div class="mb-3">
                                    @if ($latest && $latest->time_only && !$latest->time_out_only)
                                        <h6 class="fw-medium d-flex align-items-center justify-content-center mb-3">
                                            <i class="ti ti-fingerprint text-primary me-1"></i>
                                            <span>Clocked In at {{ $latest->time_only ?? '00:00' }}</span>
                                        </h6>
                                    @else
                                        <h6 class="fw-medium d-flex align-items-center justify-content-center mb-3">
                                            <i class="ti ti-fingerprint text-primary me-1"></i>
                                            <span>Not Clocked In</span>
                                        </h6>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-center mb-3">

                                    @php
                                        $showBreakManagement = false;
                                        $breakMinutes = 0;

                                        // ✅ UPDATED: Check both nextAssignment and current active assignment
                                        $assignmentToCheck =
                                            $nextAssignment ??
                                            (isset($currentActiveAssignment) ? $currentActiveAssignment : null);

                                        if (
                                            $assignmentToCheck &&
                                            $assignmentToCheck->shift &&
                                            $assignmentToCheck->shift->break_minutes > 0
                                        ) {
                                            $showBreakManagement = true;
                                            $breakMinutes = $assignmentToCheck->shift->break_minutes;
                                        }
                                    @endphp

                                    @if ($showBreakManagement)
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                                id="breakDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ti ti-clock-pause me-1"></i>Select Break
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="breakDropdown">
                                                <li>
                                                    <a class="dropdown-item" href="#" id="lunchButton"
                                                        data-break-type="lunch">
                                                        <i class="ti ti-salad me-2"></i>Lunch Break
                                                    </a>
                                                </li>
                                                {{-- <li>
                                                    <a class="dropdown-item" href="#" id="coffeeButton"
                                                        data-break-type="coffee">
                                                        <i class="ti ti-coffee me-2"></i>Coffee Break
                                                    </a>
                                                </li> --}}
                                            </ul>
                                        </div>
                                    @else
                                        <div class="badge badge-md badge-primary mb-1">
                                            Production: {{ $latest->total_work_minutes_formatted ?? '00' }}
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between mt-10">
                                    <a href="#" class="btn btn-primary w-100 me-2" id="clockInButton"
                                        data-has-shift="{{ $hasShift ? '1' : '0' }}">Clock-In</a>
                                    <a href="#" class="btn btn-outline-primary w-100" id="clockOutButton"
                                        data-shift-id="{{ $latest ? $latest->shift_id : '' }}">Clock-Out</a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Break Management Modal -->
                <div class="modal fade" id="breakModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header border-0 pb-2">
                                <h6 class="modal-title fw-semibold" id="breakModalTitle"></h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body pt-2 px-4 pb-4">
                                <div class="text-center mb-4">
                                    <div id="breakTypeIcon" class="mb-3">
                                        <div
                                            class="avatar avatar-md bg-light border rounded-circle mx-auto d-flex align-items-center justify-content-center">
                                            <i class="ti ti-salad text-muted fs-18"></i>
                                        </div>
                                    </div>
                                    <h6 id="breakTypeTitle" class="mb-2 text-dark fw-medium">Lunch Break</h6>
                                    <p class="text-muted small mb-0">Manage your break time</p>
                                </div>

                                <div class="d-flex flex-column gap-2 mb-3">
                                    <button type="button" class="btn btn-success btn-sm py-2" id="breakInBtn">
                                        <i class="ti ti-play fs-14 me-1"></i>Start Break
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm py-2" id="breakOutBtn">
                                        <i class="ti ti-stop fs-14 me-1"></i>End Break
                                    </button>
                                </div>

                                <div class="p-2 bg-light rounded-1" id="breakInfo">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="ti ti-clock fs-12 text-muted me-1"></i>
                                        <small class="text-muted">
                                            Max: <span id="maxBreakTime"
                                                class="fw-medium">{{ $breakMinutes ?? 0 }}</span>min
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Late Reason Modal -->
                <div class="modal" tabindex="-1" id="lateReasonModal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Reason for Being Late</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <textarea id="lateReasonInput" class="form-control" rows="3" placeholder="Please describe why you’re late…"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-light" data-bs-dismiss="modal"
                                    id="lateReasonCancel">Cancel</button>
                                <button class="btn btn-primary" id="lateReasonSubmit">Submit Reason</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Camera Popup Modal -->
                <div class="modal" tabindex="-1" id="cameraModal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Capture Your Photo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <video id="cameraStream" width="100%" autoplay></video>
                                <div id="image-preview" style="display:none;">
                                    <img id="capturedImage" class="img-fluid" />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" id="retakeButton" style="display:none;">Retake</button>
                                <button class="btn btn-primary" id="captureButton">Capture</button>
                                <!-- new confirm button -->
                                <button type="button" class="btn btn-success" id="confirmClockIn"
                                    style="display:none;">
                                    Confirm Clock-In
                                </button>
                                <button type="button" class="btn btn-success" id="confirmClockOut"
                                    style="display:none;">
                                    Confirm Clock-Out
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Modal -->
                <div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Clock-In Location</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div id="mapModalContainer" style="width:100%;height:400px;"></div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9 col-lg-8 d-flex">
                    <div class="row flex-fill">

                        {{-- Total Hours Today --}}
                        <div class="col-xl-4 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-2 pb-2">
                                        <span class="avatar avatar-sm bg-primary mb-2"><i
                                                class="ti ti-clock-stop"></i></span>
                                        <h2 class="mb-2">{{ $latest->total_work_minutes_formatted ?? '00' }}</h2>
                                        <p class="fw-medium text-truncate">Total Hours Today</p>
                                    </div>
                                    {{-- <div>
                                        <p class="d-flex align-items-center fs-13">
                                            <span class="avatar avatar-xs rounded-circle bg-success flex-shrink-0 me-2">
                                                <i class="ti ti-arrow-up fs-12"></i>
                                            </span>
                                            <span>5% This Week</span>
                                        </p>
                                    </div> --}}
                                </div>
                            </div>
                        </div>

                        {{-- Total Hours This Cut-off --}}
                        <div class="col-xl-4 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-2 pb-2">
                                        <span class="avatar avatar-sm bg-dark mb-2"><i class="ti ti-clock-up"></i></span>
                                        <h2 class="mb-2">{{ $totalWeeklyHoursFormatted }}</h2>
                                        <p class="fw-medium text-truncate">Total Hours Week</p>
                                    </div>
                                    {{-- <div>
                                        <p class="d-flex align-items-center fs-13">
                                            <span class="avatar avatar-xs rounded-circle bg-success flex-shrink-0 me-2">
                                                <i class="ti ti-arrow-up fs-12"></i>
                                            </span>
                                            <span>7% Last Week</span>
                                        </p>
                                    </div> --}}
                                </div>
                            </div>
                        </div>

                        {{-- Total Late Today --}}
                        <div class="col-xl-4 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-2 pb-2">
                                        <span class="avatar avatar-sm bg-info mb-2"><i
                                                class="ti ti-calendar-up"></i></span>
                                        <h2 class="mb-2">{{ $totalMonthlyHoursFormatted }}</h2>
                                        <p class="fw-medium text-truncate">Total Hours Month</p>
                                    </div>
                                    {{-- <div>
                                        <p class="d-flex align-items-center fs-13 text-truncate">
                                            <span class="avatar avatar-xs rounded-circle bg-danger flex-shrink-0 me-2">
                                                <i class="ti ti-arrow-down fs-12"></i>
                                            </span>
                                            <span>8% Last Month</span>
                                        </p>
                                    </div> --}}
                                </div>
                            </div>
                        </div>

                        {{-- Total Late this cut-off --}}
                        <div class="col-xl-4 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-2 pb-2">
                                        <span class="avatar avatar-sm bg-pink mb-2"><i
                                                class="ti ti-calendar-star"></i></span>
                                        <h2 class="mb-2">{{ $totalMonthlyNightHoursFormatted }}</h2>
                                        <p class="fw-medium text-truncate">Night Diff This Month</p>
                                    </div>
                                    {{-- <div>
                                        <p class="d-flex align-items-center fs-13 text-truncate">
                                            <span class="avatar avatar-xs rounded-circle bg-danger flex-shrink-0 me-2">
                                                <i class="ti ti-arrow-down fs-12"></i>
                                            </span>
                                            <span>6% Last Month</span>
                                        </p>
                                    </div> --}}
                                </div>
                            </div>
                        </div>

                        {{-- Leaves taken this cut-off --}}
                        <div class="col-xl-4 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-2 pb-2">
                                        <span class="avatar avatar-sm bg-pink mb-2"><i
                                                class="ti ti-calendar-star"></i></span>
                                        <h2 class="mb-2">{{ $totalMonthlyLateHoursFormatted }}</h2>
                                        <p class="fw-medium text-truncate">Late this Month</p>
                                    </div>
                                    {{-- <div>
                                        <p class="d-flex align-items-center fs-13 text-truncate">
                                            <span class="avatar avatar-xs rounded-circle bg-danger flex-shrink-0 me-2">
                                                <i class="ti ti-arrow-down fs-12"></i>
                                            </span>
                                            <span>6% Last Month</span>
                                        </p>
                                    </div> --}}
                                </div>
                            </div>
                        </div>

                        {{-- Leaves remaing --}}
                        <div class="col-xl-4 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-2 pb-2">
                                        <span class="avatar avatar-sm bg-pink mb-2"><i
                                                class="ti ti-calendar-star"></i></span>
                                        <h2 class="mb-2">{{ $totalMonthlyUndertimeHoursFormatted }}</h2>
                                        <p class="fw-medium text-truncate">Undertime this Month</p>
                                    </div>
                                    {{-- <div>
                                        <p class="d-flex align-items-center fs-13 text-truncate">
                                            <span class="avatar avatar-xs rounded-circle bg-danger flex-shrink-0 me-2">
                                                <i class="ti ti-arrow-down fs-12"></i>
                                            </span>
                                            <span>6% Last Month</span>
                                        </p>
                                    </div> --}}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Page Links --}}
            <div class="payroll-btns mb-3">
                <a href="{{ route('attendance-employee') }}" class="btn btn-white active border me-2">Attendance</a>
                <a href="{{ route('attendance-request') }}" class="btn btn-white  border me-2">Attendance Requests</a>
            </div>


            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Employee Attendance</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter" onchange="filter()">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select"
                                onchange="filter()">
                                <option value="" selected>All Status</option>
                                <option value="present">Present</option>
                                <option value="late">Late</option>
                                <option value="absent">Absent</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable" id="empAttTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Shift</th>
                                    <th>Clock In</th>
                                    <th>Status</th>
                                    <th>Break Time</th>
                                    <th>Clock Out</th>
                                    <th>Late</th>
                                    <th>Photo</th>
                                    <th>Location</th>
                                    <th>Device</th>
                                    <th>Production Hours</th>
                                </tr>
                            </thead>
                            <tbody id="empAttTableBody">
                                @if (in_array('Read', $permission))
                                    @foreach ($attendances as $att)
                                        @php
                                            $status = $att->status;
                                            $statusText = ucfirst($status);
                                            if ($status === 'present') {
                                                $badgeClass = 'badge-success-transparent';
                                            } elseif ($status === 'late') {
                                                $badgeClass = 'badge-danger-transparent';
                                            } else {
                                                $badgeClass = 'badge-secondary-transparent';
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                {{ $att->attendance_date->format('Y-m-d') }}
                                            </td>
                                            <td>{{ $att->shift->name ?? '-' }}</td>
                                            <td>{{ $att->time_only }}</td>
                                            <td>
                                                <span class="badge {{ $badgeClass }} d-inline-flex align-items-center">
                                                    <i class="ti ti-point-filled me-1"></i>{{ $statusText }}
                                                </span>
                                                @if ($status === 'late')
                                                    <a href="#" class="ms-2" data-bs-toggle="tooltip"
                                                        data-bs-placement="right" title="{{ $att->late_status_box }}">
                                                        <i class="ti ti-info-circle text-info"></i>
                                                    </a>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if (empty($att->break_in_only) && empty($att->break_out_only))
                                                    <span class="text-muted">-</span>
                                                @else
                                                    <div class="d-flex flex-column align-items-center">
                                                        <span>{{ $att->break_in_only }} -
                                                            {{ $att->break_out_only }}</span>
                                                        @if (!empty($att->break_late) && $att->break_late > 0)
                                                            <span
                                                                class="badge badge-danger-transparent d-inline-flex align-items-center mt-1"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="Extended break time by {{ $att->break_late }} minutes">
                                                                <i class="ti ti-alert-circle me-1"></i>Over Break:
                                                                {{ $att->break_late }} min
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $att->time_out_only }}
                                            </td>
                                            <td>
                                                {{ $att->total_late_formatted }}
                                            </td>
                                            <td>
                                                @if ($att->time_in_photo_path || $att->time_out_photo_path)
                                                    <div class="btn-group" style="position: static; overflow: visible;">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                            data-bs-toggle="dropdown" data-bs-boundary="viewport"
                                                            data-bs-container="body">
                                                            View Photo
                                                        </button>
                                                        <ul class="dropdown-menu"
                                                            style="z-index: 9999; overflow: visible;">
                                                            @if ($att->time_in_photo_path)
                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href="{{ Storage::url($att->time_in_photo_path) }}"
                                                                        target="_blank">Clock-In Photo</a>
                                                                </li>
                                                            @endif
                                                            @if ($att->time_out_photo_path)
                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href="{{ Storage::url($att->time_out_photo_path) }}"
                                                                        target="_blank">Clock-Out Photo</a>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                @else
                                                    <span class="text-muted">No Photo</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (($att->time_in_latitude && $att->time_in_longitude) || ($att->time_out_latitude && $att->time_out_longitude))
                                                    <div class="btn-group" style="position: static; overflow: visible;">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                            data-bs-toggle="dropdown">
                                                            View Location
                                                        </button>
                                                        <ul class="dropdown-menu"
                                                            style="z-index: 9999; overflow: visible;">
                                                            @if ($att->time_in_latitude && $att->time_in_longitude)
                                                                <li>
                                                                    <a class="dropdown-item view-map-btn" href="#"
                                                                        data-lat="{{ $att->time_in_latitude }}"
                                                                        data-lng="{{ $att->time_in_longitude }}">Clock-In
                                                                        Location</a>
                                                                </li>
                                                            @endif
                                                            @if ($att->time_out_latitude && $att->time_out_longitude)
                                                                <li>
                                                                    <a class="dropdown-item view-map-btn" href="#"
                                                                        data-lat="{{ $att->time_out_latitude }}"
                                                                        data-lng="{{ $att->time_out_longitude }}">Clock-Out
                                                                        Location</a>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                @else
                                                    <span class="text-muted">No Location</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($att->clock_in_method || $att->clock_out_method)
                                                    <div class="btn-group" style="position: static; overflow: visible;">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                            data-bs-toggle="dropdown" data-bs-boundary="viewport"
                                                            data-bs-container="body">
                                                            View Device
                                                        </button>
                                                        <ul class="dropdown-menu"
                                                            style="z-index: 9999; overflow: visible;">
                                                            @if ($att->clock_in_method)
                                                                <li>
                                                                    <a class="dropdown-item" href="#">
                                                                        Clock-In Device ({{ $att->clock_in_method }})</a>
                                                                </li>
                                                            @endif
                                                            @if ($att->clock_out_method)
                                                                <li>
                                                                    <a class="dropdown-item" href="#">
                                                                        Clock-Out Device ({{ $att->clock_out_method }})</a>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                @else
                                                    <span class="text-muted">No Device</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-success d-inline-flex align-items-center">
                                                    <i class="ti ti-clock-hour-11 me-1"></i>
                                                    {{ $att->total_work_minutes_formatted }}
                                                </span>
                                                @if (!empty($att->total_night_diff_minutes_formatted) && $att->total_night_diff_minutes_formatted !== '00:00')
                                                    <br>
                                                    <span class="badge badge-info d-inline-flex align-items-center mt-1">
                                                        <i class="ti ti-moon me-1"></i>
                                                        Night: {{ $att->total_night_diff_minutes_formatted }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    {{-- Same scripts on Request --}}
    <script>
        function filter() {
            var dateRange = $('#dateRange_filter').val();
            var status = $('#status_filter').val();

            $.ajax({
                url: '{{ route('attendance-employee-filter') }}',
                type: 'GET',
                data: {
                    dateRange: dateRange,
                    status: status,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#empAttTable').DataTable().destroy();
                        $('#empAttTableBody').html(response.html);
                        $('#empAttTable').DataTable();

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

    {{-- Clock and Date UI --}}
    <script>
        function updateClock() {
            const now = new Date();

            const options = {
                hour: 'numeric',
                minute: 'numeric',
                hour12: true
            };

            const dateOptions = {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            };

            const time = now.toLocaleTimeString('en-US', options);
            const date = now.toLocaleDateString('en-US', dateOptions);

            document.getElementById('live-clock').textContent = `${time}, ${date}`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateClock();
            setInterval(updateClock, 1000); // Update every second
        });
    </script>

    {{-- Variables --}}
    <script>
        const requirePhoto = {{ $settings->require_photo_capture ? 'true' : 'false' }};
        const geotaggingEnabled = {{ $settings->geotagging_enabled ? 'true' : 'false' }};
        const geofencingEnabled = {{ $settings->geofencing_enabled ? 'true' : 'false' }};
        const lateReasonOn = {{ $settings->enable_late_status_box ? 'true' : 'false' }};
        const graceMinutes = {{ $gracePeriod }};
        const shiftStartTime = "{{ $nextAssignment?->shift?->start_time ?? '00:00:00' }}";
        const hasShift = {{ $hasShift ? 'true' : 'false' }};
        const isFlexible = {{ $isFlexible ? 'true' : 'false' }};
        const isRestDay = {{ $isRestDay ? 'true' : 'false' }};
        const subBlocked = {{ $subBlocked ? 'true' : 'false' }};
        const subBlockMessage = {!! json_encode($subBlockMessage) !!};
        const allowedMinutesBeforeClockIn = {{ $nextAssignment?->shift?->allowed_minutes_before_clock_in ?? 0 }};
        const shiftName = "{{ $nextAssignment?->shift?->name ?? 'Current Shift' }}";
        const maxBreakMinutes =
            {{ $nextAssignment && $nextAssignment->shift && $nextAssignment->shift->break_minutes
                ? $nextAssignment->shift->break_minutes
                : ($currentActiveAssignment && $currentActiveAssignment->shift && $currentActiveAssignment->shift->break_minutes
                    ? $currentActiveAssignment->shift->break_minutes
                    : 0) }};
    </script>

    // Clock In/Out Script
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // UI elements
            const clockInButton = document.getElementById('clockInButton');
            const clockOutButton = document.getElementById('clockOutButton');
            const shiftId = clockOutButton?.dataset.shiftId;
            const hasShift = clockInButton.dataset.hasShift === '1';

            // Camera modal elements
            const cameraModalEl = document.getElementById('cameraModal');
            const cameraModal = new bootstrap.Modal(cameraModalEl);
            const video = document.getElementById('cameraStream');
            const preview = document.getElementById('image-preview');
            const img = document.getElementById('capturedImage');
            const capBtn = document.getElementById('captureButton');
            const retakeBtn = document.getElementById('retakeButton');
            const confirmClockIn = document.getElementById('confirmClockIn');
            const confirmClockOut = document.getElementById('confirmClockOut');

            // Late-reason modal elements
            const lateModalEl = document.getElementById('lateReasonModal');
            const lateModal = new bootstrap.Modal(lateModalEl);
            const lateInput = document.getElementById('lateReasonInput');
            const lateSubmitBtn = document.getElementById('lateReasonSubmit');

            // Subscription check
            if (subBlocked) {
                if (typeof toastr !== 'undefined') {
                    toastr.error(subBlockMessage ||
                        'Your subscription is not active. Please contact your administrator.');
                } else {
                    alert(subBlockMessage || 'Your subscription is not active. Please contact your administrator.');
                }
                if (clockInButton) {
                    clockInButton.disabled = true;
                    clockInButton.classList.add('disabled');
                }
                if (clockOutButton) {
                    clockOutButton.disabled = true;
                    clockOutButton.classList.add('disabled');
                }
            }

            // Camera state
            let stream, blobPhoto;
            let isClockingIn = true; // Track if we're clocking in or out

            async function startCamera(mode) {
                // Set mode flag
                isClockingIn = mode === 'in';

                // Show appropriate confirmation button based on mode
                confirmClockIn.style.display = isClockingIn ? 'inline-block' : 'none';
                confirmClockOut.style.display = !isClockingIn ? 'inline-block' : 'none';

                stream = await navigator.mediaDevices.getUserMedia({
                    video: true
                });
                video.srcObject = stream;
                video.style.display = 'block';
                preview.style.display = 'none';
                capBtn.style.display = 'inline-block';
                retakeBtn.style.display = 'none';
                confirmClockIn.style.display = 'none';
                confirmClockOut.style.display = 'none';
            }

            function stopCamera() {
                stream?.getTracks().forEach(t => t.stop());
            }

            capBtn.addEventListener('click', () => {
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                canvas.toBlob(b => {
                    blobPhoto = b;
                    img.src = URL.createObjectURL(b);
                    video.style.display = 'none';
                    preview.style.display = 'block';
                    capBtn.style.display = 'none';
                    retakeBtn.style.display = 'inline-block';

                    // Show the appropriate confirmation button
                    confirmClockIn.style.display = isClockingIn ? 'inline-block' : 'none';
                    confirmClockOut.style.display = !isClockingIn ? 'inline-block' : 'none';
                }, 'image/jpeg');
            });

            retakeBtn.addEventListener('click', () => {
                startCamera(isClockingIn ? 'in' : 'out');
            });

            cameraModalEl.addEventListener('hidden.bs.modal', stopCamera);

            // Much faster location handling
            let cachedCoords = null;
            let locationTimeout = null;

            // Pre-cache location when page loads
            if ((geotaggingEnabled || geofencingEnabled) && navigator.geolocation) {
                console.log('Pre-caching location...');

                // Quick low-accuracy cache first
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        cachedCoords = pos.coords;
                        console.log('✓ Location cached:', {
                            lat: pos.coords.latitude,
                            lng: pos.coords.longitude,
                            accuracy: pos.coords.accuracy
                        });
                    },
                    err => {
                        console.warn('Pre-cache failed:', err.message);
                    }, {
                        enableHighAccuracy: false,
                        maximumAge: 60000,
                        timeout: 2000 // Very quick for initial cache
                    }
                );
            }

            // FAST location getter with better error messages
            function getLocationOrFallback() {
                return new Promise((resolve, reject) => {
                    if (!navigator.geolocation) {
                        return reject(new Error('GEOLOCATION_NOT_SUPPORTED'));
                    }

                    // Check permissions first
                    if (navigator.permissions) {
                        navigator.permissions.query({
                            name: 'geolocation'
                        }).then(result => {
                            console.log('Location permission:', result.state);
                            if (result.state === 'denied') {
                                return reject(new Error('PERMISSION_DENIED'));
                            }
                        }).catch(() => {
                            // Ignore permission check errors, continue with location request
                        });
                    }

                    let resolved = false;

                    // Use cached if available and recent
                    if (cachedCoords) {
                        console.log('Using cached location');
                        resolved = true;
                        return resolve(cachedCoords);
                    }

                    console.log('Getting fresh location...');

                    // Set a manual timeout that's shorter than the geolocation timeout
                    locationTimeout = setTimeout(() => {
                        if (!resolved) {
                            resolved = true;
                            reject(new Error('TIMEOUT'));
                        }
                    }, 3000); // 3 second timeout

                    // Try high accuracy first, then fallback to low accuracy
                    navigator.geolocation.getCurrentPosition(
                        pos => {
                            if (!resolved) {
                                resolved = true;
                                clearTimeout(locationTimeout);
                                cachedCoords = pos.coords;
                                console.log('✓ Fresh location obtained:', {
                                    lat: pos.coords.latitude,
                                    lng: pos.coords.longitude
                                });
                                resolve(pos.coords);
                            }
                        },
                        err => {
                            console.warn('High accuracy failed, trying low accuracy...', err.message);

                            // Fallback to low accuracy
                            navigator.geolocation.getCurrentPosition(
                                pos => {
                                    if (!resolved) {
                                        resolved = true;
                                        clearTimeout(locationTimeout);
                                        cachedCoords = pos.coords;
                                        console.log('✓ Low accuracy location obtained:', {
                                            lat: pos.coords.latitude,
                                            lng: pos.coords.longitude
                                        });
                                        resolve(pos.coords);
                                    }
                                },
                                err2 => {
                                    if (!resolved) {
                                        resolved = true;
                                        clearTimeout(locationTimeout);
                                        console.error('All location attempts failed:', err2
                                            .message);
                                        reject(err2);
                                    }
                                }, {
                                    enableHighAccuracy: false,
                                    maximumAge: 30000,
                                    timeout: 2000
                                }
                            );
                        }, {
                            enableHighAccuracy: true,
                            maximumAge: 5000,
                            timeout: 2000
                        }
                    );
                });
            }

            // BETTER error messages based on error type
            function getLocationErrorMessage(error) {
                console.error('Location error details:', error);

                if (error.message === 'GEOLOCATION_NOT_SUPPORTED') {
                    return 'Your device does not support location services.';
                }

                if (error.message === 'PERMISSION_DENIED' || error.code === 1) {
                    return 'Location access denied. Please enable location permission for this site in your browser settings.';
                }

                if (error.message === 'TIMEOUT' || error.code === 3) {
                    return 'Location request timed out. Please check your GPS/location settings and try again.';
                }

                if (error.code === 2) {
                    return 'Location unavailable. Please check your internet connection and GPS settings.';
                }

                return 'Unable to get location. Please ensure location services are enabled and try again.';
            }

            // Compute minutes late
            function computeLateMinutes() {
                const [hh, mm, ss] = shiftStartTime.split(':').map(Number);
                const start = new Date();
                start.setHours(hh, mm, ss, 0);
                const diff = new Date() - start;
                return diff > 0 ? Math.floor(diff / 60000) : 0;
            }

            // Main Clock-In handler
            clockInButton.addEventListener('click', async (e) => {
                e.preventDefault();
                clockInButton.disabled = true;

                try {
                    if (isRestDay) {
                        toastr.error('You cannot clock in on a rest day.');
                        clockInButton.disabled = false;
                        return;
                    }

                    if (!hasShift) {
                        toastr.error('No active shift today.');
                        clockInButton.disabled = false;
                        return;
                    }

                    // 1) Photo?
                    if (requirePhoto) {
                        await startCamera('in');
                        cameraModal.show();
                        clockInButton.disabled = false;
                        return;
                    }

                    // 2) Late reason? (skip if flexible)
                    if (!isFlexible && lateReasonOn && computeLateMinutes() > graceMinutes) {
                        lateModal.show();
                        clockInButton.disabled = false;
                        return;
                    }

                    // 3) Location?
                    if ((geotaggingEnabled || geofencingEnabled)) {
                        try {
                            toastr.info('Getting your location...', '', {
                                timeOut: 1000
                            });
                            const coords = await getLocationOrFallback();
                            await doClockIn(null, coords.latitude, coords.longitude, null, coords
                                .accuracy);
                            return;
                        } catch (err) {
                            const errorMessage = getLocationErrorMessage(err);
                            toastr.error(errorMessage);
                            clockInButton.disabled = false;
                            return;
                        }
                    }

                    // 4) Direct (no location needed)
                    await doClockIn();

                } catch (error) {
                    console.error('Clock-in error:', error);
                    toastr.error('Something went wrong. Please try again.');
                    clockInButton.disabled = false;
                }
            });

            // Camera confirm clock in flow
            confirmClockIn.addEventListener('click', async () => {
                try {
                    // Late reason? (skip if flexible)
                    if (!isFlexible && lateReasonOn && computeLateMinutes() > graceMinutes) {
                        const onHidden = () => {
                            lateModal.show();
                            cameraModalEl.removeEventListener('hidden.bs.modal', onHidden);
                        };
                        cameraModalEl.addEventListener('hidden.bs.modal', onHidden);
                        cameraModal.hide();
                        return;
                    }

                    cameraModal.hide();

                    if ((geotaggingEnabled || geofencingEnabled)) {
                        try {
                            toastr.info('Getting your location...', '', {
                                timeOut: 1000
                            });
                            const coords = await getLocationOrFallback();
                            await doClockIn(blobPhoto, coords.latitude, coords.longitude, null, coords
                                .accuracy);
                            return;
                        } catch (err) {
                            const errorMessage = getLocationErrorMessage(err);
                            toastr.error(errorMessage);
                            clockInButton.disabled = false;
                            return;
                        }
                    }

                    await doClockIn(blobPhoto);

                } catch (error) {
                    console.error('Camera confirm error:', error);
                    toastr.error('Something went wrong. Please try again.');
                    clockInButton.disabled = false;
                }
            });

            // Late reason submit flow
            lateSubmitBtn.addEventListener('click', async () => {
                const reason = lateInput.value.trim();
                if (!reason) {
                    toastr.error('Please enter a reason.');
                    return;
                }

                lateModal.hide();

                try {
                    if ((geotaggingEnabled || geofencingEnabled)) {
                        toastr.info('Getting your location...', '', {
                            timeOut: 1000
                        });
                        const coords = await getLocationOrFallback();
                        await doClockIn(blobPhoto, coords.latitude, coords.longitude, reason, coords
                            .accuracy);
                        return;
                    }

                    await doClockIn(blobPhoto, null, null, reason);

                } catch (err) {
                    const errorMessage = getLocationErrorMessage(err);
                    toastr.error(errorMessage);
                    clockInButton.disabled = false;
                }
            });

            // Final clock in sender
            async function doClockIn(photoBlob = null, lat = null, lng = null, lateReason = null, accuracy = 0) {
                const formData = new FormData();
                if (photoBlob) formData.append('time_in_photo', photoBlob, 'selfie.jpg');
                if (geotaggingEnabled || geofencingEnabled) {
                    formData.append('time_in_latitude', lat);
                    formData.append('time_in_longitude', lng);
                    formData.append('time_in_accuracy', accuracy);
                }
                if (lateReason) formData.append('late_status_reason', lateReason);
                formData.append('clock_in_method', 'manual_web');

                try {
                    const res = await fetch('/api/attendance/clock-in', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Authorization': 'Bearer ' + localStorage.getItem('token'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    const data = await res.json();

                    if (res.ok) {
                        toastr.success(data.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                        return;
                    } else {
                        // Handle early clock-in error specifically
                        if (res.status === 403 && data.earliest_allowed_time) {
                            toastr.warning(`Early Clock-In Restricted: ${data.message}`, '', {
                                timeOut: 5000
                            });
                        } else {
                            toastr.error('Clock-In failed: ' + data.message);
                        }
                    }
                } catch (err) {
                    console.error('Network error:', err);
                    toastr.error('Network error. Please check your connection and try again.');
                } finally {
                    clockInButton.disabled = false;
                }
            }

            // Clock Out Button Handler
            if (clockOutButton) {
                clockOutButton.addEventListener('click', async (e) => {
                    e.preventDefault();
                    clockOutButton.disabled = true;

                    // 1) Photo?
                    if (requirePhoto) {
                        await startCamera('out');
                        cameraModal.show();
                        clockOutButton.disabled = false;
                        return;
                    }

                    // 2) Geo + shift
                    const form = new FormData();
                    form.append('shift_id', shiftId);
                    if ((geotaggingEnabled || geofencingEnabled) && navigator.geolocation) {
                        try {
                            const coords = await getLocationOrFallback();
                            form.append('time_out_latitude', coords.latitude);
                            form.append('time_out_longitude', coords.longitude);
                            form.append('time_out_accuracy', coords.accuracy || 0);
                        } catch (err) {
                            const errorMessage = getLocationErrorMessage(err);
                            toastr.error(errorMessage);
                            clockOutButton.disabled = false;
                            return;
                        }
                    }
                    form.append('clock_out_method', 'manual_web');
                    sendClockOut(form);
                });
            }

            // Camera confirm clock out
            confirmClockOut.addEventListener('click', async () => {
                cameraModal.hide();
                const form = new FormData();
                form.append('shift_id', shiftId);
                form.append('time_out_photo', blobPhoto, 'selfie.jpg');
                if ((geotaggingEnabled || geofencingEnabled)) {
                    try {
                        const coords = await getLocationOrFallback();
                        form.append('time_out_latitude', coords.latitude);
                        form.append('time_out_longitude', coords.longitude);
                        form.append('time_out_accuracy', coords.accuracy || 0);
                    } catch (err) {
                        const errorMessage = getLocationErrorMessage(err);
                        toastr.error(errorMessage);
                        clockOutButton.disabled = false;
                        return;
                    }
                }
                form.append('clock_out_method', 'manual_web');
                sendClockOut(form);
            });

            // Final clock out sender
            async function sendClockOut(form) {
                try {
                    const res = await fetch('/api/attendance/clock-out', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Authorization': 'Bearer ' + localStorage.getItem('token'),
                            'Accept': 'application/json'
                        },
                        body: form
                    });
                    const data = await res.json();
                    if (res.ok) {
                        toastr.success(data.message);
                        setTimeout(() => location.reload(), 500);
                    } else {
                        toastr.error('Clock-Out failed: ' + data.message);
                    }
                } catch (err) {
                    console.error('Network error:', err);
                    toastr.error('Network error. Please check your connection and try again.');
                } finally {
                    clockOutButton.disabled = false;
                }
            }
        });
    </script>

    <!-- Attendance Table Map -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let map, marker;

            // When someone clicks any "View Map" button
            document.querySelectorAll('.view-map-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const lat = parseFloat(btn.dataset.lat);
                    const lng = parseFloat(btn.dataset.lng);

                    // wait until Google Maps API is ready
                    if (typeof google === 'undefined') {
                        console.error('Google Maps not loaded');
                        return;
                    }

                    // Initialize map inside the modal container
                    const container = document.getElementById('mapModalContainer');
                    map = new google.maps.Map(container, {
                        center: {
                            lat,
                            lng
                        },
                        zoom: 15,
                        disableDefaultUI: false,
                    });

                    // Place or move marker
                    marker = new google.maps.Marker({
                        position: {
                            lat,
                            lng
                        },
                        map: map,
                        title: 'Clock-In Here'
                    });

                    // Show the Bootstrap modal
                    new bootstrap.Modal(document.getElementById('mapModal')).show();
                });
            });

            // Optional: clean up map when modal closes
            document.getElementById('mapModal').addEventListener('hidden.bs.modal', () => {
                document.getElementById('mapModalContainer').innerHTML = '';
                map = marker = null;
            });
        });
    </script>

    {{-- Request Attendance Script / Store Function --}}
    <script>
        $(document).ready(function() {
            function formatMinutes(mins) {
                if (isNaN(mins) || mins <= 0) return '';
                var hr = Math.floor(mins / 60);
                var min = mins % 60;
                var text = '';
                if (hr > 0) text += hr + 'hr' + (hr > 1 ? 's ' : ' ');
                if (min > 0) text += min + 'min' + (min > 1 ? 's' : '');
                return text.trim();
            }

            function computeNightDiffMinutes(startTime, endTime) {
                var totalNDMinutes = 0;

                // We'll check each night diff window (22:00–06:00 next day) for overlap
                var current = new Date(startTime);
                current.setHours(22, 0, 0, 0); // 10:00 PM

                // If the shift starts before the first 10PM, set window to today at 10PM
                if (startTime > current) {
                    // Already past 10PM, next window
                    current.setDate(current.getDate() + 1);
                }

                while (current < endTime) {
                    var ndWindowStart = new Date(current);
                    var ndWindowEnd = new Date(current);
                    ndWindowEnd.setHours(6, 0, 0, 0);
                    ndWindowEnd.setDate(ndWindowEnd.getDate() + 1);

                    // Overlap calculation
                    var overlapStart = new Date(Math.max(startTime, ndWindowStart));
                    var overlapEnd = new Date(Math.min(endTime, ndWindowEnd));
                    var overlap = overlapEnd - overlapStart;

                    if (overlap > 0) {
                        totalNDMinutes += Math.floor(overlap / 1000 / 60);
                    }
                    current.setDate(current.getDate() + 1);
                }

                return totalNDMinutes;
            }

            function computeRequestAttendanceMinutes() {
                var start = $('#requestAttendanceIn').val();
                var end = $('#requestAttendanceOut').val();
                var breakMins = parseInt($('#requestAttendanceBreakMinutes').val()) || 0;

                if (start && end) {
                    var startTime = new Date(start);
                    var endTime = new Date(end);

                    if (endTime > startTime) {
                        var diffMs = endTime - startTime;
                        var totalMinutes = Math.floor(diffMs / 1000 / 60);

                        // Night diff calculation
                        var ndMins = computeNightDiffMinutes(startTime, endTime);

                        // Regular minutes: total - ND
                        var regMins = totalMinutes - ndMins;

                        // Deduct break minutes from regular mins only
                        var regMinsFinal = regMins - breakMins;
                        if (regMinsFinal < 0) regMinsFinal = 0;

                        // Update fields
                        $('#requestAttendanceRequestMinutes').val(formatMinutes(regMinsFinal));
                        $('#requestAttendanceRequestMinutesHidden').val(regMinsFinal);

                        $('#requestAttedanceNightDiffMinutes').val(formatMinutes(ndMins));
                        $('#requestAttendanceNightDiffMinutesHidden').val(ndMins);

                        // Show/hide ND field
                        if (ndMins > 0) {
                            $('.ndHidden').show();
                        } else {
                            $('.ndHidden').hide();
                            $('#requestAttedanceNightDiffMinutes').val('');
                            $('#requestAttendanceNightDiffMinutesHidden').val('');
                        }
                    } else {
                        $('#requestAttendanceRequestMinutes').val('');
                        $('#requestAttendanceRequestMinutesHidden').val('');
                        $('.ndHidden').hide();
                    }
                } else {
                    $('#requestAttendanceRequestMinutes').val('');
                    $('#requestAttendanceRequestMinutesHidden').val('');
                    $('.ndHidden').hide();
                }
            }

            $('#requestAttendanceIn, #requestAttendanceOut, #requestAttendanceBreakMinutes').on('change input',
                computeRequestAttendanceMinutes);

            // Initially hide ND
            $('.ndHidden').hide();

            $('#requestAttendanceIn, #requestAttendanceOut').on('change input', computeRequestAttendanceMinutes);

            // Handle form submission (unchanged)
            $('#employeeRequestAttendanceForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this)[0];
                var formData = new FormData(form);
                formData.append('_token', '{{ csrf_token() }}');
                $.ajax({
                    type: 'POST',
                    url: '{{ url('api/attendance-employee/request') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Attendance request submitted successfully.');
                            $('#request_attendance').modal('hide');
                            setTimeout(function() {
                                window.location.reload();
                            }, 500);
                        } else {
                            toastr.error('Error: ' + (response.message ||
                                'Unable to request attendance.'));
                        }
                    },
                    error: function(xhr) {
                        let msg = 'An error occurred while processing your request.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                    }
                });
            });
        });
    </script>

    // Break Script
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const breakModal = new bootstrap.Modal(document.getElementById('breakModal'));
            const lunchButton = document.getElementById('lunchButton');
            const coffeeButton = document.getElementById('coffeeButton');
            const breakInBtn = document.getElementById('breakInBtn');
            const breakOutBtn = document.getElementById('breakOutBtn');
            const breakModalTitle = document.getElementById('breakModalTitle');
            const breakTypeTitle = document.getElementById('breakTypeTitle');
            const breakTypeIcon = document.getElementById('breakTypeIcon');
            const breakInfo = document.getElementById('breakInfo');
            const maxBreakTime = document.getElementById('maxBreakTime');

            let currentBreakType = '';
            const maxBreakMinutes =
                {{ $nextAssignment && $nextAssignment->shift ? $nextAssignment->shift->break_minutes : 0 }};

            // Set max break time
            if (maxBreakTime) {
                maxBreakTime.textContent = maxBreakMinutes;
            }

            // Break button click handlers
            function setupBreakButton(button, type, icon, title) {
                if (button) {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        currentBreakType = type;



                        // Update icon
                        const iconElement = breakTypeIcon.querySelector('i');
                        iconElement.className = `ti ${icon} text-primary fs-24`;

                        // Check current break status
                        checkBreakStatus();

                        breakModal.show();
                    });
                }
            }

            setupBreakButton(lunchButton, 'lunch', 'ti-salad', 'Lunch Break');
            setupBreakButton(coffeeButton, 'coffee', 'ti-coffee', 'Coffee Break');

            // Check current break status
            async function checkBreakStatus() {
                try {
                    console.log('🔍 Checking break status...');

                    const response = await fetch('/api/attendance/break-status', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    console.log('📊 Break status response:', data);

                    if (data.success) {
                        // ✅ NEW: Check if break is completed
                        if (data.break_completed) {
                            console.log('❌ Break already completed for this shift');

                            // Hide both buttons and show completion message
                            breakInBtn.style.display = 'none';
                            breakOutBtn.style.display = 'none';

                            // Update modal content to show break completed
                            breakTypeTitle.textContent = currentBreakType === 'lunch' ? 'Lunch Break' :
                                'Coffee Break';
                            breakInfo.innerHTML = `
                    <div class="alert alert-info mb-0">
                        <i class="ti ti-check-circle me-2"></i>
                        Break completed for this shift. Only one break is allowed per shift.
                    </div>
                `;

                            return;
                        }

                        if (data.has_active_break) {
                            console.log('✅ Active break found:', data.data);

                            // User has active break - show break out option
                            breakInBtn.style.display = 'none';
                            breakOutBtn.style.display = 'block';

                            const breakData = data.data;
                            const isOvertime = breakData.is_overtime;

                            breakOutBtn.innerHTML = isOvertime ?
                                '<i class="ti ti-stop me-2"></i>End Break (Overtime)' :
                                '<i class="ti ti-stop me-2"></i>End Break';

                            if (isOvertime) {
                                breakOutBtn.className = 'btn btn-warning';
                            } else {
                                breakOutBtn.className = 'btn btn-danger';
                            }

                        } else {
                            console.log('✅ No active break, break available');

                            // No active break - show break in option (if break is available)
                            if (data.data && data.data.break_available) {
                                breakInBtn.style.display = 'block';
                                breakOutBtn.style.display = 'none';

                                // Update break info with max minutes
                                if (maxBreakTime && data.data.max_break_minutes) {
                                    maxBreakTime.textContent = data.data.max_break_minutes;
                                }
                            } else {
                                // No break available for this shift
                                breakInBtn.style.display = 'none';
                                breakOutBtn.style.display = 'none';

                                breakInfo.innerHTML = `
                        <div class="alert alert-warning mb-0">
                            <i class="ti ti-info-circle me-2"></i>
                            Break time is not available for this shift.
                        </div>
                    `;
                            }
                        }
                    }
                } catch (error) {
                    console.error('❌ Error checking break status:', error);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Unable to check break status');
                    }
                }
            }

            // Break In handler
            if (breakInBtn) {
                breakInBtn.addEventListener('click', async function() {
                    try {
                        breakInBtn.disabled = true;

                        const response = await fetch('/api/attendance/break-in', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                break_type: currentBreakType
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            if (typeof toastr !== 'undefined') {
                                toastr.success(data.message);
                            }
                            breakModal.hide();

                            // Optionally refresh page or update UI
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(data.message || 'Failed to start break');
                            }
                        }
                    } catch (error) {
                        console.error('Break in error:', error);
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Unable to start break. Please try again.');
                        }
                    } finally {
                        breakInBtn.disabled = false;
                    }
                });
            }

            // Break Out handler
            if (breakOutBtn) {
                breakOutBtn.addEventListener('click', async function() {
                    try {
                        breakOutBtn.disabled = true;

                        const response = await fetch('/api/attendance/break-out', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            if (data.data.break_late_minutes > 0) {
                                if (typeof toastr !== 'undefined') {
                                    toastr.warning(data.message);
                                }
                            } else {
                                if (typeof toastr !== 'undefined') {
                                    toastr.success(data.message);
                                }
                            }
                            breakModal.hide();

                            // Optionally refresh page or update UI
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(data.message || 'Failed to end break');
                            }
                        }
                    } catch (error) {
                        console.error('Break out error:', error);
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Unable to end break. Please try again.');
                        }
                    } finally {
                        breakOutBtn.disabled = false;
                    }
                });
            }
        });
    </script>
@endpush
