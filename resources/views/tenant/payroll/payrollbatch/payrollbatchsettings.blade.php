<?php $page = 'users'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Payroll Batch Settings</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                               Payroll
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Payroll Batch Settings</li>
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
                        <a href="#" data-bs-toggle="modal" data-bs-target="#create_payroll_batch"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Create Payroll Batch</a> 
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
                        <h5 class="mb-0">Payroll Batch Settings</h5> 
                    </div>
                    <div class="card-body p-0">
                        <div class="custom-datatable-filter table-responsive">
                            <table class="table datatable" id="payroll_batch_settings_table">
                                <thead class="thead-light">
                                    <tr> 
                                        <th>Name</th>
                                        <th class="text-center">Batch Employee Count</th>  
                                        @if (in_array('Update', $permission) || in_array('Delete',$permission))
                                            <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="payrollBatchSettingsTableBody">
                                    @if (in_array('Read', $permission))
                                        @foreach ($payrollBatchSettings as $pbs)
                                             <tr id="pbs_row_{{ $pbs->id }}">
                                                <td>{{ $pbs->name ?? null }}</span>
                                                </td>
                                                <td class="text-center"> 
                                                    {{$pbs->batch_users_count}}
                                                </td> 
                                                @if (in_array('Update', $permission) || in_array('Delete',$permission))
                                                    <td class="text-center">
                                                        <div class="action-icon d-inline-flex">
                                                            @if(in_array('Update',$permission))
                                                            <a href="#" class="me-2" data-bs-toggle="modal"
                                                                data-bs-target="#edit_pbsettings" data-id="{{ $pbs->id }}" 
                                                                data-name="{{$pbs->name}}"><i
                                                                    class="ti ti-edit"></i></a>
                                                            @endif
                                                            @if(in_array('Delete',$permission))
                                                            <a href="#" class="btn-delete" data-id="{{ $pbs->id }}"
                                                                data-name="{{ $pbs->name }}"><i
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
    @component('components.modal-popup')
    @endcomponent
    @endsection

    @push('scripts')

    <script>
    $(document).ready(function() {
        $('#createPayrollBatchForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '{{ route("payroll-batch-settings-store") }}',
                method: 'POST',
                data: {
                    batch_name: $('#batch_name').val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message, 'Success');
                        $('#create_payroll_batch').modal('hide');
                        $('#createPayrollBatchForm')[0].reset();
                        location.reload();
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
                        toastr.error(xhr.responseJSON.message || 'Forbidden.');
                    } else {
                        toastr.error('An unexpected error occurred.');
                    }
                }
            });
        });
    $(document).on('click', '[data-bs-target="#edit_pbsettings"]', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        $('#edit_pbsettings_id').val(id);
        $('#edit_batch_name').val(name);
    }); 
    $('#editPbSettingsForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route("payroll-batch-settings-update") }}',  
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: $('#edit_pbsettings_id').val(),
                batch_name: $('#edit_batch_name').val()
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Updated');
                    $('#edit_pbsettings').modal('hide'); 
                    location.reload();
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
                    toastr.error(xhr.responseJSON.message || 'Forbidden.');
                } else {
                    toastr.error('An unexpected error occurred.');
                }
            }
        });
    });

    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        $('#delete_pbsettings_id').val(id); 
        $('#delete_pbsettings_name').text(name); 
        $("#delete_pbsettings").modal('show');
    });
 
    $('#deletePbSettingsForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#delete_pbsettings_id').val();
        $.ajax({
            url: '{{ route("payroll-batch-settings-delete") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Deleted');
                    $('#delete_pbsettings').modal('hide'); 
                    location.reload();
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
                    toastr.error(xhr.responseJSON.message || 'Forbidden.');
                } else {
                    toastr.error('An unexpected error occurred.');
                }
            }
        });
    });

    });

    </script>
    @endpush
