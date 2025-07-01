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
                                <select name="role_filter" id="role_filter" class="select2 form-select"
                                    onchange="user_filter()">
                                    <option value="" selected>All Roles</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <select name="status_filter" id="status_filter" class="select2 form-select"
                                    onchange="user_filter()">
                                    <option value="" selected>All Statuses</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <select name="sortby_filter" id="sortby_filter" class="select2 form-select"
                                    onchange="user_filter()">
                                    <option value="" selected>All Sort By</option>
                                    <option value="ascending">Ascending</option>
                                    <option value="descending">Descending</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="last_7_days">Last 7 days</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="custom-datatable-filter table-responsive">
                            <table class="table datatable" id="user_permission_table">
                                <thead class="thead-light">
                                    <tr> 
                                        <th>Name</th>
                                        <th>Email</th> 
                                        <th class="text-center">Role</th>
                                        <th class="text-center">Data Access Level</th>
                                        <th>Status</th>
                                        @if (in_array('Update', $permission))
                                            <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (in_array('Read', $permission))
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
                                                <td>{{ $user->email }}</td> 
                                                <td class="text-center">
                                                    <span class=" badge badge-md p-2 fs-10 badge-pink-transparent">
                                                        {{ $user->userPermission->role->role_name ?? null }}</span>
                                                </td>
                                                <td class="text-center">
                                                    {{$user->userPermission->data_access_level->access_name ?? 'No Specified Access' }}
                                                </td>
                                                <td>
                                                    @if (isset($user->employmentDetail) && isset($user->employmentDetail->status))
                                                        @if ($user->employmentDetail->status == 1)
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
                                                    @else
                                                        <span class="badge badge-secondary d-inline-flex align-items-center badge-xs">
                                                            <i class="ti ti-point-filled me-1"></i> Unknown
                                                        </span>
                                                    @endif
                                                </td>
                                                @if (in_array('Update', $permission))
                                                    <td class="text-center">
                                                        <div class="action-icon d-inline-flex">
                                                            <a href="#" class="me-2"
                                                                onclick="user_permissionEdit({{ $user->userPermission->id }})"><i
                                                                    class="ti ti-shield"></i></a>
                                                            <a href="#" class="me-2"
                                                               onclick="user_data_accessEdit({{ $user->userPermission->id }})"><i
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
            <div class="modal fade" id="edit_user_permissionModal">
                <div class="modal-dialog modal-dialog-centered modal-lg w-100">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title ">Edit User Permission</h4>
                            <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                                aria-label="Close">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                        <form action="{{ route('edit-user-permission') }}" id="editUserPermissionForm" method="POST">
                            @csrf
                            <input type="hidden" name="edit_user_permission_id" id="edit_user_permission_id"
                                class="form-control">
                            <div class="modal-body pb-0">
                                <div style="max-height: 500px; overflow-y: auto;">
                                     <table class="table table-sm table-bordered" id="subModuleTbl">
                                        <thead  style="position: sticky; top: 0; z-index: 2;background: white;">
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
                                        <tbody id="edit_">
                                            @foreach ($sub_modules as $s_mod)
                                                <tr>
                                                    <td class="text-center d-flex justify-content-center"><input type="checkbox" class="form-control form-check-input"></td>
                                                    <td>{{ $s_mod->module->module_name }}/{{ $s_mod->sub_module_name }}</td>
                                                    @foreach ($CRUD as $crud)
                                                        <td class="text-center">
                                                            <input type="checkbox" name="edit_user_permission_ids[]"
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
                                <button type="submit" class="btn btn-primary">Update User Permission</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="edit_dataaccessModal">
              <div class="modal-dialog modal-dialog-centered modal-md w-100">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title ">Edit Data Access Level</h4>
                        <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                    <form action="{{ route('edit-user-data-access-level') }}" id="editUserDataAccessForm" method="POST">
                        @csrf
                         <input type="hidden" name="edit_user_data_access_id" id="edit_user_data_access_id"
                                class="form-control">
                        <div class="modal-body pb-0"> 
                            <div class="row mb-3">
                                <div class="form-group col-12">
                                    <label for="status" class="form-label d-block">Data Access Level:</label>
                                    <select name="edit_user_data_access" id="edit_user_data_access" class="select2 form-control">
                                        <option value="" selected disabled>Select Access Level</option>
                                         @foreach ($data_access as $access)
                                             <option value="{{$access->id}}">{{$access->access_name}}</option>
                                         @endforeach
                                    </select> 
                                </div>
                            </div>  
                             <div class="col-md-12" id="editbranchSelectWrapper" style="display:none;">
                                    <div class="mb-3">
                                        <label for="editbranches" class="form-label">Select Branches:</label>
                                        <select name="editbranch_id[]" id="editbranches" class="select2 form-control" multiple>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div> 
                                    <button type="button" id="editselectAllBranches" class="btn btn-sm btn-outline-primary me-1">Select All</button>
                                    <button type="button" id="editdeselectAllBranches" class="btn btn-sm btn-outline-secondary">Deselect All</button>
                            </div> 
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Data Acess</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> 
       @include('layout.partials.footer-company')
        </div>
        <!-- /Page Wrapper -->
        @component('components.modal-popup')
        @endcomponent
    @endsection

    @push('scripts')
     <script>
       $(document).ready(function () { 
            $('.select2').select2();  
             $('#edit_user_data_access').on('change', function () {
                let selectedText = $("#edit_user_data_access option:selected").text().toLowerCase();
                if (selectedText === 'organization-wide access') {
                    $('#editbranchSelectWrapper').slideDown();
                } else {
                    $('#editbranchSelectWrapper').slideUp();
                    $('#editbranches').val(null).trigger('change'); 
                }
            });
        
            $('#editselectAllBranches').on('click', function () {
                let allOptions = $('#editbranches option').map(function () {
                    return $(this).val();
                }).get();
                $('#editbranches').val(allOptions).trigger('change');
            });
        
            $('#editdeselectAllBranches').on('click', function () {
                $('#editbranches').val(null).trigger('change');
            });
        });

    </script>
      <script>
        $(document).ready(function() { 

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

                  $('#editUserDataAccessForm').on('submit', function(e) {
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
                                user_filter();
                                $('#edit_dataaccessModal').modal('hide');
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
                            $('#edit_dataaccessModal').modal('hide');
                        }
                    });
                });

            });


            function user_data_accessEdit( id) {

                $('#editUserDataAccessForm')[0].reset();

                $.ajax({
                    url: '{{ route('get-user-permission-details') }}',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: 'GET',
                    data: {
                        user_permission_id: id
                    },
                    success: function(response) { 
                        $('#edit_user_data_access_id').val(response.user_permission.id); 
                        if(response.user_permission.data_access_level){
                          $('#edit_user_data_access').val(response.user_permission.data_access_level.id).trigger('change');
                        }else{
                          $('#edit_user_data_access').val('').trigger('change');
                        }
                        if(response.user_permission.user_permission_access){
                        let accessIds = response.user_permission.user_permission_access.access_ids;   
                        if (typeof accessIds === 'string') {
                            accessIds = accessIds.split(',');  
                        } 
                        $('#editbranches').val(accessIds).trigger('change');
                    }
                    
                        $('#edit_dataaccessModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('Failed to get user data access details');
                    }
                });

            }


            function user_permissionEdit(id) {

                $('#editUserPermissionForm')[0].reset();

                $.ajax({
                    url: '{{ route('get-user-permission-details') }}',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: 'GET',
                    data: {
                        user_permission_id: id
                    },
                    success: function(response) {
                        console.log(response);
                        $('#edit_user_permission_id').val(response.user_permission.id);
                        $('input[name="edit_permission_ids[]"]').prop('checked', false);

                        if (response.user_permission.user_permission_ids) {
                            var selectedIds = response.user_permission.user_permission_ids.split(',');
                            $('input[name="edit_user_permission_ids[]"]').each(function() {
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
                    url: '{{ route('user-filter') }}',
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
                                let fullName = user.personal_information?.first_name + ' ' + user
                                    .personal_information?.last_name;
                                let email = user.email; 
                                let role = user.user_permission?.role?.role_name ?? '';
                                let data_access_level = user.user_permission.data_access_level 
                                                ? user.user_permission.data_access_level.access_name 
                                                : 'No Specified Access';
                                let statusBadge = (user.user_permission?.status === 1) ?
                                    '<span class="badge badge-success">Active</span>' :
                                    '<span class="badge badge-danger">Inactive</span>';

                                let action =
                                    `   <div class="action-icon d-inline-flex"><a href="#" onclick="user_permissionEdit(${user.user_permission?.id})"><i class="ti ti-shield"></i></a> 
                                     <a href="#" class="me-2" onclick="user_data_accessEdit(${user.user_permission?.id})"><i class="ti ti-edit"></i></a></div>`;
                                if (response.permission.includes('Read')) {
                                    tbody += `
                            <tr> 
                            <td>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="avatar avatar-md avatar-rounded">
                                        <img src="{{ URL::asset('build/img/users/user-32.jpg') }}" alt="img">
                                    </a>
                                    <div class="ms-2"><h6 class="fw-medium"><a href="#">${fullName}</a></h6></div>
                                </div>
                            </td>
                            <td>${email}</td> 
                            <td class="text-center"><span class="badge badge-pink-transparent">${role}</span></td>
                            <td class="text-center">${data_access_level}</td>
                            <td>${statusBadge}</td>  `;
                                    if (response.permission.includes('Update')) {
                                        tbody += `<td class="text-center">${action}</td>`;
                                    }

                                    tbody += `</tr>`;
                                }
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
