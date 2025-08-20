<?php $page = 'employees'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Salary Bond</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item"> {{ $user->personalInformation->first_name }}'s
                                Salary Bond

                            </li>
                            <input type="hidden" id="userID" value="{{ $user->id }}">
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
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
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                            class="ti ti-file-type-xls me-1"></i>Download Template</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_bond"
                            data-user-id="{{ $user->id }}"
                            class="btn btn-primary d-flex align-items-center addSalaryBond">
                            <i class="ti ti-circle-plus me-2"></i>Add Salary Bond
                        </a>
                    </div>
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="payroll-btns mb-3">
                <a href="{{ route('salaryRecord', $user->id) }}" class="btn btn-white  border me-2">Salary Record</a>
                <a href="{{ route('salaryBond', $user->id) }}" class="btn btn-white active  border me-2">Salary Bond</a>
                <a href="{{ route('adminRequestAttendance') }}" class="btn btn-white border me-2">Employee Allowances</a>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Salary Bond</h5>

                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange-filtered"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter" onchange="filter()">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group me-2">
                            <select name="salaryType_filter" id="salaryType_filter" class="select2 form-select"
                                onchange="filter()">
                                <option value="" selected>All Salary Types</option>
                                <option value="monthly_fixed">Monthly Fixed</option>
                                <option value="daily_rate">Daily Rate</option>
                                <option value="hourly_rate">Hourly Rate</option>
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select" onchange="filter()">
                                <option value="" selected>All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
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
                                    <th>Date Issued</th>
                                    <th>Amount</th>
                                    <th>Payable In</th>
                                    <th>Payable Amount</th>
                                    <th>Remaining Amount</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($user->salaryBonds as $sb)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($sb->date_issued)->format('F j, Y') }}</td>
                                        <td>
                                            <span class="fw-semibold text-dark">
                                                {{ $sb->amount ? '₱' . number_format($sb->amount, 2) : '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">
                                                {{ $sb->payable_in ? $sb->payable_in . ' ' . Str::plural('Cutoff', $sb->payable_in) : 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">
                                                {{ $sb->payable_amount ? '₱' . number_format($sb->payable_amount, 2) : '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-danger">
                                                {{ $sb->remaining_amount ? '₱' . number_format($sb->remaining_amount, 2) : '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge d-inline-flex align-items-center badge-xs
                                                @if ($sb->status == 'completed') badge-success
                                                @elseif($sb->status == 'pending') badge-warning
                                                @elseif($sb->status == 'claimed') badge-info
                                                @elseif($sb->status == 'canceled') badge-danger
                                                @else badge-secondary @endif">
                                                <i class="ti ti-point-filled me-1"></i>
                                                {{ ucfirst($sb->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">
                                                {{ $sb->remarks ?: '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_bond" data-id="{{ $sb->id }}"
                                                    data-user-id="{{ $sb->user_id }}" data-amount="{{ $sb->amount }}"
                                                    data-payable-in="{{ $sb->payable_in }}"
                                                    data-payable-amount="{{ $sb->payable_amount }}"
                                                    data-remaining-amount="{{ $sb->remaining_amount }}"
                                                    data-date-completed="{{ $sb->date_completed }}"
                                                    data-date-issued="{{ $sb->date_issued }}"
                                                    data-remarks="{{ $sb->remarks }}">
                                                    <i class="ti ti-edit" title="Edit"></i></a>
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_bond" data-id="{{ $sb->id }}"
                                                    data-user-id="{{ $sb->user_id }}">
                                                    <i class="ti ti-trash" title="Delete"></i>
                                                </a>
                                            </div>
                                        </td>
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
    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script src="{{ asset('build/js/employeedetails/salary/salary.js') }}"></script>

    {{-- Add Salary Bond --}}
    <script>
        $(document).ready(function() {
            function calculatePayableAmount() {
                const amount = parseFloat($('#salaryBondAmount').val()) || 0;
                const payableIn = parseFloat($('#salaryBondPayableIn').val()) || 0;

                if (amount > 0 && payableIn > 0) {
                    const payableAmount = amount / payableIn;
                    $('#salaryBondPayableAmount').val(payableAmount.toFixed(2));
                } else {
                    $('#salaryBondPayableAmount').val('');
                }
            }

            $('#salaryBondAmount, #salaryBondPayableIn').on('input keyup change', function() {
                calculatePayableAmount();
            });

            // Form submission
            $('#addSalaryBondForm').on('submit', function(e) {
                e.preventDefault();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
                    }
                });

                $.ajax({
                    url: "{{ route('api.addSalaryBond', $user->id) }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('.btn-submit').prop('disabled', true).text('Saving...');
                    },
                    success: function(response) {
                        $('#add_bond').modal('hide');
                        toastr.success(response.message || 'Salary bond added successfully');
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    },
                    error: function(xhr) {
                        console.log('Error response:', xhr);
                        console.log('Response JSON:', xhr.responseJSON);

                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            Object.keys(errors).forEach(key => {
                                toastr.error(errors[key][0]);
                            });
                        } else {
                            toastr.error('An error occurred while saving');
                        }
                    },
                    complete: function() {
                        $('.btn-submit').prop('disabled', false).text('Save');
                    }
                });
            });

            // Clear form when modal is closed
            $('#add_bond').on('hidden.bs.modal', function() {
                $('#addSalaryBondForm')[0].reset();
                $('#salaryBondPayableAmount').val('');
            });
        });
    </script>

    {{-- Edit Salary Bond --}}
    <script>
        $(document).ready(function() {
            // Calculate payable amount for edit form
            function calculateEditPayableAmount() {
                const amount = parseFloat($('#editSalaryBondAmount').val()) || 0;
                const payableIn = parseFloat($('#editSalaryBondPayableIn').val()) || 0;

                if (amount > 0 && payableIn > 0) {
                    const payableAmount = amount / payableIn;
                    $('#editSalaryBondPayableAmount').val(payableAmount.toFixed(2));
                } else {
                    $('#editSalaryBondPayableAmount').val('');
                }
            }

            $('#editSalaryBondAmount, #editSalaryBondPayableIn').on('input keyup change', function() {
                calculateEditPayableAmount();
            });

            // Populate form when edit modal is opened
            $(document).on('click', '[data-bs-target="#edit_bond"]', function(e) {
                e.preventDefault();

                const id = $(this).data('id');
                const userId = $(this).data('user-id');
                const amount = $(this).data('amount');
                const payableIn = $(this).data('payable-in');
                const payableAmount = $(this).data('payable-amount');
                const remainingAmount = $(this).data('remaining-amount');
                const dateCompleted = $(this).data('date-completed');
                const dateIssued = $(this).data('date-issued');
                const remarks = $(this).data('remarks');

                console.log('Populating form with:', {
                    id,
                    userId,
                    amount,
                    payableIn,
                    payableAmount,
                    remainingAmount,
                    dateCompleted,
                    dateIssued,
                    remarks
                });

                // Populate form fields
                $('#editSalaryBondId').val(id);
                $('#editSalaryBondUserId').val(userId);
                $('#editSalaryBondAmount').val(amount);
                $('#editSalaryBondPayableIn').val(payableIn);
                $('#editSalaryBondPayableAmount').val(payableAmount);
                $('#editSalaryBondDateIssued').val(dateIssued);
                $('#editSalaryBondRemarks').val(remarks);

                // Set status based on remaining amount or other logic
                if (remainingAmount <= 0) {
                    $('#editSalaryBondStatus').val('completed');
                } else {
                    $('#editSalaryBondStatus').val('pending');
                }

                // Trigger select2 refresh if using select2
                $('#editSalaryBondStatus').trigger('change');
            });

            // Edit form submission
            $('#edit_bond form').on('submit', function(e) {
                e.preventDefault();

                const salaryBondId = $('#editSalaryBondId').val();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
                    }
                });

                $.ajax({
                    url: "{{ route('api.editSalaryBond', ['userId' => $user->id, 'salaryBondId' => '__SALARY_BOND_ID__']) }}"
                        .replace('__SALARY_BOND_ID__', salaryBondId),
                    method: 'PUT',
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('#salaryBondConfirmBtn').prop('disabled', true).text('Saving...');
                    },
                    success: function(response) {
                        $('#edit_bond').modal('hide');
                        toastr.success(response.message || 'Salary bond updated successfully');
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    },
                    error: function(xhr) {
                        console.log('Error response:', xhr);
                        console.log('Response JSON:', xhr.responseJSON);

                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            Object.keys(errors).forEach(key => {
                                toastr.error(errors[key][0]);
                            });
                        } else {
                            toastr.error('An error occurred while updating');
                        }
                    },
                    complete: function() {
                        $('#salaryBondConfirmBtn').prop('disabled', false).text('Save Changes');
                    }
                });
            });

            // Clear form when edit modal is closed
            $('#edit_bond').on('hidden.bs.modal', function() {
                $(this).find('form')[0].reset();
                $('#editSalaryBondPayableAmount').val('');
            });
        });
    </script>

    {{-- Delete Salary Bond --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
            let salaryBondDeleteId = null;
            let salaryBondDeleteUserId = null;

            const salaryBondDeleteButtons = document.querySelectorAll('.btn-delete');
            const salaryBondDeleteBtn = document.getElementById('salaryBondDeleteBtn');

            // Set up the delete buttons to capture data
            salaryBondDeleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    salaryBondDeleteId = this.getAttribute('data-id');
                    salaryBondDeleteUserId = this.getAttribute('data-user-id');
                });
            });

            // Confirm delete button click event
            salaryBondDeleteBtn?.addEventListener('click', function() {
                if (!salaryBondDeleteId || !salaryBondDeleteUserId)
                    return; // Ensure both deleteId and userId are available

                fetch(`/api/employees/employee-details/${salaryBondDeleteUserId}/salary-bond/delete/${salaryBondDeleteId}`, {
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
                            toastr.success("Salary bond deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_bond'));
                            deleteModal.hide(); // Hide the modal

                            setTimeout(() => window.location.reload(),
                                800); // Refresh the page after a short delay
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting salary bond.");
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
