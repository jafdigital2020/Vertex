<?php $page = 'recruitment-manpower-requests'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">{{ $manpowerRequest->position }}</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('recruitment.manpower-requests.index') }}">Manpower Requests</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $manpowerRequest->request_number }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    @if (in_array('Update', $permission) && $manpowerRequest->can_edit)
                        <div class="me-2 mb-2">
                            <a href="{{ route('recruitment.manpower-requests.edit', $manpowerRequest->id) }}" class="btn btn-primary d-flex align-items-center">
                                <i class="ti ti-edit me-2"></i>Edit Request
                            </a>
                        </div>
                    @endif
                    <div class="me-2 mb-2">
                        <a href="{{ route('recruitment.manpower-requests.index') }}" class="btn btn-outline-dark d-flex align-items-center">
                            <i class="ti ti-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="row">
                <!-- Request Details -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title">Request Details</h4>
                            <div>
                                @if($manpowerRequest->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($manpowerRequest->status == 'pending_coo_approval')
                                    <span class="badge bg-info">Pending COO Approval</span>
                                @elseif($manpowerRequest->status == 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($manpowerRequest->status == 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @elseif($manpowerRequest->status == 'posted')
                                    <span class="badge bg-primary">Posted</span>
                                @elseif($manpowerRequest->status == 'filled')
                                    <span class="badge bg-success">Filled</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($manpowerRequest->status) }}</span>
                                @endif
                                <span class="badge {{ $manpowerRequest->priority_badge }} ms-2">{{ ucfirst($manpowerRequest->priority) }}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Basic Info -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Department</h6>
                                    <p>{{ $manpowerRequest->department->department_name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Designation</h6>
                                    <p>{{ $manpowerRequest->designation->designation_name ?? 'Not specified' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Employment Type</h6>
                                    <p>{{ ucwords(str_replace('-', ' ', $manpowerRequest->employment_type)) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Number of Vacancies</h6>
                                    <p>{{ $manpowerRequest->vacancies }} position(s)</p>
                                </div>
                            </div>

                            <!-- Business Justification -->
                            <div class="mb-4">
                                <h6 class="text-muted">Business Justification</h6>
                                <div class="bg-light p-3 rounded">
                                    {!! nl2br(e($manpowerRequest->justification)) !!}
                                </div>
                            </div>

                            <!-- Job Description -->
                            <div class="mb-4">
                                <h6 class="text-muted">Job Description</h6>
                                <div>{!! nl2br(e($manpowerRequest->job_description)) !!}</div>
                            </div>

                            <!-- Requirements -->
                            @if($manpowerRequest->requirements)
                                <div class="mb-4">
                                    <h6 class="text-muted">Requirements</h6>
                                    <ul>
                                        @foreach($manpowerRequest->requirements as $requirement)
                                            <li>{{ $requirement }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Skills -->
                            @if($manpowerRequest->skills)
                                <div class="mb-4">
                                    <h6 class="text-muted">Required Skills</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($manpowerRequest->skills as $skill)
                                            <span class="badge bg-primary">{{ $skill }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Salary -->
                            @if($manpowerRequest->salary_min || $manpowerRequest->salary_max)
                                <div class="mb-4">
                                    <h6 class="text-muted">Proposed Salary Range</h6>
                                    <p class="text-success fw-bold">
                                        @if($manpowerRequest->salary_min && $manpowerRequest->salary_max)
                                            ₱{{ number_format($manpowerRequest->salary_min) }} - ₱{{ number_format($manpowerRequest->salary_max) }}
                                        @elseif($manpowerRequest->salary_min)
                                            From ₱{{ number_format($manpowerRequest->salary_min) }}
                                        @else
                                            Up to ₱{{ number_format($manpowerRequest->salary_max) }}
                                        @endif
                                    </p>
                                </div>
                            @endif

                            <!-- Review Notes -->
                            @if($manpowerRequest->review_notes)
                                <div class="mb-4">
                                    <h6 class="text-muted">Review Notes</h6>
                                    <div class="alert alert-info">
                                        {!! nl2br(e($manpowerRequest->review_notes)) !!}
                                    </div>
                                </div>
                            @endif

                            <!-- Approval Notes -->
                            @if($manpowerRequest->approval_notes)
                                <div class="mb-4">
                                    <h6 class="text-muted">Approval Notes</h6>
                                    <div class="alert alert-success">
                                        {!! nl2br(e($manpowerRequest->approval_notes)) !!}
                                    </div>
                                </div>
                            @endif

                            <!-- Rejection Reason -->
                            @if($manpowerRequest->rejection_reason)
                                <div class="mb-4">
                                    <h6 class="text-muted">Rejection Reason</h6>
                                    <div class="alert alert-danger">
                                        {!! nl2br(e($manpowerRequest->rejection_reason)) !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Job Posting Info -->
                    @if($manpowerRequest->jobPosting)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title">Related Job Posting</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Job Code</h6>
                                        <p>{{ $manpowerRequest->jobPosting->job_code }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Status</h6>
                                        <p>
                                            @if($manpowerRequest->jobPosting->status == 'open')
                                                <span class="badge bg-success">Open</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($manpowerRequest->jobPosting->status) }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Posted Date</h6>
                                        <p>{{ $manpowerRequest->jobPosting->posted_date ? $manpowerRequest->jobPosting->posted_date->format('M d, Y') : 'Not posted yet' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Applications Received</h6>
                                        <p>
                                            <span class="badge bg-info">{{ $manpowerRequest->jobPosting->applications_count ?? 0 }}</span>
                                            @if($manpowerRequest->jobPosting->applications_count > 0)
                                                <a href="{{ route('recruitment.applications.index') }}?job_posting_id={{ $manpowerRequest->jobPosting->id }}" class="btn btn-sm btn-outline-primary ms-2">
                                                    View Applications
                                                </a>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('recruitment.job-postings.show', $manpowerRequest->jobPosting->id) }}" class="btn btn-primary">
                                        <i class="ti ti-eye me-2"></i>View Job Posting
                                    </a>
                                    @if($manpowerRequest->jobPosting->status == 'open')
                                        <a href="{{ route('career.show', $manpowerRequest->jobPosting->id) }}" class="btn btn-outline-info" target="_blank">
                                            <i class="ti ti-external-link me-2"></i>View Public Page
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Request Info Sidebar -->
                <div class="col-lg-4">
                    <!-- Request Information -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Request Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">Request Number</small>
                                <div class="fw-bold">{{ $manpowerRequest->request_number }}</div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Requested By</small>
                                <div>{{ $manpowerRequest->requester->username }}</div>
                            </div>
                            @if($manpowerRequest->reviewer)
                                <div class="mb-3">
                                    <small class="text-muted">Reviewed By</small>
                                    <div>{{ $manpowerRequest->reviewer->username }}</div>
                                </div>
                            @endif
                            @if($manpowerRequest->approver)
                                <div class="mb-3">
                                    <small class="text-muted">{{ $manpowerRequest->status == 'rejected' ? 'Rejected' : 'Approved' }} By</small>
                                    <div>{{ $manpowerRequest->approver->username }}</div>
                                </div>
                            @endif
                            <div class="mb-3">
                                <small class="text-muted">Submitted Date</small>
                                <div>{{ $manpowerRequest->submitted_at ? $manpowerRequest->submitted_at->format('M d, Y') : 'Not submitted yet' }}</div>
                            </div>
                            @if($manpowerRequest->reviewed_at)
                                <div class="mb-3">
                                    <small class="text-muted">Reviewed Date</small>
                                    <div>{{ $manpowerRequest->reviewed_at->format('M d, Y') }}</div>
                                </div>
                            @endif
                            @if($manpowerRequest->approved_at)
                                <div class="mb-3">
                                    <small class="text-muted">{{ $manpowerRequest->status == 'rejected' ? 'Rejected' : 'Approved' }} Date</small>
                                    <div>{{ $manpowerRequest->approved_at->format('M d, Y') }}</div>
                                </div>
                            @endif
                            @if($manpowerRequest->target_start_date)
                                <div class="mb-3">
                                    <small class="text-muted">Target Start Date</small>
                                    <div>{{ $manpowerRequest->target_start_date->format('M d, Y') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Actions</h5>
                        </div>
                        <div class="card-body">
                            @if($manpowerRequest->status == 'pending' && in_array('Update', $permission))
                                <button class="btn btn-info w-100 mb-2" onclick="submitForReview({{ $manpowerRequest->id }})">
                                    <i class="ti ti-send"></i> Submit to COO
                                </button>
                            @endif
                            @if($manpowerRequest->can_approve && in_array('Update', $permission))
                                <button class="btn btn-success w-100 mb-2" onclick="approveRequest({{ $manpowerRequest->id }})">
                                    <i class="ti ti-check"></i> Approve Request
                                </button>
                            @endif
                            @if($manpowerRequest->can_reject && in_array('Update', $permission))
                                <button class="btn btn-danger w-100 mb-2" onclick="rejectRequest({{ $manpowerRequest->id }})">
                                    <i class="ti ti-x"></i> Reject Request
                                </button>
                            @endif
                            @if($manpowerRequest->can_post && in_array('Update', $permission))
                                <button class="btn btn-primary w-100 mb-2" onclick="postJob({{ $manpowerRequest->id }})">
                                    <i class="ti ti-world"></i> Post Job
                                </button>
                            @endif
                            @if($manpowerRequest->can_edit && in_array('Update', $permission))
                                <a href="{{ route('recruitment.manpower-requests.edit', $manpowerRequest->id) }}" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="ti ti-edit"></i> Edit Request
                                </a>
                            @endif
                            <a href="{{ route('recruitment.manpower-requests.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="ti ti-arrow-left"></i> Back to List
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
    function submitForReview(requestId) {
        if (confirm('Submit this request to COO for approval?')) {
            performAction(requestId, 'submit-review', 'Submitting...');
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