$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

let employeeTable;

$(document).ready(() => {
    employeeTable = initFilteredDataTable('#employee_list_table');

    // ✅ Intercept "Add Employee" button click
    $(document).on('click', '#addEmployeeBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        // Check license requirements BEFORE opening modal
        checkLicenseBeforeOpeningAddModal();

        return false;
    });
});
$(document).ready(function () {
    setupBranchDepartmentDesignation('#branch_filter', '#department_filter', '#designation_filter');

    setupBranchDepartmentDesignation('#addBranchId', '#add_departmentSelect', '#add_designationSelect');

    setupBranchDepartmentDesignation('#editBranchId', '#editDepartmentSelect', '#editDesignationSelect');
});

function filter() {
    const params = {
        branch: $('#branch_filter').val(),
        department: $('#department_filter').val(),
        designation: $('#designation_filter').val(),
        status: $('#status_filter').val(),
        sort_by: $('#sortby_filter').val()
    };

    $.get(routes.emplistfilter, params)
        .done(res => {
            if (res.status !== 'success') {
                toastr.warning('Failed to load employee list.');
                return;
            }
            if ($.fn.DataTable.isDataTable('#employee_list_table')) {
                $('#employee_list_table').DataTable().destroy();
            }

            $('#employeeListTableBody').html(res.html);

            $('#employee_list_table').DataTable({
                ordering: true,
                searching: true,
                paging: true
            });
        })
        .fail(() => toastr.error('An error occurred while filtering employee list.'));
}

function populateDropdown($select, items, placeholder = 'Select') {
    $select.empty();
    $select.append(`<option value="">All ${placeholder}</option>`);
    items.forEach(item => {
        $select.append(`<option value="${item.id}">${item.name}</option>`);
    });
}

function setupBranchDepartmentDesignation(branchSelector, departmentSelector, designationSelector) {
    $(branchSelector).on('input', function () {
        const branchId = $(this).val();

        $.get('/api/filter-from-branch', { branch_id: branchId }, function (res) {
            if (res.status === 'success') {
                populateDropdown($(departmentSelector), res.departments, 'Departments');
                populateDropdown($(designationSelector), res.designations, 'Designations');
            }
        });
    });

    $(departmentSelector).on('input', function () {
        const departmentId = $(this).val();
        const branchId = $(branchSelector).val();

        $.get('/api/filter-from-department', {
            department_id: departmentId,
            branch_id: branchId,
        }, function (res) {
            if (res.status === 'success') {
                if (res.branch_id) {
                    $(branchSelector).val(res.branch_id).trigger('change');
                }
                populateDropdown($(designationSelector), res.designations, 'Designations');
            }
        });
    });

    $(designationSelector).on('change', function () {
        const designationId = $(this).val();
        const branchId = $(branchSelector).val();
        const departmentId = $(departmentSelector).val();

        $.get('/api/filter-from-designation', {
            designation_id: designationId,
            branch_id: branchId,
            department_id: departmentId
        }, function (res) {
            if (res.status === 'success') {
                if (designationId === '') {
                    populateDropdown($(designationSelector), res.designations, 'Designations');
                } else {
                    $(branchSelector).val(res.branch_id).trigger('change');
                    $(departmentSelector).val(res.department_id).trigger('change');
                }
            }
        });
    });
}

// Modify existing employeeAdd form submission
$('#addEmployeeForm').on('submit', function (e) {
    e.preventDefault();

    // First check if this will cause overage
    checkLicenseOverageBeforeAdd($(this));
});

