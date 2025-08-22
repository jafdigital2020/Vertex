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
                    @if(in_array('Export',$permission))
                    <div class="me-2 mb-2">
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
                            </ul>
                        </div>
                    </div>
                    @endif
                    @if(in_array('Create',$permission))
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_user_to_holiday_exception"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            User</a>
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
                    <h5>Holiday Exception List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                          <div class="form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select"
                                oninput="holidayExceptionFilter()" style="width:150px;">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select"
                                oninput="holidayExceptionFilter()" style="width:159px;">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                           <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select"
                                oninput="holidayExceptionFilter()" style="width:150px;">
                                <option value="" selected>All Designations</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="holiday_filter" id="holiday_filter" class="select2 form-select"
                                oninput="holidayExceptionFilter()" style="width:150px;">
                                <option value="" selected>All Holidays</option>
                                 @foreach ($holidays as $holiday)
                                       <option value="{{ $holiday->id }}">{{ $holiday->name }}</option>
                                 @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select"
                                oninput="holidayExceptionFilter()">
                                <option value="" selected>All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable" id="holidayExTable">
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
                                    <th class="text-center">Holiday</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Edited By</th>
                                     @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                    <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="holidayExTableBody">
                                @if(in_array('Read',$permission))
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
                                        @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                        <td class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                
                                                @if(in_array('Update',$permission))
                                                @if( $holidayException->status == 'active')
                                                <a href="#" class="btn-deactivate" data-bs-toggle="modal"
                                                    data-bs-target="#deactivate_holiday"
                                                    data-id="{{ $holidayException->id }}"
                                                    data-name="{{ $holidayException->user->personalInformation->first_name }} {{ $holidayException->user->personalInformation->last_name }}"><i
                                                        class="ti ti-cancel" title="Deactivate"></i></a>
                                                @else
                                                <a href="#" class="btn-activate" data-bs-toggle="modal"
                                                    data-bs-target="#activate_holiday"
                                                    data-id="{{ $holidayException->id }}"
                                                    data-name="{{ $holidayException->user->personalInformation->first_name }} {{ $holidayException->user->personalInformation->last_name }}"
                                                    title="Activate"><i class="ti ti-circle-check"></i></a>
                                                @endif
                                                @endif
                                        
                                              @if(in_array('Delete',$permission))
                                                <a href="javascript:void(0);" data-bs-toggle="modal" class="btn-delete"
                                                    data-bs-target="#delete_holiday_exception"
                                                    data-id="{{ $holidayException->id }}"
                                                    data-name="{{ $holidayException->user->personalInformation->first_name }} {{ $holidayException->user->personalInformation->last_name }}"
                                                    title="Delete"><i class="ti ti-trash"></i></a>
                                                @endif
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                                @endif
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
   
    <script>
    
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const authToken = localStorage.getItem('token');
 
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
 
        function updateEmployeeSelect(modal) {
            const allEmps = modal.data('employees') || [];
            const deptIds = modal.find('.department-select').val() || [];
            const desigIds = modal.find('.designation-select').val() || [];

            const filtered = allEmps.filter(emp => {
                const ed = emp.employment_detail;
                if (!ed) return false;
                if (deptIds.length && !deptIds.includes(String(ed.department_id))) return false;
                if (desigIds.length && !desigIds.includes(String(ed.designation_id))) return false;
                return true;
            });

            let opts = '<option value="">All Employee</option>';
            filtered.forEach(emp => {
                const u = emp.personal_information;
                if (u) {
                    opts += `<option value="${emp.id}">${u.last_name}, ${u.first_name}</option>`;
                }
            });

            modal.find('.employee-select').html(opts).trigger('change');
        }
    
        $(document).on('change', '.branch-select', function () {
            const $this = $(this);
            if (handleSelectAll($this)) return;

            const branchIds = $this.val() || [];
            const modal = $this.closest('.modal');
            const depSel = modal.find('.department-select');
            const desSel = modal.find('.designation-select');
            const empSel = modal.find('.employee-select');
    
            depSel.html('<option value="">All Department</option>').trigger('change');
            desSel.html('<option value="">All Designation</option>').trigger('change');
            empSel.html('<option value="">All Employee</option>').trigger('change');
            modal.removeData('employees');

            if (!branchIds.length) return;
    
            $.ajax({
                url: '/api/holiday-exception/departments',
                method: 'GET',
                data: { branch_ids: branchIds },
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Authorization': 'Bearer ' + authToken
                },
                success(data) {
                    let dOpts = '<option value="">All Department</option>';
                    data.forEach(d => {
                        dOpts += `<option value="${d.id}">${d.department_name}</option>`;
                    });
                    depSel.html(dOpts).trigger('change');
                },
                error() {
                    alert('Failed to fetch departments.');
                }
            });
    
            $.ajax({
                url: '/api/holiday-exception/employees',
                method: 'GET',
                data: { branch_ids: branchIds },
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Authorization': 'Bearer ' + authToken
                },
                success(data) {
                    modal.data('employees', data || []);
                    updateEmployeeSelect(modal);
                },
                error() {
                    alert('Failed to fetch employees.');
                }
            });
        });
    
        $(document).on('change', '.department-select', function () {
            const $this = $(this);
            if (handleSelectAll($this)) return;

            const deptIds = $this.val() || [];
            const modal = $this.closest('.modal');
            const desSel = modal.find('.designation-select');

            desSel.html('<option value="">All Designation</option>').trigger('change');
            updateEmployeeSelect(modal);

            if (!deptIds.length) return;

            $.ajax({
                url: '/api/holiday-exception/designations',
                method: 'GET',
                data: { department_ids: deptIds },
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
    
        $(document).on('change', '.designation-select', function () {
            const $this = $(this);
            if (handleSelectAll($this)) return;
            updateEmployeeSelect($this.closest('.modal'));
        });
    
        $(document).on('change', '.employee-select', function () {
            handleSelectAll($(this));
        });
    
        $(document).on('change', '.holiday-select', function () {
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
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Authorization': `Bearer ${authToken}`
                        },
                        body: formData
                    });

                    if (response.status === 422) {
                        const payload = await response.json();
                        for (const [field, messages] of Object.entries(payload.errors)) {
                            const inputName = field.replace(/\.\d+$/, '') + '[]';
                            const input = form.querySelector(`[name="${inputName}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const fb = document.createElement('div');
                                fb.className = 'invalid-feedback';
                                fb.innerText = messages[0];
                                input.insertAdjacentElement('afterend', fb);
                            }
                        }
                        toastr.error('Please fix the highlighted errors.', 'Validation Failed');
                        return;
                    }

                    if (!response.ok) {
                        const payload = await response.json();  
                        toastr.error(payload.message || 'An error occurred.', 'Error');
                        return;
                    }

                    const data = await response.json();
                    toastr.success(data.message);

                    form.reset();
                    const modalEl = document.getElementById('add_user_to_holiday_exception');
                    bootstrap.Modal.getInstance(modalEl)?.hide();

                    holidayExceptionFilter(); 
                } catch (err) {
                    toastr.error(err.message || 'Something went wrong. Please try again.', 'Error');
                }

            });
        });
    </script>

    {{-- Deactivate --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const authToken = localStorage.getItem('token');
            let deactivateId = null;

            const confirmDeactivateHolidayExceptionBtn = document.getElementById('confirmDeactivateHolidayExceptionBtn');
            const deactivateHolidayEmployeeName = document.getElementById('deactivateHolidayEmployeeName');
 
            document.addEventListener('click', function (event) {
                const button = event.target.closest('.btn-deactivate');
                if (button) {
                    deactivateId = button.getAttribute('data-id');
                    const employeeName = button.getAttribute('data-name');

                    if (deactivateHolidayEmployeeName) {
                        deactivateHolidayEmployeeName.textContent = employeeName;
                    }
                }
            });

            confirmDeactivateHolidayExceptionBtn?.addEventListener('click', function () {
                if (!deactivateId) return;

                fetch(`/api/holidays/holiday-exception/deactivate/${deactivateId}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${authToken}`
                    },
                })
                .then(async response => {
                    if (response.ok) {
                        toastr.success("Holiday Exception has been successfully deactivated.");

                        const deactivateModal = bootstrap.Modal.getInstance(document.getElementById('deactivate_holiday'));
                        deactivateModal?.hide();
                        holidayExceptionFilter(); 
                    } else {
                        let message = "Error deactivating holiday exception.";
                        try {
                            const data = await response.json();
                            message = data.message || message;
                        } catch (_) {}
                        toastr.error(message);
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
      document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const authToken = localStorage.getItem('token');
        let activateId = null;

        const confirmActivateHolidayExceptionBtn = document.getElementById('confirmActivateHolidayExceptionBtn');
        const activateHolidayEmployeeName = document.getElementById('activateHolidayEmployeeName');
 
        document.addEventListener('click', function (event) {
            const button = event.target.closest('.btn-activate');
            if (button) {
                activateId = button.getAttribute('data-id');
                const employeeName = button.getAttribute('data-name');

                if (activateHolidayEmployeeName) {
                    activateHolidayEmployeeName.textContent = employeeName;
                }
            }
        });

        confirmActivateHolidayExceptionBtn?.addEventListener('click', function () {
            if (!activateId) return;

            fetch(`/api/holidays/holiday-exception/activate/${activateId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${authToken}`
                },
            })
            .then(async response => {
                if (response.ok) {
                    toastr.success("Holiday Exception has been successfully activated.");

                    const activateModal = bootstrap.Modal.getInstance(document.getElementById('activate_holiday'));
                    activateModal?.hide();
                    holidayExceptionFilter(); 
                } else {
                    let message = "Error activating holiday exception.";
                    try {
                        const data = await response.json();
                        message = data.message || message;
                    } catch (_) {}
                    toastr.error(message);
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
       document.addEventListener("DOMContentLoaded", function () {
            const authToken = localStorage.getItem("token");
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let holidayExceptionDeleteId = null;

            const holidayExceptionConfirmDeleteBtn = document.getElementById('holidayExceptionConfirmDeleteBtn');
            const holidayExceptionEmployeePlaceHolder = document.getElementById('holidayExceptionEmployeePlaceHolder');
 
            document.addEventListener('click', function (e) {
                const button = e.target.closest('.btn-delete');
                if (button) {
                    holidayExceptionDeleteId = button.getAttribute('data-id');
                    const holidayExceptionUserName = button.getAttribute('data-name');

                    if (holidayExceptionEmployeePlaceHolder) {
                        holidayExceptionEmployeePlaceHolder.textContent = holidayExceptionUserName;
                    }
                }
            });
 
            holidayExceptionConfirmDeleteBtn?.addEventListener('click', function () {
                if (!holidayExceptionDeleteId) return;

                fetch(`/api/holidays/holiday-exception/delete/${holidayExceptionDeleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${authToken}`,
                    },
                })
                .then(async response => {
                    if (response.ok) {
                        toastr.success("Holiday exception deleted successfully.");

                        const deleteModal = bootstrap.Modal.getInstance(
                            document.getElementById('delete_holiday_exception')
                        );
                        deleteModal?.hide();
                        holidayExceptionFilter(); 
                    } else {
                        let message = "Error deleting holiday exception.";
                        try {
                            const data = await response.json();
                            message = data.message || message;
                        } catch (_) {}
                        toastr.error(message);
                    }
                })
                .catch(error => {
                    console.error(error);
                    toastr.error("Server error.");
                });
            });
        });

 

        function holidayExceptionFilter(){
            var holiday = $('#holiday_filter').val();
            var branch = $('#branch_filter').val();
            var department = $('#department_filter').val();
            var designation = $("#designation_filter").val();
            var status = $('#status_filter').val();

             $.ajax({
                url: '{{ route('holidayEx_filter') }}',
                type: 'GET',
                data: {
                    holiday:holiday,
                    status: status,
                    branch: branch,
                    department: department,
                    designation: designation
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#holidayExTable').DataTable().destroy(); 
                        $('#holidayExTableBody').html(response.html);
                        $('#holidayExTable').DataTable();      
                    } else if (response.status === 'error') {
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
        // $('#branch_filter').on('input', function () {
        //     const branchId = $(this).val() || 'all'; 
            
        //     $.get(`/api/branches/${branchId}/departments`, function (departments) {
        //         $('#department_filter').empty().append('<option value="">All Departments</option>');
        //         departments.forEach(dep => {
        //             $('#department_filter').append(`<option value="${dep.id}">${dep.department_name}</option>`);
        //         });
        //         holidayExceptionFilter();
        //     });
        // });

        // $('#department_filter').on('change', function () {
        //     const departmentId = $(this).val(); 
        //     $.get(`/api/departments/${departmentId}/branch`, function (branch) {
        //         $('#branch_filter').val(branch.id).trigger('change');
        //         holidayExceptionFilter();
        //     }); 
        // });
 
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

            $('#designation_filter').on('input', function() {
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
