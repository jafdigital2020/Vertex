<?php $page = 'recruitment-interviews'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Interviews</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Recruitment
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Interviews</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    @if (in_array('Create', $permission))
                        <div class="me-2 mb-2">
                            <a href="{{ route('recruitment.interviews.create') }}" class="btn btn-primary d-flex align-items-center">
                                <i class="ti ti-square-rounded-plus-filled me-2"></i>Schedule Interview
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
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Scheduled Interviews</h4>
                        </div>
                        <div class="card-body">
                            <!-- Filter Section -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="statusFilter">
                                        <option value="">All Status</option>
                                        <option value="scheduled">Scheduled</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="no_show">No Show</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Interview Type</label>
                                    <select class="form-select" id="typeFilter">
                                        <option value="">All Types</option>
                                        <option value="initial">Initial Screening</option>
                                        <option value="technical">Technical</option>
                                        <option value="final">Final</option>
                                        <option value="panel">Panel</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date Range</label>
                                    <select class="form-select" id="dateFilter">
                                        <option value="">All Dates</option>
                                        <option value="today">Today</option>
                                        <option value="tomorrow">Tomorrow</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" class="form-control" id="searchFilter" placeholder="Candidate name, position...">
                                </div>
                            </div>

                            <!-- Interviews Table -->
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Candidate</th>
                                            <th>Position</th>
                                            <th>Interview Type</th>
                                            <th>Date & Time</th>
                                            <th>Interviewer(s)</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($interviews))
                                            @foreach($interviews as $interview)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm me-2">
                                                                @if($interview->application->candidate->photo)
                                                                    <img src="{{ asset('storage/' . $interview->application->candidate->photo) }}" alt="Photo" class="img-fluid rounded-circle">
                                                                @else
                                                                    <div class="avatar-initial rounded-circle bg-primary">
                                                                        {{ substr($interview->application->candidate->first_name, 0, 1) }}{{ substr($interview->application->candidate->last_name, 0, 1) }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0">{{ $interview->application->candidate->full_name }}</h6>
                                                                <small class="text-muted">{{ $interview->application->candidate->email }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <h6 class="mb-0">{{ $interview->application->jobPosting->title }}</h6>
                                                            <small class="text-muted">{{ $interview->application->jobPosting->department->department_name }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">{{ ucfirst($interview->interview_type) }}</span>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($interview->scheduled_date)->format('M d, Y') }}</div>
                                                            <small class="text-muted">{{ \Carbon\Carbon::parse($interview->scheduled_time)->format('h:i A') }}</small>
                                                            @if($interview->location)
                                                                <br><small class="text-muted">{{ $interview->location }}</small>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($interview->interviewer)
                                                            <div>{{ $interview->interviewer->username }}</div>
                                                        @endif
                                                        @if($interview->panel_members)
                                                            @foreach($interview->panel_members as $member)
                                                                <small class="text-muted d-block">{{ $member }}</small>
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($interview->status == 'scheduled')
                                                            <span class="badge bg-info">Scheduled</span>
                                                        @elseif($interview->status == 'in_progress')
                                                            <span class="badge bg-warning">In Progress</span>
                                                        @elseif($interview->status == 'completed')
                                                            <span class="badge bg-success">Completed</span>
                                                        @elseif($interview->status == 'cancelled')
                                                            <span class="badge bg-danger">Cancelled</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ ucfirst($interview->status) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <a href="javascript:void(0);" class="btn btn-white btn-sm btn-rounded dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                                                            </a>
                                                            <ul class="dropdown-menu dropdown-menu-right">
                                                                @if (in_array('Read', $permission))
                                                                    <li>
                                                                        <a class="dropdown-item" href="{{ route('recruitment.interviews.show', $interview->id) }}">
                                                                            <i class="ti ti-eye me-2"></i>View Details
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                @if (in_array('Update', $permission) && in_array($interview->status, ['scheduled', 'in_progress']))
                                                                    <li>
                                                                        <a class="dropdown-item" href="{{ route('recruitment.interviews.edit', $interview->id) }}">
                                                                            <i class="ti ti-edit-2 me-2"></i>Edit
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                @if($interview->status == 'scheduled')
                                                                    <li>
                                                                        <a class="dropdown-item" href="javascript:void(0);" onclick="startInterview({{ $interview->id }})">
                                                                            <i class="ti ti-play me-2"></i>Start Interview
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                @if($interview->status == 'in_progress')
                                                                    <li>
                                                                        <a class="dropdown-item" href="javascript:void(0);" onclick="completeInterview({{ $interview->id }})">
                                                                            <i class="ti ti-check me-2"></i>Mark Complete
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                @if(in_array($interview->status, ['scheduled', 'in_progress']))
                                                                    <li>
                                                                        <a class="dropdown-item text-warning" href="javascript:void(0);" onclick="cancelInterview({{ $interview->id }})">
                                                                            <i class="ti ti-x me-2"></i>Cancel
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('recruitment.candidates.show', $interview->application->candidate->id) }}">
                                                                        <i class="ti ti-user me-2"></i>View Candidate
                                                                    </a>
                                                                </li>
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
                            @if(isset($interviews) && $interviews->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $interviews->links() }}
                                </div>
                            @endif

                            @if(!isset($interviews) || $interviews->isEmpty())
                                <div class="text-center py-5">
                                    <img src="{{ URL::asset('build/img/empty-box.png') }}" alt="No Interviews" class="mb-3" style="width: 100px;">
                                    <h5>No Interviews Scheduled</h5>
                                    <p class="text-muted">Schedule interviews with candidates to move forward in the hiring process.</p>
                                    @if (in_array('Create', $permission))
                                        <a href="{{ route('recruitment.interviews.create') }}" class="btn btn-primary">
                                            <i class="ti ti-calendar-plus me-2"></i>Schedule First Interview
                                        </a>
                                    @endif
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
    function startInterview(interviewId) {
        if (confirm('Mark this interview as in progress?')) {
            updateInterviewStatus(interviewId, 'in_progress');
        }
    }

    function completeInterview(interviewId) {
        if (confirm('Mark this interview as completed?')) {
            updateInterviewStatus(interviewId, 'completed');
        }
    }

    function cancelInterview(interviewId) {
        if (confirm('Are you sure you want to cancel this interview?')) {
            updateInterviewStatus(interviewId, 'cancelled');
        }
    }

    function updateInterviewStatus(interviewId, status) {
        fetch(`/recruitment/interviews/${interviewId}/status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating interview status');
            }
        })
        .catch(error => {
            alert('Error updating interview status');
            console.error('Error:', error);
        });
    }

    // Filter functionality
    document.getElementById('statusFilter').addEventListener('change', filterInterviews);
    document.getElementById('typeFilter').addEventListener('change', filterInterviews);
    document.getElementById('dateFilter').addEventListener('change', filterInterviews);
    document.getElementById('searchFilter').addEventListener('input', filterInterviews);

    function filterInterviews() {
        const params = new URLSearchParams();
        
        const status = document.getElementById('statusFilter').value;
        const type = document.getElementById('typeFilter').value;
        const date = document.getElementById('dateFilter').value;
        const search = document.getElementById('searchFilter').value;

        if (status) params.append('status', status);
        if (type) params.append('interview_type', type);
        if (date) params.append('date_range', date);
        if (search) params.append('search', search);

        window.location.href = '{{ route("recruitment.interviews.index") }}?' + params.toString();
    }
</script>
@endsection