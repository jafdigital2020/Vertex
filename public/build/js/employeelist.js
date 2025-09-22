
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

let employeeTable;

$(document).ready(() => {
    employeeTable = initFilteredDataTable('#employee_list_table');
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
            if (response.status === 'overage_warning' && response.will_cause_overage) {
                // Show overage confirmation modal
                showOverageConfirmation(response.overage_details, form);
            } else {
                // No overage, proceed normally
                submitEmployeeForm(form);
            }
        },
        error: function () {
            // If check fails, proceed anyway (fallback)
            submitEmployeeForm(form);
        }
    });
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

