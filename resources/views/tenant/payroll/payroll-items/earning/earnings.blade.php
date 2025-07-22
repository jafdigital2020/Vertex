<?php $page = 'earnings'; ?>
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
                               Payroll
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Payroll Items</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if(in_array('Export',$permission))
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
                    @endif
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
                    <a href="{{ route('earnings') }}" class="btn btn-white active border me-2">Earnings</a>
                    <a href="{{ route('deductions') }}" class="btn btn-white border me-2">Deductions</a>
                    <a href="{{ route('allowance') }}" class="btn btn-white border me-2">Allowance</a>
                </div>
                <div class="d-flex gap-2 mb-2">
                    @if(in_array('Create',$permission))
                    <a href="#" data-bs-toggle="modal" data-bs-target="#add_earning"
                        class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                        Earning</a>
                    <a href="{{ route('user-earnings') }}" class="btn btn-secondary d-flex align-items-center">
                        <i class="ti ti-circle-plus me-2"></i>Assign Earning
                    </a>
                    @endif
                </div>
            </div>

            <!-- /Breadcrumb -->

            <!-- Payroll list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Earning List</h5>
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
                                    <th>Name</th>
                                    <th class="text-center">Calculation Method</th>
                                    <th class="text-center">Default / Unit Amount</th>
                                    <th class="text-center">Taxable</th>
                                    <th class="text-center">Created By</th>
                                    <th class="text-center">Updated By</th>
                                    @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                    <th class="text-center" >Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="earningTableBody">
                                @foreach ($earningTypes as $earning)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <h6 class="fs-14 fw-medium text-gray-9">{{ $earning->name }}</h6>
                                        </td>
                                        <td class="text-center">{{ ucfirst($earning->calculation_method) }}</td>
                                        <td class="text-center">{{ number_format($earning->default_amount, 2) }}</td>
                                        <td class="text-center">{{ $earning->is_taxable ? 'Yes' : 'No' }}</td>
                                        <td class="text-center">{{ $earning->creator_name }}</td>
                                        <td class="text-center">{{ $earning->updater_name }}</td>
                                        @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                        <td class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                @if(in_array('Update',$permission))
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#edit_earning"
                                                    data-id="{{ $earning->id }}" data-name="{{ $earning->name }}"
                                                    data-calculation-method="{{ $earning->calculation_method }}"
                                                    data-default-amount="{{ $earning->default_amount }}"
                                                    data-is-taxable="{{ $earning->is_taxable ? '1' : '0' }}"
                                                    data-all-employees="{{ $earning->apply_to_all_employees ? '1' : '0' }}"
                                                    data-description="{{ $earning->description }}">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                @endif
                                                @if(in_array('Delete',$permission))
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_earning" data-id="{{ $earning->id }}"
                                                    data-name="{{ $earning->name }}"><i class="ti ti-trash"></i></a>
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
            <!-- /Payroll list -->

        </div>

        @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    {{-- Form Submission Create/Store --}}
     <script>
            function filter(){
                var sort_by = $('#sort_by').val();
                $.ajax({
                    url: '{{ route('earnings-filter') }}',
                    type: 'GET',
                    data: {
                        sort_by: sort_by
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#earningTableBody').html(response.html);
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
    <script>
        $(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            $('#addEarningForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous error states
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                // Build payload
                let payload = {
                    name: $('#earningName').val().trim(),
                    calculation_method: $('#earningCalculationMethod').val(),
                    default_amount: $('#earningDefaultAmount').val().trim(),
                    is_taxable: $('#earningIsTaxable').val(),
                    apply_to_all_employees: $('input[name="apply_to_all_employees"]:checked').val() ||
                        0,
                    description: $('#earningDescription').val()
                        .trim(),
                };

                $.ajax({
                    url: '/api/payroll/payroll-items/earnings/store',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        $('#addEarningForm')[0].reset();
                        $('#addEarningModal').modal('hide');

                        toastr.success('Earning type created successfully.');
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
                                let errHtml = '<div class="invalid-feedback">' + messages[0] + '</div>';
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

    {{-- Form Submission Update --}}
    <script>
        $(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            let currentEarningId = null;

            $('#edit_earning').on('show.bs.modal', function(event) {
                let button = $(event.relatedTarget);
                currentEarningId = button.data('id');

                $('#editEarningName').val(button.data('name'));
                $('#editEarningCalculationMethod').val(button.data('calculation-method'));
                $('#editEarningDefaultAmount').val(button.data('default-amount'));
                $('#editEarningIsTaxable').val(button.data('is-taxable'));

                if (button.data('all-employees') == '1') {
                    $('#edit_apply_to_all_yes').prop('checked', true);
                } else {
                    $('#edit_apply_to_all_no').prop('checked', true);
                }

                $('#editEarningDescription').val(button.data('description'));

                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            });

            $('#editEarningForm').on('submit', function(e) {
                e.preventDefault();

                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                // JSON payload
                let payload = {
                    name: $('#editEarningName').val().trim(),
                    calculation_method: $('#editEarningCalculationMethod').val(),
                    default_amount: $('#editEarningDefaultAmount').val().trim(),
                    is_taxable: $('#editEarningIsTaxable').val(),
                    apply_to_all_employees: $('input[name="apply_to_all_employees"]:checked').val() ||
                        0,
                    description: $('#editEarningDescription').val().trim(),
                };

                $.ajax({
                    url: '/api/payroll/payroll-items/earnings/update/' + currentEarningId,
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {

                        $('#editEarningForm')[0].reset();
                        $('#edit_earning').modal('hide');
                        toastr.success('Earning type updated successfully.');
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

    {{-- Delete Confirmation --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let deleteId = null;

            const deleteButtons = document.querySelectorAll('.btn-delete');
            const earningConfirmBtn = document.getElementById('earningConfirmBtn');
            const earningPlaceHolder = document.getElementById('earningPlaceHolder');

            // Set up the delete buttons to capture data
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    deleteId = this.getAttribute('data-id');
                    const earningName = this.getAttribute('data-name');

                    if (earningPlaceHolder) {
                        earningPlaceHolder.textContent = earningName;
                    }
                });
            });

            // Confirm delete button click event
            earningConfirmBtn?.addEventListener('click', function() {
                if (!deleteId) return;

                fetch(`/api/payroll/payroll-items/earnings/delete/${deleteId}`, {
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
                            toastr.success("Earning type deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_earning'));
                            deleteModal.hide(); // Hide the modal

                            setTimeout(() => window.location.reload(),
                                800); // Refresh the page after a short delay
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting earning type.");
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
