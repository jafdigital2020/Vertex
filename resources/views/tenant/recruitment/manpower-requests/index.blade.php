<?php $page = 'recruitment-manpower-requests'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="mb-1 fw-bold">Talent Management Overview</h2>
                            <p class="text-muted mb-0">Manage recruitment, hiring, and onboarding processes</p>
                        </div>
                        @if (in_array('Create', $permission))
                            <button class="btn btn-primary px-4" onclick="window.location.href='{{ route('recruitment.manpower-requests.create') }}'">
                                <i class="ti ti-plus me-2"></i>New Manpower Request
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- My Manpower Requests Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="fw-bold text-primary mb-1">My Manpower Requests</h5>
                                    <p class="text-muted mb-0">Request additional headcount for your team</p>
                                </div>
                                @if (in_array('Create', $permission))
                                    <button class="btn btn-primary btn-sm px-3" onclick="window.location.href='{{ route('recruitment.manpower-requests.create') }}'">
                                        <i class="ti ti-plus me-1"></i>New Manpower Request
                                    </button>
                                @endif
                            </div>

                            <!-- Request Workflow Info -->
                            <div class="alert alert-light border-start border-primary border-3 mb-4">
                                <div class="d-flex align-items-start">
                                    <i class="ti ti-info-circle text-primary me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-semibold mb-1">Request Workflow</h6>
                                        <p class="mb-0 text-muted">Your manpower request will be reviewed by HREP, then forwarded to COO for approval. Once approved, the job will be posted by HREP.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistics Row -->
                            <div class="row g-3 mb-4">
                                <div class="col-lg-2 col-md-4">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="icon-circle bg-primary bg-opacity-10 me-3">
                                            <i class="ti ti-files text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fs-5 fw-bold text-dark">{{ $stats['total'] }}</div>
                                            <div class="text-muted small">Total Requests</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-4">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="icon-circle bg-secondary bg-opacity-10 me-3">
                                            <i class="ti ti-edit text-secondary"></i>
                                        </div>
                                        <div>
                                            <div class="fs-5 fw-bold text-dark">{{ $stats['total'] - $stats['pending_coo'] - $stats['ready_to_post'] - $stats['posted'] - $stats['filled'] - $stats['rejected'] }}</div>
                                            <div class="text-muted small">Draft</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-4">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="icon-circle bg-warning bg-opacity-10 me-3">
                                            <i class="ti ti-clock text-warning"></i>
                                        </div>
                                        <div>
                                            <div class="fs-5 fw-bold text-dark">{{ $stats['pending_coo'] }}</div>
                                            <div class="text-muted small">Pending</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-4">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="icon-circle bg-success bg-opacity-10 me-3">
                                            <i class="ti ti-check-circle text-success"></i>
                                        </div>
                                        <div>
                                            <div class="fs-5 fw-bold text-dark">{{ $stats['ready_to_post'] }}</div>
                                            <div class="text-muted small">Approved/Posted</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-4">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="icon-circle bg-danger bg-opacity-10 me-3">
                                            <i class="ti ti-x-circle text-danger"></i>
                                        </div>
                                        <div>
                                            <div class="fs-5 fw-bold text-dark">{{ $stats['rejected'] }}</div>
                                            <div class="text-muted small">Rejected</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-4">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="icon-circle bg-info bg-opacity-10 me-3">
                                            <i class="ti ti-user-check text-info"></i>
                                        </div>
                                        <div>
                                            <div class="fs-5 fw-bold text-dark">{{ $stats['filled'] }}</div>
                                            <div class="text-muted small">Filled</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Search Bar -->
                        <div class="position-relative" style="width: 400px;">
                            <i class="ti ti-search position-absolute start-0 top-50 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" class="form-control ps-5" id="searchFilter" placeholder="Search by position, department, or request number..." style="border-radius: 8px;">
                        </div>

                        <!-- Filter Dropdowns -->
                        <div class="d-flex gap-2">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="ti ti-list me-1"></i>All Positions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="filterByStatus('')">All Positions</a></li>
                                    @foreach($departments as $dept)
                                        <li><a class="dropdown-item" href="#" onclick="filterByDepartment('{{ $dept->id }}')">{{ $dept->department_name }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="ti ti-building me-1"></i>All Departments
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="filterByDepartment('')">All Departments</a></li>
                                    @foreach($departments as $dept)
                                        <li><a class="dropdown-item" href="#" onclick="filterByDepartment('{{ $dept->id }}')">{{ $dept->department_name }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="ti ti-calendar me-1"></i>All Time
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="filterByTime('')">All Time</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterByTime('today')">Today</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterByTime('week')">This Week</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="filterByTime('month')">This Month</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Tabs -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="border-bottom">
                                <nav class="nav nav-tabs border-0" role="tablist">
                                    <button class="nav-link {{ request('status') == '' ? 'active' : '' }} border-0 px-4 py-3" onclick="filterByStatus('')" type="button">
                                        All ({{ $stats['total'] }})
                                    </button>
                                    <button class="nav-link {{ request('status') == 'pending' ? 'active' : '' }} border-0 px-4 py-3" onclick="filterByStatus('pending')" type="button">
                                        Draft ({{ $stats['total'] - $stats['pending_coo'] - $stats['ready_to_post'] - $stats['posted'] - $stats['filled'] - $stats['rejected'] }})
                                    </button>
                                    <button class="nav-link {{ request('status') == 'pending_coo_approval' ? 'active' : '' }} border-0 px-4 py-3" onclick="filterByStatus('pending_coo_approval')" type="button">
                                        Pending ({{ $stats['pending_coo'] }})
                                    </button>
                                    <button class="nav-link {{ request('status') == 'approved' ? 'active' : '' }} border-0 px-4 py-3" onclick="filterByStatus('approved')" type="button">
                                        Approved ({{ $stats['ready_to_post'] }})
                                    </button>
                                    <button class="nav-link {{ request('status') == 'posted' ? 'active' : '' }} border-0 px-4 py-3" onclick="filterByStatus('posted')" type="button">
                                        Posted ({{ $stats['posted'] }})
                                    </button>
                                    <button class="nav-link {{ request('status') == 'rejected' ? 'active' : '' }} border-0 px-4 py-3" onclick="filterByStatus('rejected')" type="button">
                                        Rejected ({{ $stats['rejected'] }})
                                    </button>
                                    <button class="nav-link {{ request('status') == 'filled' ? 'active' : '' }} border-0 px-4 py-3" onclick="filterByStatus('filled')" type="button">
                                        Filled ({{ $stats['filled'] }})
                                    </button>
                                </nav>
                            </div>

                            <!-- Table Content -->
                            @if($requests->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" style="font-size: 14px;">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0 px-4 py-3 fw-semibold text-muted">Request #</th>
                                                <th class="border-0 px-4 py-3 fw-semibold text-muted">Position</th>
                                                <th class="border-0 px-4 py-3 fw-semibold text-muted">Department</th>
                                                <th class="border-0 px-4 py-3 fw-semibold text-muted">Vacancies</th>
                                                <th class="border-0 px-4 py-3 fw-semibold text-muted">Request Date</th>
                                                <th class="border-0 px-4 py-3 fw-semibold text-muted">Status</th>
                                                <th class="border-0 px-4 py-3 fw-semibold text-muted">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($requests as $request)
                                                <tr class="border-0">
                                                    <td class="px-4 py-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="icon-circle bg-primary bg-opacity-10 me-3" style="width: 32px; height: 32px;">
                                                                <i class="ti ti-file-text text-primary small"></i>
                                                            </div>
                                                            <span class="fw-semibold text-primary">{{ $request->request_number }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div>
                                                            <div class="fw-semibold text-dark">{{ $request->position }}</div>
                                                            <div class="text-muted small">{{ ucwords(str_replace('-', ' ', $request->employment_type)) }}</div>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="icon-circle bg-secondary bg-opacity-10 me-2" style="width: 24px; height: 24px;">
                                                                <i class="ti ti-building text-secondary" style="font-size: 12px;"></i>
                                                            </div>
                                                            <span>{{ $request->department->department_name }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="icon-circle bg-info bg-opacity-10 me-2" style="width: 24px; height: 24px;">
                                                                <i class="ti ti-users text-info" style="font-size: 12px;"></i>
                                                            </div>
                                                            <span class="fw-semibold">{{ $request->vacancies }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="icon-circle bg-secondary bg-opacity-10 me-2" style="width: 24px; height: 24px;">
                                                                <i class="ti ti-calendar text-secondary" style="font-size: 12px;"></i>
                                                            </div>
                                                            <span>{{ $request->submitted_at ? $request->submitted_at->format('n/j/Y') : $request->created_at->format('n/j/Y') }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @if($request->status == 'pending')
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border-0 px-3 py-2">
                                                                <i class="ti ti-edit me-1"></i>Draft
                                                            </span>
                                                        @elseif($request->status == 'pending_coo_approval')
                                                            <span class="badge bg-warning bg-opacity-10 text-warning border-0 px-3 py-2">
                                                                <i class="ti ti-clock me-1"></i>Pending COO Approval
                                                            </span>
                                                        @elseif($request->status == 'approved')
                                                            <span class="badge bg-success bg-opacity-10 text-success border-0 px-3 py-2">
                                                                <i class="ti ti-check me-1"></i>Approved
                                                            </span>
                                                        @elseif($request->status == 'rejected')
                                                            <span class="badge bg-danger bg-opacity-10 text-danger border-0 px-3 py-2">
                                                                <i class="ti ti-x me-1"></i>Rejected
                                                            </span>
                                                        @elseif($request->status == 'posted')
                                                            <span class="badge bg-primary bg-opacity-10 text-primary border-0 px-3 py-2">
                                                                <i class="ti ti-world me-1"></i>Posted
                                                            </span>
                                                        @elseif($request->status == 'filled')
                                                            <span class="badge bg-info bg-opacity-10 text-info border-0 px-3 py-2">
                                                                <i class="ti ti-user-check me-1"></i>Filled
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border-0 px-3 py-2">
                                                                {{ ucfirst($request->status) }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="d-flex align-items-center gap-2">
                                                            @if($request->status == 'pending' && $request->can_edit && in_array('Update', $permission))
                                                                <button class="btn btn-primary btn-sm px-3" onclick="submitForReview({{ $request->id }})">
                                                                    <i class="ti ti-send me-1"></i>Submit Request
                                                                </button>
                                                            @endif
                                                            
                                                            <div class="dropdown">
                                                                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="dropdown" style="border-radius: 6px;">
                                                                    <i class="ti ti-dots-vertical"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                                                    <li><a class="dropdown-item py-2" href="{{ route('recruitment.manpower-requests.show', $request->id) }}">
                                                                        <i class="ti ti-eye me-2 text-primary"></i>View Details
                                                                    </a></li>
                                                                    @if($request->can_edit && in_array('Update', $permission))
                                                                        <li><a class="dropdown-item py-2" href="{{ route('recruitment.manpower-requests.edit', $request->id) }}">
                                                                            <i class="ti ti-edit me-2 text-secondary"></i>Edit Request
                                                                        </a></li>
                                                                    @endif
                                                                    @if($request->can_approve && in_array('Update', $permission))
                                                                        <li><button class="dropdown-item py-2" onclick="approveRequest({{ $request->id }})">
                                                                            <i class="ti ti-check me-2 text-success"></i>Approve Request
                                                                        </button></li>
                                                                    @endif
                                                                    @if($request->can_reject && in_array('Update', $permission))
                                                                        <li><button class="dropdown-item py-2" onclick="rejectRequest({{ $request->id }})">
                                                                            <i class="ti ti-x me-2 text-danger"></i>Reject Request
                                                                        </button></li>
                                                                    @endif
                                                                    @if($request->can_post && in_array('Update', $permission))
                                                                        <li><button class="dropdown-item py-2" onclick="postJob({{ $request->id }})">
                                                                            <i class="ti ti-world me-2 text-info"></i>Post Job
                                                                        </button></li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="icon-circle bg-light mx-auto mb-3" style="width: 80px; height: 80px;">
                                        <i class="ti ti-file-text text-muted" style="font-size: 32px;"></i>
                                    </div>
                                    <h5 class="fw-semibold mb-2">No Manpower Requests</h5>
                                    <p class="text-muted mb-4">{{ request()->hasAny(['status', 'search', 'department_id']) ? 'No requests match your current filters.' : 'Start the recruitment process by creating manpower requests for open positions.' }}</p>
                                    @if(in_array('Create', $permission) && !request()->hasAny(['status', 'search', 'department_id']))
                                        <button class="btn btn-primary px-4" onclick="window.location.href='{{ route('recruitment.manpower-requests.create') }}'">
                                            <i class="ti ti-plus me-2"></i>Create First Request
                                        </button>
                                    @endif
                                </div>
                            @endif

                            <!-- Pagination -->
                            @if($requests->hasPages())
                                <div class="border-top px-4 py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            Showing {{ $requests->firstItem() }} to {{ $requests->lastItem() }} of {{ $requests->total() }} requests
                                        </div>
                                        {{ $requests->withQueryString()->links() }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- /Page Wrapper -->

    <!-- Hidden filter form -->
    <form method="GET" action="{{ route('recruitment.manpower-requests.index') }}" id="filterForm" style="display: none;">
        <input type="hidden" name="status" id="statusFilter" value="{{ request('status') }}">
        <input type="hidden" name="department_id" id="departmentFilter" value="{{ request('department_id') }}">
        <input type="hidden" name="search" id="searchInput" value="{{ request('search') }}">
    </form>

@endsection

@section('script')
<style>
.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nav-tabs .nav-link {
    color: #6c757d;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    color: #007bff;
    border-bottom: 2px solid #007bff !important;
    background: none;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.dropdown-menu {
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.btn {
    border-radius: 6px;
    font-weight: 500;
}

.card {
    border-radius: 12px;
}
</style>

<script>
    // Status filtering
    function filterByStatus(status) {
        document.getElementById('statusFilter').value = status;
        document.getElementById('filterForm').submit();
    }

    // Department filtering  
    function filterByDepartment(departmentId) {
        document.getElementById('departmentFilter').value = departmentId;
        document.getElementById('filterForm').submit();
    }

    // Time filtering (placeholder)
    function filterByTime(time) {
        // Could be implemented to filter by date ranges
        console.log('Filter by time:', time);
    }

    // Search functionality
    let searchTimeout;
    document.getElementById('searchFilter').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('searchInput').value = e.target.value;
            document.getElementById('filterForm').submit();
        }, 500);
    });

    // Action functions
    function submitForReview(requestId) {
        if (confirm('Submit this request to COO for approval?')) {
            performAction(requestId, 'submit-for-review', 'Submitting...');
        }
    }

    function approveRequest(requestId) {
        const notes = prompt('Approval notes (optional):');
        if (notes !== null) {
            performAction(requestId, 'approve', 'Approving...', { notes: notes });
        }
    }

    function rejectRequest(requestId) {
        const reason = prompt('Reason for rejection (required):');
        if (reason && reason.trim() !== '') {
            performAction(requestId, 'reject', 'Rejecting...', { reason: reason });
        } else if (reason !== null) {
            alert('Please provide a reason for rejection.');
        }
    }

    function postJob(requestId) {
        if (confirm('Create and post job posting from this request?')) {
            performAction(requestId, 'post-job', 'Posting...');
        }
    }

    function performAction(requestId, action, loadingText, data = {}) {
        fetch(`/recruitment/manpower-requests/${requestId}/${action}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Something went wrong'));
            }
        })
        .catch(error => {
            alert('Error performing action. Please try again.');
            console.error('Error:', error);
        });
    }
</script>
@endsection