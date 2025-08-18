<?php $page = 'affiliate.register'; ?>
@extends('layout.mainlayout')
@section('content')
<!-- Page Wrapper -->
<div class="container-fuild">
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
                <div class="card-body px-4 py-4">
                <form id="addBranchForm" enctype="multipart/form-data">
                    @csrf
                    <!-- Affiliate Account Details -->
                    <div class="wizard-step" data-step="1">
                    <div class="row mb-4">
                        <div class="col-12">
                        <h6 class="mb-3 text-primary fw-bold"><i class="fas fa-user-circle me-2"></i>Affiliate Account Details</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                        <label class="form-label">Tenant Name</label>
                        <input name="tenant_name" value="JAF Digital" class="form-control" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                        <label class="form-label">Tenant Code</label>
                        <input name="tenant_code" value="JDGI" class="form-control" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                        <label class="form-label">Username</label>
                        <input name="global_user[username]" value="joli_admin" class="form-control" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                        <label class="form-label">Email</label>
                        <input name="global_user[email]" value="admin@jolibee.co" class="form-control" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input name="global_user[password]" value="12345678" class="form-control" type="password" readonly>
                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="const p=this.previousElementSibling;p.type=p.type==='password'?'text':'password';this.innerHTML=p.type==='password'?'Show':'Hide';">Show</button>
                        </div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-3 text-primary fw-bold"><i class="fas fa-building me-2"></i>Branch Basic Information</h6>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Branch Logo</label>
                        <input type="file" class="form-control" id="branchLogoInput" name="branch_logo">
                        <div class="mt-2 d-flex align-items-center">
                            <img id="branchLogoPreview" src="{{ asset('build/img/profiles/avatar-30.jpg') }}" alt="Logo Preview" class="img-thumbnail me-2" width="80" style="border-radius:8px;">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="cancelLogoUpload">Cancel</button>
                        </div>
                        </div>
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Branch Name</label>
                        <input type="text" class="form-control" id="branchName" name="name" required>
                        </div>
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Branch Type</label>
                        <select class="form-select" id="branchType" name="branch_type" required>
                            <option value="main">Main</option>
                            <option value="satellite">Satellite</option>
                        </select>
                        </div>
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="branchContactNumber" name="contact_number">
                        </div>
                        <div class="mb-3 col-md-12">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" id="branchAddress" name="location">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Starter Features</label>
                        <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" value="201 File & HR Records Management" id="feature201File" name="starter_features[]">
                            <label class="form-check-label" for="feature201File">201 File & HR Records Management</label>
                            </div>
                            <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" value="Government Report Generator (BIR, SSS, PhilHealth, Pag-IBIG)" id="featureGovReport" name="starter_features[]">
                            <label class="form-check-label" for="featureGovReport">Government Report Generator (BIR, SSS, PhilHealth, Pag-IBIG)</label>
                            </div>
                            <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" value="Holiday Calculation" id="featureHolidayCalc" name="starter_features[]">
                            <label class="form-check-label" for="featureHolidayCalc">Holiday Calculation</label>
                            </div>
                            <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" value="Payroll Processing" id="featurePayroll" name="starter_features[]">
                            <label class="form-check-label" for="featurePayroll">Payroll Processing</label>
                            </div>
                            <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" value="Employee Self-Service Portal" id="featureESS" name="starter_features[]">
                            <label class="form-check-label" for="featureESS">Employee Self-Service Portal</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" value="Real-Time Attendance" id="featureAttendance" name="starter_features[]">
                            <label class="form-check-label" for="featureAttendance">Real-Time Attendance</label>
                            </div>
                            <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" value="Attendance Photo Capture" id="featurePhotoCapture" name="starter_features[]">
                            <label class="form-check-label" for="featurePhotoCapture">Attendance Photo Capture</label>
                            </div>
                            <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" value="Geotagging - Location Tracking" id="featureGeotagging" name="starter_features[]">
                            <label class="form-check-label" for="featureGeotagging">Geotagging - Location Tracking</label>
                            </div>
                            <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" value="Employee Payslip" id="featurePayslip" name="starter_features[]">
                            <label class="form-check-label" for="featurePayslip">Employee Payslip</label>
                            </div>
                            <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" value="Leave and Overtime Filing" id="featureLeaveOvertime" name="starter_features[]">
                            <label class="form-check-label" for="featureLeaveOvertime">Leave and Overtime Filing</label>
                            </div>
                            <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" value="Flexible Shift Scheduling" id="featureShiftScheduling" name="starter_features[]">
                            <label class="form-check-label" for="featureShiftScheduling">Flexible Shift Scheduling</label>
                            </div>
                        </div>
                        </div>
                    </div>
                    </div>
                    <!-- Step 2: Salary & Contributions -->
                    <div class="wizard-step d-none" data-step="2">
                    <h6 class="mb-3 text-primary fw-bold"><i class="fas fa-coins me-2"></i>Salary & Contributions</h6>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Salary Type</label>
                        <select class="form-select" id="branchSalaryType" name="salary_type">
                            <option value="monthly">Monthly</option>
                            <option value="daily">Daily</option>
                            <option value="hourly">Hourly</option>
                        </select>
                        </div>
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Basic Salary</label>
                        <input type="number" class="form-control" id="branchBasicSalary" name="basic_salary">
                        </div>
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Salary Computation Type</label>
                        <select class="form-select" id="branchSalaryComputationType" name="salary_computation_type">
                            <option value="fixed">Fixed</option>
                            <option value="variable">Variable</option>
                        </select>
                        </div>
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Worked Days Per Year</label>
                        <select class="form-select" id="branchWorkedDaysPerYear" name="worked_days_per_year">
                            <option value="260">260</option>
                            <option value="261">261</option>
                            <option value="custom">Custom</option>
                        </select>
                        </div>
                        <div class="mb-3 col-md-6" id="addCustomWorkedDaysWrapper" style="display:none;">
                        <label class="form-label">Custom Worked Days</label>
                        <input type="number" class="form-control" id="branchCustomWorkedDays" name="custom_worked_days">
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-3 text-primary fw-bold"><i class="fas fa-file-invoice-dollar me-2"></i>Contributions</h6>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                        <label class="form-label">SSS Contribution Type</label>
                        <select class="form-select" id="branchSSSContributionType" name="sss_contribution_type">
                            <option value="table">Table</option>
                            <option value="fixed">Fixed</option>
                        </select>
                        </div>
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Fixed SSS Amount</label>
                        <input type="number" class="form-control" id="branchSSSFixedContribution" name="fixed_sss_amount">
                        </div>
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Philhealth Contribution Type</label>
                        <select class="form-select" id="branchPhilhealthContributionType" name="philhealth_contribution_type">
                            <option value="table">Table</option>
                            <option value="fixed">Fixed</option>
                        </select>
                        </div>
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Fixed Philhealth Amount</label>
                        <input type="number" class="form-control" id="branchPhilhealthFixedContribution" name="fixed_philhealth_amount">
                        </div>
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Pagibig Contribution Type</label>
                        <select class="form-select" id="branchPagibigContributionType" name="pagibig_contribution_type">
                            <option value="table">Table</option>
                            <option value="fixed">Fixed</option>
                        </select>
                        </div>
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Fixed Pagibig Amount</label>
                        <input type="number" class="form-control" id="branchPagibigFixedContribution" name="fixed_pagibig_amount">
                        </div>
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Withholding Tax Type</label>
                        <select class="form-select" id="branchWithholdingTaxType" name="withholding_tax_type">
                            <option value="table">Table</option>
                            <option value="fixed">Fixed</option>
                        </select>
                        </div>
                        <div class="mb-3 col-md-6">
                        <label class="form-label">Fixed Withholding Amount</label>
                        <input type="number" class="form-control" id="branchWithholdingTaxFixedContribution" name="fixed_withholding_amount">
                        </div>
                    </div>
                    </div>
                    <!-- Step 3: Confirmation -->
                    <div class="wizard-step d-none" data-step="3">
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
                    <div class="progress-bar bg-primary" id="wizardProgressBar" role="progressbar" style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="text-center mt-2">
                    <small id="wizardStepText" class="text-primary fw-bold">Step 1 of 3</small>
                    </div>
                </form>
                </div>
            </div>
            </div>
        </div>
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
            // Update wizard step circle and title on step change
            function updateWizardHeader(step) {
                $('#wizardStepCircle').text(step);
                let titles = [
                    'Step 1: Basic Information',
                    'Step 2: Salary Information',
                    'Step 3: Contributions'
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
@endsection

@push('scripts')
<script>
    // Wizard logic
    let currentStep = 1;
    const totalSteps = 3;

    function showStep(step) {
        $('.wizard-step').addClass('d-none');
        $('.wizard-step[data-step="' + step + '"]').removeClass('d-none');
        $('#wizardPrev').prop('disabled', step === 1);
        $('#wizardNext').toggleClass('d-none', step === totalSteps);
        $('#wizardSubmit').toggleClass('d-none', step !== totalSteps);
        // Progress bar
        let percent = Math.round((step / totalSteps) * 100);
        $('#wizardProgressBar').css('width', percent + '%').attr('aria-valuenow', percent);
        $('#wizardStepText').text('Step ' + step + ' of ' + totalSteps);
    }

    $('#wizardNext').on('click', function() {
        if (currentStep < totalSteps) {
            currentStep++;
            showStep(currentStep);
        }
    });
    $('#wizardPrev').on('click', function() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });

    // Initial step
    showStep(currentStep);

    // Logo preview
    $('#branchLogoInput').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#branchLogoPreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
    $('#cancelLogoUpload').on('click', function() {
        $('#branchLogoInput').val('');
        $('#branchLogoPreview').attr('src', "{{ asset('build/img/profiles/avatar-30.jpg') }}");
    });

    // Show/hide custom worked days
    $('#branchWorkedDaysPerYear').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#addCustomWorkedDaysWrapper').show();
        } else {
            $('#addCustomWorkedDaysWrapper').hide();
            $('#branchCustomWorkedDays').val('');
        }
    }).trigger('change');

    // Show/hide fixed contribution fields
    function toggleFixedField(selectId, inputId) {
        const select = $('#' + selectId);
        const inputCol = $('#' + inputId).closest('.col-md-6');
        if (select.val() === 'fixed') {
            inputCol.show();
        } else {
            inputCol.hide();
            $('#' + inputId).val('');
        }
    }
    $('#branchSSSContributionType').on('change', function() {
        toggleFixedField('branchSSSContributionType', 'branchSSSFixedContribution');
    }).trigger('change');
    $('#branchPhilhealthContributionType').on('change', function() {
        toggleFixedField('branchPhilhealthContributionType', 'branchPhilhealthFixedContribution');
    }).trigger('change');
    $('#branchPagibigContributionType').on('change', function() {
        toggleFixedField('branchPagibigContributionType', 'branchPagibigFixedContribution');
    }).trigger('change');
    $('#branchWithholdingTaxType').on('change', function() {
        toggleFixedField('branchWithholdingTaxType', 'branchWithholdingTaxFixedContribution');
    }).trigger('change');

    // Form submission (AJAX)
    $('#addBranchForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let authToken = localStorage.getItem("token");
        $.ajax({
            url: "{{ route('api.branchCreate') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Authorization': authToken ? `Bearer ${authToken}` : undefined
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);
                    $('#addBranchForm')[0].reset();
                    $('#branchLogoPreview').attr('src', "{{ asset('build/img/profiles/avatar-30.jpg') }}");
                    $('#addBranchForm').after('<div class="alert alert-success mt-3">Branch saved successfully! (Redirect disabled, no branch index route defined)</div>');
                } else {
                    toastr.error(response.errors);
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors || {};
                for (const key in errors) {
                    toastr.error(errors[key][0]);
                }
            }
        });
    });
</script>
@endpush
