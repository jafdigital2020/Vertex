<?php $page = 'recruitment-applications'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Job Applications</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Recruitment
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Applications</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
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
                        <button class="btn btn-outline-primary" onclick="toggleView()">
                            <i class="ti ti-layout-kanban" id="viewIcon"></i>
                            <span id="viewText">Kanban View</span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Applications List</h4>
                        </div>
                        <div class="card-body">
                            <!-- Filter Section -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="statusFilter">
                                        <option value="">All Status</option>
                                        <option value="submitted">Submitted</option>
                                        <option value="screening">Screening</option>
                                        <option value="interview_scheduled">Interview Scheduled</option>
                                        <option value="interview_completed">Interview Completed</option>
                                        <option value="offer_extended">Offer Extended</option>
                                        <option value="hired">Hired</option>
                                        <option value="rejected">Rejected</option>
                                        <option value="withdrawn">Withdrawn</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Job Position</label>
                                    <select class="form-select" id="jobFilter">
                                        <option value="">All Positions</option>
                                        @if(isset($jobPostings))
                                            @foreach($jobPostings as $job)
                                                <option value="{{ $job->id }}">{{ $job->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date Range</label>
                                    <select class="form-select" id="dateFilter">
                                        <option value="">All Time</option>
                                        <option value="today">Today</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                        <option value="quarter">This Quarter</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" class="form-control" id="searchFilter" placeholder="Candidate name, email...">
                                </div>
                            </div>

                            <!-- Table View -->
                            <div id="tableView">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Application ID</th>
                                                <th>Candidate</th>
                                                <th>Job Position</th>
                                                <th>Status</th>
                                                <th>Applied Date</th>
                                                <th>Last Update</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(isset($applications))
                                                @foreach($applications as $application)
                                                    <tr>
                                                        <td>
                                                            <span class="fw-bold text-primary">#{{ $application->application_number }}</span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar avatar-sm me-2">
                                                                    @if($application->candidate->photo)
                                                                        <img src="{{ asset('storage/' . $application->candidate->photo) }}" alt="Photo" class="img-fluid rounded-circle">
                                                                    @else
                                                                        <div class="avatar-initial rounded-circle bg-primary">
                                                                            {{ substr($application->candidate->first_name, 0, 1) }}{{ substr($application->candidate->last_name, 0, 1) }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-0">{{ $application->candidate->full_name }}</h6>
                                                                    <small class="text-muted">{{ $application->candidate->email }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <h6 class="mb-0">{{ $application->jobPosting->title }}</h6>
                                                                <small class="text-muted">{{ $application->jobPosting->department->department_name }}</small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if($application->status == 'submitted')
                                                                <span class="badge bg-info">Submitted</span>
                                                            @elseif($application->status == 'screening')
                                                                <span class="badge bg-warning">Screening</span>
                                                            @elseif($application->status == 'interview_scheduled')
                                                                <span class="badge bg-primary">Interview Scheduled</span>
                                                            @elseif($application->status == 'interview_completed')
                                                                <span class="badge bg-secondary">Interview Completed</span>
                                                            @elseif($application->status == 'offer_extended')
                                                                <span class="badge bg-warning">Offer Extended</span>
                                                            @elseif($application->status == 'hired')
                                                                <span class="badge bg-success">Hired</span>
                                                            @elseif($application->status == 'rejected')
                                                                <span class="badge bg-danger">Rejected</span>
                                                            @else
                                                                <span class="badge bg-secondary">{{ ucfirst($application->status) }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{ $application->created_at->format('M d, Y') }}
                                                        </td>
                                                        <td>
                                                            {{ $application->updated_at->diffForHumans() }}
                                                        </td>
                                                        <td>
                                                            <div class="dropdown">
                                                                <a href="javascript:void(0);" class="btn btn-white btn-sm btn-rounded dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                                                                </a>
                                                                <ul class="dropdown-menu dropdown-menu-right">
                                                                    @if (in_array('Read', $permission))
                                                                        <li>
                                                                            <a class="dropdown-item" href="{{ route('recruitment.applications.show', $application->id) }}">
                                                                                <i class="ti ti-eye me-2"></i>View Details
                                                                            </a>
                                                                        </li>
                                                                    @endif
                                                                    @if (in_array('Update', $permission))
                                                                        <li>
                                                                            <a class="dropdown-item" href="javascript:void(0);" onclick="updateStatus({{ $application->id }})">
                                                                                <i class="ti ti-edit me-2"></i>Update Status
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a class="dropdown-item" href="{{ route('recruitment.interviews.create') }}?application_id={{ $application->id }}">
                                                                                <i class="ti ti-calendar me-2"></i>Schedule Interview
                                                                            </a>
                                                                        </li>
                                                                    @endif
                                                                    <li>
                                                                        <a class="dropdown-item" href="{{ route('recruitment.candidates.show', $application->candidate->id) }}">
                                                                            <i class="ti ti-user me-2"></i>View Candidate
                                                                        </a>
                                                                    </li>
                                                                    @if($application->candidate->resume_path)
                                                                        <li>
                                                                            <a class="dropdown-item" href="{{ asset('storage/' . $application->candidate->resume_path) }}" target="_blank">
                                                                                <i class="ti ti-download me-2"></i>Download Resume
                                                                            </a>
                                                                        </li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                @if(isset($applications) && $applications->hasPages())
                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $applications->links() }}
                                    </div>
                                @endif

                                @if(!isset($applications) || $applications->isEmpty())
                                    <div class="text-center py-5">
                                        <img src="{{ URL::asset('build/img/empty-box.png') }}" alt="No Applications" class="mb-3" style="width: 100px;">
                                        <h5>No Applications Found</h5>
                                        <p class="text-muted">Applications will appear here when candidates apply for your job postings.</p>
                                        <a href="{{ route('recruitment.job-postings.index') }}" class="btn btn-primary">
                                            <i class="ti ti-briefcase me-2"></i>Manage Job Postings
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <!-- Kanban View (hidden by default) -->
                            <div id="kanbanView" style="display: none;">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="card">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="mb-0">Submitted</h6>
                                            </div>
                                            <div class="card-body p-2">
                                                <!-- Applications will be dynamically loaded here -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="card">
                                            <div class="card-header bg-warning text-white">
                                                <h6 class="mb-0">Screening</h6>
                                            </div>
                                            <div class="card-body p-2">
                                                <!-- Applications will be dynamically loaded here -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0">Interview</h6>
                                            </div>
                                            <div class="card-body p-2">
                                                <!-- Applications will be dynamically loaded here -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="card">
                                            <div class="card-header bg-secondary text-white">
                                                <h6 class="mb-0">Evaluation</h6>
                                            </div>
                                            <div class="card-body p-2">
                                                <!-- Applications will be dynamically loaded here -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="card">
                                            <div class="card-header bg-warning text-white">
                                                <h6 class="mb-0">Offer</h6>
                                            </div>
                                            <div class="card-body p-2">
                                                <!-- Applications will be dynamically loaded here -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="card">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="mb-0">Hired</h6>
                                            </div>
                                            <div class="card-body p-2">
                                                <!-- Applications will be dynamically loaded here -->
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
    <!-- /Page Wrapper -->

@endsection

@section('script')
<script>
    function toggleView() {
        const tableView = document.getElementById('tableView');
        const kanbanView = document.getElementById('kanbanView');
        const viewIcon = document.getElementById('viewIcon');
        const viewText = document.getElementById('viewText');

        if (tableView.style.display === 'none') {
            tableView.style.display = 'block';
            kanbanView.style.display = 'none';
            viewIcon.className = 'ti ti-layout-kanban';
            viewText.textContent = 'Kanban View';
        } else {
            tableView.style.display = 'none';
            kanbanView.style.display = 'block';
            viewIcon.className = 'ti ti-table';
            viewText.textContent = 'Table View';
        }
    }

    function updateStatus(applicationId) {
        // This would open a modal or redirect to update status
        // For now, let's use a simple prompt
        const newStatus = prompt('Enter new status (submitted, screening, interview_scheduled, interview_completed, offer_extended, hired, rejected, withdrawn):');
        if (newStatus) {
            fetch(`/recruitment/applications/${applicationId}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating status');
                }
            });
        }
    }

    // Filter functionality
    document.getElementById('statusFilter').addEventListener('change', filterApplications);
    document.getElementById('jobFilter').addEventListener('change', filterApplications);
    document.getElementById('dateFilter').addEventListener('change', filterApplications);
    document.getElementById('searchFilter').addEventListener('input', filterApplications);

    function filterApplications() {
        const params = new URLSearchParams();
        
        const status = document.getElementById('statusFilter').value;
        const job = document.getElementById('jobFilter').value;
        const date = document.getElementById('dateFilter').value;
        const search = document.getElementById('searchFilter').value;

        if (status) params.append('status', status);
        if (job) params.append('job_posting_id', job);
        if (date) params.append('date_range', date);
        if (search) params.append('search', search);

        window.location.href = '{{ route("recruitment.applications.index") }}?' + params.toString();
    }
</script>
@endsection