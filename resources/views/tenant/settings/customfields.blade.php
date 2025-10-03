<?php $page = 'custom-fields'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Settings</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Settings</li>
                        </ol>
                    </nav>
                </div>
                <div class="head-icons ms-2">
                    <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-original-title="Collapse" id="collapse-header">
                        <i class="ti ti-chevrons-up"></i>
                    </a>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <ul class="nav nav-tabs nav-tabs-solid bg-transparent border-bottom mb-3">
                {{-- <li class="nav-item">
                    <a class="nav-link " href="{{ url('profile-settings') }}"><i class="ti ti-settings me-2"></i>General
                        Settings</a>
                </li> --}}
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ url('bussiness-settings') }}"><i class="ti ti-world-cog me-2"></i>Website
                        Settings</a>
                </li> --}}
                <li class="nav-item">
                    <a class="nav-link active" href="#"><i
                            class="ti ti-device-ipad-horizontal-cog me-2"></i>App Settings</a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ url('email-settings') }}"><i class="ti ti-server-cog me-2"></i>System
                        Settings</a>
                </li> --}}
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ url('payment-gateways') }}"><i
                            class="ti ti-settings-dollar me-2"></i>Financial Settings</a>
                </li> --}}
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ url('custom-css') }}"><i class="ti ti-settings-2 me-2"></i>Other
                        Settings</a>
                </li> --}}
            </ul>
            <div class="row">
                <div class="col-xl-3 theiaStickySidebar">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column list-group settings-list">
                                <a href="{{ route('attendance-settings') }}"
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Attendance Settings</a>
                                <a href="{{ route('approval-steps') }}"
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Approval Settings</a>
                                <a href="{{ route('leave-type') }}"
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Leave Type</a>
                                <a href="{{ route('custom-fields') }}"
                                    class="d-inline-flex align-items-center rounded active py-2 px-3">Custom Fields</a>
                                <a href="{{ route('biometrics') }}"
                                    class="d-inline-flex align-items-center rounded  py-2 px-3">ZKTeco Biometrics</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="card">
                        <div class="card-body">
                            <div class="border-bottom d-flex align-items-center justify-content-between pb-3 mb-3">
                                <h4>Prefix</h4>
                                @if (in_array('Create', $permission))
                                    <div>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_prefix"
                                            class="btn btn-primary d-flex align-items-center"><i
                                                class="ti ti-circle-plus me-2"></i>Add Prefix</a>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body p-0">
                                <div class="card mb-0">
                                    <div class="card-header d-flex align-items-center justify-content-between">
                                        <h6>Prefix List(Employee-ID)</h6>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="no-sort">
                                                        <div class="form-check form-check-md">
                                                            <input class="form-check-input" type="checkbox" id="select-all">
                                                        </div>
                                                    </th>
                                                    <th class="text-center">Prefix</th>
                                                    <th class="text-center">Remarks</th>
                                                    @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                                        <th class="text-center">Action</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($customFields as $cf)
                                                    <tr>
                                                        <td>
                                                            <div class="form-check form-check-md">
                                                                <input class="form-check-input" type="checkbox">
                                                            </div>
                                                        </td>
                                                        <td class="text-center">{{ $cf->prefix_name }}</td>
                                                        <td class="text-center">{{ $cf->remarks }}</td>
                                                        @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                                            <td class="text-center">
                                                                <div class="action-icon d-inline-flex">
                                                                    @if (in_array('Update', $permission))
                                                                        <a href="#" class="me-2"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#edit_prefix"
                                                                            data-id="{{ $cf->id }}"
                                                                            data-name="{{ $cf->prefix_name }}"
                                                                            data-remarks="{{ $cf->remarks }}"><i
                                                                                class="ti ti-edit"></i></a>
                                                                    @endif
                                                                    @if (in_array('Delete', $permission))
                                                                        <a href="#" class="btn-delete"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#delete_prefix"
                                                                            data-id="{{ $cf->id }}"
                                                                            data-name="{{ $cf->prefix_name }}"><i
                                                                                class="ti ti-trash"></i></a>
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
                    </div>
                </div>
            </div>
        </div>




        {{-- Footer --}}
        @include('layout.partials.footer-company')
    </div>
    <!-- /Page Wrapper -->
    @component('components.modal-popup')
    @endcomponent
@endsection


@push('scripts')
    {{-- Create Function --}}
    <script>
        $(document).ready(function() {
            $('#addPrefixForm').on('submit', function(e) {
                e.preventDefault();

                let formData = {
                    prefix_name: $('#prefixName').val(),
                    remarks: $('#prefixRemarks').val(),
                };

                $.ajax({
                    url: '/api/settings/custom-fields/create-prefix',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message);
                        $('#addPrefixForm')[0].reset();
                        $('#add_prefix').modal('hide');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            for (let field in errors) {
                                toastr.error(errors[field][0]);
                            }
                        } else if (xhr.status === 403) {
                            toastr.error(xhr.responseJSON.message || 'Forbidden');
                        } else {
                            toastr.error('An unexpected error occurred.');
                        }
                    }
                });
            });
        });
    </script>

    {{-- Edit Function  --}}
    <script>
        $(document).ready(function() {

            // 🖊️ Populate the modal fields on click
            $('[data-bs-target="#edit_prefix"]').on('click', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let remarks = $(this).data('remarks');

                $('#editPrefixForm').data('id', id); // store prefix ID on form
                $('#editPrefixName').val(name);
                $('#editPrefixRemarks').val(remarks);
            });

            // Form Submission for Edit
            $('#editPrefixForm').on('submit', function(e) {
                e.preventDefault();

                let id = $(this).data('id');
                let formData = {
                    prefix_name: $('#editPrefixName').val(),
                    remarks: $('#editPrefixRemarks').val()
                };

                $.ajax({
                    url: `/api/settings/custom-fields/update-prefix/${id}`,
                    type: 'PUT',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message);
                        $('#editPrefixForm')[0].reset();
                        $('#edit_prefix').modal('hide');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            for (let field in errors) {
                                toastr.error(errors[field][0]);
                            }
                        } else if (xhr.status === 403) {
                            toastr.error(xhr.responseJSON.message || 'Forbidden');
                        } else {
                            toastr.error('An unexpected error occurred.');
                        }
                    }
                });
            });
        });
    </script>

    {{-- Delete Function --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            // Experience Delete
            let prefixDeleteId = null;

            const prefixDeleteButtons = document.querySelectorAll('.btn-delete');
            const prefixConfirmDeleteBtn = document.getElementById('prefixConfirmDeleteBtn');
            const prefixPlaceholder = document.getElementById('prefixPlaceholder');

            // Set up the delete buttons to capture data
            prefixDeleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    prefixDeleteId = this.getAttribute('data-id');
                    const prefixName = this.getAttribute('data-name');

                    if (prefixPlaceholder) {
                        prefixPlaceholder.textContent =
                            prefixName;
                    }
                });
            });

            // Confirm delete button click event
            prefixConfirmDeleteBtn?.addEventListener('click', function() {
                if (!prefixDeleteId)
                    return; // Ensure both id is available

                fetch(`/api/settings/custom-fields/delete-prefix/${prefixDeleteId}`, {
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
                            toastr.success("Prefix deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_prefix'));
                            deleteModal.hide(); // Hide the modal

                            setTimeout(() => window.location.reload(),
                                800);
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting prefix.");
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
@endpush
