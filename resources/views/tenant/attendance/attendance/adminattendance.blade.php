<?php $page = 'attendance-admin'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Attendance Admin</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Attendance Admin</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                     @if(in_array('Export',$permission))
                    <div class="me-2 mb-2">
                        <div class="dropdown">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                <i class="ti ti-file-export me-1"></i>Export / Download
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
                                    <a href="{{ route('downloadAttendanceTemplate') }}"
                                        class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Download
                                        Template </a>
                                </li>
                                <li>
                                    <a href="{{ route('downloadAttendanceBulkImportTemplate') }}"
                                        class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Download
                                        Bulk Import Template </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endif
                    @if(in_array('Create',$permission))
                    <div class="mb-2 d-flex gap-2">
                        <a href="#" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                            data-bs-target="#attendance_upload_modal">
                            <i class="ti ti-file-upload me-2"></i> Upload Attendance
                        </a>
                        <a href="#" class="btn btn-secondary d-flex align-items-center" data-bs-toggle="modal"
                            data-bs-target="#bulk_attendance_upload_modal">
                            <i class="ti ti-file-upload me-2"></i> Bulk Upload Attendance
                        </a>
                    </div>
                    @endif
                    <div class="ms-2 head-icons">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="card border-0">
                <div class="card-body">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-5">
                            <div class="mb-3 mb-md-0">
                                <h4 class="mb-1">Attendance Details Today</h4>
                                {{-- <p>Data from the 800+ total no of employees</p> --}}
                            </div>
                        </div> 
                    </div>
                        <div class="border rounded">
                        <div class="row gx-0">
                            <div class="col-md col-sm-4 border-end">
                        <div class="p-3">
                            <span class="fw-medium mb-1 d-block">
                                <i class="ti ti-check text-success me-1"></i> Present
                            </span>
                            <div class="d-flex align-items-center justify-content-between">
                                <h5>{{ $totalPresent }}</h5>
                                <span class="badge bg-success-subtle text-success d-inline-flex align-items-center">
                                    <i class="ti ti-users me-1"></i> Employees
                                </span>
                            </div>
                        </div>
                    </div> 
                    <div class="col-md col-sm-4 border-end">
                        <div class="p-3">
                            <span class="fw-medium mb-1 d-block">
                                <i class="ti ti-clock-edit text-warning me-1"></i> Late Login
                            </span>
                            <div class="d-flex align-items-center justify-content-between">
                                <h5>{{ $totalLate }}</h5>
                                <span class="badge bg-warning-subtle text-warning d-inline-flex align-items-center">
                                    <i class="ti ti-clock me-1"></i> Late
                                </span>
                            </div>
                        </div>
                    </div> 
                    <div class="col-md col-sm-4">
                        <div class="p-3">
                            <span class="fw-medium mb-1 d-block">
                                <i class="ti ti-user-off text-danger me-1"></i> Absent
                            </span>
                            <div class="d-flex align-items-center justify-content-between">
                                <h5>{{ $totalAbsent }}</h5>
                                <span class="badge bg-danger-subtle text-danger d-inline-flex align-items-center">
                                    <i class="ti ti-x me-1"></i> Absent
                                </span>
                            </div>
                        </div>
                    </div> 
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Admin Attendance</h5>
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
                          <div class="col-2 form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select ">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div> 
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select">
                                <option value="" selected>All Designations</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                @endforeach
                            </select>
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
                           <button class="btn btn-primary" onclick="filter()"><i class="fas fa-filter me-2"></i>Filter</button>
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
                                    <th class="text-center">Date</th>
                                    <th class="text-center">Shift</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Clock In</th>
                                    <th class="text-center">Clock Out</th>
                                    <th class="text-center">Late</th>
                                    <th>Photo</th>
                                    <th>Location</th>
                                    <th>Device</th>
                                    <th>Production Hours</th>
                                    @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                    <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="adminAttTableBody">
                                @foreach ($userAttendances as $userAtt)
                                    @php
                                        $status = $userAtt->status;
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
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="#" class="avatar avatar-md border avatar-rounded">
                                                    <img src="{{ URL::asset('build/img/users/user-49.jpg') }}"
                                                        class="img-fluid" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a
                                                            href="#">{{ $userAtt->user->personalInformation->last_name }},
                                                            {{ $userAtt->user->personalInformation->first_name }}
                                                            {{ $userAtt->user->personalInformation->middle_name }}.</a>
                                                    </h6>
                                                    <span
                                                        class="fs-12 fw-normal ">{{ $userAtt->user->employmentDetail->department->department_name }}</span>
                                                </div>
                                            </div>
                                        </td>
                                         <td class="text-center">
                                            @if($userAtt->attendance_date)
                                                {{ \Carbon\Carbon::parse($userAtt->attendance_date)->format('F j, Y') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                           <td class="text-center">{{ $userAtt->shift->name ?? '-' }}</td>
                                           <td class="text-center">
                                            <span class="badge {{ $badgeClass }} d-inline-flex align-items-center">
                                                <i class="ti ti-point-filled me-1"></i>{{ $statusText }}
                                            </span>
                                        </td>
                                           <td class="text-center">{{ $userAtt->time_only }}</td>
                                           <td class="text-center">{{ $userAtt->time_out_only }}</td>
                                            <td class="text-center">{{ $userAtt->total_late_formatted }}</td>
                                        <td>
                                            @if ($userAtt->time_in_photo_path || $userAtt->time_out_photo_path)
                                                <div class="btn-group" style="position: static; overflow: visible;">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                        data-bs-toggle="dropdown" data-bs-boundary="viewport"
                                                        data-bs-container="body">
                                                        View Photo
                                                    </button>
                                                    <ul class="dropdown-menu" style="z-index: 9999; overflow: visible;">
                                                        @if ($userAtt->time_in_photo_path)
                                                            <li>
                                                                <a class="dropdown-item"
                                                                    href="{{ Storage::url($userAtt->time_in_photo_path) }}"
                                                                    target="_blank">Clock-In Photo</a>
                                                            </li>
                                                        @endif
                                                        @if ($userAtt->time_out_photo_path)
                                                            <li>
                                                                <a class="dropdown-item"
                                                                    href="{{ Storage::url($userAtt->time_out_photo_path) }}"
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
                                            @if (
                                                ($userAtt->time_in_latitude && $userAtt->time_in_longitude) ||
                                                    ($userAtt->time_out_latitude && $userAtt->time_out_longitude))
                                                <div class="btn-group" style="position: static; overflow: visible;">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                        View Location
                                                    </button>
                                                    <ul class="dropdown-menu" style="z-index: 9999; overflow: visible;">
                                                        @if ($userAtt->time_in_latitude && $userAtt->time_in_longitude)
                                                            <li>
                                                                <a class="dropdown-item view-map-btn" href="#"
                                                                    data-lat="{{ $userAtt->time_in_latitude }}"
                                                                    data-lng="{{ $userAtt->time_in_longitude }}">Clock-In
                                                                    Location</a>
                                                            </li>
                                                        @endif
                                                        @if ($userAtt->time_out_latitude && $userAtt->time_out_longitude)
                                                            <li>
                                                                <a class="dropdown-item view-map-btn" href="#"
                                                                    data-lat="{{ $userAtt->time_out_latitude }}"
                                                                    data-lng="{{ $userAtt->time_out_longitude }}">Clock-Out
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
                                            @if ($userAtt->clock_in_method || $userAtt->clock_out_method)
                                                <div class="btn-group" style="position: static; overflow: visible;">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                        data-bs-toggle="dropdown" data-bs-boundary="viewport"
                                                        data-bs-container="body">
                                                        View Device
                                                    </button>
                                                    <ul class="dropdown-menu" style="z-index: 9999; overflow: visible;">
                                                        @if ($userAtt->clock_in_method)
                                                            <li>
                                                                <a class="dropdown-item" href="#">
                                                                    Clock-In Device ({{ $userAtt->clock_in_method }})</a>
                                                            </li>
                                                        @endif
                                                        @if ($userAtt->clock_out_method)
                                                            <li>
                                                                <a class="dropdown-item" href="#">
                                                                    Clock-Out Device ({{ $userAtt->clock_out_method }})</a>
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
                                                {{ $userAtt->total_work_minutes_formatted }}
                                            </span>
                                            @if (!empty($userAtt->total_night_diff_minutes_formatted) && $userAtt->total_night_diff_minutes_formatted !== '00:00')
                                                <br>
                                                <span class="badge badge-info d-inline-flex align-items-center mt-1">
                                                    <i class="ti ti-moon me-1"></i>
                                                    Night: {{ $userAtt->total_night_diff_minutes_formatted }}
                                                </span>
                                            @endif
                                        </td>
                                          @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                @if(in_array('Update',$permission))
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_attendance" data-id="{{ $userAtt->id }}"
                                                    data-clock-in="{{ optional($userAtt->date_time_in)->format('H:i') }}"
                                                    data-clock-out="{{ optional($userAtt->date_time_out)->format('H:i') }}"
                                                    data-total-late="{{ $userAtt->total_late_formatted }}"
                                                    data-work-minutes="{{ $userAtt->total_work_minutes_formatted }}"
                                                    data-attendance-date="{{ $userAtt->attendance_date->format('Y-m-d') }}"
                                                    data-status="{{ $userAtt->status }}"><i class="ti ti-edit"></i></a>
                                                @endif
                                                @if(in_array('Delete',$permission))
                                                <a href="#" class="me-2 btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_attendance" data-id="{{ $userAtt->id }}"
                                                    data-first-name="{{ $userAtt->user->personalInformation->first_name }}"><i
                                                        class="ti ti-trash"></i></a>
                                                @endif
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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

        {{-- Attendance Upload Modal --}}
        <div class="modal fade" id="attendance_upload_modal" tabindex="-1" aria-labelledby="attendanceUploadLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form action="{{ route('importAttendanceCSV') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="attendanceUploadLabel">Upload Attendance CSV(Per Row)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">Select CSV File</label>
                                <input type="file" name="csv_file" id="csv_file" class="form-control"
                                    accept=".csv" required>
                                <small class="form-text text-muted">Ensure you use the correct template.
                                    <a href="{{ asset('templates/attendance_template.csv') }}" class="text-primary"
                                        target="_blank">
                                        Download Template
                                    </a>
                                </small>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-white me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-upload me-1"></i> Import File
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Bulk Attendance Upload Modal --}}
        <div class="modal fade" id="bulk_attendance_upload_modal" tabindex="-1" aria-labelledby="attendanceUploadLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form action="{{ route('bulkImportAttendanceCSV') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="attendanceUploadLabel">Upload Attendance CSV(Bulk)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">Select CSV File</label>
                                <input type="file" name="csv_file" id="csv_file" class="form-control"
                                    accept=".csv" required>

                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-white me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-upload me-1"></i> Import File
                            </button>
                        </div>
                    </form>
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
<script>

        function filter(){
            var dateRange = $('#dateRange_filter').val();
            var branch = $('#branch_filter').val();
            var department = $('#department_filter').val();
            var designation = $('#designation_filter').val();
            var status = $('#status_filter').val();

             $.ajax({
                url: '{{ route('attendance-admin-filter') }}',
                type: 'GET',
                data: {
                    branch: branch,
                    department: department,
                    designation: designation,
                    dateRange: dateRange,
                    status: status, 
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#adminAttTableBody').html(response.html);
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
        document.addEventListener("DOMContentLoaded", function() {
            const authToken = localStorage.getItem("token");
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Helper: parse "X hr Y min" or "Y min" into total minutes
            function parseFormattedMinutes(str) {
                let hours = 0,
                    mins = 0;
                const hrMatch = str.match(/(\d+)\s*hr/);
                const minMatch = str.match(/(\d+)\s*min/);
                if (hrMatch) hours = parseInt(hrMatch[1], 10);
                if (minMatch) mins = parseInt(minMatch[1], 10);
                return hours * 60 + mins;
            }

            // 1. Populate modal form when "Edit" is clicked
            document.addEventListener("click", function (e) {
                if (e.target.closest('[data-bs-target="#edit_attendance"]')) {
                    const btn = e.target.closest('[data-bs-target="#edit_attendance"]');

                    const id = btn.dataset.id;
                    const date = btn.dataset.attendanceDate;
                    const clockIn = btn.dataset.clockIn;
                    const clockOut = btn.dataset.clockOut;
                    const rawLate = btn.dataset.totalLate;
                    const rawWork = btn.dataset.workMinutes;
                    const status = btn.dataset.status;

                    document.getElementById("editAttendanceId").value = id;
                    document.getElementById("attendanceDate").value = date;
                    document.getElementById("dateTimeIn").value = clockIn;
                    document.getElementById("dateTimeOut").value = clockOut;
                    document.getElementById("totalLateMinutes").value = rawLate;
                    document.getElementById("totalWorkMinutes").value = rawWork;
                    document.getElementById("attendanceStatus").value = status;
                }
            });
            // 2. Handle "Save Changes" button click
            document.getElementById("updateAttendanceBtn").addEventListener("click", async function(e) {
                e.preventDefault();

                const id = document.getElementById("editAttendanceId").value.trim();
                const date = document.getElementById("attendanceDate").value.trim();
                const clockIn = document.getElementById("dateTimeIn").value.trim();
                const clockOut = document.getElementById("dateTimeOut").value.trim();
                const rawLate = document.getElementById("totalLateMinutes").value;
                const rawWork = document.getElementById("totalWorkMinutes").value;
                const status = document.getElementById("attendanceStatus").value.trim();

                // Basic validation
                if (!date || !clockIn || !clockOut) {
                    toastr.error("Date, Clock-in and Clock-out are required.");
                    return;
                }

                // Convert formatted strings back to integers
                const lateMin = parseFormattedMinutes(rawLate);
                const workMin = parseFormattedMinutes(rawWork);

                try {
                    const res = await fetch(`/api/attendance-admin/update/${id}`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "Authorization": "Bearer " + authToken
                        },
                        body: JSON.stringify({
                            attendance_date: date,
                            date_time_in: clockIn,
                            date_time_out: clockOut,
                            total_late_minutes: lateMin,
                            total_work_minutes: workMin,
                            status: status
                        })
                    });

                    const payload = await res.json();
                    if (res.ok) {
                        toastr.success("Attendance updated successfully!");
                        $('#edit_attendance').modal('hide');
                        filter();
                    } else {
                        toastr.error(payload.message || "Update failed.");
                    }
                } catch (err) {
                    console.error(err);
                    toastr.error("Something went wrong.");
                }
            });
        });
    </script>

    {{-- Delete --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const authToken = localStorage.getItem("token");
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            let deleteId = null;

            const deleteButtons = document.querySelectorAll('.btn-delete');
            const attendanceConfirmDeleteBtn = document.getElementById('attendanceConfirmDeleteBtn');
            const userPlaceHolder = document.getElementById('userPlaceHolder');

            // Set up the delete buttons to capture data
            document.addEventListener("click", function(e) {
                const button = e.target.closest('[data-bs-target="#delete_attendance"]');
                if (button) {
                    deleteId = button.getAttribute('data-id');
                    const userName = button.getAttribute('data-first-name'); 
                    const userPlaceHolder = document.getElementById("userPlaceHolder");  
                    if (userPlaceHolder) {
                        userPlaceHolder.textContent = userName;
                    }
                }
            });

            // Confirm delete button click event
            attendanceConfirmDeleteBtn?.addEventListener('click', function() {
                if (!deleteId) return; // Ensure both deleteId and userId are available

                fetch(`/api/attendance-admin/delete/${deleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.getAttribute(
                                    "content"),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${authToken}`,
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Attendance deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_attendance'));
                            deleteModal.hide(); // Hide the modal
                            filter();
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message || "Error deleting attendance.");
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

    {{-- toastr for import --}}
    <script>
        @if (session('toastr_success'))
            toastr.success("{!! session('toastr_success') !!}");
        @endif

        @if (session('toastr_error'))
            toastr.error("{!! session('toastr_error') !!}");
        @endif

        @if (session('toastr_details') && is_array(session('toastr_details')))
            let details = `{!! implode('<br>', session('toastr_details')) !!}`;
            toastr.info(details);
        @endif
    </script>
      <script>
        function populateDropdown($select, items, placeholder = 'Select') {
            $select.empty();
            $select.append(`<option value="">All ${placeholder}</option>`);
            items.forEach(item => {
                $select.append(`<option value="${item.id}">${item.name}</option>`);
            });
        }

        $(document).ready(function () {

            $('#branch_filter').on('input', function () {
                const branchId = $(this).val();  

                $.get('/api/filter-from-branch', { branch_id: branchId }, function (res) {
                    if (res.status === 'success') {
                        populateDropdown($('#department_filter'), res.departments, 'Departments');
                        populateDropdown($('#designation_filter'), res.designations, 'Designations');
                    }
                });
            });


          $('#department_filter').on('input', function () {
                const departmentId = $(this).val();
                const branchId = $('#branch_filter').val();  

                $.get('/api/filter-from-department', {
                    department_id: departmentId,
                    branch_id: branchId,
                }, function (res) {
                    if (res.status === 'success') {
                        if (res.branch_id) {
                            $('#branch_filter').val(res.branch_id).trigger('change');
                        }
                        populateDropdown($('#designation_filter'), res.designations, 'Designations');
                    }
                });
            });

            $('#designation_filter').on('input', function () {
                const designationId = $(this).val();
                const branchId = $('#branch_filter').val();
                const departmentId = $('#department_filter').val();

                $.get('/api/filter-from-designation', {
                    designation_id: designationId,
                    branch_id: branchId,
                    department_id: departmentId
                }, function (res) {
                    if (res.status === 'success') {
                        if (designationId === '') {
                            populateDropdown($('#designation_filter'), res.designations, 'Designations');
                        } else {
                            $('#branch_filter').val(res.branch_id).trigger('change');
                            $('#department_filter').val(res.department_id).trigger('change');
                        }
                    }
                });
            }); 
        });

    </script>
@endpush