// New function to check overage before adding
function checkLicenseOverageBeforeAdd(form) {
    $.ajax({
        url: '/employees/check-license-overage',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.status === 'implementation_fee_required') {
                // Show implementation fee modal
                showImplementationFeeModal(response.data, form);
            } else if (response.status === 'upgrade_required') {
                // Show plan upgrade modal
                showPlanUpgradeModal(response.data, form);
            } else if (response.status === 'overage_warning' && response.will_cause_overage) {
                // Show overage confirmation modal
                showOverageConfirmation(response.overage_details, form);
            } else {
                // No overage, proceed normally
                submitEmployeeForm(form);
            }
        },
        error: function (xhr) {
            // Check for implementation fee or upgrade errors
            if (xhr.status === 402 && xhr.responseJSON) {
                const response = xhr.responseJSON;
                if (response.status === 'implementation_fee_required') {
                    showImplementationFeeModal(response.data, form);
                    return;
                }
            }
            if (xhr.status === 403 && xhr.responseJSON) {
                const response = xhr.responseJSON;
                if (response.status === 'upgrade_required') {
                    showPlanUpgradeModal(response.data, form);
                    return;
                }
            }
            // If check fails for other reasons, show error
            toastr.error('Unable to verify license status. Please try again.');
        }
    });
}

