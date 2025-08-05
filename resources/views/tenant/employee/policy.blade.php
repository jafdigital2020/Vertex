<?php $page = 'policy'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Policies</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employees
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Policies</li>
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
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_policy"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            Policy</a>
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

            <!-- Policy list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Policies List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange-filtered"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter"  >
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group me-2">
                            <select name="targetType_filter" id="targetType_filter" class="select2 form-select" onchange="filter()">
                                <option value="" selected>All Target Types</option>
                                <option value="company-wide">Company Wide</option>
                                <option value="branch">Branch</option>
                                <option value="department">Department</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable-filtered" id="policy_table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th  class="text-center">Title</th>
                                    <th  class="text-center">Date</th>
                                    <th  class="text-center">Target Type</th>
                                    <th  class="text-center">Attachment</th>
                                    <th  class="text-center">Created By</th>
                                     @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                    <th  class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="policyTableBody">
                                @if(in_array('Read',$permission))
                                @foreach ($policies as $policy)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td  class="text-center">
                                            <h6 class="fs-14 fw-medium text-gray-9">{{ $policy->policy_title }}</h6>
                                        </td>
                                        <td  class="text-center">{{ \Carbon\Carbon::parse($policy->effective_date)->format('F j, Y') }}</td>

                                        <td  class="text-center">
                                            @foreach ($policy->targets->groupBy('target_type') as $targetType => $targets)
                                                @if ($targetType == 'company-wide')
                                                    <span>{{ ucfirst($targetType) }}</span>
                                                @else
                                                    <button type="button" class="btn btn-primary btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#targetModal{{ $policy->id }}{{ ucfirst($targetType) }}"
                                                        data-policy-id="{{ $policy->id }}"><i class="ti ti-eye me-1"></i>
                                                        {{ ucfirst($targetType) }}
                                                    </button>
                                                @endif
                                            @endforeach
                                        </td>

                                        <td  class="text-center">
                                            @if ($policy->attachment_path)
                                                <a href="{{ Storage::url($policy->attachment_path) }}" target="_blank"
                                                    class="btn btn-outline-primary btn-sm d-inline-flex align-items-center">
                                                    <i class="ti ti-file-description me-1"></i> View
                                                </a>
                                            @else
                                                <span class="text-muted fst-italic">No Attachment</span>
                                            @endif
                                        </td>
                                        <td  class="text-center">{{ $policy->createdBy->personalInformation->last_name ?? 'N/A' }},
                                            {{ $policy->createdBy->personalInformation->first_name ?? 'N/A' }}</td>
                                        @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                        <td  class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                @if(in_array('Update',$permission))
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_policy" data-id="{{ $policy->id }}"
                                                    data-policy-title="{{ $policy->policy_title }}"
                                                    data-policy-content="{{ $policy->policy_content }}"
                                                    data-effective-date="{{ $policy->effective_date }}"
                                                    data-attachment-type="{{ $policy->attachment_type }}"><i
                                                        class="ti ti-edit"></i></a>
                                                @endif
                                                @if(in_array('Delete',$permission))
                                                <a href="#" data-bs-toggle="modal" class="btn-delete"
                                                    data-bs-target="#delete_policy" data-id="{{ $policy->id }}"
                                                    data-policy-title="{{ $policy->policy_title }}"><i
                                                        class="ti ti-trash"></i></a>
                                                @endif
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>

                        <!-- Target Modal for each policy (will only show targets in the modal) -->
                        @foreach ($policies as $policy)
                            @foreach ($policy->targets->groupBy('target_type') as $targetType => $targets)
                                <div class="modal fade" id="targetModal{{ $policy->id }}{{ ucfirst($targetType) }}"
                                    tabindex="-1"
                                    aria-labelledby="targetModalLabel{{ $policy->id }}{{ ucfirst($targetType) }}"
                                    aria-hidden="true" data-policy-id="{{ $policy->id }}">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content shadow-lg border-0">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-semibold"
                                                    id="targetModalLabel{{ $policy->id }}{{ ucfirst($targetType) }}">
                                                    <i class="ti ti-target me-2"></i>{{ ucfirst($targetType) }} Targets
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                @if ($targets->count())
                                                    <div class="table-responsive">
                                                        <table class="table datatable-filtered table-bordered align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th style="width: 60px;">#</th>
                                                                    <th>Target Name</th>
                                                                    @if ($targetType === 'employee')
                                                                        <th>Email</th>
                                                                    @endif
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody >
                                                                @foreach ($targets as $i => $target)
                                                                    <tr>
                                                                        <td class="text-center">{{ $i + 1 }}</td>
                                                                        <td>
                                                                            <span
                                                                                class="fw-medium">{{ $target->target_name }}</span>
                                                                        </td>
                                                                        @if ($targetType === 'employee')
                                                                            <td>
                                                                                <span class="text-muted small">
                                                                                    {{ $target->user->email ?? '-' }}
                                                                                </span>
                                                                            </td>
                                                                        @endif
                                                                        <td>
                                                                            <!-- Pass target_id and target_type with each remove button -->
                                                                            <button type="button"
                                                                                class="btn btn-danger btn-sm remove-target"
                                                                                data-target-id="{{ $target->id }}"
                                                                                data-target-type="{{ $targetType }}"
                                                                                data-policy-id="{{ $policy->id }}">
                                                                                Remove
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="alert alert-info mb-0">
                                                        No targets found for this {{ $targetType }}.
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="ti ti-x me-1"></i>Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach


                    </div>
                </div>
            </div>
            <!-- /Policylist list -->

        </div>

        {{-- Footer Company --}}
        @include('layout.partials.footer-company')


    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup', [
        'branches' => $branches,
    ])
    @endcomponent
