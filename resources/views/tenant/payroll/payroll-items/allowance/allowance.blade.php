<?php $page = 'philhealth'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Payroll Items</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Payroll Items</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="mb-2">
                        <div class="dropdown d-flex align-items-center gap-2 flex-wrap">

                            {{-- Government Redirection Links --}}
                            <div class="d-flex align-items-center gap-2 flex-wrap me-2">
                                <a href="https://www.sss.gov.ph/" target="_blank"
                                    class="btn btn-light border d-flex align-items-center px-3 py-1 shadow-sm"
                                    title="Visit SSS Official Website" rel="noopener">
                                    <span
                                        class="bg-white rounded-circle d-flex align-items-center justify-content-center me-2"
                                        style="width:28px;height:28px;">
                                        <img src="{{ asset('build/img/sss-logo.png') }}" alt="SSS Logo"
                                            style="width:22px;height:22px;">
                                    </span>
                                    <span class="fw-semibold text-dark">SSS</span>
                                </a>
                                <a href="https://www.philhealth.gov.ph/" target="_blank"
                                    class="btn btn-light border d-flex align-items-center px-3 py-1 shadow-sm"
                                    title="Visit PhilHealth Official Website" rel="noopener">
                                    <span
                                        class="bg-white rounded-circle d-flex align-items-center justify-content-center me-2"
                                        style="width:28px;height:28px;">
                                        <img src="{{ asset('build/img/philhealth.jpeg') }}" alt="PhilHealth Logo"
                                            style="width:22px;height:22px;">
                                    </span>
                                    <span class="fw-semibold text-dark">PhilHealth</span>
                                </a>
                                <a href="https://www.pagibigfund.gov.ph/" target="_blank"
                                    class="btn btn-light border d-flex align-items-center px-3 py-1 shadow-sm"
                                    title="Visit Pag-IBIG Fund Official Website" rel="noopener">
                                    <span
                                        class="bg-white rounded-circle d-flex align-items-center justify-content-center me-2"
                                        style="width:28px;height:28px;">
                                        <img src="{{ asset('build/img/pag-ibig.png') }}" alt="Pag-IBIG Logo"
                                            style="width:22px;height:22px;">
                                    </span>
                                    <span class="fw-semibold text-dark">Pag-IBIG</span>
                                </a>
                                <a href="https://www.bir.gov.ph/" target="_blank"
                                    class="btn btn-light border d-flex align-items-center px-3 py-1 shadow-sm"
                                    title="Visit BIR Official Website" rel="noopener">
                                    <span
                                        class="bg-white rounded-circle d-flex align-items-center justify-content-center me-2"
                                        style="width:28px;height:28px;">
                                        <img src="{{ asset('build/img/BIR.png') }}" alt="BIR Logo"
                                            style="width:22px;height:22px;">
                                    </span>
                                    <span class="fw-semibold text-dark">BIR</span>
                                </a>
                            </div>

                            {{-- <div>
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
                            </div> --}}
                        </div>
                    </div>
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gy-2 justify-content-between my-4">
                <div class="payroll-btns">
                    <a href="{{ route('sss-contributionTable') }}" class="btn btn-white  border me-2">SSS
                        Contribution</a>
                    <a href="{{ route('philhealth') }}" class="btn btn-white border me-2">PhilHealth</a>
                    <a href="{{ route('withholding-taxTable') }}" class="btn btn-white border me-2">Withholding Tax</a>
                    <a href="{{ route('ot-table') }}" class="btn btn-white border me-2">OT Table</a>
                    <a href="{{ route('de-minimis-benefits') }}" class="btn btn-white border me-2">De Minimis</a>
                    <a href="{{ route('earnings') }}" class="btn btn-white border me-2">Earnings</a>
                    <a href="{{ route('deductions') }}" class="btn btn-white border me-2">Deductions</a>
                    <a href="{{ route('allowance') }}" class="btn btn-white active border me-2">Allowance</a>
                </div>
                <div class="d-flex gap-2 mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#add_allowance"
                        class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                        Allowance</a>
                    <a href="{{ route('userAllowanceIndex') }}" class="btn btn-secondary d-flex align-items-center">
                        <i class="ti ti-circle-plus me-2"></i>Assign Allowance
                    </a>
                </div>
            </div>

            <!-- /Breadcrumb -->

            <!-- Payroll list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Allowances</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="form-group">
                            <select id="sort_by" name="sort_by" class="select form-select select2" onchange="filter()">
                                <option value="" selected>Sort by</option>
                                <option value="recent">Recently Added</option>
                                <option value="asc">Ascending</option>
                                <option value="desc">Descending</option>
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
                                    <th class="text-center">Name</th>
                                    <th class="text-center">Calculation Basis</th>
                                    <th class="text-center">Amount</th>
                                    <th class="text-center">Taxable</th>
                                    <th class="text-center">Created By</th>
                                    <th class="text-center">Updated By</th>
                                    <th class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="allowanceTableBody">
                                @foreach ($allowances as $allowance)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox"
                                                    id="allowance-{{ $allowance->id }}">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-semibold">{{ $allowance->allowance_name }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark">
                                                @switch($allowance->calculation_basis)
                                                    @case('fixed')
                                                        Fixed Amount
                                                    @break

                                                    @case('per_attended_day')
                                                        Per Attended Day
                                                    @break

                                                    @case('per_attended_hour')
                                                        Per Attended Hour
                                                    @break

                                                    @default
                                                        Unknown Basis
                                                @endswitch
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-semibold">
                                                {{ number_format($allowance->amount, 2) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $allowance->is_taxable ? 'success' : 'danger' }}">
                                                {{ $allowance->is_taxable ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-semibold">
                                                {{ $allowance->creator_name }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-semibold">
                                                {{ $allowance->updater_name }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                {{-- Edit --}}
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#edit_allowance"
                                                    data-id="{{ $allowance->id }}"
                                                    data-allowance-name="{{ $allowance->allowance_name }}"
                                                    data-calculation-basis="{{ $allowance->calculation_basis }}"
                                                    data-amount="{{ $allowance->amount }}"
                                                    data-is-taxable="{{ $allowance->is_taxable ? '1' : '0' }}"
                                                    data-all-employees="{{ $allowance->apply_to_all_employees ? '1' : '0' }}"
                                                    data-description="{{ $allowance->description }}">
                                                    <i class="ti ti-edit"></i>
                                                </a>

                                                {{-- Delete --}}
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_allowance" data-id="{{ $allowance->id }}"
                                                    data-allowance-name="{{ $allowance->allowance_name }}"><i
                                                        class="ti ti-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /Payroll list -->

        </div>
        @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->
    @component('components.modal-popup')
    @endcomponent
@endsection


@push('scripts')

    {{-- Store Script --}}
    <script>
        $(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            $('#addAllowanceForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous error states
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                // Build payload
                let payload = {
                    allowance_name: $('#allowanceName').val().trim(),
                    calculation_basis: $('#allowanceCalculationBasis').val(),
                    amount: $('#allowanceAmount').val().trim(),
                    is_taxable: $('#allowanceIsTaxable').val(),
                    apply_to_all_employees: $('input[name="apply_to_all_employees"]:checked').val() ||
                        0,
                    description: $('#allowanceDescription').val()
                        .trim(),
                };

                $.ajax({
                    url: '/api/payroll/payroll-items/allowance/create',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        $('#addAllowanceForm')[0].reset();
                        $('#add_allowance').modal('hide');

                        toastr.success('Allowance type created successfully.');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                let $input = $('[name="' + field + '"]');
                                $input.addClass('is-invalid');
                                let errHtml = '<div class="invalid-feedback">' +
                                    messages[0] + '</div>';
                                if ($input.next('.select2').length) {
                                    $input.closest('.mb-3').append(errHtml);
                                } else {
                                    $input.after(errHtml);
                                }
                            });
                        } else if (xhr.status === 403) {
                            toastr.error(xhr.responseJSON?.message);
                        } else {
                            toastr.error('An unexpected error occurred. Please try again.');
                            console.error(xhr.responseText);
                        }
                    }
                });
            });
        });
    </script>

    {{-- Edit Script --}}
    <script>
        $(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            let currentAllowanceId = null;

            $('#edit_allowance').on('show.bs.modal', function(event) {
                let button = $(event.relatedTarget);
                currentAllowanceId = button.data('id');

                $('#editAllowanceName').val(button.data('allowance-name'));
                $('#editAllowanceCalculationBasis').val(button.data('calculation-basis'));
                $('#editAllowanceAmount').val(button.data('amount'));
                $('#editAllowanceIsTaxable').val(button.data('is-taxable'));

                if (button.data('all-employees') == '1') {
                    $('#edit_allowance_apply_to_all_yes').prop('checked', true);
                } else {
                    $('#edit_allowance_apply_to_all_no').prop('checked', true);
                }

                $('#editAllowanceDescription').val(button.data('description'));

                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            });

            $('#editAllowanceForm').on('submit', function(e) {
                e.preventDefault();

                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                // JSON payload
                let payload = {
                    allowance_name: $('#editAllowanceName').val().trim(),
                    calculation_basis: $('#editAllowanceCalculationBasis').val(),
                    amount: $('#editAllowanceAmount').val().trim(),
                    is_taxable: $('#editAllowanceIsTaxable').val(),
                    apply_to_all_employees: $('input[name="apply_to_all_employees"]:checked').val() ||
                        0,
                    description: $('#editAllowanceDescription').val().trim(),
                };

                $.ajax({
                    url: '/api/payroll/payroll-items/allowance/update/' + currentAllowanceId,
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {

                        $('#editAllowanceForm')[0].reset();
                        $('#edit_allowance').modal('hide');
                        toastr.success('Allowance updated successfully.');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                let $input = $('[name="' + field + '"]');
                                $input.addClass('is-invalid');
                                let errHtml = '<div class="invalid-feedback">' +
                                    messages[0] + '</div>';
                                if ($input.next('.select2').length) {
                                    $input.closest('.mb-3').append(errHtml);
                                } else {
                                    $input.after(errHtml);
                                }
                            });
                        } else if (xhr.status === 403) {
                            toastr.error(xhr.responseJSON?.message);
                        } else {
                            toastr.error('An unexpected error occurred. Please try again.');
                            console.error(xhr.responseText);
                        }
                    }
                });
            });
        });
    </script>

    {{-- Delete Script --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let deleteId = null;

            const deleteButtons = document.querySelectorAll('.btn-delete');
            const allowanceConfirmBtn = document.getElementById('allowanceConfirmBtn');
            const allowancePlaceHolder = document.getElementById('allowancePlaceHolder');

            // Set up the delete buttons to capture data
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    deleteId = this.getAttribute('data-id');
                    const allowanceName = this.getAttribute('data-allowance-name');

                    if (allowancePlaceHolder) {
                        allowancePlaceHolder.textContent = allowanceName;
                    }
                });
            });

            // Confirm delete button click event
            allowanceConfirmBtn?.addEventListener('click', function() {
                if (!deleteId) return;

                fetch(`/api/payroll/payroll-items/allowance/delete/${deleteId}`, {
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
                            toastr.success("Allowance deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_allowance'));
                            deleteModal.hide(); // Hide the modal

                            setTimeout(() => window.location.reload(),
                                800); // Refresh the page after a short delay
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting allowance.");
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

@endpush