// Show implementation fee modal
function showImplementationFeeModal(data, form) {
    $('#impl_current_users').text(data.current_users);
    $('#impl_new_user_count').text(data.new_user_count);
    $('#impl_fee_amount').text('₱' + parseFloat(data.amount_due).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

    // Store form reference
    $('#implementation_fee_modal').data('form', form);

    // Show modal
    $('#implementation_fee_modal').modal('show');
}

// Show plan upgrade modal with plan selection
function showPlanUpgradeModal(data, form) {
    console.log('🚀 showPlanUpgradeModal called');
    console.log('📊 Data received:', data);
    console.log('📋 Available plans:', data.available_plans);

    // Store upgrade data globally
    window.upgradeData = data;

    // Populate current plan info
    $('#upgrade_current_plan_name').text(data.current_plan || '-');
    $('#upgrade_current_plan_limit').text('Up to ' + (data.current_plan_limit || '-') + ' users');
    $('#upgrade_current_users').text(data.current_users || '-');
    $('#upgrade_new_user_count').text(data.new_user_count || '-');

    // Clear previous plans
    $('#available_plans_container').empty();
    $('#selected_plan_summary').hide();
    $('#confirmPlanUpgradeBtn').prop('disabled', true);

    console.log('✅ Modal info populated');

    // Render available plans
    if (data.available_plans && data.available_plans.length > 0) {
        console.log('✅ Found ' + data.available_plans.length + ' plans to display');
        console.log('✅ Found ' + data.available_plans.length + ' plans to display');

        data.available_plans.forEach(function(plan) {
            console.log('📦 Rendering plan:', plan.name);
            const isRecommended = plan.is_recommended || (data.recommended_plan && plan.id === data.recommended_plan.id);
            const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#064856'; // Get primary color from CSS variable or fallback
            const planCard = `
                <div class="col-lg-4 col-md-6">
                    <div class="card plan-option h-100 position-relative overflow-hidden ${isRecommended ? 'border-primary' : 'border-light'}"
                         data-plan-id="${plan.id}"
                         style="cursor: pointer; transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 16px; border-width: 2px; transform-origin: center;">

                        ${isRecommended ? `
                        <div class="position-absolute top-0 end-0 m-3" style="z-index: 10;">
                            <span class="badge bg-gradient px-3 py-2 rounded-pill shadow-sm text-primary" style="background: linear-gradient(135deg, ${primaryColor} 0%, #064856 100%);">
                                <i class="ti ti-star-filled me-1"></i>Recommended
                            </span>
                        </div>
                        ` : ''}

                        <div class="card-body p-4 d-flex flex-column" style="min-height: 480px;">
                            <!-- Plan Name & Icon -->
                            <div class="text-center mb-4">
                                <div class="position-relative d-inline-block mb-3">
                                    <div class="avatar avatar-xl rounded-circle ${isRecommended ? 'bg-gradient' : 'bg-light'} d-flex align-items-center justify-content-center shadow-sm"
                                         style="${isRecommended ? 'background: linear-gradient(135deg, ' + primaryColor + ' 0%, #064856 100%);' : ''} transition: all 0.3s ease;">
                                        <i class="ti ti-package fs-2 ${isRecommended ? 'text-primary' : 'text-primary'}"></i>
                                    </div>
                                    ${isRecommended ? '<div class="position-absolute top-0 start-100 translate-middle"><span class="badge bg-danger rounded-circle" style="width: 12px; height: 12px; padding: 0;"></span></div>' : ''}
                                </div>
                                <h4 class="fw-bold mb-2" style="color: #2c3e50; letter-spacing: -0.5px;">${plan.name}</h4>
                                <p class="text-muted small mb-0" style="font-size: 0.85rem;">Perfect for growing teams</p>
                            </div>

                            <!-- Pricing -->
                            <div class="text-center mb-4 py-3 rounded-3" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                                <div class="d-flex align-items-baseline justify-content-center">
                                    <span class="text-muted me-1" style="font-size: 1.1rem; font-weight: 500;">₱</span>
                                    <h2 class="fw-bold mb-0" style="background: linear-gradient(135deg, ${primaryColor} 0%, #064856 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 2.5rem;">
                                        ${parseFloat(plan.price).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0})}
                                    </h2>
                                    <span class="text-muted ms-2" style="font-size: 1rem;">/${plan.billing_cycle === 'monthly' ? 'mo' : 'yr'}</span>
                                </div>
                                <small class="text-muted" style="font-size: 0.8rem; font-weight: 500;">Billed ${plan.billing_cycle}</small>
                            </div>

                            <!-- Features -->
                            <div class="mb-4 flex-grow-1">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-3 d-flex align-items-start" style="transition: transform 0.2s ease;">
                                        <div class="flex-shrink-0 me-3">
                                            <span class="avatar avatar-xs rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);">
                                                <i class="ti ti-users text-white fs-6"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-0 fw-semibold" style="color: #2c3e50; font-size: 0.95rem;">Up to ${plan.employee_limit} users</p>
                                            <small class="text-muted" style="font-size: 0.8rem;">User capacity</small>
                                        </div>
                                    </li>
                                    <li class="mb-3 d-flex align-items-start" style="transition: transform 0.2s ease;">
                                        <div class="flex-shrink-0 me-3">
                                            <span class="avatar avatar-xs rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);">
                                                <i class="ti ti-coin text-white fs-6"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-0 fw-semibold" style="color: #2c3e50; font-size: 0.95rem;">₱${parseFloat(plan.implementation_fee).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                                            <small class="text-muted" style="font-size: 0.8rem;">Implementation fee</small>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <!-- Amount Due Breakdown -->
                            <div class="rounded-3 p-3 mb-4 shadow-sm" style="background: linear-gradient(135deg, rgba(${parseInt(primaryColor.slice(1, 3), 16)}, ${parseInt(primaryColor.slice(3, 5), 16)}, ${parseInt(primaryColor.slice(5, 7), 16)}, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-left: 4px solid ${primaryColor};">
                                <small class="text-muted d-block mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Upgrade Cost Breakdown</small>

                                ${plan.implementation_fee_difference > 0 ? `
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted" style="font-size: 0.85rem;">Implementation Fee Difference</span>
                                    <span class="fw-semibold" style="color: #2c3e50;">₱${parseFloat(plan.implementation_fee_difference).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                </div>
                                ` : ''}

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted" style="font-size: 0.85rem;">Plan Price Difference</span>
                                    <span class="fw-semibold" style="color: #2c3e50;">₱${parseFloat(plan.plan_price_difference).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom: 1px dashed rgba(0,0,0,0.1);">
                                    <span class="text-muted" style="font-size: 0.85rem;">Subtotal</span>
                                    <span class="fw-semibold" style="color: #2c3e50;">₱${parseFloat(plan.subtotal).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted" style="font-size: 0.85rem;">VAT (${plan.vat_percentage}%)</span>
                                    <span class="fw-semibold" style="color: #2c3e50;">₱${parseFloat(plan.vat_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Total Amount</small>
                                        <h5 class="fw-bold mb-0" style="background: linear-gradient(135deg, ${primaryColor} 0%, #064856 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                            ₱${parseFloat(plan.total_upgrade_cost).toLocaleString('en-US', {minimumFractionDigits: 2})}
                                        </h5>
                                    </div>
                                    <i class="ti ti-arrow-up-right fs-3" style="color: ${primaryColor}; opacity: 0.3;"></i>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <button class="btn w-100 py-2 rounded-3 shadow-sm fw-semibold select-plan-btn"
                                    style="transition: all 0.3s ease; ${isRecommended ? 'background: linear-gradient(135deg, ' + primaryColor + ' 0%, #064856 100%); color: white; border: none;' : 'background: white; color: ' + primaryColor + '; border: 2px solid ' + primaryColor + ';'}">
                                <i class="ti ti-check-circle me-2"></i>
                                ${isRecommended ? 'Select This Plan' : 'Choose Plan'}
                            </button>
                        </div>

                        <!-- Selected State Overlay -->
                        <div class="position-absolute top-0 start-0 w-100 h-100 border border-success rounded-3"
                             style="opacity: 0; transition: opacity 0.3s ease; pointer-events: none; border-width: 3px !important; z-index: 5;"></div>
                    </div>
                </div>
            `;
            $('#available_plans_container').append(planCard);
        });

        console.log('✅ All plans rendered to container');

        // Handle plan selection using event delegation
        console.log('🎯 Setting up click handlers for plan cards using event delegation');

        // Add hover effects
        $('#available_plans_container').on('mouseenter', '.plan-option', function() {
            const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#667eea';
            $(this).css({
                'transform': 'translateY(-8px) scale(1.02)',
                'box-shadow': `0 20px 40px rgba(${parseInt(primaryColor.slice(1, 3), 16)}, ${parseInt(primaryColor.slice(3, 5), 16)}, ${parseInt(primaryColor.slice(5, 7), 16)}, 0.25)`
            });
        }).on('mouseleave', '.plan-option', function() {
            if (!$(this).hasClass('selected-plan')) {
                $(this).css({
                    'transform': 'translateY(0) scale(1)',
                    'box-shadow': ''
                });
            }
        });

        // Handle plan selection
        $('#available_plans_container').off('click', '.plan-option').on('click', '.plan-option', function() {
            console.log('🖱️ Plan card clicked!');
            const planId = $(this).data('plan-id');
            console.log('Selected plan ID:', planId);
            const plan = data.available_plans.find(p => p.id === planId);

            if (plan) {
                console.log('✅ Plan found:', plan.name);

                // Remove selection from all cards
                $('.plan-option').removeClass('selected-plan').css({
                    'transform': 'translateY(0) scale(1)',
                    'box-shadow': ''
                }).find('.position-absolute.border-success').css('opacity', '0');

                // Add selection to clicked card
                const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#667eea';
                $(this).addClass('selected-plan').css({
                    'transform': 'translateY(-8px) scale(1.02)',
                    'box-shadow': '0 20px 40px rgba(40, 199, 111, 0.3)'
                }).find('.position-absolute.border-success').css('opacity', '1');

                // Update summary
                $('#summary_plan_name').text(plan.name);
                $('#summary_plan_limit').text('Up to ' + plan.employee_limit + ' users');
                $('#summary_plan_price').text('₱' + parseFloat(plan.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                // Show implementation fee difference if greater than 0
                if (plan.implementation_fee_difference > 0) {
                    $('#summary_impl_fee_row').show();
                    $('#summary_impl_fee_difference').text('₱' + parseFloat(plan.implementation_fee_difference).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                } else {
                    $('#summary_impl_fee_row').hide();
                }

                // Show plan price difference
                $('#summary_plan_price_difference').text('₱' + parseFloat(plan.plan_price_difference).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_subtotal').text('₱' + parseFloat(plan.subtotal).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_vat_percentage').text(plan.vat_percentage);
                $('#summary_vat_amount').text('₱' + parseFloat(plan.vat_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_total_amount').text('₱' + parseFloat(plan.total_upgrade_cost).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                // Animate summary appearance
                $('#selected_plan_summary').slideDown(300);
                $('#confirmPlanUpgradeBtn').prop('disabled', false).data('selected-plan-id', planId);

                console.log('✅ Summary updated and button enabled');
            } else {
                console.error('❌ Plan not found for ID:', planId);
            }
        });
    } else {
        console.warn('⚠️ No plans available or empty array');
        $('#available_plans_container').html('<div class="col-12 text-center"><p class="text-muted">No upgrade plans available</p></div>');
    }

    // Store form reference
    $('#plan_upgrade_modal').data('form', form);

    // Show modal
    console.log('📢 Showing plan upgrade modal...');
    $('#plan_upgrade_modal').modal('show');
    console.log('✅ Modal show command executed');
}

// Show overage confirmation modal
function showOverageConfirmation(overageDetails, form) {
    // Populate modal with overage details
    $('#currentLicenseCount').text(overageDetails.current_active_licenses);
    $('#baseLicenseLimit').text(overageDetails.base_license_limit);
    $('#overageCount').text(overageDetails.new_overage_count);
    $('#overageRate').text('₱' + overageDetails.overage_rate_per_license + '/month');
    $('#billingCycle').text(overageDetails.billing_cycle === 'yearly' ? 'Yearly' : 'Monthly');
    $('#additionalCost').text('₱' + overageDetails.additional_monthly_cost);

    // Store form reference for later use
    $('#license_overage_modal').data('form', form);

    // Show modal
    $('#license_overage_modal').modal('show');
}

// Handle implementation fee confirmation - generate invoice then redirect
$('#confirmImplementationFeeBtn').on('click', function () {
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="ti ti-loader me-2"></i>Generating Invoice...');

    // Generate implementation fee invoice
    $.ajax({
        url: '/employees/generate-implementation-fee-invoice',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#implementation_fee_modal').modal('hide');

            if (response.status === 'success') {
                toastr.success('Implementation fee invoice generated. Redirecting to payment...');

                // Redirect to billing page after short delay
                setTimeout(function() {
                    window.location.href = '/billing';
                }, 1500);
            } else {
                toastr.error(response.message || 'Failed to generate invoice');
                btn.prop('disabled', false).html('<i class="ti ti-credit-card me-2"></i>Proceed to Payment');
            }
        },
        error: function(xhr) {
            let message = 'Failed to generate invoice. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            toastr.error(message);
            btn.prop('disabled', false).html('<i class="ti ti-credit-card me-2"></i>Proceed to Payment');
        }
    });
});

// Handle plan upgrade - redirect to billing
// Handle plan upgrade confirmation - generate invoice with selected plan
$('#confirmPlanUpgradeBtn').on('click', function () {
    const btn = $(this);
    const selectedPlanId = btn.data('selected-plan-id');

    if (!selectedPlanId) {
        toastr.error('Please select a plan to upgrade to');
        return;
    }

    btn.prop('disabled', true).html('<i class="ti ti-loader me-2"></i>Generating Invoice...');

    // Generate plan upgrade invoice with selected plan
    $.ajax({
        url: '/employees/generate-plan-upgrade-invoice',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            new_plan_id: selectedPlanId
        },
        success: function(response) {
            $('#plan_upgrade_modal').modal('hide');

            if (response.status === 'success') {
                toastr.success('Plan upgrade invoice generated. Redirecting to payment...');

                // Redirect to billing page after short delay
                setTimeout(function() {
                    window.location.href = '/billing';
                }, 1500);
            } else {
                toastr.error(response.message || 'Failed to generate upgrade invoice');
                btn.prop('disabled', false).html('<i class="ti ti-arrow-up-circle me-2"></i>Proceed with Upgrade');
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON;
            toastr.error(error?.message || 'Failed to generate upgrade invoice');
            btn.prop('disabled', false).html('<i class="ti ti-arrow-up-circle me-2"></i>Proceed with Upgrade');
        }
    });
});

// Handle overage confirmation
$('#confirmOverageBtn').on('click', function () {
    const form = $('#license_overage_modal').data('form');
    $('#license_overage_modal').modal('hide');

    // Proceed with employee creation
    submitEmployeeForm(form);
});

// Extract the actual form submission logic
function submitEmployeeForm(form) {
    let formData = new FormData(form[0]);

    $.ajax({
        url: routes.employeeAdd,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (response.status == 'success') {
                let message = 'Employee created successfully!';

                // Close the add employee modal immediately
                $('#add_employee').modal('hide');
                $('#addEmployeeForm')[0].reset();
                $('#previewImage').attr('src', '{{ URL::asset("build/img/users/user-13.jpg") }}');
                $('.select2').val(null).trigger('change');

                // If there's an overage warning, show the overage modal after closing the add modal
                if (response.overage_warning) {
                    // Wait for the add modal to fully close before showing overage modal
                    $('#add_employee').one('hidden.bs.modal', function() {
                        // Populate overage modal with details
                        $('#currentLicenseCount').text(response.overage_warning.overage_count);
                        $('#overageCount').text(response.overage_warning.overage_count);
                        $('#additionalCost').text('₱' + response.overage_warning.overage_amount);
                        
                        // Update modal message
                        $('#license_overage_modal .modal-body .alert p').last().html(
                            '<strong>Your new employee has been created successfully!</strong><br><br>' +
                            'This action has exceeded your plan\'s license limit. An additional invoice of <strong>₱' + 
                            response.overage_warning.overage_amount + '</strong> has been generated for the license overage.'
                        );

                        // Update button text for acknowledgment
                        $('#confirmOverageBtn').html('<i class="ti ti-check me-1"></i>Acknowledge');
                        
                        // Set action to acknowledge (not activate)
                        $('#license_overage_modal').data('action', 'acknowledge');

                        // Show overage confirmation modal
                        $('#license_overage_modal').modal('show');
                    });
                    
                    message += ' License overage detected - please review the charges.';
                } else {
                    // No overage, just refresh the list
                    filter();
                }

                toastr.success(message);
            } else {
                toastr.error(response.message);
            }
        },
        error: function (xhr, status, error) {
            // Handle implementation fee requirement
            if (xhr.status === 402 && xhr.responseJSON) {
                const response = xhr.responseJSON;
                if (response.status === 'implementation_fee_required') {
                    showImplementationFeeModal(response.data, form);
                    return;
                }
            }

            // Handle plan upgrade requirement
            if (xhr.status === 403 && xhr.responseJSON) {
                const response = xhr.responseJSON;
                if (response.status === 'upgrade_required') {
                    showPlanUpgradeModal(response.data, form);
                    return;
                }
            }

            let message = 'An error occurred.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            toastr.error(message);
        }
    });
}

$('#editEmployeeForm').on('submit', function (e) {
    e.preventDefault();
    let formData = new FormData(this);

    $.ajax({
        url: routes.employeeEdit,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.status == 'success') {
                toastr.success('Employee updated successfully!');
                $('#edit_employee').modal('hide');
                filter();
            }

        },
        error: function (xhr) {
            let message = 'An error occurred.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    const json = JSON.parse(xhr.responseText);
                    if (json.message) message = json.message;
                } catch (e) {
                    message = xhr.responseText;
                }
            }
            console.error('Error editing employee:', message);
            toastr.error(message);
        }
    });
});

$('#deleteEmployeeForm').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
        url: routes.employeeDelete,
        method: 'POST',
        data: $(this).serialize(),
        success: function (response) {
            toastr.success('Employee deleted successfully!');
            $('#delete_modal').modal('hide');
            filter();

        },
        error: function (xhr) {
            let message = 'An error occurred.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    const json = JSON.parse(xhr.responseText);
                    if (json.message) message = json.message;
                } catch (e) {
                    message = xhr.responseText;
                }
            }
            console.error('Error deleting employee:', message);
            toastr.error(message);
        }
    });
});

