<?php $page = 'employee-status-management'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee Status Management</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">Employees</li>
                            <li class="breadcrumb-item active" aria-current="page">Status Management</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs mb-3" id="statusTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="employee-list-tab" data-bs-toggle="tab" data-bs-target="#employee-list" type="button" role="tab">
                        <i class="ti ti-users me-1"></i>Employee List
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-approvals-tab" data-bs-toggle="tab" data-bs-target="#pending-approvals" type="button" role="tab">
                        <i class="ti ti-clock me-1"></i>Pending Approvals <span class="badge bg-warning ms-1" id="pendingCount">0</span>
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="statusTabContent">
                <!-- Employee List Tab -->
                <div class="tab-pane fade show active" id="employee-list" role="tabpanel">
                    <!-- Page Content -->
                    <div class="row">
                        <div class="col-12">
                            <!-- Filters Card -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Filters</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Branch</label>
                                            <select class="form-select" id="branchFilter">
                                                <option value="">All Branches</option>
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Department</label>
                                            <select class="form-select" id="departmentFilter">
                                                <option value="">All Departments</option>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Designation</label>
                                            <select class="form-select" id="designationFilter">
                                                <option value="">All Designations</option>
                                                @foreach ($designations as $designation)
                                                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Employment Status</label>
                                            <select class="form-select" id="employmentStateFilter">
                                                <option value="">All Status</option>
                                                <option value="Active">Active</option>
                                                <option value="AWOL">AWOL</option>
                                                <option value="Resigned">Resigned</option>
                                                <option value="Terminated">Terminated</option>
                                                <option value="Suspended">Suspended</option>
                                                <option value="Floating">Floating</option>
                                            </select>
                                </div>
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-primary" id="applyFilter">
                                        <i class="ti ti-filter me-1"></i>Apply Filters
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="resetFilter">
                                        <i class="ti ti-refresh me-1"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Status Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Employee Status List</h5>
                            @if (in_array('Update', $permission))
                                <button type="button" class="btn btn-warning" id="bulkUpdateBtn" disabled>
                                    <i class="ti ti-edit me-1"></i>Bulk Update Status
                                </button>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="employeeStatusTable">
                                    <thead>
                                        <tr>
                                            @if (in_array('Update', $permission))
                                                <th><input type="checkbox" id="selectAll"></th>
                                            @endif
                                            <th>Employee ID</th>
                                            <th>Name</th>
                                            <th>Branch</th>
                                            <th>Department</th>
                                            <th>Designation</th>
                                            <th>Current Status</th>
                                            @if (in_array('Update', $permission))
                                                <th>Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody id="employeeStatusTableBody">
                                        @foreach ($employees as $employee)
                                                <tr>
                                                    @if (in_array('Update', $permission))
                                                        <td><input type="checkbox" class="employee-checkbox"
                                                                value="{{ $employee->id }}"></td>
                                                    @endif
                                                    <td>{{ $employee->employmentDetail->employee_id ?? 'N/A' }}</td>
                                                    <td>{{ $employee->personalInformation->full_name ?? 'N/A' }}</td>
                                                    <td>{{ $employee->employmentDetail->branch->name ?? 'N/A' }}</td>
                                                    <td>{{ $employee->employmentDetail->department->department_name ?? 'N/A' }}</td>
                                                    <td>{{ $employee->employmentDetail->designation->designation_name ?? 'N/A' }}
                                                    </td>
                                                    <td>
                                            @php
                                                $status = $employee->employmentDetail->employment_state ?? 'N/A';
                                                $badgeClass = match ($status) {
                                                    'Active' => 'bg-success',
                                                    'AWOL' => 'bg-dark',
                                                    'Resigned' => 'bg-info',
                                                    'Terminated' => 'bg-danger',
                                                    'Suspended' => 'bg-secondary',
                                                    'Floating' => 'bg-primary',
                                                    default => 'bg-light text-dark'
                                                };
                                            @endphp
                                                        <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                                                    </td>
                                                    @if (in_array('Update', $permission))
                                                        <td>
                                                            <button class="btn btn-sm btn-primary update-status-btn"
                                                                data-user-id="{{ $employee->id }}"
                                                                data-current-status="{{ $employee->employmentDetail->employment_state ?? '' }}"
                                                                data-employee-name="{{ $employee->personalInformation->full_name ?? '' }}">
                                                                <i class="ti ti-edit"></i> Update
                                                            </button>
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
    
        <!-- Pending Approvals Tab -->
        <div class="tab-pane fade" id="pending-approvals" role="tabpanel">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pending Status Change Approvals</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="approvalsTable">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Current Status</th>
                                            <th>Requested Status</th>
                                            <th>Requested By</th>
                                            <th>Request Date</th>
                                            <th>Remarks</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="approvalsTableBody">
                                        <!-- Will be populated via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Tab Content -->

    </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Employee Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="updateStatusForm">
                    <div class="modal-body">
                        <input type="hidden" id="updateUserId" name="user_id">
                        <div class="mb-3">
                            <label class="form-label">Employee</label>
                            <input type="text" class="form-control" id="updateEmployeeName" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Status</label>
                            <input type="text" class="form-control" id="updateCurrentStatus" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="employment_state" id="updateNewStatus" required>
                                <option value="">Select Status</option>
                                <option value="Active">Active</option>
                                <option value="AWOL">AWOL</option>
                                <option value="Resigned">Resigned</option>
                                <option value="Terminated">Terminated</option>
                                <option value="Suspended">Suspended</option>
                                <option value="Floating">Floating</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" id="updateRemarks" rows="3"
                                placeholder="Optional remarks..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Update Status Modal -->
    <div class="modal fade" id="bulkUpdateStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Update Employee Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="bulkUpdateStatusForm">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong><span id="selectedCount">0</span></strong> employee(s) selected
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="employment_state" id="bulkNewStatus" required>
                                <option value="">Select Status</option>
                                <option value="Active">Active</option>
                                <option value="AWOL">AWOL</option>
                                <option value="Resigned">Resigned</option>
                                <option value="Terminated">Terminated</option>
                                <option value="Suspended">Suspended</option>
                                <option value="Floating">Floating</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" id="bulkRemarks" rows="3"
                                placeholder="Optional remarks..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update All Selected</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Approve Status Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Approve Status Change</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this status change request?</p>
                    <input type="hidden" id="approveId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmApprove">Approve</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Status Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Status Change</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm">
                    <div class="modal-body">
                        <input type="hidden" id="rejectId">
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectionReason" rows="3" required 
                                placeholder="Please provide a reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const authToken = localStorage.getItem('token');
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Initialize DataTable
        let table = $('#employeeStatusTable').DataTable({
            order: [[1, 'asc']],
            pageLength: 25
        });

        // Apply Filters
        $('#applyFilter').on('click', function () {
            const filters = {
                branch: $('#branchFilter').val(),
                department: $('#departmentFilter').val(),
                designation: $('#designationFilter').val(),
                employment_state: $('#employmentStateFilter').val()
            };

            $.ajax({
                url: '{{ route('employee-status-filter') }}',
                method: 'GET',
                data: filters,
                success: function (response) {
                    if (response.status === 'success') {
                        table.destroy();
                        $('#employeeStatusTableBody').html(response.html);
                        table = $('#employeeStatusTable').DataTable({
                            order: [[1, 'asc']],
                            pageLength: 25
                        });
                        attachEventListeners();
                    }
                },
                error: function (xhr) {
                    toastr.error('Failed to apply filters');
                }
            });
        });

        // Reset Filters
        $('#resetFilter').on('click', function () {
            $('#branchFilter').val('');
            $('#departmentFilter').val('');
            $('#designationFilter').val('');
            $('#employmentStateFilter').val('');
            location.reload();
        });

        // Select All Checkbox
        $('#selectAll').on('change', function () {
            $('.employee-checkbox').prop('checked', $(this).is(':checked'));
            toggleBulkUpdateButton();
        });

        // Individual Checkbox
        $(document).on('change', '.employee-checkbox', function () {
            toggleBulkUpdateButton();
        });

        function toggleBulkUpdateButton() {
            const selectedCount = $('.employee-checkbox:checked').length;
            $('#bulkUpdateBtn').prop('disabled', selectedCount === 0);
            $('#selectedCount').text(selectedCount);
        }

        // Update Status Button
        $(document).on('click', '.update-status-btn', function () {
            const userId = $(this).data('user-id');
            const currentStatus = $(this).data('current-status');
            const employeeName = $(this).data('employee-name');

            $('#updateUserId').val(userId);
            $('#updateEmployeeName').val(employeeName);
            $('#updateCurrentStatus').val(currentStatus);
            $('#updateNewStatus').val('');
            $('#updateRemarks').val('');

            $('#updateStatusModal').modal('show');
        });

        // Submit Update Status Form
        $('#updateStatusForm').on('submit', function (e) {
            e.preventDefault();

            const formData = {
                user_id: $('#updateUserId').val(),
                employment_state: $('#updateNewStatus').val(),
                remarks: $('#updateRemarks').val()
            };

            $.ajax({
                url: '/api/employees/status/update',
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Authorization': 'Bearer ' + authToken,
                    'Accept': 'application/json'
                },
                data: JSON.stringify(formData),
                contentType: 'application/json',
                success: function (response) {
                    toastr.success(response.message);
                    $('#updateStatusModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to update status';
                    toastr.error(message);
                }
            });
        });

        // Bulk Update Button
        $('#bulkUpdateBtn').on('click', function () {
            const selectedCount = $('.employee-checkbox:checked').length;
            $('#selectedCount').text(selectedCount);
            $('#bulkNewStatus').val('');
            $('#bulkRemarks').val('');
            $('#bulkUpdateStatusModal').modal('show');
        });

        // Submit Bulk Update Form
        $('#bulkUpdateStatusForm').on('submit', function (e) {
            e.preventDefault();

            const userIds = $('.employee-checkbox:checked').map(function () {
                return $(this).val();
            }).get();

            const formData = {
                user_ids: userIds,
                employment_state: $('#bulkNewStatus').val(),
                remarks: $('#bulkRemarks').val()
            };

            $.ajax({
                url: '/api/employees/status/bulk-update',
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Authorization': 'Bearer ' + authToken,
                    'Accept': 'application/json'
                },
                data: JSON.stringify(formData),
                contentType: 'application/json',
                success: function (response) {
                    toastr.success(response.message);
                    $('#bulkUpdateStatusModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to update status';
                    toastr.error(message);
                }
            });
        });

        function attachEventListeners() {
            $('.employee-checkbox').off('change').on('change', toggleBulkUpdateButton);
            $('.update-status-btn').off('click').on('click', function () {
                const userId = $(this).data('user-id');
                const currentStatus = $(this).data('current-status');
                const employeeName = $(this).data('employee-name');

                $('#updateUserId').val(userId);
                $('#updateEmployeeName').val(employeeName);
                $('#updateCurrentStatus').val(currentStatus);
                $('#updateNewStatus').val('');
                $('#updateRemarks').val('');

                $('#updateStatusModal').modal('show');
            });
        }

        attachEventListeners();
    </script>

    @include('tenant.employee.employee-status-approvals-script')
@endpush