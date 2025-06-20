<?php $page = 'geofence-settings'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h6 class="fw-medium d-inline-flex align-items-center mb-3 mb-sm-0"><a
                            href="{{ route('attendance-settings') }}">
                            <i class="ti ti-arrow-left me-2"></i>Attendance Settings</a>
                    </h6>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="d-flex gap-2 mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_geofence" data-user-id=""
                            class="btn btn-primary d-flex align-items-center addGeofence"><i
                                class="ti ti-circle-plus me-2"></i>Add Geofence</a>

                        <a href="#" data-bs-toggle="modal" data-bs-target="#assign_geofence"
                            class="btn btn-secondary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i>Assign Geofence
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

            <div class="row">
                <div class="col-xl-12">
                    <div>
                        <div class="tab-content custom-accordion-items">
                            <div class="tab-pane active show" id="bottom-justified-tab1" role="tabpanel">
                                <div class="accordion accordions-items-seperate" id="accordionExample">
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="contact-grids-tab p-0 mb-3">
                                            <ul class="nav nav-underline" id="myTab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link {{ request('tab') !== 'users' ? 'active' : '' }}"
                                                        id="location-tab" data-bs-toggle="tab" href="#locationTab"
                                                        role="tab" aria-controls="locationTab"
                                                        aria-selected="{{ request('tab') !== 'users' ? 'true' : 'false' }}">Locations</a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link {{ request('tab') === 'users' ? 'active' : '' }}"
                                                        id="user-tab" data-bs-toggle="tab" href="#userTab" role="tab"
                                                        aria-controls="userTab"
                                                        aria-selected="{{ request('tab') === 'users' ? 'true' : 'false' }}">Users</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="tab-content" id="geofenceTabContent">
                                            {{-- Location Tab --}}
                                            <div class="tab-pane fade {{ request('tab') !== 'users' ? 'show active' : '' }}"
                                                id="locationTab" role="tabpanel">
                                                <div class="row">
                                                    <div class="col-md-12 d-flex">
                                                        <div class="card flex-fill mb-0">
                                                            <div class="card-body">
                                                                <div class="row align-items-center">
                                                                    <table class="table datatable">
                                                                        <thead class="thead-light">
                                                                            <tr>
                                                                                <th class="no-sort">
                                                                                    <div class="form-check form-check-md">
                                                                                        <input class="form-check-input"
                                                                                            type="checkbox" id="select-all">
                                                                                    </div>
                                                                                </th>
                                                                                <th>Name</th>
                                                                                <th>Branch</th>
                                                                                <th>Address</th>
                                                                                <th>Radius</th>
                                                                                <th>Created By</th>
                                                                                <th>Edited By</th>
                                                                                <th>Expiration Date</th>
                                                                                <th>Status</th>
                                                                                <th></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($geofences as $geofence)
                                                                                <tr>
                                                                                    <td>
                                                                                        <div
                                                                                            class="form-check form-check-md">
                                                                                            <input class="form-check-input"
                                                                                                type="checkbox">
                                                                                        </div>
                                                                                    </td>
                                                                                    <td>{{ $geofence->geofence_name }}</td>
                                                                                    <td>{{ $geofence->branch->name ?? 'N/A' }}
                                                                                    </td>
                                                                                    <td>{{ $geofence->geofence_address }}
                                                                                    </td>
                                                                                    <td>{{ $geofence->geofence_radius }}
                                                                                    </td>
                                                                                    <td>{{ $geofence->creator_name }}</td>
                                                                                    <td>{{ $geofence->updater_name ?? 'N/A' }}
                                                                                    </td>
                                                                                    <td>{{ $geofence->expiration_date ?? 'No Expiration' }}
                                                                                    </td>
                                                                                    <td> <span
                                                                                            class="badge d-inline-flex align-items-center badge-xs
                                                                                        {{ $geofence->status === 'inactive' ? 'badge-danger' : 'badge-success' }}">
                                                                                            <i
                                                                                                class="ti ti-point-filled me-1"></i>{{ ucfirst($geofence->status) }}
                                                                                        </span></td>
                                                                                    <td>
                                                                                        <div
                                                                                            class="action-icon d-inline-flex">
                                                                                            {{-- Edit --}}
                                                                                            <a href="#"
                                                                                                class="me-2 btn-edit"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#edit_geofence"
                                                                                                data-id="{{ $geofence->id }}"
                                                                                                data-geofence-name="{{ $geofence->geofence_name }}"
                                                                                                data-geofence-address="{{ $geofence->geofence_address }}"
                                                                                                data-latitude="{{ $geofence->latitude }}"
                                                                                                data-longitude="{{ $geofence->longitude }}"
                                                                                                data-geofence-radius="{{ $geofence->geofence_radius }}"
                                                                                                data-expiration-date="{{ $geofence->expiration_date }}"
                                                                                                data-branch-id="{{ $geofence->branch_id }}"
                                                                                                data-status="{{ $geofence->status }}">
                                                                                                <i class="ti ti-edit"
                                                                                                    title="Edit"></i></a>
                                                                                            {{-- Delete --}}
                                                                                            <a href="#"
                                                                                                class="me-2 btn-delete"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#delete_geofence"
                                                                                                data-id="{{ $geofence->id }}"
                                                                                                data-geofence-name="{{ $geofence->geofence_name }}">
                                                                                                <i class="ti ti-trash"
                                                                                                    title="Delete"></i></a>
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

                                            @php
                                                $selectedBranch = $branches->where('id', $selectedBranchId)->first();
                                                $branchLabel = $selectedBranch ? $selectedBranch->name : 'All Branches';

                                                $selectedDepartment = $departments
                                                    ->where('id', $selectedDepartmentId)
                                                    ->first();
                                                $departmentLabel = $selectedDepartment
                                                    ? $selectedDepartment->department_name
                                                    : ' All Departments';

                                                $selectedDesignation = $designations
                                                    ->where('id', $selectedDesignationId)
                                                    ->first();
                                                $designationLabel = $selectedDesignation
                                                    ? $selectedDesignation->designation_name
                                                    : ' All Designations ';
                                            @endphp

                                            {{-- User Tab --}}
                                            <div class="tab-pane fade {{ request('tab') === 'users' ? 'show active' : '' }}"
                                                id="userTab" role="tabpanel">
                                                <div class="row">
                                                    <div class="col-md-12 d-flex">
                                                        <div class="card flex-fill mb-0">
                                                            <div
                                                                class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                                                                <h5>Employee List</h5>
                                                                {{-- Search Filter --}}
                                                                <div
                                                                    class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                                                                    <div class="dropdown me-3">
                                                                        <a href="javascript:void(0);"
                                                                            id="branchDropdownToggle"
                                                                            class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                                                            data-bs-toggle="dropdown">
                                                                            {{ $branchLabel }}
                                                                        </a>
                                                                        <ul class="dropdown-menu dropdown-menu-end p-3">
                                                                            <li>
                                                                                <a href="javascript:void(0);"
                                                                                    class="dropdown-item rounded-1 branch-filter"
                                                                                    data-id=""
                                                                                    data-name="All Branches">
                                                                                    All Branches
                                                                                </a>
                                                                            </li>
                                                                            @foreach ($branches as $branch)
                                                                                <li>
                                                                                    <a href="javascript:void(0);"
                                                                                        class="dropdown-item rounded-1 branch-filter"
                                                                                        data-id="{{ $branch->id }}"
                                                                                        data-name="{{ $branch->name }}">
                                                                                        {{ $branch->name }}
                                                                                    </a>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                    <div class="dropdown me-3">
                                                                        <a href="javascript:void(0);"
                                                                            id="departmentDropdownToggle"
                                                                            class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                                                            data-bs-toggle="dropdown">
                                                                            {{ $departmentLabel }}
                                                                        </a>
                                                                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                                                                            <li>
                                                                                <a href="javascript:void(0);"
                                                                                    class="dropdown-item rounded-1 department-filter"
                                                                                    data-id=""
                                                                                    data-name="All Departments">All
                                                                                    Departments</a>
                                                                            </li>
                                                                            @foreach ($departments as $department)
                                                                                <li>
                                                                                    <a href="javascript:void(0);"
                                                                                        class="dropdown-item rounded-1 department-filter"
                                                                                        data-id="{{ $department->id }}"
                                                                                        data-name="{{ $department->department_name }}">{{ $department->department_name }}</a>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                    <div class="dropdown me-3">
                                                                        <a href="javascript:void(0);"
                                                                            id="designationDropdownToggle"
                                                                            class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                                                            data-bs-toggle="dropdown">
                                                                            {{ $designationLabel }}
                                                                        </a>
                                                                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                                                                            <li>
                                                                                <a href="javascript:void(0);"
                                                                                    class="dropdown-item rounded-1 designation-filter"
                                                                                    data-id=""
                                                                                    data-name="All Designations">All
                                                                                    Designations</a>
                                                                            </li>
                                                                            @foreach ($designations as $designation)
                                                                                <li>
                                                                                    <a href="javascript:void(0);"
                                                                                        class="dropdown-item rounded-1 designation-filter"
                                                                                        data-id="{{ $designation->id }}"
                                                                                        data-name="{{ $designation->designation_name }}">{{ $designation->designation_name }}</a>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                    <div class="dropdown me-3">
                                                                        <a href="javascript:void(0);"
                                                                            id="typeDropdownToggle"
                                                                            class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                                                            data-bs-toggle="dropdown">
                                                                            {{ $selectedAssignmentType ? ucfirst($selectedAssignmentType) : 'Assignment Type' }}
                                                                        </a>
                                                                        <ul class="dropdown-menu dropdown-menu-end p-3">
                                                                            <li>
                                                                                <a href="javascript:void(0);"
                                                                                    class="dropdown-item rounded-1 assignment-type-filter"
                                                                                    data-value="manual">Manual</a>
                                                                            </li>
                                                                            <li>
                                                                                <a href="javascript:void(0);"
                                                                                    class="dropdown-item rounded-1 assignment-type-filter"
                                                                                    data-value="exempt">Exempt</a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                    <div class="dropdown">
                                                                        <a href="javascript:void(0);"
                                                                            id="sortDropdownToggle"
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
                                                                            <li><a href="javascript:void(0);"
                                                                                    class="dropdown-item rounded-1 sort-filter"
                                                                                    data-value="recently_added">Recently
                                                                                    Added</a></li>
                                                                            <li><a href="javascript:void(0);"
                                                                                    class="dropdown-item rounded-1 sort-filter"
                                                                                    data-value="asc">Ascending</a></li>
                                                                            <li><a href="javascript:void(0);"
                                                                                    class="dropdown-item rounded-1 sort-filter"
                                                                                    data-value="desc">Descending</a></li>
                                                                            <li><a href="javascript:void(0);"
                                                                                    class="dropdown-item rounded-1 sort-filter"
                                                                                    data-value="last_month">Last Month</a>
                                                                            </li>
                                                                            <li><a href="javascript:void(0);"
                                                                                    class="dropdown-item rounded-1 sort-filter"
                                                                                    data-value="last_7_days">Last 7
                                                                                    Days</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row align-items-center">
                                                                    <table class="table datatable">
                                                                        <thead class="thead-light">
                                                                            <tr>
                                                                                <th class="no-sort">
                                                                                    <div class="form-check form-check-md">
                                                                                        <input class="form-check-input"
                                                                                            type="checkbox"
                                                                                            id="select-all">
                                                                                    </div>
                                                                                </th>
                                                                                <th>Name</th>
                                                                                <th>Branch</th>
                                                                                <th>Geofence Name</th>
                                                                                <th>Type</th>
                                                                                <th>Assigned By</th>
                                                                                <th></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($assignedGeofences as $geofenceUser)
                                                                                <tr>
                                                                                    <td>
                                                                                        <div
                                                                                            class="form-check form-check-md">
                                                                                            <input class="form-check-input"
                                                                                                type="checkbox">
                                                                                        </div>
                                                                                    </td>
                                                                                    <td>{{ $geofenceUser->user->personalInformation->first_name }}
                                                                                    </td>
                                                                                    <td>{{ $geofenceUser->user->branch->name }}
                                                                                    </td>
                                                                                    <td>{{ $geofenceUser->geofence->geofence_name }}
                                                                                    </td>
                                                                                    <td>
                                                                                        <span
                                                                                            class="badge d-inline-flex align-items-center badge-xs
                                                                                    {{ $geofenceUser->assignment_type === 'manual' ? 'badge-dark' : 'badge-secondary' }}">
                                                                                            <i
                                                                                                class="ti ti-point-filled me-1"></i>{{ ucfirst($geofenceUser->assignment_type) }}
                                                                                        </span>
                                                                                    </td>
                                                                                    <td>{{ $geofenceUser->creator_name }}
                                                                                    </td>
                                                                                    <td>
                                                                                        <div
                                                                                            class="action-icon d-inline-flex">
                                                                                            {{-- Edit --}}
                                                                                            <a href="#"
                                                                                                class="me-2"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#edit_assign_geofence"
                                                                                                data-id="{{ $geofenceUser->id }}"
                                                                                                data-geofence-id="{{ $geofenceUser->geofence_id }}"
                                                                                                data-assignment-type="{{ $geofenceUser->assignment_type }}">
                                                                                                <i class="ti ti-edit"
                                                                                                    title="Edit"></i></a>
                                                                                            {{-- Delete --}}
                                                                                            <a href="#"
                                                                                                class="me-2 btn-deleteGeofenceUser"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#delete_assign_geofence"
                                                                                                data-id="{{ $geofenceUser->id }}">
                                                                                                <i class="ti ti-trash"
                                                                                                    title="Delete"></i></a>
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   @include('layout.partials.footer-company')
    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup', [
        'branches' => $branches,
        'activeGeofences' => $activeGeofences,
        'departments' => $departments,
        'designations' => $designations,
        'employees' => $employees,
    ])
    @endcomponent
@endsection

@push('scripts')
    <script src="{{ asset('build/js/department/filters.js') }}"></script>
    <script src="{{ asset('build/js/geofence/geofence.js') }}"></script>
    <script src="{{ asset('build/js/geofence/geofenceuser.js') }}"></script>
@endpush