$('#deactivateEmployeeForm').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
        url: routes.employeeDeactivate,
        method: 'POST',
        data: $(this).serialize(),
        success: function (response) {
            toastr.success('Employee deactivated successfully!');
            $('#deactivate_modal').modal('hide');
            filter();
        },
        error: function (xhr) {
            let message = 'An error occurred.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    const json = JSON.parse(xhr.responseText);
                    if (json.message) message = json.message;
                } catch (e) {
                    message = xhr.responseText;
                }
            }
            console.error('Error deleting employee:', message);
            toastr.error(message);
        }
    });
});

$('#activateEmployeeForm').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
        url: routes.employeeActivate,
        method: 'POST',
        data: $(this).serialize(),
        success: function (response) {
            toastr.success('Employee activated successfully!');
            $('#activate_modal').modal('hide');
            filter();
        },
        error: function (xhr) {
            let message = 'An error occurred.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    const json = JSON.parse(xhr.responseText);
                    if (json.message) message = json.message;
                } catch (e) {
                    message = xhr.responseText;
                }
            }
            console.error('Error deleting employee:', message);
            toastr.error(message);
        }
    });
});

function deleteEmployee(id) {
    $.ajax({
        url: routes.getEmployeeDetails,
        method: 'GET',
        data: {
            emp_id: id,
        },
        success: function (response) {
            if (response.status === 'success') {
                $('#delete_id').val(id);
                $('#delete_modal').modal('show');
            } else {
                toastr.warning('Employee not found.');
            }
        },
        error: function () {
            toastr.error('An error occurred while getting employee details.');
        }
    });
}
function deactivateEmployee(id) {
    $.ajax({
        url: routes.getEmployeeDetails,
        method: 'GET',
        data: {
            emp_id: id,
        },
        success: function (response) {
            if (response.status === 'success') {
                $('#deact_id').val(id);
                $('#deactivate_modal').modal('show');
            } else {
                toastr.warning('Employee not found.');
            }
        },
        error: function () {
            toastr.error('An error occurred while getting employee details.');
        }
    });
}

