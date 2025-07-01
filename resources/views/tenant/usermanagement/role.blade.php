<?php $page = 'roles-permissions'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Roles</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Administration
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Roles</li>
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
                    @if (in_array('Create', $permission))
                    <div class="mb-2"> 
                            <a href="#" data-bs-toggle="modal" data-bs-target="#add_roleModal"
                                class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                                Roles</a> 
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

            <!-- Assets Lists -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Roles List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-group">
                                <select name="status_filter" id="status_filter" class="select2 form-select"
                                    onchange="role_filter()">
                                    <option value="" selected>All Statuses</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select name="sortby_filter" id="sortby_filter" class="select2 form-select"
                                    onchange="role_filter()">
                                    <option value="" selected>All Sort By</option>
                                    <option value="ascending">Ascending</option>
                                    <option value="descending">Descending</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="last_7_days">Last 7 days</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable table-bordered" id="role_permission_table">
                            <thead class="thead-light">
                                <tr> 
                                    <th class="text-center">Role</th> 
                                    <th class="text-center">Data Access Level</th>
                                    <th class="text-center">Status</th>
                                    @if (in_array('Update', $permission))
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if (in_array('Read', $permission))
                                    @foreach ($roles as $role)
                                        <tr class="text-center"> 
                                            <td>{{ $role->role_name }}</td>
                                            <td>{{$role->data_access_level->access_name ?? 'No Specified Access'}}</td> 
                                            <td>
                                                @if ($role->status == 1)
                                                    <span
                                                        class="badge badge-success d-inline-flex align-items-center badge-xs">
                                                        <i class="ti ti-point-filled me-1"></i> Active
                                                    </span>
                                                @else
                                                    <span
                                                        class="badge badge-danger d-inline-flex align-items-center badge-xs">
                                                        <i class="ti ti-point-filled me-1"></i> Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            @if (in_array('Update', $permission))
                                                <td>
                                                    <div class="action-icon d-inline-flex">
                                                        <a href="#" class="me-2"
                                                            onclick="permissionEdit({{ $role->id }})"><i
                                                                class="ti ti-shield"></i></a>
                                                        <a href="#" class="me-2"
                                                            onclick="roleEdit({{ $role->id }})"><i
                                                                class="ti ti-edit"></i></a>
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
    <div class="modal fade" id="add_roleModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Role</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form action="{{ route('add-role') }}" id="addRoleForm" method="POST">
                        @csrf
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Role Name</label>
                                    <input type="text" id="add_role_name" name="add_role_name" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                   <label class="form-label d-block">Data Access Level:</label>
                                    <select name="add_data_access" id="add_data_access" class="select2 form-control">
                                         <option value="" disabled selected>Select Access Level</option>
                                         @foreach ($data_access as $access)
                                             <option value="{{$access->id}}">{{$access->access_name}}</option>
                                         @endforeach
                                    </select> 
                                </div>
                            </div>
                        </div> 
                    </div> 
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
        <div class="modal fade" id="edit_roleModal">
            <div class="modal-dialog modal-dialog-centered modal-md w-100">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title ">Edit Role</h4>
                        <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                    <form action="{{ route('edit-role') }}" id="editRoleForm" method="POST">
                        @csrf
                        <div class="modal-body pb-0">
                            <div class="row mb-3">
                                <div class="form-group col-12 ">
                                    <label for="" class="form-label d-block">Role Name:</label>
                                    <input type="hidden" name="edit_role_id" id="edit_role_id"
                                        class="form-control text-sm">
                                    <input type="text" name="edit_role_name" id="edit_role_name"
                                        class="form-control text-sm">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="form-group col-12">
                                    <label for="status" class="form-label d-block">Data Access Level:</label>
                                    <select name="edit_data_access" id="edit_data_access" class="select2 form-control">
                                        <option value="" selected disabled>Select Access Level</option>
                                         @foreach ($data_access as $access)
                                             <option value="{{$access->id}}">{{$access->access_name}}</option>
                                         @endforeach
                                    </select> 
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="form-group col-12">
                                    <label for="status" class="form-label d-block">Status:</label>
                                    <select name="edit_role_status" id="edit_role_status" class="select2 form-control">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>

                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Role</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="edit_role_permissionModal">
            <div class="modal-dialog modal-dialog-centered modal-lg w-100">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title ">Edit Permission</h4>
                        <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                    <form action="{{ route('edit-role-permission') }}" id="editRolePermissionForm" method="POST">
                        @csrf
                        <input type="hidden" name="edit_role_permission_id" id="edit_role_permission_id"
                            class="form-control">
                        <div class="modal-body pb-0">
                            <div style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-sm table-bordered" id="subModuleTbl">
                                    <thead  style="position: sticky; top: 0; z-index: 2; background: white;">
                                        <tr>
                                            <th class="col-1 text-center align-middle">
                                                <div class="d-flex justify-content-center align-items-center p-0 m-0">
                                                    <input type="checkbox" class="form-check-input" id="checkAllRows">
                                                </div>
                                            </th>
                                            <th class="col-5">Module/Sub Module</th> 
                                            @foreach ($CRUD as $crud)
                                            <th class="col-1 text-center">
                                                {{ $crud->control_name }} 
                                                <input type="checkbox" class="form-check-input column-checkbox ms-2" data-crud="{{ $crud->id }}">
                                            </th>
                                            @endforeach
                                         </tr>
                                    </thead> 
                                    <tbody> 
                                        @foreach ($sub_modules as $s_mod)
                                            <tr>
                                                <td class="text-center d-flex justify-content-center"><input type="checkbox" class="form-control form-check-input"></td>
                                                <td  >{{ $s_mod->module->module_name }}/{{ $s_mod->sub_module_name }}</td>
                                           @foreach ($CRUD as $crud)
                                                <td class="text-center">
                                                    <input type="checkbox" name="edit_permission_ids[]"
                                                        value="{{ $s_mod->id }}-{{ $crud->id }}"
                                                        class="form-check-input crud-checkbox"
                                                        data-crud-id="{{ $crud->id }}"
                                                        style="transform: scale(1.5); transform-origin: center;">
                                                </td>
                                            @endforeach 
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Permission</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    @include('layout.partials.footer-company')
    </div>
    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts') 

   <script>
   $(document).ready(function () {    

    $('#checkAllRows').on('change', function () {
        let isChecked = $(this).is(':checked');
        $('tbody input[type="checkbox"]').prop('checked', isChecked);
        $('.column-checkbox').prop('checked', isChecked);
    });
 
    $('.column-checkbox').on('change', function () {
        let crudId = $(this).data('crud');
        let isChecked = $(this).is(':checked');
        $(`.crud-checkbox[data-crud-id="${crudId}"]`).prop('checked', isChecked);

        updateRowCheckboxes();
        updateMasterCheckbox();
    });
 
    $('tbody').on('change', '.crud-checkbox', function () {
        let crudId = $(this).data('crud-id');
 
        let totalInColumn = $(`.crud-checkbox[data-crud-id="${crudId}"]`).length;
        let checkedInColumn = $(`.crud-checkbox[data-crud-id="${crudId}"]:checked`).length;
        $(`.column-checkbox[data-crud="${crudId}"]`).prop('checked', totalInColumn === checkedInColumn);
 
        let $row = $(this).closest('tr');
        let totalInRow = $row.find('.crud-checkbox').length;
        let checkedInRow = $row.find('.crud-checkbox:checked').length;
        $row.find('td:first-child input[type="checkbox"]').prop('checked', totalInRow === checkedInRow);

        updateMasterCheckbox();
    });
 
    $('tbody tr').each(function () {
        let $row = $(this);
        let $rowMasterCheckbox = $row.find('td:first-child input[type="checkbox"]');

        $rowMasterCheckbox.on('change', function () {
            let isChecked = $(this).is(':checked');
            $row.find('.crud-checkbox').prop('checked', isChecked).trigger('change');
        });
    });
 
    $('tbody').on('change', 'input[type="checkbox"]', function () {
        updateMasterCheckbox();
    });

    function updateRowCheckboxes() {
        $('tbody tr').each(function () {
            let $row = $(this);
            let total = $row.find('.crud-checkbox').length;
            let checked = $row.find('.crud-checkbox:checked').length;
            $row.find('td:first-child input[type="checkbox"]').prop('checked', total === checked);
        });
    }

    function updateMasterCheckbox() {
        let total = $('#subModuleTbl tbody input[type="checkbox"]').length;
        let checked = $('#subModuleTbl tbody input[type="checkbox"]:checked').length;
        $('#checkAllRows').prop('checked', total === checked);
    }


        $('#addRoleForm').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status === 'success') {
                            role_filter();
                            toastr.success(response.message);
                            $('#add_roleModal').modal('hide');
                        } else {
                            toastr.warning(response.message || 'Something went wrong.');
                        }
                    },
                    error: function(xhr) {
                         if (xhr.status === 422 && xhr.responseJSON?.message) { 
                        let errors = xhr.responseJSON.message;
                        for (let field in errors) {
                            if (errors.hasOwnProperty(field)) {
                                errors[field].forEach(msg => {
                                    toastr.error(msg);
                                });
                            }
                        }
                        } else {
                            let errMsg = xhr.responseJSON?.message || 'An error occurred while creating a role.';
                            toastr.error(errMsg);
                        }
                    }
                });
            });

            $('#editRoleForm').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status === 'success') {
                            role_filter();
                            toastr.success(response.message);
                            $('#edit_roleModal').modal('hide');
                        } else {
                            toastr.warning(response.message || 'Something went wrong.');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON?.message) { 
                        let errors = xhr.responseJSON.message;
                        for (let field in errors) {
                            if (errors.hasOwnProperty(field)) {
                                errors[field].forEach(msg => {
                                    toastr.error(msg);
                                });
                            }
                        }
                        } else {
                            let errMsg = xhr.responseJSON?.message || 'An error occurred while creating a role.';
                            toastr.error(errMsg);
                        }
                    }
                });
            });

            $('#editRolePermissionForm').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status === 'success') {
                            role_filter();
                            toastr.success(response.message);
                            $('#edit_role_permissionModal').modal('hide');
                        } else {
                            toastr.warning(response.message || 'Something went wrong.');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'An unexpected error occurred.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        toastr.error(errorMessage);
                        $('#edit_user_permissionModal').modal('hide');
                    }
                });
            });
        });

        function roleEdit(id) {

            $('#editRoleForm')[0].reset();
            $('#edit_role_div').empty();

            $.ajax({
                url: '{{ route('get-role-details') }}',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: 'GET',
                data: {
                    role_id: id
                },
                success: function(response) {
                    
                    $('#edit_role_id').val(response.role.id);
                    $('#edit_role_name').val(response.role.role_name); 
                    if(response.role.data_access_level){
                        $('#edit_data_access').val(response.role.data_access_level.id).trigger('change');           
                    }else{
                        $('#edit_data_access').val('').trigger('change');          
                    }
                    if (response.role.status == 1) {
                        $('#edit_role_status').val('1').trigger('change');
                    } else {
                        $('#edit_role_status').val('0').trigger('change');
                    }
                    $('#edit_roleModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Failed to get role details');
                }
            });
        }
        function permissionEdit(id) {
            $('#editRolePermissionForm')[0].reset();
            $.ajax({
                url: '{{ route('get-role-permission-details') }}',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: 'GET',
                data: {
                    role_permission_id: id
                },
                success: function(response) {
                    $('#edit_role_permission_id').val(response.role_permission.id);
                    $('input[name="edit_permission_ids[]"]').prop('checked', false);

                    if (response.role_permission.role_permission_ids) {
                        const selectedIds = response.role_permission.role_permission_ids.split(',');

                        $('input[name="edit_permission_ids[]"]').each(function () {
                            if (selectedIds.includes($(this).val())) {
                                $(this).prop('checked', true);
                            }
                        });

                        $('tbody tr').each(function () {
                            let $row = $(this);
                            let rowCheckboxes = $row.find('.crud-checkbox');
                            let allChecked = rowCheckboxes.length === rowCheckboxes.filter(':checked').length;
                            $row.find('td:first-child input[type="checkbox"]').prop('checked', allChecked);
                        });

                        $('.column-checkbox').each(function () {
                            let crudId = $(this).data('crud');
                            let columnCheckboxes = $(`.crud-checkbox[data-crud-id="${crudId}"]`);
                            let allChecked = columnCheckboxes.length === columnCheckboxes.filter(':checked').length;
                            $(this).prop('checked', allChecked);
                        });

                        const total = $('#subModuleTbl tbody input[type="checkbox"]').length;
                        const checked = $('#subModuleTbl tbody input[type="checkbox"]:checked').length;
                        $('#checkAllRows').prop('checked', total === checked);
                    }

                    $('#edit_role_permissionModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Failed to get role permission details');
                }
            });
        }


        function role_filter() {

            let status_filter = $('#status_filter').val();
            let sortby_filter = $('#sortby_filter').val();

            $.ajax({
                url: '{{ route('role-filter') }}',
                method: 'GET',
                data: {
                    status: status_filter,
                    sort_by: sortby_filter
                },
                success: function(response) {
                    if (response.status === 'success') {
                        let tbody = '';
                        $.each(response.roles, function(i, role) {

                            let role_name = role.role_name;   
                            let data_access_level = role.data_access_level 
                                                    ? role.data_access_level.access_name 
                                                    : 'No Specified Access';
                            let statusBadge = (role.status === 1) ?
                                '<span class="badge badge-success"><i class="ti ti-point-filled me-1"></i>Active</span>' :
                                '<span class="badge badge-danger"> <i class="ti ti-point-filled me-1"></i>Inactive</span>';

                            let action =
                                `    <div class="action-icon d-inline-flex"> <a href="#" class="me-2" onclick="permissionEdit(${role.id})"><i class="ti ti-shield"></i></a>  
                                                <a href="#" class="me-2" onclick="roleEdit(${role.id})"><i class="ti ti-edit"></i></a></div>`;
                            if (response.permission.includes('Read')) {
                                tbody += `
                            <tr class="text-center"> 
                            <td>${role_name}</td>  
                            <td>${data_access_level}</td>
                            <td>${statusBadge}</td>  `;
                            if (response.permission.includes('Update')) {
                                tbody += `<td class="text-center">${action}</td>`;
                            } 
                              tbody += `</tr>`;
                            }
                        });

                        $('#role_permission_table tbody').html(tbody);
                    } else {
                        toastr.warning('Failed to load users.');
                    }
                },
                error: function() {
                    toastr.error('An error occurred while filtering users.');
                }
            });
        } 
    </script>
@endpush
