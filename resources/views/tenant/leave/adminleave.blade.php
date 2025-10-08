<?php $page = 'leaves'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Leaves</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Leaves</li>
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
                    {{-- <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_leaves"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            Leave</a>
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

            <!-- Leaves Info -->
            <div class="row">
                <div class="col-xl-4 col-md-6">
                    <div class="card bg-pink-img">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <span
                                            class="avatar avatar-md rounded-circle bg-white d-flex align-items-center justify-content-center">
                                            <i class="ti ti-user-edit text-pink fs-18"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-1">Approved Leaves</p>
                                    <h4 id="approvedLeavesCount">{{ $approvedLeavesCount }}</h4>
                                    <small class="text-muted">This Month</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card bg-yellow-img">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <span
                                            class="avatar avatar-md rounded-circle bg-white d-flex align-items-center justify-content-center">
                                            <i class="ti ti-user-exclamation text-warning fs-18"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-1">Rejected Leaves</p>
                                    <h4 id="rejectedLeavesCount">{{ $rejectedLeavesCount }}</h4>
                                    <small class="text-muted">This Month</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card bg-blue-img">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <span
                                            class="avatar avatar-md rounded-circle bg-white d-flex align-items-center justify-content-center">
                                            <i class="ti ti-user-question text-info fs-18"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-1">Pending Requests</p>
                                    <h4 id="pendingLeavesCount">{{ $pendingLeavesCount }}</h4>
                                    <small class="text-muted">This Month</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Leaves Info -->

            <!-- Leaves list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Leave List</h5>

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
                        <div class="form-group me-2">
                            <select name="leavetype_filter" id="leavetype_filter" class="select2 form-select"
                                oninput="filter()">
                                <option value="" selected>All LeaveType</option>
                                @foreach ($leaveTypes as $leavetype)
                                    <option value="{{ $leavetype->id }}">{{ $leavetype->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select" oninput="filter()">
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
                        <table class="table datatable" id="adminLeaveTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Employee</th>
                                    <th class="text-center">Leave Type</th>
                                    <th class="text-center">From</th>
                                    <th class="text-center">To</th>
                                    <th class="text-center">No of Days</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Next Approver</th>
                                    <th class="text-center">Last Approved By</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="adminLeaveTableBody">
                                @foreach ($leaveRequests as $lr)
                                    @php
                                        $status = strtolower($lr->status);
                                        $colors = [
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'pending' => 'primary',
                                        ];
                                    @endphp

                                    <tr data-leave-id="{{ $lr->id }}">
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox" value="{{ $lr->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="javascript:void(0);" class="avatar avatar-md border avatar-rounded">
                                                    <img src="{{ URL::asset('build/img/users/user-32.jpg') }}" class="img-fluid"
                                                        alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a
                                                            href="javascript:void(0);">{{ $lr->user->personalInformation->last_name }},
                                                            {{ $lr->user->personalInformation->first_name }}</a>
                                                    </h6>
                                                    <span
                                                        class="fs-12 fw-normal ">{{ $lr->user->employmentDetail->department->department_name ?? 'No Department' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center">
                                                <p class="fs-14 fw-medium d-flex align-items-center mb-0">
                                                    {{ $lr->leaveType->name }}
                                                </p>
                                                <a href="#" class="ms-2" data-bs-toggle="tooltip" data-bs-placement="right"
                                                    title="{{ $lr->reason }}">
                                                    <i class="ti ti-info-circle text-info"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($lr->start_date)->format('d M Y') }}
                                        </td>
                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($lr->end_date)->format('d M Y') }}
                                        </td>
                                        <td class="text-center">
                                            {{ $lr->days_requested }}
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
                                                            data-action="APPROVED" data-leave-id="{{ $lr->id }}"
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
                                                            data-action="REJECTED" data-leave-id="{{ $lr->id }}"
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
                                                            data-action="CHANGES_REQUESTED" data-leave-id="{{ $lr->id }}">
                                                            <span
                                                                class="rounded-circle bg-transparent-{{ $colors['pending'] }} d-flex justify-content-center align-items-center me-2">
                                                                <i class="ti ti-point-filled text-{{ $colors['pending'] }}"></i>
                                                            </span>
                                                            Pending
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if (count($lr->next_approvers))
                                                {{ implode(', ', $lr->next_approvers) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="d-flex flex-column">
                                                {{-- 1) Approver name --}}
                                                <span class="fw-semibold">
                                                    {{ $lr->last_approver ?? '—' }}
                                                    <a href="#" data-bs-toggle="tooltip" data-bs-placement="right"
                                                        data-bs-title="{{ $lr->latestApproval->comment ?? 'No comment' }}">
                                                        <i class="ti ti-info-circle text-info"></i></a>
                                                </span>
                                                {{-- Approval date/time --}}
                                                @if ($lr->latestApproval)
                                                    <small class="text-muted mt-1">
                                                        {{ \Carbon\Carbon::parse($lr->latestApproval->acted_at)->format('d M Y, h:i A') }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                @if (in_array('Update', $permission))
                                                    <a href="#" class="me-2" data-bs-toggle="modal"
                                                        data-bs-target="#leave_admin_edit" data-id="{{ $lr->id }}"
                                                        data-leave-id="{{ $lr->leave_type_id }}"
                                                        data-start-date="{{ $lr->start_date }}" data-end-date="{{ $lr->end_date }}"
                                                        data-half-day="{{ $lr->half_day_type }}" data-reason="{{ $lr->reason }}"
                                                        data-current-step="{{ $lr->current_step }}" data-status="{{ $lr->status }}"
                                                        data-remaining-balance="{{ $lr->remaining_balance }}"
                                                        data-file-attachment="{{ $lr->file_attachment }}"><i
                                                            class="ti ti-edit"></i></a>
                                                @endif
                                                @if (in_array('Delete', $permission))
                                                    <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                        data-bs-target="#leave_admin_delete" data-id="{{ $lr->id }}"
                                                        data-name="{{ $lr->user->personalInformation->first_name }} {{ $lr->user->personalInformation->last_name }}"><i
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
            <!-- /Leaves list -->

            <!-- Approval Comment Modal -->
            <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="approvalForm">
                            <div class="modal-header">
                                <h5 class="modal-title" id="approvalModalLabel">Add Approval Comment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="modalLeaveId">
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

        </div>
        @include('layout.partials.footer-company')
    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup', [
        'leaveTypes' => $leaveTypes,
    ])
    @endcomponent
@endsection

@push('scripts')
    <!-- Date Range Picker JS -->
    <script>
        if ($('.bookingrange-filtered').length > 0) {
            var start = moment().startOf('year');
            var end = moment().endOf('year');

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

        $('#dateRange_filter').on('apply.daterangepicker', function (ev, picker) {
            filter();
        });


        function filter() {
            const dateRange = $('#dateRange_filter').val();
            const status = $('#status_filter').val();
            const leavetype = $('#leaveType_filter').val();
            $.ajax({
                url: '{{ route('leave-admin-filter') }}',
                type: 'GET',
                data: {
                    dateRange,
                    status,
                    leavetype
                },
                success: function (response) {
                    if (response.status === 'success') {
                        $('#adminLeaveTable').DataTable().destroy();
                        $('#adminLeaveTableBody').html(response.html);
                        $('#adminLeaveTable').DataTable();
                        $('#pendingLeavesCount').text(response.pendingLeavesCount);
                        $('#approvedLeavesCount').text(response.approvedLeavesCount);
                        $('#rejectedLeavesCount').text(response.rejectedLeavesCount);
                    } else {
                        toastr.error(response.message || 'Something went wrong.');
                    }
                },
                error: function (xhr) {
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

    <!-- Approve/Reject Leave Request -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));

            // 1) Open modal for both Approve & Reject buttons
            document.addEventListener('click', function (event) {
                if (event.target.closest('.js-approve-btn')) {
                    const btn = event.target.closest('.js-approve-btn');
                    document.getElementById('modalLeaveId').value = btn.dataset.leaveId;
                    document.getElementById('modalAction').value = btn.dataset.action;
                    document.getElementById('modalComment').value = '';
                    document.getElementById('approvalModalLabel').textContent =
                        btn.dataset.action === 'APPROVED' ? 'Approve with comment' :
                            btn.dataset.action === 'REJECTED' ? 'Reject with comment' :
                                'Request Changes with comment';
                }
            });

            // 2) Submit the modal form for both Approve & Reject
            document.getElementById('approvalForm').addEventListener('submit', async e => {
                e.preventDefault();

                const leaveId = document.getElementById('modalLeaveId').value;
                const action = document.getElementById('modalAction').value;
                const comment = document.getElementById('modalComment').value.trim();
                const url = action === 'REJECTED' ?
                    `/api/leave/leave-request/${leaveId}/reject` :
                    `/api/leave/leave-request/${leaveId}/approve`;

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

    <!-- Edit Leave Request -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const leaveTypes = window.availableLeaveTypes || {};

            const editModal = document.getElementById('leave_admin_edit');
            const form = document.getElementById('adminEditRequestLeaveForm');
            const hiddenId = document.getElementById('adminEditLeaveRequestId');
            const leaveTypeSelect = document.getElementById('adminEditLeaveType');
            const startInput = document.getElementById('adminEditLeaveRequestStartDate');
            const endInput = document.getElementById('adminEditLeaveRequestEndDate');
            const halfDayBlock = document.getElementById('adminEditHalfDayBlock');
            const halfDayType = document.getElementById('adminEditHalfDayType');
            const daysInp = document.getElementById('adminEditDaysRequested');
            const remInp = document.getElementById('adminEditCurrentBalance');
            const reasonInput = document.getElementById('adminEditLeaveRequestReason');
            const fileInput = document.getElementById('adminEditLeaveRequestFileAttachment');
            const fileAttachmentBlock = document.getElementById(
                'adminEditDocumentBlock'); // To show the file attachment info
            const fileAttachmentLink = document.createElement('a'); // To create a link for the file

            // When the modal opens, pull data-* attrs from the clicked button
            editModal.addEventListener('show.bs.modal', event => {
                const btn = event.relatedTarget; // Button that triggered the modal
                const status = btn.dataset.status;
                const currentStep = parseInt(btn.dataset.currentStep, 10);

                // Populate form fields with other data from the button
                hiddenId.value = btn.dataset.id;
                leaveTypeSelect.value = btn.dataset.leaveId;
                leaveTypeSelect.dispatchEvent(new Event('change'));
                startInput.value = btn.dataset.startDate;
                endInput.value = btn.dataset.endDate;
                halfDayType.value = btn.dataset.halfDay || '';
                reasonInput.value = btn.dataset.reason;

                // Get and log the remaining balance
                const remainingBalance = btn.dataset.remainingBalance;

                if (remainingBalance) {
                    remInp.value = remainingBalance;
                } else {
                    remInp.value = '0';
                }

                // Handle file attachment
                const fileAttachment = btn.dataset
                    .fileAttachment;

                // Create the file URL
                const fileUrl = '/storage/' +
                    fileAttachment;

                // Check if file exists and display it
                if (fileAttachment && fileAttachment !== 'null') {
                    fileAttachmentLink.href = fileUrl;
                    fileAttachmentLink.textContent = 'View Current Attachment';
                    fileAttachmentLink.target = '_blank';

                    // Add the link to the file attachment block
                    fileAttachmentBlock.appendChild(fileAttachmentLink);
                    fileAttachmentBlock.style.display = 'block'; // Make the file attachment visible
                } else {
                    fileAttachmentBlock.style.display = 'none'; // Hide if no file
                }

                const editable = status === 'pending' && currentStep === 1;
                form.querySelectorAll('input, select, textarea').forEach(el => {
                    el.disabled = !editable;
                });
                document.getElementById('adminEditLeaveRequestUpdateBtn').style.display = editable ? '' :
                    'none';

                // Update UI and calculate the number of days
                updateUI();
                calculateDays();
            });

            // Helper function to get the leave setting for the selected leave type
            function getSetting() {
                let raw = (leaveTypes[leaveTypeSelect.value]?.leave_setting) ||
                    (leaveTypes[leaveTypeSelect.value]?.leaveSetting) || {};
                if (Array.isArray(raw)) raw = raw[0];
                return raw || {};
            }

            // Update UI based on leave type settings
            function updateUI() {
                const cfg = getSetting();

                // Show half-day option only if allowed and same day
                const same = startInput.value && endInput.value && startInput.value === endInput.value;
                if (cfg.allow_half_day && same) {
                    halfDayBlock.style.display = 'block';
                } else {
                    halfDayBlock.style.display = 'none';
                    halfDayType.value = '';
                }

                // Document required?
                fileInput.required = !!cfg.require_documents;
            }

            // Calculate the number of days based on start and end date
            function calculateDays() {
                const f = startInput.value,
                    t = endInput.value;
                if (!f || !t) {
                    daysInp.value = '';
                    return;
                }

                const from = new Date(f),
                    to = new Date(t);
                if (isNaN(from) || isNaN(to) || to < from) {
                    daysInp.value = '';
                    return;
                }

                let span = Math.floor((to - from) / (1000 * 60 * 60 * 24)) + 1;
                let total = span;

                if (halfDayBlock.style.display === 'block') {
                    if (halfDayType.value === 'AM' || halfDayType.value === 'PM') {
                        total = 0.5;
                    } else {
                        total = 1;
                    }
                }

                daysInp.value = total;
            }

            // Recalculate the number of days when any relevant input changes
            leaveTypeSelect.addEventListener('change', () => {
                updateUI();
                calculateDays();
            });
            ['input', 'change'].forEach(evt => {
                startInput.addEventListener(evt, () => {
                    updateUI();
                    calculateDays();
                });
                endInput.addEventListener(evt, () => {
                    updateUI();
                    calculateDays();
                });
            });
            halfDayType.addEventListener('change', calculateDays);

            // Submit the edit
            form.addEventListener('submit', async e => {
                e.preventDefault();
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const id = hiddenId.value;

                const fd = new FormData();
                fd.append('leave_type_id', leaveTypeSelect.value);
                fd.append('start_date', startInput.value);
                fd.append('end_date', endInput.value);
                fd.append('days_requested', daysInp.value);
                if (halfDayType.value) fd.append('half_day_type', halfDayType.value);
                if (fileInput.files[0]) fd.append('file_attachment', fileInput.files[0]);
                fd.append('reason', reasonInput.value);

                try {
                    const res = await fetch(`/api/leave/leave-admin/update/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: fd
                    });
                    const body = await res.json();
                    if (!res.ok) throw body;
                    toastr.success(body.message);
                    filter();
                } catch (err) {
                    const msg = err.message ||
                        (err.errors && Object.values(err.errors)[0][0]) ||
                        'Update failed.';
                    toastr.error(msg);
                }
            });
        });
    </script>

    <!-- Delete Leave Request -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let deleteId = null;

            const deleteButtons = document.querySelectorAll('.btn-delete');
            const adminLeaveRequestConfirmBtn = document.getElementById('adminLeaveRequestConfirmBtn');
            const userAdminLeavePlaceHolder = document.getElementById('userAdminLeavePlaceHolder');

            // Set up the delete buttons to capture data

            $(document).on('click', '.btn-delete', function () {
                deleteId = $(this).data('id');
                const userName = $(this).data('data-name');

                if (userAdminLeavePlaceHolder) {
                    userAdminLeavePlaceHolder.textContent =
                        userName;
                }
            });

            // Confirm delete button click event
            adminLeaveRequestConfirmBtn?.addEventListener('click', function () {
                if (!deleteId) return; // Ensure both deleteId and userId are available

                fetch(`/api/leave/leave-admin/delete/${deleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content"),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${authToken}`,
                    },
                })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Leave request deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'leave_admin_delete'));
                            deleteModal.hide(); // Hide the modal
                            filter();
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting leave request.");
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

    <!-- Bulk Actions Buttons -->
    <script>
        // Bulk Actions Implementation
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheckbox = document.getElementById('select-all');
            const bulkApproveBtn = document.getElementById('bulkApprove');
            const bulkRejectBtn = document.getElementById('bulkReject');
            const bulkActionsDropdown = document.getElementById('bulkActionsDropdown');

            // ✅ Select All / Deselect All functionality
            selectAllCheckbox.addEventListener('change', function () {
                const isChecked = this.checked;
                const rowCheckboxes = document.querySelectorAll(
                    '#adminLeaveTableBody input[type="checkbox"]');

                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });

                updateBulkActionButton();
            });

            // ✅ Individual checkbox change handler
            document.addEventListener('change', function (e) {
                if (e.target.type === 'checkbox' && e.target.closest('#adminLeaveTableBody')) {
                    updateSelectAllState();
                    updateBulkActionButton();
                }
            });

            // ✅ Update "Select All" checkbox state
            function updateSelectAllState() {
                const rowCheckboxes = document.querySelectorAll('#adminLeaveTableBody input[type="checkbox"]');
                const checkedBoxes = document.querySelectorAll(
                    '#adminLeaveTableBody input[type="checkbox"]:checked');

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
                    '#adminLeaveTableBody input[type="checkbox"]:checked');
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

            // ✅ Get selected leave IDs
            function getSelectedLeaveIds() {
                const checkedBoxes = document.querySelectorAll(
                    '#adminLeaveTableBody input[type="checkbox"]:checked');
                const leaveIds = [];

                checkedBoxes.forEach(checkbox => {
                    const row = checkbox.closest('tr');
                    const leaveId = row.dataset.leaveId; // We'll add this data attribute to each row
                    if (leaveId) {
                        leaveIds.push(leaveId);
                    }
                });

                return leaveIds;
            }

            // ✅ Bulk Approve Handler
            bulkApproveBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const selectedIds = getSelectedLeaveIds();

                if (selectedIds.length === 0) {
                    toastr.warning('Please select at least one leave request.');
                    return;
                }

                // Show confirmation dialog
                if (confirm(`Are you sure you want to approve ${selectedIds.length} leave request(s)?`)) {
                    processBulkAction('approve', selectedIds);
                }
            });

            // ✅ Bulk Reject Handler
            bulkRejectBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const selectedIds = getSelectedLeaveIds();

                if (selectedIds.length === 0) {
                    toastr.warning('Please select at least one leave request.');
                    return;
                }

                // Show confirmation dialog
                if (confirm(`Are you sure you want to reject ${selectedIds.length} leave request(s)?`)) {
                    processBulkAction('reject', selectedIds);
                }
            });

            // ✅ Process bulk action
            async function processBulkAction(action, leaveIds) {
                const token = document.querySelector('meta[name="csrf-token"]').content;

                try {
                    // Show loading state
                    const actionBtn = action === 'approve' ? bulkApproveBtn : bulkRejectBtn;
                    const originalText = actionBtn.innerHTML;
                    actionBtn.innerHTML = '<i class="ti ti-loader ti-spin me-2"></i>Processing...';
                    actionBtn.style.pointerEvents = 'none';

                    const response = await fetch('/api/leave/bulk-action', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            action: action,
                            leave_ids: leaveIds,
                            comment: `Bulk ${action} by admin` // Default comment
                        })
                    });

                    const result = await response.json();

                    if (response.ok) {
                        toastr.success(result.message ||
                            `Successfully ${action}d ${leaveIds.length} leave request(s).`);

                        // Refresh the table
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        throw new Error(result.message || `Failed to ${action} leave requests.`);
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