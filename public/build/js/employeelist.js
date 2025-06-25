
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$('#addEmployeeForm').on('submit', function (e) {
    e.preventDefault();

    let form = $(this)[0];
    let formData = new FormData(form);

    $.ajax({
        url: routes.employeeAdd,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
        },
        success: function (response) {
            if (response.status == 'success') {
                toastr.success('Employee created successfully!');
                $('#add_employee').modal('hide');
                $('#addEmployeeForm')[0].reset();
                $('#previewImage').attr('src', '{{ URL::asset("build/img/users/user-13.jpg") }}');
                $('.select2').val(null).trigger('change');
                empList_filter();
            } else {
                toastr.error(response.message);
            }

        },
        error: function (xhr, status, error) {
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
            console.error('Error adding employee:', message);
            toastr.error(message);

        }
    });
});

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
                empList_filter();
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
            empList_filter();
            setTimeout(function () {
                location.reload();
            }, 500);
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
            empList_filter();
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
            empList_filter();
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
function activateEmployee(id) {
    $.ajax({
        url: routes.getEmployeeDetails,
        method: 'GET',
        data: {
            emp_id: id,
        },
        success: function (response) {
            if (response.status === 'success') {
                $('#act_id').val(id);
                $('#activate_modal').modal('show');
            } else {
                toastr.warning('Employee not found.');
            }
        },
        error: function () {
            toastr.error('An error occurred while getting employee details.');
        }
    });
}
function branchReset_filter() {
    autoFilterBranch('branch_filter', 'department_filter', 'designation_filter', true);
    empList_filter();
}
function departmentReset_filter() {
    autoFilterDepartment('department_filter', 'branch_filter', 'designation_filter', true);
    empList_filter();
}
function designationReset_filter() {
    autoFilterDesignation('designation_filter', 'branch_filter', 'department_filter', true);
    empList_filter();
}


function empList_filter() {
    let branch_filter = $('#branch_filter').val();
    let department_filter = $('#department_filter').val();
    let designation_filter = $('#designation_filter').val();
    let status_filter = $('#status_filter').val();
    let sortby_filter = $('#sortby_filter').val();

    $.ajax({
        url: routes.emplistfilter,
        method: 'GET',
        data: {
            branch: branch_filter,
            department: department_filter,
            designation: designation_filter,
            status: status_filter,
            sort_by: sortby_filter
        },
        success: function (response) {
            if (response.status === 'success') {
                let tbody = '';
                $.each(response.data, function (i, employeeList) {

                    let empID = employeeList.employment_detail.employee_id;
                    let empPicture = employeeList.personal_information.profile_picture;
                    let imgSrc = `/storage/${empPicture}`;
                    let fullName = employeeList.personal_information?.last_name + ' ' + employeeList
                        .personal_information?.first_name;
                    let email = employeeList.email;
                    let department = employeeList.employment_detail.department.department_name;
                    let designation = employeeList.employment_detail.designation.designation_name;
                    let date_hired = new Date(employeeList.employment_detail.date_hired).toISOString().split('T')[0];

                    let status = Number(employeeList.employment_detail?.status);
                    let statusBadge = (status === 1)
                        ? '<span class="badge bg-success"><i class="ti ti-point-filled me-1"></i>Active</span>'
                        : '<span class="badge bg-danger"><i class="ti ti-point-filled me-1"></i>Inactive</span>';


                    let action = `<div class="action-icon d-inline-flex">`;

                    if (response.permission.includes('Update')) {
                        if (status == 0) {
                            action += `
                                    <a href="#" class="btn-activate" onclick="activateEmployee(${employeeList.id})" title="Activate">
                                        <i class="ti ti-circle-check"></i>
                                    </a>`;
                        } else {
                            action += `
                                    <a href="#" class="btn-deactivate" onclick="deactivateEmployee(${employeeList.id})" title="Deactivate">
                                        <i class="ti ti-cancel"></i>
                                    </a>`;
                        }
                    }

                    if (response.permission.includes('Delete')) {
                        action += `
                                <a href="#" class="btn-delete" onclick="deleteEmployee(${employeeList.id})" title="Delete">
                                    <i class="ti ti-trash"></i>
                                </a>`;
                    }

                    action += `</div>`;
                    let tdActions = '<td>';

                    if (response.permission.includes('Read')) {
                        tdActions += `
                                <a href="/employees/employee-details/${employeeList.id}" class="me-2" title="View Full Details">
                                    <i class="ti ti-eye"></i>
                                </a>`;
                    }

                    if (response.permission.includes('Update')) {
                        tdActions += `
                                <a href="#" class="me-2" onclick="editEmployee(${employeeList.id})">
                                    <i class="ti ti-edit"></i>
                                </a>`;
                    }

                    tdActions += `${empID}</td>`;
                    if (response.permission.includes('Read')) {
                        tbody += `
                          <tr>
                              ${tdActions}
                            </td>
                            <td>
                            <div class="d-flex align-items-center">
                                <a href="/employee-details" class="avatar avatar-md"
                                data-bs-toggle="modal" data-bs-target="#view_details">
                                <img src="${imgSrc}" class="img-fluid rounded-circle" alt="img">
                                </a>
                                <div class="ms-2">
                                <h6 class="fw-medium"><a href="#">${fullName}</a></h6>
                                </div>
                            </div>
                            </td>
                            <td>${email}</td>
                            <td>${department}</td>
                            <td>${designation}</td>
                            <td>${date_hired}</td>
                            <td>${statusBadge}</td>  `;
                        if (response.permission.includes('Update')) {
                            tbody += `<td class="text-center">${action}</td>`;
                        }
                        tbody += `</tr>`;
                    }
                });
                $('#employee_list_table tbody').html(tbody);
            } else {
                toastr.warning('Failed to load employee list.');
            }
        },
        error: function () {
            toastr.error('An error occurred while filtering employee list.');
        }
    });
}

