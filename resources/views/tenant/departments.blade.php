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
                           @if (in_array('Export', $permission))
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                <i class="ti ti-file-export me-1"></i>Export
                            </a>
                            @endif
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
                        @if (in_array('Create', $permission))
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_department"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            Department</a>
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
            <!-- Search Filter -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Department List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select"
                                oninput="deptList_filter();">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select"
                                oninput="deptList_filter()">
                                <option value="" selected>All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                           <div class="form-group">
                            <select name="sortby_filter" id="sortby_filter" class="select2 form-select"
                                onchange="deptList_filter()">
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
                        <table class="table datatable-filtered" id="department_list_table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Department </th>
                                    <th class="text-center">Code</th>
                                    <th class="text-center">No of Employees</th>
                                    <th class="text-center">Head</th>
                                    <th class="text-center">Branch</th>
                                    <th class="text-center">Status</th>
                                    @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                    <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if (in_array('Read', $permission) )
                                @foreach ($departments as $department)
                                    <tr>
                                        <td>
                                            <h6 class="fw-medium">{{ $department->department_name }}
                                            </h6>
                                        </td>
                                        <td class="text-center">
                                            <h6 class="fw-medium">{{ $department->department_code }}
                                            </h6>
                                        </td>
                                        <td class="text-center">
                                            {{ $department->employee_count }}
                                        </td>
                                        <td class="text-center">
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
                                        <td class="text-center">
                                            <h6 class="fw-medium">
                                                <a
                                                    href="#">{{ $department->branch ? $department->branch->name : 'No branch' }}</a>
                                            </h6>
                                        </td>
                                        <td class="text-center">
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
                                        @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                             @if (in_array('Update', $permission))
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_department" data-id="{{ $department->id }}"
                                                    data-department_code="{{ $department->department_code }}"
                                                    data-department_name="{{ $department->department_name }}"
                                                    data-department_head="{{ $department->head_of_department }}"
                                                    data-branch_id="{{ $department->branch_id }}" title="Edit"><i
                                                        class="ti ti-edit"></i></a>
                                             @endif
                                             @if (in_array('Delete', $permission))
                                                <a href="javascript:void(0);" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_modal" data-id="{{ $department->id }}"
                                                    data-department_name="{{ $department->department_name }}"
                                                    title="Delete"><i class="ti ti-trash"></i></a>
                                             @endif
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
@include('layout.partials.footer-company')

    </div>
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
    <script src="{{ asset('build/js/datatable-filtered.js') }}"></script>
    <script src="{{ asset('build/js/department/department.js') }}"></script> 
    <script src="{{ asset('build/js/department/filters.js') }}"></script>

    <script>

    $(document).on('click', '[data-bs-target="#edit_department"]', function () {
        const button = $(this);
        $('#edit_department input[name="id"]').val(button.data('id'));
        $('#edit_department input[name="department_code"]').val(button.data('department_code'));
        $('#edit_department input[name="department_name"]').val(button.data('department_name'));
        $('#edit_department select[name="head_of_department"]').val(button.data('department_head')).trigger('change');
        $('#edit_department select[name="branch_id"]').val(button.data('branch_id')).trigger('change');
    });

    $(document).on('click', '.btn-delete', function () {
        const button = $(this);

        $('#delete_modal input[name="id"]').val(button.data('id'));
        $('#delete_modal span.department-name').text(button.data('department_name'));
    }); 
   
    let departmentTable;

    $(document).ready(() => {
        departmentTable = initFilteredDataTable('#department_list_table'); 
    });

    function deptList_filter() {
        const branch_filter = $('#branch_filter').val();
        const status_filter = $('#status_filter').val();
        const sortby_filter = $('#sortby_filter').val();

        $.ajax({
            url: '{{ route('deptList-filter') }}',
            method: 'GET',
            data: {
                branch: branch_filter,
                status: status_filter,
                sort_by: sortby_filter
            },
            success: function (response) {
                if (response.status !== 'success') {
                    toastr.warning('Failed to load department list.');
                    return;
                }

                const rows = response.data.map(dep => {
                    const name = `<h6 class="fw-medium">${dep.department_name}</h6>`;
                    const code = `<h6 class="fw-medium">${dep.department_code}</h6>`;
                    const activeCnt = dep.employee_count;
                    const head = dep.head
                        ? `${dep.head.personal_information.last_name}, ${dep.head.personal_information.first_name}`
                        : 'No Head Assigned';
                    const branch = dep.branch?.name ?? '';
                    const isActive = dep.status === 'active';
                    const statusBadge = isActive
                        ? `<span class="badge badge-success d-inline-flex align-items-center badge-xs"><i class="ti ti-point-filled me-1"></i>Active</span>`
                        : `<span class="badge badge-danger d-inline-flex align-items-center badge-xs"><i class="ti ti-point-filled me-1"></i>Inactive</span>`;

                    let crud = '';

                    if (response.permission.includes('Update') || response.permission.includes('Delete')) {
                        crud += '<div class="action-icon d-inline-flex">';

                        if (response.permission.includes('Update')) {
                            crud += `
                                <a href="#" class="me-2" data-bs-toggle="modal"
                                data-bs-target="#edit_department"
                                data-id="${dep.id}"
                                data-department_code="${dep.department_code}"
                                data-department_name="${dep.department_name}"
                                data-department_head="${dep.head_of_department}"
                                data-branch_id="${dep.branch_id}"
                                title="Edit">
                                <i class="ti ti-edit"></i>
                                </a>`;
                        }

                        if (response.permission.includes('Delete')) {
                            crud += `
                                <a href="javascript:void(0);" class="btn-delete" data-bs-toggle="modal"
                                data-bs-target="#delete_modal"
                                data-id="${dep.id}"
                                data-department_name="${dep.department_name}"
                                title="Delete">
                                <i class="ti ti-trash"></i>
                                </a>`;
                        }

                        crud += '</div>';
                    }

                    return [
                        name,
                        `<div class="text-center">${code}</div>`,
                        `<div class="text-center">${activeCnt}</div>`,
                        `<div class="text-center"><h6 class="fw-medium">${head}</h6></div>`,
                        `<div class="text-center"><h6 class="fw-medium">${branch}</h6></div>`,
                        `<div class="text-center">${statusBadge}</div>`,
                        `<div class="text-center">${crud}</div>`
                    ];
                });

                departmentTable.clear();
                departmentTable.rows.add(rows);
                departmentTable.draw(false);
            },
            error: function () {
                toastr.error('An error occurred while filtering department list.');
            }
        });
    }

    $('#branch_filter, #status_filter, #sortby_filter').on('change', deptList_filter);

</script>

@endpush
