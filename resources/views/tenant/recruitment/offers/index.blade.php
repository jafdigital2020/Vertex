<?php $page = 'recruitment-offers'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Job Offers</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Recruitment
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Job Offers</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    @if (in_array('Create', $permission))
                        <div class="me-2 mb-2">
                            <a href="{{ route('recruitment.offers.create') }}" class="btn btn-primary d-flex align-items-center">
                                <i class="ti ti-square-rounded-plus-filled me-2"></i>Create Offer
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
                            <h4 class="card-title">Job Offers List</h4>
                        </div>
                        <div class="card-body">
                            <!-- Filter Section -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="statusFilter">
                                        <option value="">All Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="sent">Sent</option>
                                        <option value="accepted">Accepted</option>
                                        <option value="rejected">Rejected</option>
                                        <option value="withdrawn">Withdrawn</option>
                                        <option value="expired">Expired</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Department</label>
                                    <select class="form-select" id="departmentFilter">
                                        <option value="">All Departments</option>
                                        @if(isset($departments))
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Salary Range</label>
                                    <select class="form-select" id="salaryFilter">
                                        <option value="">All Ranges</option>
                                        <option value="0-30000">₱0 - ₱30,000</option>
                                        <option value="30000-50000">₱30,000 - ₱50,000</option>
                                        <option value="50000-100000">₱50,000 - ₱100,000</option>
                                        <option value="100000-999999">₱100,000+</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" class="form-control" id="searchFilter" placeholder="Candidate name, position...">
                                </div>
                            </div>

                            <!-- Offers Table -->
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Offer ID</th>
                                            <th>Candidate</th>
                                            <th>Position</th>
                                            <th>Offered Salary</th>
                                            <th>Status</th>
                                            <th>Offer Date</th>
                                            <th>Expiry Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($offers))
                                            @foreach($offers as $offer)
                                                <tr>
                                                    <td>
                                                        <span class="fw-bold text-primary">#{{ $offer->offer_number }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm me-2">
                                                                @if($offer->application->candidate->photo)
                                                                    <img src="{{ asset('storage/' . $offer->application->candidate->photo) }}" alt="Photo" class="img-fluid rounded-circle">
                                                                @else
                                                                    <div class="avatar-initial rounded-circle bg-primary">
                                                                        {{ substr($offer->application->candidate->first_name, 0, 1) }}{{ substr($offer->application->candidate->last_name, 0, 1) }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0">{{ $offer->application->candidate->full_name }}</h6>
                                                                <small class="text-muted">{{ $offer->application->candidate->email }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <h6 class="mb-0">{{ $offer->application->jobPosting->title }}</h6>
                                                            <small class="text-muted">{{ $offer->application->jobPosting->department->department_name }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-success fw-bold">
                                                            ₱{{ number_format($offer->offered_salary) }}
                                                        </div>
                                                        @if($offer->benefits)
                                                            <small class="text-muted">+ Benefits</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($offer->status == 'pending')
                                                            <span class="badge bg-warning">Pending</span>
                                                        @elseif($offer->status == 'sent')
                                                            <span class="badge bg-info">Sent</span>
                                                        @elseif($offer->status == 'accepted')
                                                            <span class="badge bg-success">Accepted</span>
                                                        @elseif($offer->status == 'rejected')
                                                            <span class="badge bg-danger">Rejected</span>
                                                        @elseif($offer->status == 'withdrawn')
                                                            <span class="badge bg-secondary">Withdrawn</span>
                                                        @elseif($offer->status == 'expired')
                                                            <span class="badge bg-dark">Expired</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ ucfirst($offer->status) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $offer->created_at->format('M d, Y') }}
                                                    </td>
                                                    <td>
                                                        @if($offer->expires_at)
                                                            {{ $offer->expires_at->format('M d, Y') }}
                                                            @if($offer->expires_at->isPast())
                                                                <br><small class="text-danger">Expired</small>
                                                            @elseif($offer->expires_at->diffInDays() <= 3)
                                                                <br><small class="text-warning">{{ $offer->expires_at->diffForHumans() }}</small>
                                                            @endif
                                                        @else
                                                            No expiry
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
                                                                        <a class="dropdown-item" href="{{ route('recruitment.offers.show', $offer->id) }}">
                                                                            <i class="ti ti-eye me-2"></i>View Details
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                @if (in_array('Update', $permission) && in_array($offer->status, ['pending', 'sent']))
                                                                    <li>
                                                                        <a class="dropdown-item" href="{{ route('recruitment.offers.edit', $offer->id) }}">
                                                                            <i class="ti ti-edit-2 me-2"></i>Edit
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                @if($offer->status == 'pending')
                                                                    <li>
                                                                        <a class="dropdown-item" href="javascript:void(0);" onclick="sendOffer({{ $offer->id }})">
                                                                            <i class="ti ti-send me-2"></i>Send Offer
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                @if(in_array($offer->status, ['pending', 'sent']))
                                                                    <li>
                                                                        <a class="dropdown-item text-warning" href="javascript:void(0);" onclick="withdrawOffer({{ $offer->id }})">
                                                                            <i class="ti ti-x me-2"></i>Withdraw
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                @if($offer->status == 'accepted')
                                                                    <li>
                                                                        <a class="dropdown-item text-success" href="javascript:void(0);" onclick="startOnboarding({{ $offer->id }})">
                                                                            <i class="ti ti-user-plus me-2"></i>Start Onboarding
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('recruitment.candidates.show', $offer->application->candidate->id) }}">
                                                                        <i class="ti ti-user me-2"></i>View Candidate
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="javascript:void(0);" onclick="generateOfferLetter({{ $offer->id }})">
                                                                        <i class="ti ti-file-download me-2"></i>Download Offer Letter
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
                            @if(isset($offers) && $offers->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $offers->links() }}
                                </div>
                            @endif

                            @if(!isset($offers) || $offers->isEmpty())
                                <div class="text-center py-5">
                                    <img src="{{ URL::asset('build/img/empty-box.png') }}" alt="No Offers" class="mb-3" style="width: 100px;">
                                    <h5>No Job Offers Created</h5>
                                    <p class="text-muted">Create job offers for successful candidates to finalize the hiring process.</p>
                                    @if (in_array('Create', $permission))
                                        <a href="{{ route('recruitment.offers.create') }}" class="btn btn-primary">
                                            <i class="ti ti-file-plus me-2"></i>Create First Offer
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
    function sendOffer(offerId) {
        if (confirm('Send this offer to the candidate?')) {
            updateOfferStatus(offerId, 'sent');
        }
    }

    function withdrawOffer(offerId) {
        if (confirm('Are you sure you want to withdraw this offer?')) {
            updateOfferStatus(offerId, 'withdrawn');
        }
    }

    function updateOfferStatus(offerId, status) {
        fetch(`/recruitment/offers/${offerId}/status`, {
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
                alert('Error updating offer status');
            }
        })
        .catch(error => {
            alert('Error updating offer status');
            console.error('Error:', error);
        });
    }

    function generateOfferLetter(offerId) {
        window.open(`/recruitment/offers/${offerId}/download`, '_blank');
    }

    function startOnboarding(offerId) {
        if (confirm('Start the onboarding process for this candidate?')) {
            // This would redirect to onboarding module or create employee record
            alert('Onboarding feature will be integrated with Employee module');
        }
    }

    // Filter functionality
    document.getElementById('statusFilter').addEventListener('change', filterOffers);
    document.getElementById('departmentFilter').addEventListener('change', filterOffers);
    document.getElementById('salaryFilter').addEventListener('change', filterOffers);
    document.getElementById('searchFilter').addEventListener('input', filterOffers);

    function filterOffers() {
        const params = new URLSearchParams();
        
        const status = document.getElementById('statusFilter').value;
        const department = document.getElementById('departmentFilter').value;
        const salary = document.getElementById('salaryFilter').value;
        const search = document.getElementById('searchFilter').value;

        if (status) params.append('status', status);
        if (department) params.append('department_id', department);
        if (salary) params.append('salary_range', salary);
        if (search) params.append('search', search);

        window.location.href = '{{ route("recruitment.offers.index") }}?' + params.toString();
    }
</script>
@endsection