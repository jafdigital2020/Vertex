<?php $page = 'deductions'; ?>
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
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                HR
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Payroll Items</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="mb-2">
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
                    <a href="{{ route('withholding-taxTable') }}" class="btn btn-white border me-2">Withholding Tax</a>
                    <a href="{{ route('ot-table') }}" class="btn btn-white border">OT Table</a>
                    <a href="{{ route('de-minimis-benefits') }}" class="btn btn-white  border">De Minimis</a>
                    <a href="{{ route('earnings') }}" class="btn btn-white border">Earnings</a>
                    <a href="{{ route('deductions') }}" class="btn btn-white active border">Deductions</a>
                </div>
                <div class="d-flex gap-2 mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#add_deduction"
                        class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                        Deduction</a>
                    <a href="{{ route('user-deductions') }}" class="btn btn-secondary d-flex align-items-center">
                        <i class="ti ti-circle-plus me-2"></i>Assign Deduction
                    </a>
                </div>
            </div>

            <!-- /Breadcrumb -->

            <!-- Payroll list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Deduction List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">

                        <div class="dropdown">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                Sort By : Last 7 Days
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Recently Added</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Ascending</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Desending</a>
                                </li>
                            </ul>
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
                                    <th>Name</th>
                                    <th>Calculation Method</th>
                                    <th>Default / Unit Amount</th>
                                    <th>Taxable</th>
                                    <th>Created By</th>
                                    <th>Updated By</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($deductionTypes as $deduction)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <h6 class="fs-14 fw-medium text-gray-9">{{ $deduction->name }}</h6>
                                        </td>
                                        <td>{{ ucfirst($deduction->calculation_method) }}</td>
                                        <td>{{ number_format($deduction->default_amount, 2) }}</td>
                                        <td>{{ $deduction->is_taxable ? 'Yes' : 'No' }}</td>
                                        <td>{{ $deduction->creator_name }}</td>
                                        <td>{{ $deduction->updater_name }}</td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#edit_deduction"
                                                    data-id="{{ $deduction->id }}" data-name="{{ $deduction->name }}"
                                                    data-calculation-method="{{ $deduction->calculation_method }}"
                                                    data-default-amount="{{ $deduction->default_amount }}"
                                                    data-is-taxable="{{ $deduction->is_taxable ? '1' : '0' }}"
                                                    data-all-employees="{{ $deduction->apply_to_all_employees ? '1' : '0' }}"
                                                    data-description="{{ $deduction->description }}">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_deduction" data-id="{{ $deduction->id }}"
                                                    data-name="{{ $deduction->name }}"><i class="ti ti-trash"></i></a>
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

        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2025 &copy; OneJAF Vertex.</p>
            <p>Designed &amp; Developed By <a href="javascript:void(0);" class="text-primary">JAF Digital Group Inc.</a>
            </p>
        </div>

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    {{-- Form Submission Create/Store --}}
    <script>
        $(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            $('#addDeductionForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous error states
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                // Build payload
                let payload = {
                    name: $('#deductionName').val().trim(),
                    calculation_method: $('#deductionCalculationMethod').val(),
                    default_amount: $('#deductionDefaultAmount').val().trim(),
                    is_taxable: $('#deductionIsTaxable').val(),
                    apply_to_all_employees: $('input[name="apply_to_all_employees"]:checked').val() ||
                        0,
                    description: $('#deductionDescription').val()
                        .trim(),
                };

                $.ajax({
                    url: '/api/payroll/payroll-items/deductions/store',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        $('#addDeductionForm')[0].reset();
                        $('#add_deduction').modal('hide');

                        toastr.success('Deduction type created successfully.');
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
                        } else {
                            toastr.error('An unexpected error occurred. Please try again.');
                            console.error(xhr.responseText);
                        }
                    }
                });
            });
        });
    </script>

    {{-- Form Submission Update --}}
    <script>
        $(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            let currentDeductionId = null;

            $('#edit_deduction').on('show.bs.modal', function(event) {
                let button = $(event.relatedTarget);
                currentDeductionId = button.data('id');

                $('#editDeductionName').val(button.data('name'));
                $('#editDeductionCalculationMethod').val(button.data('calculation-method'));
                $('#editDeductionDefaultAmount').val(button.data('default-amount'));
                $('#editDeductionIsTaxable').val(button.data('is-taxable'));

                if (button.data('all-employees') == '1') {
                    $('#editDeduction_apply_to_all_yes').prop('checked', true);
                } else {
                    $('#editDeduction_apply_to_all_no').prop('checked', true);
                }

                $('#editDeductionDescription').val(button.data('description'));

                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            });

            $('#editDeductionForm').on('submit', function(e) {
                e.preventDefault();

                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                // JSON payload
                let payload = {
                    name: $('#editDeductionName').val().trim(),
                    calculation_method: $('#editDeductionCalculationMethod').val(),
                    default_amount: $('#editDeductionDefaultAmount').val().trim(),
                    is_taxable: $('#editDeductionIsTaxable').val(),
                    apply_to_all_employees: $('input[name="apply_to_all_employees"]:checked').val() ||
                        0,
                    description: $('#editDeductionDescription').val().trim(),
                };

                $.ajax({
                    url: '/api/payroll/payroll-items/deductions/update/' + currentDeductionId,
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {

                        $('#editDeductionForm')[0].reset();
                        $('#edit_deduction').modal('hide');
                        toastr.success('Deduction type updated successfully.');
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
                        } else {
                            toastr.error('An unexpected error occurred. Please try again.');
                            console.error(xhr.responseText);
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
            const deductionConfirmBtn = document.getElementById('deductionConfirmBtn');
            const deductionPlaceHolder = document.getElementById('deductionPlaceHolder');

            // Set up the delete buttons to capture data
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    deleteId = this.getAttribute('data-id');
                    const deductionName = this.getAttribute('data-name');

                    if (deductionPlaceHolder) {
                        deductionPlaceHolder.textContent = deductionName;
                    }
                });
            });

            // Confirm delete button click event
            deductionConfirmBtn?.addEventListener('click', function() {
                if (!deleteId) return;

                fetch(`/api/payroll/payroll-items/deductions/delete/${deleteId}`, {
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
                            toastr.success("Deduction type deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_deduction'));
                            deleteModal.hide(); // Hide the modal

                            setTimeout(() => window.location.reload(),
                                800); // Refresh the page after a short delay
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting deduction type.");
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
