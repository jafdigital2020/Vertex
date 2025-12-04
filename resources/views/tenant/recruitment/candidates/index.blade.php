<?php $page = 'recruitment-candidates'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Candidates</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Recruitment
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Candidates</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    @if (in_array('Create', $permission))
                        <div class="me-2 mb-2">
                            <a href="{{ route('recruitment.candidates.create') }}" class="btn btn-primary d-flex align-items-center">
                                <i class="ti ti-square-rounded-plus-filled me-2"></i>Add Candidate
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
                            <h4 class="card-title">Candidates List</h4>
                        </div>
                        <div class="card-body">
                            <!-- Filter Section -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="statusFilter">
                                        <option value="">All Status</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Department</label>
                                    <select class="form-select" id="departmentFilter">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Experience Level</label>
                                    <select class="form-select" id="experienceFilter">
                                        <option value="">All Levels</option>
                                        <option value="entry">Entry Level</option>
                                        <option value="mid">Mid Level</option>
                                        <option value="senior">Senior Level</option>
                                        <option value="executive">Executive</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" class="form-control" id="searchFilter" placeholder="Name, email, phone...">
                                </div>
                            </div>

                            <!-- Candidates Table -->
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Candidate</th>
                                            <th>Contact</th>
                                            <th>Status</th>
                                            <th>Experience</th>
                                            <th>Applied Date</th>
                                            <th>Last Activity</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($candidates as $candidate)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-md me-3">
                                                            @if($candidate->photo)
                                                                <img src="{{ asset('storage/' . $candidate->photo) }}" alt="Photo" class="img-fluid rounded-circle">
                                                            @else
                                                                <div class="avatar-initial rounded-circle bg-primary">
                                                                    {{ substr($candidate->first_name, 0, 1) }}{{ substr($candidate->last_name, 0, 1) }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">{{ $candidate->full_name }}</h6>
                                                            <small class="text-muted">ID: {{ $candidate->candidate_id }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div>{{ $candidate->email }}</div>
                                                        <small class="text-muted">{{ $candidate->phone }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($candidate->status == 'new')
                                                        <span class="badge bg-info">{{ ucfirst($candidate->status) }}</span>
                                                    @elseif($candidate->status == 'screening')
                                                        <span class="badge bg-warning">{{ ucfirst($candidate->status) }}</span>
                                                    @elseif($candidate->status == 'interview')
                                                        <span class="badge bg-primary">{{ ucfirst($candidate->status) }}</span>
                                                    @elseif($candidate->status == 'evaluation')
                                                        <span class="badge bg-secondary">{{ ucfirst($candidate->status) }}</span>
                                                    @elseif($candidate->status == 'offer')
                                                        <span class="badge bg-warning">{{ ucfirst($candidate->status) }}</span>
                                                    @elseif($candidate->status == 'hired')
                                                        <span class="badge bg-success">{{ ucfirst($candidate->status) }}</span>
                                                    @else
                                                        <span class="badge bg-danger">{{ ucfirst($candidate->status) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($candidate->years_of_experience)
                                                        {{ $candidate->years_of_experience }} years
                                                    @else
                                                        Not specified
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $candidate->created_at->format('M d, Y') }}
                                                </td>
                                                <td>
                                                    {{ $candidate->updated_at->diffForHumans() }}
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <a href="javascript:void(0);" class="btn btn-white btn-sm btn-rounded dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-menu-right">
                                                            @if (in_array('Read', $permission))
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('recruitment.candidates.show', $candidate->id) }}">
                                                                        <i class="ti ti-eye me-2"></i>View Profile
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @if (in_array('Update', $permission))
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('recruitment.candidates.edit', $candidate->id) }}">
                                                                        <i class="ti ti-edit-2 me-2"></i>Edit
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('recruitment.applications.index') }}?candidate_id={{ $candidate->id }}">
                                                                    <i class="ti ti-file-description me-2"></i>View Applications
                                                                </a>
                                                            </li>
                                                            @if($candidate->resume_path)
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ asset('storage/' . $candidate->resume_path) }}" target="_blank">
                                                                        <i class="ti ti-download me-2"></i>Download Resume
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @if (in_array('Delete', $permission) && $candidate->applications_count == 0)
                                                                <li>
                                                                    <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteCandidate({{ $candidate->id }})">
                                                                        <i class="ti ti-trash me-2"></i>Delete
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($candidates->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $candidates->links() }}
                                </div>
                            @endif

                            @if($candidates->isEmpty())
                                <div class="text-center py-5">
                                    <img src="{{ URL::asset('build/img/empty-box.png') }}" alt="No Candidates" class="mb-3" style="width: 100px;">
                                    <h5>No Candidates Found</h5>
                                    <p class="text-muted">Start building your talent pool by adding candidates or they can apply through your career page.</p>
                                    @if (in_array('Create', $permission))
                                        <a href="{{ route('recruitment.candidates.create') }}" class="btn btn-primary">
                                            <i class="ti ti-plus me-2"></i>Add First Candidate
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
    function deleteCandidate(candidateId) {
        if (confirm('Are you sure you want to delete this candidate? This action cannot be undone.')) {
            fetch(`/recruitment/candidates/${candidateId}`, {
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
                    alert('Error deleting candidate');
                }
            });
        }
    }

    // Filter functionality
    document.getElementById('statusFilter').addEventListener('change', filterCandidates);
    document.getElementById('departmentFilter').addEventListener('change', filterCandidates);
    document.getElementById('experienceFilter').addEventListener('change', filterCandidates);
    document.getElementById('searchFilter').addEventListener('input', filterCandidates);

    function filterCandidates() {
        const params = new URLSearchParams();
        
        const status = document.getElementById('statusFilter').value;
        const department = document.getElementById('departmentFilter').value;
        const experience = document.getElementById('experienceFilter').value;
        const search = document.getElementById('searchFilter').value;

        if (status) params.append('status', status);
        if (department) params.append('department_id', department);
        if (experience) params.append('experience', experience);
        if (search) params.append('search', search);

        window.location.href = '{{ route("recruitment.candidates.index") }}?' + params.toString();
    }
</script>
@endsection