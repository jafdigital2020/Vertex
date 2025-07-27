<?php $page = 'shift-management'; ?>
@extends('layout.mainlayout')
@include('components.modal-popup', [
        'branches' => $branches,
        'departments' => $departments,
        'designations' => $designations,
        'employees' => $employees,
        'shifts' => $shifts,
    ])
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
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Attendance
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Shift & Schedule</li>
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
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                            class="ti ti-file-type-xls me-1"></i>Download Template</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endif
                    @if(in_array('Create',$permission))
                    <div class="d-flex gap-2 mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#assign_shift_modal"
                            class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i>Add Shift
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
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Schedule List</h5>
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
                            <select name="branch_filter" id="branch_filter" class="select2 form-select" style="width:150px;">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select" style="width:150px;">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select" style="width:150px;">
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
                        <table class="table datatable table-bordered text-center align-middle mb-0" id="shiftTable">
                            <thead class="table-light">
                                <tr>
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
                                        <td class="text-start">
                                            <div class="d-flex align-items-center">
                                                @if(isset($emp->personalInformation) && !empty($emp->personalInformation->profile_picture))
                                                    <img src="{{ asset('storage/' . $emp->personalInformation->profile_picture) }}"
                                                        class="rounded-circle me-2" width="40" height="40" alt="Profile Picture">
                                                @else
                                                    <img src="https://via.placeholder.com/40"
                                                        class="rounded-circle me-2" width="40" height="40" alt="Profile Picture">
                                                @endif
                                                    {{ $emp->personalInformation->first_name ?? 'N/A'}} {{ $emp->personalInformation->last_name ?? 'N/A'}}
                                            </div>
                                        </td>
                                        @foreach ($dateRange as $date)
                                            @php
                                                $dateStr = $date->format('Y-m-d');
                                                $shifts = $assignments[$emp->id][$dateStr] ?? [];
                                            @endphp
                                            <td class="p-2 align-middle">
                                                @if (empty($shifts))
                                                    <span class="badge bg-danger">No Shift</span>
                                                @else
                                                    @foreach ($shifts as $shift)
                                                        @if (!empty($shift['rest_day']))
                                                            <span class="badge bg-warning text-dark">Rest Day</span>
                                                        @else
                                                            <div class="badge bg-outline-success d-flex flex-column align-items-center mb-1">
                                                                <div>{{ $shift['name'] }}</div>
                                                                <small>{{ $shift['start_time'] }} - {{ $shift['end_time'] }}</small>
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

@endsection

