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
                            </ul>
                        </div>
                    </div>
                    @endif
                    @if (in_array('Create', $permission))
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_designation"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            Designation</a>
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

            <!-- Search Filter  -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Designation List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select"
                                oninput="designation_filter(); autoFilterBranch('branch_filter','department_filter',true)">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select"
                                oninput="designation_filter(); autoFilterDepartment('department_filter','branch_filter', true)">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div> 
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select"
                                oninput="designation_filter()">
                                <option value="" selected>All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div> 
                        <div class="form-group">
                            <select name="sortby_filter" id="sortby_filter" class="select2 form-select"
                                onchange="designation_filter()">
                                <option value="" selected>All Sort By</option>
                                <option value="ascending">Ascending</option>
                                <option value="descending">Descending</option>
                                <option value="last_month">Last Month</option>
                                <option value="last_7_days">Last 7 days</option>
                            </select>
                        </div> 
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable" id="designation_table">
                            <thead class="thead-light">
                                <tr> 
                                    <th>Designation </th>
                                    <th>Branch</th>
                                    <th>Department</th>
                                    <th>Job Description</th>
                                    <th class="text-center">No of Employees</th>
                                    <th class="text-center">Status</th>
                                    @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                    <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($designations as $designation)
                                    <tr>
                                       
                                        <td>
                                            <h6 class="fw-medium fs-14 text-dark">{{ $designation->designation_name }}
                                            </h6>
                                        </td>
                                        <td>{{ $designation->department->branch->name ?? 'N/A' }}</td>
                                        <td>{{ $designation->department->department_name ?? 'N/A' }}</td>
                                        <td>
                                            @if ($designation->job_description)
                                                {{ $designation->job_description }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ $designation->active_employees_count }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>Active
                                            </span>
                                        </td>
                                         @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                        <td class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                 @if (in_array('Update', $permission))
                                                    <a href="#" class="me-2 btn-edit" data-bs-toggle="modal"
                                                        data-bs-target="#edit_designation" data-id="{{ $designation->id }}"
                                                        data-designation_name="{{ $designation->designation_name }}"
                                                        data-department_id="{{ $designation->department_id }}"
                                                        data-job_description="{{ $designation->job_description }}"
                                                        data-branch_id="{{ $designation->department->branch_id }}"><i
                                                            class="ti ti-edit"></i></a>
                                                 @endif
                                                  @if (in_array('Delete', $permission))
                                                    <a href="javascript:void(0);" class="btn-delete" data-bs-toggle="modal"
                                                        data-bs-target="#delete_modal" data-id="{{ $designation->id }}"
                                                        data-designation_name="{{ $designation->designation_name }}"
                                                        title="Delete"><i class="ti ti-trash"></i></a>
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
            <!-- /Search Filter -->

        </div>

        @include('layout.partials.footer-company')

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

    <!-- Filter JS -->
    <script src="{{ asset('build/js/department/filters.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            // Designation Store
            document.getElementById("addDesignationForm")?.addEventListener("submit", async function(event) {
                event.preventDefault();
                let designationName = document.getElementById("designationName").value.trim();
                let departmentId = document.getElementById("departmentId").value.trim();
                let jobDescription = document.getElementById("jobDescription").value.trim();
                let branchId = document.getElementById("branchId").value.trim();

                if (!designationName || !departmentId || !branchId) {
                    toastr.error("Please complete all required fields.");
                    return;
                }

                try {
                    let response = await fetch("/api/designations/create", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "Authorization": `Bearer ${authToken}`
                        },
                        body: JSON.stringify({
                            designation_name: designationName,
                            department_id: departmentId,
                            job_description: jobDescription
                        })
                    });
                    let data = await response.json();
                    response.ok ? toastr.success("Designation created successfully!") && setTimeout(
                        () => location.reload(), 1500) : toastr.error(data.message ||
                        "Failed to create designation.");
                } catch (error) {
                    console.error(error);
                    toastr.error("Something went wrong.");
                }
            });
        });
    </script>

    <script>
        let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let authToken = localStorage.getItem("token");

        $(document).on('click', '.btn-delete', function() {
            deleteId = $(this).data("id");
            $('#designationPlaceHolder').text($(this).data("designation_name"));
            console.log("Delete clicked", deleteId);
        });

        $('#confirmDeleteBtn').on('click', function() {
            if (!deleteId) return;
            fetch(`/api/designations/delete/${deleteId}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json",
                        "Content-Type": "application/json",
                        "Authorization": `Bearer ${authToken}`
                    }
                })
                .then(response => response.ok ? toastr.success("Designation deleted successfully!") && setTimeout(
                    () => location.reload(), 800) : response.json().then(data => toastr.error(data.message ||
                    "Error deleting designation.")))
                .catch(error => {
                    console.error(error);
                    toastr.error("Server error.");
                });
        });
    </script>

    <script>
        function loadDepartments(branchId, departmentDropdown, selectedDepartmentId = null, callback = null) {
             
            if (!branchId) {
                departmentDropdown.empty().append('<option value="" disabled selected>Select Department</option>');
                if (callback) callback();
                return;
            }
            $.ajax({
                url: `/designations/departments/${branchId}`,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    departmentDropdown.empty().append('<option value="">Select Department</option>');
                    $.each(data, function(key, value) {
                        departmentDropdown.append(
                            `<option value="${value.id}">${value.department_name}</option>`);
                    });

                    // Debug
                    let allOptions = [];
                    departmentDropdown.find('option').each(function() {
                        allOptions.push($(this).val());
                    });

                    let matched = false;
                    if (selectedDepartmentId) {
                        departmentDropdown.find('option').each(function() {
                            if (
                                String($(this).val()) === String(selectedDepartmentId) ||
                                Number($(this).val()) === Number(selectedDepartmentId)
                            ) {
                                $(this).prop('selected', true);
                                matched = true;
                            }
                        }); 

                        
                        departmentDropdown.trigger('change');  
                    }

                    if (callback) callback();
                }
            });
        }

        // All event bindings BELOW the function definition!

        $('#branchId').on('change', function() {
            loadDepartments( $(this).val(), $('#departmentId'));
        });

        $('#editBranchId').on('change', function() {
            loadDepartments($(this).val(), $('#editDepartmentId'));
        });

        $(document).on('click', '[data-bs-target="#edit_designation"]', function() {
            let $btn = $(this),
                editId = $btn.data("id"),
                branchId = $btn.data("branch_id"),
                departmentId = $btn.data("department_id");

            $('#editDesignationId').val(editId);
            $('#editDesignationName').val($btn.data("designation_name"));
            $('#editJobDescription').val($btn.data("job_description"));
            $('#editBranchId').val(branchId).trigger('change');

            // Always call AFTER setting branch!
            loadDepartments(branchId, $('#editDepartmentId'), departmentId);
        });

        $('#editDesignationForm').on('submit', async function(event) {
            event.preventDefault();

            let designationName = $('#editDesignationName').val().trim();
            let departmentId = $('#editDepartmentId').val();
            let branchId = $('#editBranchId').val();
            let jobDescription = $('#editJobDescription').val().trim();
 
            if (!designationName || !departmentId) {
                toastr.error("Please complete all fields.");
                return;
            }

            try {
                let response = await fetch(`/api/designations/update/${$('#editDesignationId').val()}`, {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "Authorization": `Bearer ${authToken}`
                    },
                    body: JSON.stringify({
                        designation_name: designationName,
                        department_id: departmentId,
                        branch_id: branchId,
                        job_description: jobDescription
                    })
                });
                let data = await response.json();
                if (response.ok) {
                    toastr.success("Designation updated successfully!");
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(data.message || "Update failed.");
                }
            } catch (error) {
                console.error(error);
                toastr.error("Something went wrong.");
            }
        });  

       
        function designation_filter() {

            let branch_filter = $('#branch_filter').val();
            let department_filter = $('#department_filter').val(); 
            let status_filter = $('#status_filter').val();
            let sortby_filter = $('#sortby_filter').val();

            $.ajax({
                url: "{{route('designation-filter')}}",
                method: 'GET',
                data: {
                    branch: branch_filter,
                    department: department_filter, 
                    status: status_filter,
                    sort_by: sortby_filter
                },
                success: function (response) {
                    if (response.status === 'success') {
                        let tbody = '';
                  
                        $.each(response.data, function (i, designation) {

                            let designation_name = designation.designation_name;
                            let designation_branch = designation.department.branch.name; 
                            let designation_department = designation.department.department_name;  
                            let designation_job_desc = designation.job_description; 
                            let statusBadge = (designation.status == "active")
                                ? '<span class="badge bg-success"><i class="ti ti-point-filled me-1"></i>Active</span>'
                                : '<span class="badge bg-danger"><i class="ti ti-point-filled me-1"></i>Inactive</span>';
                            let action='';
                              
                            if (response.permission.includes('Update')) {
                                action += `
                                    <a href="#" class="me-2 btn-edit" data-bs-toggle="modal"
                                        data-bs-target="#edit_designation"
                                        data-id="${designation.id}"
                                        data-designation_name="${designation.designation_name}"
                                        data-department_id="${designation.department_id}"
                                        data-job_description="${designation.job_description ?? ''}"
                                        data-branch_id="${designation.department.branch_id}">
                                        <i class="ti ti-edit"></i>
                                    </a>`;
                            }

                            if (response.permission.includes('Delete')) {
                                action += `
                                    <a href="javascript:void(0);" class="btn-delete" data-bs-toggle="modal"
                                        data-bs-target="#delete_modal"
                                        data-id="${designation.id}"
                                        data-designation_name="${designation.designation_name}"
                                        title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </a>`;
                            }
                            if (response.permission.includes('Read')) {
                                tbody += `
                                <tr>  
                                    <td>${designation_name}</td>
                                    <td>${designation_branch}</td>
                                    <td>${designation_department}</td>
                                    <td>${designation_job_desc ?? 'N/A'}</td>
                                    <td class="text-center">${designation.active_employees_count}</td>
                                    <td class="text-center">${statusBadge}</td>`;
                                    if (response.permission.includes('Update') || response.permission.includes('Delete')) {
                                        tbody += `<td class="text-center"><div class="action-icon d-inline-flex">${action}</div></td>`;
                                    } 
                                tbody += `</tr>`;
                            }
                        });
                        $('#designation_table tbody').html(tbody);
                    } else {
                        toastr.warning('Failed to load designation.');
                    }
                },
                error: function () { 
                    toastr.error('An error occurred while filtering designation.');
                }
            });
        } 

        
    function autoFilterBranch(branchSelect, departmentSelect,isFilter = false) {
        var branch = $('#' + branchSelect).val();
        var departmentSelect = $('#' + departmentSelect); 
        var departmentPlaceholder = isFilter ? 'All Departments' : 'Select Department';
        var designationPlaceholder = isFilter ? 'All Designations' : 'Select Designation';
        $.ajax({
            url: "{{route('designationBranch-filter')}}",
            method: 'GET',
            data: {
                branch: branch,
            },
            success: function (response) {
                if (response.status === 'success') {
                    departmentSelect.empty().append(`<option value="" selected>${departmentPlaceholder}</option>`); 

                    $.each(response.departments, function (i, department) {
                        departmentSelect.append(
                            $('<option>', {
                                value: department.id,
                                text: department.department_name
                            })
                        );
                    }); 
                     designation_filter();
                } else {
                    toastr.warning('Failed to get departments.');
                }
            },
            error: function () {
                toastr.error('An error occurred while getting departments');
            }
        });
    }

    
    function autoFilterDepartment(departmentSelect, branchSelect, isFilter = false) {
        let department = $('#' + departmentSelect).val();
        let branch_select = $('#' + branchSelect);  

        $.ajax({
            url:"{{route('designationDepartment-filter')}}",
            method: 'GET',
            data: {
                department: department,
                branch: branch_select.val(),
            },
            success: function (response) {
                if (response.status === 'success') {
                    if (response.branch_id !== '') {
                         branch_select.val(response.branch_id).trigger('change'); 
                    }  
                     designation_filter();
                } else {
                    toastr.warning('Failed to get branch and designation list.');
                }
            },
            error: function () {
                toastr.error('An error occurred while getting branch and designation list.');
            }
        });
    }

    </script> 
   
@endpush
