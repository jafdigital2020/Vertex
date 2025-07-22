<?php $page = 'leave-type'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Settings</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Settings</li>
                        </ol>
                    </nav>
                </div>
                <div class="head-icons ms-2">
                    <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-original-title="Collapse" id="collapse-header">
                        <i class="ti ti-chevrons-up"></i>
                    </a>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <ul class="nav nav-tabs nav-tabs-solid bg-transparent border-bottom mb-3">
                {{-- <li class="nav-item">
                    <a class="nav-link " href="{{ url('profile-settings') }}"><i class="ti ti-settings me-2"></i>General
                        Settings</a>
                </li> --}}
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ url('bussiness-settings') }}"><i class="ti ti-world-cog me-2"></i>Website
                        Settings</a>
                </li> --}}
                <li class="nav-item">
                    <a class="nav-link active" href="{{ url('salary-settings') }}"><i
                            class="ti ti-device-ipad-horizontal-cog me-2"></i>App Settings</a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ url('email-settings') }}"><i class="ti ti-server-cog me-2"></i>System
                        Settings</a>
                </li> --}}
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ url('payment-gateways') }}"><i
                            class="ti ti-settings-dollar me-2"></i>Financial Settings</a>
                </li> --}}
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ url('custom-css') }}"><i class="ti ti-settings-2 me-2"></i>Other
                        Settings</a>
                </li> --}}
            </ul>
            <div class="row">
                <div class="col-xl-3 theiaStickySidebar">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column list-group settings-list">
                                <a href="{{ route('attendance-settings') }}"
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Attendance Settings</a>
                                <a href="{{ route('approval-steps') }}"
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Approval Settings</a>
                                <a href="{{ route('leave-type') }}"
                                    class="d-inline-flex align-items-center rounded active py-2 px-3">Leave Type</a>
                                <a href="{{ route('custom-fields') }}"
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Custom Fields</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="card">
                        <div class="card-body">
                            <div class="border-bottom d-flex align-items-center justify-content-between pb-3 mb-3">
                                <h4>Leave Type</h4>
                                @if(in_array('Update',$permission))
                                <div>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#add_leaveType"
                                        class="btn btn-primary d-flex align-items-center"><i
                                            class="ti ti-circle-plus me-2"></i>Add Leave Type</a>
                                </div>
                                @endif
                            </div>
                            <div class="card-body p-0">
                                <div class="card mb-0">
                                    <div class="card-header d-flex align-items-center justify-content-between">
                                        <h6>Leave Type List</h6>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="no-sort">
                                                        <div class="form-check form-check-md">
                                                            <input class="form-check-input" type="checkbox" id="select-all">
                                                        </div>
                                                    </th>
                                                    <th>Leave Type</th>
                                                    <th class="text-center">Leave Days(Entitle)</th>
                                                    <th class="text-center">Payment</th>
                                                    <th class="text-center">Status</th>
                                                    @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                                    <th class="text-center">Action</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($leaveTypes as $leaveType)
                                                    @php
                                                        $statusClass =
                                                            $leaveType->status === 'active'
                                                                ? 'badge-success'
                                                                : 'badge-warning';
                                                        $statusLabel = ucfirst($leaveType->status);

                                                        $paidClass = $leaveType->is_paid
                                                            ? 'badge-success'
                                                            : 'badge-secondary';
                                                        $paidLabel = $leaveType->is_paid ? 'Paid' : 'Unpaid';
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="form-check form-check-md">
                                                                <input class="form-check-input" type="checkbox">
                                                            </div>
                                                        </td>
                                                        <td class="text-dark">{{ $leaveType->name ?? 'N/A' }}</td>
                                                        <td class="text-center">{{ $leaveType->default_entitle }}</td>
                                                        <td class="text-center"> <span class="badge {{ $paidClass }}">
                                                                <i class="ti ti-point-filled"></i> {{ $paidLabel }}
                                                            </span></td>
                                                        <td class="text-center">
                                                            <span class="badge {{ $statusClass }}">
                                                                <i class="ti ti-point-filled"></i> {{ $statusLabel }}
                                                            </span>
                                                        </td>
                                                        @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                                        <td class="text-center">
                                                            <div class="action-icon d-inline-flex">
                                                                 @if(in_array('Update',$permission))
                                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                                    data-bs-target="#edit_leaveType"
                                                                    data-id="{{ $leaveType->id }}"
                                                                    data-name="{{ $leaveType->name }}"
                                                                    data-default-entitle="{{ $leaveType->default_entitle }}"
                                                                    data-accrual-frequency="{{ $leaveType->accrual_frequency }}"
                                                                    data-max-carryover="{{ $leaveType->max_carryover }}"
                                                                    data-is-paid="{{ $leaveType->is_paid ? '1' : '0' }}"
                                                                    data-is-earned="{{ $leaveType->is_earned ? '1' : '0' }}"
                                                                    data-earned-rate="{{ $leaveType->earned_rate }}"
                                                                    data-earned-interval="{{ $leaveType->earned_interval }}"
                                                                    data-is-cash-convertible="{{ $leaveType->is_cash_convertible ? '1' : '0' }}"
                                                                    data-conversion-rate="{{ $leaveType->conversion_rate }}"><i
                                                                        class="ti ti-edit"></i></a>
                                                                 @endif
                                                                @if(in_array('Delete',$permission))
                                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                                    data-bs-target="#delete_leaveType"
                                                                    data-id="{{ $leaveType->id }}"
                                                                    data-name="{{ $leaveType->name }}"><i
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
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('layout.partials.footer-company')
    </div>
    <!-- /Page Wrapper -->
    @component('components.modal-popup', [
        'leaveTypes' => $leaveTypes,
    ])
    @endcomponent
