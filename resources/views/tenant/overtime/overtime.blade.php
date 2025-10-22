<?php $page = 'overtime'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Overtime</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i> Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Overtime</li>
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
                                        <a href="{{ route('downloadOvertimeTemplate') }}" class="dropdown-item rounded-1"><i
                                                class="ti ti-file-type-xls me-1"></i>Download
                                            Template</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
                    @if (in_array('Create', $permission) || in_array('Import', $permission))
                        <div class="mb-2">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#uploadOvertimeCSVModal"
                                class="btn btn-primary d-flex align-items-center"><i class="ti ti-upload me-2"></i>Upload
                                Overtime</a>
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

            <!-- Overtime Counts -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center flex-wrap justify-content-between">
                                <div>
                                    <p class="fs-12 fw-medium mb-0 text-gray-5">Pending Count</p>
                                    <h4 id="pendingCount">{{ $pendingCount }}</h4>
                                    <small class="text-muted">This Month</small>
                                </div>
                                <div>
                                    <span
                                        class="p-2 br-10 bg-pink-transparent border border-pink d-flex align-items-center justify-content-center"><i
                                            class="ti ti-user-edit text-pink fs-18"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center flex-wrap justify-content-between">
                                <div>
                                    <p class="fs-12 fw-medium mb-0 text-gray-5">Approved Request</p>
                                    <h4 id="approvedCount">{{ $approvedCount }}</h4>
                                    <small class="text-muted">This Month</small>
                                </div>
                                <div>
                                    <span
                                        class="p-2 br-10 bg-transparent-purple border border-purple d-flex align-items-center justify-content-center"><i
                                            class="ti ti-user-exclamation text-purple fs-18"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center flex-wrap justify-content-between">
                                <div>
                                    <p class="fs-12 fw-medium mb-0 text-gray-5">Rejected</p>
                                    <h4 id="rejectedCount">{{ $rejectedCount }}</h4>
                                    <small class="text-muted">This Month</small>
                                </div>
                                <div>
                                    <span
                                        class="p-2 br-10 bg-skyblue-transparent border border-skyblue d-flex align-items-center justify-content-center"><i
                                            class="ti ti-user-exclamation text-skyblue fs-18"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center flex-wrap justify-content-between">
                                <div>
                                    <p class="fs-12 fw-medium mb-0 text-gray-5">Overtime Requests</p>
                                    <h4 id="totalRequests">{{ $totalRequests }}</h4>
                                    <small class="text-muted">This Month</small>
                                </div>
                                <div>
                                    <span
                                        class="p-2 br-10 bg-transparent-primary border border-primary d-flex align-items-center justify-content-center"><i
                                            class="ti ti-user-check text-primary fs-18"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Overtime Counts -->

            <!-- Performance Indicator list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Overtime</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">

                        <!-- Bulk Actions Dropdown -->
                        <div class="dropdown me-2">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="bulkActionsDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
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
                                @if (in_array('Delete', $permission))
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center"
                                            id="bulkDelete">
                                            <i class="ti ti-trash me-2 text-danger"></i>
                                            <span>Delete</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange-filtered"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group me-2" style="max-width:200px;">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select"
                                style="width:150px;" oninput="filter()">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select"
                                style="width:150px;" oninput="filter()">
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
                            <select name="status_filter" id="status_filter" class="select2 form-select"
                                style="width:150px;" oninput="filter()">
                                <option value="" selected>All Status</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable" id="overtimeAdminTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Employee</th>
                                    <th class="text-center">Date </th>
                                    <th class="text-center">Start & End Time</th>
                                    <th class="text-center">Overtime Hours</th>
                                    <th class="text-center">File Attachment</th>
                                    <th class="text-center">Offset Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">OT Type</th>
                                    <th class="text-center">Next Approver</th>
                                    <th class="text-center">Last Approved By</th>
                                    @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="overtimeAdminTableBody">
                                @if (in_array('Read', $permission))
                                    @foreach ($overtimes as $ot)
                                        @php
                                            $status = strtolower($ot->status);
                                            $colors = [
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'pending' => 'info',
                                            ];
                                        @endphp
                                        <tr data-overtime-id="{{ $ot->id }}">
                                            <td>
                                                <div class="form-check form-check-md">
                                                    <input class="form-check-input" type="checkbox"
                                                        value="{{ $ot->id }}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center file-name-icon">
                                                    <a href="#" class="avatar avatar-md border avatar-rounded">
                                                        <img src="{{ asset('storage/' . $ot->user->personalInformation->profile_picture) }}"
                                                            class="img-fluid" alt="img">
                                                    </a>
                                                    <div class="ms-2">
                                                        <h6 class="fw-medium"><a
                                                                href="#">{{ $ot->user->personalInformation->last_name }},
                                                                {{ $ot->user->personalInformation->first_name }}</a></h6>
                                                        <span
                                                            class="fs-12 fw-normal ">{{ $ot->user->employmentDetail->department->department_name }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                {{ $ot->overtime_date ? $ot->overtime_date->format('F j, Y') : 'N/A' }}
                                            </td>
                                            <td class="text-center">
                                                {{ $ot->date_ot_in ? $ot->date_ot_in->format('g:i A') : 'N/A' }} -
                                                {{ $ot->date_ot_out ? $ot->date_ot_out->format('g:i A') : 'N/A' }}
                                            </td>

                                            <td class="text-center">
                                                <div>
                                                    <span class="d-block">
                                                        <strong>OT:</strong> {{ $ot->total_ot_minutes_formatted }}
                                                    </span>
                                                    <span class="d-block">
                                                        <strong>ND:</strong> {{ $ot->total_night_diff_minutes_formatted }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if ($ot->file_attachment)
                                                    <a href="{{ asset('storage/' . $ot->file_attachment) }}"
                                                        class="text-primary" target="_blank">
                                                        <i class="ti ti-file-text"></i> View Attachment
                                                    </a>
                                                @else
                                                    <span class="text-muted">No Attachment</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{ $ot->offset_date ? \Carbon\Carbon::parse($ot->offset_date)->format('F j, Y') : 'N/A' }}
                                            </td>
                                            <td class="text-center">
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
                                                                data-overtime-id="{{ $ot->id }}"
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
                                                                data-overtime-id="{{ $ot->id }}"
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
                                                                data-overtime-id="{{ $ot->id }}">
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
                                            <td class="text-center">{{ $ot->ot_login_type }}</td>
                                            <td class="text-center">
                                                @if (count($ot->next_approvers))
                                                    {{ implode(', ', $ot->next_approvers) }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="d-flex flex-column">
                                                    {{-- 1) Approver name --}}
                                                    <span class="fw-semibold">
                                                        {{ $ot->last_approver ?? '—' }}
                                                        <a href="#" data-bs-toggle="tooltip"
                                                            data-bs-placement="right"
                                                            data-bs-title="{{ $ot->latestApproval->comment ?? 'No comment' }}">
                                                            <i class="ti ti-info-circle text-info"></i></a>
                                                    </span>
                                                    {{-- Approval date/time --}}
                                                    @if ($ot->latestApproval)
                                                        <small class="text-muted mt-1">
                                                            {{ \Carbon\Carbon::parse($ot->latestApproval->acted_at)->format('d M Y, h:i A') }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                            @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                                <td class="text-center">
                                                    <div class="action-icon d-inline-flex">
                                                        @if (in_array('Update', $permission))
                                                            <a href="#" class="me-2" data-bs-toggle="modal"
                                                                data-bs-target="#edit_admin_overtime"
                                                                data-id="{{ $ot->id }}"
                                                                data-user-id="{{ $ot->user_id }}"
                                                                data-overtime-date="{{ $ot->overtime_date }}"
                                                                data-ot-in="{{ $ot->date_ot_in }}"
                                                                data-ot-out="{{ $ot->date_ot_out }}"
                                                                data-total-ot="{{ $ot->total_ot_minutes }}"
                                                                data-file-attachment="{{ $ot->file_attachment }}"
                                                                data-offset-date="{{ $ot->offset_date }}"
                                                                data-status="{{ $ot->status }}"><i
                                                                    class="ti ti-edit"></i></a>
                                                        @endif
                                                        @if (in_array('Delete', $permission))
                                                            <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                                data-bs-target="#delete_admin_overtime"
                                                                data-id="{{ $ot->id }}"
                                                                data-user-name="{{ $ot->user->personalInformation->first_name }} {{ $ot->user->personalInformation->last_name }}"><i
                                                                    class="ti ti-trash"></i></a>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /OT list -->
        </div>

        <!-- Approval Comment Modal -->
        <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="approvalForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="approvalModalLabel">Add Approval Comment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="modalOvertimeId">
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

        <div class="modal fade" id="uploadOvertimeCSVModal" tabindex="-1" aria-labelledby="uploadOvertimeCSVLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form action="{{ route('importOvertimeCSV') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="modal-header">
                            <h5 class="modal-title" id="uploadOvertimeCSVLabel">Upload Overtime CSV</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">Select CSV File</label>
                                <input type="file" name="csv_file" id="csv_file" class="form-control"
                                    accept=".csv" required>
                                <small class="form-text text-muted">Ensure you use the correct template.
                                    <a href="{{ asset('templates/overtime_template.csv') }}" class="text-primary">
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

        @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

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

        $('#dateRange_filter').on('apply.daterangepicker', function(ev, picker) {
            filter();
        });

        function filter() {
            const dateRange = $('#dateRange_filter').val();
            const branch = $('#branch_filter').val();
            const department = $('#department_filter').val();
            const designation = $('#designation_filter').val();
            const status = $('#status_filter').val();

            $.ajax({
                url: '{{ route('overtime-admin-filter') }}',
                type: 'GET',
                data: {
                    branch,
                    department,
                    designation,
                    dateRange,
                    status,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#overtimeAdminTable').DataTable().destroy();
                        $('#overtimeAdminTableBody').html(response.html);
                        $('#overtimeAdminTable').DataTable();
                        $('#pendingCount').text(response.pendingCount);
                        $('#approvedCount').text(response.approvedCount);
                        $('#rejectedCount').text(response.rejectedCount);
                        $('#totalRequests').text(response.totalRequests);
                    } else {
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

    {{-- Approvers Steps --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));

            // 1) Open modal for both Approve & Reject buttons
            document.addEventListener('click', function(event) {
                if (event.target.closest('.js-approve-btn')) {
                    const btn = event.target.closest('.js-approve-btn');
                    document.getElementById('modalOvertimeId').value = btn.dataset.overtimeId;
                    document.getElementById('modalAction').value = btn.dataset.action;
                    document.getElementById('modalComment').value = '';
                    document.getElementById('approvalModalLabel').textContent =
                        btn.dataset.action === 'approved' ? 'Approve with comment' :
                        btn.dataset.action === 'rejected' ? 'Reject with comment' :
                        'Request Changes with comment';
                }
            });

            // 2) Submit the modal form for both Approve & Reject
            document.getElementById('approvalForm').addEventListener('submit', async e => {
                e.preventDefault();

                const overtimeId = document.getElementById('modalOvertimeId').value;
                const action = document.getElementById('modalAction').value;
                const comment = document.getElementById('modalComment').value.trim();
                const url = action === 'rejected' ?
                    `/api/overtime/${overtimeId}/reject` :
                    `/api/overtime/${overtimeId}/approve`;

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

    {{-- Edit Overtime --}}
    <script>
        $(document).ready(function() {

            $(document).on('click', 'a[data-bs-target="#edit_admin_overtime"]', function() {
                const id = $(this).data('id');
                $('#editAdminOvertimeForm').data('id', id);

                // Overtime date
                let overtimeDate = $(this).data('overtime-date');
                $('#editAdminOvertimeDate').val(overtimeDate ? overtimeDate.toString().substring(0, 10) :
                    '');

                // Offset date
                let offsetDate = $(this).data('offset-date');
                $('#editAdminOvertimeOffsetDate').val(offsetDate ? offsetDate.toString().substring(0, 10) :
                    '');

                // Other inputs
                $('#overtimeUserId').val($(this).data('user-id'));
                $('#editAdminOvertimeDateOtIn').val($(this).data('ot-in'));
                $('#editAdminOvertimeDateOtOut').val($(this).data('ot-out'));

                // Total OT minutes
                let mins = parseInt($(this).data('total-ot')) || 0;
                $('#editAdminOvertimeTotalOtMinutes').val(formatMinutes(mins));
                $('#editAdminOvertimeTotalOtMinutesHidden').val(mins);

                // File attachment
                let attachment = $(this).data('file-attachment');
                let displayHtml = '';
                if (attachment && attachment !== 'null' && attachment !== '') {
                    let url = `/storage/${attachment}`;
                    let filename = attachment.split('/').pop();
                    displayHtml = `
                    <a href="${url}" target="_blank" class="text-primary">
                        <i class="ti ti-file"></i> View Current Attachment
                    </a>`;
                }
                $('#currentAdminOvertimeAttachment').html(displayHtml);

                // Clear file input
                $('#adminOvertimeFileAttachment').val('');
            });

            // Recompute minutes when user changes start/end
            function formatMinutes(mins) {
                if (isNaN(mins) || mins <= 0) return '';
                var hr = Math.floor(mins / 60);
                var min = mins % 60;
                var text = '';
                if (hr > 0) text += hr + 'hr' + (hr > 1 ? 's ' : ' ');
                if (min > 0) text += min + 'min' + (min > 1 ? 's' : '');
                return text.trim();
            }

            function computeOvertimeMinutesEdit() {
                var start = $('#editAdminOvertimeDateOtIn').val();
                var end = $('#editAdminOvertimeDateOtOut').val();
                if (start && end) {
                    var startTime = new Date(start);
                    var endTime = new Date(end);
                    if (endTime > startTime) {
                        var diffMs = endTime - startTime;
                        var diffMins = Math.floor(diffMs / 1000 / 60);
                        $('#editAdminOvertimeTotalOtMinutes').val(formatMinutes(diffMins));
                        $('#editAdminOvertimeTotalOtMinutesHidden').val(diffMins);
                    } else {
                        $('#editAdminOvertimeTotalOtMinutes').val('');
                        $('#editAdminOvertimeTotalOtMinutesHidden').val('');
                    }
                } else {
                    $('#editAdminOvertimeTotalOtMinutes').val('');
                    $('#editAdminOvertimeTotalOtMinutesHidden').val('');
                }
            }
            $('#editAdminOvertimeDateOtIn, #editAdminOvertimeDateOtOut').on('change input',
                computeOvertimeMinutesEdit);

            // Submit update AJAX
            $('#editAdminOvertimeForm').on('submit', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                var form = $(this)[0];
                var formData = new FormData(form);
                formData.append('_token', '{{ csrf_token() }}');
                formData.set('total_ot_minutes', $('#editAdminOvertimeTotalOtMinutesHidden').val());

                $.ajax({
                    type: 'POST',
                    url: `/api/overtime/update/${id}/`,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Overtime updated successfully.');
                            $('#edit_admin_overtime').modal('hide');
                            filter();
                        } else {
                            toastr.error('Error: ' + (response.message ||
                                'Unable to update overtime.'));
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

    {{-- Delete Overtime --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let deleteId = null;

            const confirmOvertimeAdminDeleteBtn = document.getElementById('confirmOvertimeAdminDeleteBtn');
            const userPlaceholder = document.getElementById('userPlaceholder');

            $(document).on('click', '.btn-delete', function() {
                deleteId = $(this).data('id');
                const userName = $(this).data('user-name');

                $('#userPlaceholder').text(userName);
                $('#deleteOvertimeId').val(deleteId);
            });

            confirmOvertimeAdminDeleteBtn?.addEventListener('click', function() {
                if (!deleteId) return;

                fetch(`/api/overtime/delete/${deleteId}`, {
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
                            toastr.success("Overtime deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_admin_overtime'));
                            deleteModal?.hide();
                            filter();
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message || "Error deleting overtime.");
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

    {{-- Toastr Notifications --}}
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

    {{-- Dynamic Filters --}}
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

    {{-- Bulk Actions --}}
    <script>
        // Bulk Actions Implementation
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const bulkApproveBtn = document.getElementById('bulkApprove');
            const bulkRejectBtn = document.getElementById('bulkReject');
            const bulkDeleteBtn = document.getElementById('bulkDelete');
            const bulkActionsDropdown = document.getElementById('bulkActionsDropdown');

            // ✅ Select All / Deselect All functionality
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                const rowCheckboxes = document.querySelectorAll(
                    '#overtimeAdminTableBody input[type="checkbox"]');

                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });

                updateBulkActionButton();
            });

            // ✅ Individual checkbox change handler
            document.addEventListener('change', function(e) {
                if (e.target.type === 'checkbox' && e.target.closest('#overtimeAdminTableBody')) {
                    updateSelectAllState();
                    updateBulkActionButton();
                }
            });

            // ✅ Update "Select All" checkbox state
            function updateSelectAllState() {
                const rowCheckboxes = document.querySelectorAll('#overtimeAdminTableBody input[type="checkbox"]');
                const checkedBoxes = document.querySelectorAll(
                    '#overtimeAdminTableBody input[type="checkbox"]:checked');

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
                    '#overtimeAdminTableBody input[type="checkbox"]:checked');
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

            // ✅ Get selected overtime IDs
            function getSelectedOvertimeIds() {
                const checkedBoxes = document.querySelectorAll(
                    '#overtimeAdminTableBody input[type="checkbox"]:checked');
                const overtimeIds = [];

                checkedBoxes.forEach(checkbox => {
                    const row = checkbox.closest('tr');
                    const overtimeId = row.dataset.overtimeId;
                    if (overtimeId) {
                        overtimeIds.push(overtimeId);
                    }
                });

                return overtimeIds;
            }

            // ✅ Bulk Approve Handler
            bulkApproveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const selectedIds = getSelectedOvertimeIds();

                if (selectedIds.length === 0) {
                    toastr.warning('Please select at least one overtime request.');
                    return;
                }

                // Show confirmation dialog
                if (confirm(
                        `Are you sure you want to approve ${selectedIds.length} overtime request(s)?`)) {
                    processBulkAction('approve', selectedIds);
                }
            });

            // ✅ Bulk Reject Handler
            bulkRejectBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const selectedIds = getSelectedOvertimeIds();

                if (selectedIds.length === 0) {
                    toastr.warning('Please select at least one overtime request.');
                    return;
                }

                // Show confirmation dialog
                if (confirm(`Are you sure you want to reject ${selectedIds.length} overtime request(s)?`)) {
                    processBulkAction('reject', selectedIds);
                }
            });

            // ✅ Bulk Delete Handler
            bulkDeleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const selectedIds = getSelectedOvertimeIds();

                if (selectedIds.length === 0) {
                    toastr.warning('Please select at least one overtime request.');
                    return;
                }

                // Show confirmation dialog with warning
                const confirmDelete = confirm(
                    ` WARNING: Are you sure you want to permanently delete ${selectedIds.length} overtime request(s)? This action cannot be undone.`
                );

                if (confirmDelete) {
                    processBulkAction('delete', selectedIds);
                }
            });

            // ✅ Process bulk action
            async function processBulkAction(action, overtimeIds) {
                const token = document.querySelector('meta[name="csrf-token"]').content;

                try {
                    // Show loading state
                    let actionBtn;
                    if (action === 'approve') {
                        actionBtn = bulkApproveBtn;
                    } else if (action === 'reject') {
                        actionBtn = bulkRejectBtn;
                    } else if (action === 'delete') {
                        actionBtn = bulkDeleteBtn;
                    }

                    if (actionBtn) {
                        const originalText = actionBtn.innerHTML;
                        actionBtn.innerHTML = '<i class="ti ti-loader ti-spin me-2"></i>Processing...';
                        actionBtn.style.pointerEvents = 'none';
                    }

                    const response = await fetch('/api/overtime/bulk-action', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            action: action,
                            overtime_ids: overtimeIds,
                            comment: `Bulk ${action}` // Default comment
                        })
                    });

                    const result = await response.json();

                    if (response.ok) {
                        toastr.success(result.message ||
                            `Successfully ${action}d ${overtimeIds.length} overtime request(s).`);

                        // Refresh the table
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        throw new Error(result.message || `Failed to ${action} overtime requests.`);
                    }
                } catch (error) {
                    console.error('Bulk action error:', error);
                    toastr.error(error.message || 'An error occurred while processing the bulk action.');
                } finally {
                    // Reset button state
                    let actionBtn;
                    if (action === 'approve') {
                        actionBtn = bulkApproveBtn;
                    } else if (action === 'reject') {
                        actionBtn = bulkRejectBtn;
                    } else if (action === 'delete') {
                        actionBtn = bulkDeleteBtn;
                    }

                    if (actionBtn) {
                        actionBtn.innerHTML = actionBtn.getAttribute('data-original-text') || actionBtn
                            .innerHTML.replace('<i class="ti ti-loader ti-spin me-2"></i>Processing...',
                                action === 'approve' ? 'Approve' : action === 'reject' ? 'Reject' : 'Delete');
                        actionBtn.style.pointerEvents = 'auto';
                    }
                }
            }

            // Initialize button state
            updateBulkActionButton();
        });
    </script>
@endpush
