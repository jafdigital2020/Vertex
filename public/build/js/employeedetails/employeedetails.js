document.addEventListener("DOMContentLoaded", function () {
    let authToken = localStorage.getItem("token");
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

    // ============ Government Details ID =============== //

    // Open modal and set user_id
    document.querySelectorAll(".editGovernmentBtn").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.getAttribute(
                "data-user-id"); // Ensure userId is declared properly

            const sssNumber = this.getAttribute("data-sss-number") || "";
            const philhealthNumber = this.getAttribute("data-philhealth-number") || "";
            const pagibigNumber = this.getAttribute("data-pagibig-number") || "";
            const tinNumber = this.getAttribute("data-tin-number") || "";

            document.getElementById("userId").value = userId;
            document.getElementById("sssNumber").value = sssNumber;
            document.getElementById("philhealthNumber").value = philhealthNumber;
            document.getElementById("pagibigNumber").value = pagibigNumber;
            document.getElementById("tinNumber").value = tinNumber;
        });
    });

    // Handle form submission
    document.getElementById("governmentForm")?.addEventListener("submit", async function (event) {
        event.preventDefault();

        let userId = document.getElementById("userId").value;
        let sssNumber = document.getElementById("sssNumber").value.trim();
        let philhealthNumber = document.getElementById("philhealthNumber").value.trim();
        let pagibigNumber = document.getElementById("pagibigNumber").value.trim();
        let tinNumber = document.getElementById("tinNumber").value.trim();

        if (!userId) {
            toastr.error("User ID is missing.");
            return;
        }

        try {
            let response = await fetch(
                `/api/employees/employee-details/${userId}/government-id`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    sss_number: sssNumber,
                    philhealth_number: philhealthNumber,
                    pagibig_number: pagibigNumber,
                    tin_number: tinNumber,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success(data.message || "Government ID saved successfully!");
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || "Failed to save Government ID.");
            }
        } catch (error) {
            console.error(error);
            toastr.error("Something went wrong. Please try again.");
        }
    });

    // =================== Bank Details ===================== //
    // Open modal and set user_id
    document.querySelectorAll(".editBankDetailsBtn").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.getAttribute(
                "data-user-id"); // Ensure userId is declared properly
            console.log("User ID Retrieved:", userId);

            const accountName = this.getAttribute("data-account-name") || "";
            const accountNumber = this.getAttribute("data-account-number") || "";


            document.getElementById("bankUserId").value = userId;
            document.getElementById("accountName").value = accountName;
            document.getElementById("accountNumber").value = accountNumber;

            const bankId = this.getAttribute("data-bank-id");
            const editBankSelect = document.getElementById("bankId");
            editBankSelect.value = bankId;

            // Force UI update
            editBankSelect.dispatchEvent(new Event('change'));
        });
    });

    // Handle form submission
    document.getElementById("bankForm")?.addEventListener("submit", async function (event) {
        event.preventDefault();

        let userId = document.getElementById("bankUserId").value;
        let bankId = document.getElementById("bankId").value.trim();
        let accountName = document.getElementById("accountName").value.trim();
        let accountNumber = document.getElementById("accountNumber").value.trim();

        if (!userId) {
            toastr.error("User ID is missing.");
            return;
        }

        try {
            let response = await fetch(
                `/api/employees/employee-details/${userId}/bank-details`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    bank_id: bankId,
                    account_name: accountName,
                    account_number: accountNumber,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success(data.message || "Bank details saved successfully!");
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || "Failed to save bank details.");
            }
        } catch (error) {
            console.error(error);
            toastr.error("Something went wrong. Please try again.");
        }
    });

    // ===================== Family Information ========================== //

    // Add a new set of fields
    document.getElementById("addFamilyField").addEventListener("click", function () {
        let container = document.getElementById("familyFieldsContainer");
        let newFieldSet = document.createElement("div");
        newFieldSet.classList.add("row", "family-info");

        // Create new fields with a remove button
        newFieldSet.innerHTML = `
    <div class="col-md-3">
        <div class="mb-3">
            <label class="form-label">Name <span class="text-danger"> *</span></label>
            <input type="text" class="form-control" name="name[]" id="name">
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label class="form-label">Relationship<span class="text-danger"> *</span></label>
            <input type="text" class="form-control" name="relationship[]" id="relationship">
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label class="form-label">Phone </label>
            <input type="text" class="form-control" name="phone_number[]" id="phoneNumber">
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3 position-relative">
            <div class="mb-3">
                <label class="form-label">Date of birth <span class="text-danger"> *</span></label>
                    <input type="date" class="form-control" name="birthdate[]" id="birthdate" placeholder="dd/mm/yyyy">
            </div>
        </div>
    </div>

    <!-- Remove Button -->
    <div class="col-6 mt-2">
        <button type="button" class="btn btn-danger btn-sm mb-3 removeFamilyField">
            <i class="ti ti-x"></i> Remove
        </button>
    </div>
`;

        // Append the new field set
        container.appendChild(newFieldSet);

        // Add functionality to remove the added field set
        newFieldSet.querySelector('.removeFamilyField').addEventListener('click', function () {
            container.removeChild(newFieldSet);
        });
    });


    // Populate User ID
    document.querySelectorAll(".editFamilyInfoBtn").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.getAttribute(
                "data-user-id");

            document.getElementById("familyUserId").value = userId;
        });
    });

    // Handle form submission
    document.getElementById("familyForm")?.addEventListener("submit", async function (event) {
        event.preventDefault();

        let userId = document.getElementById("familyUserId").value;
        let names = Array.from(document.querySelectorAll("input[name='name[]']")).map(input =>
            input.value.trim());
        let relationships = Array.from(document.querySelectorAll(
            "input[name='relationship[]']")).map(input => input.value.trim());
        let phoneNumbers = Array.from(document.querySelectorAll("input[name='phone_number[]']"))
            .map(input => input.value.trim());
        let birthdates = Array.from(document.querySelectorAll("input[name='birthdate[]']")).map(
            input => input.value.trim());

        if (!userId) {
            toastr.error("User ID is missing.");
            return;
        }

        try {
            let response = await fetch(
                `/api/employees/employee-details/${userId}/family-informations`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    user_id: userId,
                    name: names,
                    relationship: relationships,
                    phone_number: phoneNumbers,
                    birthdate: birthdates,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success(data.message || "Family informations saved successfully!");
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || "Failed to save familiy informations.");
            }
        } catch (error) {
            console.error(error);
            toastr.error("Something went wrong. Please try again.");
        }
    });

    // Family Information Edit
    let editId = "";

    // 🌟 1. Populate fields when edit icon is clicked
    document.querySelectorAll('[data-bs-target="#edit_family_information"]').forEach(button => {
        button.addEventListener("click", function () {
            editId = this.getAttribute("data-id");

            document.getElementById("editFamilyId").value = editId;
            document.getElementById("editFamilyUserId").value = this.getAttribute(
                "data-user-id");
            document.getElementById("editName").value = this.getAttribute(
                "data-name");
            document.getElementById("editRelationship").value = this.getAttribute(
                "data-relationship");
            document.getElementById("editPhoneNumber").value = this.getAttribute(
                "data-phone-number");
            document.getElementById("editBirthdate").value = this.getAttribute(
                "data-birthdate");
        });
    });

    // 🌟 2. Handle update button click
    document.getElementById("updateFamilyBtn").addEventListener("click", async function (event) {
        event.preventDefault();

        let userId = document.getElementById("editFamilyUserId").value.trim();
        let editId = document.getElementById("editFamilyId").value.trim();
        let name = document.getElementById("editName").value.trim();
        let relationship = document.getElementById("editRelationship").value.trim();
        let phoneNumber = document.getElementById("editPhoneNumber").value.trim();
        let birthdate = document.getElementById("editBirthdate").value.trim();

        if (name === "" || relationship === "" || phoneNumber === "" ||
            birthdate === "") {
            toastr.error("Please complete all fields.");
            return;
        }

        try {
            let response = await fetch(
                `/api/employees/employee-details/${userId}/family-informations/update/${editId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    user_id: userId,
                    name: name,
                    relationship: relationship,
                    phone_number: phoneNumber,
                    birthdate: birthdate,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success("Family information updated successfully!");
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

    // Family Information Delete
    let deleteId = null;
    let userId = null;

    const deleteButtons = document.querySelectorAll('.btn-delete');
    const familyInfoDeleteBtn = document.getElementById('familyInfoDeleteBtn');
    const familyNamePlaceHolder = document.getElementById('familyNamePlaceHolder');

    // Set up the delete buttons to capture data
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            deleteId = this.getAttribute('data-id');
            userId = this.getAttribute('data-user-id');  // Set userId globally
            const familyName = this.getAttribute('data-name');

            if (familyNamePlaceHolder) {
                familyNamePlaceHolder.textContent = familyName; // Update the modal with the family name
            }
        });
    });

    // Confirm delete button click event
    familyInfoDeleteBtn?.addEventListener('click', function () {
        if (!deleteId || !userId) return; // Ensure both deleteId and userId are available

        fetch(`/api/employees/employee-details/${userId}/family-informations/delete/${deleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute("content"),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`,
            },
        })
            .then(response => {
                if (response.ok) {
                    toastr.success("Family Information deleted successfully.");

                    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('delete_family_information'));
                    deleteModal.hide(); // Hide the modal

                    setTimeout(() => window.location.reload(), 800); // Refresh the page after a short delay
                } else {
                    return response.json().then(data => {
                        toastr.error(data.message || "Error deleting family information.");
                    });
                }
            })
            .catch(error => {
                console.error(error);
                toastr.error("Server error.");
            });
    });

    // =================== Employee Education Details ========================== //
    // Open modal and set user_id
    document.querySelectorAll(".editEducationBtn").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.getAttribute(
                "data-user-id");

            document.getElementById("educationUserId").value = userId;
        });
    });

    // Handle form submission
    document.getElementById("educationForm")?.addEventListener("submit", async function (event) {
        event.preventDefault();

        let userId = document.getElementById("educationUserId").value;
        let institutionName = document.getElementById("institutionName").value.trim();
        let courseOrLevel = document.getElementById("courseOrLevel").value.trim();
        let dateFrom = document.getElementById("dateFrom").value.trim();
        let dateTo = document.getElementById("dateTo").value.trim();

        if (!userId) {
            toastr.error("User ID is missing.");
            return;
        }

        try {
            let response = await fetch(
                `/api/employees/employee-details/${userId}/education-details`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    institution_name: institutionName,
                    course_or_level: courseOrLevel,
                    date_from: dateFrom,
                    date_to: dateTo,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success(data.message || "Education details saved successfully!");
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || "Failed to save education details.");
            }
        } catch (error) {
            console.error(error);
            toastr.error("Something went wrong. Please try again.");
        }
    });

    //Edit Education Details
    let editEducationId = "";

    // 🌟 1. Populate fields when edit icon is clicked
    document.querySelectorAll('[data-bs-target="#edit_education"]').forEach(button => {
        button.addEventListener("click", function () {
            editEducationId = this.getAttribute("data-id");

            document.getElementById("editEducationId").value = editEducationId;
            document.getElementById("editEducationUserId").value = this.getAttribute(
                "data-user-id");
            document.getElementById("editInstitutionName").value = this.getAttribute(
                "data-institution-name");
            document.getElementById("editCourseOrLevel").value = this.getAttribute(
                "data-course-level");
            document.getElementById("editDateFrom").value = this.getAttribute(
                "data-date-from");
            document.getElementById("editDateTo").value = this.getAttribute(
                "data-date-to");
        });
    });

    // 🌟 2. Handle update button click
    document.getElementById("updateEducationBtn").addEventListener("click", async function (event) {
        event.preventDefault();

        let userId = document.getElementById("editEducationUserId").value.trim();
        let educationId = document.getElementById("editEducationId").value.trim();
        let institutionName = document.getElementById("editInstitutionName").value.trim();
        let courseOrLevel = document.getElementById("editCourseOrLevel").value.trim();
        let dateFrom = document.getElementById("editDateFrom").value.trim();
        let dateTo = document.getElementById("editDateTo").value.trim();

        if (institutionName === "" || courseOrLevel === "" || dateFrom === "" ||
            dateTo === "") {
            toastr.error("Please complete all fields.");
            return;
        }

        try {
            let response = await fetch(
                `/api/employees/employee-details/${userId}/education-details/update/${educationId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    user_id: userId,
                    institution_name: institutionName,
                    course_or_level: courseOrLevel,
                    date_from: dateFrom,
                    date_to: dateTo,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success("Education details updated successfully!");
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

    // Delete Education
    let educationDeleteId = null;
    let educationUserId = null;

    const educationDeleteButtons = document.querySelectorAll('.btn-delete');
    const educationDeleteBtn = document.getElementById('educationDeleteBtn');
    const institutionPlaceHolderName = document.getElementById('institutionPlaceHolderName');

    // Set up the delete buttons to capture data
    educationDeleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            educationDeleteId = this.getAttribute('data-id');
            educationUserId = this.getAttribute('data-user-id');
            const institutionName = this.getAttribute('data-institution-name');

            if (institutionPlaceHolderName) {
                institutionPlaceHolderName.textContent = institutionName; // Update the modal with the family name
            }
        });
    });

    // Confirm delete button click event
    educationDeleteBtn?.addEventListener('click', function () {
        if (!educationDeleteId || !educationUserId) return; // Ensure both deleteId and userId are available

        fetch(`/api/employees/employee-details/${educationUserId}/education-details/delete/${educationDeleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute("content"),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`,
            },
        })
            .then(response => {
                if (response.ok) {
                    toastr.success("Education detail deleted successfully.");

                    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('delete_education'));
                    deleteModal.hide(); // Hide the modal

                    setTimeout(() => window.location.reload(), 800); // Refresh the page after a short delay
                } else {
                    return response.json().then(data => {
                        toastr.error(data.message || "Error deleting education detail.");
                    });
                }
            })
            .catch(error => {
                console.error(error);
                toastr.error("Server error.");
            });
    });

    // ================== Employee Experience ====================== //
    document.querySelectorAll(".editExperienceBtn").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.getAttribute(
                "data-user-id");

            document.getElementById("experienceUserId").value = userId;
        });
    });

    // Handle "isPresent" checkbox behavior
    const isPresentCheckbox = document.getElementById("isPresent");
    const dateToField = document.getElementById("experienceDateTo");

    if (isPresentCheckbox && dateToField) {
        // Initial state
        if (isPresentCheckbox.checked) {
            dateToField.disabled = true;
        }

        isPresentCheckbox.addEventListener("change", function () {
            if (this.checked) {
                dateToField.disabled = true;
                dateToField.value = "";
            } else {
                dateToField.disabled = false;
            }
        });
    }

    // Handle form submission
    document.getElementById("experienceForm")?.addEventListener("submit", async function (event) {
        event.preventDefault();

        let userId = document.getElementById("experienceUserId").value;
        let previousCompany = document.getElementById("previousCompany").value.trim();
        let designation = document.getElementById("designation").value.trim();
        let dateFrom = document.getElementById("experienceDateFrom").value.trim();
        let dateTo = document.getElementById("experienceDateTo").value.trim();
        let isPresent = document.getElementById("isPresent").checked ? 1 : 0;

        if (!userId) {
            toastr.error("User ID is missing.");
            return;
        }

        const formData = {
            user_id: userId,
            previous_company: previousCompany,
            designation: designation,
            date_from: dateFrom,
            date_to: dateTo,
            is_present: isPresent
        };

        try {
            let response = await fetch(
                `/api/employees/employee-details/${userId}/experience-details`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify(formData)
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success(data.message || "Experience details saved successfully!");
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || "Failed to save experience details.");
            }
        } catch (error) {
            console.error(error);
            toastr.error("Something went wrong. Please try again.");
        }
    });

    // Edit Experience
    let editExperienceId = "";

    // 🌟 1. Populate fields when edit icon is clicked
    document.querySelectorAll('[data-bs-target="#edit_experience"]').forEach(button => {
        button.addEventListener("click", function () {
            editExperienceId = this.getAttribute("data-id");

            document.getElementById("editExperienceId").value = editExperienceId;
            document.getElementById("editExperienceUserId").value = this.getAttribute(
                "data-user-id");
            document.getElementById("editPreviousCompany").value = this.getAttribute(
                "data-previous-company");
            document.getElementById("editDesignation").value = this.getAttribute(
                "data-designation");
            document.getElementById("editExperienceDateFrom").value = this.getAttribute(
                "data-date-from");
            document.getElementById("editExperienceDateTo").value = this.getAttribute(
                "data-date-to");

            const isPresent = this.getAttribute("data-is-present") == "1";
            const isPresentCheckbox = document.getElementById("editIsPresent");
            const dateToField = document.getElementById("editExperienceDateTo");

            isPresentCheckbox.checked = isPresent;
            dateToField.disabled = isPresent;
        });
    });

    document.getElementById("editIsPresent").addEventListener("change", function () {
        const dateToField = document.getElementById("editExperienceDateTo");
        dateToField.disabled = this.checked;
        if (this.checked) {
            dateToField.value = ""; // clear if currently working
        }
    });

    // 🌟 2. Handle update button click
    document.getElementById("updateExperienceBtn").addEventListener("click", async function (event) {
        event.preventDefault();

        let userId = document.getElementById("editExperienceUserId").value.trim();
        let experienceId = document.getElementById("editExperienceId").value.trim();
        let previousCompany = document.getElementById("editPreviousCompany").value.trim();
        let designation = document.getElementById("editDesignation").value.trim();
        let dateFrom = document.getElementById("editExperienceDateFrom").value.trim();
        let dateTo = document.getElementById("editExperienceDateTo").value.trim();
        let isPresent = document.getElementById("editIsPresent").checked ? 1 : 0;


        if (previousCompany === "" || designation === "" || dateFrom === "") {
            toastr.error("Please complete all fields.");
            return;
        }

        try {
            let response = await fetch(
                `/api/employees/employee-details/${userId}/experience-details/update/${experienceId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    user_id: userId,
                    previous_company: previousCompany,
                    designation: designation,
                    date_from: dateFrom,
                    date_to: dateTo,
                    is_present: isPresent,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success("Education details updated successfully!");
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

    // Experience Delete
    let experienceDeleteId = null;
    let experienceUserId = null;

    const experienceDeleteButtons = document.querySelectorAll('.btn-delete');
    const experienceDeleteBtn = document.getElementById('experienceDeleteBtn');
    const companyPlaceHolderName = document.getElementById('companyPlaceHolderName');

    // Set up the delete buttons to capture data
    experienceDeleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            experienceDeleteId = this.getAttribute('data-id');
            experienceUserId = this.getAttribute('data-user-id');
            const previousCompany = this.getAttribute('data-previous-company');

            if (companyPlaceHolderName) {
                companyPlaceHolderName.textContent = previousCompany; // Update the modal with the family name
            }
        });
    });

    // Confirm delete button click event
    experienceDeleteBtn?.addEventListener('click', function () {
        if (!experienceDeleteId || !experienceUserId) return; // Ensure both deleteId and userId are available

        fetch(`/api/employees/employee-details/${experienceUserId}/experience-details/delete/${experienceDeleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute("content"),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`,
            },
        })
            .then(response => {
                if (response.ok) {
                    toastr.success("Experience detail deleted successfully.");

                    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('delete_experience'));
                    deleteModal.hide(); // Hide the modal

                    setTimeout(() => window.location.reload(), 800); // Refresh the page after a short delay
                } else {
                    return response.json().then(data => {
                        toastr.error(data.message || "Error deleting experience detail.");
                    });
                }
            })
            .catch(error => {
                console.error(error);
                toastr.error("Server error.");
            });
    });

    // =================  Employee Emergency Contact ===================== //
    document.querySelectorAll(".editEmergencyContact").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.getAttribute(
                "data-user-id");
            console.log("User ID Retrieved:", userId);

            const primaryName = this.getAttribute("data-primary-name") || "";
            const primaryRelationship = this.getAttribute("data-primary-relationship") || "";
            const primaryPhoneOne = this.getAttribute("data-primary-phoneone") || "";
            const primaryPhoneTwo = this.getAttribute("data-primary-phonetwo") || "";
            const secondaryName = this.getAttribute("data-secondary-name") || "";
            const secondaryRelationship = this.getAttribute("data-secondary-relationship") || "";
            const secondaryPhoneOne = this.getAttribute("data-secondary-phoneone") || "";
            const secondaryPhoneTwo = this.getAttribute("data-secondary-phonetwo") || "";

            document.getElementById("emergencyContactId").value = userId;
            document.getElementById("primaryName").value = primaryName;
            document.getElementById("primaryRelationship").value = primaryRelationship;
            document.getElementById("primaryPhoneOne").value = primaryPhoneOne;
            document.getElementById("primaryPhoneTwo").value = primaryPhoneTwo;
            document.getElementById("secondaryName").value = secondaryName;
            document.getElementById("secondaryRelationship").value = secondaryRelationship;
            document.getElementById("secondaryPhoneOne").value = secondaryPhoneOne;
            document.getElementById("secondaryPhoneTwo").value = secondaryPhoneTwo;

        });
    });

    // Handle form submission
    document.getElementById("emergencyContactForm")?.addEventListener("submit", async function (event) {
        event.preventDefault();

        let userId = document.getElementById("emergencyContactId").value;
        let primaryName = document.getElementById("primaryName").value.trim();
        let primaryRelationship = document.getElementById("primaryRelationship").value.trim();
        let primaryPhoneOne = document.getElementById("primaryPhoneOne").value.trim();
        let primaryPhoneTwo = document.getElementById("primaryPhoneTwo").value.trim();
        let secondaryName = document.getElementById("secondaryName").value.trim();
        let secondaryRelationship = document.getElementById("secondaryRelationship").value.trim();
        let secondaryPhoneOne = document.getElementById("secondaryPhoneOne").value.trim();
        let secondaryPhoneTwo = document.getElementById("secondaryPhoneTwo").value.trim();

        if (!userId) {
            toastr.error("User ID is missing.");
            return;
        }

        try {
            let response = await fetch(`/api/employees/employee-details/${userId}/emergency-contacts`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    primary_name: primaryName,
                    primary_relationship: primaryRelationship,
                    primary_phone_one: primaryPhoneOne,
                    primary_phone_two: primaryPhoneTwo,
                    secondary_name: secondaryName,
                    secondary_relationship: secondaryRelationship,
                    secondary_phone_one: secondaryPhoneOne,
                    secondary_phone_two: secondaryPhoneTwo,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success(data.message || "Emergency contact saved successfully!");
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || "Failed to save emergency contact.");
            }
        } catch (error) {
            console.error(error);
            toastr.error("Something went wrong. Please try again.");
        }
    });

    // =================== Employee Personal Information ====================== //
    document.querySelectorAll(".editPersonalInformation").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.getAttribute(
                "data-user-id");
            console.log("User ID Retrieved:", userId);

            const nationality = this.getAttribute("data-nationality") || "";
            const religion = this.getAttribute("data-religion") || "";
            const civilStatus = this.getAttribute("data-civil-status") || "";
            const noOfChildren = this.getAttribute("data-no-children") || "";
            const spouseName = this.getAttribute("data-spouse-name") || "";

            document.getElementById("personalInfoUserId").value = userId;
            document.getElementById("nationality").value = nationality;
            document.getElementById("religion").value = religion;
            document.getElementById("civilStatus").value = civilStatus;
            document.getElementById("noOfChildren").value = noOfChildren;
            document.getElementById("spouseName").value = spouseName;
        });
    });

    // Handle form submission
    document.getElementById("personalInformationForm")?.addEventListener("submit", async function (event) {
        event.preventDefault();

        let userId = document.getElementById("personalInfoUserId").value;
        let nationality = document.getElementById("nationality").value.trim();
        let religion = document.getElementById("religion").value.trim();
        let civilStatus = document.getElementById("civilStatus").value.trim();
        let noOfChildren = document.getElementById("noOfChildren").value.trim();
        let spouseName = document.getElementById("spouseName").value.trim();

        if (!userId) {
            toastr.error("User ID is missing.");
            return;
        }

        try {
            let response = await fetch(`/api/employees/employee-details/${userId}/personal-informations`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    nationality: nationality,
                    religion: religion,
                    civil_status: civilStatus,
                    no_of_children: noOfChildren,
                    spouse_name: spouseName,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success(data.message || "Personal information saved successfully!");
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || "Failed to save personal information.");
            }
        } catch (error) {
            console.error(error);
            toastr.error("Something went wrong. Please try again.");
        }
    });

    // =================== Employee Basic Information (Personal Information) ====================== //
    document.querySelectorAll(".ediBasicInformation").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.getAttribute(
                "data-user-id");
            console.log("User ID Retrieved:", userId);

            const phoneNumber = this.getAttribute("data-phone-number") || "";
            const gender = this.getAttribute("data-gender") || "";
            const birthPlace = this.getAttribute("data-birthplace") || "";
            const birthDate = this.getAttribute("data-birthdate") || "";
            const completeAddress = this.getAttribute("data-complete-address") || "";

            document.getElementById("basicInfoUserId").value = userId;
            document.getElementById("basicInforPhoneNumber").value = phoneNumber;
            document.getElementById("birthPlace").value = birthPlace;
            document.getElementById("birthDate").value = birthDate;
            document.getElementById("completeAddress").value = completeAddress;
            document.getElementById("gender").value = gender;
            const genderSelect = document.getElementById("gender");

            // Force UI update
            genderSelect.dispatchEvent(new Event('change'));
        });
    });

    // Handle form submission
    document.getElementById("basicInformationForm")?.addEventListener("submit", async function (event) {
        event.preventDefault();

        let userId = document.getElementById("basicInfoUserId").value;
        let phoneNumber = document.getElementById("basicInforPhoneNumber").value.trim();
        let gender = document.getElementById("gender").value.trim();
        let birthPlace = document.getElementById("birthPlace").value.trim();
        let birthDate = document.getElementById("birthDate").value.trim();
        let completeAddress = document.getElementById("completeAddress").value.trim();

        if (!userId) {
            toastr.error("User ID is missing.");
            return;
        }

        try {
            let response = await fetch(`/api/employees/employee-details/${userId}/basic-informations`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    phone_number: phoneNumber,
                    gender: gender,
                    birth_date: birthDate,
                    birth_place: birthPlace,
                    complete_address: completeAddress,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success(data.message || "Personal information saved successfully!");
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || "Failed to save personal information.");
            }
        } catch (error) {
            console.error(error);
            toastr.error("Something went wrong. Please try again.");
        }
    });

    // ============ Get Branch Data (Branch and Employee) ================= //
    $(document).on("change", ".branch-select", function () {
        let branchId = $(this).val();
        let modal = $(this).closest(".modal");
        let departmentSelect = modal.find(".department-select");
        let employeeSelect = modal.find(".employee-select");

        if (!branchId) {
            departmentSelect.html('<option value="">Select Department</option>');
            employeeSelect.html('<option value="">Select User</option>');
            return;
        }

        $.ajax({
            url: "/api/get-branch-data/" + branchId,
            method: "GET",
            headers: {
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "Authorization": `Bearer ${authToken}`
            },
            success: function (data) {
                let departmentOptions = '<option value="">Select Department</option>';
                let employeeOptions = '<option value="">Select User</option>';

                data.departments.forEach(function (department) {
                    departmentOptions += `<option value="${department.id}">${department.department_name}</option>`;
                });

                data.employees.forEach(function (employee) {
                    if (employee.user && employee.user.personal_information) {
                        employeeOptions += `<option value="${employee.user.id}">${employee.user.personal_information.last_name}, ${employee.user.personal_information.first_name}</option>`;
                    }
                });

                departmentSelect.html(departmentOptions).trigger("change");
                employeeSelect.html(employeeOptions).trigger("change");
            },
            error: function () {
                alert("Failed to fetch departments and employees.");
            }
        });
    });

    // ===================== Department to Designation Select =====================
    $(document).on("change", ".department-select", function () {
        let departmentId = $(this).val();
        let modal = $(this).closest(".modal");
        let designationSelect = modal.find(".designation-select");

        if (!departmentId) {
            designationSelect.html('<option value="">Select Designation</option>');
            return;
        }

        $.ajax({
            url: "/get-designations/" + departmentId,
            method: "GET",
            headers: {
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "Authorization": `Bearer ${authToken}`
            },
            success: function (data) {
                let designationOptions = '<option value="">Select Designation</option>';
                data.forEach(function (designation) {
                    designationOptions += `<option value="${designation.id}">${designation.designation_name}</option>`;
                });

                designationSelect.html(designationOptions).trigger("change");
            },
            error: function () {
                alert("Failed to fetch designations.");
            }
        });
    });

    // ==================== Image Preview For Edit Employee ==================== //
    window.previewDetailsSelectedImage = function (event) {
        const input = event.target;
        const file = input.files?.[0];

        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const previewImage = document.getElementById('detailsPreviewImage');
                if (previewImage) {
                    previewImage.src = e.target.result;
                }
            }
            reader.readAsDataURL(file);
        }
    };

    document.getElementById('detailsCancelImageBtn')?.addEventListener('click', function () {
        const input = document.getElementById('detailsProfileImageInput');
        const previewImage = document.getElementById('detailsPreviewImage');

        if (input) input.value = '';
        if (previewImage) previewImage.src = currentImagePath;
    });

    // ====================== Update Employee ============================== //
    $(document).on('click', '[data-bs-target="#edit_viewemployee"]', function () {
        let button = $(this);
        let modal = $('#edit_viewemployee');

        modal.find('#detailsEmployeeForm').attr('data-id', button.data('user-id'));
        modal.find('#detailsInfoUserId').val(button.data('user-id'));
        modal.find('#detailsFirstName').val(button.data('first-name'));
        modal.find('#detailsLastName').val(button.data('last-name'));
        modal.find('#detailsMiddleName').val(button.data('middle-name'));
        modal.find('#detailsSuffix').val(button.data('suffix'));
        modal.find('#detailsUsername').val(button.data('username'));
        modal.find('#detailsEmail').val(button.data('email'));
        modal.find('#detailsEmployeeId').val(button.data('employee-id'));
        modal.find('#detailsDateHired').val(button.data('date-hired'));
        modal.find('#detailsRoleId').val(button.data('role-id')).trigger('change');
        modal.find('#detailsEmploymentStatus').val(button.data('employment-status')).trigger('change');
        modal.find('#detailsEmploymentType').val(button.data('employment-type')).trigger('change');
        modal.find('#detailsPassword').val('').trigger('change');
        modal.find('#detailsConfirmPassword').val('').trigger('change');
        modal.find('#securityLicenseNumber').val(button.data('security-license'));
        modal.find('#securityLicenseExpiration').val(button.data('security-expiration'));

        // Populate branch, department, designation, and reporting_to
        let branchId = button.data('branch-id');
        modal.find('#detailsBranchId').val(branchId).trigger('change');

        setTimeout(() => {
            let departmentId = button.data('department-id');
            modal.find('#detailsDepartmentId').val(departmentId).trigger('change');

            setTimeout(() => {
                let designationId = button.data('designation-id');
                modal.find('#detailsDesignationId').val(designationId).trigger('change');

                setTimeout(() => {
                    let reportingTo = button.data('reporting-to');
                    modal.find('#detailsReportingTo').val(reportingTo).trigger('change');
                }, 400);
            }, 400);
        }, 400);
    });

    // ===== Handle Form Submission =====
    const detailsForm = document.getElementById("detailsEmployeeForm");
    if (detailsForm) {
        detailsForm.addEventListener("submit", async function (event) {
            event.preventDefault();

            let formData = new FormData(this);
            formData.append('_method', 'PUT');

            let userId = this.getAttribute('data-id');
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
            let authToken = localStorage.getItem("token");

            try {
                let response = await fetch(`/api/employees/employee-details/${userId}/detail-informations`, {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                        "Authorization": `Bearer ${authToken}`,
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: formData
                });

                let data = await response.json();

                if (response.ok) {
                    toastr.success("Employee updated successfully!");
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(data.message || "Update failed.");
                }
            } catch (error) {
                toastr.error("Something went wrong.");
                console.error("Update Error:", error);
            }
        });
    }

    // ================= Salary and Contribution Computation Add/Edit =============== //
    document.querySelectorAll(".editSalaryContribution").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.getAttribute("data-user-id");

            const sssContribution = this.getAttribute("data-sss-contribution") || "";
            const philhealthContribution = this.getAttribute("data-philhealth-contribution") || "";
            const pagibigContribution = this.getAttribute("data-pagibig-contribution") || "";
            const withholdingTax = this.getAttribute("data-withholding-tax") || "";

            const sssContributionOverride = this.getAttribute("data-sss-override") || "";
            const philhealthContributionOverride = this.getAttribute("data-philhealth-override") || "";
            const pagibigContributionOverride = this.getAttribute("data-pagibig-override") || "";
            const withholdingTaxOverride = this.getAttribute("data-withholding-override") || "";

            const workedDaysPerYear = this.getAttribute("data-worked-days") || "";

            const selectSssContribution = document.getElementById("sssContribution");
            const selectPhilhealthContribution = document.getElementById("philhealthContribution");
            const selectPagibigContribution = document.getElementById("pagibigContribution");
            const selectWithholdingTax = document.getElementById("withholdingTax");

            const sssContributionOverrideInput = document.getElementById("sssContributionOverride");
            const philhealthContributionOverrideInput = document.getElementById("philhealthContributionOverride");
            const pagibigContributionOverrideInput = document.getElementById("pagibigContributionOverride");
            const withholdingTaxOverrideInput = document.getElementById("withholdingTaxOverride");

            document.getElementById("salaryUserId").value = userId;
            document.getElementById("workedDaysPerYear").value = workedDaysPerYear;

            selectSssContribution.value = sssContribution;
            selectPhilhealthContribution.value = philhealthContribution;
            selectPagibigContribution.value = pagibigContribution;
            selectWithholdingTax.value = withholdingTax;

            sssContributionOverrideInput.value = sssContributionOverride;
            philhealthContributionOverrideInput.value = philhealthContributionOverride;
            pagibigContributionOverrideInput.value = pagibigContributionOverride;
            withholdingTaxOverrideInput.value = withholdingTaxOverride;

            // Utility function to toggle visibility
            function toggleOverrideField(selectElement, overrideInput) {
                if (selectElement.value === "manual") {
                    overrideInput.type = "text"; // Show the input
                } else {
                    overrideInput.type = "hidden"; // Hide the input
                }
            }

            // Initial toggle state
            toggleOverrideField(selectSssContribution, sssContributionOverrideInput);
            toggleOverrideField(selectPhilhealthContribution, philhealthContributionOverrideInput);
            toggleOverrideField(selectPagibigContribution, pagibigContributionOverrideInput);
            toggleOverrideField(selectWithholdingTax, withholdingTaxOverrideInput);

            // Add change event listeners
            selectSssContribution.addEventListener("change", () => {
                toggleOverrideField(selectSssContribution, sssContributionOverrideInput);
            });

            selectPhilhealthContribution.addEventListener("change", () => {
                toggleOverrideField(selectPhilhealthContribution, philhealthContributionOverrideInput);
            });

            selectPagibigContribution.addEventListener("change", () => {
                toggleOverrideField(selectPagibigContribution, pagibigContributionOverrideInput);
            });

            selectWithholdingTax.addEventListener("change", () => {
                toggleOverrideField(selectWithholdingTax, withholdingTaxOverrideInput);
            });

            // Dispatch change events to reflect current state
            selectSssContribution.dispatchEvent(new Event('change'));
            selectPhilhealthContribution.dispatchEvent(new Event('change'));
            selectPagibigContribution.dispatchEvent(new Event('change'));
            selectWithholdingTax.dispatchEvent(new Event('change'));
        });
    });

    // Handle Form Submission
    document.getElementById("salaryForm")?.addEventListener("submit", async function (event) {
        event.preventDefault();

        let userId = document.getElementById("salaryUserId").value;
        let sssContribution = document.getElementById("sssContribution").value.trim();
        let philhealthContribution = document.getElementById("philhealthContribution").value.trim();
        let pagibigContribution = document.getElementById("pagibigContribution").value.trim();
        let withholdingTax = document.getElementById("withholdingTax").value.trim();
        let sssContributionOverride = document.getElementById("sssContributionOverride").value.trim();
        let philhealthContributionOverride = document.getElementById("philhealthContributionOverride").value.trim();
        let pagibigContributionOverride = document.getElementById("pagibigContributionOverride").value.trim();
        let withholdingTaxOverride = document.getElementById("withholdingTaxOverride").value.trim();
        let workedDaysPerYear = document.getElementById("workedDaysPerYear").value.trim();

        if (!userId) {
            toastr.error("User ID is missing.");
            return;
        }

        try {
            let response = await fetch(
                `/api/employees/employee-details/${userId}/salary-contributions`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Authorization": `Bearer ${authToken}`
                },
                body: JSON.stringify({
                    sss_contribution: sssContribution,
                    philhealth_contribution: philhealthContribution,
                    pagibig_contribution: pagibigContribution,
                    withholding_tax: withholdingTax,
                    sss_contribution_override: sssContributionOverride,
                    philhealth_contribution_override: philhealthContributionOverride,
                    pagibig_contribution_override: pagibigContributionOverride,
                    withholding_tax_override: withholdingTaxOverride,
                    worked_days_per_year: workedDaysPerYear,
                })
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success(data.message || "Contribution computation saved successfully!");
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || "Failed to save contribution computation.");
            }
        } catch (error) {
            console.error(error);
            toastr.error("Something went wrong. Please try again.");
        }
    });

    // ===================== Employee Details Attachment ===================== //
    document.querySelectorAll('.addAttachment').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var userId = btn.getAttribute('data-user-id');
            document.getElementById('attachmentUserId').value = userId;
        });
    });

    const form = document.getElementById('attachmentForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        const formData = new FormData(form);
        const userId = document.getElementById('attachmentUserId').value;

        fetch(`/api/employees/employee-details/${userId}/attachments`, {
            method: 'POST',
            body: formData,
            headers: {
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken,       // Only for web.php routes
                "Authorization": `Bearer ${authToken}` // Only if you're using API authentication
            }
        })
            .then(async response => {
                if (!response.ok) {
                    let data = await response.json();
                    throw new Error(data.message || 'Something went wrong');
                }
                return response.json();
            })
            .then(() => {
                form.reset();
                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('add_attachment'));
                modal.hide();
                toastr.success('Attachment uploaded successfully!');
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(error => {
                toastr.error('Error: ' + error.message);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save';
            });
    });

});
