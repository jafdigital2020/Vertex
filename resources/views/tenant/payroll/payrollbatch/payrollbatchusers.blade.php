<?php $page = 'users'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Payroll Batch Users</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Payroll
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Payroll Batch Users</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if(in_array('Export',$permission))
                    <div class="me-2 mb-2">
                        <div class="dropdown">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-orange d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                <i class="ti ti-file-export me-1"></i>Export
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                        <i class="ti ti-file-type-pdf me-1"></i>Export as PDF
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                        <i class="ti ti-file-type-xls me-1"></i>Export as Excel
                                    </a>
                                </li>
                            </ul>
                        </div>
                        @endif
                    </div>
                    @if (in_array('Create', $permission))
                        <div class="mb-2 d-flex gap-2"> 
                            <a href="#" class="btn btn-secondary d-flex align-items-center" data-bs-toggle="modal"
                                data-bs-target="#bulk_pb_assigning_modal">
                                <i class="ti ti-plus me-2"></i> Bulk Assigning
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
                <div class="card-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h5 class="mb-0">Payroll Batch Users</h5>
                        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3"> 
                        <div class="form-group me-2" style="width:200px;">
                            <select name="batch_filter" id="batch_filter" class="select2 form-select"
                                oninput="filter()">
                                <option value="" selected>All Batches</option>
                                @foreach ($payrollBatchSettings as $pbs)
                                    <option value="{{ $pbs->id }}">{{ $pbs->name }}</option>
                                @endforeach
                                <option value="-1">No Defined Batch</option>
                            </select>
                        </div>
                        <div class="form-group me-2" style="width:200px;">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select" oninput="filter()">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2" style="width:200px;">
                            <select name="department_filter" id="department_filter" class="select2 form-select"
                                oninput="filter()">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2" style="width:200px;">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select"
                                oninput="filter()">
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
                            <table class="table datatable" id="pbUsersTable">
                                <thead class="thead-light">
                                    <tr> 
                                        <th>Name</th>
                                        <th class="text-center">Batch Name</th>   
                                        @if (in_array('Update', $permission))
                                            <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="pbUsersTableBody">
                                    {{-- @if (in_array('Read', $permission))
                                        @foreach ($users as $user)
                                            <tr> 
                                                <td>
                                                    <div class="d-flex align-items-center file-name-icon">
                                                        <a href="#" class="avatar avatar-md avatar-rounded">
                                                            <img src="{{ URL::asset('build/img/users/user-32.jpg') }}"
                                                                class="img-fluid" alt="img">
                                                        </a>
                                                        <div class="ms-2">
                                                            <h6 class="fw-medium"><a
                                                                    href="#">{{ $user->personalInformation->first_name ?? '' }}
                                                                    {{ $user->personalInformation->last_name ?? '' }} </a></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                              <td class="text-center">
                                                @if(($user->payrollBatchUsers ?? collect())->count())
                                                    @foreach($user->payrollBatchUsers as $pbUser)
                                                        {{ $pbUser->batchSetting->name }}@if(!$loop->last), @endif
                                                    @endforeach
                                                @else
                                                    No Defined Batch
                                                @endif
                                               </td> 
                                                <td class="text-center">   
                                                <a href="#" onclick='editPayrollBatchUsers({{ $user->id }}, @json($user->payrollBatchUsers ?? []))'> 
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                </td>   
                                            </tr>
                                        @endforeach
                                    @endif --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> 
        </div> 
       @include('layout.partials.footer-company')
        </div> 
        @component('components.modal-popup', ['payrollBatchSettings' => $payrollBatchSettings, 'branches'=> $branches])
        @endcomponent
 
    @endsection

    @push('scripts') 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        $('.select2').select2(); 
    $('#branchSelect').on('change', function() {
            let branchIds = $(this).val();
            if (branchIds.length > 0) {
                $.ajax({
                    url: "{{ route('fetch.departments') }}",  
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        branches: branchIds
                    },
                    success: function(response) {
                        $('#departmentSelect').empty();
                        $.each(response.departments, function(id, name) {
                            $('#departmentSelect').append('<option value="'+id+'">'+name+'</option>');
                        });
                        $('#departmentSelect').trigger('change');
                    }
                });
            }
    });
 
    $('#departmentSelect').on('change', function() {
        let departmentIds = $(this).val();
        if (departmentIds.length > 0) {
            $.ajax({
                url: "{{ route('fetch.designations') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    departments: departmentIds
                },
                success: function(response) {
                    $('#designationSelect').empty();
                    $.each(response.designations, function(id, name) {
                        $('#designationSelect').append('<option value="'+id+'">'+name+'</option>');
                    });
                    $('#designationSelect').trigger('change');
                }
            });
        }
    });
 
    $('#designationSelect').on('change', function() {
        let designationIds = $(this).val();
        if (designationIds.length > 0) {
            $.ajax({
                url: "{{ route('fetch.employees') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    designations: designationIds
                },
                success: function(response) {
                    $('#employeeSelect').empty();
                    $.each(response.employees, function(id, name) {
                        $('#employeeSelect').append('<option value="'+id+'">'+name+'</option>');
                    });
                }
            });
        }
    });
}); 

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#bulkAssignForm').on('submit', function(e) {
        e.preventDefault();

        let form = $(this);  

        $.ajax({
            url: '{{ route("payroll-batch.check-duplicate") }}',
            method: 'POST',
            data: form.serialize(),
            success: async function (response) {
                let formData = form.serializeArray();  

                if (response.conflicts.length > 0) {
                    let includeUserIds = [];

                    for (const conflict of response.conflicts) {
                        const result = await Swal.fire({
                            title: 'Already Assigned',
                            html: `<p><strong>${conflict.name}</strong> is already assigned to <em>${conflict.batch}</em>.</p><p>Do you want to include this user anyway?</p>`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Include',
                            cancelButtonText: 'No, Skip'
                        });

                        if (result.isConfirmed) {
                            includeUserIds.push(conflict.user_id);
                        }
                    }
    
                    formData.push({
                        name: 'force_include_users',
                        value: includeUserIds.join(',')
                    });
                } 
                
                $.ajax({
                    url: '{{ route("payroll-batch-bulk-assign") }}',  
                    method: 'POST',
                    data: $.param(formData),
                    success: function(response) {
                        Swal.fire({
                            title: 'Success',
                            text: 'Payroll batch successfully assigned.',
                            icon: 'success'
                        }).then(() => { 
                            $('#bulk_pb_assigning_modal').modal('hide');
                            filter();
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to assign employees payroll batch.',
                            icon: 'error'
                        });
                    }
                });
            },
            error: function (xhr) {
                console.error(xhr);
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to validate employees.',
                    icon: 'error'
                });
            }
        });
    });

  </script>

  <script>
  
    let Table;

    function initializeTable() {
        if ($.fn.DataTable.isDataTable('#pbUsersTable')) {
            $('#pbUsersTable').DataTable().clear().destroy();
        }

        assetsTable = $('#pbUsersTable').DataTable({
            pageLength: 10,
            responsive: true
        });
    }

    $('#dateRange_filter').on('apply.daterangepicker', function () {
        filter();
    });

    function filter() { 
        const branch = $('#branch_filter').val();
        const department = $('#department_filter').val();
        const designation = $('#designation_filter').val(); 
        const batch = $('#batch_filter').val();
        $.ajax({
            url: '{{ route('payroll-batch-users-filter') }}',
            type: 'GET',
            data: {
                branch,
                department,
                designation, 
                batch,
            },
            success: function (response) {
                if (response.status === 'success') {
                    if ($.fn.DataTable.isDataTable('#pbUsersTable')) {
                        $('#pbUsersTable').DataTable().destroy();
                    }

                    $('#pbUsersTableBody').html(response.html);

                    Table = $('#upbUsersTable').DataTable({
                        pageLength: 10,
                        responsive: true
                    });
                    initializeTable();
                } else {
                    toastr.error(response.message || 'Something went wrong.');
                }
            },
            error: function (xhr) {
                let message = 'An unexpected error occurred.';
                if (xhr.status === 403) {
                    message = 'You are not authorized to perform this action.';
                } else if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            }
        });
    }
    function editPayrollBatchUsers(userId, batchUsers) {
        batchUsers = batchUsers || [];

        let batchIds = batchUsers.map(function(item) {
            return item.pbsettings_id;
        });

        $('#edit_user_id').val(userId);
        $('#edit_batch_users_select').val(batchIds).trigger('change');

        $('#editPayrollBatchUsersModal').modal('show');
    } 

     $('#editPayrollBatchUsersForm').on('submit', function(e) {
        e.preventDefault();

        let userId = $('#edit_user_id').val();
        let batchIds = $('#edit_batch_users_select').val() || [];  

        $.ajax({
            url: '{{ route("payroll-batch-users-update") }}',
            method: 'POST',
            data: {
                user_id: userId,
                batch_ids: batchIds,  
                _token: '{{ csrf_token() }}'  
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Updated');
                    $('#editPayrollBatchUsersModal').modal('hide');
                    filter();
                } else {
                    toastr.error(response.message || 'Something went wrong.');
                }
            },
             error: function(xhr) {
                if (xhr.status === 422) { 
                    let errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        errors[field].forEach(function(message) {
                            toastr.error(message);
                        });
                    }
                } else if (xhr.status === 403) { 
                    let response = xhr.responseJSON;
                    toastr.error(response.message || 'Forbidden.');
                } else {
                    toastr.error('An unexpected error occurred.');
                }
            }
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
