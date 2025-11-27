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

    // ✅ Store all plans globally for filtering
    window.allUpgradePlans = data.available_plans || [];
    window.currentBillingCycle = data.current_billing_cycle || 'monthly';

    // ✅ Set up billing cycle toggle
    const currentCycle = window.currentBillingCycle;
    $('#billing_cycle_toggle').prop('checked', currentCycle === 'yearly');

    // Update label styling based on current selection
    function updateBillingCycleLabels(cycle) {
        if (cycle === 'monthly') {
            $('#billing_cycle_label_monthly').css({'font-weight': '700', 'color': '#0d6efd'});
            $('#billing_cycle_label_yearly').css({'font-weight': '500', 'color': '#6c757d'});
        } else {
            $('#billing_cycle_label_monthly').css({'font-weight': '500', 'color': '#6c757d'});
            $('#billing_cycle_label_yearly').css({'font-weight': '700', 'color': '#0d6efd'});
        }
    }

    updateBillingCycleLabels(currentCycle);

    // ✅ Billing cycle toggle handler
    $('#billing_cycle_toggle').off('change').on('change', function() {
        const isYearly = $(this).is(':checked');
        const selectedCycle = isYearly ? 'yearly' : 'monthly';
        console.log('🔄 Billing cycle toggled to:', selectedCycle);

        updateBillingCycleLabels(selectedCycle);
        renderPlansForCycle(selectedCycle);
    });

    // ✅ Function to render plans based on billing cycle
    function renderPlansForCycle(billingCycle) {
        const filteredPlans = window.allUpgradePlans.filter(plan => plan.billing_cycle === billingCycle);

        console.log(`✅ Rendering ${filteredPlans.length} ${billingCycle} plans`);

        // Clear container
        $('#available_plans_container').empty();
        $('#selected_plan_summary').hide();
        $('#confirmPlanUpgradeBtn').prop('disabled', true);

        if (filteredPlans.length === 0) {
            $('#available_plans_container').html(`
                <div class="col-12 text-center py-5">
                    <i class="ti ti-info-circle fs-1 text-muted mb-3"></i>
                    <p class="text-muted">No ${billingCycle} plans available for upgrade</p>
                </div>
            `);
            return;
        }

        // ✅ Determine column size based on number of plans
        // If 4+ plans, use 4 columns (col-lg-3), otherwise use 3 columns (col-lg-4)
        const planCount = filteredPlans.length;
        const colClass = planCount >= 4 ? 'col-lg-3 col-md-6' : 'col-lg-4 col-md-6';
        const cardMinHeight = planCount >= 4 ? '420px' : '480px'; // Shorter for 4+ plans
        const cardPadding = planCount >= 4 ? 'p-3' : 'p-4'; // Less padding for 4+ plans
        const spacingClass = planCount >= 4 ? 'mb-3' : 'mb-4'; // Tighter spacing
        const headingSize = planCount >= 4 ? 'h5' : 'h4'; // Smaller heading for 4+ plans
        const priceSize = planCount >= 4 ? '2rem' : '2.5rem'; // Smaller price for 4+ plans

        console.log(`📐 Using ${colClass} for ${planCount} plans`);

        filteredPlans.forEach(function(plan, index) {
            console.log('📦 Rendering plan:', plan.name);
            const isRecommended = plan.is_recommended || (data.recommended_plan && plan.id === data.recommended_plan.id);

            // Define color schemes for each plan tier (matching the reference image)
            const planColors = {
                0: { header: '#52C480', headerText: 'white', badge: 'Best for Start Up!' },  // Green - Free/Starter
                1: { header: '#FDB913', headerText: 'white', badge: 'Best Value' },           // Yellow - Core
                2: { header: '#D16074', headerText: 'white', badge: 'Most Popular!' },        // Red - Pro
                3: { header: '#E57F7F', headerText: 'white', badge: 'Enterprise' }             // Coral - Elite
            };

            const colorScheme = planColors[index % 4] || planColors[0];

            const planCard = `
                <div class="${colClass}">
                    <div class="card plan-option h-100 shadow-sm border-0"
                         data-plan-id="${plan.id}"
                         style="cursor: pointer; transition: all 0.3s ease; border-radius: 12px; overflow: hidden;">

                        <!-- Colored Header with Badge -->
                        <div class="position-relative" style="background: ${colorScheme.header}; padding: 1.25rem 1.5rem;">
                            ${isRecommended ? `
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-white text-dark px-2 py-1" style="font-size: 0.7rem; font-weight: 600;">
                                    <i class="ti ti-star-filled" style="color: ${colorScheme.header};"></i> Recommended
                                </span>
                            </div>
                            ` : `
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-white bg-opacity-25 text-white px-2 py-1" style="font-size: 0.7rem; font-weight: 600;">
                                    ${colorScheme.badge}
                                </span>
                            </div>
                            `}

                            <!-- Plan Name with Icon -->
                            <div class="d-flex align-items-center justify-content-between mt-3">
                                <h4 class="fw-bold mb-0" style="color: ${colorScheme.headerText}; font-size: ${planCount >= 4 ? '1.3rem' : '1.5rem'};">
                                    ${plan.name}
                                </h4>
                                <i class="ti ti-package" style="font-size: ${planCount >= 4 ? '1.8rem' : '2rem'}; color: ${colorScheme.headerText}; opacity: 0.9;"></i>
                            </div>
                            <p class="mb-0 mt-1" style="color: ${colorScheme.headerText}; opacity: 0.9; font-size: ${planCount >= 4 ? '0.75rem' : '0.85rem'};">
                                Good for ${plan.employee_limit <= 20 ? 'Start Up' : plan.employee_limit <= 100 ? 'Micro-Small' : plan.employee_limit <= 200 ? 'Medium' : 'Large'} Businesses
                            </p>
                        </div>

                        <div class="card-body p-${planCount >= 4 ? '3' : '4'} d-flex flex-column">
                            <!-- Pricing Section -->
                            <div class="text-center mb-${planCount >= 4 ? '3' : '4'} pb-${planCount >= 4 ? '3' : '4'}" style="border-bottom: 2px solid #f0f0f0;">
                                <div class="d-flex align-items-start justify-content-center mb-2">
                                    <span style="color: #1a1a1a; font-size: ${planCount >= 4 ? '1.5rem' : '1.8rem'}; font-weight: 700; margin-right: 4px;">₱</span>
                                    <h2 class="fw-bold mb-0" style="color: #1a1a1a; font-size: ${planCount >= 4 ? '2.5rem' : '3rem'}; line-height: 1;">
                                        ${parseFloat(plan.price).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0})}
                                    </h2>
                                </div>
                                <p class="text-muted mb-0" style="font-size: ${planCount >= 4 ? '0.8rem' : '0.9rem'};">
                                    ${plan.price > 0 ? 'based price' : ''} ${plan.billing_cycle === 'monthly' ? '/month' : '/year'}
                                </p>
                            </div>

                            <!-- GET STARTED Button -->
                            <button class="btn w-100 py-2 rounded-3 fw-semibold mb-${planCount >= 4 ? '3' : '4'} select-plan-btn"
                                    style="background: ${colorScheme.header}; color: white; border: none; font-size: ${planCount >= 4 ? '0.9rem' : '1rem'}; transition: all 0.3s ease;">
                                GET STARTED
                            </button>

                            <!-- Employee Limit Badge -->
                            <div class="text-center mb-${planCount >= 4 ? '3' : '4'}">
                                <span class="badge px-3 py-2" style="background: rgba(${parseInt(colorScheme.header.slice(1,3), 16)}, ${parseInt(colorScheme.header.slice(3,5), 16)}, ${parseInt(colorScheme.header.slice(5,7), 16)}, 0.15); color: ${colorScheme.header}; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'}; font-weight: 600;">
                                    <i class="ti ti-users me-1"></i> Up to ${plan.employee_limit} Employees
                                </span>
                            </div>

                            <!-- What's Included Section -->
                            <div class="mb-${planCount >= 4 ? '3' : '4'}">
                                <h6 class="fw-bold mb-3" style="color: #1a1a1a; font-size: ${planCount >= 4 ? '0.9rem' : '1rem'};">What's Included</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-check" style="color: ${colorScheme.header}; font-size: 1.1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #333; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">Creation of Portal</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.75rem; color: #999; cursor: help;" title="Your company portal will be created"></i>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-check" style="color: ${colorScheme.header}; font-size: 1.1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #333; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">${plan.employee_limit <= 20 ? '2 Days' : plan.employee_limit <= 100 ? '7 Days' : plan.employee_limit <= 200 ? '7 Days' : '14 Days'} Free Training</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.75rem; color: #999; cursor: help;" title="Free training period"></i>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-check" style="color: ${colorScheme.header}; font-size: 1.1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #333; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">Knowledge Base</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.75rem; color: #999; cursor: help;" title="Access to knowledge base"></i>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-check" style="color: ${colorScheme.header}; font-size: 1.1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #333; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">${plan.employee_limit >= 200 ? 'User Video Tutorial' : 'Lifetime Email Support'}</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.75rem; color: #999; cursor: help;" title="Support included"></i>
                                        </div>
                                    </li>
                                    ${plan.employee_limit >= 200 ? `
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-check" style="color: ${colorScheme.header}; font-size: 1.1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #333; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">Lifetime Email & Call Support</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.75rem; color: #999; cursor: help;" title="Premium support"></i>
                                        </div>
                                    </li>
                                    ` : ''}
                                    ${plan.employee_limit >= 200 ? `
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-check" style="color: ${colorScheme.header}; font-size: 1.1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #333; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">Free ${plan.employee_limit >= 500 ? '2' : '1'} Biometrics Device${plan.employee_limit >= 500 ? 's' : ''}</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.75rem; color: #999; cursor: help;" title="Biometric devices included"></i>
                                        </div>
                                    </li>
                                    ` : ''}
                                    ${plan.employee_limit >= 500 ? `
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-check" style="color: ${colorScheme.header}; font-size: 1.1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #333; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">FREE Custom Company Logo</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.75rem; color: #999; cursor: help;" title="Custom branding included"></i>
                                        </div>
                                    </li>
                                    ` : ''}
                                </ul>
                            </div>

                            <!-- Available Add-Ons Section -->
                            <div class="mt-auto">
                                <h6 class="fw-bold mb-3" style="color: #1a1a1a; font-size: ${planCount >= 4 ? '0.9rem' : '1rem'};">Available Add-Ons</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-tool" style="color: #999; font-size: 1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #666; font-size: ${planCount >= 4 ? '0.8rem' : '0.85rem'};">Custom Company Logo</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.7rem; color: #999; cursor: help;"></i>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-device-mobile" style="color: #999; font-size: 1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #666; font-size: ${planCount >= 4 ? '0.8rem' : '0.85rem'};">Mobile App (iOS & Android)</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.7rem; color: #999; cursor: help;"></i>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-fingerprint" style="color: #999; font-size: 1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #666; font-size: ${planCount >= 4 ? '0.8rem' : '0.85rem'};">Biometrics Integration</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.7rem; color: #999; cursor: help;"></i>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-tool" style="color: #999; font-size: 1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #666; font-size: ${planCount >= 4 ? '0.8rem' : '0.85rem'};">Biometric Labor Installation</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.7rem; color: #999; cursor: help;"></i>
                                        </div>
                                    </li>

                                </ul>
                            </div>

                            <!-- Cost Breakdown (Collapsible) -->
                            <div class="mt-3 pt-3" style="border-top: 2px solid #f0f0f0;">
                                <button class="btn btn-link w-100 text-start p-0 text-decoration-none d-flex align-items-center justify-content-between"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#costBreakdown${plan.id}"
                                        style="color: #666; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">
                                    <span><i class="ti ti-receipt me-2"></i>View Upgrade Cost Breakdown</span>
                                    <i class="ti ti-chevron-down"></i>
                                </button>
                                <div class="collapse mt-3" id="costBreakdown${plan.id}">
                                    <div class="bg-light rounded-3 p-3">
                                        ${plan.implementation_fee_difference > 0 ? `
                                        <div class="d-flex justify-content-between mb-2">
                                            <span style="font-size: 0.85rem; color: #666;">Implementation Fee Diff.</span>
                                            <span style="font-size: 0.85rem; font-weight: 600; color: #333;">₱${parseFloat(plan.implementation_fee_difference).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                        </div>
                                        ` : ''}
                                        <div class="d-flex justify-content-between mb-2">
                                            <span style="font-size: 0.85rem; color: #666;">Plan Price Difference</span>
                                            <span style="font-size: 0.85rem; font-weight: 600; color: #333;">₱${parseFloat(plan.plan_price_difference).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2 pb-2" style="border-bottom: 1px dashed #ddd;">
                                            <span style="font-size: 0.85rem; color: #666;">Subtotal</span>
                                            <span style="font-size: 0.85rem; font-weight: 600; color: #333;">₱${parseFloat(plan.subtotal).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span style="font-size: 0.85rem; color: #666;">VAT (${plan.vat_percentage}%)</span>
                                            <span style="font-size: 0.85rem; font-weight: 600; color: #333;">₱${parseFloat(plan.vat_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center pt-2" style="border-top: 2px solid ${colorScheme.header};">
                                            <span style="font-size: 0.9rem; font-weight: 700; color: #1a1a1a;">Upgrade Cost</span>
                                            <span style="font-size: 1.1rem; font-weight: 700; color: ${colorScheme.header};">₱${parseFloat(plan.total_upgrade_cost).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected State Indicator -->
                        <div class="position-absolute top-0 start-0 w-100 h-100"
                             style="opacity: 0; transition: opacity 0.3s ease; pointer-events: none; border: 3px solid #28c76f; border-radius: 12px; z-index: 5;"></div>
                    </div>
                </div>
            `;
            $('#available_plans_container').append(planCard);
        });

        // Re-attach event handlers
        setupPlanCardHandlers(filteredPlans);
    }

    // ✅ Setup event handlers for plan cards
    function setupPlanCardHandlers(plans) {
        // Add hover effects
        $('#available_plans_container').off('mouseenter mouseleave').on('mouseenter', '.plan-option', function() {
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
        $('#available_plans_container').off('click').on('click', '.plan-option', function() {
            console.log('🖱️ Plan card clicked!');
            const planId = $(this).data('plan-id');
            console.log('Selected plan ID:', planId);
            const plan = window.allUpgradePlans.find(p => p.id === planId);

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
    }

    // ✅ Initial render with current billing cycle
    const initialCycle = currentCycle;
    renderPlansForCycle(initialCycle);

    console.log('✅ Modal info populated');

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
                }

                toastr.success(message);
                setTimeout(function() {
                    // reload the current page via href to ensure full navigation refresh
                    window.location.href = window.location.href;
                }, 800);
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

