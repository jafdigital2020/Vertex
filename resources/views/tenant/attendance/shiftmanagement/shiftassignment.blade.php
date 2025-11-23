<?php $page = 'shift-management'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Shift Management</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Attendance
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Shift & Schedule</li>
                        </ol>
                    </nav>
                </div>

                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if (in_array('Export', $permission))
                        {{-- <div class="me-2 mb-2">
                        <div class="dropdown">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                <i class="ti ti-file-export me-1"></i>Export
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3" style="z-index:1050;position:absolute">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                            class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                            class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                            class="ti ti-file-type-xls me-1"></i>Download Template</a>
                                </li>
                            </ul>
                        </div>
                    </div> --}}
                    @endif
                    @if (in_array('Create', $permission))
                        <div class="d-flex gap-2 mb-2">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#assign_shift_modal"
                                class="btn btn-primary d-flex align-items-center">
                                <i class="ti ti-circle-plus me-2"></i>Assign Shift
                            </a>
                            <a href="{{ route('shift-list') }}" class="btn btn-secondary d-flex align-items-center">
                                <i class="ti ti-arrow-right me-2"></i>Shift List
                            </a>
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

            {{-- Table --}}
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3 bg-primary">
                    <h5 class="text-white">Schedule List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <!-- Bulk Actions Dropdown -->
                        <div class="dropdown me-2">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="bulkActionsDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Bulk Actions
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="bulkActionsDropdown">

                                @if (in_array('Delete', $permission))
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center"
                                            id="bulkDelete">
                                            <i class="ti ti-trash me-2 text-danger"></i>
                                            <span>Delete</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>

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
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable table text-center align-middle mb-0" id="shiftTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Employee</th>
                                    @foreach ($dateRange as $date)
                                        <th data-date="{{ $date->format('Y-m-d') }}">
                                            {{ $date->format('m/d/Y') }}<br>
                                            <small>{{ $date->format('D') }}</small>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody id="shiftAssignmentTableBody">
                                @foreach ($employees as $emp)
                                    <tr data-user-id="{{ $emp->id }}">
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox"
                                                    value="{{ $emp->id }}">
                                            </div>
                                        </td>
                                        <td class="text-start">
                                            <div class="d-flex align-items-center">
                                                @if (isset($emp->personalInformation) && !empty($emp->personalInformation->profile_picture))
                                                    <img src="{{ asset('storage/' . $emp->personalInformation->profile_picture) }}"
                                                        class="rounded-circle me-2" width="40" height="40"
                                                        alt="Profile Picture">
                                                @else
                                                    <img src="https://via.placeholder.com/40" class="rounded-circle me-2"
                                                        width="40" height="40" alt="Profile Picture">
                                                @endif
                                                {{ $emp->personalInformation->first_name ?? 'N/A' }}
                                                {{ $emp->personalInformation->last_name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        @foreach ($dateRange as $date)
                                            @php
                                                $dateStr = $date->format('Y-m-d');
                                                $shifts = $assignments[$emp->id][$dateStr] ?? [];
                                            @endphp
                                            <td class="p-2 align-middle">
                                                @if (empty($shifts))
                                                    <span class="badge bg-raspberry">No Shift</span>
                                                @else
                                                    @foreach ($shifts as $shift)
                                                        @if (!empty($shift['rest_day']))
                                                            <span class="badge bg-mustard text-white">Rest Day</span>
                                                        @else
                                                            <div
                                                                class="badge bg-outline-primary d-flex flex-column align-items-center mb-1">
                                                                <div>{{ $shift['name'] }}</div>
                                                                <small>{{ $shift['start_time'] }} -
                                                                    {{ $shift['end_time'] }}</small>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </td>
                                        @endforeach
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
    @include('components.modal-popup', [
        'branches' => $branches,
        'departments' => $departments,
        'designations' => $designations,
        'employees' => $employees,
        'shifts' => $shifts,
    ])
@endsection

@push('scripts')
    {{-- Data Filter --}}
    <script>
        function fetchFilteredData() {
            let dateRange = $('#dateRange_filter').val();
            let [start_date, end_date] = dateRange.split(' - ');
            let branch_id = $('#branch_filter').val();
            let department_id = $('#department_filter').val();
            let designation_id = $('#designation_filter').val();

            $.ajax({
                url: "{{ route('shiftmanagement.filter') }}",
                method: "GET",
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    branch_id: branch_id,
                    department_id: department_id,
                    designation_id: designation_id
                },
                success: function(response) {
                    $('#shiftTable').DataTable().destroy();
                    updateTableHeader(response.dateRange);
                    $('#shiftAssignmentTableBody').html(response.html);
                    $('#shiftTable').DataTable();

                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        }

        function updateTableHeader(dateRange) {
            // ✅ Include the checkbox column
            let headerHtml = `
        <th class="no-sort">
            <div class="form-check form-check-md">
                <input class="form-check-input" type="checkbox" id="select-all">
            </div>
        </th>
        <th>Employee</th>
    `;

            dateRange.forEach(date => {
                headerHtml += `<th data-date="${date.full}">
            ${date.short}<br><small>${date.day}</small>
        </th>`;
            });

            $('#shiftTable thead tr').html(headerHtml);

            // ✅ Re-attach the select-all event listener after rebuilding header
            const selectAllCheckbox = document.getElementById('select-all');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const isChecked = this.checked;
                    const rowCheckboxes = document.querySelectorAll(
                        '#shiftAssignmentTableBody input[type="checkbox"]');
                    rowCheckboxes.forEach(cb => cb.checked = isChecked);
                    updateBulkActionButton();
                });
            }
        }
    </script>

    {{-- Date Range Picker --}}
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            let start = moment().startOf('isoWeek');
            let end = moment().endOf('isoWeek');

            $('.bookingrange').daterangepicker({
                startDate: start,
                endDate: end,
                locale: {
                    format: 'MM/DD/YYYY'
                }
            });

            $('#branch_filter, #department_filter, #designation_filter').on('change', fetchFilteredData);
            $('.bookingrange').on('apply.daterangepicker', fetchFilteredData);
        });
    </script>

    {{-- Cascading Filters --}}
    <script>
        function populateDropdown($select, items, placeholder = 'Select') {
            $select.empty();
            $select.append(`<option value="">All ${placeholder}</option>`);
            items.forEach(item => {
                $select.append(`<option value="${item.id}">${item.name}</option>`);
            });
        }

        $(document).ready(function() {

            $('#branch_filter').on('input', function() {
                const branchId = $(this).val();

                $.get('/api/filter-from-branch', {
                    branch_id: branchId
                }, function(res) {
                    if (res.status === 'success') {
                        populateDropdown($('#department_filter'), res.departments, 'Departments');
                        populateDropdown($('#designation_filter'), res.designations,
                            'Designations');
                    }
                });
            });


            $('#department_filter').on('input', function() {
                const departmentId = $(this).val();
                const branchId = $('#branch_filter').val();

                $.get('/api/filter-from-department', {
                    department_id: departmentId,
                    branch_id: branchId,
                }, function(res) {
                    if (res.status === 'success') {
                        if (res.branch_id) {
                            $('#branch_filter').val(res.branch_id).trigger('change');
                        }
                        populateDropdown($('#designation_filter'), res.designations,
                            'Designations');
                    }
                });
            });

            $('#designation_filter').on('change', function() {
                const designationId = $(this).val();
                const branchId = $('#branch_filter').val();
                const departmentId = $('#department_filter').val();

                $.get('/api/filter-from-designation', {
                    designation_id: designationId,
                    branch_id: branchId,
                    department_id: departmentId
                }, function(res) {
                    if (res.status === 'success') {
                        if (designationId === '') {
                            populateDropdown($('#designation_filter'), res.designations,
                                'Designations');
                        } else {
                            $('#branch_filter').val(res.branch_id).trigger('change');
                            $('#department_filter').val(res.department_id).trigger('change');
                        }
                    }
                });
            });

        });
    </script>

    {{-- Shift Assignment Modal Scripts --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const typeSelect = document.getElementById("assignmentType");
            const daysWrapper = document.getElementById("daysOfWeekWrapper");
            const customWrapper = document.getElementById("customDatesWrapper");

            typeSelect.addEventListener("change", function() {
                const type = this.value;
                daysWrapper.classList.add("d-none");
                customWrapper.classList.add("d-none");

                if (type === "recurring") daysWrapper.classList.remove("d-none");
                if (type === "custom") customWrapper.classList.remove("d-none");
            });
        });
    </script>

    {{-- Cascading Selects in Modal --}}
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
                const shiftSel = modal.find('.shift-select');

                // reset downstream
                depSel.html('<option value="">All Department</option>').trigger('change');
                desSel.html('<option value="">All Designation</option>').trigger('change');
                empSel.html('<option value="">All Employee</option>').trigger('change');
                shiftSel.html('<option value="">All Shifts</option>').trigger('change');
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
        });
    </script>

    {{-- Shift Assignment Submission --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('assignShiftForm');
            const typeEl = document.getElementById('assignmentType');
            const daysChecks = document.querySelectorAll('input[name="days_of_week[]"]');
            const authToken = localStorage.getItem('token');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            const $shiftSelect = $('#shiftAssignmentShiftId');
            const $isRestDay = $('#isRestDay');

            $shiftSelect.select2({
                allowClear: true
            });

            // Disable and clear shift selection when rest day is checked
            $isRestDay.on('change', function() {
                const isChecked = $(this).is(':checked');

                if (isChecked) {
                    // Clear all selections (DO NOT select 'All Shift')
                    $shiftSelect.val([]).trigger('change.select2');
                }

                // Disable or enable the shift dropdown
                $shiftSelect.prop('disabled', isChecked);
            });

            // Core request sender
            async function sendAssignment({
                override = false,
                skip = false
            } = {}) {
                // Build base payload
                const isRestDay = document.getElementById('isRestDay').checked;
                const payload = {
                    user_id: [].concat($('#shiftAssignmentUserId').val() || []),
                    type: typeEl.value,
                    start_date: document.getElementById('shiftAssignmentStartDate').value,
                    end_date: document.getElementById('shiftAssignmentEndDate').value || null,
                    is_rest_day: isRestDay ? 1 : 0,
                    override: override ? 1 : 0,
                    skip_rest_check: skip ? 1 : 0,
                };

                if (!isRestDay) {
                    payload.shift_id = [].concat($('#shiftAssignmentShiftId').val() || []);
                }
                if (typeEl.value === 'recurring') {
                    payload.days_of_week = Array.from(daysChecks)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);
                    payload.custom_dates = [];
                } else if (typeEl.value === 'custom') {
                    payload.custom_dates = [].concat($('#customDates').val() || []);
                    payload.days_of_week = [];
                }

                console.log('⏳ Sending payload:', payload);
                const res = await fetch('/api/shift-management/shift-assignment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${authToken}`,
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (res.ok) {
                    toastr.success(data.message || 'Shift assigned successfully!');
                    fetchFilteredData();
                    $('#assign_shift_modal').modal('hide');

                    // Refresh the page after a short delay to allow UI updates/toast to show
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);

                    return;
                }

                // If server asks for override/skip…
                if (data.requires_override && !override && !skip) {
                    const doOverride = confirm(
                        data.message +
                        "\n\nOK = Override the rest day\nCancel = Skip the rest day"
                    );
                    if (doOverride) {
                        return sendAssignment({
                            override: true,
                            skip: false
                        });
                    }

                    const doSkip = confirm(
                        "Are you sure you want to SKIP the conflicting rest day?\n\n" +
                        "OK = Skip it\nCancel = Cancel assignment"
                    );
                    if (doSkip) {
                        return sendAssignment({
                            override: false,
                            skip: true
                        });
                    }

                    toastr.info('Shift assignment cancelled.');
                    return;
                }

                // Any other error
                toastr.error(data.message || 'Failed to assign shift.');
            }

            // Kick it off
            form.addEventListener('submit', e => {
                e.preventDefault();
                sendAssignment();
            });
        });
    </script>

    {{-- Bulk Actions --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const bulkDeleteBtn = document.getElementById('bulkDelete');
            const bulkActionsDropdown = document.getElementById('bulkActionsDropdown');

            if (!bulkDeleteBtn) return;

            // Select / Deselect all rows
            selectAllCheckbox && selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                const rowCheckboxes = document.querySelectorAll(
                    '#shiftAssignmentTableBody input[type="checkbox"]');

                rowCheckboxes.forEach(cb => cb.checked = isChecked);
                updateBulkActionButton();
            });

            // Individual checkbox change -> update header and bulk button
            document.addEventListener('change', function(e) {
                if (e.target.type === 'checkbox' && e.target.closest('#shiftAssignmentTableBody')) {
                    updateSelectAllState();
                    updateBulkActionButton();
                }
            });

            function updateSelectAllState() {
                const rowCheckboxes = document.querySelectorAll('#shiftAssignmentTableBody input[type="checkbox"]');
                const checkedBoxes = document.querySelectorAll(
                    '#shiftAssignmentTableBody input[type="checkbox"]:checked');

                if (checkedBoxes.length === 0) {
                    if (selectAllCheckbox) {
                        selectAllCheckbox.indeterminate = false;
                        selectAllCheckbox.checked = false;
                    }
                } else if (checkedBoxes.length === rowCheckboxes.length) {
                    if (selectAllCheckbox) {
                        selectAllCheckbox.indeterminate = false;
                        selectAllCheckbox.checked = true;
                    }
                } else {
                    if (selectAllCheckbox) {
                        selectAllCheckbox.indeterminate = true;
                        selectAllCheckbox.checked = false;
                    }
                }
            }

            function updateBulkActionButton() {
                const checkedBoxes = document.querySelectorAll(
                    '#shiftAssignmentTableBody input[type="checkbox"]:checked');
                const hasSelection = checkedBoxes.length > 0;

                if (bulkActionsDropdown) {
                    bulkActionsDropdown.disabled = !hasSelection;
                    bulkActionsDropdown.textContent = hasSelection ? `Bulk Actions (${checkedBoxes.length})` :
                        'Bulk Actions';
                    bulkActionsDropdown.classList.toggle('btn-primary', hasSelection);
                    bulkActionsDropdown.classList.toggle('btn-outline-primary', !hasSelection);
                }
            }

            // Collect selected user IDs from checked rows
            function getSelectedUserIds() {
                const checkedBoxes = document.querySelectorAll(
                    '#shiftAssignmentTableBody input[type="checkbox"]:checked');
                const userIds = [];

                checkedBoxes.forEach(cb => {
                    const row = cb.closest('tr');
                    const userId = row && row.dataset ? row.dataset.userId : null;
                    if (userId) {
                        userIds.push(userId);
                    }
                });

                return userIds.filter(Boolean);
            }

            // Bulk Delete handler with enhanced confirmation
            bulkDeleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const selectedUserIds = getSelectedUserIds();

                if (selectedUserIds.length === 0) {
                    toastr.warning('Please select at least one employee to delete their shifts.');
                    return;
                }

                // ✅ Enhanced confirmation message
                const confirmMessage =
                    `⚠️ WARNING: This will delete ALL shift assignments for ${selectedUserIds.length} selected employee(s).\n\n` +
                    `This includes:\n` +
                    `• All recurring shifts\n` +
                    `• All custom date shifts\n` +
                    `• All rest days\n\n` +
                    `This action CANNOT be undone!\n\n` +
                    `Are you absolutely sure you want to proceed?`;

                if (!confirm(confirmMessage)) {
                    return;
                }

                // ✅ Double confirmation for safety
                const doubleConfirm = confirm(
                    `🔴 FINAL CONFIRMATION\n\n` +
                    `You are about to permanently delete ALL shifts for ${selectedUserIds.length} employee(s).\n\n` +
                    `Click OK to proceed with deletion.`
                );

                if (!doubleConfirm) {
                    toastr.info('Bulk delete cancelled.');
                    return;
                }

                processBulkDelete(selectedUserIds);
            });

            // Process bulk delete request
            async function processBulkDelete(userIds) {
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const authToken = localStorage.getItem('token');

                try {
                    // Show loading state
                    const originalText = bulkDeleteBtn.innerHTML;
                    bulkDeleteBtn.setAttribute('data-original-text', originalText);
                    bulkDeleteBtn.innerHTML = '<i class="ti ti-loader ti-spin me-2"></i>Deleting...';
                    bulkDeleteBtn.style.pointerEvents = 'none';
                    bulkActionsDropdown.disabled = true;

                    const res = await fetch('/api/shift-management/bulk-delete-assignments', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Authorization': `Bearer ${authToken}`
                        },
                        body: JSON.stringify({
                            user_ids: userIds
                        })
                    });

                    const data = await res.json();

                    if (res.ok) {
                        toastr.success(data.message || `Deleted shifts for ${userIds.length} employee(s).`);

                        // Reload the page after a short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        throw new Error(data.message || 'Failed to delete shift assignments.');
                    }
                } catch (err) {
                    console.error('Bulk delete error:', err);
                    toastr.error(err.message || 'An error occurred while deleting shift assignments.');

                    // Restore button state on error
                    const original = bulkDeleteBtn.getAttribute('data-original-text') ||
                        '<i class="ti ti-trash me-2 text-danger"></i><span>Delete</span>';
                    bulkDeleteBtn.innerHTML = original;
                    bulkDeleteBtn.style.pointerEvents = 'auto';
                    bulkActionsDropdown.disabled = false;
                }
            }

            // Initialize state
            updateBulkActionButton();
        });
    </script>
@endpush
