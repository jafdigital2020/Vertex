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
                    <div class="mb-2">
                        <a href="#" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                            data-bs-target="#"><i class="ti ti-file-upload me-2"></i>Upload</a>
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

                $name = Auth::user()->personalInformation->first_name ?? Auth::user()->username;
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
                                        Auth::user()->personalInformation->profile_picture
                                            ? 'storage/' . Auth::user()->personalInformation->profile_picture
                                            : 'build/img/profiles/avatar-27.jpg',
                                    ) }}"
                                        alt="Img">
                                </div>
                            </div>

                            <div class="text-center">
                                <div class="badge badge-md badge-primary mb-3">Production :
                                    {{ $latest->total_work_minutes_formatted ?? '00' }}</div>
                                <h6 class="fw-medium d-flex align-items-center justify-content-center mb-3">
                                    <i class="ti ti-fingerprint text-primary me-1"></i>
                                    Clock-In at {{ $latest->time_only ?? '00:00' }}
                                </h6>

                                {{-- <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-icon btn-sm btn-warning" id="lunchButton"
                                            title="Lunch Break">
                                            <i class="ti ti-salad"></i>
                                        </button>
                                        <button class="btn btn-icon btn-sm btn-secondary" id="coffeeButton"
                                            title="Coffee Break">
                                            <i class="ti ti-coffee"></i>
                                        </button>
                                    </div>
                                    <h6 class="fw-medium d-flex align-items-center mb-0">
                                        <i class="ti ti-fingerprint text-primary me-1"></i>
                                        Clock-In at {{ $latest->time_only ?? '00:00' }}
                                    </h6>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-icon btn-sm btn-warning" id="lunchButton"
                                            title="Lunch Break">
                                            <i class="ti ti-salad"></i>
                                        </button>
                                        <button class="btn btn-icon btn-sm btn-secondary" id="coffeeButton"
                                            title="Coffee Break">
                                            <i class="ti ti-coffee"></i>
                                        </button>
                                    </div>
                                </div> --}}

                                <div class="d-flex justify-content-between">
                                    <a href="#" class="btn btn-primary w-100 me-2" id="clockInButton"
                                        data-has-shift="{{ $hasShift ? '1' : '0' }}">Clock-In</a>
                                    <a href="#" class="btn btn-outline-primary w-100" id="clockOutButton"
                                        data-shift-id="{{ $latest ? $latest->shift_id : '' }}">Clock-Out</a>
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
                                <button class="btn btn-light" data-bs-dismiss="modal" id="lateReasonCancel">Cancel</button>
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
                                        <h2 class="mb-2">8.36 / <span class="fs-20 text-gray-5"> 9</span></h2>
                                        <p class="fw-medium text-truncate">Total Hours Today</p>
                                    </div>
                                    <div>
                                        <p class="d-flex align-items-center fs-13">
                                            <span class="avatar avatar-xs rounded-circle bg-success flex-shrink-0 me-2">
                                                <i class="ti ti-arrow-up fs-12"></i>
                                            </span>
                                            <span>5% This Week</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Total Hours This Cut-off --}}
                        <div class="col-xl-4 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-2 pb-2">
                                        <span class="avatar avatar-sm bg-dark mb-2"><i class="ti ti-clock-up"></i></span>
                                        <h2 class="mb-2">10 / <span class="fs-20 text-gray-5"> 40</span></h2>
                                        <p class="fw-medium text-truncate">Total Hours Week</p>
                                    </div>
                                    <div>
                                        <p class="d-flex align-items-center fs-13">
                                            <span class="avatar avatar-xs rounded-circle bg-success flex-shrink-0 me-2">
                                                <i class="ti ti-arrow-up fs-12"></i>
                                            </span>
                                            <span>7% Last Week</span>
                                        </p>
                                    </div>
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
                                        <h2 class="mb-2">75 / <span class="fs-20 text-gray-5"> 98</span></h2>
                                        <p class="fw-medium text-truncate">Total Hours Month</p>
                                    </div>
                                    <div>
                                        <p class="d-flex align-items-center fs-13 text-truncate">
                                            <span class="avatar avatar-xs rounded-circle bg-danger flex-shrink-0 me-2">
                                                <i class="ti ti-arrow-down fs-12"></i>
                                            </span>
                                            <span>8% Last Month</span>
                                        </p>
                                    </div>
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
                                        <h2 class="mb-2">16 / <span class="fs-20 text-gray-5"> 28</span></h2>
                                        <p class="fw-medium text-truncate">Overtime this Month</p>
                                    </div>
                                    <div>
                                        <p class="d-flex align-items-center fs-13 text-truncate">
                                            <span class="avatar avatar-xs rounded-circle bg-danger flex-shrink-0 me-2">
                                                <i class="ti ti-arrow-down fs-12"></i>
                                            </span>
                                            <span>6% Last Month</span>
                                        </p>
                                    </div>
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
                                        <h2 class="mb-2">16 / <span class="fs-20 text-gray-5"> 28</span></h2>
                                        <p class="fw-medium text-truncate">Overtime this Month</p>
                                    </div>
                                    <div>
                                        <p class="d-flex align-items-center fs-13 text-truncate">
                                            <span class="avatar avatar-xs rounded-circle bg-danger flex-shrink-0 me-2">
                                                <i class="ti ti-arrow-down fs-12"></i>
                                            </span>
                                            <span>6% Last Month</span>
                                        </p>
                                    </div>
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
                                        <h2 class="mb-2">16 / <span class="fs-20 text-gray-5"> 28</span></h2>
                                        <p class="fw-medium text-truncate">Overtime this Month</p>
                                    </div>
                                    <div>
                                        <p class="d-flex align-items-center fs-13 text-truncate">
                                            <span class="avatar avatar-xs rounded-circle bg-danger flex-shrink-0 me-2">
                                                <i class="ti ti-arrow-down fs-12"></i>
                                            </span>
                                            <span>6% Last Month</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Employee Attendance</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                Select Status
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Present</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Absent</a>
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
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Last Month</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7 Days</a>
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
                                    <th>Date</th>
                                    <th>Shift</th>
                                    <th>Clock In</th>
                                    <th>Status</th>
                                    <th>Clock Out</th>
                                    <th>Late</th>
                                    <th>Photo</th>
                                    <th>Location</th>
                                    <th>Device</th>
                                    <th>Production Hours</th>
                                </tr>
                            </thead>
                            <tbody>
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
                                        <td>{{ $att->shift->name }}</td>
                                        <td>{{ $att->time_only }}</td>
                                        <td>
                                            <span class="badge {{ $badgeClass }} d-inline-flex align-items-center">
                                                <i class="ti ti-point-filled me-1"></i>{{ $statusText }}
                                            </span>
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
                                                    <ul class="dropdown-menu" style="z-index: 9999; overflow: visible;">
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
                                                    <ul class="dropdown-menu" style="z-index: 9999; overflow: visible;">
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
                                                    <ul class="dropdown-menu" style="z-index: 9999; overflow: visible;">
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
                                                <i
                                                    class="ti ti-clock-hour-11 me-1"></i>{{ $att->total_work_minutes_formatted }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2025 &copy; OneJAF Vertex.</p>
            <p>Designed &amp; Developed By <a href="javascript:void(0);" class="text-primary">JAF Digital Group Inc.</a>
            </p>
        </div>

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
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
        const graceMinutes = {{ $settings->grace_period }};
        const shiftStartTime = "{{ $nextAssignment?->shift?->start_time ?? '00:00:00' }}";
    </script>

    {{-- Clock In Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // UI elements
            const clockInButton = document.getElementById('clockInButton');

            const hasShift = clockInButton.dataset.hasShift === '1';

            // Camera modal elems
            const cameraModalEl = document.getElementById('cameraModal');
            const cameraModal = new bootstrap.Modal(cameraModalEl);
            const video = document.getElementById('cameraStream');
            const preview = document.getElementById('image-preview');
            const img = document.getElementById('capturedImage');
            const capBtn = document.getElementById('captureButton');
            const retakeBtn = document.getElementById('retakeButton');
            const confirmBtn = document.getElementById('confirmClockIn');
            const confirmOut = document.getElementById('confirmClockOut');

            // Late-reason modal elems
            const lateModalEl = document.getElementById('lateReasonModal');
            const lateModal = new bootstrap.Modal(lateModalEl);
            const lateInput = document.getElementById('lateReasonInput');
            const lateSubmitBtn = document.getElementById('lateReasonSubmit');

            // Camera state
            let stream, blobPhoto;
            async function startCamera() {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: true
                });
                video.srcObject = stream;
                video.style.display = 'block';
                preview.style.display = 'none';
                capBtn.style.display = 'inline-block';
                retakeBtn.style.display = 'none';
                confirmBtn.style.display = 'none';
                confirmOut.style.display = 'none';
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
                    confirmBtn.style.display = 'inline-block';
                }, 'image/jpeg');
            });
            retakeBtn.addEventListener('click', startCamera);
            cameraModalEl.addEventListener('hidden.bs.modal', stopCamera);

            // Fast geolocation: low-accuracy initial fetch to prime cache
            let cachedCoords = null;
            if ((geotaggingEnabled || geofencingEnabled) && navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        cachedCoords = pos.coords;
                    },
                    err => {
                        console.warn('Initial geo error', err);
                    }, {
                        enableHighAccuracy: false,
                        maximumAge: 60000,
                        timeout: 3000
                    }
                );
                // then keep cache fresh in background
                navigator.geolocation.watchPosition(
                    pos => {
                        cachedCoords = pos.coords;
                    },
                    err => {
                        /* ignore */
                    }, {
                        enableHighAccuracy: false,
                        maximumAge: 60000,
                        timeout: 3000
                    }
                );
            }

            // getLocation: use cached if available, else quick fallback
            function getLocationOrFallback() {
                return new Promise((resolve, reject) => {
                    if (cachedCoords) {
                        return resolve(cachedCoords);
                    }
                    navigator.geolocation.getCurrentPosition(
                        pos => resolve(pos.coords),
                        err => reject(err), {
                            enableHighAccuracy: false,
                            maximumAge: 0,
                            timeout: 3000
                        }
                    );
                });
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

                if (!hasShift) {
                    toastr.error('No active shift today.');
                    return;
                }

                // 1) Photo?
                if (requirePhoto) {
                    await startCamera();
                    cameraModal.show();
                    clockInButton.disabled = false;
                    return;
                }

                // 2) Late reason?
                if (lateReasonOn && computeLateMinutes() > graceMinutes) {
                    lateModal.show();
                    clockInButton.disabled = false;
                    return;
                }

                // 3) Location?
                if ((geotaggingEnabled || geofencingEnabled) && navigator.geolocation) {
                    return getLocationOrFallback()
                        .then(coords =>
                            doClockIn(null, coords.latitude, coords.longitude, null, coords.accuracy)
                        )
                        .catch(() => {
                            toastr.error('Unable to get location.');
                            clockInButton.disabled = false;
                        });
                }

                // 4) Direct
                doClockIn();
            });

            // After camera confirm
            confirmBtn.addEventListener('click', () => {
                if (lateReasonOn && computeLateMinutes() > graceMinutes) {
                    const onHidden = () => {
                        lateModal.show();
                        cameraModalEl.removeEventListener('hidden.bs.modal', onHidden);
                    };
                    cameraModalEl.addEventListener('hidden.bs.modal', onHidden);
                    cameraModal.hide();
                    return;
                }

                // 2) Otherwise just hide camera and proceed to geotag/send
                cameraModal.hide();

                if ((geotaggingEnabled || geofencingEnabled) && navigator.geolocation) {
                    return getLocationOrFallback()
                        .then(coords => doClockIn(blobPhoto, coords.latitude, coords.longitude, null, coords
                            .accuracy))
                        .catch(() => {
                            toastr.error('Please allow location access.');
                            clockInButton.disabled = false;
                        });
                }

                // 3) Finally, if no late-reason or geo needed, send immediately
                doClockIn(blobPhoto);
            });

            // After late reason submit
            lateSubmitBtn.addEventListener('click', () => {
                const reason = lateInput.value.trim();
                if (!reason) {
                    toastr.error('Please enter a reason.');
                    return;
                }
                lateModal.hide();

                if ((geotaggingEnabled || geofencingEnabled) && navigator.geolocation) {
                    return getLocationOrFallback()
                        .then(coords =>
                            doClockIn(blobPhoto, coords.latitude, coords.longitude, reason, coords.accuracy)
                        )
                        .catch(() => {
                            toastr.error('Unable to get location.');
                            clockInButton.disabled = false;
                        });
                }

                doClockIn(blobPhoto, null, null, reason);
            });

            // Final sender
            async function doClockIn(photoBlob = null, lat = null, lng = null, lateReason = null, accuracy = 0) {
                const formData = new FormData();
                if (photoBlob) formData.append('time_in_photo', photoBlob, 'selfie.jpg');
                if (geotaggingEnabled || geofencingEnabled) {
                    formData.append('time_in_latitude', lat);
                    formData.append('time_in_longitude', lng);
                    formData.append('time_in_accuracy', accuracy);
                }
                if (lateReason) formData.append('late_status_reason', lateReason);

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
                        toastr.error('Clock-In failed: ' + data.message);
                    }
                } catch (err) {
                    console.error(err);
                    toastr.error('Something went wrong. Please try again.');
                } finally {
                    // re-enable the button so the user can retry if it failed
                    clockInButton.disabled = false;
                }
            }
        });
    </script>

    {{-- Clock Out Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const requirePhoto = {{ $settings->require_photo_capture ? 'true' : 'false' }};
            const geotaggingEnabled = {{ $settings->geotagging_enabled ? 'true' : 'false' }};
            const geofencingEnabled = {{ $settings->geofencing_enabled ? 'true' : 'false' }};

            const btn = document.getElementById('clockOutButton');
            const shiftId = btn?.dataset.shiftId;
            const cameraEl = document.getElementById('cameraModal');
            const camera = new bootstrap.Modal(cameraEl);
            const video = document.getElementById('cameraStream');
            const preview = document.getElementById('image-preview');
            const img = document.getElementById('capturedImage');
            const capBtn = document.getElementById('captureButton');
            const retake = document.getElementById('retakeButton');
            const confirmIn = document.getElementById('confirmClockIn');
            const confirm = document.getElementById('confirmClockOut');

            let stream, photoBlob;

            // Camera helpers (same as above)
            async function startCam() {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: true
                });
                video.srcObject = stream;
                video.style.display = 'block';
                preview.style.display = 'none';
                capBtn.style.display = 'inline-block';
                retake.style.display = 'none';
                confirm.style.display = 'none';
            }

            function stopCam() {
                stream?.getTracks().forEach(t => t.stop());
            }
            capBtn.addEventListener('click', () => {
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                canvas.toBlob(b => {
                    photoBlob = b;
                    img.src = URL.createObjectURL(b);
                    video.style.display = 'none';
                    preview.style.display = 'block';
                    capBtn.style.display = 'none';
                    retake.style.display = 'inline-block';
                    confirm.style.display = 'inline-block';
                }, 'image/jpeg');
            });
            retake.addEventListener('click', startCam);
            cameraEl.addEventListener('hidden.bs.modal', stopCam);

            // Geo helpers (same as above)
            let cache = null;
            if ((geotaggingEnabled || geofencingEnabled) && navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    p => cache = p.coords,
                    () => {}, {
                        maximumAge: 60000,
                        timeout: 5000
                    }
                );
            }

            function getLoc() {
                return new Promise((res, rej) => {
                    if (cache && !geofencingEnabled) return res(cache);
                    navigator.geolocation.getCurrentPosition(
                        p => res(p.coords),
                        e => rej(e), {
                            maximumAge: 60000,
                            timeout: 5000
                        }
                    );
                });
            }

            // Submit helper
            async function sendClockOut(form) {
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
                if (res.ok) toastr.success(data.message), setTimeout(() => location.reload(), 500);
                else toastr.error('Clock-Out failed: ' + data.message);
                btn.disabled = false;
            }

            // Main handler
            if (btn) btn.addEventListener('click', async e => {
                e.preventDefault();
                btn.disabled = true;

                // 1) Photo?
                if (requirePhoto) {
                    await startCam();
                    camera.show();
                    btn.disabled = false;
                    return;
                }

                // 2) Geo + shift
                const form = new FormData();
                form.append('shift_id', shiftId);
                if ((geotaggingEnabled || geofencingEnabled) && navigator.geolocation) {
                    try {
                        const c = await getLoc();
                        form.append('time_out_latitude', c.latitude);
                        form.append('time_out_longitude', c.longitude);
                        form.append('time_out_accuracy', c.accuracy || 0);
                    } catch {
                        toastr.error('Enable location services');
                        btn.disabled = false;
                        return;
                    }
                }
                form.append('clock_out_method', 'manual_web');
                sendClockOut(form);
            });

            // After photo confirm
            confirm.addEventListener('click', async () => {
                camera.hide();
                const form = new FormData();
                form.append('shift_id', shiftId);
                form.append('time_out_photo', photoBlob, 'selfie.jpg');
                if ((geotaggingEnabled || geofencingEnabled) && navigator.geolocation) {
                    try {
                        const c = await getLoc();
                        form.append('time_out_latitude', c.latitude);
                        form.append('time_out_longitude', c.longitude);
                        form.append('time_out_accuracy', c.accuracy || 0);
                    } catch {
                        toastr.error('Enable location services');
                        btn.disabled = false;
                        return;
                    }
                }
                form.append('clock_out_method', 'manual_web');
                sendClockOut(form);
            });
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
@endpush
