<?php $page = 'allowance-user'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee's Allowances</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>s
                            </li>
                            <li class="breadcrumb-item">
                                Payroll Items
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee's Allowances</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">

                    {{-- <div class="me-2 mb-2">
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
                    </div> --}}

                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_allowance_user"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Assign
                            Allowance</a>
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
                    <h5>Employee's Allowances</h5>
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
                            <select name="branch_filter" id="branch_filter" class="select2 form-select"
                                style="width:150px;">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select"
                                style="width:150px;">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select"
                                style="width:150px;">
                                <option value="" selected>All Designations</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select">
                                <option value="" selected>All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
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
                                    <th class="text-center">Allowance</th>
                                    <th class="text-center">Override Amount</th>
                                    <th class="text-center">Frequency</th>
                                    <th class="text-center">Effective Date</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Created By</th>
                                    <th class="text-center">Edited By</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($userAllowances as $allowance)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox" value="{{ $allowance->id }}"
                                                    id="allowance_{{ $allowance->id }}">
                                            </div>
                                        </td>
                                        <td>{{ $allowance->user->personalInformation->full_name }}</td>
                                        <td>{{ $allowance->allowance->allowance_name ?? '-' }}</td>
                                        <td><em>{{ $allowance->override_amount ?? 'No Override' }}</em></td>
                                        <td>{{ ucwords(str_replace('_', ' ', $allowance->frequency)) }}</td>
                                        <td>
                                            {{ $allowance->effective_start_date?->format('M j, Y') ?? '' }} -
                                            {{ $allowance->effective_end_date?->format('M j, Y') ?? 'Indefinite' }} </td>
                                        <td>{{ ucfirst($allowance->type) }}</td>
                                        <td>
                                            <span
                                                class="badge d-inline-flex align-items-center badge-xs
                                                {{ $allowance->status === 'inactive' ? 'badge-danger' : 'badge-success' }}">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($allowance->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $allowance->creator_name }}</td>
                                        <td>{{ $allowance->updater_name }}</td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" data-bs-toggle="modal"
                                                    data-bs-target="#edit_allowance_user" data-id="{{ $allowance->id }}"
                                                    data-allowance-id="{{ $allowance->allowance_id }}"
                                                    data-type="{{ $allowance->type }}"
                                                    data-override-enabled="{{ $allowance->override_enabled ? 1 : 0 }}"
                                                    data-override-amount="{{ $allowance->override_amount }}"
                                                    data-calculation-basis="{{ $allowance->calculation_basis }}"
                                                    data-frequency="{{ $allowance->frequency }}"
                                                    data-effective_start_date="{{ $allowance->effective_start_date?->format('Y-m-d') ?? '' }}"
                                                    data-effective_end_date="{{ $allowance->effective_end_date?->format('Y-m-d') ?? '' }}"
                                                    data-status="{{ $allowance->status }}">
                                                    <i class="ti ti-edit" title="Edit"></i>
                                                </a>
                                                {{-- Delete --}}
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_allowance_user"
                                                    data-id="{{ $allowance->id }}"
                                                    data-name="{{ $allowance->user->personalInformation->full_name ?? 'Allowance' }}">
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
        'allowances' => $allowances,
        'branches' => $branches,
        'departments' => $departments,
        'designations' => $designations,
    ])
    @endcomponent
@endsection

