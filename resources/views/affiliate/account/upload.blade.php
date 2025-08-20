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
                        <div class="d-flex align-items-center">
                            <div class="wizard-circle bg-white text-primary me-3" style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:1.2rem;">
                                <span id="wizardStepCircle">1</span>
                            </div>
                            <div>
                                <h5 class="mb-0 text-white">Affiliate Account Upload</h5>
                                <small id="wizardStepTitle" class="text-white-50">Step 1: Upload CSV</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-4 py-4" style="min-height: 540px; display: flex; flex-direction: column;">
                        <form id="uploadAffiliateForm" enctype="multipart/form-data" method="POST" action="{{ route('affiliate-account-upload-post') }}">
                            @csrf

                            <div class="wizard-step" data-step="1" style="min-height:340px;">
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
                            
                            <!-- Wizard Progress -->
                            <div class="progress mt-4" style="height: 8px;">
                                <div class="progress-bar bg-primary" id="wizardProgressBar" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-center mt-2">
                                <small id="wizardStepText" class="text-primary fw-bold">Step 1 of 1</small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .wizard-step {
                animation: fadeIn .3s;
                transition: min-height .2s;
                background: #f8fafd;
                border-radius: 10px;
                padding: 24px 18px 12px 18px;
                box-shadow: 0 1px 4px rgba(13,110,253,0.04);
                margin-bottom: 0;
                min-height: 340px;
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
            }

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

            .progress {
                background: #e9ecef;
                border-radius: 6px;
            }

            .progress-bar {
                border-radius: 6px;
            }

            .btn-primary, .btn-success {
                box-shadow: 0 2px 8px rgba(13,110,253,0.08);
                font-weight: 500;
            }

            .btn-outline-secondary {
                font-weight: 500;
            }

            .wizard-circle {
                border: 2px solid #0d6efd;
                box-shadow: 0 2px 8px rgba(13,110,253,0.08);
            }

            @media (max-width: 767px) {
                .wizard-step {
                    padding: 12px 4px 8px 4px;
                }

                .card-body {
                    padding: 1rem !important;
                }
            }
        </style>
        
        <script>
            // Initialize wizard logic
            let currentStep = 1;
            const totalSteps = 1;

            function showStep(step) {
                $('.wizard-step').addClass('d-none');
                $('.wizard-step[data-step="' + step + '"]').removeClass('d-none');
                $('#wizardSubmit').toggleClass('d-none', step !== totalSteps);
                let percent = Math.round((step / totalSteps) * 100);
                $('#wizardProgressBar').css('width', percent + '%').attr('aria-valuenow', percent);
                $('#wizardStepText').text('Step ' + step + ' of ' + totalSteps);
            }

            // Initial step
            showStep(currentStep);
        </script>

    </div>
</div>
@endsection
