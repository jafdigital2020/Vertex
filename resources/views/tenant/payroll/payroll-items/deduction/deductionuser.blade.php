<?php $page = 'deduction-user'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee's Deduction</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Payroll Items
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee's Deduction</li>
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
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_deduction_user"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Assign
                            Deduction</a>
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
                    <h5>Employee's Deductions</h5>
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
                                    <th>Deductions</th>
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
                                @foreach ($userDeductions as $userDeduction)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>{{ $userDeduction->user->personalInformation->last_name }},
                                            {{ $userDeduction->user->personalInformation->first_name }} </td>
                                        <td>{{ $userDeduction->deductionType->name }}</td>
                                        <td>{{ $userDeduction->amount }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $userDeduction->frequency)) }}</td>
                                        <td>{{ $userDeduction->effective_start_date?->format('M j, Y') ?? '' }} -
                                            {{ $userDeduction->effective_end_date?->format('M j, Y') ?? '' }} </td>
                                        <td>{{ ucfirst($userDeduction->type) }}</td>
                                        <td>
                                            <span
                                                class="badge d-inline-flex align-items-center badge-xs
                                                {{ $userDeduction->status === 'inactive' ? 'badge-danger' : 'badge-success' }}">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($userDeduction->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $userDeduction->creator_name }}</td>
                                        <td>{{ $userDeduction->updater_name }}</td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#edit_deduction_user"
                                                    data-id="{{ $userDeduction->id }}"
                                                    data-deduction-type-id="{{ $userDeduction->deduction_type_id }}"
                                                    data-type="{{ $userDeduction->type }}"
                                                    data-amount="{{ $userDeduction->amount }}"
                                                    data-frequency="{{ $userDeduction->frequency }}"
                                                    data-effective_start_date="{{ $userDeduction->effective_start_date?->format('Y-m-d') ?? '' }}"
                                                    data-effective_end_date="{{ $userDeduction->effective_end_date?->format('Y-m-d') ?? '' }}"
                                                    data-status="{{ $userDeduction->status }}">
                                                    <i class="ti ti-edit" title="Edit"></i>
                                                </a>

                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_deduction_user"
                                                    data-id="{{ $userDeduction->id }}"
                                                    data-name="{{ $userDeduction->user->personalInformation->last_name }}, {{ $userDeduction->user->personalInformation->first_name }}">
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

        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2025 &copy; OneJAF Vertex.</p>
            <p>Designed &amp; Developed By <a href="javascript:void(0);" class="text-primary">JAF Digital Group Inc.</a>
            </p>
        </div>

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup', [
        'deductionTypes' => $deductionTypes,
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
            const $typeSelect = $('#userDeductionType');
            const $deductionSectionAmountDates = $('#deductionSectionAmountDates');

            // Toggle the “Amount / Dates” block based on type
            function toggleSection() {
                if ($typeSelect.val() === 'exclude') {
                    $deductionSectionAmountDates.hide();
                } else {
                    $deductionSectionAmountDates.show();
                }
            }

            // On page load, set the initial state
            toggleSection();

            // Whenever “Type” changes, hide/show accordingly
            $typeSelect.on('change', toggleSection);

            // Handle form submit
            $('#assignDeductionUserForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous validation states
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                // Gather form values
                let payload = {
                    type: $('#userDeductionType').val(),
                    deduction_type_id: $('#deductionTypeId').val(),
                    user_id: $('#userDeductionUser_id').val() || [], // array of selected user IDs
                    amount: $('#userDeductionAmount').val().trim() || null,
                    effective_start_date: $('#userDeductionEffectiveStartDate').val() || null,
                    effective_end_date: $('#userDeductionEffectiveEndDate').val() || null,
                    frequency: $('#userDeductionFrequency').val() || null,
                };

                $.ajax({
                    url: '/api/payroll/payroll-items/deductions/user/assign',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        // Reset and close modal
                        $('#assignDeductionUserForm')[0].reset();
                        $('#add_deduction_user').modal('hide');

                        // Toastr success
                        toastr.success(response.message || 'Deduction assigned successfully.');
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
            const $typeSelect = $('#editUserDeductionType');
            const $editDeductionSectionAmountDates = $('#editDeductionSectionAmountDates');

            // Toggle hide/show of Amount/Frequency/Date block
            function toggleSection() {
                if ($typeSelect.val() === 'exclude') {
                    $editDeductionSectionAmountDates.hide();
                } else {
                    $editDeductionSectionAmountDates.show();
                }
            }

            // When the modal is shown, populate fields
            $('#edit_deduction_user').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const recordId = button.data('id');
                const deductionTypeId = button.data('deduction-type-id');
                const type = button.data('type');
                const amount = button.data('amount') ?? '';
                const frequency = button.data('frequency');
                const startDate = button.data('effective_start_date');
                const endDate = button.data('effective_end_date');

                // Store current ID somewhere—attach to form as data attribute
                $('#editAssignDeductionForm').data('record-id', recordId);

                // Populate each field
                $('#editUserDeductionType').val(type);
                $('#editDeductionTypeId').val(deductionTypeId);
                $('#editUserDeductionAmount').val(amount);
                $('#editUserDeductionFrequency').val(frequency);
                $('#editUserDeductionEffectiveStartDate').val(startDate);
                $('#editUserDeductionEffectiveEndDate').val(endDate);

                // Toggle section on load
                toggleSection();

                // Clear any previous validation states
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            });

            // Whenever Type changes, hide/show fields
            $typeSelect.on('change', toggleSection);

            // Handle form submission via AJAX (PUT)
            $('#editAssignDeductionForm').on('submit', function(e) {
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
                    type: $('#editUserDeductionType').val(),
                    deduction_type_id: $('#editDeductionTypeId').val(),
                    amount: $('#editUserDeductionAmount').val().trim() || null,
                    frequency: $('#editUserDeductionFrequency').val(),
                    effective_start_date: $('#editUserDeductionEffectiveStartDate').val() || null,
                    effective_end_date: $('#editUserDeductionEffectiveEndDate').val() || null,
                };

                $.ajax({
                    url: '/api/payroll/payroll-items/deductions/user/update/' + recordId,
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        // Reset form, close modal, show success
                        $('#editAssignDeductionForm')[0].reset();
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
            const userDeductionConfirmBtn = document.getElementById('userDeductionConfirmBtn');
            const userDeductionPlaceholder = document.getElementById('userDeductionPlaceholder');

            // Set up the delete buttons to capture data
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    deleteId = this.getAttribute('data-id');
                    const deductionName = this.getAttribute('data-name');

                    if (userDeductionPlaceholder) {
                        userDeductionPlaceholder.textContent = deductionName;
                    }
                });
            });

            // Confirm delete button click event
            userDeductionConfirmBtn?.addEventListener('click', function() {
                if (!deleteId) return;

                fetch(`/api/payroll/payroll-items/deductions/user/delete/${deleteId}`, {
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
                            toastr.success("Assigned deduction deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_deduction_user'));
                            deleteModal.hide(); // Hide the modal

                            setTimeout(() => window.location.reload(),
                                800); // Refresh the page after a short delay
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting assigned deduction.");
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
