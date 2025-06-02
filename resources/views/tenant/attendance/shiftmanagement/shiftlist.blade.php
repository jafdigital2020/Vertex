<?php $page = 'shift-list'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Shift List</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Shift Management
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Shift List</li>
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
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#schedule_timing"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            Shift</a>
                    </div>
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Shift List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                Designation
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Finance</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Developer</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Executive</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                Select Status
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Inactive</a>
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
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Ascending</a>
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
                                    <th>Shift Name</th>
                                    <th>Branch</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Break Minutes</th>
                                    <th>Created By</th>
                                    <th>Edited By</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($shifts as $shift)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>{{ $shift->name }}</td>
                                        <td>{{ $shift->branch->name ?? 'All Branches' }}</td>
                                        <td>{{ $shift->start_time }}</td>
                                        <td>{{ $shift->end_time }}</td>
                                        <td>{{ $shift->break_minutes }}</td>
                                        <td>{{ $shift->creator_name }}</td>
                                        <td>{{ $shift->updater_name }}</td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" class="me-2 editShiftBtn" data-bs-toggle="modal"
                                                    data-bs-target="#edit_shiftlist" data-id="{{ $shift->id }}"
                                                    data-name="{{ $shift->name }}"
                                                    data-start-time="{{ $shift->start_time }}"
                                                    data-end-time="{{ $shift->end_time }}"
                                                    data-break-minutes="{{ $shift->break_minutes }}"
                                                    data-notes="{{ $shift->notes }}"
                                                    data-branch-id="{{ $shift->branch_id }}"><i class="ti ti-edit"></i></a>
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_shift" data-id="{{ $shift->id }}"
                                                    data-name="{{ $shift->name }}"><i class="ti ti-trash"></i></a>
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

        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2025 &copy; OneJAF Vertex.</p>
            <p>Designed &amp; Developed By <a href="javascript:void(0);" class="text-primary">JAF Digital Group Inc.</a>
            </p>
        </div>

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup', [
        'branches' => $branches,
        'departments' => $departments,
        'designations' => $designations,
        'employees' => $employees,
        'shifts' => $shifts,
    ])
    @endcomponent
@endsection

@push('scripts')
    {{-- Store --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
            let authToken = localStorage.getItem("token");

            document.getElementById("createShiftForm")?.addEventListener("submit", async function(event) {
                event.preventDefault();

                let form = document.getElementById("createShiftForm");
                let formData = new FormData(form);

                try {
                    let response = await fetch(
                        `/api/shift-management/shift-list/create`, {
                            method: "POST",
                            headers: {
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": csrfToken,
                                "Authorization": `Bearer ${authToken}`
                            },
                            body: formData,
                        });

                    let data = await response.json();

                    if (response.ok) {
                        toastr.success(data.message || "Shift saved successfully!");
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(data.message || "Failed to save shift.");
                    }
                } catch (error) {
                    console.error(error);
                    toastr.error("Something went wrong. Please try again.");
                }
            });
        });
    </script>

    {{-- Update --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
            let authToken = localStorage.getItem("token");

            // 1. Populate form fields on modal open
            document.querySelectorAll('.editShiftBtn').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('shiftListId').value = this.getAttribute('data-id');
                    document.getElementById('editShiftListName').value = this.getAttribute(
                        'data-name');
                    document.getElementById('editStartTime').value = this.getAttribute(
                        'data-start-time');
                    document.getElementById('editEndTime').value = this.getAttribute(
                        'data-end-time');
                    document.getElementById('editBreakMinutes').value = this.getAttribute(
                        'data-break-minutes');
                    document.getElementById('editNotes').value = this.getAttribute('data-notes') ||
                        '';

                    const branchId = this.getAttribute("data-branch-id");
                    const editBranchSelect = document.getElementById("edtiShiftListBranchId");
                    editBranchSelect.value = branchId;

                    // Force UI update
                    editBranchSelect.dispatchEvent(new Event('change'));
                });
            });

            // 2. Handle Update Submit
            document.getElementById('editShiftForm')?.addEventListener('submit', async function(event) {
                event.preventDefault();

                const shiftId = document.getElementById('shiftListId').value;
                const formData = {
                    branch_id: document.getElementById('edtiShiftListBranchId').value,
                    name: document.getElementById('editShiftListName').value,
                    break_minutes: document.getElementById('editBreakMinutes').value,
                    start_time: document.getElementById('editStartTime').value,
                    end_time: document.getElementById('editEndTime').value,
                    notes: document.getElementById('editNotes').value,
                };

                try {
                    let response = await fetch(`/api/shift-management/shift-list/update/${shiftId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${authToken}`,
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(formData),
                    });

                    let data = await response.json();

                    if (response.ok) {
                        toastr.success(data.message || "Shift updated successfully!");
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(data.message || "Failed to update shift.");
                    }
                } catch (error) {
                    console.error(error);
                    toastr.error("Something went wrong. Please try again.");
                }
            });
        });
    </script>

    {{-- Delete --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
            let authToken = localStorage.getItem("token");

            let deleteShiftId = null;

            const deleteButtons = document.querySelectorAll('.btn-delete');
            const confirmDeleteBtn = document.getElementById('shiftListConfirmDeleteBtn');
            const shiftListPlaceHolder = document.getElementById('shiftListPlaceHolder');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    deleteShiftId = this.getAttribute('data-id');
                    const shiftListName = this.getAttribute('data-name');

                    if (shiftListPlaceHolder) {
                        shiftListPlaceHolder.textContent = shiftListName;
                    }
                });
            });

            confirmDeleteBtn?.addEventListener('click', function() {
                if (!deleteShiftId) return;

                fetch(`/api/shift-management/shift-list/delete/${deleteShiftId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${authToken}`,
                            'X-CSRF-TOKEN': csrfToken
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Shift deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_shift'));
                            deleteModal.hide();

                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message || "Error shift employee.");
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show the wrapper if needed:
            document.getElementById('customDatesWrapper')
                .classList.remove('d-none');

            // Activate bootstrap-datepicker with multi-date mode
            $('#customDates').datepicker({
                format: 'yyyy-mm-dd', // date format sent to server
                multidate: true, // enable picking multiple dates
                multidateSeparator: ',', // how dates are joined in the input
                autoclose: false, // keep open so user can pick many
                todayHighlight: true,
                clearBtn: true
            });
        });
    </script>
@endpush