@push('scripts')
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
                success: function (response) {
                    $('#shiftTable').DataTable().destroy();
                    $('#shiftAssignmentTableBody').html(response.html);
                    $('#shiftTable').DataTable();    
                    updateTableHeader(response.dateRange);
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                }
            });
        }

        function updateTableHeader(dateRange) {
            let headerHtml = '<th>Employee</th>';
            dateRange.forEach(date => {
                headerHtml += `<th data-date="${date.full}">
                    ${date.short}<br><small>${date.day}</small>
                </th>`;
            });
            $('#shiftTable thead tr').html(headerHtml);
        }
    </script>
    <script>
        $(document).ready(function () {
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
     <script>

        function populateDropdown($select, items, placeholder = 'Select') {
            $select.empty();
            $select.append(`<option value="">All ${placeholder}</option>`);
            items.forEach(item => {
                $select.append(`<option value="${item.id}">${item.name}</option>`);
            });
        }

        $(document).ready(function () {

            $('#branch_filter').on('input', function () {
                const branchId = $(this).val();

                $.get('/api/filter-from-branch', { branch_id: branchId }, function (res) {
                    if (res.status === 'success') {
                        populateDropdown($('#department_filter'), res.departments, 'Departments');
                        populateDropdown($('#designation_filter'), res.designations, 'Designations');
                    }
                });
            });


          $('#department_filter').on('input', function () {
                const departmentId = $(this).val();
                const branchId = $('#branch_filter').val();

                $.get('/api/filter-from-department', {
                    department_id: departmentId,
                    branch_id: branchId,
                }, function (res) {
                    if (res.status === 'success') {
                        if (res.branch_id) {
                            $('#branch_filter').val(res.branch_id).trigger('change');
                        }
                        populateDropdown($('#designation_filter'), res.designations, 'Designations');
                    }
                });
            });

            $('#designation_filter').on('change', function () {
                const designationId = $(this).val();
                const branchId = $('#branch_filter').val();
                const departmentId = $('#department_filter').val();

                $.get('/api/filter-from-designation', {
                    designation_id: designationId,
                    branch_id: branchId,
                    department_id: departmentId
                }, function (res) {
                    if (res.status === 'success') {
                        if (designationId === '') {
                            populateDropdown($('#designation_filter'), res.designations, 'Designations');
                        } else {
                            $('#branch_filter').val(res.branch_id).trigger('change');
                            $('#department_filter').val(res.department_id).trigger('change');
                        }
                    }
                });
            });

        });
    </script>
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
        });
   </script>
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
                    // setTimeout(() => {
                    //     window.location.reload();
                    // }, 1000);
                    fetchFilteredData();
                    $('#assign_shift_modal').modal('hide');

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


    {{-- TABLE RENDER DISPLAY --}}
    <script>
        // window.shiftAssignments = @json($assignments);
        // window.shiftEmployees = @json($employees);

        // $(function() {
        //     const $picker = $('.bookingrange');
        //     const start = moment().startOf('isoWeek');
        //     const end = moment().endOf('isoWeek');

        //     // Initialize date range picker
        //     $picker.daterangepicker({
        //         startDate: start,
        //         endDate: end,
        //         locale: {
        //             format: 'MM/DD/YYYY'
        //         },
        //         opens: 'left'
        //     }, fetchAndRender); // Fetch data when date range changes

        //     // Initial load
        //     fetchAndRender(start, end);

        //     // Fetch data from backend with filters applied
        //     function fetchAndRender(start, end) {
        //         const branchId = $('#branchDropdownToggle').data('id') || '';
        //         const departmentId = $('#departmentDropdownToggle').data('id') || '';
        //         const designationId = $('#designationDropdownToggle').data('id') || '';

        //         $.ajax({
        //             url: '/shift-management', // Ensure this URL is correct for your endpoint
        //             data: {
        //                 start_date: start.format('YYYY-MM-DD'),
        //                 end_date: end.format('YYYY-MM-DD'),
        //                 branch_id: branchId,
        //                 department_id: departmentId,
        //                 designation_id: designationId
        //             },
        //             headers: {
        //                 'Accept': 'application/json',
        //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        //                 'Authorization': `Bearer ${localStorage.getItem('token')}`
        //             },
        //             success: function(response) {
        //                 window.shiftAssignments = response.assignments;
        //                 window.shiftEmployees = response.employees;
        //                 renderTable(start, end); // Render table after data fetch
        //             },
        //             error: function() {
        //                 toastr.error('Error fetching shift assignments.');
        //             }
        //         });
        //     }

        //     // Render the table with shift assignments
        //     function renderTable(start, end) {
        //         const dates = [];
        //         const cur = start.clone();
        //         while (cur.isSameOrBefore(end)) {
        //             dates.push(cur.clone());
        //             cur.add(1, 'day');
        //         }

        //         // Rebuild table header
        //         const $head = $('#shiftTable thead tr').empty().append('<th>Employee</th>');
        //         dates.forEach(dt => {
        //             $head.append(
        //                 `<th data-date="${dt.format('YYYY-MM-DD')}">
        //                     ${dt.format('MM/DD/YYYY')}<br>
        //                     <small>${dt.format('ddd')}</small>
        //                 </th>`
        //             );
        //         });

        //         // Rebuild table body
        //         const tbody = $('#shiftTable tbody').empty();

        //         Object.keys(window.shiftEmployees).forEach(userId => {
        //             const emp = window.shiftEmployees[userId];
        //             const $row = $(`<tr data-user-id="${userId}"></tr>`);

        //             // Employee column
        //             $row.append(`
        //                 <td class="text-start">
        //                     <div class="d-flex align-items-center">
        //                         <img src="${emp.profile_picture ? '/storage/' + emp.profile_picture : 'https://via.placeholder.com/40'}"
        //                              class="rounded-circle me-2" width="40" height="40" alt="Profile Picture">
        //                         <a href="/shift-mangement/assign-shift/edit/${emp.assignment_id}" class="text-decoration-none">
        //                             ${emp.first_name} ${emp.last_name}
        //                         </a>
        //                     </div>
        //                 </td>`);

        //             // Date columns (shift data per day)
        //             dates.forEach(dt => {
        //                 const ds = dt.format('YYYY-MM-DD');
        //                 const shifts = (window.shiftAssignments[userId][ds] || []);

        //                 let cellContent = '';
        //                 if (!shifts.length) {
        //                     cellContent = '<span class="badge bg-danger">No Shift</span>';
        //                 } else {
        //                     cellContent = shifts.map(shift => {
        //                         if (shift.rest_day) {
        //                             return `
        //    <span class="badge bg-warning text-dark">Rest Day</span>`;
        //                         } else {
        //                             return `
        //     <div class="badge bg-outline-success d-flex flex-column align-items-center mb-1">
        //         <div>${shift.name}</div>
        //         <small>${shift.start_time} - ${shift.end_time}</small>
        //     </div>`;
        //                         }
        //                     }).join('');
        //                 }

        //                 $row.append(`<td class="p-2 align-top">${cellContent}</td>`);
        //             });

        //             tbody.append($row); // Append the row to the table
        //         });
        //     }

            // When Branch is selected
            // $(document).on('click', '.branch-filter', function() {
            //     const branchId = $(this).data('id');

            //     // Update Branch Label
            //     $('#branchDropdownToggle').text($(this).data('name'));

            //     // Clear Department and Designation Dropdowns
            //     $('#departmentDropdownToggle').text('All Departments');
            //     $('#designationDropdownToggle').text('All Designations');

            //     // Store Branch ID
            //     $('#branchDropdownToggle').data('id', branchId);

            //     // AJAX to fetch departments and employees for the selected branch
            //     $.ajax({
            //         url: `/api/get-branch-data/${branchId}`,
            //         type: 'GET',
            //         headers: {
            //             'Accept': 'application/json',
            //             'Authorization': `Bearer ${localStorage.getItem('token')}`
            //         },
            //         success: function(response) {
            //             const $departmentDropdown = $('#departmentDropdownToggle').siblings(
            //                 '.dropdown-menu');
            //             $departmentDropdown.empty();

            //             // Check if departments exist for the branch
            //             if (response.departments.length > 0) {
            //                 // Show all departments for the selected branch
            //                 $departmentDropdown.append(`
            //                     <li>
            //                         <a href="javascript:void(0);" class="dropdown-item rounded-1 department-filter" data-id="" data-name="All Departments">All Departments</a>
            //                     </li>
            //                 `);

            //                 response.departments.forEach(dep => {
            //                     $departmentDropdown.append(`
            //                         <li>
            //                             <a href="javascript:void(0);" class="dropdown-item rounded-1 department-filter"
            //                                data-id="${dep.id}" data-name="${dep.department_name}">
            //                                ${dep.department_name}
            //                             </a>
            //                         </li>
            //                     `);
            //                 });
            //             } else {
            //                 // If no departments are available for the branch
            //                 $departmentDropdown.append(`
            //                     <li><a href="javascript:void(0);" class="dropdown-item text-muted">No Departments Available</a></li>
            //                 `);
            //             }

            //             // Clear Designations because this is a new department list
            //             const $designationDropdown = $('#designationDropdownToggle').siblings(
            //                 '.dropdown-menu');
            //             $designationDropdown.empty().append(`
            //                 <li>
            //                     <a href="javascript:void(0);" class="dropdown-item rounded-1 designation-filter" data-id="" data-name="All Designations">All Designations</a>
            //                 </li>
            //             `);
            //         }
            //     });

            //     // Automatically call the filter function
            //     fetchAndRender(moment().startOf('isoWeek'), moment().endOf('isoWeek'));
            // });

            // // When Department is selected
            // $(document).on('click', '.department-filter', function() {
            //     const departmentId = $(this).data('id');

            //     // Update Department Label
            //     $('#departmentDropdownToggle').text($(this).data('name'));

            //     // Store Department ID
            //     $('#departmentDropdownToggle').data('id', departmentId);

            //     // Clear Designation Label
            //     $('#designationDropdownToggle').text('All Designations');

            //     // AJAX to fetch designations for selected department
            //     $.ajax({
            //         url: `/api/get-designations/${departmentId}`,
            //         type: 'GET',
            //         headers: {
            //             'Accept': 'application/json',
            //             'Authorization': `Bearer ${localStorage.getItem('token')}`
            //         },
            //         success: function(response) {
            //             const $designationDropdown = $('#designationDropdownToggle').siblings(
            //                 '.dropdown-menu');
            //             $designationDropdown.empty();

            //             $designationDropdown.append(`
            //                 <li>
            //                     <a href="javascript:void(0);" class="dropdown-item rounded-1 designation-filter" data-id="" data-name="All Designations">All Designations</a>
            //                 </li>
            //             `);

            //             response.forEach(des => {
            //                 $designationDropdown.append(`
            //                     <li>
            //                         <a href="javascript:void(0);" class="dropdown-item rounded-1 designation-filter"
            //                            data-id="${des.id}" data-name="${des.designation_name}">
            //                            ${des.designation_name}
            //                         </a>
            //                     </li>
            //                 `);
            //             });
            //         }
            //     });

            //     // Automatically call the filter function
            //     fetchAndRender(moment().startOf('isoWeek'), moment().endOf('isoWeek'));
            // });

            // // When Designation is selected
            // $(document).on('click', '.designation-filter', function() {
            //     const designationId = $(this).data('id');

            //     // Update Designation Label
            //     $('#designationDropdownToggle').text($(this).data('name'));

            //     // Store Designation ID
            //     $('#designationDropdownToggle').data('id', designationId);

            //     // Automatically call the filter function
            //     fetchAndRender(moment().startOf('isoWeek'), moment().endOf('isoWeek'));
            // });

    </script>

@endpush
