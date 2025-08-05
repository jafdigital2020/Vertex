<?php $page = 'leaves-employee'; ?>
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
                    @if(in_array('Export',$permission))
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
                    @if(in_array('Create',$permission))
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#request_leave"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Request
                            Leave</a>
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

            <!-- Leaves Info -->
            <div class="row">
                @foreach ($leaveTypes as $lt)
                    @php
                        $colors = ['black', 'blue', 'pink', 'purple'];
                        $color = $colors[$loop->index % count($colors)];
                        $bgcolors = [
                            'badge-secondary-transparent',
                            'bg-info-transparent',
                            'bg-purple-transparent',
                            'bg-pink-transparent',
                        ];
                        $bgcolor = $bgcolors[$loop->index % count($bgcolors)];
                    @endphp

                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-{{ $color }}-le">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="text-start">
                                        <p class="mb-1">{{ $lt->name }}s</p>
                                        <h4>{{ $lt->current_balance }}</h4>
                                    </div>
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-2">
                                            <span class="avatar avatar-md d-flex">
                                                <i class="ti ti-calendar-event fs-32"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <span class="badge {{ $bgcolor }}">
                                    Remaining Leaves: {{ $lt->current_balance }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- /Leaves Info -->

            <!-- Leaves list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <div class="d-flex">
                        <h5 class="me-2">Leave List</h5>
                    </div>
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
                             <select name="leavetype_filter" id="leavetype_filter" class="select2 form-select" oninput="filter()">
                                <option value="" selected>All LeaveType</option>
                                @foreach ($leaveTypes as $leavetype)
                                    <option value="{{$leavetype->id}}">{{$leavetype->name}}</option>
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
                        <table class="table datatable" id="employeeLeaveTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Leave Type</th>
                                    <th class="text-center">From</th>
                                    <th class="text-center">To</th>
                                    <th class="text-center">Approved By</th>
                                    <th class="text-center">No of Days</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="employeeLeaveTableBody">
                                @foreach ($leaveRequests as $lr)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <p class="fs-14 fw-medium d-flex align-items-center mb-0">
                                                    {{ $lr->leaveType->name }}</p>
                                                <a href="#" class="ms-2" data-bs-toggle="tooltip"
                                                    data-bs-placement="right"
                                                    data-bs-title="{{ $lr->reason ?? 'No reason provided' }}">
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
                                            @if ($lr->lastApproverName)
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);"
                                                        class="avatar avatar-md border avatar-rounded">
                                                        <img src="{{ asset('storage/' . $lr->latestApproval->approver->personalInformation->profile_picture) }}"
                                                            class="img-fluid" alt="avatar">
                                                    </a>
                                                    <div class="ms-2">
                                                        <h6 class="fw-medium mb-0">
                                                            {{ $lr->lastApproverName }}
                                                        </h6>
                                                        <span class="fs-12 fw-normal">
                                                            {{ $lr->lastApproverDept }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                       <td class="text-center">
                                            {{ $lr->days_requested }}
                                        </td>
                                       <td class="text-center">
                                            <div class="dropdown">
                                                <a href="javascript:void(0);"
                                                    class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
                                                    data-bs-toggle="dropdown">
                                                    @php
                                                        $status = strtolower($lr->status);
                                                        switch ($status) {
                                                            case 'approved':
                                                                $color = 'success';
                                                                break;
                                                            case 'pending':
                                                                $color = 'primary';
                                                                break;
                                                            case 'rejected':
                                                                $color = 'danger';
                                                                break;
                                                            default:
                                                                $color = 'secondary';
                                                        }
                                                    @endphp
                                                    <span
                                                        class="rounded-circle bg-transparent-{{ $color }} d-flex justify-content-center align-items-center me-2">
                                                        <i class="ti ti-point-filled text-{{ $color }}"></i>
                                                    </span>
                                                    {{ Str::ucfirst($status) }}
                                                </a>
                                            </div>
                                        </td>
                                       <td class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_request_leave" data-id="{{ $lr->id }}"
                                                    data-leave-id="{{ $lr->leave_type_id }}"
                                                    data-start-date="{{ $lr->start_date }}"
                                                    data-end-date="{{ $lr->end_date }}"
                                                    data-half-day="{{ $lr->half_day_type }}"
                                                    data-reason="{{ $lr->reason }}"
                                                    data-current-step="{{ $lr->current_step }}"
                                                    data-status="{{ $lr->status }}"><i class="ti ti-edit"></i></a>
                                                <a href="javascript:void(0);" data-bs-toggle="modal" class="btn-delete"
                                                    data-bs-target="#delete_request_leave" data-id="{{ $lr->id }}"
                                                    data-leave-name="{{ $lr->leaveType->name }}"><i
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
        // keyBy('id') so we can do availableLeaveTypes[<id>]
        window.availableLeaveTypes = {!! $leaveTypes->keyBy('id')->toJson() !!};
        console.log('📦 availableLeaveTypes:', window.availableLeaveTypes);
    </script>

   <script>
    $('#dateRange_filter').on('apply.daterangepicker', function(ev, picker) {
        filter();
    });

    function filter() {
        const dateRange = $('#dateRange_filter').val();
        const status = $('#status_filter').val();
        const leavetype = $('#leaveType_filter').val();
        $.ajax({
            url: '{{ route('leave-employees-filter') }}',
            type: 'GET',
            data: {
                dateRange,
                status,
                leavetype
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#employeeLeaveTable').DataTable().destroy();  
                    $('#employeeLeaveTableBody').html(response.html);
                    $('#employeeLeaveTable').DataTable(); 
                   
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const leaveTypes = window.availableLeaveTypes || {};
            const leaveTypeSelect = document.getElementById('leaveTypeId');
            const startInput = document.getElementById('leaveRequestStartDate');
            const endInput = document.getElementById('leaveRequestEndDate');
            const halfDayBlock = document.getElementById('halfDayBlock');
            const halfDayType = document.getElementById('halfDayType');
            const daysInp = document.getElementById('daysRequested');
            const remInp = document.getElementById('currentBalance');
            const fileInput = document.getElementById('leaveRequestFileAttachment');

            // Unwrap the single-element leave_setting array into an object
            function getSettingCfg() {
                const lt = leaveTypes[leaveTypeSelect.value] || {};
                let raw = lt.leave_setting ?? lt.leaveSetting;
                if (Array.isArray(raw)) raw = raw[0];
                return raw || {};
            }

            // Update the Remaining Days & the half-day and document UI
            function updateUI() {
                const cfg = getSettingCfg();
                const bal = leaveTypes[leaveTypeSelect.value]?.current_balance;
                remInp.value = bal != null ? bal : '';

                // half-day logic
                const from = startInput.value,
                    to = endInput.value;
                const same = from && to && from === to;
                if (cfg.allow_half_day && same) {
                    halfDayBlock.style.display = 'block';
                } else {
                    halfDayBlock.style.display = 'none';
                    halfDayType.value = '';
                }

                // supporting docs requirement
                fileInput.required = Boolean(cfg.require_documents);
            }

            // Calculate and display request_days
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

                const span = Math.floor((to - from) / (1000 * 60 * 60 * 24)) + 1;
                let total = span;

                if (halfDayBlock.style.display === 'block') {
                    if (halfDayType.value === 'AM' || halfDayType.value === 'PM') {
                        total = 0.5;
                    } else {
                        total = 1; // Full Day option
                    }
                }

                daysInp.value = total;
            }

            // Whenever you change the leave type: update remaining AND recalc days
            leaveTypeSelect.addEventListener('change', () => {
                updateUI();
                calculateDays();
            });

            // Date changes also re-run both
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

            // Changing half-day only affects daysRequested
            halfDayType.addEventListener('change', calculateDays);

            // Initial run in case fields are pre-filled
            updateUI();
            calculateDays();
        });
    </script>

    {{-- Form Handling Submission Request --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('requestLeaveForm');
            const leaveTypeSelect = document.getElementById('leaveTypeId');
            const startInput = document.getElementById('leaveRequestStartDate');
            const endInput = document.getElementById('leaveRequestEndDate');
            const halfDayType = document.getElementById('halfDayType');
            const daysInp = document.getElementById('daysRequested');
            const remInp = document.getElementById('currentBalance');
            const fileInput = document.getElementById('leaveRequestFileAttachment');
            const reasonInput = document.getElementById('leaveRequestReason');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const token = document.querySelector('meta[name="csrf-token"]').content;

                // Build FormData
                const fd = new FormData();
                fd.append('leave_type_id', leaveTypeSelect.value);
                fd.append('start_date', startInput.value);
                fd.append('end_date', endInput.value);
                fd.append('days_requested', daysInp.value);
                if (halfDayType.value) {
                    fd.append('half_day_type', halfDayType.value);
                }
                if (fileInput.files.length) {
                    fd.append('file_attachment', fileInput.files[0]);
                }
                fd.append('reason', reasonInput.value);

                try {
                    const res = await fetch('/api/leave/leave-request', {
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
                    $("#request_leave").modal('hide');
                    filter();
                } catch (err) {
                    const msg = err.message ||
                        (err.errors && Object.values(err.errors)[0][0]) ||
                        'Submission failed.';
                    toastr.error(msg);
                }
            });
        });
    </script>

    {{-- Form Handling Submission Edit Request --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const leaveTypes = window.availableLeaveTypes || {};

            const editModal = document.getElementById('edit_request_leave');
            const form = document.getElementById('editRequestLeaveForm');
            const hiddenId = document.getElementById('editLeaveRequestId');
            const leaveTypeSelect = document.getElementById('editLeaveTypeId');
            const startInput = document.getElementById('editLeaveRequestStartDate');
            const endInput = document.getElementById('editLeaveRequestEndDate');
            const halfDayBlock = document.getElementById('editHalfDayBlock');
            const halfDayType = document.getElementById('editHalfDayType');
            const daysInp = document.getElementById('editDaysRequested');
            const remInp = document.getElementById('editCurrentBalance');
            const reasonInput = document.getElementById('editLeaveRequestReason');
            const fileInput = document.getElementById('editLeaveRequestFileAttachment');

            // when the modal opens, pull data-* attrs from the clicked button
            editModal.addEventListener('show.bs.modal', event => {
                const btn = event.relatedTarget;
                const status = btn.dataset.status;
                const currentStep = parseInt(btn.dataset.currentStep, 10);

                hiddenId.value = btn.dataset.id;
                leaveTypeSelect.value = btn.dataset.leaveId;
                leaveTypeSelect.dispatchEvent(new Event('change'));
                startInput.value = btn.dataset.startDate;
                endInput.value = btn.dataset.endDate;
                halfDayType.value = btn.dataset.halfDay || '';
                reasonInput.value = btn.dataset.reason;

                const editable = status === 'pending' && currentStep === 1;
                form.querySelectorAll('input, select, textarea').forEach(el => {
                    el.disabled = !editable;
                });
                document
                    .getElementById('editLeaveRequestUpdateBtn')
                    .style.display = editable ? '' : 'none';

                updateUI();
                calculateDays();
            });

            function getSetting() {
                let raw = (leaveTypes[leaveTypeSelect.value]?.leave_setting) ||
                    (leaveTypes[leaveTypeSelect.value]?.leaveSetting) || {};
                if (Array.isArray(raw)) raw = raw[0];
                return raw || {};
            }

            function updateUI() {
                const cfg = getSetting();
                const bal = leaveTypes[leaveTypeSelect.value]?.current_balance;
                remInp.value = bal != null ? bal : '';

                // half-day only if same day & allowed
                const same = startInput.value && endInput.value &&
                    startInput.value === endInput.value;
                if (cfg.allow_half_day && same) {
                    halfDayBlock.style.display = 'block';
                } else {
                    halfDayBlock.style.display = 'none';
                    halfDayType.value = '';
                }

                // document required?
                fileInput.required = !!cfg.require_documents;
            }

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

            // re-calculate on any change
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

            // submit the edit
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
                    const res = await fetch(`/api/leave/leave-request/${id}`, {
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
                    $("#edit_request_leave").modal('hide');
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

    {{-- Form Handling Submission Delete Request --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let deleteId = null;

            const deleteButtons = document.querySelectorAll('.btn-delete');
            const leaveRequestConfirmDeleteBtn = document.getElementById('leaveRequestConfirmDeleteBtn');
            const leaveTypeNamePlaceHolder = document.getElementById('leaveTypeNamePlaceHolder');


            $(document).on('click', '.btn-delete', function () {
                deleteId = $(this).data('id');
                const leaveTypeName = $(this).data('leave-name');

                if (leaveTypeNamePlaceHolder) {
                    leaveTypeNamePlaceHolder.textContent = leaveTypeName;
                }
            });

            // Confirm delete button click event
            leaveRequestConfirmDeleteBtn?.addEventListener('click', function() {
                if (!deleteId) return; // Ensure both deleteId and userId are available

                fetch(`/api/leave/leave-request/delete/${deleteId}`, {
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
                                'delete_request_leave'));
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
@endpush
