<?php $page = 'company-settings'; ?>
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
                            <li class="breadcrumb-item">
                                Administration
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Company Settings</li>
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
                <li class="nav-item">
                    <a class="nav-link active" href="#"><i class="ti ti-building me-2"></i>Company
                        Settings</a>
                </li>
            </ul>
            <div class="row">
                <div class="col-xl-3 theiaStickySidebar">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column list-group settings-list">
                                <a href="{{ route('company-settings') }}"
                                    class="d-inline-flex align-items-center rounded active py-2 px-3">Company
                                    Information</a>
                                <a href="{{ route('attendance-settings') }}"
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Attendance
                                    Settings</a>
                                <a href="{{ route('approval-steps') }}"
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Approval Settings</a>
                                <a href="{{ route('leave-type') }}"
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Leave Type</a>
                                <a href="{{ route('custom-fields') }}"
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Custom Fields</a>
                                <a href="{{ route('biometrics') }}"
                                    class="d-inline-flex align-items-center rounded  py-2 px-3">ZKTeco Biometrics</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="card">
                        <div class="card-body">
                            <div class="border-bottom mb-3 pb-3">
                                <h4>Company Information</h4>
                            </div>

                            <!-- Company Information Form -->
                            <form id="companyInfoForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="tenant_name" class="form-label">Company Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="tenant_name" name="tenant_name"
                                            value="{{ $tenant->tenant_name }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tenant_email" class="form-label">Company Email</label>
                                        <input type="email" class="form-control" id="tenant_email" name="tenant_email"
                                            value="{{ $tenant->tenant_email }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="tenant_address" class="form-label">Company Address</label>
                                        <textarea class="form-control" id="tenant_address" name="tenant_address"
                                            rows="3">{{ $tenant->tenant_address }}</textarea>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-2"></i>Save Changes
                                    </button>
                                </div>
                            </form>

                            <hr class="my-4">

                            <!-- Company Code Section -->
                            <div class="border-bottom mb-3 pb-3">
                                <h4>Company Code</h4>
                                <p class="text-muted">This code is used to identify your company when logging in. Change it
                                    with caution.</p>
                            </div>

                            <form id="tenantCodeForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="current_tenant_code" class="form-label">Current Company Code</label>
                                        <input type="text" class="form-control" id="current_tenant_code"
                                            value="{{ $tenant->tenant_code }}" disabled>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tenant_code" class="form-label">New Company Code <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control text-uppercase" id="tenant_code"
                                            name="tenant_code" placeholder="Enter new company code"
                                            title="Only letters, numbers, dashes and underscores" required>
                                        <small class="text-muted">Only letters, numbers, dashes (-) and underscores (_) are
                                            allowed</small>
                                    </div>
                                </div>
                                <div class="alert alert-warning d-flex align-items-center" role="alert">
                                    <i class="ti ti-alert-triangle me-2"></i>
                                    <div>
                                        <strong>Warning:</strong> Changing the company code will affect how users log in to
                                        the system. Make sure to notify all users about this change.
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="ti ti-edit me-2"></i>Update Company Code
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- /Page Wrapper -->
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Setup AJAX with CSRF token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Company Information Form
            $('#companyInfoForm').on('submit', function (e) {
                e.preventDefault();
                console.log('Company info form submitted');

                const submitBtn = $(this).find('button[type="submit"]');
                const originalBtnText = submitBtn.html();

                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...'
                );

                $.ajax({
                    url: '{{ route('update-company-info') }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (response) {
                        console.log('Success response:', response);
                        toastr.success(response.message || 'Company information updated successfully', 'Success');
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', xhr, status, error);
                        console.error('Response text:', xhr.responseText);
                        const errorMessage = xhr.responseJSON?.message || 'Failed to update company information';
                        toastr.error(errorMessage, 'Error');
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                });
            });

            // Tenant Code Form
            $('#tenantCodeForm').on('submit', function (e) {
                e.preventDefault();
                console.log('Tenant code form submitted');

                const form = $(this);

                if (confirm('Are you sure you want to change the company code? This will affect how users log in. All users will need to use the new code.')) {
                    const submitBtn = form.find('button[type="submit"]');
                    const originalBtnText = submitBtn.html();

                    submitBtn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Updating...'
                    );

                    console.log('Sending request to update tenant code');

                    $.ajax({
                        url: '{{ route('update-tenant-code') }}',
                        type: 'POST',
                        data: form.serialize(),
                        dataType: 'json',
                        success: function (response) {
                            console.log('Tenant code response:', response);
                            toastr.success(response.message || 'Company code updated successfully', 'Success');
                            // Update the current code display
                            if (response.data && response.data.tenant_code) {
                                $('#current_tenant_code').val(response.data.tenant_code);
                                $('#tenant_code').val('');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Tenant code error:', xhr, status, error);
                            console.error('Response text:', xhr.responseText);
                            const errorMessage = xhr.responseJSON?.message || 'Failed to update company code';
                            toastr.error(errorMessage, 'Error');
                        },
                        complete: function () {
                            submitBtn.prop('disabled', false).html(originalBtnText);
                        }
                    });
                }
            });

            // Convert tenant code to uppercase on input
            $('#tenant_code').on('input', function () {
                this.value = this.value.toUpperCase();
            });
        });
    </script>
@endpush