@endsection

@push('scripts')
    <script src="{{ asset('build/js/datatable-filtered.js') }}"></script>  
    <script> 
     if ($('.bookingrange-filtered').length > 0) {
        var start = moment().startOf('year');
        var end = moment().endOf('year');
        function booking_range(start, end) {
            $('.bookingrange-filtered span').html(start.format('M/D/YYYY') + ' - ' + end.format('M/D/YYYY'));
        }

        $('.bookingrange-filtered').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
                'Next Year': [moment().add(1, 'year').startOf('year'), moment().add(1, 'year').endOf('year')]
            }
        }, booking_range);
        booking_range(start, end);
    } 
    let policyTable; 
    $(document).ready(function () {
        policyTable = initFilteredDataTable('#policy_table'); 
    }); 
    $('#dateRange_filter').on('apply.daterangepicker', function () {
        filter();
    });

    $('#targetType_filter').on('change', function () {
        filter();
    });

    function filter() {
        const dateRange = $('#dateRange_filter').val();
        const targetType = $('#targetType_filter').val();

        $.ajax({
            url: '{{ route('policy_filter') }}',
            type: 'GET',
            data: {
                targetType,
                dateRange
            },
            success: function (response) {
                if (response.status === 'success') {
                    $('#policy_table').DataTable().destroy(); 
                    $('#policyTableBody').html(response.html); 
                    $('#policy_table').DataTable(); 
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
    <script>
        $(document).ready(function() {
            function updateFilters() {
                var targetType = $('#targetType').val(); 
                $('.byFilter').hide();
                $('.branchFilter').hide();
                $('.departmentFilter').hide();
                $('.designationFilter').hide();
                $('.employeeFilter').hide();

                if (targetType === 'company-wide' || targetType === '') {
                    // Hide everything if company-wide or no selection
                    $('.byFilter').hide();
                } else if (targetType === 'branch') {
                    $('.byFilter').show();
                    $('.branchFilter').show();
                    // Hide other filters
                } else if (targetType === 'department') {
                    $('.byFilter').show();
                    $('.branchFilter').show();
                    $('.departmentFilter').show();
                } else if (targetType === 'employee') {
                    $('.byFilter').show();
                    $('.branchFilter').show();
                    $('.departmentFilter').show();
                    $('.designationFilter').show();
                    $('.employeeFilter').show();
                }
            }

            // Initial state
            updateFilters();

            // On change
            $('#targetType').on('change', updateFilters);
        });
    </script>

    <script>
        $(document).ready(function() {
            function updateFilters() {
                var targetType = $('#editTargetType').val();
                // Hide all filters initially
                $('.editByFilter').hide();
                $('.editBranchFilter').hide();
                $('.editDepartmentFilter').hide();
                $('.editDesignationFilter').hide();
                $('.editEmployeeFilter').hide();

                if (targetType === 'company-wide' || targetType === '') {
                    // Hide everything if company-wide or no selection
                    $('.editByFilter').hide();
                } else if (targetType === 'branch') {
                    $('.editByFilter').show();
                    $('.editBranchFilter').show();
                    // Hide other filters
                } else if (targetType === 'department') {
                    $('.editByFilter').show();
                    $('.editBranchFilter').show();
                    $('.editDepartmentFilter').show();
                } else if (targetType === 'employee') {
                    $('.editByFilter').show();
                    $('.editBranchFilter').show();
                    $('.editDepartmentFilter').show();
                    $('.editDesignationFilter').show();
                    $('.editEmployeeFilter').show();
                }
            }

            // Initial state
            updateFilters();

            // On change
            $('#editTargetType').on('change', updateFilters);
        });
    </script>

    {{-- File Label --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('policyAttachment');
            const fileLabel = document.getElementById('fileUploadLabel');

            fileInput.addEventListener('change', function() {
                if (fileInput.files.length > 0) {
                    // For multiple files, show all names separated by comma
                    let names = Array.from(fileInput.files).map(f => f.name).join(', ');
                    fileLabel.textContent = names;
                } else {
                    fileLabel.textContent = 'Drag and drop your files';
                }
            });
        });
    </script>

    {{-- Branch, Department, Designation & Employee Selectors --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const authToken = localStorage.getItem('token');

            // — Helper: if user picks the empty‐value “All” option, auto-select every real option
            function handleSelectAll($sel) {
                const vals = $sel.val() || [];
                if (vals.includes('')) {
                    const all = $sel.find('option')
                        .map((i, opt) => $(opt).val())
                        .get()
                        .filter(v => v !== '');
                    $sel.val(all).trigger('change');
                    return true;
                }
                return false;
            }

            // — Rebuild Employee list based on selected Departments & Designations
            function updateEmployeeSelect(modal) {
                const allEmps = modal.data('employees') || [];
                const deptIds = modal.find('.department-select').val() || [];
                const desigIds = modal.find('.designation-select').val() || [];

                const filtered = allEmps.filter(emp => {
                    if (deptIds.length && !deptIds.includes(String(emp.department_id))) return false;
                    if (desigIds.length && !desigIds.includes(String(emp.designation_id))) return false;
                    return true;
                });

                let opts = '<option value="">All Employee</option>';
                filtered.forEach(emp => {
                    const u = emp.user?.personal_information;
                    if (u) {
                        opts += `<option value="${emp.user.id}">
                   ${u.last_name}, ${u.first_name}
                 </option>`;
                    }
                });

                modal.find('.employee-select')
                    .html(opts)
                    .trigger('change');
            }

            // — Branch change → fetch Depts, Emps & Shifts
            $(document).on('change', '.branch-select', function() {
                const $this = $(this);
                if (handleSelectAll($this)) return;

                const branchIds = $this.val() || [];
                const modal = $this.closest('.modal');
                const depSel = modal.find('.department-select');
                const desSel = modal.find('.designation-select');
                const empSel = modal.find('.employee-select');

                // reset downstream
                depSel.html('<option value="">All Department</option>').trigger('change');
                desSel.html('<option value="">All Designation</option>').trigger('change');
                empSel.html('<option value="">All Employee</option>').trigger('change');
                modal.removeData('employees');

                if (!branchIds.length) return;

                $.ajax({
                    url: '/api/shift-management/get-branch-data?' + $.param({
                        branch_ids: branchIds
                    }),
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Authorization': 'Bearer ' + authToken
                    },
                    success(data) {
                        // populate Departments
                        let dOpts = '<option value="">All Department</option>';
                        data.departments.forEach(d => {
                            dOpts +=
                                `<option value="${d.id}">${d.department_name}</option>`;
                        });
                        depSel.html(dOpts).trigger('change');

                        // cache & render Employees
                        modal.data('employees', data.employees || []);
                        updateEmployeeSelect(modal);

                        // populate Shifts (ensure your API now returns data.shifts[])
                        let sOpts = '<option value="">All Shift</option>';
                        (data.shifts || []).forEach(s => {
                            sOpts += `<option value="${s.id}">${s.name}</option>`;
                        });
                        shiftSel.html(sOpts).trigger('change');
                    },
                    error() {
                        alert('Failed to fetch branch data.');
                    }
                });
            });

            // — Department change → fetch Designations & re-filter Employees
            $(document).on('change', '.department-select', function() {
                const $this = $(this);
                if (handleSelectAll($this)) return;

                const deptIds = $this.val() || [];
                const modal = $this.closest('.modal');
                const desSel = modal.find('.designation-select');

                desSel.html('<option value="">All Designation</option>').trigger('change');
                updateEmployeeSelect(modal);

                if (!deptIds.length) return;

                $.ajax({
                    url: '/api/shift-management/get-designations?' + $.param({
                        department_ids: deptIds
                    }),
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Authorization': 'Bearer ' + authToken
                    },
                    success(data) {
                        let o = '<option value="">All Designation</option>';
                        data.forEach(d => {
                            o += `<option value="${d.id}">${d.designation_name}</option>`;
                        });
                        desSel.html(o).trigger('change');
                    },
                    error() {
                        alert('Failed to fetch designations.');
                    }
                });
            });

            // — Designation change → re-filter Employees
            $(document).on('change', '.designation-select', function() {
                const $this = $(this);
                if (handleSelectAll($this)) return;
                updateEmployeeSelect($this.closest('.modal'));
            });

            // — Employee “All Employee” handler
            $(document).on('change', '.employee-select', function() {
                handleSelectAll($(this));
            });
        });
    </script>

    {{-- Create Form Submission --}}
    <script>
        let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let authToken = localStorage.getItem("token");

        $('#addPolicyForm').submit(function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            let targetType = $('#targetType').val();

            // Remove all unrelated fields from FormData
            if (targetType === 'branch') {
                formData.delete('department_id[]');
                formData.delete('user_id[]');
            } else if (targetType === 'department') {
                formData.delete('branch_id[]');
                formData.delete('user_id[]');
            } else if (targetType === 'employee') {
                formData.delete('branch_id[]');
                formData.delete('department_id[]');
            } else if (targetType === 'company-wide') {
                formData.delete('branch_id[]');
                formData.delete('department_id[]');
                formData.delete('user_id[]');
            }

            $.ajax({
                url: '/api/policy/create',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Authorization': 'Bearer ' + authToken
                },
                success: function(response) {
                    toastr.success('Policy added!');
                    $('#addPolicyForm')[0].reset();
                    $('#add_policy').modal('hide');
                    filter();

                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || xhr.responseText || xhr.statusText || 'An unknown error occurred';
                    toastr.error(message);
                }
            });
        });
    </script>

    {{-- Delete Policy --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let policyDeleteId = null;
            const policyDeleteBtn = document.getElementById('policyConfirmDeleteBtn');
            const policyPlaceHolder = document.getElementById('policyPlaceHolder');

            // Use delegation to listen for delete button clicks
            document.addEventListener('click', function(e) {
                const button = e.target.closest('.btn-delete');
                if (!button) return;

                policyDeleteId = button.getAttribute('data-id');
                const policyName = button.getAttribute('data-policy-title');

                if (policyPlaceHolder) {
                    policyPlaceHolder.textContent = policyName;
                }
            });

            // Confirm delete
            policyDeleteBtn?.addEventListener('click', function() {
                if (!policyDeleteId) return;

                fetch(`/api/policy/delete/${policyDeleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${authToken}`,
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Policy deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_policy'));
                            deleteModal.hide();
                            filter();
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message || "Error deleting policy.");
                            });
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        toastr.error("Server error.");
                    });
            });
        });
    </script>

    {{-- Remove Target --}}
    <script>
        $('.remove-target').on('click', function() {
            // Access the target ID and policy ID from the button
            var targetId = $(this).data('target-id'); // Get the target ID
            var policyId = $(this).data('policy-id'); // Get the policy ID

            // Log to verify the data being sent
            console.log('Sending targetId:', targetId, 'policyId:', policyId);

            // Send AJAX request to delete the target
            $.ajax({
                url: '{{ route('api.policyRemoveTarget') }}', // API route
                type: 'POST',
                data: {
                    id: targetId, // Send target id as `id`
                    policy_id: policyId, // Send policy id as `policy_id`
                    _token: $('meta[name="csrf-token"]').attr('content') // CSRF token
                },
                success: function(response) {
                    // Remove the target row from the UI
                    $(this).closest('tr').remove(); // Remove the target row from the table

                    // Display success message
                    toastr.success('Target removed successfully!');
                    setTimeout(() => {
                        window.location.reload(); // Reload the page to reflect changes
                    }, 500);
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseJSON); // Log the response for debugging
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error('Failed to remove target: ' + xhr.responseJSON.message);
                    } else {
                        toastr.error('Failed to remove target: ' + status + ' - ' + error);
                    }
                }
            });
        });
    </script>

    {{-- Edit Policy --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            // 🌟 1. Delegate click events for edit buttons
            document.addEventListener("click", function(e) {
                const button = e.target.closest('[data-bs-target="#edit_policy"]');
                if (!button) return;

                const id = button.dataset.id;
                const policyTitle = button.dataset.policyTitle;
                const policyContent = button.dataset.policyContent;
                const effectiveDate = button.dataset.effectiveDate;
                const attachmentType = button.dataset.attachmentType;
                const targetType = button.dataset.targetType;

                // Set the policy ID in the hidden input field
                document.getElementById("editPolicyId").value = id;

                // Populate the modal with the current values
                document.getElementById("editPolicyTitle").value = policyTitle;
                document.getElementById("editEffectiveDate").value = effectiveDate;
                document.getElementById("editPolicyContent").value = policyContent;
                document.getElementById("editAttachmentType").value = attachmentType;

                // Load the select inputs (target type, branch, department, etc.)
                const targetTypeSel = document.getElementById("editTargetType");
                targetTypeSel.value = targetType || '';
                targetTypeSel.dispatchEvent(new Event('change'));

                if (targetType === "branch") {
                    // Pre-select the branch based on data attributes or backend data
                }
            });

            // 🌟 2. Handle update button click
            document.getElementById("editPolicyForm").addEventListener("submit", async function(e) {
                e.preventDefault();

                const editId = document.getElementById("editPolicyId")
                .value; // Get policy ID from the hidden input field
                const title = document.getElementById("editPolicyTitle").value.trim();
                const effectiveDate = document.getElementById("editEffectiveDate").value;
                const targetType = document.getElementById("editTargetType").value;
                const branchIds = Array.from(document.getElementById("editPolicyBranchFilter")
                    .selectedOptions).map(option => option.value);
                const departmentIds = Array.from(document.getElementById("editPolicyDepartmentFilter")
                    .selectedOptions).map(option => option.value);
                const employeeIds = Array.from(document.getElementById("editPolicyUserFilter")
                    .selectedOptions).map(option => option.value);
                const policyContent = document.getElementById("editPolicyContent").value.trim();
                const attachment = document.getElementById("editPolicyAttachment").files[
                0]; // Assuming file attachment is optional

                // Ensure required fields are filled out
                if (!title || !effectiveDate) {
                    return toastr.error("Please complete all fields.");
                }

                const payload = {
                    policy_title: title,
                    effective_date: effectiveDate,
                    target_type: targetType,
                    policy_content: policyContent,
                    attachment_path: attachment ? attachment.name :
                        null // Attach the file name or null if no file selected
                };

                // Add branch, department, or employee if selected
                if (targetType === 'branch') {
                    payload.branch_ids = branchIds;
                } else if (targetType === 'department') {
                    payload.department_ids = departmentIds;
                } else if (targetType === 'employee') {
                    payload.employee_ids = employeeIds;
                }

                try {
                    const res = await fetch(`/api/policy/update/${editId}`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "Authorization": `Bearer ${authToken}`
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();

                    if (res.ok) {
                        toastr.success("Policy updated successfully!");
                        $('#edit_policy').modal('hide');
                        filter();
                    } else {
                        (data.errors ?
                            Object.values(data.errors).flat().forEach(msg => toastr.error(msg)) :
                            toastr.error(data.message || "Update failed.")
                        );
                    }

                } catch (err) {
                    console.error(err);
                    toastr.error("Something went wrong.");
                }
            });
        });
    </script>
@endpush
