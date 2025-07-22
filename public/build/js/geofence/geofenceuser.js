document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const authToken = localStorage.getItem('token');

    // Function to show the current active tab
    const tabKey = 'activeGeofenceTab';

    const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
    tabLinks.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            localStorage.setItem(tabKey, event.target.getAttribute('href'));
        });
    });

    const activeTab = localStorage.getItem(tabKey);
    if (activeTab) {
        const tabTrigger = document.querySelector(`a[href="${activeTab}"]`);
        if (tabTrigger) new bootstrap.Tab(tabTrigger).show();
    }

    // ============== Assigning Filter ============== //

    // — Helper: if user picks the empty‐value “All” option, auto-select every real option
    function handleSelectAll($sel) {
        const vals = $sel.val() || [];
        if (vals.includes('')) {
            const all = $sel.find('option')
                .map((i, opt) => $(opt).val())
                .get()
                .filter(v => v !== '');
            $sel.val(all).trigger('change');
            return true;
        }
        return false;
    }

    // — Rebuild Employee list based on selected Departments & Designations
    function updateEmployeeSelect(modal) {
        const allEmps = modal.data('employees') || [];
        const deptIds = modal.find('.department-select').val() || [];
        const desigIds = modal.find('.designation-select').val() || [];

        const filtered = allEmps.filter(emp => {
            if (deptIds.length && !deptIds.includes(String(emp.department_id))) return false;
            if (desigIds.length && !desigIds.includes(String(emp.designation_id))) return false;
            return true;
        });

        let opts = '<option value="">All Employee</option>';
        filtered.forEach(emp => {
            const u = emp.user?.personal_information;
            if (u) {
                opts += `<option value="${emp.user.id}">
           ${u.last_name}, ${u.first_name}
         </option>`;
            }
        });

        modal.find('.employee-select')
            .html(opts)
            .trigger('change');
    }
 

    // — Branch change → fetch Depts, Emps & Shifts
    $(document).on('change', '.branch-select', function () {
        const $this = $(this);
        if (handleSelectAll($this)) return;

        const branchIds = $this.val() || [];
        const modal = $this.closest('.modal');
        const depSel = modal.find('.department-select');
        const desSel = modal.find('.designation-select');
        const empSel = modal.find('.employee-select');
        const shiftSel = modal.find('.shift-select');

        // reset downstream
        depSel.html('<option value="">All Department</option>').trigger('change');
        desSel.html('<option value="">All Designation</option>').trigger('change');
        empSel.html('<option value="">All Employee</option>').trigger('change');
        shiftSel.html('<option value="">All Shift</option>').trigger('change');
        modal.removeData('employees');

        if (!branchIds.length) return;

        $.ajax({
            url: '/api/shift-management/get-branch-data?' + $.param({
                branch_ids: branchIds
            }),
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Authorization': 'Bearer ' + authToken
            },
            success(data) {
                // populate Departments
                let dOpts = '<option value="">All Department</option>';
                data.departments.forEach(d => {
                    dOpts +=
                        `<option value="${d.id}">${d.department_name}</option>`;
                });
                depSel.html(dOpts).trigger('change');

                // cache & render Employees
                modal.data('employees', data.employees || []);
                updateEmployeeSelect(modal);

                // populate Shifts (ensure your API now returns data.shifts[])
                let sOpts = '<option value="">All Shift</option>';
                (data.shifts || []).forEach(s => {
                    sOpts += `<option value="${s.id}">${s.name}</option>`;
                });
                shiftSel.html(sOpts).trigger('change');
            },
            error() {
                alert('Failed to fetch branch data.');
            }
        });
    });

    // — Department change → fetch Designations & re-filter Employees
    $(document).on('change', '.department-select', function () {
        const $this = $(this);
        if (handleSelectAll($this)) return;

        const deptIds = $this.val() || [];
        const modal = $this.closest('.modal');
        const desSel = modal.find('.designation-select');

        desSel.html('<option value="">All Designation</option>').trigger('change');
        updateEmployeeSelect(modal);

        if (!deptIds.length) return;

        $.ajax({
            url: '/api/shift-management/get-designations?' + $.param({
                department_ids: deptIds
            }),
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Authorization': 'Bearer ' + authToken
            },
            success(data) {
                let o = '<option value="">All Designation</option>';
                data.forEach(d => {
                    o += `<option value="${d.id}">${d.designation_name}</option>`;
                });
                desSel.html(o).trigger('change');
            },
            error() {
                alert('Failed to fetch designations.');
            }
        });
    });

    // — Designation change → re-filter Employees
    $(document).on('change', '.designation-select', function () {
        const $this = $(this);
        if (handleSelectAll($this)) return;
        updateEmployeeSelect($this.closest('.modal'));
    });

    // — Employee “All Employee” handler
    $(document).on('change', '.employee-select', function () {
        handleSelectAll($(this));
    });

    // — Shift “All Shift” handler
    $(document).on('change', '.shift-select', function () {
        handleSelectAll($(this));
    });

    // ============== Assigning Geofence User ============== //
    const assignGeofenceForm = document.getElementById('assignGeofenceForm');

    assignGeofenceForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const userIds = $('#geofenceUserId').val(); // multiple
        const geofenceIds = $('#geofenceId').val(); // multiple
        const assignmentType = $('#assignmentType').val();

        if (!userIds || !geofenceIds || !assignmentType) {
            toastr.error('Please fill all required fields.');
            return;
        }

        const assignments = [];
        userIds.forEach(userId => {
            geofenceIds.forEach(geofenceId => {
                assignments.push({
                    user_id: userId,
                    geofence_id: geofenceId,
                    assignment_type: assignmentType
                });
            });
        });

        fetch("/api/settings/geofence/assignment", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`,
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                assignments
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Geofence(s) assigned successfully.');

                    // Reset form and Select2 fields
                    assignGeofenceForm.reset();
                    $('.select2').val(null).trigger('change');
                    
                    const modalElement = bootstrap.Modal.getInstance(document.getElementById('assign_geofence'));
                    if (modalElement) {
                        modalElement.hide();
                    }
                    user_filter(); 
                    
                } else {
                    toastr.error(data.message || 'Failed to assign geofence.');
                }
            })
            .catch(error => {
                console.error(error);
                toastr.error('An unexpected error occurred.');
            });
    });

    // ============== Edit Assigned Geofence User ============== //

    let editId = "";

    // 🌟 1. Populate fields when edit icon is clicked
    document.addEventListener('click', function (e) {
        const button = e.target.closest('[data-bs-target="#edit_assign_geofence"]');
        if (button) {
            const editId = button.getAttribute("data-id");
            document.getElementById("editGeofenceUserId").value = editId;

            const geofenceId = button.getAttribute("data-geofence-id");
            const editGeofenceSelect = document.getElementById("editAssignGeofenceId");
            editGeofenceSelect.value = geofenceId;

            const assignmentType = button.getAttribute("data-assignment-type");
            const editAssignmentTypeSelect = document.getElementById("editAssignmentType");
            editAssignmentTypeSelect.value = assignmentType;

            editGeofenceSelect.dispatchEvent(new Event('change'));
            editAssignmentTypeSelect.dispatchEvent(new Event('change'));
        }
    });

    // 🌟 2. Handle update button click
    document.getElementById("geofenceUserUpdateBtn").addEventListener("click", async function (event) {
        event.preventDefault();
        
        let editGeofenceUserId = document.getElementById("editGeofenceUserId").value.trim();
        let editGeofenceId = document.getElementById("editAssignGeofenceId").value.trim();
        let editAssignmentType = document.getElementById("editAssignmentType").value.trim();

        if (editGeofenceId === "" || editAssignmentType === "") {
            toastr.error("Please complete all fields.");
            return;
        }

        try {
            let response = await fetch(
                `/api/settings/geofence/assignment/update/${editGeofenceUserId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    geofence_id: editGeofenceId,
                    assignment_type: editAssignmentType
                })
            });

            let data = await response.json();

            if (response.ok) {
               toastr.success("User's geofence updated successfully!");
               $('#edit_assign_geofence').modal('hide');
               user_filter();
            } else {
                toastr.error(data.message || "Update failed.");
            }
        } catch (error) {
            console.error(error);
            toastr.error("Something went wrong.");
        }
    });

    // ============== Delete Assigned Geofence User ============== //

    // Geofence deletion
    let deleteId = null;
 
    const geofenceUserDeleteBtn = document.getElementById('geofenceUserDeleteBtn');

    // Set up the delete buttons to capture data
    document.addEventListener('click', function (e) {
        const button = e.target.closest('.btn-deleteGeofenceUser');
        if (button) {
            deleteId = button.getAttribute('data-id'); 
            const input = document.getElementById('deleteGeofenceUserId');
            if (input) input.value = deleteId;
        }
    });

    // Confirm delete button click event
    geofenceUserDeleteBtn?.addEventListener('click', function () {
        if (!deleteId) return;

        fetch(`/api/settings/geofence/assignment/delete/${deleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content"),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`,
            },
        })
            .then(response => {
                if (response.ok) {
                    toastr.success("User's geofence deleted successfully.");

                    const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                        'delete_assign_geofence'));
                    deleteModal.hide(); // Hide the modal   
                    user_filter();
                } else {
                    return response.json().then(data => {
                        toastr.error(data.message ||
                            "Error user geofence.");
                    });
                }
            })
            .catch(error => {
                console.error(error);
                toastr.error("Server error.");
            });
    });
});
function user_filter() {   
    var branch = $('#branch_filter').val();
    var department = $('#department_filter').val();
    var designation = $('#designation_filter').val();
    var type = $('#type_filter').val();
    $.ajax({
        url: geofenceUserFilterUrl,
        type: 'GET',
        data: {
            branch: branch,
            department: department,
            designation: designation, 
            type: type,
        },
        success: function(response) {
            if (response.status === 'success') {  
               
                $('#usersTableBody').html(response.html);
            } else if (response.status === 'error') {
                toastr.error(response.message || 'Something went wrong.');
            }
        },
        error: function(xhr) {
            let message = 'An unexpected error occurred.';
            if (xhr.status === 403) {
                message = 'You are not authorized to perform this action.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            toastr.error(message);
        }
    });
} 
function populateDropdown($select, items, placeholder = 'Select') {
    $select.empty();
    $select.append(`<option value="">All ${placeholder}</option>`);
    items.forEach(item => {
        $select.append(`<option value="${item.id}">${item.name}</option>`);
    });
}

$(document).ready(function() {

    $('#branch_filter').on('input', function() {
        const branchId = $(this).val();

        $.get('/api/filter-from-branch', {
            branch_id: branchId
        }, function(res) {
            if (res.status === 'success') {
                populateDropdown($('#department_filter'), res.departments, 'Departments');
                populateDropdown($('#designation_filter'), res.designations,
                'Designations');
            }
        });
    });


    $('#department_filter').on('input', function() {
        const departmentId = $(this).val();
        const branchId = $('#branch_filter').val();

        $.get('/api/filter-from-department', {
            department_id: departmentId,
            branch_id: branchId,
        }, function(res) {
            if (res.status === 'success') {
                if (res.branch_id) {
                    $('#branch_filter').val(res.branch_id).trigger('change');
                }
                populateDropdown($('#designation_filter'), res.designations,
                'Designations');
            }
        });
    });

    $('#designation_filter').on('change', function() {
        const designationId = $(this).val();
        const branchId = $('#branch_filter').val();
        const departmentId = $('#department_filter').val();

        $.get('/api/filter-from-designation', {
            designation_id: designationId,
            branch_id: branchId,
            department_id: departmentId
        }, function(res) {
            if (res.status === 'success') {
                if (designationId === '') {
                    populateDropdown($('#designation_filter'), res.designations,
                        'Designations');
                } else {
                    $('#branch_filter').val(res.branch_id).trigger('change');
                    $('#department_filter').val(res.department_id).trigger('change');
                }
            }
        });
    });

}); 