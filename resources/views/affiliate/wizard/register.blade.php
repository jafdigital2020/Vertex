<?php $page = 'affiliate.register'; ?>

@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->

    <!-- Ensure jQuery is loaded before any script uses $ -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <div class="container-fuild">
        <div class="content">

            <div class="text-center my-4">
                <div class="text-center mb-4">
                    <img src="{{ asset('build/img/Timora-logo.png') }}" alt="Timora Logo" style="height: 50px;">

                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-9 col-xl-8">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-primary text-white py-3">
                            <div class="d-flex align-items-center">
                                <div class="wizard-circle bg-white text-primary me-3"
                                    style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:1.2rem;">
                                    <span id="wizardStepCircle">1</span>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-white">Business Registration Wizard</h5>
                                    <small id="wizardStepTitle" class="text-white-50">Step 1: Plan Summary</small>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-4 py-2" style="min-height: 540px; display: flex; flex-direction: column;">
                            <form id="addBranchForm" enctype="multipart/form-data"
                                style="flex:1;display:flex;flex-direction:column;" method="POST"
                                action="{{ route('affiliate-branch-register') }}">
                                @csrf

                                <!-- STEP 1: Plan Summary (NON-BLOCKING) -->
                                <div class="wizard-step" data-step="1" style="min-height:340px;">
                                    <div class="container-fluid py-3">
                                        <div class="row justify-content-center">
                                            <!-- Left Section -->
                                            <h2 class="my-2">Plan Summary</h2>
                                            <div class="col-lg-7 mb-2">


                                                <div class="card shadow-sm mb-4">
                                                    <div class="card-body">
                                                        <h5>Your Subscription</h5>
                                                        <p>Customize your plan to match your HR & Payroll needs.</p>

                                                        <div class="form-group mb-3">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center mb-1">
                                                                <label for="totalEmployees" class="mb-0"><strong>Total
                                                                        Employees:</strong></label>
                                                                <small class="text-muted">₱49.00 per additional user</small>
                                                            </div>
                                                            <input type="number" id="totalEmployees" class="form-control"
                                                                name="total_employees" value="1" min="0" step="1"
                                                                data-included="0" data-price-per-user="49">

                                                        </div>

                                                        <h3 class="mt-3 mb-2">Included Features:</h3>

                                                        <div class="d-flex flex-wrap gap-3">
                                                            <!-- Employee Access Card -->
                                                            <div class="flex-fill" style="min-width: 300px;">

                                                                <div class="mb-3">
                                                                    <div class="mb-3">
                                                                        <strong>Employee Access</strong>
                                                                        <div class="d-flex flex-column mt-2 gap-2">
                                                                            <!-- Time Keeping Card -->
                                                                            <div class="card py-2 px-3 mb-1 border"
                                                                                style="border-color: #064857; border-radius: 0.5rem;">
                                                                                <div
                                                                                    class="d-flex align-items-center text-primary">
                                                                                    <i class="bi bi-clock me-2"></i>
                                                                                    <span>Time Keeping (Clock-in &
                                                                                        Clock-out)</span>
                                                                                </div>
                                                                            </div>

                                                                            <!-- Payslip View Card -->
                                                                            <div class="card py-2 px-3 mb-1 border"
                                                                                style="border-color: #064857; border-radius: 0.5rem;">
                                                                                <div
                                                                                    class="d-flex align-items-center text-primary">
                                                                                    <i class="bi bi-eye me-2"></i>
                                                                                    <span>Payslip View & Download</span>
                                                                                </div>
                                                                            </div>

                                                                            <!-- Attendance Photo Capture Card -->
                                                                            <div class="card py-2 px-3 mb-1 border"
                                                                                style="border-color: #064857; border-radius: 0.5rem;">
                                                                                <div
                                                                                    class="d-flex align-items-center text-primary">
                                                                                    <i class="bi bi-camera me-2"></i>
                                                                                    <span>Attendance Photo Capture</span>
                                                                                </div>
                                                                            </div>

                                                                            <!-- Leave and Overtime Filing Card -->
                                                                            <div class="card py-2 px-3 border"
                                                                                style="border-color: #064857; border-radius: 0.5rem;">
                                                                                <div
                                                                                    class="d-flex align-items-center text-primary">
                                                                                    <i
                                                                                        class="bi bi-calendar-check me-2"></i>
                                                                                    <span>Leave and Overtime Filing</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                            <!-- Owner Access Card -->
                                                            <div class="flex-fill" style="min-width: 300px;">
                                                                <div class="mb-3">
                                                                    <strong>Owner Access:</strong>
                                                                    <div class="d-flex flex-column mt-2 gap-2">
                                                                        <!-- Government Report Generator -->
                                                                        <div class="card py-2 px-3 mb-1 border"
                                                                            style="border-color: #064857; border-radius: 0.5rem;">
                                                                            <div
                                                                                class="d-flex align-items-center text-primary">
                                                                                <i class="bi bi-building me-2"></i>
                                                                                <span>Government Report Generator</span>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Employee List View -->
                                                                        <div class="card py-2 px-3 mb-1 border"
                                                                            style="border-color: #064857; border-radius: 0.5rem;">
                                                                            <div
                                                                                class="d-flex align-items-center text-primary">
                                                                                <i class="bi bi-eye me-2"></i>
                                                                                <span>Employee List View</span>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Payroll Process -->
                                                                        <div class="card py-2 px-3 mb-1 border"
                                                                            style="border-color: #064857; border-radius: 0.5rem;">
                                                                            <div
                                                                                class="d-flex align-items-center text-primary">
                                                                                <i class="bi bi-cash me-2"></i>
                                                                                <span>Payroll Process</span>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Create Employee -->
                                                                        <div class="card py-2 px-3 mb-1 border"
                                                                            style="border-color: #064857; border-radius: 0.5rem;">
                                                                            <div
                                                                                class="d-flex align-items-center text-primary">
                                                                                <i class="bi bi-person-plus me-2"></i>
                                                                                <span>Create Employee</span>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Geotagging + Location Tracking -->
                                                                        <div class="card py-2 px-3 mb-1 border"
                                                                            style="border-color: #064857; border-radius: 0.5rem;">
                                                                            <div
                                                                                class="d-flex align-items-center text-primary">
                                                                                <i class="bi bi-geo-alt me-2"></i>
                                                                                <span>Geotagging + Location Tracking</span>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Earnings & Deductions -->
                                                                        <div class="card py-2 px-3 mb-1 border"
                                                                            style="border-color: #064857; border-radius: 0.5rem;">
                                                                            <div
                                                                                class="d-flex align-items-center text-primary">
                                                                                <i class="bi bi-calculator me-2"></i>
                                                                                <span>Earnings & Deductions</span>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Flexible Shift Scheduling -->
                                                                        <div class="card py-2 px-3 border"
                                                                            style="border-color: #064857; border-radius: 0.5rem;">
                                                                            <div
                                                                                class="d-flex align-items-center text-primary">
                                                                                <i class="bi bi-calendar-range me-2"></i>
                                                                                <span>Flexible Shift Scheduling</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <hr>
                                                        <div>
                                                            <div class="d-flex align-items-center justify-content-between px-4 py-3 rounded"
                                                                style="background: linear-gradient(to right, #064857, #2ca8a8); color: white;">
                                                                <div class="w-100 d-flex align-items-center justify-content-between"
                                                                    style="min-height: 70px; width: 100%;">
                                                                    <div class="flex-grow-1">
                                                                        <div class="small">Monthly</div>
                                                                        <strong id="leftMonthly"
                                                                            style="font-size: 1.25rem;">₱49.00 /
                                                                            month</strong>
                                                                    </div>
                                                                    <div>
                                                                        <i class="bi bi-cash"
                                                                            style="font-size: 2.5rem;"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            {{-- <span><strong id="leftYearly">₱588.00 /
                                                                    year</strong></span> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Right Section -->
                                            <div class="col-lg-5">
                                                <div class="card mb-4">
                                                    <div class="card-body">

                                                        <h5>Features</h5>
                                                        <p>Use checkboxes to add more features.</p>

                                                        <div id="addons-list">
                                                            <div class="text-center py-4">
                                                                <div class="spinner-border text-primary" role="status">
                                                                    <span class="visually-hidden">Loading...</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <script>
                                                            // Map addon_key to icon class
                                                            const addonIcons = {
                                                                employee_official_business: 'bi-briefcase',
                                                                asset_management_tracking: 'bi-hdd-network',
                                                                bank_data_export_csv: 'bi-file-earmark-spreadsheet',
                                                                payroll_batch_processing: 'bi-stack',
                                                                policy_upload: 'bi-upload',
                                                                custom_holiday: 'bi-calendar-heart'
                                                            };

                                                            function slugify(text) {
                                                                return text.toString().toLowerCase()
                                                                    .replace(/\s+/g, '-')           // Replace spaces with -
                                                                    .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                                                                    .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                                                                    .replace(/^-+/, '')             // Trim - from start of text
                                                                    .replace(/-+$/, '');            // Trim - from end of text
                                                            }

                                                            $(function () {
                                                                $.get('{{ route("api.affiliate-addons") }}', function (data) {
                                                                    let html = '';
                                                                    if (data && Array.isArray(data.addons) && data.addons.length) {
                                                                        data.addons.forEach(function (addon) {
                                                                            const id = slugify(addon.name);
                                                                            const icon = addonIcons[addon.addon_key] || 'bi-box';
                                                                            html += `
                                                                                                            <div class="d-flex align-items-center justify-content-between p-3 mb-2 border rounded" style="border-color: #064857;">
                                                                                                                <div class="d-flex align-items-center">
                                                                                                                    <input class="form-check-input me-2 feature-checkbox" type="checkbox"
                                                                                                                        id="${id}" name="features[]" value="${addon.addon_key}"
                                                                                                                        data-addon-key="${addon.addon_key}" data-price="${addon.price}">

                                                                                                                    <div class="me-3">
                                                                                                                        <i class="bi ${icon}"></i>
                                                                                                                    </div>
                                                                                                                    <label class="form-check-label fw-bold mb-0" for="${id}">
                                                                                                                        ${addon.name}
                                                                                                                    </label>
                                                                                                                </div>
                                                                                                                <div class="text-end fw-semibold">
                                                                                                                    ₱${parseFloat(addon.price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}/mo
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        `;
                                                                        });
                                                                    } else {
                                                                        html = `<div class="alert alert-warning mb-0">No add-on features available.</div>`;
                                                                    }
                                                                    $('#addons-list').html(html);

                                                                    // Bind change event after rendering checkboxes
                                                                    $(document).on('change', 'input.feature-checkbox', computePricing);

                                                                    // Store addon_key as data attribute for each checkbox
                                                                    data.addons.forEach(function (addon) {
                                                                        const id = slugify(addon.name);
                                                                        setTimeout(function () {
                                                                            $('#' + id).attr('data-addon-key', addon.addon_key);
                                                                        }, 0);
                                                                    });
                                                                }).fail(function () {
                                                                    $('#addons-list').html('<div class="alert alert-danger mb-0">Failed to load add-on features.</div>');
                                                                });
                                                            });
                                                        </script>
                                                    </div>
                                                </div>

                                                <div class="card shadow-sm">
                                                    <div class="card-body"
                                                        style="background-color: #f8f9fa; padding: 20px; border-radius: 8px;">
                                                        <h5 style="color: #064857;">Pricing Summary</h5>
                                                        <p style="color: #064857;">Plan: <strong>Starter</strong></p>
                                                        <p style="color: #064857;">Added Employees: <span
                                                                id="sumEmployees">₱490.00</span></p>
                                                        <p style="color: #064857;">Added Features: <span
                                                                id="sumFeatures">₱0.00</span></p>
                                                        <p style="color: #064857;">VAT (12%): <span id="sumVat">₱0.00</span>
                                                        </p>
                                                        <hr style="border-color: #064857;">
                                                        <p><strong id="sumBeforeTrial"
                                                                style="color: #064857;">₱490.00</strong></p>
                                                        <!--  <button type="button" id="planContinueBtn" class="btn" style="background-color: #064857; color:  white; width: 100%; margin-top: 20px; border-radius: 5px; padding: 12px;">Continue</button> -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 2: Basic Information (YOUR EXISTING FIELDS) -->
                                <div class="wizard-step d-none" data-step="2" style="min-height:340px;">
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h6 class="mb-3 text-primary fw-bold"><i class="fas fa-link me-2"></i>Referral
                                                Code <span class="text-danger">*</span></h6>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="input-group">
                                                <input name="referral_code" id="referral_code" class="form-control"
                                                    type="text" required placeholder="Enter referral code">
                                                <button type="button" id="verifyReferralCode"
                                                    class="btn btn-info">Verify</button>
                                            </div>
                                            <span id="referralStatus" class="text-success d-none ms-2">Referral code is
                                                valid.</span>
                                            <span id="referralError" class="text-danger d-none ms-2">Invalid referral
                                                code.</span>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h6 class="mb-3 text-primary fw-bold"><i class="fas fa-user-circle me-2"></i>
                                                User Details</h6>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                                            <input name="first_name" class="form-control" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Middle Name</label>
                                            <input name="middle_name" class="form-control">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                            <input name="last_name" class="form-control" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Suffix</label>
                                            <input name="suffix" class="form-control">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Username <span class="text-danger">*</span></label>
                                            <input name="username" class="form-control" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input name="email" type="email" class="form-control" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Password <span class="text-danger">*</span></label>
                                            <input name="password" type="password" class="form-control" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Confirm Password <span
                                                    class="text-danger">*</span></label>
                                            <input name="confirm_password" type="password" class="form-control" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Phone Number <span
                                                    class="text-danger">*</span></label>
                                            <input name="phone_number" class="form-control" required>
                                        </div>
                                        <input name="role_id" type="hidden" value="2">
                                        <input type="hidden" name="billing_period" id="billing_period" value="monthly">
                                        <input type="hidden" name="is_trial" id="is_trial" value="1">
                                        <input type="hidden" name="plan_slug" id="plan_slug" value="starter">
                                    </div>
                                    <hr>
                                    <h6 class="mb-3 text-primary fw-bold"><i class="fas fa-building me-2"></i>Business
                                        Information</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Company Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="branchName" name="branch_name"
                                                required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Company Location <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="branchLocation"
                                                name="branch_location" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 3: Confirmation -->
                                <div class="wizard-step d-none" data-step="3" style="min-height:340px;">
                                    <h6 class="mb-3 text-primary fw-bold"><i
                                            class="fas fa-check-circle me-2"></i>Confirmation</h6>
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
                                        <i class="fas fa-save me-2"></i>Save Company
                                    </button>
                                </div>

                                <!-- Wizard Progress -->
                                <div class="progress mt-4" style="height: 8px;">
                                    <div class="progress-bar bg-primary" id="wizardProgressBar" role="progressbar"
                                        style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="text-center mt-2">
                                    <small id="wizardStepText" class="text-primary fw-bold">Step 1 of 3</small>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <script>
                // Wizard header updater
                function updateWizardHeader(step) {
                    $('#wizardStepCircle').text(step);
                    const titles = [
                        'Step 1: Plan Summary',
                        'Step 2: Basic Information',
                        'Step 3: Confirmation'
                    ];
                    $('#wizardStepTitle').text(titles[step - 1] || '');
                }

                // Core step switcher
                function showStep(step) {
                    $('.wizard-step').addClass('d-none');
                    $('.wizard-step[data-step="' + step + '"]').removeClass('d-none');
                    $('#wizardPrev').prop('disabled', step === 1);
                    const totalSteps = window.totalSteps || 3;
                    $('#wizardNext').toggleClass('d-none', step === totalSteps);
                    $('#wizardSubmit').toggleClass('d-none', step !== totalSteps);

                    // Progress bar + text
                    let percent = Math.round((step / totalSteps) * 100);
                    $('#wizardProgressBar').css('width', percent + '%').attr('aria-valuenow', percent);
                    $('#wizardStepText').text('Step ' + step + ' of ' + totalSteps);

                    updateWizardHeader(step);

                    // Normalize heights after switch
                    setTimeout(setWizardStepHeight, 100);
                }

                // Height normalization for visible step
                function setWizardStepHeight() {
                    let max = 0;
                    $('.wizard-step').each(function () {
                        $(this).css('min-height', '0');
                        if (!$(this).hasClass('d-none')) {
                            max = Math.max(max, $(this).outerHeight());
                        }
                    });
                    if (max < 340) max = 340;
                    $('.wizard-step').css('min-height', max + 'px');
                }

                // Validation for Step 2 (Basic Info)
                function validateStep2() {
                    let valid = true;
                    const $scope = $('.wizard-step[data-step="2"]');

                    $scope.find('[required]').each(function () {
                        if (!$(this).val()) {
                            $(this).addClass('is-invalid');
                            valid = false;
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });

                    // Password match check
                    let pw = $('[name="password"]').val();
                    let cpw = $('[name="confirm_password"]').val();
                    if (pw !== cpw) {
                        $('[name="confirm_password"]').addClass('is-invalid');
                        valid = false;
                    } else {
                        $('[name="confirm_password"]').removeClass('is-invalid');
                    }
                    return valid;
                }

                // Wire up once DOM ready
                $(function () {
                    window.currentStep = 1;
                    window.totalSteps = 3;

                    // Initial render
                    showStep(window.currentStep);
                    $(window).on('resize', setWizardStepHeight);

                    // Step navigation
                    $('#wizardNext').on('click', function () {
                        // Only validate on Step 2
                        if (window.currentStep === 2) {
                            if (!validateStep2()) {
                                toastr?.error?.('Please complete all required fields and ensure passwords match.');
                                return;
                            }
                        }

                        if (window.currentStep < window.totalSteps) {
                            window.currentStep++;
                            showStep(window.currentStep);

                            // Populate confirmation on final step
                            if (window.currentStep === window.totalSteps) {
                                const html = `
                                                        <ul class="list-group">
                                                            <li class="list-group-item"><strong>Total Employees:</strong> ${$('#totalEmployees').val() || '-'}</li>
                                                            <li class="list-group-item"><strong>Selected Features:</strong> ${($('input[name="features[]"]:checked').map(function () { return $(this).val(); }).get().join(', ') || 'None')
                                    }</li>
                                                            <li class="list-group-item"><strong>REFERRAL CODE:</strong> ${$('[name="referral_code"]').val() || '-'}</li>
                                                            <li class="list-group-item"><strong>First Name:</strong> ${$('[name="first_name"]').val() || '-'}</li>
                                                            <li class="list-group-item"><strong>Middle Name:</strong> ${$('[name="middle_name"]').val() || '-'}</li>
                                                            <li class="list-group-item"><strong>Last Name:</strong> ${$('[name="last_name"]').val() || '-'}</li>
                                                            <li class="list-group-item"><strong>Suffix:</strong> ${$('[name="suffix"]').val() || '-'}</li>
                                                            <li class="list-group-item"><strong>Username:</strong> ${$('[name="username"]').val() || '-'}</li>
                                                            <li class="list-group-item"><strong>Email:</strong> ${$('[name="email"]').val() || '-'}</li>
                                                            <li class="list-group-item"><strong>Phone Number:</strong> ${$('[name="phone_number"]').val() || '-'}</li>
                                                            <li class="list-group-item"><strong>Branch Name:</strong> ${$('[name="branch_name"]').val() || '-'}</li>
                                                            <li class="list-group-item"><strong>Branch Location:</strong> ${$('[name="branch_location"]').val() || '-'}</li>
                                                        </ul>
                                                        `;
                                $('#confirmationDetails').html(html);
                            }
                        }
                    });

                    $('#wizardPrev').on('click', function () {
                        if (window.currentStep > 1) {
                            window.currentStep--;
                            showStep(window.currentStep);
                        }
                    });

                    // Remove is-invalid on input (Step 2 only)
                    $('.wizard-step[data-step="2"] input[required]').on('input', function () {
                        if ($(this).val()) {
                            $(this).removeClass('is-invalid');
                            // AJAX submit
                            $('#addBranchForm').on('submit', function (e) {
                                e.preventDefault();
                                let form = $('#addBranchForm')[0];
                                let formData = new FormData(form);

                                // Collect selected features as objects with addon_key, start_date, end_date
                                let features = [];
                                $('input[name="features[]"]:checked').each(function () {
                                    let addonKey = $(this).data('addon-key');
                                    // You can set start_date and end_date here if needed, for now leave blank or set to null
                                    if (addonKey) {
                                        features.push({
                                            addon_key: addonKey,
                                            start_date: null,
                                            end_date: null
                                        });
                                    }
                                });

                                // Remove existing features[] from FormData
                                formData.delete('features[]');
                                formData.delete('features');
                                // Append features as array of objects (not JSON string)
                                features.forEach(function (feature, idx) {
                                    for (const key in feature) {
                                        if (feature[key] !== undefined && feature[key] !== null) {
                                            formData.append(`features[${idx}][${key}]`, feature[key]);
                                        }
                                    }
                                });

                                // Fix is_trial to boolean true/false
                                formData.set('is_trial', true);

                                $.ajax({
                                    url: "{{ url('/api/affiliate/branch/register') }}",
                                    type: "POST",
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    headers: {}, // no CSRF header for API endpoint
                                    success: function (response) {
                                        if (response.status === 'success') {
                                            toastr?.success?.(response.message || 'Branch saved successfully!');
                                            $('#addBranchForm')[0].reset();
                                            $('#addBranchForm').after('<div class="alert alert-success mt-3">Branch saved successfully!</div>');
                                            // reset wizard
                                            window.currentStep = 1;
                                            showStep(window.currentStep);

                                            // Redirect to payment checkout if URL is present
                                            if (response.payment_checkout_url) {
                                                window.location.href = response.payment_checkout_url;
                                            }
                                        } else {
                                            toastr?.error?.(response.message || 'An error occurred.');
                                        }
                                    },
                                    error: function (xhr) {
                                        let errors = xhr.responseJSON?.errors || {};
                                        if (xhr.responseJSON?.message) {
                                            toastr?.error?.(xhr.responseJSON.message);
                                        }
                                        for (const key in errors) {
                                            toastr?.error?.(errors[key][0]);
                                        }
                                    }
                                });
                            });
                            toastr?.error?.(xhr.responseJSON.message);
                        }
                        for (const key in errors) {
                            toastr?.error?.(errors[key][0]);
                        }
                    }
                                                    });
                                                });
                                            });

                // Height equalization on first paint
                $(function () { setTimeout(setWizardStepHeight, 120); });
            </script>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // =============== Wizard ===============
        let currentStep = 1;
        const totalSteps = 3;

        function updateWizardHeader(step) {
            $('#wizardStepCircle').text(step);
            const titles = [
                'Step 1: Plan Summary',
                'Step 2: Basic Information',
                'Step 3: Confirmation'
            ];
            $('#wizardStepTitle').text(titles[step - 1] || '');
        }

        function setWizardStepHeight() {
            let max = 0;
            $('.wizard-step').each(function () {
                $(this).css('min-height', '0');
                if (!$(this).hasClass('d-none')) max = Math.max(max, $(this).outerHeight());
            });
            if (max < 340) max = 340;
            $('.wizard-step').css('min-height', max + 'px');
        }

        function showStep(step) {
            $('.wizard-step').addClass('d-none');
            $('.wizard-step[data-step="' + step + '"]').removeClass('d-none');

            $('#wizardPrev').prop('disabled', step === 1);
            $('#wizardNext').toggleClass('d-none', step === totalSteps);
            $('#wizardSubmit').toggleClass('d-none', step !== totalSteps);

            const percent = Math.round((step / totalSteps) * 100);
            $('#wizardProgressBar').css('width', percent + '%').attr('aria-valuenow', percent);
            $('#wizardStepText').text('Step ' + step + ' of ' + totalSteps);

            updateWizardHeader(step);
            setTimeout(setWizardStepHeight, 100);
        }

        function validateStep2() {
            let valid = true;
            const $scope = $('.wizard-step[data-step="2"]');

            $scope.find('[required]').each(function () {
                if (!$(this).val()) { $(this).addClass('is-invalid'); valid = false; }
                else { $(this).removeClass('is-invalid'); }
            });

            const pw = $('[name="password"]').val();
            const cpw = $('[name="confirm_password"]').val();
            if (pw !== cpw) { $('[name="confirm_password"]').addClass('is-invalid'); valid = false; }
            else { $('[name="confirm_password"]').removeClass('is-invalid'); }

            return valid;
        }

        // =============== Pricing ===============
        function formatPHP(amount) {
            try {
                return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 }).format(amount);
            } catch (_) {
                return '₱' + (Math.round(amount * 100) / 100).toFixed(2);
            }
        }

        function updatePricingSummary() {
            const employeesMonthly = parseFloat($('#sumEmployees').text().replace('₱', '').replace(',', ''));
            const featuresMonthly = parseFloat($('#sumFeatures').text().replace('₱', '').replace(',', ''));
            const vatMonthly = parseFloat($('#sumVat').text().replace('₱', '').replace(',', ''));

            const totalMonthly = employeesMonthly + featuresMonthly + vatMonthly;

            const html = `
                                        <ul class="list-group">
                                          <li class="list-group-item"><strong>Total Employees:</strong> ₱${employeesMonthly.toFixed(2)}</li>
                                          <li class="list-group-item"><strong>Added Features:</strong> ₱${featuresMonthly.toFixed(2)}</li>
                                          <li class="list-group-item"><strong>VAT (12%):</strong> ₱${vatMonthly.toFixed(2)}</li>
                                          <li class="list-group-item"><strong>Total Monthly Cost:</strong> <strong>₱${totalMonthly.toFixed(2)}</strong></li>
                                        </ul>
                                      `;

            $('#confirmationDetails').html(html);
        }

        function computePricing() {
            const $emp = $('#totalEmployees');
            const totalEmployees = Math.max(0, parseInt($emp.val(), 10) || 0);
            const included = parseInt($emp.data('included'), 10) || 0;
            const perUser = parseFloat($emp.data('price-per-user')) || 49;

            // Employees
            const billableUsers = Math.max(0, totalEmployees - included);
            const employeesMonthly = billableUsers * perUser;

            // Features (sum data-price of checked)
            let featuresMonthly = 0;
            const lines = [];
            $('input[name="features[]"]:checked').each(function () {
                const name = $(this).val();
                const p = parseFloat($(this).data('price')) || 0;
                featuresMonthly += p;
                lines.push(`<li>${name}: <strong>${formatPHP(p)}</strong>/mo</li>`);
            });

            const subtotalMonthly = employeesMonthly + featuresMonthly;
            const vatMonthly = +(subtotalMonthly * 0.12).toFixed(2);
            const subtotalYearly = subtotalMonthly * 12; // adjust if you add annual discount

            // Left card totals
            $('#leftMonthly').text(`${formatPHP(subtotalMonthly)} / month`);
            $('#leftYearly').text(`${formatPHP(subtotalYearly)} / year`);

            // Right summary totals
            $('#sumEmployees').text(formatPHP(employeesMonthly));
            $('#sumFeatures').text(formatPHP(featuresMonthly));
            $('#sumVat').text(formatPHP(vatMonthly));

            // Feature breakdown UI
            $('#featuresBreakdown').html(
                lines.length ? `<ul class="mb-0 ps-3">${lines.join('')}</ul>` : `<em>No add-ons selected</em>`
            );

            // Trial strike-through + total
            $('#sumBeforeTrial').text(formatPHP(subtotalMonthly));
            $('#sumTrial').text(formatPHP(1));

            // Optional hidden fields for backend
            $('#pricingMonthly').val(subtotalMonthly.toFixed(2));
            $('#pricingYearly').val(subtotalYearly.toFixed(2));
            $('#pricingVat').val(vatMonthly.toFixed(2));
            $('#pricingFeatures').val(featuresMonthly.toFixed(2));

            // Call the updatePricingSummary function to update the confirmation section
            updatePricingSummary();
        }

        // =============== Bindings ===============
        $(function () {
            // Initial render
            showStep(currentStep);
            setTimeout(setWizardStepHeight, 120);
            $(window).on('resize', setWizardStepHeight);

            // Nav
            $('#wizardNext').off('click').on('click', function () {
                if (currentStep === 2 && !validateStep2()) {
                    toastr?.error?.('Please complete all required fields and ensure passwords match.');
                    return;
                }
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);

                    if (currentStep === totalSteps) {
                        const features = $('input[name="features[]"]:checked')
                            .map(function () { return $(this).val(); }).get().join(', ') || 'None';

                        const html = `
                                              <ul class="list-group">
                                                <li class="list-group-item"><strong>Total Employees:</strong> ${$('#totalEmployees').val() || '-'}</li>
                                                <li class="list-group-item"><strong>Selected Features:</strong> ${features}</li>
                                                <li class="list-group-item"><strong>REFERRAL CODE:</strong> ${$('[name="referral_code"]').val() || '-'}</li>
                                                <li class="list-group-item"><strong>First Name:</strong> ${$('[name="first_name"]').val() || '-'}</li>
                                                <li class="list-group-item"><strong>Middle Name:</strong> ${$('[name="middle_name"]').val() || '-'}</li>
                                                <li class="list-group-item"><strong>Last Name:</strong> ${$('[name="last_name"]').val() || '-'}</li>
                                                <li class="list-group-item"><strong>Suffix:</strong> ${$('[name="suffix"]').val() || '-'}</li>
                                                <li class="list-group-item"><strong>Username:</strong> ${$('[name="username"]').val() || '-'}</li>
                                                <li class="list-group-item"><strong>Email:</strong> ${$('[name="email"]').val() || '-'}</li>
                                                <li class="list-group-item"><strong>Phone Number:</strong> ${$('[name="phone_number"]').val() || '-'}</li>
                                                <li class="list-group-item"><strong>Branch Name:</strong> ${$('[name="branch_name"]').val() || '-'}</li>
                                                <li class="list-group-item"><strong>Branch Location:</strong> ${$('[name="branch_location"]').val() || '-'}</li>
                                              </ul>
                                            `;
                        $('#confirmationDetails').html(html);
                        updatePricingSummary();
                    }
                }
            });

            $('#wizardPrev').off('click').on('click', function () {
                if (currentStep > 1) { currentStep--; showStep(currentStep); }
            });

            $('.wizard-step[data-step="2"] input[required]').on('input', function () {
                if ($(this).val()) $(this).removeClass('is-invalid');
            });

            $('#planContinueBtn').off('click').on('click', function () {
                $('#wizardNext').trigger('click');
            });

            // Pricing bindings
            $('#totalEmployees').on('input change', computePricing);
            $('input[name="features[]"]').on('change', computePricing);

            // First compute
            computePricing();

            // Recompute when returning to Step 1
            const originalShowStep = window.showStep;
            window.showStep = function (step) {
                if (typeof originalShowStep === 'function') originalShowStep(step);
                if (step === 1) computePricing();
            };

            // AJAX submit (unchanged)
            $('#addBranchForm').off('submit').on('submit', function (e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);

                $.ajax({
                    url: "{{ url('/api/affiliate/branch/register') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {},
                    success: function (response) {
                        if (response.status === 'success') {
                            // toastr?.success?.(response.message || 'Branch saved successfully!');
                            form.reset();
                            $('#addBranchForm').after('<div class="alert alert-success mt-3">Branch saved successfully!</div>');
                            currentStep = 1;
                            showStep(currentStep);
                            computePricing(); // reset totals

                            // Redirect to payment checkout if URL is present
                            if (response.payment_checkout_url) {
                                // Direct redirect to checkout
                                window.location.href = response.payment_checkout_url;
                            }
                        } else {
                            // toastr?.error?.(response.message || 'An error occurred.');
                        }
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        // if (xhr.responseJSON?.message) toastr?.error?.(xhr.responseJSON.message);
                        // for (const key in errors) toastr?.error?.(errors[key][0]);
                    }
                });
            });
        });


        $(document).ready(function () {
            $('#verifyReferralCode').on('click', function () {
                const referralCode = $('#referral_code').val();

                if (!referralCode) {
                    $('#referralError').text("Please enter a referral code.").removeClass('d-none');
                    $('#referralStatus').addClass('d-none');
                    return;
                }

                // Make AJAX request to verify the referral code
                $.ajax({
                    url: '{{ route("verify.referral.code") }}',
                    type: 'POST',
                    data: {
                        referral_code: referralCode,
                        _token: '{{ csrf_token() }}', // CSRF token for security
                    },
                    success: function (response) {
                        if (response.success) {
                            // Code is valid
                            $('#referralStatus').removeClass('d-none');
                            $('#referralError').addClass('d-none');
                            $('#referralStatus').text("Referral code is valid.");
                        } else {
                            // Code is invalid
                            $('#referralError').removeClass('d-none');
                            $('#referralStatus').addClass('d-none');
                            $('#referralError').text(response.message);
                        }
                    },
                    error: function (xhr) {
                        // In case of an error
                        $('#referralError').removeClass('d-none');
                        $('#referralStatus').addClass('d-none');
                        $('#referralError').text('Invalid referral code. Please ask your affiliate for a valid code or try again.');
                    }
                });
            });
        });
    </script>
@endpush