@endsection


@push('scripts')
    {{-- Form Handling Store/Create --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const authToken = localStorage.getItem("token");
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const form = document.getElementById('addLeaveTypeForm');
            const modal = new bootstrap.Modal(document.getElementById('add_leaveType'));

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const isEarned = form.querySelector('#leaveTypeIsEarned').checked ? 1 : 0;
                const isCashConvertible = form.querySelector('#leaveTypeIsCashConvertible').checked ?
                    1 : 0;
                const conversionRate = form.querySelector('#conversionRate').value.trim();

                const payload = {
                    name: form.name.value.trim(),
                    is_earned: isEarned,
                    default_entitle: form.default_entitle.value.trim(),
                    is_paid: form.is_paid.value === '1' ? 1 : 0,
                    is_cash_convertible: isCashConvertible,
                    conversion_rate: isCashConvertible ? parseFloat(conversionRate) || 0 : 0,
                };

                if (isEarned) {
                    payload.earned_rate = parseFloat(form.earned_rate.value);
                    payload.earned_interval = form.earned_interval.value;
                    payload.accrual_frequency = 'NONE';
                    payload.max_carryover = 0;
                } else {
                    payload.accrual_frequency = form.accrual_frequency.value;
                    payload.max_carryover = parseFloat(form.max_carryover.value);
                    payload.earned_rate = null;
                    payload.earned_interval = null;
                }

                try {
                    const res = await fetch('/api/settings/leave-type/create', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Authorization': `Bearer ${authToken}`
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();
                    if (res.ok) {
                        toastr.success('Leave type added successfully!');
                        form.reset();
                        modal.hide();
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        const errs = data.errors || {
                            general: [data.message]
                        };
                        Object.values(errs).flat().forEach(msg => toastr.error(msg));
                    }
                } catch (err) {
                    console.error(err);
                    toastr.error('Failed to save. Please try again.');
                }
            });
        });
    </script>

    {{-- Edit Leave Type --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const authToken = localStorage.getItem("token");
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            // Elements
            const editForm = document.getElementById("editLeaveTypeForm");
            const modalEl = document.getElementById("edit_leaveType");
            const modal = new bootstrap.Modal(modalEl);
            const chkEarned = document.getElementById("editLeaveTypeIsEarned");
            const earnedSection = document.getElementById("editEarnedFields");
            const globalSection = document.getElementById("editGlobalFields");
            const btnUpdate = document.getElementById("updateLeaveTypeBtn");
            const chkCashConvertible = document.getElementById("editLeaveTypeIsCashConvertible");
            const inputConversionRate = document.getElementById("editConversionRate");

            // Toggle function
            function toggleEditSections() {
                if (chkEarned.checked) {
                    earnedSection.style.display = "";
                    globalSection.style.display = "none";
                } else {
                    earnedSection.style.display = "none";
                    globalSection.style.display = "";
                }
            }

            // 1️⃣ Populate fields when “Edit” button opens modal
            document.querySelectorAll('[data-bs-target="#edit_leaveType"]').forEach(btn => {
                btn.addEventListener("click", () => {
                    const id = btn.getAttribute("data-id");
                    const name = btn.dataset.name;
                    const isPaid = btn.dataset.isPaid === "1";
                    const isEarn = btn.dataset.isEarned === "1";
                    const isCashConvertible = btn.dataset.isCashConvertible === "1";

                    // common
                    editForm.leave_type_id.value = id;
                    editForm.name.value = name;
                    editForm.is_paid.value = isPaid ? "1" : "0";
                    editForm.default_entitle.value = btn.dataset.defaultEntitle;
                    chkEarned.checked = isEarn;

                    // earned vs global
                    if (isEarn) {
                        editForm.earned_rate.value = btn.dataset.earnedRate;
                        editForm.earned_interval.value = btn.dataset.earnedInterval;
                    } else {
                        editForm.accrual_frequency.value = btn.dataset.accrualFrequency;
                        editForm.max_carryover.value = btn.dataset.maxCarryover;
                    }

                    chkCashConvertible.checked = btn.dataset.isCashConvertible === "1" || btn
                        .dataset.isCashConvertible === "true";
                    inputConversionRate.value = btn.dataset.conversionRate || "";


                    ["editLeaveTypeIsPaid", "editAccrualFrequency", "editLeaveTypeEarnedInterval"]
                    .forEach(id => document.getElementById(id)?.dispatchEvent(new Event("change")));

                    toggleEditSections();
                    modal.show();
                });
            });

            // 2️⃣ When Earned-switch toggles in the modal
            chkEarned.addEventListener("change", toggleEditSections);

            // 3️⃣ Handle the PUT update
            btnUpdate.addEventListener("click", async function(e) {
                e.preventDefault();

                const id = editForm.leave_type_id.value;
                const isEarn = chkEarned.checked ? 1 : 0;
                const isCashConvertible = chkCashConvertible.checked ? 1 : 0;
                const conversionRate = inputConversionRate.value.trim();


                const payload = {
                    name: editForm.name.value.trim(),
                    is_earned: isEarn,
                    default_entitle: parseFloat(editForm.default_entitle.value),
                    is_paid: editForm.is_paid.value === "1",
                    is_cash_convertible: isCashConvertible,
                    conversion_rate: isCashConvertible ? parseFloat(inputConversionRate.value) ||
                        0 : 0,
                };

                if (isEarn) {
                    payload.earned_rate = parseFloat(editForm.earned_rate.value);
                    payload.earned_interval = editForm.earned_interval.value;
                    payload.accrual_frequency = "NONE";
                    payload.max_carryover = 0;
                } else {
                    payload.accrual_frequency = editForm.accrual_frequency.value;
                    payload.max_carryover = parseFloat(editForm.max_carryover.value);
                    payload.earned_rate = null;
                    payload.earned_interval = null;
                }

                try {
                    const res = await fetch(`/api/settings/leave-type/update/${id}`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "Authorization": `Bearer ${authToken}`
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();

                    if (res.ok) {
                        toastr.success("Leave Type updated successfully!");
                        modal.hide();
                        setTimeout(() => location.reload(), 800);
                    } else {
                        const errs = data.errors || {
                            general: [data.message]
                        };
                        Object.values(errs).flat().forEach(msg => toastr.error(msg));
                    }
                } catch (err) {
                    console.error(err);
                    toastr.error("Something went wrong. Please try again.");
                }
            });
        });
    </script>

    {{-- Delete Leave Type --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            // Experience Delete
            let leaveTypeDeleteId = null;

            const leaveTypeDeleteButtons = document.querySelectorAll('.btn-delete');
            const leaveTypeDeleteBtn = document.getElementById('leaveTypeConfirmDeleteBtn');
            const leaveTypePlaceholder = document.getElementById('leaveTypePlaceholder');

            // Set up the delete buttons to capture data
            leaveTypeDeleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    leaveTypeDeleteId = this.getAttribute('data-id');
                    const leaveTypeName = this.getAttribute('data-name');

                    if (leaveTypePlaceholder) {
                        leaveTypePlaceholder.textContent =
                            leaveTypeName;
                    }
                });
            });

            // Confirm delete button click event
            leaveTypeDeleteBtn?.addEventListener('click', function() {
                if (!leaveTypeDeleteId)
                    return; // Ensure both id is available

                fetch(`/api/settings/leave-type/delete/${leaveTypeDeleteId}`, {
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
                            toastr.success("Leave type deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_leaveType'));
                            deleteModal.hide(); // Hide the modal

                            setTimeout(() => window.location.reload(),
                                800);
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting leave type.");
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

    {{-- Hide Inputs if Earned is on /Store Modal --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chk = document.getElementById('leaveTypeIsEarned');
            const earnedFields = document.getElementById('earnedFields');
            const globalFields = document.getElementById('globalFields');

            function toggleSections() {
                if (chk.checked) {
                    earnedFields.style.display = '';
                    globalFields.style.display = 'none';
                } else {
                    earnedFields.style.display = 'none';
                    globalFields.style.display = '';
                }
            }

            chk.addEventListener('change', toggleSections);
            toggleSections(); // initial state
        });
    </script>
@endpush
