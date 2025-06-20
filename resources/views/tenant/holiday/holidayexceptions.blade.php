<?php $page = 'holiday-exception'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Holiday Exception</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Holiday
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Holiday Exception</li>
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
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_user_to_holiday_exception"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            User</a>
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
                                    <th>Employee</th>
                                    <th>Branch</th>
                                    <th>Department</th>
                                    <th>Holiday</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Edited By</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($holidayExceptions as $holidayException)
                                    @php
                                        $statusClass =
                                            $holidayException->status === 'active' ? 'badge-success' : 'badge-danger';
                                        $statusLabel = ucfirst($holidayException->status);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{ url('employee-details') }}" class="avatar avatar-md"
                                                    data-bs-toggle="modal" data-bs-target="#view_details"><img
                                                        src="{{ asset('storage/' . $holidayException->user->personalInformation->profile_picture) }}"
                                                        class="img-fluid rounded-circle" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="{{ url('employee-details') }}"
                                                            data-bs-toggle="modal" data-bs-target="#view_details">
                                                            {{ $holidayException->user->personalInformation->last_name }}
                                                            {{ $holidayException->user->personalInformation->suffix }},
                                                            {{ $holidayException->user->personalInformation->first_name }}
                                                            {{ $holidayException->user->personalInformation->middle_name }}</a>
                                                    </p>
                                                    <span class="fs-12"></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $holidayException->user->employmentDetail->branch->name }}</td>
                                        <td>{{ $holidayException->user->employmentDetail->department->department_name }}
                                        </td>
                                        <td>{{ $holidayException->holiday->name }}</td>
                                        <td>
                                            <span class="badge {{ $statusClass }}">
                                                <i class="ti ti-point-filled"></i> {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td>{{ $holidayException->creator_name }}</td>
                                        <td>{{ $holidayException->updater_name }}</td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" class="btn-deactivate" data-bs-toggle="modal"
                                                    data-bs-target="#deactivate_holiday"
                                                    data-id="{{ $holidayException->id }}"
                                                    data-name="{{ $holidayException->user->personalInformation->first_name }} {{ $holidayException->user->personalInformation->last_name }}"><i
                                                        class="ti ti-cancel" title="Deactivate"></i></a>

                                                <a href="#" class="btn-activate" data-bs-toggle="modal"
                                                    data-bs-target="#activate_holiday"
                                                    data-id="{{ $holidayException->id }}"
                                                    data-name="{{ $holidayException->user->personalInformation->first_name }} {{ $holidayException->user->personalInformation->last_name }}"
                                                    title="Activate"><i class="ti ti-circle-check"></i></a>

                                                <a href="javascript:void(0);" data-bs-toggle="modal" class="btn-delete"
                                                    data-bs-target="#delete_holiday_exception"
                                                    data-id="{{ $holidayException->id }}"
                                                    data-name="{{ $holidayException->user->personalInformation->first_name }} {{ $holidayException->user->personalInformation->last_name }}"
                                                    title="Delete"><i class="ti ti-trash"></i></a>
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
    <!-- /Page Wrapper -->

    @component('components.modal-popup', [
        'branches' => $branches,
        'departments' => $departments,
        'designations' => $designations,
        'holidays' => $holidays,
    ])
    @endcomponent
@endsection

@push('scripts')
    {{-- Filter --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const authToken = localStorage.getItem('token');

            // — Helper: if user picks the empty‐value “All” option, auto-select every real option
            function handleSelectAll($sel) {
                const vals = $sel.val() || [];
                if (vals.includes('')) {
                    const all = $sel.find('option')
                        .map((i, opt) => $(opt).val())
                        .get()
                        .filter(v => v !== '');
                    $sel.val(all).trigger('change');
                    return true;
                }
                return false;
            }

            // — Rebuild Employee list based on selected Departments & Designations
            function updateEmployeeSelect(modal) {
                const allEmps = modal.data('employees') || [];
                const deptIds = modal.find('.department-select').val() || [];
                const desigIds = modal.find('.designation-select').val() || [];

                const filtered = allEmps.filter(emp => {
                    if (deptIds.length && !deptIds.includes(String(emp.department_id))) return false;
                    if (desigIds.length && !desigIds.includes(String(emp.designation_id))) return false;
                    return true;
                });

                let opts = '<option value="">All Employee</option>';
                filtered.forEach(emp => {
                    const u = emp.user?.personal_information;
                    if (u) {
                        opts += `<option value="${emp.user.id}">
                   ${u.last_name}, ${u.first_name}
                 </option>`;
                    }
                });

                modal.find('.employee-select')
                    .html(opts)
                    .trigger('change');
            }

            // — Branch change → fetch Depts, Emps & Shifts
            $(document).on('change', '.branch-select', function() {
                const $this = $(this);
                if (handleSelectAll($this)) return;

                const branchIds = $this.val() || [];
                const modal = $this.closest('.modal');
                const depSel = modal.find('.department-select');
                const desSel = modal.find('.designation-select');
                const empSel = modal.find('.employee-select');

                // reset downstream
                depSel.html('<option value="">All Department</option>').trigger('change');
                desSel.html('<option value="">All Designation</option>').trigger('change');
                empSel.html('<option value="">All Employee</option>').trigger('change');
                modal.removeData('employees');

                if (!branchIds.length) return;

                $.ajax({
                    url: '/api/shift-management/get-branch-data?' + $.param({
                        branch_ids: branchIds
                    }),
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Authorization': 'Bearer ' + authToken
                    },
                    success(data) {
                        // populate Departments
                        let dOpts = '<option value="">All Department</option>';
                        data.departments.forEach(d => {
                            dOpts +=
                                `<option value="${d.id}">${d.department_name}</option>`;
                        });
                        depSel.html(dOpts).trigger('change');

                        // cache & render Employees
                        modal.data('employees', data.employees || []);
                        updateEmployeeSelect(modal);

                        // populate Shifts (ensure your API now returns data.shifts[])
                        let sOpts = '<option value="">All Shift</option>';
                        (data.shifts || []).forEach(s => {
                            sOpts += `<option value="${s.id}">${s.name}</option>`;
                        });
                        shiftSel.html(sOpts).trigger('change');
                    },
                    error() {
                        alert('Failed to fetch branch data.');
                    }
                });
            });

            // — Department change → fetch Designations & re-filter Employees
            $(document).on('change', '.department-select', function() {
                const $this = $(this);
                if (handleSelectAll($this)) return;

                const deptIds = $this.val() || [];
                const modal = $this.closest('.modal');
                const desSel = modal.find('.designation-select');

                desSel.html('<option value="">All Designation</option>').trigger('change');
                updateEmployeeSelect(modal);

                if (!deptIds.length) return;

                $.ajax({
                    url: '/api/shift-management/get-designations?' + $.param({
                        department_ids: deptIds
                    }),
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Authorization': 'Bearer ' + authToken
                    },
                    success(data) {
                        let o = '<option value="">All Designation</option>';
                        data.forEach(d => {
                            o += `<option value="${d.id}">${d.designation_name}</option>`;
                        });
                        desSel.html(o).trigger('change');
                    },
                    error() {
                        alert('Failed to fetch designations.');
                    }
                });
            });

            // — Designation change → re-filter Employees
            $(document).on('change', '.designation-select', function() {
                const $this = $(this);
                if (handleSelectAll($this)) return;
                updateEmployeeSelect($this.closest('.modal'));
            });

            // — Employee “All Employee” handler
            $(document).on('change', '.employee-select', function() {
                handleSelectAll($(this));
            });

            // — Holiday handler
            $(document).on('change', '.holiday-select', function() {
                handleSelectAll($(this));
            });
        });
    </script>

    {{-- Add Holiday Exception User --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const authToken = localStorage.getItem('token');
            const form = document.getElementById('addHolidayExceptionUserForm');

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Clear previous validation states
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                const formData = new FormData(form);

                try {
                    const response = await fetch('/api/holidays/holiday-exception/create/', {
                        method: 'POST',
                        headers: {
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "Authorization": `Bearer ${authToken}`
                        },
                        body: formData
                    });

                    // validation errors
                    if (response.status === 422) {
                        const payload = await response.json();
                        for (const [field, messages] of Object.entries(payload.errors)) {
                            const name = field.replace(/\.\d+$/, '') + '[]';
                            const input = form.querySelector(`[name="${name}"]`);
                            if (!input) continue;
                            input.classList.add('is-invalid');
                            const fb = document.createElement('div');
                            fb.className = 'invalid-feedback';
                            fb.innerText = messages[0];
                            input.insertAdjacentElement('afterend', fb);
                        }
                        toastr.error('Please fix the highlighted errors.', 'Validation Failed');
                        return;
                    }

                    if (!response.ok) throw new Error('Network error');

                    const data = await response.json();
                    toastr.success(data.message);

                    form.reset();
                    const modalEl = document.getElementById('add_user_to_holiday_exception');
                    bootstrap.Modal.getInstance(modalEl).hide();

                    setTimeout(() => {
                        window.location.reload();
                    }, 800);

                } catch (err) {
                    console.error(err);
                    toastr.error('Something went wrong, please try again.', 'Error');
                }
            });
        });
    </script>

    {{-- Deactivate --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const authToken = localStorage.getItem('token');

            let deactivateId = null;

            const deactivateButton = document.querySelectorAll('.btn-deactivate');
            const confirmDeactivateHolidayExceptionBtn = document.getElementById(
                'confirmDeactivateHolidayExceptionBtn');
            const deactivateHolidayEmployeeName = document.getElementById('deactivateHolidayEmployeeName');

            deactivateButton.forEach(button => {
                button.addEventListener('click', function() {
                    deactivateId = this.getAttribute('data-id');
                    const employeeName = this.getAttribute('data-name');

                    if (deactivateHolidayEmployeeName) {
                        deactivateHolidayEmployeeName.textContent = employeeName;
                    }
                });
            });

            confirmDeactivateHolidayExceptionBtn?.addEventListener('click', function() {
                if (!deactivateId) return;

                fetch(`/api/holidays/holiday-exception/deactivate/${deactivateId}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.getAttribute("content"),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            "Authorization": `Bearer ${authToken}`
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Holiday Exception has been successfully deactivated.");

                            const deactivateModal = bootstrap.Modal.getInstance(document.getElementById(
                                'deactivate_holiday'));
                            deactivateModal.hide();

                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deactivating holiday exception.");
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

    {{-- Activate --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const authToken = localStorage.getItem('token');

            let activateId = null;

            const activateButton = document.querySelectorAll('.btn-activate');
            const confirmActivateHolidayExceptionBtn = document.getElementById(
                'confirmActivateHolidayExceptionBtn');
            const activateHolidayEmployeeName = document.getElementById('activateHolidayEmployeeName');

            activateButton.forEach(button => {
                button.addEventListener('click', function() {
                    activateId = this.getAttribute('data-id');
                    const employeeName = this.getAttribute('data-name');

                    if (activateHolidayEmployeeName) {
                        activateHolidayEmployeeName.textContent = employeeName;
                    }
                });
            });

            confirmActivateHolidayExceptionBtn?.addEventListener('click', function() {
                if (!activateId) return;

                fetch(`/api/holidays/holiday-exception/activate/${activateId}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.getAttribute("content"),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            "Authorization": `Bearer ${authToken}`
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Holiday Exception has been successfully activated.");

                            const activateModal = bootstrap.Modal.getInstance(document.getElementById(
                                'activate_holiday'));
                            activateModal.hide();

                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error activating holiday exception.");
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

    {{-- Delete --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            // Experience Delete
            let holidayExceptionDeleteId = null;

            const holidayExceptionDeleteButtons = document.querySelectorAll('.btn-delete');
            const holidayExceptionConfirmDeleteBtn = document.getElementById('holidayExceptionConfirmDeleteBtn');
            const holidayExceptionEmployeePlaceHolder = document.getElementById('holidayExceptionEmployeePlaceHolder');

            // Set up the delete buttons to capture data
            holidayExceptionDeleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    holidayExceptionDeleteId = this.getAttribute('data-id');
                    const holidayExceptionUserName = this.getAttribute('data-name');

                    if (holidayExceptionEmployeePlaceHolder) {
                        holidayExceptionEmployeePlaceHolder.textContent =
                            holidayExceptionUserName;
                    }
                });
            });

            // Confirm delete button click event
            holidayExceptionConfirmDeleteBtn?.addEventListener('click', function() {
                if (!holidayExceptionDeleteId)
                    return;

                fetch(`/api/holidays/holiday-exception/delete/${holidayExceptionDeleteId}`, {
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
                            toastr.success("Holiday exception deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_holiday_exception'));
                            deleteModal.hide(); // Hide the modal

                            setTimeout(() => window.location.reload(),
                                800); // Refresh the page after a short delay
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting holiday exception.");
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
