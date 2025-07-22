<?php $page = 'leave-assigned-users'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Assigned Users</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Leave Settings
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $leaveType->name ?? 'Leave Type' }}
                            </li>
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
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Search Filter -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Users Assigned to {{ $leaveType->name }}</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select"
                                oninput="deptList_filter();">
                                <option value="" selected>All Branches</option>

                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select"
                                oninput="deptList_filter()">
                                <option value="" selected>All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <select name="sortby_filter" id="sortby_filter" class="select2 form-select"
                                onchange="deptList_filter()">
                                <option value="" selected>All Sort By</option>
                                <option value="ascending">Ascending</option>
                                <option value="descending">Descending</option>
                                <option value="last_month">Last Month</option>
                                <option value="last_7_days">Last 7 days</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Credits</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assignedUsers as $users)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-md" data-bs-toggle="modal"
                                                    data-bs-target="#view_details"><img
                                                        src="{{ asset('storage/' . ($users->user->personalInformation->profile_picture ?? 'default-profile.jpg')) }}"
                                                        class="img-fluid rounded-circle" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="{{ url('employee-details') }}"
                                                            data-bs-toggle="modal" data-bs-target="#view_details">
                                                            {{ $users->user->personalInformation->last_name ?? '' }}
                                                            {{ $users->user->personalInformation->suffix ?? '' }},
                                                            {{ $users->user->personalInformation->first_name ?? '' }}
                                                            {{ $users->user->personalInformation->middle_name ?? '' }}</a>
                                                    </p>
                                                    <span
                                                        class="fs-12">{{ $users->user->employmentDetail->department->department_name ?? '' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $users->user->employmentDetail->branch->name ?? 'N/A' }}</td>
                                        <td>{{ $users->current_balance ?? 'N/A' }}</td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_assigned_users_leave"
                                                    data-id="{{ $users->id }}"
                                                    data-leave-name="{{ $users->leaveType->name }}"
                                                    data-current-balance="{{ $users->current_balance }}" title="Edit"><i
                                                        class="ti ti-edit"></i></a>
                                                <a href="javascript:void(0);" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_assigned_users_leave"
                                                    data-id="{{ $users->id }}" title="Delete"><i
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
        </div>
        @include('layout.partials.footer-company')

    </div>
    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    {{-- Edit Assigned Users --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            // 🌟 1. Delegate click events for edit buttons
            document.addEventListener("click", function(e) {
                const button = e.target.closest('[data-bs-target="#edit_assigned_users_leave"]');
                if (!button) return;

                const id = button.dataset.id;
                const leaveName = button.dataset.leaveName;
                const currentBalance = button.dataset.currentBalance;

                document.getElementById("leaveEntitlementId").value = id;
                document.getElementById("assignedLeaveTypeName").value = leaveName;
                document.getElementById("assignedLeaveCurrentBalance").value = currentBalance;

            });

            // 🌟 2. Handle update button click
            document.getElementById("updateAssignedLeaveBtn").addEventListener("click", async function(e) {
                e.preventDefault();

                const editId = document.getElementById("leaveEntitlementId").value;
                const leaveName = document.getElementById("assignedLeaveTypeName").value.trim();
                const currentBalance = document.getElementById("assignedLeaveCurrentBalance").value
                    .trim();

                const payload = {
                    current_balance: currentBalance,
                };

                try {
                    const res = await fetch(`/api/leave/leave-settings/assigned-users/${editId}`, {
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
                        toastr.success("Updated successfully!");
                        $('#edit_assigned_users_leave').modal('hide');
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        (data.errors ?
                            Object.values(data.errors).flat().forEach(msg => toastr.error(msg)) :
                            toastr.error(data.message || "Update failed.")
                        );
                    }

                } catch (err) {
                    console.error(err);
                    toastr.error("Something went wrong.");
                }
            });
        });
    </script>

    {{-- Delete Assigned Users --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let leaveEntitlementId = null;
            const assignedLeaveConfirmBtn = document.getElementById('assignedLeaveConfirmBtn');

            // Use delegation to listen for delete button clicks
            document.addEventListener('click', function(e) {
                const button = e.target.closest('.btn-delete');
                if (!button) return;

                leaveEntitlementId = button.getAttribute('data-id');

            });

            // Confirm delete
            assignedLeaveConfirmBtn?.addEventListener('click', function() {
                if (!leaveEntitlementId) return;

                fetch(`/api/leave/leave-settings/assigned-users/delete/${leaveEntitlementId}`, {
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
                            toastr.success("Deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_assigned_users_leave'));
                            deleteModal.hide();
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message || "An error occured.");
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
