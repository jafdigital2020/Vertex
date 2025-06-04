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
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_role"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            Roles</a>
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

            <!-- Assets Lists -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Roles List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                Status
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
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Recently Added</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Ascending</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Desending</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Last Month</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7 Days</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th class="text-center">Role</th>
                                    <th class="text-center">Created Date</th>
                                    <th class="text-center">Updated Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    <tr class="text-center">
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>{{ $role->role_name }}</td>
                                        <td>{{ $role->created_at->format('Y-m-d') }}</td>
                                        <td>{{ $role->updated_at->format('Y-m-d') }}</td>
                                        <td>
                                            @if ($role->status == 1)
                                                <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i> Active
                                                </span>
                                            @else
                                                <span class="badge badge-danger d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i> Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                     
                                            <div class="action-icon d-inline-flex"> 
                                               <a href="#" class="me-2" onclick="permissionEdit({{$role->id}})"><i class="ti ti-shield"></i></a>  
                                                <a href="#" class="me-2" onclick="roleEdit({{$role->id}})"><i class="ti ti-edit"></i></a>  
                                             
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
      <div class="modal fade" id="edit_roleModal">
        <div class="modal-dialog modal-dialog-centered modal-md w-100">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title ">Edit Role</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form action="{{route('edit-role')}}" id="editRoleForm" method="POST"> 
                    @csrf 
                    <div class="modal-body pb-0">
                        <div class="row mb-3">
                            <div class="form-group col-12 ">
                                <label for="" class="form-label d-block">Role Name:</label> 
                                <input type="hidden" name="edit_role_id" id="edit_role_id" class="form-control text-sm">
                                <input type="text" name="edit_role_name" id="edit_role_name" class="form-control text-sm">
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
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form action="{{route('edit-role-permission')}}" id="editRolePermissionForm" method="POST"> 
                    @csrf
                    <input type="hidden" name="edit_role_permission_id" id="edit_role_permission_id" class="form-control">
                    <div class="modal-body pb-0"> 
                        <div style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>  
                                    <th>Module/Sub Module</th> 
                                    <th>Create</th>
                                    <th>Read</th>
                                    <th>Update</th>
                                    <th>Delete</th>
                                    <th>Import</th>
                                    <th>Export</th>
                                </tr>
                            </thead>
                            <tbody>
                                   
                                 @foreach ($sub_modules as $s_mod)
                                     <tr> 
                                        <td>{{$s_mod->module->module_name}}/{{$s_mod->sub_module_name}}</td>
                                        @foreach ($CRUD as $crud)
                                        <td class="text-center">
                                        <input type="checkbox" name="edit_permission_ids[]" value="{{$s_mod->id}}-{{$crud->id}}" class="form-check-input" style="transform: scale(1.5); transform-origin: center;">
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


        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2014 - 2025 &copy; SmartHR.</p>
            <p>Designed &amp; Developed By <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>

    </div> 
    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script>
        function roleEdit(id) {
        $('#editRoleForm')[0].reset();
        $('#edit_role_div').empty();

        $.ajax({
            url: '{{ route("get-role-details") }}', 
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'GET',
            data: { role_id: id },
            success: function(response) {   
                $('#edit_role_id').val(response.role.id);
                $('#edit_role_name').val(response.role.role_name);
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
            url: '{{ route("get-role-permission-details") }}', 
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'GET',
            data: { role_permission_id: id },
            success: function(response) {     
                $('#edit_role_permission_id').val(response.role_permission.id); 
                $('input[name="edit_permission_ids[]"]').prop('checked', false); 

                if(response.role_permission.role_permission_ids ){
                    var selectedIds = response.role_permission.role_permission_ids.split(','); 
                    $('input[name="edit_permission_ids[]"]').each(function() {
                        if (selectedIds.includes($(this).val())) {
                            $(this).prop('checked', true);
                        }
                    });
                } 

                $('#edit_role_permissionModal').modal('show'); 
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Failed to get role permission details');
            }
        });
    } 
    </script>
@endpush
