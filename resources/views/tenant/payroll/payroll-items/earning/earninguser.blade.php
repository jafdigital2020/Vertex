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
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_earning_user"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Assign
                            Earning</a>
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

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Employee's Earnings</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                         <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter" oninput="filter()">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select" style="width:150px;" onchange="filter()">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select"   style="width:150px;" onchange="filter()">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select"  style="width:150px;"  onchange="filter()">
                                <option value="" selected>All Designations</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                @endforeach
                            </select>
                        </div> 
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select" onchange="filter()">
                                <option value="" selected>All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option> 
                            </select>
                         </div>
                </div>
            </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable" id="userEarningsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Employee</th>
                                    <th class="text-center">Earnings</th>
                                    <th class="text-center">Amount</th>
                                    <th class="text-center">Frequency</th>
                                    <th class="text-center">Effective Date</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Created By</th>
                                    <th class="text-center">Edited By</th>
                                    @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                    <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="userEarningsTableBody">
                                @foreach ($userEarnings as $userEarning)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>{{ $userEarning->user->personalInformation->last_name }},
                                            {{ $userEarning->user->personalInformation->first_name }} </td>
                                        <td class="text-center">{{ $userEarning->earningType->name }}</td>
                                        <td class="text-center">{{ $userEarning->amount }}</td>
                                        <td class="text-center">{{ ucwords(str_replace('_', ' ', $userEarning->frequency)) }}</td>
                                        <td class="text-center">{{ $userEarning->effective_start_date?->format('M j, Y') ?? '' }} -
                                            {{ $userEarning->effective_end_date?->format('M j, Y') ?? '' }} </td>
                                        <td class="text-center">{{ ucfirst($userEarning->type) }}</td>
                                        <td class="text-center">
                                            <span
                                                class="badge d-inline-flex align-items-center badge-xs
                                                {{ $userEarning->status === 'inactive' ? 'badge-danger' : 'badge-success' }}">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($userEarning->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $userEarning->creator_name }}</td>
                                        <td class="text-center">{{ $userEarning->updater_name }}</td>
                                        @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                        <td class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                @if(in_array('Update',$permission))
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
                                                @endif
                                                @if(in_array('Delete',$permission))
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_earning_user"
                                                    data-id="{{ $userEarning->id }}"
                                                    data-name="{{ $userEarning->user->personalInformation->last_name }}, {{ $userEarning->user->personalInformation->first_name }}">
                                                    <i class="ti ti-trash" title="Delete"></i>
                                                </a>
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
 <script>   
    $('#dateRange_filter').on('apply.daterangepicker', function(ev, picker) { 
        filter();
    }); 
    function filter() {
        const dateRange = $('#dateRange_filter').val();  
        var branch = $('#branch_filter').val();
        var department = $('#department_filter').val();
        var designation = $('#designation_filter').val();
        const status = $('#status_filter').val(); 
        $.ajax({
            url: '{{ route('user-earnings-filter') }}',
            type: 'GET',
            data: { 
                dateRange,
                status,
                branch,
                department,
                designation
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#userEarningsTable').DataTable().destroy();
                    $('#userEarningsTableBody').html(response.html);
                    $('#userEarningsTable').DataTable();
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
                        $('#add_earning_user').modal('hide'); 
                        toastr.success(response.message || 'Earning assigned successfully.');
                        filter();
                    },
                    error: function(xhr) {
                     if (xhr.status === 422) {
                        let json = xhr.responseJSON;

                        if (json.errors && json.errors.user_id && json.errors.user_id.length) {
                            toastr.error(json.errors.user_id[0]);
                        }

                        $.each(json.errors, function(field, messages) {
                            if (field === 'user_id') return;

                            let baseField = field.replace(/\.\d+$/, '');
                            let $input = $('[name="' + baseField + (baseField.endsWith('[]') ? '"' : '"]'));
                            if (!$input.length) {
                                $input = $('[name="' + baseField + '[]"]');
                            }

                            $input.addClass('is-invalid');
                            let errHtml = '<div class="invalid-feedback">' + messages[0] + '</div>';
                            $input.after(errHtml);
                        });
                    } else if (xhr.status === 403) {
                        let message = xhr.responseJSON?.message;
                        toastr.error(message);
                        console.warn('403 Forbidden:', xhr.responseText);
                    } else {
                        let message = xhr.responseJSON?.message || 'An unexpected error occurred. Please try again.';
                        toastr.error(message);
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
                       
                        $('#editAssignEarningForm')[0].reset();
                        $('#edit_earning_user').modal('hide');
                        toastr.success(response.message ||
                            'Assigned earning updated successfully.'); 
                        filter();
                    },
                    error: function(xhr) {
                       if (xhr.status === 422) {
                            const json = xhr.responseJSON;
                            if (json.errors) {
                                if (json.errors.earning_type_id) {
                                    toastr.error(json.errors.earning_type_id[0]);
                                }
                            }
 
                            $.each(json.errors, function(field, messages) {
                                const $input = $('[name="' + field + '"]');
                                $input.addClass('is-invalid');
                                const errHtml = '<div class="invalid-feedback">' + messages[0] + '</div>';
                                $input.after(errHtml);
                            });

                        } else if (xhr.status === 403) {
                            const message = xhr.responseJSON?.message;
                            toastr.error(message);
                            console.warn('403 Forbidden:', xhr.responseText);

                        } else {
                            const message = xhr.responseJSON?.message || 'An unexpected error occurred. Please try again.';
                            toastr.error(message);
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
          
            $(document).on('click', '.btn-delete', function () {
                deleteId = $(this).data('id');
                const earningName = $(this).data('name');

                const $userEarningPlaceHolder = $('#userEarningPlaceHolder');
                if ($userEarningPlaceHolder.length) {
                    $userEarningPlaceHolder.text(earningName);
                }
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
                            filter();
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
@endpush
