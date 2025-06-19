document.addEventListener("DOMContentLoaded", function () {
    let authToken = localStorage.getItem("token");
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

    // Designation Store
    document.getElementById("addDesignationForm")?.addEventListener("submit", async function (event) {
        event.preventDefault();
        let designationName = document.getElementById("designationName").value.trim();
        let departmentId = document.getElementById("departmentId").value.trim();
        let jobDescription = document.getElementById("jobDescription").value.trim();
        let branchId = document.getElementById("branchId").value.trim();

        if (!designationName || !departmentId || !branchId) {
            toastr.error("Please complete all required fields.");
            return;
        }

        try {
            let response = await fetch("/api/designations/create", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({ designation_name: designationName, department_id: departmentId, job_description: jobDescription })
            });
            let data = await response.json();
            response.ok ? toastr.success("Designation created successfully!") && setTimeout(() => location.reload(), 1500) : toastr.error(data.message || "Failed to create designation.");
        } catch (error) {
            console.error(error);
            toastr.error("Something went wrong.");
        }
    });

    // Load Departments Function
    function loadDepartments(branchId, departmentDropdown, selectedDepartmentId = null) {
        if (!branchId) {
            departmentDropdown.empty().append('<option value="" disabled selected>Select Department</option>');
            return;
        }

        $.ajax({
            url: `/designations/departments/${branchId}`,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                departmentDropdown.empty().append('<option value="" disabled selected>Select Department</option>');
                $.each(data, function (key, value) {
                    departmentDropdown.append(`<option value="${value.id}">${value.department_name}</option>`);
                });
                if (selectedDepartmentId) departmentDropdown.val(selectedDepartmentId).trigger('change');
            }
        });
    }

    // Create Form - Load Departments when Branch Changes
    $('#branchId').on('change', function () {
        loadDepartments($(this).val(), $('#departmentId'));
    });

    // Edit Modal - Populate Fields when Edit Button is Clicked
    $('[data-bs-target="#edit_designation"]').on('click', function () {
        let editId = $(this).data("id"), branchId = $(this).data("branch_id"), departmentId = $(this).data("department_id");
        $('#editDesignationId').val(editId);
        $('#editDesignationName').val($(this).data("designation_name"));
        $('#editJobDescription').val($(this).data("job_description"));
        $('#editBranchId').val(branchId).trigger('change');
        setTimeout(() => loadDepartments(branchId, $('#editDepartmentId'), departmentId), 100);
    });

    // Edit Modal - Reload Departments when Branch Changes
    $('#editBranchId').on('change', function () {
        loadDepartments($(this).val(), $('#editDepartmentId'));
    });

    // Update Designation
    $('#updateDesignationBtn').on('click', async function (event) {
        event.preventDefault();
        let editId = $('#editDesignationId').val(), designationName = $('#editDesignationName').val().trim(), departmentId = $('#editDepartmentId').val(), jobDescription = $('#editJobDescription').val().trim();
        if (!designationName || !departmentId || !jobDescription) {
            toastr.error("Please complete all fields.");
            return;
        }
        try {
            let response = await fetch(`/api/designations/update/${editId}`, {
                method: "PUT",
                headers: { "Content-Type": "application/json", "Accept": "application/json", "X-CSRF-TOKEN": csrfToken, "Authorization": `Bearer ${authToken}` },
                body: JSON.stringify({ designation_name: designationName, department_id: departmentId, job_description: jobDescription })
            });
            let data = await response.json();
            response.ok ? toastr.success("Designation updated successfully!") && setTimeout(() => location.reload(), 1500) : toastr.error(data.message || "Update failed.");
        } catch (error) {
            console.error(error);
            toastr.error("Something went wrong.");
        }
    });

    // Delete Designation
    let deleteId = null;
    $('.btn-delete').on('click', function () {
        deleteId = $(this).data("id");
        $('#designationPlaceHolder').text($(this).data("designation_name"));
    });

    $('#confirmDeleteBtn').on('click', function () {
        if (!deleteId) return;
        fetch(`/api/designations/delete/${deleteId}`, {
            method: "DELETE",
            headers: { "X-CSRF-TOKEN": csrfToken, "Accept": "application/json", "Content-Type": "application/json", "Authorization": `Bearer ${authToken}` }
        })
            .then(response => response.ok ? toastr.success("Designation deleted successfully!") && setTimeout(() => location.reload(), 800) : response.json().then(data => toastr.error(data.message || "Error deleting designation.")))
            .catch(error => { console.error(error); toastr.error("Server error."); });
    });
});
