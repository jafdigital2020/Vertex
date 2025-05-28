<?php $page = 'designations'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Designations</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Designations</li>
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
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_designation"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            Designation</a>
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

            @php
                $selectedBranch = $branches->where('id', $selectedBranchId)->first();
                $branchLabel = $selectedBranch ? $selectedBranch->name : 'All Branches';
                $selectedDepartment = $departments->where('id', $selectedDepartmentId)->first();
                $departmentLabel = $selectedDepartment ? $selectedDepartment->department_name : ' All Departments';
            @endphp

            <!-- Search Filter  -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Designation List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);" id="branchDropdownToggle"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                {{ $branchLabel }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1 branch-filter"
                                        data-id="" data-name="All Branches">
                                        All Branches
                                    </a>
                                </li>
                                @foreach ($branches as $branch)
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1 branch-filter"
                                            data-id="{{ $branch->id }}" data-name="{{ $branch->name }}">
                                            {{ $branch->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);" id="departmentDropdownToggle"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                {{ $departmentLabel }}
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1 department-filter"
                                        data-id="" data-name="All Departments">All Departments</a>
                                </li>
                                @foreach ($departments as $department)
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1 department-filter"
                                            data-id="{{ $department->id }}"
                                            data-name="{{ $department->department_name }}">{{ $department->department_name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);" id="statusDropdownToggle"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                {{ $selectedStatus ? ucfirst($selectedStatus) : 'Status' }}
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
                                Sort By:
                                @if ($selectedSort == 'recently_added')
                                    Recently Added
                                @elseif ($selectedSort == 'asc')
                                    Ascending
                                @elseif ($selectedSort == 'desc')
                                    Descending
                                @elseif ($selectedSort == 'last_month')
                                    Last Month
                                @elseif ($selectedSort == 'last_7_days')
                                    Last 7 Days
                                @else
                                    Last 7 Days
                                @endif
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
                                    <th>Designation </th>
                                    <th>Department</th>
                                    <th>Job Description</th>
                                    <th>No of Employees</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($designations as $designation)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <h6 class="fw-medium fs-14 text-dark">{{ $designation->designation_name }}
                                            </h6>
                                        </td>
                                        <td>{{ $designation->department->department_name }}</td>
                                        <td>
                                            @if ($designation->job_description)
                                                {{ $designation->job_description }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            {{ $designation->active_employees_count }}
                                        </td>
                                        <td>
                                            <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>Active
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_designation" data-id="{{ $designation->id }}"
                                                    data-designation_name="{{ $designation->designation_name }}"
                                                    data-department_id="{{ $designation->department_id }}"
                                                    data-job_description="{{ $designation->job_description }}"
                                                    data-branch_id="{{ $designation->department->branch_id }}"><i
                                                        class="ti ti-edit"></i></a>
                                                <a href="javascript:void(0);" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_modal" data-id="{{ $designation->id }}"
                                                    data-designation_name="{{ $designation->designation_name }}"
                                                    title="Delete"><i class="ti ti-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /Search Filter -->

        </div>

        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2025 &copy; OneJAF Vertex.</p>
            <p>Designed &amp; Developed By <a href="https://jafdigital.co/" class="text-primary" target="_blank">JAF
                    Digital Group Inc.</a></p>
        </div>

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup', [
        'departments' => $departments,
        'branches' => $branches,
    ])
    @endcomponent
@endsection

@push('scripts')
    <script>
        document.body.dataset.selectedBranchId = "{{ $selectedBranchId ?? '' }}";
        document.body.dataset.selectedStatus = "{{ $selectedStatus ?? '' }}";
        document.body.dataset.selectedSort = "{{ $selectedSort ?? '' }}";
    </script>
    <script src="{{ asset('build/js/designation/designation.js') }}"></script>
    <!-- Filter JS -->
    <script src="{{ asset('build/js/department/filters.js') }}"></script>
@endpush