// ✅ FIXED: activateEmployee function
function activateEmployee(id) {
    console.log('Activating employee:', id);

    // First check overage before activation
    $.ajax({
        url: '/employees/check-license-overage',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            console.log('Overage check response:', response);

            if (response.status === 'overage_warning' && response.will_cause_overage) {
                // Show overage confirmation modal
                showActivationOverageModal(response.overage_details, id);
            } else {
                // No overage, proceed directly with activation
                proceedWithActivation(id);
            }
        },
        error: function (xhr) {
            console.error('Overage check failed:', xhr);
            // Fallback - proceed directly with activation
            proceedWithActivation(id);
        }
    });
}

// ✅ NEW: Show overage modal specifically for activation
function showActivationOverageModal(overageDetails, employeeId) {
    // Populate modal with overage details
    $('#currentLicenseCount').text(overageDetails.current_active_licenses || 0);
    $('#baseLicenseLimit').text(overageDetails.base_license_limit || 0);

    // Calculate what the new count will be
    const newCount = (overageDetails.current_active_licenses || 0) + 1;
    const overageCount = newCount - (overageDetails.base_license_limit || 0);

    // Update modal content for activation
    $('#license_overage_modal .modal-title').text('License Limit Exceeded');
    $('#license_overage_modal .modal-body p').first().html(
        `Activating this employee will exceed your license limit.<br>
        Current active licenses: <strong>${overageDetails.current_active_licenses || 0}</strong><br>
        License limit: <strong>${overageDetails.base_license_limit || 0}</strong><br>
        New total after activation: <strong>${newCount}</strong><br>
        Overage licenses: <strong>${overageCount}</strong><br><br>
        Additional charges will apply for the overage licenses.`
    );

    // Store employee ID and action type in modal data
    $('#license_overage_modal').data('employeeId', employeeId);
    $('#license_overage_modal').data('action', 'activate');

    // Update button text
    $('#confirmOverageBtn').html('<i class="ti ti-check me-1"></i>Proceed with Activation');

    // Show the modal
    $('#license_overage_modal').modal('show');
}

