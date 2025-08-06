<?php $page = 'users'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Assets Settings</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Assets Management
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Assets Settings</li>
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
                    </div>
                     @endif
                    @if (in_array('Create', $permission))
                    <div class="mb-2"> 
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_assets"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            Asset</a> 
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
                <div class="card-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h5 class="mb-0">Assets Settings</h5>
                        <div class="d-flex flex-wrap gap-3"> 
                            <div class="form-group">
                                <select name="status_filter" id="status_filter" class="select2 form-select"
                                    onchange="filter()">
                                    <option value="" selected>All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="broken">Broken</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="retired">Retired</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <select name="sortby_filter" id="sortby_filter" class="select2 form-select"
                                    onchange="filter()">
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
                                        <th class="text-center">Category</th>
                                        <th class="text-center">Model</th>  
                                        <th class="text-center">Manufacturer</th> 
                                        <th class="text-center">Serial Number</th> 
                                        <th class="text-center">Processor</th> 
                                        <th class="text-center">Price</th>
                                        @if (in_array('Update', $permission) || in_array('Delete',$permission))
                                            <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="assetsSettingsTableBody">
                                    @if (in_array('Read', $permission))
                                        @foreach ($assets as $asset)
                                            <tr>  
                                                <td>{{ $asset->name ?? null }}</span>
                                                </td>
                                                  <td class="text-center">
                                                    {{ $asset->category->name ?? 'NA' }}
                                                </td> 
                                                <td class="text-center">
                                                    {{ $asset->model ?? 'NA' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $asset->manufacturer ?? 'NA' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $asset->serial_number ?? 'NA' }}
                                                </td>
                                                   <td class="text-center">
                                                    {{ $asset->processor ?? 'NA' }}
                                                </td>
                                                 
                                                 <td class="text-center">
                                                    {{$asset->price}}
                                                </td>
                                                @if (in_array('Update', $permission))
                                                    <td class="text-center">
                                                        <div class="action-icon d-inline-flex">
                                                            @if(in_array('Update',$permission))
                                                            <a href="#" class="me-2" data-bs-toggle="modal"
                                                                data-bs-target="#edit_assets" data-id="{{ $asset->id }}" 
                                                                data-name="{{$asset->name}}" data-description="{{$asset->description}}" 
                                                                data-quantity="{{$asset->quantity}}" data-categoryname="{{$asset->category->id}}" 
                                                                data-price="{{$asset->price}}" data-status="{{$asset->status}}"
                                                                data-model="{{$asset->model}}" data-manufacturer="{{$asset->manufacturer}}" data-serial_number="{{$asset->serial_number}}" data-processor="{{$asset->processor}}"><i
                                                                    class="ti ti-edit"></i></a>
                                                            @endif
                                                            @if(in_array('Delete',$permission))
                                                            <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                                data-bs-target="#delete_assets" data-id="{{ $asset->id }}"
                                                                data-name="{{ $asset->name }}"><i
                                                                    class="ti ti-trash"></i></a>
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
        </div> 
       @include('layout.partials.footer-company')
        </div>
        <!-- /Page Wrapper -->
    
    @component('components.modal-popup', [
        'categories' => $categories,
    ])
    @endcomponent
    @endsection

    @push('scripts')

        <script> 

        function filter() { 
            const status = $('#status_filter').val();
            const sortBy = $('#sortby_filter').val();
            $.ajax({
                url: '{{ route('assets-settings-filter') }}',
                type: 'GET',
                data: {  
                    status, 
                    sortBy
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#assetsSettingsTableBody').html(response.html);
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
        <script>
            $(function() {
            $('#existingCategory').on('change', function() {
                if ($(this).val() === 'new') {
                $('#newCategoryInput').show();
                } else {
                $('#newCategoryInput').hide().val('');
                }
            });
               $('#edit_existingCategory').on('change', function() {
                if ($(this).val() === 'new') {
                $('#edit_newCategoryInput').show();
                } else {
                $('#edit_newCategoryInput').hide().val('');
                }
            });
            });
            $('#edit_assets').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); 
 
                var id = button.data('id');
                var name = button.data('name');
                var description = button.data('description');
                var categoryName = button.data('categoryname');
                var price = button.data('price');
                var quantity = button.data('quantity');
                var model = button.data('model');
                var manufacturer = button.data('manufacturer');
                var serial_number = button.data('serial_number');
                var processor = button.data('processor');
                var modal = $(this);
                modal.find('#edit_id').val(id);
                modal.find('#edit_name').val(name);
                modal.find('#edit_description').val(description);
                modal.find('#edit_existingCategory').val(categoryName).trigger("change");
                modal.find('#edit_price').val(price);
                modal.find('#edit_quantity').val(quantity); 
                modal.find('#edit_model').val(model);
                modal.find('#edit_manufacturer').val(manufacturer);
                modal.find('#edit_serial_number').val(serial_number);
                modal.find('#edit_processor').val(processor);
                 
                $('#edit_status').select2({
                    dropdownParent: $('#edit_assets'),
                    width: '100%',
                    minimumResultsForSearch: 0
                });
            
            });

              $('#delete_assets').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);  
                var id = button.data('id');
                var name = button.data('name'); 
                var modal = $(this);
                modal.find('#delete_assets_id').val(id);
                modal.find('#assetsPlaceholder').text(name);
            });

        </script>
         <script>

        $(document).ready(function() {
            $('#addAssetsForm').on('submit', function(e) {
                e.preventDefault();  

                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize(); 

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    success: function(response) { 
                        $('#add_assets').modal('hide'); 
                        $('#addAssetsForm')[0].reset();
                        $('#existingCategory').val('').trigger('change');   
                        toastr.success('Asset added successfully!');
                        filter();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                let $input = $('[name="' + field + '"]');
                                $input.addClass('is-invalid');
                                $input.next('.invalid-feedback').remove();  
                                $input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                            });
                        } else {
                            toastr.error('Something went wrong.');
                        }
                    }
                });
            });
 
            $('#existingCategory').on('change', function () {
                if ($(this).val() === 'new') {
                    $('#newCategoryInput').show().attr('required', true);
                } else {
                    $('#newCategoryInput').hide().val('').removeAttr('required');
                }
            });
        });
        </script>
        <script>
        $(document).ready(function () {
            $('#editAssetsForm').on('submit', function (e) {
                e.preventDefault();

                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        $('#edit_assets').modal('hide');
                        $('#editAssetsForm')[0].reset();
                        $('#edit_existingCategory').val('').trigger('change');
                        toastr.success('Asset updated successfully!');
                        filter();
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function (field, messages) {
                                let $input = $('[name="' + field + '"]');
                                $input.addClass('is-invalid');
                                $input.next('.invalid-feedback').remove();
                                $input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                            });
                        } else {
                            toastr.error('Something went wrong.');
                        }
                    }
                });
            });

            $('#edit_existingCategory').on('change', function () {
                if ($(this).val() === 'new') {
                    $('#edit_newCategoryInput').show().attr('required', true);
                } else {
                    $('#edit_newCategoryInput').hide().val('').removeAttr('required');
                }
            });
        });
        </script>
        <script>
            $('#assetsConfirmDeleteBtn').on('click', function () {
            let assetId = $('#delete_assets_id').val();
              
            $.ajax({
                url: '/assets-settings/delete',  
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: assetId
                },
                success: function (response) {
                    $('#delete_assets').modal('hide');
                    toastr.success('Asset deleted successfully!');
                    filter();
                },
                error: function (xhr) {
                    toastr.error('Failed to delete asset.');
                }
            });
        });
        </script>
    @endpush
