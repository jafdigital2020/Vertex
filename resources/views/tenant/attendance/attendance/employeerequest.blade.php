<?php $page = 'request-attendance'; ?>
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
                <a href="{{ route('attendance-employee') }}" class="btn btn-white  border me-2">Attendance</a>
                <a href="{{ route('attendance-request') }}" class="btn btn-white active  border me-2">Attendance
                    Requests</a>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Attendance Requests</h5>
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
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select">
                                <option value="" selected>All Status</option>
                                <option value="present">Present</option>
                                <option value="late">Late</option>
                                <option value="absent">Absent</option>
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <button class="btn btn-primary" onclick="filter()"><i
                                    class="fas fa-filter me-2"></i>Filter</button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Total Break</th>
                                    <th>Total Hours</th>
                                    <th>File Attachment</th>
                                    <th>Status</th>
                                    <th>Approved By</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="empAttReqTableBody">
                                @foreach ($attendances as $req)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <p class="fs-14 fw-medium d-flex align-items-center mb-0">
                                                    {{ $req->request_date->format('Y-m-d') }}</p>
                                                <a href="#" class="ms-2" data-bs-toggle="tooltip"
                                                    data-bs-placement="right"
                                                    data-bs-title="{{ $req->reason ?? 'No reason provided' }}">
                                                    <i class="ti ti-info-circle text-info"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td>{{ $req->time_only }}</td>
                                        <td>{{ $req->time_out_only }}</td>
                                        <td>{{ $req->total_break_minutes_formatted ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-success d-inline-flex align-items-center">
                                                <i class="ti ti-clock-hour-11 me-1"></i>
                                                {{ $req->total_request_minutes_formatted }}
                                            </span>
                                            @if (!empty($req->total_request_nd_minutes_formatted) && $req->total_request_nd_minutes_formatted !== '00:00')
                                                <br>
                                                <span class="badge badge-info d-inline-flex align-items-center mt-1">
                                                    <i class="ti ti-moon me-1"></i>
                                                    Night: {{ $req->total_request_nd_minutes_formatted }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($req->file_attachment)
                                                <a href="{{ asset('storage/' . $req->file_attachment) }}"
                                                    class="text-primary" target="_blank">
                                                    <i class="ti ti-file-text"></i> View Attachment
                                                </a>
                                            @else
                                                <span class="text-muted">No Attachment</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = 'badge-info';
                                                if ($req->status == 'approved') {
                                                    $badgeClass = 'badge-success';
                                                } elseif ($req->status == 'rejected') {
                                                    $badgeClass = 'badge-warning';
                                                }
                                            @endphp
                                            <span
                                                class="badge {{ $badgeClass }} d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($req->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($req->lastApproverName)
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);"
                                                        class="avatar avatar-md border avatar-rounded">
                                                        <img src="{{ asset('storage/' . $req->latestApproval->approver->personalInformation->profile_picture) }}"
                                                            class="img-fluid" alt="avatar">
                                                    </a>
                                                    <div class="ms-2">
                                                        <h6 class="fw-medium mb-0">
                                                            {{ $req->lastApproverName }}
                                                        </h6>
                                                        <span class="fs-12 fw-normal">
                                                            {{ $req->lastApproverDept }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                        <td>
                                            @if ($req->status !== 'approved')
                                                <div class="action-icon d-inline-flex">
                                                    <a href="#" class="me-2" data-bs-toggle="modal"
                                                        data-bs-target="#edit_request_attendance"
                                                        data-id="{{ $req->id }}"
                                                        data-request-date="{{ $req->request_date }}"
                                                        data-request-in="{{ $req->request_date_in }}"
                                                        data-request-out="{{ $req->request_date_out }}"
                                                        data-total-minutes="{{ $req->total_request_minutes }}"
                                                        data-total-break="{{ $req->total_break_minutes }}"
                                                        data-total-nd="{{ $req->total_request_nd_minutes }}"
                                                        data-reason="{{ $req->reason }}"
                                                        data-file-attachment="{{ $req->file_attachment }}"><i
                                                            class="ti ti-edit"></i></a>
                                                    <a href="#" data-bs-toggle="modal" class="btn-delete"
                                                        data-bs-target="#delete_request_attendance"
                                                        data-id="{{ $req->id }}"><i class="ti ti-trash"></i></a>
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
                url: '{{ route('attendance-request-filter') }}',
                type: 'GET',
                data: {
                    dateRange: dateRange,
                    status: status,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#empAttReqTableBody').html(response.html);
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

                var current = new Date(startTime);
                current.setHours(22, 0, 0, 0);

                if (startTime > current) {
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
                            filter();
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

    {{-- Request Attendance Edit Function --}}
    <script>
        $(document).ready(function() {
            // When edit icon/link is clicked, populate the modal fields
            $(document).on('click', 'a[data-bs-target="#edit_request_attendance"]', function() {
                const $this = $(this);

                // Set values from data- attributes
                $('#editRequestAttendanceId').val($this.data('id'));

                let reqDate = $this.data('request-date');
                $('#editRequestAttendanceDate').val(reqDate ? reqDate.toString().substring(0, 10) : '');

                let reqIn = $this.data('request-in');
                $('#editRequestAttendanceIn').val(reqIn ? reqIn.replace(' ', 'T') : '');

                let reqOut = $this.data('request-out');
                $('#editRequestAttendanceOut').val(reqOut ? reqOut.replace(' ', 'T') : '');

                let breakMins = $this.data('total-break') || 0;
                $('#editRequestAttendanceBreakMinutes').val(breakMins);

                let ndMins = parseInt($this.data('total-nd')) || 0;
                $('#editRequestAttedanceNightDiffMinutes').val(formatMinutes(ndMins));
                $('#editRequestAttendanceNightDiffMinutesHidden').val(ndMins);

                let regMins = parseInt($this.data('total-minutes')) || 0;
                $('#editRequestAttendanceRequestMinutes').val(formatMinutes(regMins));
                $('#editRequestAttendanceRequestMinutesHidden').val(regMins);

                $('#editRequestAttedanceReason').val($this.data('reason') || '');

                // File attachment preview
                let attachment = $this.data('file-attachment');
                let displayHtml = '';
                if (attachment && attachment !== 'null' && attachment !== '') {
                    let url = `/storage/${attachment}`;
                    displayHtml = `<a href="${url}" target="_blank" class="badge bg-info">Current File</a>`;
                }
                $('#requestAttendanceCurrentAttachment').html(displayHtml);
                $('#editRequestAttendanceFileAttachment').val('');

                // Show/hide ND field
                if (ndMins > 0) {
                    $('.editNdHidden').show();
                } else {
                    $('.editNdHidden').hide();
                    $('#editRequestAttedanceNightDiffMinutes').val('');
                    $('#editRequestAttendanceNightDiffMinutesHidden').val('');
                }
            });

            // Util to format mins to hr/mins (for user display only)
            function formatMinutes(mins) {
                if (isNaN(mins) || mins <= 0) return '';
                var hr = Math.floor(mins / 60);
                var min = mins % 60;
                var text = '';
                if (hr > 0) text += hr + 'hr' + (hr > 1 ? 's ' : ' ');
                if (min > 0) text += min + 'min' + (min > 1 ? 's' : '');
                return text.trim();
            }

            // Recompute regular and ND mins when date/time/break changes
            $('#editRequestAttendanceIn, #editRequestAttendanceOut, #editRequestAttendanceBreakMinutes').on(
                'change input', computeEditAttendanceMinutes);

            function computeEditAttendanceMinutes() {
                var start = $('#editRequestAttendanceIn').val();
                var end = $('#editRequestAttendanceOut').val();
                var breakMins = parseInt($('#editRequestAttendanceBreakMinutes').val()) || 0;

                if (start && end) {
                    var startTime = new Date(start);
                    var endTime = new Date(end);

                    if (endTime > startTime) {
                        var diffMs = endTime - startTime;
                        var totalMinutes = Math.floor(diffMs / 1000 / 60);

                        var ndMins = computeNightDiffMinutes(startTime, endTime);
                        var regMins = totalMinutes - ndMins;
                        var regMinsFinal = regMins - breakMins;
                        if (regMinsFinal < 0) regMinsFinal = 0;

                        $('#editRequestAttendanceRequestMinutes').val(formatMinutes(regMinsFinal));
                        $('#editRequestAttendanceRequestMinutesHidden').val(regMinsFinal);

                        $('#editRequestAttedanceNightDiffMinutes').val(formatMinutes(ndMins));
                        $('#editRequestAttendanceNightDiffMinutesHidden').val(ndMins);

                        if (ndMins > 0) {
                            $('.editNdHidden').show();
                        } else {
                            $('.editNdHidden').hide();
                            $('#editRequestAttedanceNightDiffMinutes').val('');
                            $('#editRequestAttendanceNightDiffMinutesHidden').val('');
                        }
                    } else {
                        $('#editRequestAttendanceRequestMinutes').val('');
                        $('#editRequestAttendanceRequestMinutesHidden').val('');
                        $('.editNdHidden').hide();
                    }
                } else {
                    $('#editRequestAttendanceRequestMinutes').val('');
                    $('#editRequestAttendanceRequestMinutesHidden').val('');
                    $('.editNdHidden').hide();
                }
            }

            function computeNightDiffMinutes(startTime, endTime) {
                var totalNDMinutes = 0;
                var current = new Date(startTime);
                current.setHours(22, 0, 0, 0);

                if (startTime > current) {
                    current.setDate(current.getDate() + 1);
                }

                while (current < endTime) {
                    var ndWindowStart = new Date(current);
                    var ndWindowEnd = new Date(current);
                    ndWindowEnd.setHours(6, 0, 0, 0);
                    ndWindowEnd.setDate(ndWindowEnd.getDate() + 1);

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

            // Submit update
            $('#employeeEditRequestAttendanceForm').on('submit', function(e) {
                e.preventDefault();
                var id = $('#editRequestAttendanceId').val();
                var form = $(this)[0];
                var formData = new FormData(form);
                formData.append('_token', '{{ csrf_token() }}');
                formData.set('total_request_minutes', $('#editRequestAttendanceRequestMinutesHidden')
                    .val());
                formData.set('total_request_nd_minutes', $('#editRequestAttendanceNightDiffMinutesHidden')
                    .val());

                $.ajax({
                    type: 'POST',
                    url: `/api/attendance-employee/request/edit/${id}`,
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Attendance request updated successfully.');
                            $('#edit_request_attendance').modal('hide');
                            filter();
                        } else {
                            toastr.error('Error: ' + (response.message ||
                                'Unable to update request.'));
                        }
                    },
                    error: function(xhr) {
                        let msg = 'An error occurred while updating your request.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                    }
                });
            });

            $('.editNdHidden').hide();
        });
    </script>

    {{-- Request Attendance Delete Function --}}
    <script>
        let requestDeleteId = null;

        $(document).ready(function() {
            $(document).on('click', 'a[data-bs-target="#delete_request_attendance"]', function() {
                requestDeleteId = $(this).data('id');
            });

            // Handle delete confirmation
            $('#requestAttendanceConfirmBtn').on('click', function() {
                if (requestDeleteId) {
                    $.ajax({
                        type: 'DELETE',
                        url: `/api/attendance-employee/request/delete/${requestDeleteId}/`,
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success('Request attendance deleted successfully.');
                                $('#delete_request_attendance').modal('hide');
                                filter();
                            } else {
                                toastr.error('Error: ' + (response.message ||
                                    'Unable to delete request.'));
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
                }
            });
        });
    </script>
@endpush
