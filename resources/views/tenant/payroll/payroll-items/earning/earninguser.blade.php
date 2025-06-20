<?php $page = 'earning-user'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee's Earnings</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Payroll Items
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee's Earnings</li>
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
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_earning_user"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Assign
                            Earning</a>
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
                    <h5>Employee's Earnings</h5>
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
                                    <th>Earnings</th>
                                    <th>Amount</th>
                                    <th>Frequency</th>
                                    <th>Effective Date</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Edited By</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($userEarnings as $userEarning)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>{{ $userEarning->user->personalInformation->last_name }},
                                            {{ $userEarning->user->personalInformation->first_name }} </td>
                                        <td>{{ $userEarning->earningType->name }}</td>
                                        <td>{{ $userEarning->amount }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $userEarning->frequency)) }}</td>
                                        <td>{{ $userEarning->effective_start_date?->format('M j, Y') ?? '' }} -
                                            {{ $userEarning->effective_end_date?->format('M j, Y') ?? '' }} </td>
                                        <td>{{ ucfirst($userEarning->type) }}</td>
                                        <td>
                                            <span
                                                class="badge d-inline-flex align-items-center badge-xs
                                                {{ $userEarning->status === 'inactive' ? 'badge-danger' : 'badge-success' }}">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($userEarning->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $userEarning->creator_name }}</td>
                                        <td>{{ $userEarning->updater_name }}</td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#edit_earning_user"
                                                    data-id="{{ $userEarning->id }}"
                                                    data-earning-type-id="{{ $userEarning->earning_type_id }}"
                                                    data-type="{{ $userEarning->type }}"
                                                    data-amount="{{ $userEarning->amount }}"
                                                    data-frequency="{{ $userEarning->frequency }}"
                                                    data-effective_start_date="{{ $userEarning->effective_start_date?->format('Y-m-d') ?? '' }}"
                                                    data-effective_end_date="{{ $userEarning->effective_end_date?->format('Y-m-d') ?? '' }}"
                                                    data-status="{{ $userEarning->status }}">
                                                    <i class="ti ti-edit" title="Edit"></i>
                                                </a>

                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_earning_user"
                                                    data-id="{{ $userEarning->id }}"
                                                    data-name="{{ $userEarning->user->personalInformation->last_name }}, {{ $userEarning->user->personalInformation->first_name }}">
                                                    <i class="ti ti-trash" title="Delete"></i>
                                                </a>
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
        'earningTypes' => $earningTypes,
        'branches' => $branches,
        'departments' => $departments,
        'designations' => $designations,
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

        });
    </script>

    {{-- Form Submission Store/Create w/ Hide Section --}}
    <script>
        $(document).ready(function() {
            // CSRF token for all Ajax requests
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Cache “Type” select and the section to hide/show
            const $typeSelect = $('#userEarningType');
            const $sectionAmountDates = $('#sectionAmountDates');

            // Toggle the “Amount / Dates” block based on type
            function toggleSection() {
                if ($typeSelect.val() === 'exclude') {
                    $sectionAmountDates.hide();
                } else {
                    $sectionAmountDates.show();
                }
            }

            // On page load, set the initial state
            toggleSection();

            // Whenever “Type” changes, hide/show accordingly
            $typeSelect.on('change', toggleSection);

            // Handle form submit
            $('#assignEarningUserForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous validation states
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                // Gather form values
                let payload = {
                    type: $('#userEarningType').val(),
                    earning_type_id: $('#earningTypeId').val(),
                    user_id: $('#userEarningUser_id').val() || [], // array of selected user IDs
                    amount: $('#userEarningAmount').val().trim() || null,
                    effective_start_date: $('#userEarningEffectiveStartDate').val() || null,
                    effective_end_date: $('#userEarningEffectiveEndDate').val() || null,
                    frequency: $('#userEarningFrequency').val() || null,
                };

                $.ajax({
                    url: '/api/payroll/payroll-items/earnings/user/assign',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        // Reset and close modal
                        $('#assignEarningUserForm')[0].reset();
                        $('#assignEarningUserModal').modal('hide');

                        // Toastr success
                        toastr.success(response.message || 'Earning assigned successfully.');
                        setTimeout(() => window.location.reload(), 800);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let json = xhr.responseJSON;
                            if (json.errors && json.errors.user_id && json.errors.user_id
                                .length) {
                                toastr.error(json.errors.user_id[0]);
                            }

                            $.each(json.errors, function(field, messages) {
                                if (field === 'user_id') return;

                                let baseField = field.replace(/\.\d+$/, '');
                                let $input = $('[name="' + baseField + (baseField
                                    .endsWith('[]') ? '"' : '"]'));
                                if (!$input.length) {
                                    $input = $('[name="' + baseField + '[]"]');
                                }
                                $input.addClass('is-invalid');
                                let errHtml = '<div class="invalid-feedback">' +
                                    messages[0] + '</div>';
                                $input.after(errHtml);
                            });
                        } else {
                            toastr.error('An unexpected error occurred. Please try again.');
                            console.error(xhr.responseText);
                        }
                    }
                });
            });
        });
    </script>

    {{-- Form Submission Update/Edit w/Hide Section --}}
    <script>
        $(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Cache selects & section
            const $typeSelect = $('#editUserEarningType');
            const $sectionAmountDates = $('#editSectionAmountDates');

            // Toggle hide/show of Amount/Frequency/Date block
            function toggleSection() {
                if ($typeSelect.val() === 'exclude') {
                    $sectionAmountDates.hide();
                } else {
                    $sectionAmountDates.show();
                }
            }

            // When the modal is shown, populate fields
            $('#edit_earning_user').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const recordId = button.data('id');
                const earningTypeId = button.data('earning-type-id');
                const type = button.data('type');
                const amount = button.data('amount') ?? '';
                const frequency = button.data('frequency');
                const startDate = button.data('effective_start_date');
                const endDate = button.data('effective_end_date');

                // Store current ID somewhere—attach to form as data attribute
                $('#editAssignEarningForm').data('record-id', recordId);

                // Populate each field
                $('#editUserEarningType').val(type);
                $('#editEarningTypeId').val(earningTypeId);
                $('#editUserEarningAmount').val(amount);
                $('#editUserEarningFrequency').val(frequency);
                $('#editUserEarningEffectiveStartDate').val(startDate);
                $('#editUserEarningEffectiveEndDate').val(endDate);

                // Toggle section on load
                toggleSection();

                // Clear any previous validation states
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            });

            // Whenever Type changes, hide/show fields
            $typeSelect.on('change', toggleSection);

            // Handle form submission via AJAX (PUT)
            $('#editAssignEarningForm').on('submit', function(e) {
                e.preventDefault();

                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                const recordId = $(this).data('record-id');
                if (!recordId) {
                    toastr.error('Missing record ID.');
                    return;
                }

                // Build payload
                const payload = {
                    type: $('#editUserEarningType').val(),
                    earning_type_id: $('#editEarningTypeId').val(),
                    amount: $('#editUserEarningAmount').val().trim() || null,
                    frequency: $('#editUserEarningFrequency').val(),
                    effective_start_date: $('#editUserEarningEffectiveStartDate').val() || null,
                    effective_end_date: $('#editUserEarningEffectiveEndDate').val() || null,
                };

                $.ajax({
                    url: '/api/payroll/payroll-items/earnings/user/update/' + recordId,
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        // Reset form, close modal, show success
                        $('#editAssignEarningForm')[0].reset();
                        $('#edit_earning_user').modal('hide');
                        toastr.success(response.message ||
                            'Assigned earning updated successfully.');
                        setTimeout(() => window.location.reload(), 800);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const json = xhr.responseJSON;
                            if (json.errors) {
                                if (json.errors.earning_type_id) {
                                    toastr.error(json.errors.earning_type_id[0]);
                                }
                            }
                            // Render inline errors
                            $.each(json.errors, function(field, messages) {
                                const $input = $('[name="' + field + '"]');
                                $input.addClass('is-invalid');
                                const errHtml = '<div class="invalid-feedback">' +
                                    messages[0] + '</div>';
                                $input.after(errHtml);
                            });
                        } else {
                            toastr.error('An unexpected error occurred. Please try again.');
                            console.error(xhr.responseText);
                        }
                    }
                });
            });
        });
    </script>

    {{-- Delete Confirmation --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let deleteId = null;

            const deleteButtons = document.querySelectorAll('.btn-delete');
            const userEarningConfirmBtn = document.getElementById('userEarningConfirmBtn');
            const userEarningPlaceHolder = document.getElementById('userEarningPlaceHolder');

            // Set up the delete buttons to capture data
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    deleteId = this.getAttribute('data-id');
                    const earningName = this.getAttribute('data-name');

                    if (userEarningPlaceHolder) {
                        userEarningPlaceHolder.textContent = earningName;
                    }
                });
            });

            // Confirm delete button click event
            userEarningConfirmBtn?.addEventListener('click', function() {
                if (!deleteId) return;

                fetch(`/api/payroll/payroll-items/earnings/user/delete/${deleteId}`, {
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
                            toastr.success("Assigned earning deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_earning_user'));
                            deleteModal.hide(); // Hide the modal

                            setTimeout(() => window.location.reload(),
                                800); // Refresh the page after a short delay
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting assigned earning.");
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
