document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("addDepartmentForm").addEventListener("submit", async function (event) {
        event.preventDefault(); // Prevent form from submitting normally

        let departmentCode = document.getElementById("departmentCode").value.trim();
        let departmentName = document.getElementById("departmentName").value.trim();
        let headOfDepartment = document.getElementById("headOfDepartment").value.trim();
        let branchId = document.getElementById("branchId").value.trim();

        if(branchId === "") {
            toastr.error("Please select a branch.");
            return;
        }

        if (departmentCode === "") {
            toastr.error("Please enter a Department Code.");
            return;
        }

        if (departmentName === "") {
            toastr.error("Please enter a Department Name.");
            return;
        }


        let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
            "content");
        let departmentStoreRoute = document.querySelector('meta[name="department-store-url"]')?.getAttribute("content");
        let authToken = localStorage.getItem("token"); // Get authentication token

        try {
            let response = await fetch(departmentStoreRoute, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken, // CSRF for web
                    "Authorization": `Bearer ${authToken}` // Token for API authentication
                },
                body: JSON.stringify({
                    branch_id: branchId,
                    department_code: departmentCode,
                    department_name: departmentName,
                    head_of_department: headOfDepartment,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success("Department created successfully!");
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                toastr.error(data.message || "Failed to create department.");
            }
        } catch (error) {
            console.error("Error:", error);
            toastr.error("Something went wrong. Please try again.");
        }
    });

    // Edit
    let editId = "";

    // 🌟 1. Populate fields when edit icon is clicked
    document.querySelectorAll('[data-bs-target="#edit_department"]').forEach(button => {
        button.addEventListener("click", function () {
            editId = this.getAttribute("data-id");

            document.getElementById("editDepartmentId").value = editId;
            document.getElementById("editDepartmentCode").value = this.getAttribute(
                "data-department_code");
            document.getElementById("editDepartmentName").value = this.getAttribute(
                "data-department_name");

            // Set and force update for select dropdown
            const headOfDepartmentId = this.getAttribute("data-department_head");
            const select = document.getElementById("editHeadOfDepartment");
            select.value = headOfDepartmentId;

            // 🔁 Force UI update for native <select>
            select.dispatchEvent(new Event('change'));

            // Branch
            const branchId = this.getAttribute("data-branch_id");
            const editBranchSelect = document.getElementById("editBranchId");
            editBranchSelect.value = branchId;

            // Force UI update
            editBranchSelect.dispatchEvent(new Event('change'));

            select2.dispatchEvent(new Event('change'));
        });
    });

    // 🌟 2. Handle update button click
    document.getElementById("updateDepartmentBtn").addEventListener("click", async function (event) {
        event.preventDefault();

        let departmentCode = document.getElementById("editDepartmentCode").value.trim();
        let departmentName = document.getElementById("editDepartmentName").value.trim();
        let headOfDepartment = document.getElementById("editHeadOfDepartment").value.trim();
        let branchId = document.getElementById("editBranchId").value.trim();
        let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
            "content");
        let authToken = localStorage.getItem("token");

        if (departmentCode === "" || departmentName === "" || headOfDepartment === "" || branchId === "") {
            toastr.error("Please complete all fields.");
            return;
        }

        try {
            let response = await fetch(`/api/departments/update/${editId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    department_code: departmentCode,
                    department_name: departmentName,
                    head_of_department: headOfDepartment,
                    branch_id: branchId,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success("Department updated successfully!");
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                toastr.error(data.message || "Update failed.");
            }
        } catch (error) {
            console.error(error);
            toastr.error("Something went wrong.");
        }
    });

    // Delete
    let deleteId = null;
    let authToken = localStorage.getItem("token");

    const deleteButtons = document.querySelectorAll('.btn-delete');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const departmentNamePlaceHolder = document.getElementById('departmentNamePlaceHolder');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            deleteId = this.getAttribute('data-id');
            const departmentName = this.getAttribute('data-department_name');

            if (departmentNamePlaceHolder) {
                departmentNamePlaceHolder.textContent = departmentName;
            }
        });
    });

    confirmDeleteBtn?.addEventListener('click', function () {
        if (!deleteId) return;

        fetch(`/api/departments/delete/${deleteId}`, {
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
                    toastr.success("Department deleted successfully.");

                    const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                        'delete_modal'));
                    deleteModal.hide();

                    setTimeout(() => window.location.reload(), 800);
                } else {
                    return response.json().then(data => {
                        toastr.error(data.message || "Error deleting department.");
                    });
                }
            })
            .catch(error => {
                console.error(error);
                toastr.error("Server error.");
            });
    });
});
