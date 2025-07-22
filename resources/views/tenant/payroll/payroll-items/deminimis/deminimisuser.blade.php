<?php $page = 'deminimis-user'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee's Deminimis</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Payroll Items
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee's Deminimis</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if(in_array('Export',$permission))
                    <div class="me-2 mb-2">
                        <div class="dropdown">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                <i class="ti ti-file-export me-1"></i>Export
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                            class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                            class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endif
                    @if(in_array('Create',$permission))
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_deminimis_user"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Assign
                            Deminimis</a>
                    </div>
                    @endif
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Employee's Deminimis</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter" oninput="filter()">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group me-2">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select" onchange="filter()">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select" onchange="filter()">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select" onchange="filter()">
                                <option value="" selected>All Designations</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                @endforeach
                            </select>
                        </div> 
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select" onchange="filter()">
                                <option value="" selected>All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option> 
                            </select>
                        </div>   
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Employee</th>
                                    <th class="text-center">Deminimis</th>
                                    <th class="text-center" >Amount</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">Taxable Excess</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Created By</th>
                                    <th class="text-center">Edited By</th>
                                    @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                    <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="employeeDeminimisTableBody">
                                @foreach ($userDeminimis as $deminimis)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>{{ $deminimis->user->personalInformation->first_name }}
                                            {{ $deminimis->user->personalInformation->last_name }}</td>
                                        <td class="text-center">{{ ucwords(str_replace('_', ' ', $deminimis->deminimisBenefit->name)) }}</td>
                                        <td  class="text-center">{{ $deminimis->amount }}</td>
                                        <td  class="text-center">{{ $deminimis->benefit_date }}</td>
                                        <td  class="text-center">{{ $deminimis->taxable_excess }}</td>
                                        <td  class="text-center">
                                            <span
                                                class="badge d-inline-flex align-items-center badge-xs
                                                {{ $deminimis->status === 'inactive' ? 'badge-danger' : 'badge-success' }}">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($deminimis->status) }}
                                            </span>
                                        </td>
                                        <td  class="text-center">{{ $deminimis->creator_name }}</td>
                                        <td  class="text-center">{{ $deminimis->updater_name }}</td>
                                         @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                        <td  class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                @if(in_array('Update',$permission))
                                                <a href="#" data-bs-toggle="modal" data-id="{{ $deminimis->id }}"
                                                    data-deminimis-id="{{ $deminimis->deminimis_benefit_id }}"
                                                    data-amount="{{ $deminimis->amount }}"
                                                    data-benefit-date="{{ $deminimis->benefit_date }}"
                                                    data-taxable-excess="{{ $deminimis->taxable_excess }}"
                                                    data-status="{{ $deminimis->status }}"
                                                    data-bs-target="#edit_deminimis_user">
                                                    <i class="ti ti-edit" title="Edit"></i>
                                                </a>
                                                @endif
                                                 @if(in_array('Delete',$permission))
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-id="{{ $deminimis->id }}"
                                                    data-deminimis-name="{{ $deminimis->deminimisBenefit->name }}"
                                                    data-bs-target="#delete_deminimis_user">
                                                    <i class="ti ti-trash" title="Delete"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup', [
        'deMinimis' => $deMinimis,
        'branches' => $branches,
        'departments' => $departments,
        'designations' => $designations,
    ])
    @endcomponent
@endsection

