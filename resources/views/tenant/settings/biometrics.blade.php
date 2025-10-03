<?php $page = 'zkteco-biometrics'; ?>
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
                                <a href="#"><i class="ti ti-smart-home"></i></a>
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
                    <a class="nav-link active" href="#"><i class="ti ti-device-ipad-horizontal-cog me-2"></i>App
                        Settings</a>
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
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Custom Fields</a>
                                <a href="{{ route('biometrics') }}"
                                    class="d-inline-flex align-items-center rounded active py-2 px-3">ZKTeco Biometrics</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="card">
                        <div class="card-body">
                            <div class="border-bottom d-flex align-items-center justify-content-between pb-3 mb-3">
                                <h4>Biometrics</h4>

                                <div>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#add_biometrics"
                                        class="btn btn-primary d-flex align-items-center"><i
                                            class="ti ti-circle-plus me-2"></i>Add Biometrics</a>
                                </div>

                            </div>
                            <div class="card-body p-0">
                                <div class="card mb-0">
                                    <div class="card-header d-flex align-items-center justify-content-between">
                                        <h6>Biometrics List</h6>
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
                                                    <th class="text-center">Name</th>
                                                    <th class="text-center">Serial Number</th>
                                                    <th class="text-center">Biotime Server URL</th>
                                                    <th class="text-center">Biotime Username</th>
                                                    <th class="text-center"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($biometrics as $bio)
                                                    <tr>
                                                        <td>
                                                            <div class="form-check form-check-md">
                                                                <input class="form-check-input" type="checkbox">
                                                            </div>
                                                        </td>
                                                        <td class="text-center">{{ $bio->name ?? '-' }}</td>
                                                        <td class="text-center">{{ $bio->serial_number ?? '-' }}</td>
                                                        <td class="text-center">{{ $bio->biotime_server_url ?? '-' }}</td>
                                                        <td class="text-center">{{ $bio->biotime_username ?? '-' }}</td>
                                                        <td class="text-center">
                                                            <div class="action-icon d-inline-flex">
                                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                                    data-bs-target="#edit_biometrics"
                                                                    data-id="{{ $bio->id }}"
                                                                    data-name="{{ $bio->name }}"
                                                                    data-serial-number="{{ $bio->serial_number }}"
                                                                    data-biotime-server-url="{{ $bio->biotime_server_url }}"
                                                                    data-biotime-username="{{ $bio->biotime_username }}"
                                                                    data-biotime-password="{{ $bio->biotime_password }}"><i
                                                                        class="ti ti-edit"></i></a>
                                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                                    data-bs-target="#delete_biometrics"
                                                                    data-id="{{ $bio->id }}"
                                                                    data-name="{{ $bio->name }}"><i
                                                                        class="ti ti-trash"></i></a>

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
    {{-- Add Biometrics --}}
    <script>
        $(document).ready(function() {
            $('#addBiometricsForm').on('submit', function(e) {
                e.preventDefault();

                const formData = {
                    name: $('#bioName').val(),
                    serial_number: $('#bioSerialNumber').val(),
                    biotime_server_url: $('#bioServerUrl').val(),
                    biotime_username: $('#bioUsername').val(),
                    biotime_password: $('#bioPassword').val()
                };

                $.ajax({
                    url: '{{ route('api.biometricsStore') }}',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message);
                        $('#addBiometricsForm')[0].reset();
                        $('#add_biometrics').modal('hide');
                        setTimeout(() => location.reload(), 1000);
                    },
                    error: function(xhr) {
                        const {
                            status,
                            responseJSON
                        } = xhr;

                        if (status === 422) {
                            Object.values(responseJSON.errors).flat().forEach(error => toastr
                                .error(error));
                        } else {
                            toastr.error(responseJSON?.message ||
                                'An unexpected error occurred.');
                        }
                    }
                });
            });

            // Edit Biometrics
            $('#edit_biometrics').on('show.bs.modal', function(e) {
                const button = $(e.relatedTarget);
                const modal = $(this);

                modal.find('#editBiometricId').val(button.data('id'));
                modal.find('#editBioName').val(button.data('name'));
                modal.find('#editBioSerialNumber').val(button.data('serial-number'));
                modal.find('#editBioServerUrl').val(button.data('biotime-server-url'));
                modal.find('#editBioUsername').val(button.data('biotime-username'));
                modal.find('#editBioPassword').val(button.data('biotime-password'));
            });

            $('#editBiometricsForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#editBiometricId').val();

                const formData = {
                    name: $('#editBioName').val(),
                    serial_number: $('#editBioSerialNumber').val(),
                    biotime_server_url: $('#editBioServerUrl').val(),
                    biotime_username: $('#editBioUsername').val(),
                    biotime_password: $('#editBioPassword').val()
                };

                $.ajax({
                    url: '{{ route('api.biometricsUpdate', ':id') }}'.replace(':id', id),
                    type: 'PUT',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message);
                        $('#edit_biometrics').modal('hide');
                        setTimeout(() => location.reload(), 1000);
                    },
                    error: function(xhr) {
                        const {
                            status,
                            responseJSON
                        } = xhr;

                        if (status === 422) {
                            Object.values(responseJSON.errors).flat().forEach(error => toastr
                                .error(error));
                        } else {
                            toastr.error(responseJSON?.message ||
                                'An unexpected error occurred.');
                        }
                    }
                });
            });

            // Delete Biometrics
            let biometricIdToDelete = null;

            $('#delete_biometrics').on('show.bs.modal', function(e) {
                const button = $(e.relatedTarget);
                biometricIdToDelete = button.data('id');
                $('#biometricsPlaceholder').text(button.data('name'));
            });

            $('#biometricsConfirmDeleteBtn').on('click', function() {
                if (!biometricIdToDelete) return;

                $.ajax({
                    url: '{{ route('api.biometricsDestroy', ':id') }}'.replace(':id', biometricIdToDelete),
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message);
                        $('#delete_biometrics').modal('hide');
                        setTimeout(() => location.reload(), 1000);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message ||
                            'An unexpected error occurred.');
                    }
                });
            });
        });
    </script>
@endpush
