<?php $page = 'employees'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Salary Record</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item"> {{ $user->personalInformation->first_name }}'s
                                Salary Record
                            </li>

                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                   @if (in_array('Export', $permission))
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
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                            class="ti ti-file-type-xls me-1"></i>Download Template</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endif
                    @if (in_array('Create', $permission))
                    <div class="d-flex gap-2 mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_salary"
                            data-user-id="{{ $user->id }}"
                            class="btn btn-primary d-flex align-items-center addSalaryRecord">
                            <i class="ti ti-circle-plus me-2"></i>Add Salary
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
            <!-- /Breadcrumb -->

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Salary Record</h5>
                     
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter" onchange="filter()">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group me-2">
                            <select name="salaryType_filter" id="salaryType_filter" class="select2 form-select" onchange="filter()">
                                <option value="" selected>All Salary Types</option>
                                <option value="monthly_fixed">Monthly Fixed</option>
                                <option value="daily_rate">Daily Rate</option>
                                <option value="hourly_rate">Hourly Rate</option> 
                            </select>
                        </div>   
                         <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select" onchange="filter()">
                                <option value="" selected>All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option> 
                            </select>
                        </div>   
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Emp ID</th>
                                    <th>Name</th>
                                    <th class="text-center">Basic Salary</th>
                                    <th class="text-center">Salary Type</th>
                                    <th class="text-center">Effective Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Encoded By</th>
                                    <th class="text-center">Remarks</th>
                                    @if (in_array('Update', $permission) || in_array('Delete',$permission))
                                    <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="salaryRecordTableBody">
                                @foreach ($salaryRecords as $salaryRecord)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td><a
                                                href="{{ url('employee-details') }}">{{ $user->employmentDetail->employee_id }}</a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{ url('employee-details') }}" class="avatar avatar-md"
                                                    data-bs-toggle="modal" data-bs-target="#view_details"><img
                                                        src="{{ asset('storage/' . $user->personalInformation->profile_picture) }}"
                                                        class="img-fluid rounded-circle" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="{{ url('employee-details') }}"
                                                            data-bs-toggle="modal" data-bs-target="#view_details">
                                                            {{ $user->personalInformation->last_name }}
                                                            {{ $user->personalInformation->suffix }},
                                                            {{ $user->personalInformation->first_name }}
                                                            {{ $user->personalInformation->middle_name }}</a></p>
                                                    <span class="fs-12"></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">{{ $salaryRecord->basic_salary }}</td>
                                        <td class="text-center">
                                            @if ($salaryRecord->salary_type == 'monthly_fixed')
                                                Monthly Fixed
                                            @elseif ($salaryRecord->salary_type == 'daily_rate')
                                                Daily Rate
                                            @elseif ($salaryRecord->salary_type == 'hourly_rate')
                                                Hourly Rate
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $salaryRecord->effective_date->format('F d, Y') }}</td>
                                        <td class="text-center">
                                            <span
                                                class="badge d-inline-flex align-items-center badge-xs
                                                {{ $salaryRecord->is_active == 1 ? 'badge-success' : 'badge-danger' }}">
                                                <i class="ti ti-point-filled me-1"></i>
                                                {{ $salaryRecord->is_active == 1 ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $salaryRecord->creator_name }}</td>
                                        <td class="text-center">{{ $salaryRecord->remarks ?? 'N/A' }}</td>
                                        @if (in_array('Update', $permission) || in_array('Delete',$permission))
                                        <td class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                @if (in_array('Update', $permission))
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_salary" data-id="{{ $salaryRecord->id }}"
                                                    data-user-id="{{ $salaryRecord->user_id }}"
                                                    data-basic-salary="{{ $salaryRecord->basic_salary }}"
                                                    data-effective-date="{{ $salaryRecord->effective_date->format('Y-m-d') }}"
                                                    data-is-active="{{ $salaryRecord->is_active }}"
                                                    data-remarks="{{ $salaryRecord->remarks }}"
                                                    data-salary-type="{{ $salaryRecord->salary_type }}">
                                                    <i class="ti ti-edit" title="Edit"></i></a>
                                                @endif
                                                @if (in_array('Delete',$permission))
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_salary" data-id="{{ $salaryRecord->id }}"
                                                    data-user-id="{{ $salaryRecord->user_id }}">
                                                    <i class="ti ti-trash" title="Delete"></i>
                                                </a>
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

      @include('layout.partials.footer-company')

    </div> 
    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script src="{{ asset('build/js/employeedetails/salary/salary.js') }}"></script>
    <script>
      function filter() {
        const dateRange = $('#dateRange_filter').val();
        const salaryType = $('#salaryType_filter').val();
        const status = $('#status_filter').val();

        $.ajax({
            url: '{{ route('salaryRecordFilter') }}',
            type: 'GET',
            data: {
                salaryType,
                dateRange,
                status
            },
            success: function (response) {
                if (response.status === 'success') {
                    $('#salaryRecordTableBody').html(response.html); 
                } else {
                    toastr.error(response.message || 'Something went wrong.');
                }
            },
            error: function (xhr) {
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
@endpush