@push('scripts')

 <script>  

    $('#dateRange_filter').on('apply.daterangepicker', function(ev, picker) { 
        filter();
    }); 
    function filter() {
        const dateRange = $('#dateRange_filter').val();  
        var branch = $('#branch_filter').val();
        var department = $('#department_filter').val();
        var designation = $('#designation_filter').val();
        const status = $('#status_filter').val(); 
        $.ajax({
            url: '{{ route('de-minimis-user-filter') }}',
            type: 'GET',
            data: { 
                dateRange,
                status,
                branch,
                department,
                designation
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#employeeDeminimisTableBody').html(response.html);
                } else {
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
 
    </script>

    {{-- Filter --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const authToken = localStorage.getItem('token');

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
            $(document).on('change', '.branch-select', function() {
                const $this = $(this);
                if (handleSelectAll($this)) return;

                const branchIds = $this.val() || [];
                const modal = $this.closest('.modal');
                const depSel = modal.find('.department-select');
                const desSel = modal.find('.designation-select');
                const empSel = modal.find('.employee-select');

                // reset downstream
                depSel.html('<option value="">All Department</option>').trigger('change');
                desSel.html('<option value="">All Designation</option>').trigger('change');
                empSel.html('<option value="">All Employee</option>').trigger('change');
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

                    },
                    error() {
                        alert('Failed to fetch branch data.');
                    }
                });
            });

            // — Department change → fetch Designations & re-filter Employees
            $(document).on('change', '.department-select', function() {
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
            $(document).on('change', '.designation-select', function() {
                const $this = $(this);
                if (handleSelectAll($this)) return;
                updateEmployeeSelect($this.closest('.modal'));
            });

            // — Employee “All Employee” handler
            $(document).on('change', '.employee-select', function() {
                handleSelectAll($(this));
            });

        });
    </script>

    {{-- Form Submission Store --}}
    <script>
        $(document).ready(function() {
            let limitAmount = 0;
            $('#deminimisBenefitId').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                limitAmount = parseFloat(selectedOption.data('limit')) || 0;
                recalcTaxableExcess();
            });
            $('#amount').on('input', recalcTaxableExcess);

            function recalcTaxableExcess() {
                const entered = parseFloat($('#amount').val()) || 0;
                const excess = entered > limitAmount ? (entered - limitAmount) : 0;
                $('#taxable_excess').val(excess.toFixed(2));
            }

            $('#assignDeminimisUserForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: '/api/payroll/payroll-items/de-minimis-user/assign',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        toastr.success('Deminimis assigned successfully!');
                        $('#add_deminimis_user').modal('hide');
                        filter();
                    },
                 error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;

                        let errorList = '';
                        Object.values(errors).forEach(msgArray => {
                            msgArray.forEach(msg => {
                                errorList += '• ' + msg + '<br>';
                            });
                        });

                        toastr.error(
                            '<strong>Could not assign deminimis. Please correct the following:</strong><br>' +
                            errorList,
                            'Validation Error', {
                                timeOut: 8000,
                                extendedTimeOut: 4000,
                                closeButton: true,
                                escapeHtml: false
                            }
                        );
                    } else if (xhr.status === 403 && xhr.responseJSON && xhr.responseJSON.message) { 
                        toastr.error( xhr.responseJSON.message );
                    } else {
                        toastr.error(
                            'An unexpected error occurred. Please try again later.',
                            'Error', {
                                timeOut: 5000,
                                closeButton: true
                            }
                        );
                    }
                }

                });
            });
        });
    </script>

    {{-- Form Submission Edit/Update --}}
    <script>
        $(document).ready(function() {
            $('#edit_deminimis_user').on('show.bs.modal', function(event) {
                const trigger = $(event.relatedTarget);

                // Read data-* attributes from the triggering <a>
                const recordId = trigger.data('id'); // deminimis.id
                const benefitId = trigger.data('deminimis-id'); // deminimis_benefit_id
                const amountValue = trigger.data('amount'); // amount
                const benefitDate = trigger.data('benefit-date'); // benefit_date
                const taxableExcess = trigger.data('taxable-excess'); // taxable_excess
                const statusValue = trigger.data('status'); // status

                // Set hidden ID
                $('#deminimiId').val(recordId);

                // Populate the “Deminimis” <select>
                $('#editDeminimisId').val(benefitId)
                $('#editDeminimisId').trigger('change');

                // Populate Amount, Benefit Date, Taxable Excess, Status
                $('#editDeminimisAmount').val(amountValue);
                $('#editDeminimisBenefitDate').val(benefitDate);
                $('#editDeminimisTaxableExcess').val(taxableExcess);
                $('#editDeminimisStatus').val(statusValue);
            });

            // 2. Recalculate “Taxable Excess” in real time inside the Edit modal
            let editLimitAmount = 0;

            // When the user changes the “Deminimis” selection:
            $('#editDeminimisId').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                editLimitAmount = parseFloat(selectedOption.data('limit')) || 0;
                recalcEditTaxableExcess();
            });

            $('#editDeminimisAmount').on('input', recalcEditTaxableExcess);

            function recalcEditTaxableExcess() {
                const entered = parseFloat($('#editDeminimisAmount').val()) || 0;
                const excess = (entered > editLimitAmount) ? (entered - editLimitAmount) : 0;
                $('#editDeminimisTaxableExcess').val(excess.toFixed(2));
            }

            $('#editDeminimisUserForm').on('submit', function(e) {
                e.preventDefault();

                const recordId = $('#deminimiId').val();
                if (!recordId) {
                    toastr.error('Record ID is missing.', 'Error');
                    return;
                }

                // Gather form data into a JS object (we send JSON for PUT)
                const payload = {
                    deminimis_benefit_id: $('#editDeminimisId').val(),
                    amount: $('#editDeminimisAmount').val(),
                    benefit_date: $('#editDeminimisBenefitDate').val(),
                    taxable_excess: $('#editDeminimisTaxableExcess').val(),
                    status: $('#editDeminimisStatus').val()
                };

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                });

                $.ajax({
                    url: `/api/payroll/payroll-items/de-minimis-user/update/${recordId}`,
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    success: function(response) {
                        toastr.success('Deminimis record updated successfully!');
                        $('#edit_deminimis_user').modal('hide');
                        filter();
                    },
                    error: function(xhr) {
                           if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorList = '';
                                Object.values(errors).forEach(msgArray => {
                                    msgArray.forEach(msg => {
                                        errorList += '• ' + msg + '<br>';
                                    });
                                });
                                toastr.error(
                                    '<strong>Could not update record. Please correct:</strong><br>' +
                                    errorList,
                                    'Validation Error', {
                                        timeOut: 8000,
                                        extendedTimeOut: 4000,
                                        escapeHtml: false
                                    }
                                );
                            } else if (xhr.status === 403) {
                                toastr.error(xhr.responseJSON?.message);
                            } else {
                                toastr.error(
                                    'An unexpected error occurred. Please try again later.',
                                    'Error'
                                );
                            }
                    }
                });
            });
        });
    </script>

    {{-- Delete Confirmation --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let deleteId = null;

            const deleteButtons = document.querySelectorAll('.btn-delete');
            const deminimisConfirmBtn = document.getElementById('deminimisConfirmBtn');
            const deminimisPlaceHolder = document.getElementById('deminimisPlaceHolder');

            // Set up the delete buttons to capture data
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    deleteId = this.getAttribute('data-id');
                    const deminimisName = this.getAttribute('data-deminimis-name');

                    if (deminimisPlaceHolder) {
                        deminimisPlaceHolder.textContent =
                        deminimisName; // Update the modal with the deminimis name
                    }
                });
            });

            // Confirm delete button click event
            deminimisConfirmBtn?.addEventListener('click', function() {
                if (!deleteId) return;

                fetch(`/api/payroll/payroll-items/de-minimis-user/delete/${deleteId}`, {
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
                            toastr.success("Deminimis record deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_deminimis_user'));
                            deleteModal.hide(); // Hide the modal
                            filter();
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting deminimis record.");
                            });
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        toastr.error("Server error.");
                    });
            });
        });
    </script>
      <script>
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
    </script>
@endpush
