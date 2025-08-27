<?php $page = 'affiliate.register'; ?>
@extends('layout.mainlayout')
@section('content')
<!-- Page Wrapper -->
<!-- Step 1: Plan Summary -->
<div class="container-fluid py-5" id="planSummaryStep">
    <div class="row justify-content-center">
        <!-- Left Section (70%) -->
        <div class="col-lg-7 mb-4">
            <div class="text-center mb-4">
                <img src="{{ asset('images/timora-logo.png') }}" alt="Timora Logo" style="height: 50px;">
                <h2 class="mt-2">Plan Summary</h2>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5>Your Subscription</h5>
                    <p>Customize your plan below.</p>

                    <div class="form-group mb-3">
                        <label for="totalEmployees">Total Employees:</label>
                        <input type="number" id="totalEmployees" class="form-control" value="10">
                        <small class="text-muted">₱49.00 per additional user</small>
                    </div>

                    <h6 class="mt-4">Included Features</h6>

                    <div class="mb-3">
                        <strong>Employee Access:</strong>
                        <div class="card p-3 mt-2">
                            <ul class="mb-0">
                                <li>Time Keeping (Clock-in & Clock-out)</li>
                                <li>Payslip View & Download</li>
                                <li>Attendance Photo Capture</li>
                                <li>Leave and Overtime Filing</li>
                            </ul>
                        </div>
                    </div>

                    <div>
                        <strong>Owner Access:</strong>
                        <div class="card p-3 mt-2">
                            <ul class="mb-0">
                                <li>Government Report Generator</li>
                                <li>201 Files and HR Record Management</li>
                                <li>Payroll Process</li>
                                <li>Create Employee</li>
                                <li>Geotagging + Location Tracking</li>
                                <li>Earnings & Deductions</li>
                                <li>Flexible Shift Scheduling</li>
                            </ul>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <span><strong>₱490.00 / month</strong></span>
                        <span><strong>₱5,880.00 / year</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section (30%) -->
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5>Features</h5>
                    <p>Use checkboxes to add features.</p>
                    @foreach(['Official Business', 'Asset Management', 'Bank', 'Payroll Batch', 'Policies'] as $feature)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="{{ Str::slug($feature) }}">
                            <label class="form-check-label" for="{{ Str::slug($feature) }}">{{ $feature }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Pricing Summary</h5>
                    <p>Plan: <strong>Starter</strong></p>
                    <p>Added Employees: ₱490.00</p>
                    <p>Added Features: ₱0.00</p>
                    <p>VAT (12%): ₱0.00</p>
                    <hr>
                    <p><del>₱490.00</del> <strong>₱0.00</strong> (7-day free trial)</p>
                    <button class="btn btn-success w-100 mt-3" id="planSummaryContinueBtn">Continue</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Step 2: Registration Wizard (existing code) -->
<div class="container-fuild d-none" id="registrationWizardStep">
    <div class="content">

    <div class="text-center my-4">
        <h2 class="mb-1">Branch Registration</h2>
        <p class="text-muted">Please complete the steps below to register a new branch.</p>
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
                                <h5 class="mb-0 text-white">Branch Registration Wizard</h5>
                                <small id="wizardStepTitle" class="text-white-50">Step 1: Basic Information</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-4 py-4" style="min-height: 540px; display: flex; flex-direction: column;">
                        <form id="addBranchForm" enctype="multipart/form-data" style="flex:1;display:flex;flex-direction:column;" method="POST" action="{{ route('affiliate-branch-register') }}">
                            @csrf
                            <div class="wizard-step" data-step="1" style="min-height:340px;">
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="mb-3 text-primary fw-bold"><i class="fas fa-user-circle me-2"></i> User Details</h6>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">First Name</label>
                                        <input name="first_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Middle Name</label>
                                        <input name="middle_name" class="form-control">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input name="last_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Suffix</label>
                                        <input name="suffix" class="form-control">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Username</label>
                                        <input name="username" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Email</label>
                                        <input name="email" type="email" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Password</label>
                                        <input name="password" type="password" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Confirm Password</label>
                                        <input name="confirm_password" type="password" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input name="phone_number" class="form-control" required>
                                    </div>
                                    <input name="role_id" type="hidden" value="1">
                                </div>
                                <hr>
                                <h6 class="mb-3 text-primary fw-bold"><i class="fas fa-building me-2"></i>Branch Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Branch Name</label>
                                        <input type="text" class="form-control" id="branchName" name="branch_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Branch Location</label>
                                        <input type="text" class="form-control" id="branchLocation" name="branch_location" required>
                                    </div>
                                </div>
                            </div>
                            <div class="wizard-step d-none" data-step="2" style="min-height:340px;">
                                <h6 class="mb-3 text-primary fw-bold"><i class="fas fa-check-circle me-2"></i>Confirmation</h6>
                                <div class="alert alert-info">
                                    <strong>Review all details below before submitting.</strong>
                                </div>
                                <div id="confirmationDetails">
                                    <!-- Populated by JS before submit -->
                                </div>
                            </div>
                            <!-- Wizard Navigation -->
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="button" class="btn btn-outline-secondary px-4" id="wizardPrev" disabled>
                                    <i class="fas fa-arrow-left me-2"></i>Previous
                                </button>
                                <button type="button" class="btn btn-primary px-4" id="wizardNext">
                                    Next<i class="fas fa-arrow-right ms-2"></i>
                                </button>
                                <button type="submit" class="btn btn-success px-4 d-none" id="wizardSubmit">
                                    <i class="fas fa-save me-2"></i>Save Branch
                                </button>
                            </div>
                            <!-- Wizard Progress -->
                            <div class="progress mt-4" style="height: 8px;">
                                <div class="progress-bar bg-primary" id="wizardProgressBar" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-center mt-2">
                                <small id="wizardStepText" class="text-primary fw-bold">Step 1 of 2</small>
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
            .wizard-step:not(.d-none) {
                display: flex !important;
            }
            .card-body {
                background: #f4f7fb;
            }
            .form-label {
                font-weight: 500;
                color: #0d6efd;
            }
            .form-control, .form-select {
                border-radius: 6px;
                border-color: #dbeafe;
                background: #fff;
                font-size: 1rem;
            }
            .form-control:focus, .form-select:focus {
                border-color: #0d6efd;
                box-shadow: 0 0 0 0.15rem rgba(13,110,253,.08);
            }
            .input-group .form-control {
                border-right: 0;
            }
            .input-group .btn {
                border-left: 0;
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
        <style>
            .wizard-circle {
                border: 2px solid #0d6efd;
                box-shadow: 0 2px 8px rgba(13,110,253,0.08);
            }
            .wizard-step {
                animation: fadeIn .3s;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px);}
                to { opacity: 1; transform: none;}
            }
        </style>
        <script>
            // Make all wizard steps same height
            $(function() {
                function setWizardStepHeight() {
                    let max = 0;
                    $('.wizard-step').each(function() {
                        $(this).css('min-height', '0');
                        if (!$(this).hasClass('d-none')) {
                            max = Math.max(max, $(this).outerHeight());
                        }
                    });
                    if (max < 340) max = 340;
                    $('.wizard-step').css('min-height', max + 'px');
                }
                setWizardStepHeight();
                $(window).on('resize', setWizardStepHeight);
                // Also update on step change
                const origShowStep = showStep;
                showStep = function(step) {
                    origShowStep(step);
                    setTimeout(setWizardStepHeight, 100);
                };
            });

            // Update wizard step circle and title on step change
            function updateWizardHeader(step) {
                $('#wizardStepCircle').text(step);
                let titles = [
                    'Step 1: Basic Information',
                    'Step 2: Confirmation'
                ];
                $('#wizardStepTitle').text(titles[step-1]);
            }
            // Hook into your showStep function
            const origShowStep = showStep;
            showStep = function(step) {
                origShowStep(step);
                updateWizardHeader(step);
            };
        </script>

    </div>
</div>
<script>
    // Stepper logic for Plan Summary -> Registration Wizard
    $(function() {
        $('#planSummaryContinueBtn').on('click', function() {
            $('#planSummaryStep').addClass('d-none');
            $('#registrationWizardStep').removeClass('d-none');
        });
    });
</script>