// ✅ UPDATED: Handle overage confirmation
$(document).on('click', '#confirmOverageBtn', function() {
    const employeeId = $('#license_overage_modal').data('employeeId');
    const action = $('#license_overage_modal').data('action');

    // Hide the modal first
    $('#license_overage_modal').modal('hide');

    if (action === 'activate' && employeeId) {
        // Proceed with activation despite overage
        proceedWithActivation(employeeId);
    } else if (action === 'acknowledge') {
        // Just acknowledge overage after employee creation
        filter(); // Refresh the employee list
        toastr.info('License overage has been noted. Invoice has been generated.');
    }
});

// ✅ UPDATED: Proceed with activation
function proceedWithActivation(employeeId) {
    console.log('Proceeding with activation for employee:', employeeId);

    // Show loading state
    toastr.info('Activating employee...', '', { timeOut: 1000 });

    $.ajax({
        url: '/employee-activate', // Make sure this matches your route
        method: 'POST',
        data: {
            act_id: employeeId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {

            let message = 'Employee activated successfully!';

            if (response.overage_warning) {
                message += ` Additional license charges of ₱${response.overage_warning.overage_amount} will be invoiced.`;
            }

            toastr.success(message);

            filter();
        },
        error: function (xhr) {
            console.error('Activation error:', xhr);

            let message = 'Failed to activate employee.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            toastr.error(message);
        }
    });
}

// ✅ NEW: Check license before opening add employee modal
function checkLicenseBeforeOpeningAddModal() {
    console.log('🔍 Checking license before opening modal...');

    $.ajax({
        url: '/employees/check-license-overage',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            console.log('✅ License check response:', response);

            if (response.status === 'implementation_fee_required') {
                console.log('💰 Implementation fee required - showing modal');
                // Show implementation fee modal INSTEAD of add employee form
                showImplementationFeeModal(response.data, null);
            } else if (response.status === 'upgrade_required') {
                console.log('🚀 Plan upgrade required - showing modal');
                console.log('📊 Upgrade data:', response.data);
                // Show plan upgrade modal INSTEAD of add employee form
                showPlanUpgradeModal(response.data, null);
            } else {
                console.log('✅ OK to add employee - showing add modal');
                // OK to proceed - open add employee modal
                $('#add_employee').modal('show');
            }
        },
        error: function (xhr) {
            console.error('❌ License check failed:', xhr);
            console.error('Status:', xhr.status);
            console.error('Response:', xhr.responseJSON);

            if (xhr.status === 402 && xhr.responseJSON) {
                const response = xhr.responseJSON;
                if (response.status === 'implementation_fee_required') {
                    showImplementationFeeModal(response.data, null);
                    return;
                }
            }
            if (xhr.status === 403 && xhr.responseJSON) {
                const response = xhr.responseJSON;
                if (response.status === 'upgrade_required') {
                    showPlanUpgradeModal(response.data, null);
                    return;
                }
            }
            // If check fails, show error
            toastr.error('Unable to verify license status. Please try again.');
        }
    });
}

// ✅ Handle overage modal dismissal - refresh list when modal is closed
$('#license_overage_modal').on('hidden.bs.modal', function() {
    const action = $(this).data('action');
    
    // If this was an acknowledgment after employee creation, refresh the list
    if (action === 'acknowledge') {
        filter(); // Refresh the employee list to show the newly added employee
        // Clear the action data
        $(this).removeData('action');
    }
});

