<?php $page = 'employee-archive'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee Archive</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee Archive</li>
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
                                    <i class="ti ti-file-export me-1"></i>Export / Download
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="#" class="dropdown-item rounded-1" data-bs-toggle="modal"
                                            data-bs-target="#exportModal">
                                            <i class="ti ti-file-type-xls me-1"></i>Export as Excel
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('downloadEmployeeTemplate') }}" class="dropdown-item rounded-1">
                                            <i class="ti ti-file-type-xls me-1"></i>Download Template
                                        </a>
                                    </li>
                                </ul>
                            </div>
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

            <!-- Employee Archive Card -->
            <div class="card">
                <div class="card-header bg-primary d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5 class="text-white">Employee Archive</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select"
                                oninput="filterEmployees();" style="width:200px;">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" 
                                        {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select"
                                oninput="filterEmployees()" style="width:200px;">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ $selectedDepartmentId == $department->id ? 'selected' : '' }}>
                                        {{ $department->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select"
                                oninput="filterEmployees()" style="width:200px;">
                                <option value="" selected>All Designations</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}"
                                        {{ $selectedDesignationId == $designation->id ? 'selected' : '' }}>
                                        {{ $designation->designation_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <input type="search" class="form-control" placeholder="Search..." 
                                id="search" onkeyup="filterEmployees()" style="width: 200px;" 
                                value="{{ $selectedSearch }}">
                        </div>
                        <div class="form-group">
                            <select name="sortby_filter" id="sortby_filter" class="select2 form-select"
                                onchange="filterEmployees()" style="width:150px;">
                                <option value="" {{ $selectedSort == '' ? 'selected' : '' }}>All Sort By</option>
                                <option value="ascending" {{ $selectedSort == 'ascending' ? 'selected' : '' }}>Ascending</option>
                                <option value="descending" {{ $selectedSort == 'descending' ? 'selected' : '' }}>Descending</option>
                                <option value="last_month" {{ $selectedSort == 'last_month' ? 'selected' : '' }}>Last Month</option>
                                <option value="last_7_days" {{ $selectedSort == 'last_7_days' ? 'selected' : '' }}>Last 7 days</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable-filtered" id="employee_archive_table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Joining Date</th>
                                    <th>Status</th>
                                    @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="employeeArchiveTableBody">
                                @foreach ($employees as $employee)
                                    @php
                                        $detail = $employee->employmentDetail;
                                    @endphp
                                    <tr>
                                        <td>
                                            @if (in_array('Read', $permission) && in_array('Update', $permission))
                                                <a href="{{ url('employees/employee-details/' . $employee->id) }}"
                                                    class="me-2" title="View Full Details"><i
                                                        class="ti ti-eye"></i></a>
                                            @endif
                                            @if (in_array('Update', $permission))
                                                <a href="#" class="me-2"
                                                    onclick="editEmployee({{ $employee->id }})"><i
                                                        class="ti ti-edit"></i></a>
                                            @endif
                                            {{ $detail->employee_id ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{ url('employee-details') }}" class="avatar avatar-md"
                                                    data-bs-toggle="modal" data-bs-target="#view_details"><img
                                                        src="{{ $employee->personalInformation && $employee->personalInformation->profile_picture ? asset('storage/' . $employee->personalInformation->profile_picture) : URL::asset('build/img/users/user-13.jpg') }}"
                                                        class="img-fluid rounded-circle" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="{{ url('employee-details') }}"
                                                            data-bs-toggle="modal" data-bs-target="#view_details">
                                                            {{ $employee->personalInformation->last_name ?? '' }}
                                                            {{ $employee->personalInformation->suffix ?? '' }},
                                                            {{ $employee->personalInformation->first_name ?? '' }}
                                                            {{ $employee->personalInformation->middle_name ?? '' }}</a>
                                                    </p>
                                                    <span
                                                        class="fs-12">{{ $employee->employmentDetail->branch->name ?? '' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $employee->email ?? '-' }}</td>
                                        <td>{{ $detail?->department?->department_name ?? 'N/A' }}</td>
                                        <td> {{ $detail?->designation?->designation_name ?? 'N/A' }}</td>
                                        <td>{{ $detail->date_hired ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge d-inline-flex align-items-center badge-xs badge-danger">
                                                <i class="ti ti-point-filled me-1"></i>Inactive
                                            </span>
                                        </td>
                                        @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                            <td>
                                                <div class="action-icon d-inline-flex">
                                                    @if (in_array('Update', $permission))
                                                        <a href="#" class="btn-activate"
                                                            onclick="activateEmployee({{ $employee->id }})"
                                                            title="Activate"><i class="ti ti-circle-check"></i></a>
                                                    @endif
                                                    @if (in_array('Delete', $permission))
                                                        <a href="#" class="btn-delete"
                                                            onclick="deleteEmployee({{ $employee->id }})">
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

@endsection

@push('scripts')
    <script src="{{ asset('build/js/datatable-filtered.js') }}"></script>
    <script>
        // Initialize DataTable for employee archive
        $(document).ready(function() {
            initFilteredDataTable('#employee_archive_table', {
                pageLength: {{ $selectedLength ?? 10 }},
                order: [], // Disable default sorting
                columnDefs: [
                    { orderable: false, targets: -1 } // Disable sorting on action column
                ]
            });
        });

        // Filter function for archive
        function filterEmployees() {
            const branch = $('#branch_filter').val();
            const department = $('#department_filter').val();
            const designation = $('#designation_filter').val();
            const sort = $('#sortby_filter').val();
            const search = $('#search').val();
            const entries = $('#entries').val();
            
            // Redirect to same page with query parameters
            const url = new URL(window.location.href);
            
            // Set or remove query parameters
            if (branch) url.searchParams.set('branch_id', branch);
            else url.searchParams.delete('branch_id');
            
            if (department) url.searchParams.set('department_id', department);
            else url.searchParams.delete('department_id');
            
            if (designation) url.searchParams.set('designation_id', designation);
            else url.searchParams.delete('designation_id');
            
            if (sort) url.searchParams.set('sort', sort);
            else url.searchParams.delete('sort');
            
            if (search) url.searchParams.set('search', search);
            else url.searchParams.delete('search');
            
            if (entries) url.searchParams.set('length', entries);
            else url.searchParams.delete('length');
            
            window.location.href = url.toString();
        }

        // Auto-filter functions (reuse from main employee list)
        $(document).ready(function() {
            // Branch auto filter
            $('#branch_filter').change(function() {
                const branchId = $(this).val();
                
                $.ajax({
                    url: '{{ route('branchAuto-filter') }}',
                    type: 'GET',
                    data: { branch: branchId },
                    success: function(response) {
                        // Update departments
                        $('#department_filter').empty().append('<option value="">All Departments</option>');
                        response.departments.forEach(function(dept) {
                            $('#department_filter').append(`<option value="${dept.id}">${dept.department_name}</option>`);
                        });

                        // Update designations
                        $('#designation_filter').empty().append('<option value="">All Designations</option>');
                        response.designations.forEach(function(desig) {
                            $('#designation_filter').append(`<option value="${desig.id}">${desig.designation_name}</option>`);
                        });
                    }
                });
            });

            // Department auto filter
            $('#department_filter').change(function() {
                const deptId = $(this).val();
                const branchId = $('#branch_filter').val();
                
                $.ajax({
                    url: '{{ route('departmentAuto-filter') }}',
                    type: 'GET',
                    data: { 
                        department: deptId,
                        branch: branchId 
                    },
                    success: function(response) {
                        // Update branch if needed
                        if (response.branch_id) {
                            $('#branch_filter').val(response.branch_id);
                        }

                        // Update designations
                        $('#designation_filter').empty().append('<option value="">All Designations</option>');
                        response.designations.forEach(function(desig) {
                            $('#designation_filter').append(`<option value="${desig.id}">${desig.designation_name}</option>`);
                        });
                    }
                });
            });

            // Designation auto filter
            $('#designation_filter').change(function() {
                const desigId = $(this).val();
                
                $.ajax({
                    url: '{{ route('designationAuto-filter') }}',
                    type: 'GET',
                    data: { designation: desigId },
                    success: function(response) {
                        // Update branch and department
                        if (response.branch_id) {
                            $('#branch_filter').val(response.branch_id);
                        }
                        if (response.department_id) {
                            $('#department_filter').val(response.department_id);
                        }
                    }
                });
            });
        });

        // Employee actions (reuse from main employee list)
        function activateEmployee(id) {
            // Implementation for activating employee
            if (confirm('Are you sure you want to activate this employee?')) {
                // Add your activation logic here
                window.location.reload();
            }
        }

        function deleteEmployee(id) {
            // Implementation for deleting employee
            if (confirm('Are you sure you want to delete this employee?')) {
                // Add your deletion logic here
                window.location.reload();
            }
        }

        function editEmployee(id) {
            // Implementation for editing employee
            console.log('Edit employee:', id);
        }
    </script>
@endpush