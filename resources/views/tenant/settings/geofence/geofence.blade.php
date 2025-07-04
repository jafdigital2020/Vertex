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
                        @if(in_array('Create',$permission))
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_geofence" data-user-id=""
                            class="btn btn-primary d-flex align-items-center addGeofence"><i
                                class="ti ti-circle-plus me-2"></i>Add Geofence</a>
                        @endif
                        @if(in_array('Create',$permission))
                        <a href="#" data-bs-toggle="modal" data-bs-target="#assign_geofence"
                            class="btn btn-secondary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i>Assign Geofence
                        </a>
                        @endif
                    </div>
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div> 
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
                                                                                <th class="text-center">Branch</th>
                                                                                <th class="text-center">Address</th>
                                                                                <th class="text-center">Radius</th>
                                                                                <th class="text-center">Created By</th>
                                                                                <th class="text-center">Edited By</th>
                                                                                <th class="text-center">Expiration Date</th>
                                                                                <th class="text-center">Status</th>
                                                                                @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                                                                <th class="text-center">Action</th>
                                                                                @endif
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody id="locationTableBody">
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
                                                                                    <td class="text-center">{{ $geofence->branch->name ?? 'N/A' }}
                                                                                    </td>
                                                                                    <td class="text-center">{{ $geofence->geofence_address }}
                                                                                    </td>
                                                                                    <td class="text-center">{{ $geofence->geofence_radius }}
                                                                                    </td>
                                                                                    <td class="text-center">{{ $geofence->creator_name }}</td>
                                                                                    <td class="text-center">{{ $geofence->updater_name ?? 'N/A' }}
                                                                                    </td>
                                                                                    <td class="text-center">{{ $geofence->expiration_date ?? 'No Expiration' }}
                                                                                    </td>
                                                                                    <td class="text-center"> <span
                                                                                            class="badge d-inline-flex align-items-center badge-xs
                                                                                        {{ $geofence->status === 'inactive' ? 'badge-danger' : 'badge-success' }}">
                                                                                            <i
                                                                                                class="ti ti-point-filled me-1"></i>{{ ucfirst($geofence->status) }}
                                                                                        </span></td>
                                                                                   @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                                                                    <td class="text-center">
                                                                                        <div
                                                                                            class="action-icon d-inline-flex">
                                                                                            @if(in_array('Update',$permission))
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
                                                                                             @endif
                                                                                             @if(in_array('Delete',$permission))
                                                                                            <a href="#"
                                                                                                class="me-2 btn-delete"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#delete_geofence"
                                                                                                data-id="{{ $geofence->id }}"
                                                                                                data-geofence-name="{{ $geofence->geofence_name }}">
                                                                                                <i class="ti ti-trash"
                                                                                                    title="Delete"></i></a>
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
                                                                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                                                                    <div class="form-group me-2">
                                                                        <select name="branch_filter" id="branch_filter" class="select2 form-select " onchange="user_filter()">
                                                                            <option value="" selected>All Branches</option>
                                                                            @foreach ($branches as $branch)
                                                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group me-2">
                                                                        <select name="department_filter" id="department_filter" class="select2 form-select"  onchange="user_filter()">
                                                                            <option value="" selected>All Departments</option>
                                                                            @foreach ($departments as $department)
                                                                                <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group me-2">
                                                                        <select name="designation_filter" id="designation_filter" class="select2 form-select"  onchange="user_filter()">
                                                                            <option value="" selected>All Designations</option>
                                                                            @foreach ($designations as $designation)
                                                                                <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                    <div class="form-group me-2">
                                                                        <select name="type_filter" id="type_filter" class="select2 form-select"  onchange="user_filter()">
                                                                            <option value="" selected>All Assignment Type</option>
                                                                            <option value="manual">Manual</option>
                                                                            <option value="exempt">Exempt</option> 
                                                                        </select>
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
                                                                                <th class="text-center">Branch</th>
                                                                                <th class="text-center">Geofence Name</th>
                                                                                <th class="text-center">Type</th>
                                                                                <th class="text-center">Assigned By</th>
                                                                                 @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                                                                <th class="text-center">Action</th>
                                                                                @endif
                                                                            </tr>
                                                                        </thead> 
                                                                        <tbody id="usersTableBody">
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
                                                                                   <td class="text-center">{{ $geofenceUser->user->branch->name }}
                                                                                    </td>
                                                                                   <td class="text-center">{{ $geofenceUser->geofence->geofence_name }}
                                                                                    </td>
                                                                                   <td class="text-center">
                                                                                        <span
                                                                                            class="badge d-inline-flex align-items-center badge-xs
                                                                                    {{ $geofenceUser->assignment_type === 'manual' ? 'badge-dark' : 'badge-secondary' }}">
                                                                                            <i
                                                                                                class="ti ti-point-filled me-1"></i>{{ ucfirst($geofenceUser->assignment_type) }}
                                                                                        </span>
                                                                                    </td>
                                                                                   <td class="text-center">{{ $geofenceUser->creator_name }}
                                                                                    </td>
                                                                                @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                                                                   <td class="text-center">
                                                                                        <div class="action-icon d-inline-flex">
                                                                                            @if(in_array('Update',$permission))
                                                                                            <a href="#"
                                                                                                class="me-2"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#edit_assign_geofence"
                                                                                                data-id="{{ $geofenceUser->id }}"
                                                                                                data-geofence-id="{{ $geofenceUser->geofence_id }}"
                                                                                                data-assignment-type="{{ $geofenceUser->assignment_type }}">
                                                                                                <i class="ti ti-edit"
                                                                                                    title="Edit"></i></a>
                                                                                            @endif 
                                                                                            @if(in_array('Delete',$permission))
                                                                                            <a href="#"
                                                                                                class="me-2 btn-deleteGeofenceUser"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#delete_assign_geofence"
                                                                                                data-id="{{ $geofenceUser->id }}">
                                                                                                <i class="ti ti-trash"
                                                                                                    title="Delete"></i></a>
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
    <script>
        const geofenceLocationFilterUrl = '{{ route('geofence-location-filter') }}';
        const geofenceUserFilterUrl = '{{route('geofence-user-filter')}}';
    </script>
    <script src="{{ asset('build/js/department/filters.js') }}"></script>
    <script src="{{ asset('build/js/geofence/geofence.js') }}"></script>
    <script src="{{ asset('build/js/geofence/geofenceuser.js') }}"></script>
@endpush
