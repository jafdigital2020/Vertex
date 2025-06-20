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
                    {{-- <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_leaves" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Leave</a>
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
                                    <h4>{{ $approvedLeavesCount }}</h4>
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
                                    <h4>{{ $rejectedLeavesCount }}</h4>
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
                                    <h4>{{ $pendingLeavesCount }}</h4>
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
                                class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                Leave Type
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Medical Leave</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Casual Leave</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Annual Leave</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
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
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>No of Days</th>
                                    <th>Status</th>
                                    <th>Next Approver</th>
                                    <th>Last Approved By</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leaveRequests as $lr)
                                    @php
                                        $status = strtolower($lr->status);
                                        $colors = [
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'pending' => 'primary',
                                        ];
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="javascript:void(0);"
                                                    class="avatar avatar-md border avatar-rounded">
                                                    <img src="{{ URL::asset('build/img/users/user-32.jpg') }}"
                                                        class="img-fluid" alt="img">
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
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <p class="fs-14 fw-medium d-flex align-items-center mb-0">
                                                    {{ $lr->leaveType->name }}</p>
                                                <a href="#" class="ms-2" data-bs-toggle="tooltip"
                                                    data-bs-placement="right" title="{{ $lr->reason }}">
                                                    <i class="ti ti-info-circle text-info"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($lr->start_date)->format('d M Y') }}
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($lr->end_date)->format('d M Y') }}
                                        </td>
                                        <td>
                                            {{ $lr->days_requested }}
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
                                                            data-action="CHANGES_REQUESTED"
                                                            data-leave-id="{{ $lr->id }}">
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
                                            @if (count($lr->next_approvers))
                                                {{ implode(', ', $lr->next_approvers) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="align-middle">
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
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#leave_admin_edit" data-id="{{ $lr->id }}"
                                                    data-leave-id="{{ $lr->leave_type_id }}"
                                                    data-start-date="{{ $lr->start_date }}"
                                                    data-end-date="{{ $lr->end_date }}"
                                                    data-half-day="{{ $lr->half_day_type }}"
                                                    data-reason="{{ $lr->reason }}"
                                                    data-current-step="{{ $lr->current_step }}"
                                                    data-status="{{ $lr->status }}"
                                                    data-remaining-balance="{{ $lr->remaining_balance }}"
                                                    data-file-attachment="{{ $lr->file_attachment }}"><i
                                                        class="ti ti-edit"></i></a>
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#leave_admin_delete" data-id="{{ $lr->id }}"
                                                    data-name="{{ $lr->user->personalInformation->first_name }} {{ $lr->user->personalInformation->last_name }}"><i
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
            <!-- /Leaves list -->

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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));

            // 1) Open modal for both Approve & Reject buttons
            document.querySelectorAll('.js-approve-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('modalLeaveId').value = btn.dataset.leaveId;
                    document.getElementById('modalAction').value = btn.dataset.action;
                    document.getElementById('modalComment').value = '';
                    document.getElementById('approvalModalLabel').textContent =
                        btn.dataset.action === 'APPROVED' ? 'Approve with comment' :
                        btn.dataset.action === 'REJECTED' ? 'Reject with comment' :
                        'Request Changes with comment';
                });
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

                } catch (err) {
                    console.error(err);
                    toastr.error(err.message);
                }
            });
        });
    </script>

    {{-- Edit Leave Request --}}
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
                    setTimeout(() => location.reload(), 1500);
                } catch (err) {
                    const msg = err.message ||
                        (err.errors && Object.values(err.errors)[0][0]) ||
                        'Update failed.';
                    toastr.error(msg);
                }
            });
        });
    </script>

    {{-- Delete Leave Request --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let deleteId = null;

            const deleteButtons = document.querySelectorAll('.btn-delete');
            const adminLeaveRequestConfirmBtn = document.getElementById('adminLeaveRequestConfirmBtn');
            const userAdminLeavePlaceHolder = document.getElementById('userAdminLeavePlaceHolder');

            // Set up the delete buttons to capture data
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    deleteId = this.getAttribute('data-id');
                    const userName = this.getAttribute('data-name');

                    if (userAdminLeavePlaceHolder) {
                        userAdminLeavePlaceHolder.textContent =
                            userName;
                    }
                });
            });

            // Confirm delete button click event
            adminLeaveRequestConfirmBtn?.addEventListener('click', function() {
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

                            setTimeout(() => window.location.reload(),
                                800); // Refresh the page after a short delay
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
@endpush
