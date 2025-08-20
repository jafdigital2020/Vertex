<?php $page = 'affiliate.register'; ?>
@extends('layout.mainlayout')

@section('content')
<!-- Page Wrapper -->
<div class="container-fluid">
    <div class="content">
        <div class="text-center my-4">
            <h2 class="mb-1">Upload Affiliate Account</h2>
            <p class="text-muted">Please upload your CSV file containing the affiliate account details below.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0 text-white">Affiliate Account Upload</h5>
                        <small class="text-white-50">Upload CSV</small>
                    </div>
                    <div class="card-body px-4 py-4" style="min-height: 540px; display: flex; flex-direction: column;">
                        <!-- Alert for success or error -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @elseif(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form id="uploadAffiliateForm" enctype="multipart/form-data" method="POST" action="{{ route('affiliate-account-upload-post') }}">
                            @csrf

                            <div style="min-height:340px;">
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="mb-3 text-primary fw-bold"><i class="fas fa-file-upload me-2"></i> CSV File Upload</h6>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Affiliate CSV File</label>
                                        <input type="file" name="csv_file" class="form-control" required>
                                        <small class="form-text text-muted">
                                            Upload a valid CSV file containing affiliate account details.
                                            <br>
                                            <a href="{{ asset('templates/affiliate.csv') }}" class="btn btn-link p-0" download>
                                                <i class="fas fa-download me-1"></i>Download Sample CSV
                                            </a>
                                        </small>
                                    </div>
                                </div>

                                <hr>
                                <h6 class="mb-3 text-primary fw-bold"><i class="fas fa-info-circle me-2"></i>CSV Format</h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <ul class="list-group">
                                            <li class="list-group-item"><strong>Column 1:</strong> Tenant Name</li>
                                            <li class="list-group-item"><strong>Column 2:</strong> Tenant Code</li>
                                            <li class="list-group-item"><strong>Column 3:</strong> Username</li>
                                            <li class="list-group-item"><strong>Column 4:</strong> Email</li>
                                            <li class="list-group-item"><strong>Column 5:</strong> Password</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="fas fa-upload me-2"></i>Upload CSV
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .card-body {
                background: #f4f7fb;
            }

            .form-label {
                font-weight: 500;
                color: #0d6efd;
            }

            .form-control {
                border-radius: 6px;
                border-color: #dbeafe;
                background: #fff;
                font-size: 1rem;
            }

            .form-control:focus {
                border-color: #0d6efd;
                box-shadow: 0 0 0 0.15rem rgba(13,110,253,.08);
            }

            .btn-primary, .btn-success {
                box-shadow: 0 2px 8px rgba(13,110,253,0.08);
                font-weight: 500;
            }

            .btn-outline-secondary {
                font-weight: 500;
            }

            @media (max-width: 767px) {
                .card-body {
                    padding: 1rem !important;
                }
            }
        </style>
    </div>
</div>
@endsection
