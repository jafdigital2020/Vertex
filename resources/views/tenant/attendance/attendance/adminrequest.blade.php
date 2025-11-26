<?php $page = 'bulk-attendance-admin'; ?>
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
                    @if (in_array('Export', $permission))
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
                                    {{-- <li>
                                        <a href="{{ route('downloadAttendanceBulkImportTemplate') }}"
                                            class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Download
                                            Bulk Import Template </a>
                                    </li> --}}
                                </ul>
                            </div>
                        </div>
                    @endif
                    @if (in_array('Create', $permission))
                        <div class="mb-2 d-flex gap-2">
                            <a href="#" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                                data-bs-target="#attendance_upload_modal">
                                <i class="ti ti-file-upload me-2"></i> Import Default Attendance
                            </a>
                            {{-- <a href="#" class="btn btn-secondary d-flex align-items-center" data-bs-toggle="modal"
                                data-bs-target="#bulk_attendance_upload_modal">
                                <i class="ti ti-file-upload me-2"></i> Import Bulk Attendance
                            </a> --}}
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
                                <h4 class="mb-1">Attendance Details</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row gx-3">
                        <div class="col-lg-4 col-md-6">
                            <div class="card text-white position-relative overflow-hidden"
                                style="border-radius:10px; background: linear-gradient(135deg, #28a745 0%, #198754 100%); min-height:120px;">
                                <div class="card-body d-flex align-items-center justify-content-between p-3">
                                    <div class="me-3" style="z-index:3;">
                                        <p class="fs-12 fw-medium mb-1 text-white-75"><i
                                                class="ti ti-check text-white-75 me-1"></i>
                                            Present</p>
                                        <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                            {{ str_pad($totalPresent ?? 0, 2, '0', STR_PAD_LEFT) }}
                                        </h2>
                                        <small class="text-white-75">Employees</small>
                                    </div>

                                    <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                        <div
                                            style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                            <i class="ti ti-users"
                                                style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                        </div>
                                        <div
                                            style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                            <div
                                                style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                                <i class="ti ti-users"
                                                    style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="card text-white position-relative overflow-hidden"
                                style="border-radius:10px; background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); min-height:120px;">
                                <div class="card-body d-flex align-items-center justify-content-between p-3">
                                    <div class="me-3" style="z-index:3;">
                                        <p class="fs-12 fw-medium mb-1 text-white-75"><i
                                                class="ti ti-clock-edit text-white-75 me-1"></i>
                                            Late Login</p>
                                        <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                            {{ str_pad($totalLate ?? 0, 2, '0', STR_PAD_LEFT) }}
                                        </h2>
                                        <small class="text-white-75">Late</small>
                                    </div>

                                    <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                        <div
                                            style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                            <i class="ti ti-clock"
                                                style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                        </div>
                                        <div
                                            style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                            <div
                                                style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                                <i class="ti ti-clock"
                                                    style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="card text-white position-relative overflow-hidden"
                                style="border-radius:10px; background: linear-gradient(135deg, #e63946 0%, #a4161a 100%); min-height:120px;">
                                <div class="card-body d-flex align-items-center justify-content-between p-3">
                                    <div class="me-3" style="z-index:3;">
                                        <p class="fs-12 fw-medium mb-1 text-white-75"><i
                                                class="ti ti-user-off text-white-75 me-1"></i>
                                            Absent</p>
                                        <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                            {{ str_pad($totalAbsent ?? 0, 2, '0', STR_PAD_LEFT) }}
                                        </h2>
                                        <small class="text-white-75">Absent</small>
                                    </div>

                                    <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                        <div
                                            style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                            <i class="ti ti-user-off"
                                                style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                        </div>
                                        <div
                                            style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                            <div
                                                style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                                <i class="ti ti-user-off"
                                                    style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="payroll-btns mb-3">
                <a href="{{ route('attendance-admin') }}" class="btn btn-white  border me-2">Attendance</a>
                <a href="{{ route('adminRequestAttendance') }}" class="btn btn-primary active border me-2">Request
                    Attendance</a>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3 bg-primary">
                    <h5 class="text-white">Attendance Requests</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">

                        <!-- Bulk Actions Dropdown -->
                        <div class="dropdown me-2">
                            <button class="btn btn-outline-teal dropdown-toggle text-white" type="button"
                                id="bulkActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Bulk Actions
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="bulkActionsDropdown">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);"
                                        id="bulkApprove">
                                        <i class="ti ti-check me-2 text-success"></i>
                                        <span>Approve</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);"
                                        id="bulkReject">
                                        <i class="ti ti-x me-2 text-danger"></i>
                                        <span>Reject</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange-filtered"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter" onchange="filter()">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class=" form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select "
                                style="width:150px;" oninput="filter()">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" style="width:150px;"
                                class="select2 form-select" style="width:150px;" oninput="filter()">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select"
                                style="width:150px;" oninput="filter()">
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
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable" id="adminReqAttTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Total Break</th>
                                    <th>Total Hours</th>
                                    <th>File Attachment</th>
                                    <th>Status</th>
                                    <th>Next Approver</th>
                                    <th>Last Approved By</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="adminReqAttTableBody">
                                @foreach ($userAttendances as $req)
                                    @php
                                        $status = strtolower($req->status);
                                        $colors = [
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'pending' => 'info',
                                        ];
                                    @endphp
                                    <tr data-attendance-id="{{ $req->id }}">
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox"
                                                    value="{{ $req->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="#" class="avatar avatar-md border avatar-rounded">
                                                    <img src="{{ asset('storage/' . $req->user->personalInformation->profile_picture) }}"
                                                        class="img-fluid" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a
                                                            href="#">{{ $req->user->personalInformation->last_name }},
                                                            {{ $req->user->personalInformation->first_name }}</a></h6>
                                                    <span
                                                        class="fs-12 fw-normal ">{{ $req->user->employmentDetail->department->department_name ?? 'No Department' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <p class="fs-14 fw-medium d-flex align-items-center mb-0">
                                                    {{ $req->request_date->format('F j, Y') }}</p>
                                                <a href="#" class="ms-2" data-bs-toggle="tooltip"
                                                    data-bs-placement="right"
                                                    data-bs-title="{{ $req->reason ?? 'No reason provided' }}">
                                                    <i class="ti ti-info-circle text-info"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td>{{ $req->time_only }}</td>
                                        <td>{{ $req->time_out_only }}</td>
                                        <td>{{ $req->total_break_minutes_formatted }}</td>
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
                                            <div class="dropdown" style="position: static; overflow: visible;">
                                                <a href="#"
                                                    class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
                                                    data-bs-toggle="dropdown">
                                                    <span
                                                        class="rounded-circle bg-transparent-{{ $colors[$status] }} d-flex justify-content-center align-items-center me-2">
                                                        <i class="ti ti-point-filled text-{{ $colors[$status] }}"></i>
                                                    </span>
                                                    {{ ucfirst($status) }}
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                                    <li>
                                                        <a href="#"
                                                            class="dropdown-item d-flex align-items-center js-approve-btn {{ $status === 'approved' ? 'active' : '' }}"
                                                            data-action="approved"
                                                            data-reqattendance-id="{{ $req->id }}"
                                                            data-bs-toggle="modal" data-bs-target="#approvalModal">
                                                            <span
                                                                class="rounded-circle bg-transparent-{{ $colors['approved'] }} d-flex justify-content-center align-items-center me-2">
                                                                <i
                                                                    class="ti ti-point-filled text-{{ $colors['approved'] }}"></i>
                                                            </span>
                                                            Approved
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#"
                                                            class="dropdown-item d-flex align-items-center js-approve-btn {{ $status === 'rejected' ? 'active' : '' }}"
                                                            data-action="rejected"
                                                            data-reqattendance-id="{{ $req->id }}"
                                                            data-bs-toggle="modal" data-bs-target="#approvalModal">
                                                            <span
                                                                class="rounded-circle bg-transparent-{{ $colors['rejected'] }} d-flex justify-content-center align-items-center me-2">
                                                                <i
                                                                    class="ti ti-point-filled text-{{ $colors['rejected'] }}"></i>
                                                            </span>
                                                            Rejected
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#"
                                                            class="dropdown-item d-flex align-items-center js-approve {{ $status === 'pending' ? 'active' : '' }}"
                                                            data-action="CHANGES_REQUESTED"
                                                            data-reqattendance-id="{{ $req->id }}">
                                                            <span
                                                                class="rounded-circle bg-transparent-{{ $colors['pending'] }} d-flex justify-content-center align-items-center me-2">
                                                                <i
                                                                    class="ti ti-point-filled text-{{ $colors['pending'] }}"></i>
                                                            </span>
                                                            Pending
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>
                                            @if (count($req->next_approvers))
                                                {{ implode(', ', $req->next_approvers) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="d-flex flex-column">
                                                {{-- 1) Approver name --}}
                                                <span class="fw-semibold">
                                                    {{ $req->latest_approver ?? '—' }}
                                                    <a href="#" data-bs-toggle="tooltip" data-bs-placement="right"
                                                        data-bs-title="{{ $req->latestApproval->comment ?? 'No comment' }}">
                                                        <i class="ti ti-info-circle text-info"></i></a>
                                                </span>
                                                {{-- Approval date/time --}}
                                                @if ($req->latestApproval)
                                                    <small class="text-muted mt-1">
                                                        {{ \Carbon\Carbon::parse($req->latestApproval->acted_at)->format('d M Y, h:i A') }}
                                                    </small>
                                                @endif
                                            </div>
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

    <!-- Approval Comment Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="approvalForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approvalModalLabel">Add Approval Comment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="modalReqId">
                        <input type="hidden" id="modalAction">
                        <div class="mb-3">
                            <label for="modalComment" class="form-label">Comment</label>
                            <textarea id="modalComment" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    {{-- Date Range Picker --}}
    <script>
        if ($('.bookingrange-filtered').length > 0) {
            var start = moment().subtract(29, 'days');
            var end = moment();

            function booking_range(start, end) {
                $('.bookingrange-filtered span').html(start.format('M/D/YYYY') + ' - ' + end.format('M/D/YYYY'));
            }

            $('.bookingrange-filtered').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                    'Next Year': [moment().add(1, 'year').startOf('year'), moment().add(1, 'year').endOf('year')]
                }
            }, booking_range);

            booking_range(start, end);
        }
    </script>

    {{-- Filter --}}
    <script>
        function filter() {
            var dateRange = $('#dateRange_filter').val();
            var branch = $('#branch_filter').val();
            var department = $('#department_filter').val();
            var designation = $('#designation_filter').val();
            var status = $('#status_filter').val();

            $.ajax({
                url: '{{ route('adminRequestAttendanceFilter') }}',
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
                        $('#adminReqAttTable').DataTable().destroy();
                        $('#adminReqAttTableBody').html(response.html);
                        $('#adminReqAttTable').DataTable();

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

    {{-- Approved and Reject --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));

            // 1) Open modal for both Approve & Reject buttons
            document.addEventListener('click', function(event) {
                if (event.target.closest('.js-approve-btn')) {
                    const btn = event.target.closest('.js-approve-btn');
                    document.getElementById('modalReqId').value = btn.dataset.reqattendanceId;
                    document.getElementById('modalAction').value = btn.dataset.action;
                    document.getElementById('modalComment').value = '';
                    document.getElementById('approvalModalLabel').textContent =
                        btn.dataset.action === 'approved' ? 'Approve with comment' :
                        btn.dataset.action === 'rejected' ? 'Reject with comment' :
                        'Request Changes with comment';

                    console.log('Req ID Passed:', btn.dataset.reqattendanceId);
                }
            });


            // 2) Submit the modal form for both Approve & Reject
            document.getElementById('approvalForm').addEventListener('submit', async e => {
                e.preventDefault();

                const reqAttId = document.getElementById('modalReqId').value;
                const action = document.getElementById('modalAction').value;
                const comment = document.getElementById('modalComment').value.trim();
                const url = action === 'rejected' ?
                    `/api/attendance-admin/request-attendance/${reqAttId}/reject` :
                    `/api/attendance-admin/request-attendance/${reqAttId}/approve`;

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify({
                            action,
                            comment
                        }),
                    });

                    // show only the message text on error
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        console.error('Error response:', err);
                        throw new Error(err.message || 'Failed to update status.');
                    }

                    const json = await res.json();
                    toastr.success(json.message);
                    modal.hide();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);

                } catch (err) {
                    console.error(err);
                    toastr.error(err.message);
                }
            });
        });
    </script>

    {{-- Edit Request Attendance / Controller on Employee Attendance --}}
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

    {{-- Delete Request Attendance / Controller on Employee Attendance --}}
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
                        url: `/api/attendance-admin/request/delete/${requestDeleteId}/`,
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

    {{-- Dynamic Branch -> Department -> Designation --}}
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

            $('#designation_filter').on('input', function() {
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

    {{-- Bulk Actions --}}
    <script>
        // Bulk Actions Implementation
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const bulkApproveBtn = document.getElementById('bulkApprove');
            const bulkRejectBtn = document.getElementById('bulkReject');
            const bulkActionsDropdown = document.getElementById('bulkActionsDropdown');

            // ✅ Select All / Deselect All functionality
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                const rowCheckboxes = document.querySelectorAll(
                    '#adminReqAttTableBody input[type="checkbox"]');

                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });

                updateBulkActionButton();
            });

            // ✅ Individual checkbox change handler
            document.addEventListener('change', function(e) {
                if (e.target.type === 'checkbox' && e.target.closest('#adminReqAttTableBody')) {
                    updateSelectAllState();
                    updateBulkActionButton();
                }
            });

            // ✅ Update "Select All" checkbox state
            function updateSelectAllState() {
                const rowCheckboxes = document.querySelectorAll('#adminReqAttTableBody input[type="checkbox"]');
                const checkedBoxes = document.querySelectorAll(
                    '#adminReqAttTableBody input[type="checkbox"]:checked');

                if (checkedBoxes.length === 0) {
                    selectAllCheckbox.indeterminate = false;
                    selectAllCheckbox.checked = false;
                } else if (checkedBoxes.length === rowCheckboxes.length) {
                    selectAllCheckbox.indeterminate = false;
                    selectAllCheckbox.checked = true;
                } else {
                    selectAllCheckbox.indeterminate = true;
                    selectAllCheckbox.checked = false;
                }
            }

            // ✅ Update bulk action button state
            function updateBulkActionButton() {
                const checkedBoxes = document.querySelectorAll(
                    '#adminReqAttTableBody input[type="checkbox"]:checked');
                const hasSelection = checkedBoxes.length > 0;

                // Enable/disable bulk action dropdown
                bulkActionsDropdown.disabled = !hasSelection;

                if (hasSelection) {
                    bulkActionsDropdown.textContent = `Bulk Actions (${checkedBoxes.length})`;
                    bulkActionsDropdown.classList.remove('btn-outline-primary');
                    bulkActionsDropdown.classList.add('btn-primary');
                } else {
                    bulkActionsDropdown.textContent = 'Bulk Actions';
                    bulkActionsDropdown.classList.remove('btn-primary');
                    bulkActionsDropdown.classList.add('btn-outline-primary');
                }
            }

            // ✅ Get selected attendance IDs
            function getSelectedAttendanceIds() {
                const checkedBoxes = document.querySelectorAll(
                    '#adminReqAttTableBody input[type="checkbox"]:checked');
                const attendanceIds = [];

                checkedBoxes.forEach(checkbox => {
                    const row = checkbox.closest('tr');
                    const attendanceId = row.dataset
                        .attendanceId; // We'll add this data attribute to each row
                    if (attendanceId) {
                        attendanceIds.push(attendanceId);
                    }
                });

                return attendanceIds;
            }

            // ✅ Bulk Approve Handler
            bulkApproveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const selectedIds = getSelectedAttendanceIds();

                if (selectedIds.length === 0) {
                    toastr.warning('Please select at least one attendance request.');
                    return;
                }

                // Show confirmation dialog
                if (confirm(
                        `Are you sure you want to approve ${selectedIds.length} attendance request(s)?`)) {
                    processBulkAction('approve', selectedIds);
                }
            });

            // ✅ Bulk Reject Handler
            bulkRejectBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const selectedIds = getSelectedAttendanceIds();

                if (selectedIds.length === 0) {
                    toastr.warning('Please select at least one attendance request.');
                    return;
                }

                // Show confirmation dialog
                if (confirm(
                        `Are you sure you want to reject ${selectedIds.length} attendance request(s)?`)) {
                    processBulkAction('reject', selectedIds);
                }
            });

            // ✅ Process bulk action
            async function processBulkAction(action, attendanceIds) {
                const token = document.querySelector('meta[name="csrf-token"]').content;

                try {
                    // Show loading state
                    const actionBtn = action === 'approve' ? bulkApproveBtn : bulkRejectBtn;
                    const originalText = actionBtn.innerHTML;
                    actionBtn.innerHTML = '<i class="ti ti-loader ti-spin me-2"></i>Processing...';
                    actionBtn.style.pointerEvents = 'none';

                    const response = await fetch('/api/attendance-admin/request-attendance/bulk-action', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            action: action,
                            request_attendance_ids: attendanceIds,
                            comment: `Bulk ${action} by admin` // Default comment
                        })
                    });

                    const result = await response.json();

                    if (response.ok) {
                        toastr.success(result.message ||
                            `Successfully ${action}d ${attendanceIds.length} attendance request(s).`);

                        // Refresh the table
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        throw new Error(result.message || `Failed to ${action} attendance requests.`);
                    }
                } catch (error) {
                    console.error('Bulk action error:', error);
                    toastr.error(error.message || 'An error occurred while processing the bulk action.');
                } finally {
                    // Reset button state
                    const actionBtn = action === 'approve' ? bulkApproveBtn : bulkRejectBtn;
                    actionBtn.innerHTML = originalText;
                    actionBtn.style.pointerEvents = 'auto';
                }
            }

            // Initialize button state
            updateBulkActionButton();
        });
    </script>
@endpush
