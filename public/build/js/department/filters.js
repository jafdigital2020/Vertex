document.addEventListener("DOMContentLoaded", function () {
    let filters = {
        branch_id: getUrlParam('branch_id') || "",
        status: getUrlParam('status') || "",
        sort: getUrlParam('sort') || "",
        department_id: getUrlParam('department_id') || "",
        designation_id: getUrlParam('designation_id') || "",
        assignment_type: getUrlParam('assignment_type') || "",
    };

    function getUrlParam(key) {
        return new URLSearchParams(window.location.search).get(key);
    }

    function updateURLParams(params) {
        let query = new URLSearchParams();

        for (let key in params) {
            if (params[key]) { // if value is NOT empty
                query.append(key, params[key]);
            }
        }
        window.location.href = `?${query.toString()}`;
    }

    function handleFilterClick(selector, key) {
        document.querySelectorAll(selector).forEach(item => {
            item.addEventListener("click", function () {
                const value = this.dataset.id || this.dataset.value || "";

                filters[key] = value;

                // Important: Special handling kapag branch_id o department_id
                if (key === 'branch_id') {
                    if (value === "") { // All Branches selected
                        filters.branch_id = "";
                        filters.department_id = "";
                        filters.designation_id = "";
                    } else {
                        filters.branch_id = value;
                        filters.department_id = "";
                        filters.designation_id = "";
                    }
                }

                if (key === 'department_id') {
                    if (value === "") { // All Departments selected
                        filters.department_id = "";
                        filters.designation_id = "";
                    } else {
                        filters.department_id = value;
                        filters.designation_id = "";
                    }
                }

                if (key === 'designation_id') {
                    if (value === "") { // All Designations selected
                        filters.designation_id = "";
                    } else {
                        filters.designation_id = value;
                    }
                }

                updateURLParams(filters);
            });
        });
    }


    handleFilterClick(".status-filter", "status");
    handleFilterClick(".sort-filter", "sort");
    handleFilterClick(".branch-filter", "branch_id");
    handleFilterClick(".department-filter", "department_id");
    handleFilterClick(".designation-filter", "designation_id");
    handleFilterClick(".assignment-type-filter", "assignment_type");

    function fetchDepartments(branchId, selectedDepartmentId = "") {
        if (!branchId) return;

        fetch(`/api/get-branch-data/${branchId}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        })
            .then(response => response.json())
            .then(data => {
                const $deptMenu = document.querySelector("#departmentDropdownToggle").nextElementSibling;
                $deptMenu.innerHTML = `
                <li><a href="javascript:void(0);" class="dropdown-item rounded-1 department-filter" data-id="" data-name="All Departments">All Departments</a></li>
            `;

                if (data.departments && data.departments.length > 0) {
                    data.departments.forEach(dep => {
                        $deptMenu.innerHTML += `
                        <li><a href="javascript:void(0);" class="dropdown-item rounded-1 department-filter" data-id="${dep.id}" data-name="${dep.department_name}">${dep.department_name}</a></li>
                    `;
                    });
                }

                attachDepartmentFilterEvents();

                // Auto set selected department label kung meron
                if (selectedDepartmentId) {
                    let selectedDept = document.querySelector(`.department-filter[data-id="${selectedDepartmentId}"]`);
                    if (selectedDept) {
                        document.getElementById("departmentDropdownToggle").textContent = selectedDept.dataset.name;
                    }
                }
            });
    }

    function fetchDesignations(departmentId, selectedDesignationId = "") {
        if (!departmentId) return;

        fetch(`/api/get-designations/${departmentId}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        })
            .then(response => response.json())
            .then(data => {
                const $desigMenu = document.querySelector("#designationDropdownToggle").nextElementSibling;
                $desigMenu.innerHTML = `
                <li><a href="javascript:void(0);" class="dropdown-item rounded-1 designation-filter" data-id="" data-name="All Designations">All Designations</a></li>
            `;

                if (data && data.length > 0) {
                    data.forEach(des => {
                        $desigMenu.innerHTML += `
                        <li><a href="javascript:void(0);" class="dropdown-item rounded-1 designation-filter" data-id="${des.id}" data-name="${des.designation_name}">${des.designation_name}</a></li>
                    `;
                    });
                }

                attachDesignationFilterEvents();

                // Auto set selected designation label kung meron
                if (selectedDesignationId) {
                    let selectedDesig = document.querySelector(`.designation-filter[data-id="${selectedDesignationId}"]`);
                    if (selectedDesig) {
                        document.getElementById("designationDropdownToggle").textContent = selectedDesig.dataset.name;
                    }
                }
            });
    }

    function attachDepartmentFilterEvents() {
        document.querySelectorAll(".department-filter").forEach(item => {
            item.addEventListener("click", function () {
                filters.department_id = this.dataset.id;
                filters.designation_id = "";
                updateURLParams(filters);
            });
        });
    }

    function attachDesignationFilterEvents() {
        document.querySelectorAll(".designation-filter").forEach(item => {
            item.addEventListener("click", function () {
                filters.designation_id = this.dataset.id;
                updateURLParams(filters);
            });
        });
    }

    // -- Initial Load Logic: if may branch_id or department_id
    if (filters.branch_id) {
        fetchDepartments(filters.branch_id, filters.department_id);
    }
    if (filters.department_id) {
        fetchDesignations(filters.department_id, filters.designation_id);
    }
});
