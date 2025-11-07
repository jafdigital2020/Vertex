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
            const planCard = `
                <div class="col-md-4 mb-3">
                    <div class="card plan-option ${isRecommended ? 'border-primary' : 'border-secondary'}"
                         data-plan-id="${plan.id}"
                         style="cursor: pointer; transition: all 0.3s;">
                        ${isRecommended ? '<div class="ribbon ribbon-top-right"><span class="bg-primary">Recommended</span></div>' : ''}
                        <div class="card-body text-center">
                            <h5 class="card-title">${plan.name}</h5>
                            <div class="my-3">
                                <h3 class="text-primary">₱${parseFloat(plan.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</h3>
                                <small class="text-muted">per ${plan.billing_cycle}</small>
                            </div>
                            <ul class="list-unstyled text-start">
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Up to <strong>${plan.employee_limit}</strong> users</li>
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Implementation fee: <strong>₱${parseFloat(plan.implementation_fee).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong></li>
                                <li class="mb-2"><i class="ti ti-check text-success me-2"></i>Amount to pay: <strong class="text-primary">₱${parseFloat(plan.implementation_fee_difference).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong></li>
                            </ul>
                            <button class="btn btn-${isRecommended ? 'primary' : 'outline-primary'} w-100 select-plan-btn">
                                ${isRecommended ? 'Select (Recommended)' : 'Select Plan'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#available_plans_container').append(planCard);
        });

        console.log('✅ All plans rendered to container');

        // Handle plan selection using event delegation
        console.log('🎯 Setting up click handlers for plan cards using event delegation');
        $('#available_plans_container').off('click', '.plan-option').on('click', '.plan-option', function() {
            console.log('🖱️ Plan card clicked!');
            const planId = $(this).data('plan-id');
            console.log('Selected plan ID:', planId);
            const plan = data.available_plans.find(p => p.id === planId);

            if (plan) {
                console.log('✅ Plan found:', plan.name);
                // Visual feedback
                $('.plan-option').removeClass('border-primary border-3').addClass('border-secondary');
                $(this).removeClass('border-secondary').addClass('border-primary border-3');

                // Update summary
                $('#summary_plan_name').text(plan.name);
                $('#summary_plan_limit').text('Up to ' + plan.employee_limit + ' users');
                $('#summary_plan_price').text('₱' + parseFloat(plan.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_current_impl_fee').text('₱' + parseFloat(data.current_implementation_fee_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_new_impl_fee').text('₱' + parseFloat(plan.implementation_fee).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_amount_due').text('₱' + parseFloat(plan.implementation_fee_difference).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                $('#selected_plan_summary').show();
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
    $('#plan_upgrade_modale').data('form', form);

    // Show modal
    console.log('📢 Showing plan upgrade modal...');
    $('#plan_upgrade_modale').modal('show');
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
            $('#plan_upgrade_modale').modal('hide');

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

                // Add overage notification if applicable
                if (response.overage_warning) {
                    message += ' Additional license invoice created for ₱' + response.overage_warning.overage_amount;
                }

                toastr.success(message);
                $('#add_employee').modal('hide');
                $('#addEmployeeForm')[0].reset();
                $('#previewImage').attr('src', '{{ URL::asset("build/img/users/user-13.jpg") }}');
                $('.select2').val(null).trigger('change');
                filter();
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

