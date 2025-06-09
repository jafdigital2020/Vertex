<?php $page = 'users'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Users</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Administration
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Users</li>
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
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- Performance Indicator list -->
            <div class="card">
             <div class="card-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h5 class="mb-0">Users List</h5> 
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-group">
                                <select name="role_filter" id="role_filter" class="select2 form-select" onchange="user_filter()">
                                    <option value="" selected>All Roles</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <select name="status_filter" id="status_filter" class="select2 form-select" onchange="user_filter()">
                                    <option value="" selected>All Statuses</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <select name="sortby_filter" id="sortby_filter" class="select2 form-select" onchange="user_filter()">
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
                        <table class="table datatable" id="user_permission_table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Created Date</th>
                                    <th>Updated Date</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    @if (in_array('Update', $permission))  
                                    <th>Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody> 
                             @if (in_array('Read', $permission)) 
                                @foreach ($users as $user)
                                    <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md avatar-rounded">
                                                <img src="{{ URL::asset('build/img/users/user-32.jpg') }}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">{{$user->personalInformation->first_name}} {{$user->personalInformation->last_name}} </a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{$user->email}}</td>
                                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $user->updated_at->format('Y-m-d') }}</td>
                                    <td>
                                        <span class=" badge badge-md p-2 fs-10 badge-pink-transparent"> {{$user->userPermission->role->role_name ?? null}}</span>
                                    </td> 
                                    <td>  
                                            @if ($user->employmentDetail->status == 1)
                                                <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i> Active
                                                </span>
                                            @else
                                                <span class="badge badge-danger d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i> Inactive
                                                </span>
                                            @endif  
                                    </td> 
                                    @if (in_array('Update', $permission))  
                                    <td class="text-center">
                                        <div class="action-icon d-inline-flex">
                                           <a href="#" class="me-2" onclick="user_permissionEdit({{$user->userPermission->id}})"><i class="ti ti-shield"></i></a>  
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
    <div class="modal fade" id="edit_user_permissionModal">
            <div class="modal-dialog modal-dialog-centered modal-lg w-100">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title ">Edit User Permission</h4>
                        <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                    <form action="{{route('edit-user-permission')}}" id="editUserPermissionForm" method="POST"> 
                        @csrf
                        <input type="hidden" name="edit_user_permission_id" id="edit_user_permission_id" class="form-control">
                        <div class="modal-body pb-0"> 
                            <div style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-bordered" >
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
                                <tbody id="edit_">
                                    
                                    @foreach ($sub_modules as $s_mod)
                                        <tr> 
                                            <td>{{$s_mod->module->module_name}}/{{$s_mod->sub_module_name}}</td>
                                            @foreach ($CRUD as $crud)
                                            <td class="text-center">
                                            <input type="checkbox" name="edit_user_permission_ids[]" value="{{$s_mod->id}}-{{$crud->id}}" class="form-check-input" style="transform: scale(1.5); transform-origin: center;">
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
                            <button type="submit" class="btn btn-primary">Update User Permission</button>
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
    <!-- /Page Wrapper -->
    @component('components.modal-popup')
    @endcomponent
@endsection



@push('scripts')

    <script>  
 
    $(document).ready(function() {
        $('#editUserPermissionForm').on('submit', function(e) {
            e.preventDefault();  

            let form = $(this);
            let url = form.attr('action');
            let formData = form.serialize();

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message); 
                        $('#edit_user_permissionModal').modal('hide'); 
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

     function user_permissionEdit(id) { 
        
        $('#editUserPermissionForm')[0].reset();

        $.ajax({
            url: '{{ route("get-user-permission-details") }}', 
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'GET',
            data: { user_permission_id: id },
            success: function(response) {     
                console.log(response);
                $('#edit_user_permission_id').val(response.user_permission.id); 
                $('input[name="edit_permission_ids[]"]').prop('checked', false); 
             
                if(response.user_permission.user_permission_ids ){
                    var selectedIds = response.user_permission.user_permission_ids.split(','); 
                    $('input[name="edit_user_permission_ids[]"]').each(function() {
                        if (selectedIds.includes($(this).val())) {
                            $(this).prop('checked', true);
                        }
                    });
                } 

                $('#edit_user_permissionModal').modal('show'); 
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Failed to get user permission details');
            }
        });

    } 

 function user_filter() {
    let role_filter = $('#role_filter').val();
    let status_filter = $('#status_filter').val();
    let sortby_filter = $('#sortby_filter').val();

    $.ajax({
        url: '{{ route("user-filter") }}',
        method: 'GET',
        data: {
            role: role_filter,
            status: status_filter,
            sort_by: sortby_filter
        },
        success: function(response) {
            if (response.status === 'success') {
 
                let tbody = '';
                $.each(response.data, function(i, user) {
                    let fullName = user.personal_information?.first_name + ' ' + user.personal_information?.last_name;
                    let email = user.email;
                    let created = new Date(user.created_at).toISOString().split('T')[0];
                    let updated = new Date(user.updated_at).toISOString().split('T')[0];
                    let role = user.user_permission?.role?.role_name ?? '';

                    console.log(user.user_permission.role);
                    let statusBadge = (user.user_permission?.status === 1)
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-danger">Inactive</span>';
                    let action = `<a href="#" onclick="user_permissionEdit(${user.user_permission?.id})"><i class="ti ti-shield"></i></a>`;

                    tbody += `
                        <tr>
                            <td><input class="form-check-input" type="checkbox"></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="avatar avatar-md avatar-rounded">
                                        <img src="{{ URL::asset('build/img/users/user-32.jpg') }}" alt="img">
                                    </a>
                                    <div class="ms-2"><h6 class="fw-medium"><a href="#">${fullName}</a></h6></div>
                                </div>
                            </td>
                            <td>${email}</td>
                            <td>${created}</td>
                            <td>${updated}</td>
                            <td><span class="badge badge-pink-transparent">${role}</span></td>
                            <td>${statusBadge}</td>
                            <td class="text-center">${action}</td>
                        </tr>
                    `;
                });

                $('#user_permission_table tbody').html(tbody);
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
