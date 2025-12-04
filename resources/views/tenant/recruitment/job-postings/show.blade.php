<?php $page = 'recruitment-job-postings'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">{{ $jobPosting->title }}</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('recruitment.job-postings.index') }}">Job Postings</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $jobPosting->job_code }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    @if (in_array('Update', $permission))
                        <div class="me-2 mb-2">
                            <a href="{{ route('recruitment.job-postings.edit', $jobPosting->id) }}" class="btn btn-primary d-flex align-items-center">
                                <i class="ti ti-edit me-2"></i>Edit Job
                            </a>
                        </div>
                    @endif
                    <div class="me-2 mb-2">
                        <a href="{{ route('recruitment.job-postings.index') }}" class="btn btn-outline-dark d-flex align-items-center">
                            <i class="ti ti-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="row">
                <!-- Job Details -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title">Job Details</h4>
                            <div>
                                @if($jobPosting->status == 'open')
                                    <span class="badge bg-success">{{ ucfirst($jobPosting->status) }}</span>
                                @elseif($jobPosting->status == 'closed')
                                    <span class="badge bg-danger">{{ ucfirst($jobPosting->status) }}</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($jobPosting->status) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Basic Info -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Department</h6>
                                    <p>{{ $jobPosting->department->department_name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Designation</h6>
                                    <p>{{ $jobPosting->designation->designation_name ?? 'Not specified' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Location</h6>
                                    <p>{{ $jobPosting->location ?? 'Not specified' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Employment Type</h6>
                                    <p>{{ ucwords(str_replace('-', ' ', $jobPosting->employment_type)) }}</p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <h6 class="text-muted">Job Description</h6>
                                <div>{!! nl2br(e($jobPosting->description)) !!}</div>
                            </div>

                            <!-- Requirements -->
                            @if($jobPosting->requirements)
                                <div class="mb-4">
                                    <h6 class="text-muted">Requirements</h6>
                                    <ul>
                                        @foreach($jobPosting->requirements as $requirement)
                                            <li>{{ $requirement }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Skills -->
                            @if($jobPosting->skills)
                                <div class="mb-4">
                                    <h6 class="text-muted">Required Skills</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($jobPosting->skills as $skill)
                                            <span class="badge bg-primary">{{ $skill }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Salary -->
                            @if($jobPosting->salary_min || $jobPosting->salary_max)
                                <div class="mb-4">
                                    <h6 class="text-muted">Salary Range</h6>
                                    <p class="text-success fw-bold">
                                        @if($jobPosting->salary_min && $jobPosting->salary_max)
                                            ₱{{ number_format($jobPosting->salary_min) }} - ₱{{ number_format($jobPosting->salary_max) }}
                                        @elseif($jobPosting->salary_min)
                                            From ₱{{ number_format($jobPosting->salary_min) }}
                                        @else
                                            Up to ₱{{ number_format($jobPosting->salary_max) }}
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Job Info Sidebar -->
                <div class="col-lg-4">
                    <!-- Job Statistics -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Job Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-primary">{{ $applicationsStats['total'] }}</h4>
                                    <small class="text-muted">Total Applications</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-warning">{{ $applicationsStats['new'] }}</h4>
                                    <small class="text-muted">New Applications</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h6 class="text-info">{{ $applicationsStats['in_review'] }}</h6>
                                    <small class="text-muted">In Review</small>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-success">{{ $applicationsStats['hired'] }}</h6>
                                    <small class="text-muted">Hired</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Info -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Job Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">Job Code</small>
                                <div class="fw-bold">{{ $jobPosting->job_code }}</div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Vacancies</small>
                                <div>{{ $jobPosting->vacancies }} position(s)</div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Created By</small>
                                <div>{{ $jobPosting->creator->username }}</div>
                            </div>
                            @if($jobPosting->recruiter)
                                <div class="mb-3">
                                    <small class="text-muted">Assigned Recruiter</small>
                                    <div>{{ $jobPosting->recruiter->username }}</div>
                                </div>
                            @endif
                            <div class="mb-3">
                                <small class="text-muted">Posted Date</small>
                                <div>{{ $jobPosting->posted_date ? $jobPosting->posted_date->format('M d, Y') : 'Not posted yet' }}</div>
                            </div>
                            @if($jobPosting->expiration_date)
                                <div class="mb-3">
                                    <small class="text-muted">Expiration Date</small>
                                    <div>
                                        {{ $jobPosting->expiration_date->format('M d, Y') }}
                                        @if($jobPosting->is_expired)
                                            <br><span class="text-danger small">Expired</span>
                                        @elseif($jobPosting->days_to_expiry <= 7)
                                            <br><span class="text-warning small">{{ $jobPosting->days_to_expiry }} days left</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            @if($jobPosting->status == 'draft' && in_array('Update', $permission))
                                <button class="btn btn-success w-100 mb-2" onclick="publishJob({{ $jobPosting->id }})">
                                    <i class="ti ti-send"></i> Publish Job
                                </button>
                            @endif
                            @if($jobPosting->status == 'open' && in_array('Update', $permission))
                                <button class="btn btn-warning w-100 mb-2" onclick="closeJob({{ $jobPosting->id }})">
                                    <i class="ti ti-x"></i> Close Job
                                </button>
                            @endif
                            @if(in_array('Create', $permission))
                                <button class="btn btn-info w-100 mb-2" onclick="cloneJob({{ $jobPosting->id }})">
                                    <i class="ti ti-copy"></i> Clone Job
                                </button>
                            @endif
                            <a href="{{ route('recruitment.applications.index') }}?job_posting_id={{ $jobPosting->id }}" class="btn btn-outline-primary w-100">
                                <i class="ti ti-users"></i> View Applications
                            </a>
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
    function publishJob(jobId) {
        if (confirm('Are you sure you want to publish this job posting?')) {
            fetch(`/recruitment/job-postings/${jobId}/publish`, {
                method: 'POST',
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
                    alert('Error publishing job posting');
                }
            });
        }
    }

    function closeJob(jobId) {
        if (confirm('Are you sure you want to close this job posting?')) {
            fetch(`/recruitment/job-postings/${jobId}/close`, {
                method: 'POST',
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
                    alert('Error closing job posting');
                }
            });
        }
    }

    function cloneJob(jobId) {
        if (confirm('Are you sure you want to clone this job posting?')) {
            fetch(`/recruitment/job-postings/${jobId}/clone`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '{{ route("recruitment.job-postings.index") }}';
                } else {
                    alert('Error cloning job posting');
                }
            });
        }
    }
</script>
@endsection