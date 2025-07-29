document.addEventListener("DOMContentLoaded", function () {

 
    let authToken = localStorage.getItem("token");
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");


    // ================= Add Salary Record ======================== //
    document.querySelectorAll(".addSalaryRecord").forEach(button => {
        button.addEventListener("click", function () {

            const userId = this.getAttribute("data-user-id");
            document.getElementById("salaryRecordUserId").value = userId;

        });
    });

    //Form Submission
    document.getElementById("addSalaryRecordForm")?.addEventListener("submit", async function (event) {
        event.preventDefault();

        let userId = document.getElementById("salaryRecordUserId").value;
        let basicSalary = document.getElementById("basicSalary").value.trim();
        let salaryType = document.getElementById("salaryType").value.trim();
        let effectiveDate = document.getElementById("effectiveDate").value.trim();
        let remarks = document.getElementById("remarks").value.trim();

        if (!userId) {
            toastr.error("User ID is missing.");
            return;
        }

        const confirmation = confirm(
            "This will deactivate the current active salary record if the effective date is today. Do you want to continue?"
        );
        if (!confirmation) {
            toastr.info("Salary record creation cancelled.");
            return;
        }

        try {
            let response = await fetch(
                `/api/employees/employee-details/${userId}/salary-records/create`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    user_id: userId,
                    basic_salary: basicSalary,
                    salary_type: salaryType,
                    effective_date: effectiveDate,
                    remarks: remarks,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success(data.message || "Salary record saved successfully!");
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || "Failed to save salary record.");
            }
        } catch (error) {
            console.error(error);
            toastr.error("Something went wrong. Please try again.");
        }
    });

    // ======================== Edit Salary Record ========================= //
    let editSalaryId = "";

    // 🌟 1. Populate fields when edit icon is clicked
    document.querySelectorAll('[data-bs-target="#edit_salary"]').forEach(button => {
        button.addEventListener("click", function () {
            editSalaryId = this.getAttribute("data-id");

            document.getElementById("editSalaryId").value = editSalaryId;
            document.getElementById("editSalaryRecordUserId").value = this.getAttribute(
                "data-user-id");
            document.getElementById("editBasicSalary").value = this.getAttribute(
                "data-basic-salary");
            document.getElementById("editEffectiveDate").value = this.getAttribute(
                "data-effective-date");
            document.getElementById("editRemarks").value = this.getAttribute(
                "data-remarks");

            const selectSalaryType = this.getAttribute("data-salary-type");
            const selectedSalary = document.getElementById("editSalaryType");
            selectedSalary.value = selectSalaryType;

            // Force UI update
            selectedSalary.dispatchEvent(new Event('change'));
        });
    });

    // 🌟 2. Handle update button click
    document.getElementById("updateSalaryBtn").addEventListener("click", async function (event) {
        event.preventDefault();

        let salaryUserId = document.getElementById("editSalaryRecordUserId").value.trim();
        let editSalaryId = document.getElementById("editSalaryId").value.trim();
        let basicSalary = document.getElementById("editBasicSalary").value.trim();
        let salaryType = document.getElementById("editSalaryType").value.trim();
        let effectiveDate = document.getElementById("editEffectiveDate").value.trim();
        let remarks = document.getElementById("editRemarks").value.trim();

        if (basicSalary === "" || effectiveDate === "" || salaryType === "") {
            toastr.error("Please complete all fields.");
            return;
        }

        try {
            let response = await fetch(
                `/api/employees/employee-details/${salaryUserId}/salary-records/update/${editSalaryId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    user_id: salaryUserId,
                    basic_salary: basicSalary,
                    salary_type: salaryType,
                    effective_date: effectiveDate,
                    remarks: remarks,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success("Salary updated successfully!");
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

    // ==================== Delete Salary Record =================== //
    // Experience Delete
    let salaryDeleteId = null;
    let salaryDeleteUserId = null;

    const salaryDeleteButtons = document.querySelectorAll('.btn-delete');
    const salaryDeleteBtn = document.getElementById('salaryDeleteBtn');

    // Set up the delete buttons to capture data
    salaryDeleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            salaryDeleteId = this.getAttribute('data-id');
            salaryDeleteUserId = this.getAttribute('data-user-id');
        });
    });

    // Confirm delete button click event
    salaryDeleteBtn?.addEventListener('click', function () {
        if (!salaryDeleteId || !salaryDeleteUserId)
            return; // Ensure both deleteId and userId are available

        fetch(`/api/employees/employee-details/${salaryDeleteUserId}/salary-records/delete/${salaryDeleteId}`, {
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
                    toastr.success("Salary record deleted successfully.");

                    const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                        'delete_salary'));
                    deleteModal.hide(); // Hide the modal

                    setTimeout(() => window.location.reload(),
                        800); // Refresh the page after a short delay
                } else {
                    return response.json().then(data => {
                        toastr.error(data.message ||
                            "Error deleting salary record.");
                    });
                }
            })
            .catch(error => {
                console.error(error);
                toastr.error("Server error.");
            });
    });
});