@push('scripts')
    {{-- Modal Filter --}}
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

    {{-- Modal Toggle --}}
    <script>
        const $typeSelect = $('#userAllowanceType');
        const $sectionAmountDates = $('#allowanceSectionAmountDates');
        const $overrideAmount = $('#userAllowanceOverrideAmount');

        function toggleSection() {
            if ($typeSelect.val() === 'exclude') {
                $sectionAmountDates.find('input, select, textarea').val('').trigger('change');
                $sectionAmountDates.hide();
            } else {
                $sectionAmountDates.show();
            }
        }

        function toggleOverrideAmount() {
            const $overrideSwitch = $('#userAllowanceOverride');
            const $overrideSection = $('.allowanceSectionOverride');
            if ($overrideSwitch.is(':checked')) {
                $overrideSection.show();
            } else {
                $overrideSection.find('input, select, textarea').val('').trigger('change');
                $overrideSection.hide();
            }
        }

        toggleSection();
        toggleOverrideAmount();

        $typeSelect.on('change', toggleSection);

        $('#userAllowanceOverride').on('change', toggleOverrideAmount);
    </script>

    {{-- Assigning Script --}}
    <script>
        $(document).ready(function() {

            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            const authToken = localStorage.getItem('token');

            $('#assignAllowanceUserForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous validation states
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                // Gather form values
                let payload = {
                    type: $('#userAllowanceType').val(),
                    allowance_id: $('#userAllowanceId').val(),
                    user_id: $('#userAllowanceUserId').val() || [],
                    override_enabled: $('#userAllowanceOverride').is(':checked') ? 1 : 0,
                    override_amount: $('#userAllowanceOverrideAmount').val() || null,
                    calculation_basis: $('#userAllowanceCalculationBasis').val() || null,
                    effective_start_date: $('#userAllowanceEffectiveStartDate').val() || null,
                    effective_end_date: $('#userAllowanceEffectiveEndDate').val() || null,
                    frequency: $('#userAllowanceFrequency').val() || null,
                };

                $.ajax({
                    url: '/api/payroll/payroll-items/allowance/user/assign',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },

                    success: function(response) {
                        $('#assignAllowanceUserForm')[0].reset();
                        $('#add_allowance_user').modal('hide');
                        toastr.success(response.message || 'Allowance assigned successfully.');
                        window.location.reload();
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

                        } else if (xhr.status === 403) {
                            let message = xhr.responseJSON?.message;
                            toastr.error(message);
                            console.warn('403 Forbidden:', xhr.responseText);
                        } else {
                            let message = xhr.responseJSON?.message ||
                                'An unexpected error occurred. Please try again.';
                            toastr.error(message);
                            console.error(xhr.responseText);
                        }

                    }
                });
            });
        });
    </script>

    {{-- Edit Allowance User --}}
    <script>
        $(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            const $typeSelect = $('#editUserAllowanceType');
            const $sectionAmountDates = $('#editAllowanceSectionAmountDates');
            const $overrideAmount = $('#editAllowanceSectionOverride');

            function toggleSection() {
                if ($typeSelect.val() === 'exclude') {
                    $sectionAmountDates.find('input, select, textarea').val('').trigger('change');
                    $sectionAmountDates.hide();
                } else {
                    $sectionAmountDates.show();
                }
            }

            function toggleOverrideAmount() {
                const $overrideSwitch = $('#editUserAllowanceOverride');
                const $overrideSection = $('.editAllowanceSectionOverride');
                if ($overrideSwitch.is(':checked')) {
                    $overrideSection.show();
                } else {
                    $overrideSection.find('input, select, textarea').val('').trigger('change');
                    $overrideSection.hide();
                }
            }

            // When the modal is shown, populate fields
            $('#edit_allowance_user').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const recordId = button.data('id');
                const allowanceId = button.data('allowance-id');
                const type = button.data('type');
                const overrideEnabled = button.data('override-enabled') ? 1 : 0;
                const overrideAmount = button.data('override-amount') ?? '';
                const calculationBasis = button.data('calculation-basis') ?? '';
                const frequency = button.data('frequency');
                const startDate = button.data('effective_start_date');
                const endDate = button.data('effective_end_date');

                // Store current ID somewhere—attach to form as data attribute
                $('#editAssignAllowanceUserForm').data('record-id', recordId);

                // Populate each field
                $('#editUserAllowanceType').val(type);
                $('#editUserAllowanceId').val(allowanceId);
                $('#editUserAllowanceOverride').prop('checked', overrideEnabled);
                $('#editUserAllowanceOverrideAmount').val(overrideAmount);
                $('#editUserAllowanceCalculationBasis').val(calculationBasis);
                $('#editUserAllowanceFrequency').val(frequency);
                $('#editUserAllowanceEffectiveStartDate').val(startDate);
                $('#editUserAllowanceEffectiveEndDate').val(endDate);

                // Toggle section on load
                toggleSection();
                toggleOverrideAmount();

                // Clear any previous validation states
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            });

            // Whenever Type changes, hide/show fields
            $typeSelect.on('change', toggleSection);
            $('#editUserAllowanceOverride').on('change', toggleOverrideAmount);

            // Handle form submission via AJAX (PUT)
            $('#editAssignAllowanceUserForm').on('submit', function(e) {
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
                    type: $('#editUserAllowanceType').val(),
                    allowance_id: $('#editUserAllowanceId').val(),
                    override_enabled: $('#editUserAllowanceOverride').is(':checked') ? 1 : 0,
                    override_amount: $('#editUserAllowanceOverrideAmount').val() || null,
                    calculation_basis: $('#editUserAllowanceCalculationBasis').val() || null,
                    frequency: $('#editUserAllowanceFrequency').val(),
                    effective_start_date: $('#editUserAllowanceEffectiveStartDate').val() || null,
                    effective_end_date: $('#editUserAllowanceEffectiveEndDate').val() || null,
                };

                $.ajax({
                    url: '/api/payroll/payroll-items/allowance/user/update/' + recordId,
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },

                    success: function(response) {

                        $('#editAssignAllowanceUserForm')[0].reset();
                        $('#edit_allowance_user').modal('hide');
                        toastr.success(response.message ||
                            'Assigned allowance updated successfully.');
                        window.location.reload();
                    },

                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const json = xhr.responseJSON;
                            if (json.errors) {
                                if (json.errors.allowance_id) {
                                    toastr.error(json.errors.allowance_id[0]);
                                }
                            }

                            $.each(json.errors, function(field, messages) {
                                const $input = $('[name="' + field + '"]');
                                $input.addClass('is-invalid');
                                const errHtml = '<div class="invalid-feedback">' +
                                    messages[0] + '</div>';
                                $input.after(errHtml);
                            });

                        } else if (xhr.status === 403) {
                            const message = xhr.responseJSON?.message;
                            toastr.error(message);
                            console.warn('403 Forbidden:', xhr.responseText);

                        } else {
                            const message = xhr.responseJSON?.message ||
                                'An unexpected error occurred. Please try again.';
                            toastr.error(message);
                            console.error(xhr.responseText);
                        }
                    }
                });
            });
        });
    </script>

    {{-- Delete Allowance User --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let deleteId = null;

            const deleteButtons = document.querySelectorAll('.btn-delete');
            const userAllowanceConfirmBtn = document.getElementById('userAllowanceConfirmBtn');
            const userAllowancePlaceHolder = document.getElementById('userAllowancePlaceHolder');

            // Set up the delete buttons to capture data

            $(document).on('click', '.btn-delete', function() {
                deleteId = $(this).data('id');
                const allowanceName = $(this).data('name');

                const $userAllowancePlaceHolder = $('#userAllowancePlaceHolder');
                if ($userAllowancePlaceHolder.length) {
                    $userAllowancePlaceHolder.text(allowanceName);
                }
            });

            // Confirm delete button click event
            userAllowanceConfirmBtn?.addEventListener('click', function() {
                if (!deleteId) return;

                fetch(`/api/payroll/payroll-items/allowance/user/delete/${deleteId}`, {
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
                                'delete_allowance_user'));
                            deleteModal.hide();
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting assigned allowance.");
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
