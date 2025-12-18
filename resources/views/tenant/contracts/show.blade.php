<?php $page = 'contracts'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Contract Details</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('admin-dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('contracts.index') }}">Contracts</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Contract Details</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <a href="{{ route('contracts.print', $contract->id) }}" class="btn btn-primary me-2" target="_blank">
                        <i class="ti ti-printer me-1"></i>Print
                    </a>
                    <a href="{{ route('contracts.index') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Contract Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Employee Details</h6>
                                    <p class="mb-1">
                                        <strong>Name:</strong>
                                        {{ $contract->user->personalInformation->first_name ?? '' }}
                                        {{ $contract->user->personalInformation->last_name ?? $contract->user->username }}
                                    </p>
                                    <p class="mb-1">
                                        <strong>Employee ID:</strong>
                                        {{ $contract->user->employmentDetail->employee_id ?? 'N/A' }}
                                    </p>
                                    <p class="mb-1">
                                        <strong>Position:</strong>
                                        {{ $contract->user->designation->name ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Contract Details</h6>
                                    <p class="mb-1">
                                        <strong>Type:</strong>
                                        <span class="badge bg-info">{{ $contract->contract_type }}</span>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Status:</strong>
                                        @if($contract->status === 'Active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($contract->status === 'Draft')
                                            <span class="badge bg-warning">Draft</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $contract->status }}</span>
                                        @endif
                                    </p>
                                    <p class="mb-1">
                                        <strong>Start Date:</strong>
                                        {{ \Carbon\Carbon::parse($contract->start_date)->format('F d, Y') }}
                                    </p>
                                    @if($contract->end_date)
                                        <p class="mb-1">
                                            <strong>End Date:</strong>
                                            {{ \Carbon\Carbon::parse($contract->end_date)->format('F d, Y') }}
                                        </p>
                                    @endif
                                    @if($contract->signed_date)
                                        <p class="mb-1">
                                            <strong>Signed Date:</strong>
                                            {{ \Carbon\Carbon::parse($contract->signed_date)->format('F d, Y') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <hr>

                            <div class="contract-content mt-4">
                                <h6 class="mb-3">Contract Content</h6>

                                @if($contract->template && $contract->template->isPdfTemplate())
                                    <!-- PDF Template -->
                                    <div class="alert alert-info mb-3">
                                        <i class="ti ti-file-type-pdf me-2"></i>
                                        This contract uses a PDF template: <strong>{{ $contract->template->name }}</strong>
                                    </div>
                                    <div class="text-center">
                                        <a href="{{ $contract->template->getPdfUrl() }}" class="btn btn-primary btn-lg"
                                            target="_blank">
                                            <i class="ti ti-download me-2"></i>Download Contract PDF
                                        </a>
                                        <p class="text-muted mt-2">
                                            <small>{{ basename($contract->template->pdf_template_path) }}</small>
                                        </p>
                                    </div>

                                    <!-- PDF Preview -->
                                    <div class="mt-4">
                                        <iframe src="{{ $contract->template->getPdfUrl() }}" width="100%" height="800px"
                                            style="border: 1px solid #ddd;">
                                        </iframe>
                                    </div>
                                @elseif($contract->content)
                                    <!-- Text Content -->
                                    <div class="border p-4 rounded" style="background: #f9f9f9;">
                                        {!! nl2br(e($contract->content)) !!}
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        No contract content available.
                                    </div>
                                @endif
                            </div>

                            @if($contract->status === 'Draft')
                                <div class="mt-4">
                                    <button class="btn btn-success sign-contract" data-id="{{ $contract->id }}">
                                        <i class="ti ti-signature me-1"></i>Sign & Activate Contract
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.sign-contract').on('click', function () {
                const id = $(this).data('id');

                if (confirm('Are you sure you want to sign and activate this contract?')) {
                    $.ajax({
                        url: '/contracts/' + id + '/sign',
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (response) {
                            if (response.status === 'success') {
                                toastr.success(response.message);
                                setTimeout(() => location.reload(), 1000);
                            }
                        },
                        error: function (xhr) {
                            toastr.error('Failed to sign contract');
                        }
                    });
                }
            });
        });
    </script>
@endsection