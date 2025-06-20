<?php $page = 'departments'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Departments</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Departments</li>
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
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_department"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            Department</a>
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
            @endphp

            <!-- Search Filter -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Department List</h5>
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
                                    <th>Department </th>
                                    <th>Code</th>
                                    <th>No of Employees</th>
                                    <th>Head</th>
                                    <th>Branch</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($departments as $department)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <h6 class="fw-medium">{{ $department->department_name }}
                                            </h6>
                                        </td>
                                        <td>
                                            <h6 class="fw-medium">{{ $department->department_code }}
                                            </h6>
                                        </td>
                                        <td>
                                            {{ $department->active_employees_count }}
                                        </td>
                                        <td>
                                            @if ($department->head && $department->head->personalInformation)
                                                @php
                                                    $headStatus = optional(
                                                        optional($department->head)->headOfDepartment,
                                                    )->status;
                                                    $employmentStatus = optional(
                                                        optional($department->head)->employmentDetail,
                                                    )->status;

                                                    $isInactive =
                                                        strtolower($headStatus) === 'inactive' ||
                                                        strtolower($employmentStatus) === 'inactive';
                                                @endphp

                                                <h6 class="fw-medium">
                                                    <a href="#">
                                                        {{ $department->head->personalInformation->last_name }},
                                                        {{ $department->head->personalInformation->first_name }}
                                                    </a>
                                                    @if ($isInactive)
                                                        <small class="text-danger">(Inactive)</small>
                                                    @endif
                                                </h6>
                                            @else
                                                <h6 class="fw-medium">
                                                    <a href="#">No Head Assigned</a>
                                                </h6>
                                            @endif
                                        </td>
                                        <td>
                                            <h6 class="fw-medium">
                                                <a
                                                    href="#">{{ $department->branch ? $department->branch->name : 'No branch' }}</a>
                                            </h6>
                                        </td>
                                        <td>
                                            @if ($department->status == 'active')
                                                <span
                                                    class="badge badge-success d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i> Active
                                                </span>
                                            @else
                                                <span class="badge badge-danger d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i> Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_department" data-id="{{ $department->id }}"
                                                    data-department_code="{{ $department->department_code }}"
                                                    data-department_name="{{ $department->department_name }}"
                                                    data-department_head="{{ $department->head_of_department }}"
                                                    data-branch_id="{{ $department->branch_id }}" title="Edit"><i
                                                        class="ti ti-edit"></i></a>
                                                <a href="javascript:void(0);" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_modal" data-id="{{ $department->id }}"
                                                    data-department_name="{{ $department->department_name }}"
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
            <!-- /Performance Indicator list -->

        </div>
@include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup', [
        'users' => $users,
        'branches' => $branches,
    ])
    @endcomponent
@endsection

@push('scripts')
    <script>
        document.body.dataset.selectedBranchId = "{{ $selectedBranchId ?? '' }}";
        document.body.dataset.selectedStatus = "{{ $selectedStatus ?? '' }}";
        document.body.dataset.selectedSort = "{{ $selectedSort ?? '' }}";
        document.body.dataset.selectedDepartment = "{{ $selectedDepartment ?? '' }}";
    </script>
    <script src="{{ asset('build/js/department/department.js') }}"></script>
    <!-- Filter JS -->
    <script src="{{ asset('build/js/department/filters.js') }}"></script>
@endpush
