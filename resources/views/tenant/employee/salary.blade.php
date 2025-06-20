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
                    <div class="d-flex gap-2 mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_salary"
                            data-user-id="{{ $user->id }}"
                            class="btn btn-primary d-flex align-items-center addSalaryRecord">
                            <i class="ti ti-circle-plus me-2"></i>Add Salary
                        </a>
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

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Salary Record</h5>
                    {{-- Search Filter --}}
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);" id="branchDropdownToggle"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">

                            </a>
                            <ul class="dropdown-menu dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1 branch-filter"
                                        data-id="" data-name="All Branches">
                                        All Branches
                                    </a>
                                </li>

                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1 branch-filter">

                                    </a>
                                </li>

                            </ul>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);" id="departmentDropdownToggle"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">

                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1 department-filter"
                                        data-id="" data-name="All Departments">All Departments</a>
                                </li>

                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1 department-filter"></a>
                                </li>

                            </ul>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);" id="designationDropdownToggle"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">

                            </a>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);" id="statusDropdownToggle"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">

                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1 status-filter"
                                        data-value="active">Active</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1 status-filter"
                                        data-value="inactive">Inactive</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <a href="javascript:void(0);" id="sortDropdownToggle"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li><a href="javascript:void(0);" class="dropdown-item rounded-1 sort-filter"
                                        data-value="recently_added">Recently Added</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item rounded-1 sort-filter"
                                        data-value="asc">Ascending</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item rounded-1 sort-filter"
                                        data-value="desc">Descending</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item rounded-1 sort-filter"
                                        data-value="last_month">Last Month</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item rounded-1 sort-filter"
                                        data-value="last_7_days">Last 7 Days</a></li>
                            </ul>
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
                                    <th>Basic Salary</th>
                                    <th>Salary Type</th>
                                    <th>Effective Date</th>
                                    <th>Status</th>
                                    <th>Encoded By</th>
                                    <th>Remarks</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
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
                                        <td>{{ $salaryRecord->basic_salary }}</td>
                                        <td>
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
                                        <td>{{ $salaryRecord->effective_date->format('F d, Y') }}</td>
                                        <td>
                                            <span
                                                class="badge d-inline-flex align-items-center badge-xs
                                                {{ $salaryRecord->is_active == 1 ? 'badge-success' : 'badge-danger' }}">
                                                <i class="ti ti-point-filled me-1"></i>
                                                {{ $salaryRecord->is_active == 1 ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ $salaryRecord->creator_name }}</td>
                                        <td>{{ $salaryRecord->remarks ?? 'N/A' }}</td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_salary" data-id="{{ $salaryRecord->id }}"
                                                    data-user-id="{{ $salaryRecord->user_id }}"
                                                    data-basic-salary="{{ $salaryRecord->basic_salary }}"
                                                    data-effective-date="{{ $salaryRecord->effective_date->format('Y-m-d') }}"
                                                    data-is-active="{{ $salaryRecord->is_active }}"
                                                    data-remarks="{{ $salaryRecord->remarks }}"
                                                    data-salary-type="{{ $salaryRecord->salary_type }}">
                                                    <i class="ti ti-edit" title="Edit"></i></a>
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_salary" data-id="{{ $salaryRecord->id }}"
                                                    data-user-id="{{ $salaryRecord->user_id }}">
                                                    <i class="ti ti-trash" title="Delete"></i>
                                                </a>
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

      @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script src="{{ asset('build/js/employeedetails/salary/salary.js') }}"></script>
@endpush
