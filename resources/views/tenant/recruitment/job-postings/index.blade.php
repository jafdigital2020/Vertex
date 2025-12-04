<?php $page = 'recruitment-job-postings'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Job Posts & Recruitment Management</h2>
                    <p class="mb-0">Manage manpower requests, COO approvals, and job postings</p>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Recruitment
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Job Postings</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    @if (in_array('Create', $permission))
                        <div class="me-2 mb-2">
                            <a href="{{ route('recruitment.job-postings.create') }}" class="btn btn-primary d-flex align-items-center">
                                <i class="ti ti-square-rounded-plus-filled me-2"></i>Create Job Posting
                            </a>
                        </div>
                    @endif
                    @if (in_array('Export', $permission))
                        <div class="me-2 mb-2">
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                    <i class="ti ti-file-export me-1"></i>Export
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Export as Excel</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
                    <div class="me-2 mb-2">
                        <button class="btn btn-outline-primary" onclick="toggleView()" id="viewToggleBtn">
                            <i class="ti ti-table" id="viewIcon"></i>
                            <span id="viewText">All Statuses</span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- HREP Workflow Info -->
            <div class="alert alert-info mb-4">
                <h6><i class="ti ti-info-circle me-2"></i>HREP Workflow</h6>
                <ol class="mb-0">
                    <li>Review manpower requests from department managers</li>
                    <li>Submit approved requests to COO for final approval and salary determination</li>
                    <li>Post approved jobs to the careers page</li>
                    <li>Close job postings when positions are filled</li>
                </ol>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="d-block text-muted small">Total Postings</span>
                                    <h4 class="text-primary">{{ $statistics['total'] }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-primary-transparent rounded">
                                    <i class="ti ti-files text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="d-block text-muted small">Draft</span>
                                    <h4 class="text-warning">{{ $statistics['draft'] }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-warning-transparent rounded">
                                    <i class="ti ti-edit text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="d-block text-muted small">Open</span>
                                    <h4 class="text-success">{{ $statistics['open'] }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-success-transparent rounded">
                                    <i class="ti ti-world text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="d-block text-muted small">Closed</span>
                                    <h4 class="text-info">{{ $statistics['closed'] }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-info-transparent rounded">
                                    <i class="ti ti-lock text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="d-block text-muted small">Filled</span>
                                    <h4 class="text-success">{{ $statistics['filled'] }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-success-transparent rounded">
                                    <i class="ti ti-user-check text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="d-block text-muted small">Total Apps</span>
                                    <h4 class="text-primary">{{ $statistics['total_applications'] }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-primary-transparent rounded">
                                    <i class="ti ti-users text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Tabs -->
            <div class="card">
                <div class="card-body p-0">
                    <ul class="nav nav-tabs nav-tabs-bottom" id="statusTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ request('status') == '' ? 'active' : '' }}" onclick="filterByStatus('')" type="button">
                                All <span class="badge bg-secondary ms-1">{{ $statistics['total'] }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ request('status') == 'draft' ? 'active' : '' }}" onclick="filterByStatus('draft')" type="button">
                                Draft <span class="badge bg-warning ms-1">{{ $statistics['draft'] }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ request('status') == 'open' ? 'active' : '' }}" onclick="filterByStatus('open')" type="button">
                                Open <span class="badge bg-success ms-1">{{ $statistics['open'] }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ request('status') == 'closed' ? 'active' : '' }}" onclick="filterByStatus('closed')" type="button">
                                Closed <span class="badge bg-secondary ms-1">{{ $statistics['closed'] }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ request('status') == 'filled' ? 'active' : '' }}" onclick="filterByStatus('filled')" type="button">
                                Filled <span class="badge bg-info ms-1">{{ $statistics['filled'] }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body py-2">
                    <form method="GET" action="{{ route('recruitment.job-postings.index') }}" id="filterForm">
                        <input type="hidden" name="status" id="statusFilter" value="{{ request('status') }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-2">
                                <select name="department_id" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search job postings...">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('recruitment.job-postings.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="ti ti-refresh"></i> Reset
                                </a>
                            </div>
                            <div class="col-md-5 text-end">
                                <div class="d-flex align-items-center gap-2 justify-content-end">
                                    <small class="text-muted">{{ $jobPostings->total() }} total job postings</small>
                                    @if(in_array('Create', $permission))
                                        <a href="{{ route('recruitment.job-postings.create') }}" class="btn btn-primary btn-sm">
                                            <i class="ti ti-plus me-1"></i>New Job Posting
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-0">

                            @if($jobPostings->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="10%">Job Code</th>
                                                <th width="20%">Position</th>
                                                <th width="15%">Department</th>
                                                <th width="8%">Vacancies</th>
                                                <th width="12%">Salary Range</th>
                                                <th width="12%">Created By</th>
                                                <th width="10%">Status</th>
                                                <th width="8%">Applicants</th>
                                                <th width="5%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($jobPostings as $posting)
                                                <tr>
                                                    <td>
                                                        <span class="fw-bold text-primary">{{ $posting->job_code }}</span>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <span class="fw-semibold">{{ $posting->title }}</span>
                                                            @if($posting->designation)
                                                                <small class="text-muted d-block">{{ $posting->designation->designation_name }}</small>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>{{ $posting->department->department_name }}</td>
                                                    <td>
                                                        <span class="fw-semibold">{{ $posting->vacancies }} position{{ $posting->vacancies > 1 ? 's' : '' }}</span>
                                                    </td>
                                                    <td>
                                                        @if($posting->salary_min || $posting->salary_max)
                                                            <span class="text-success fw-semibold">
                                                                @if($posting->salary_min && $posting->salary_max)
                                                                    ₱{{ number_format($posting->salary_min/1000, 0) }}K - ₱{{ number_format($posting->salary_max/1000, 0) }}K
                                                                @elseif($posting->salary_min)
                                                                    From ₱{{ number_format($posting->salary_min/1000, 0) }}K
                                                                @else
                                                                    Up to ₱{{ number_format($posting->salary_max/1000, 0) }}K
                                                                @endif
                                                            </span>
                                                        @else
                                                            <span class="text-muted">Not specified</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <span class="fw-semibold">{{ $posting->creator->username }}</span>
                                                            <small class="text-muted d-block">{{ $posting->posted_date ? $posting->posted_date->format('M d, Y') : ($posting->created_at ? $posting->created_at->format('M d, Y') : 'Not posted') }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($posting->status == 'open')
                                                            <span class="badge bg-success">Open</span>
                                                        @elseif($posting->status == 'closed')
                                                            <span class="badge bg-secondary">Closed</span>
                                                        @elseif($posting->status == 'draft')
                                                            <span class="badge bg-warning">Draft</span>
                                                        @elseif($posting->status == 'filled')
                                                            <span class="badge bg-info">Filled</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ ucfirst($posting->status) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-primary">{{ $posting->applications_count ?? 0 }}</span>
                                                            @if($posting->applications_count > 0)
                                                                <a href="{{ route('recruitment.applications.index') }}?job_posting_id={{ $posting->id }}" class="btn btn-sm btn-outline-primary ms-1" title="View Applications">
                                                                    <i class="ti ti-eye"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="dropdown">
                                                                <i class="ti ti-dots-vertical"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li><a class="dropdown-item" href="{{ route('recruitment.job-postings.show', $posting->id) }}"><i class="ti ti-eye me-2"></i>View Details</a></li>
                                                                @if(in_array('Update', $permission))
                                                                    <li><a class="dropdown-item" href="#"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                                                    @if($posting->status == 'draft')
                                                                        <li><button class="dropdown-item" onclick="publishJob({{ $posting->id }})"><i class="ti ti-world me-2"></i>Publish</button></li>
                                                                    @elseif($posting->status == 'open')
                                                                        <li><button class="dropdown-item" onclick="closeJob({{ $posting->id }})"><i class="ti ti-x me-2"></i>Close Posting</button></li>
                                                                    @endif
                                                                    <li><button class="dropdown-item" onclick="cloneJob({{ $posting->id }})"><i class="ti ti-copy me-2"></i>Clone Posting</button></li>
                                                                @endif
                                                                @if($posting->status == 'open')
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li><a class="dropdown-item" href="{{ route('career.show', $posting->id) }}" target="_blank"><i class="ti ti-external-link me-2"></i>View Public Page</a></li>
                                                                @endif
                                                                @if(in_array('Delete', $permission) && ($posting->applications_count ?? 0) == 0)
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li><button class="dropdown-item text-danger" onclick="deleteJob({{ $posting->id }})"><i class="ti ti-trash me-2"></i>Delete</button></li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="ti ti-briefcase display-4 text-muted mb-3"></i>
                                    <h5>No Job Postings Found</h5>
                                    <p class="text-muted">{{ request('status') || request('search') || request('department_id') ? 'Try adjusting your filters to see more results.' : 'Create your first job posting to start the recruitment process!' }}</p>
                                    @if(in_array('Create', $permission) && !request()->hasAny(['status', 'search', 'department_id']))
                                        <a href="{{ route('recruitment.job-postings.create') }}" class="btn btn-primary">
                                            <i class="ti ti-plus me-2"></i>Create Job Posting
                                        </a>
                                    @endif
                                </div>
                            @endif

                            @if($jobPostings->hasPages())
                                <div class="d-flex justify-content-between align-items-center p-3">
                                    <small class="text-muted">Showing {{ $jobPostings->firstItem() }} to {{ $jobPostings->lastItem() }} of {{ $jobPostings->total() }} job postings</small>
                                    {{ $jobPostings->withQueryString()->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- /Page Wrapper -->

@endsection

@section('script')
<script>
    // Status tab filtering
    function filterByStatus(status) {
        // Update active tab
        document.querySelectorAll('#statusTabs .nav-link').forEach(tab => {
            tab.classList.remove('active');
        });
        event.target.classList.add('active');
        
        // Update hidden field and submit form
        document.getElementById('statusFilter').value = status;
        document.getElementById('filterForm').submit();
    }

    function publishJob(jobId) {
        if (confirm('Are you sure you want to publish this job posting?')) {
            performAction(jobId, 'publish', 'Job posting published successfully!');
        }
    }

    function closeJob(jobId) {
        if (confirm('Are you sure you want to close this job posting?')) {
            performAction(jobId, 'close', 'Job posting closed successfully!');
        }
    }

    function cloneJob(jobId) {
        if (confirm('Create a copy of this job posting?')) {
            performAction(jobId, 'clone', 'Job posting cloned successfully!');
        }
    }

    function deleteJob(jobId) {
        if (confirm('Are you sure you want to delete this job posting? This action cannot be undone.')) {
            fetch(`/recruitment/job-postings/${jobId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error deleting job. Please try again.');
                console.error('Error:', error);
            });
        }
    }

    function performAction(jobId, action, successMessage) {
        fetch(`/recruitment/job-postings/${jobId}/${action}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert(`Error performing ${action}. Please try again.`);
            console.error('Error:', error);
        });
    }
</script>
@endsection