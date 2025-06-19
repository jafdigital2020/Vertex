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

});