function autoFilterBranch(branchSelect, departmentSelect, designationSelect, isFilter = false) {
    var branch = $('#' + branchSelect).val();
    var departmentSelect = $('#' + departmentSelect);
    var designationSelect = $('#' + designationSelect);
    var departmentPlaceholder = isFilter ? 'All Departments' : 'Select Department';
    var designationPlaceholder = isFilter ? 'All Designations' : 'Select Designation';
    $.ajax({
        url: routes.branchAutoFilter,
        method: 'GET',
        data: {
            branch: branch,
        },
        success: function (response) {
            if (response.status === 'success') {
                departmentSelect.empty().append(`<option value="" selected>${departmentPlaceholder}</option>`);
                designationSelect.empty().append(`<option value="" selected>${designationPlaceholder}</option>`);

                $.each(response.departments, function (i, department) {
                    departmentSelect.append(
                        $('<option>', {
                            value: department.id,
                            text: department.department_name
                        })
                    );
                });
                $.each(response.designations, function (i, designation) {
                    designationSelect.append(
                        $('<option>', {
                            value: designation.id,
                            text: designation.designation_name
                        })
                    );
                });
            } else {
                toastr.warning('Failed to get departments and designation list.');
            }
        },
        error: function () {
            toastr.error('An error occurred while getting departments and designation list.');
        }
    });
}

function autoFilterDepartment(departmentSelect, branchSelect, designationSelect, isFilter = false) {
    let department = $('#' + departmentSelect).val();
    let branch_select = $('#' + branchSelect);
    let designation_select = $('#' + designationSelect);
    var designationPlaceholder = isFilter ? 'All Designations' : 'Select Designation';

    $.ajax({
        url: routes.departmentAutoFilter,
        method: 'GET',
        data: {
            department: department,
            branch: branch_select.val(),
        },
        success: function (response) {
            if (response.status === 'success') {
                if (response.branch_id !== '') {
                    branch_select.val(response.branch_id).trigger('change');
                }
                designation_select.empty().append(`<option value="" selected>${designationPlaceholder}</option>`);
                $.each(response.designations, function (i, designation) {
                    designation_select.append(
                        $('<option>', {
                            value: designation.id,
                            text: designation.designation_name
                        })
                    );
                });
            } else {
                toastr.warning('Failed to get branch and designation list.');
            }
        },
        error: function () {
            toastr.error('An error occurred while getting branch and designation list.');
        }
    });
}

function autoFilterDesignation(designationSelect, branchSelect, departmentSelect, isFilter = false) {
    let designation = $('#' + designationSelect).val();
    let branch_select = $('#' + branchSelect);
    let department_select = $('#' + departmentSelect);

    $.ajax({
        url: routes.designationAutoFilter,
        method: 'GET',
        data: {
            designation: designation,
        },
        success: function (response) {
            if (response.status === 'success') {
                if (response.department_id !== '') {
                    department_select.val(response.department_id).trigger('change');
                }
                if (response.branch_id !== '') {
                    branch_select.val(response.branch_id).trigger('change');
                }
            } else {
                toastr.warning('Failed to get branch and department list.');
            }
        },
        error: function () {
            toastr.error('An error occurred while getting branch and department list.');
        }
    });



}


