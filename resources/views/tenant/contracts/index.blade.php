<?php $page = 'contracts'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee Contracts</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('admin-dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employees
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Contracts</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_contract"
                            class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i>Create Contract
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="row">
                <!-- Contracts List -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Contracts List</h4>
                        </div>
                        <div class="card-body">
                            <div class="custom-datatable-filter table-responsive">
                                <table class="table datatable" id="contracts_table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Employee</th>
                                            <th>Contract Type</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                            <th>Signed Date</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($contracts as $contract)
                                            <tr>
                                                <td>
                                                    {{ $contract->user->personalInformation->first_name ?? '' }}
                                                    {{ $contract->user->personalInformation->last_name ?? $contract->user->username }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $contract->contract_type }}</span>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($contract->start_date)->format('M d, Y') }}</td>
                                                <td>
                                                    @if($contract->end_date)
                                                        {{ \Carbon\Carbon::parse($contract->end_date)->format('M d, Y') }}
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($contract->status === 'Active')
                                                        <span class="badge bg-success">Active</span>
                                                    @elseif($contract->status === 'Draft')
                                                        <span class="badge bg-warning">Draft</span>
                                                    @elseif($contract->status === 'Expired')
                                                        <span class="badge bg-danger">Expired</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $contract->status }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($contract->signed_date)
                                                        {{ \Carbon\Carbon::parse($contract->signed_date)->format('M d, Y') }}
                                                    @else
                                                        <span class="text-muted">Not signed</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="dropdown table-action">
                                                        <a href="#" class="action-icon dropdown-toggle"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="ti ti-dots-vertical"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a href="{{ route('contracts.show', $contract->id) }}"
                                                                    class="dropdown-item rounded-1">
                                                                    <i class="ti ti-eye me-2"></i>View
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="{{ route('contracts.print', $contract->id) }}"
                                                                    class="dropdown-item rounded-1" target="_blank">
                                                                    <i class="ti ti-printer me-2"></i>Print
                                                                </a>
                                                            </li>
                                                            @if($contract->status === 'Draft')
                                                                <li>
                                                                    <a href="javascript:void(0);"
                                                                        class="dropdown-item rounded-1 sign-contract"
                                                                        data-id="{{ $contract->id }}">
                                                                        <i class="ti ti-signature me-2"></i>Sign
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="{{ route('contracts.edit', $contract->id) }}"
                                                                        class="dropdown-item rounded-1">
                                                                        <i class="ti ti-edit me-2"></i>Edit
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            <li>
                                                                <a href="javascript:void(0);"
                                                                    class="dropdown-item rounded-1 delete-contract"
                                                                    data-id="{{ $contract->id }}">
                                                                    <i class="ti ti-trash me-2"></i>Delete
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No contracts found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- /Page Wrapper -->

    <!-- Add Contract Modal -->
    <div class="modal fade" id="add_contract" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Contract</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addContractForm" onsubmit="return false;">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Employee <span class="text-danger">*</span></label>
                            <select class="form-select" name="user_id" id="employee_select" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->personalInformation->first_name ?? '' }}
                                        {{ $employee->personalInformation->last_name ?? $employee->username }}
                                        ({{ $employee->employmentDetail->employee_id ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contract Template</label>
                            <select class="form-select" name="template_id" id="template_select">
                                <option value="">Select Template (Optional)</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" data-type="{{ $template->contract_type }}">
                                        {{ $template->name }} ({{ $template->contract_type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contract Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="contract_type" id="contract_type" required>
                                <option value="">Select Type</option>
                                <option value="Probationary">Probationary (6 months)</option>
                                <option value="Regular">Regular</option>
                                <option value="Contractual">Contractual</option>
                                <option value="Project-Based">Project-Based</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="start_date" id="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" id="end_date">
                                <small class="text-muted" id="end_date_note" style="display:none;">
                                    Probationary contracts auto-calculate 6 months duration
                                </small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="Draft" selected>Draft</option>
                                <option value="Active">Active</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="submitContractBtn">Create Contract</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .table-action .dropdown-menu {
            z-index: 1050 !important;
        }

        .action-icon {
            color: #495057;
            font-size: 18px;
            cursor: pointer;
        }

        .action-icon:hover {
            color: #007bff;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            // Initialize DataTable with proper settings
            if (!$.fn.DataTable.isDataTable('#contracts_table')) {
                $('#contracts_table').DataTable({
                    "pageLength": 10,
                    "ordering": true,
                    "searching": true,
                    "lengthChange": true,
                    "info": true,
                    "autoWidth": false,
                    "responsive": true,
                    "order": [[2, 'desc']], // Sort by Start Date descending
                    "columnDefs": [
                        { "orderable": false, "targets": 6 } // Disable sorting on Actions column
                    ]
                });
            }

            // Setup CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Template selection auto-fills contract type
            $('#template_select').on('change', function () {
                const selectedOption = $(this).find('option:selected');
                const contractType = selectedOption.data('type');
                if (contractType) {
                    $('#contract_type').val(contractType);
                    $('#contract_type').trigger('change');
                }
            });

            // Contract type change - show note for probationary
            $('#contract_type').on('change', function () {
                if ($(this).val() === 'Probationary') {
                    $('#end_date_note').show();
                    $('#end_date').prop('required', false);
                } else {
                    $('#end_date_note').hide();
                }
            });

            // Add Contract
            $('#submitContractBtn').on('click', function () {
                console.log('Submit button clicked!');

                const form = $('#addContractForm');
                const formData = form.serialize();
                console.log('Form data:', formData);
                console.log('Route URL:', '{{ route("contracts.store") }}');

                // Disable button
                $(this).prop('disabled', true).text('Creating...');

                $.ajax({
                    url: '{{ route("contracts.store") }}',
                    method: 'POST',
                    data: formData,
                    beforeSend: function (xhr) {
                        console.log('Sending AJAX request...');
                    },
                    success: function (response) {
                        console.log('SUCCESS - Response:', response);
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            $('#add_contract').modal('hide');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            toastr.error(response.message || 'Unknown error occurred');
                            $('#submitContractBtn').prop('disabled', false).text('Create Contract');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('ERROR - Status:', status);
                        console.error('ERROR - Error:', error);
                        console.error('ERROR - Response:', xhr.responseText);
                        console.error('ERROR - Full XHR:', xhr);

                        let errorMessage = 'Failed to create contract';

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            if (xhr.responseJSON.errors) {
                                const errors = Object.values(xhr.responseJSON.errors).flat();
                                errorMessage = errors.join('<br>');
                            }
                        }

                        toastr.error(errorMessage);
                        $('#submitContractBtn').prop('disabled', false).text('Create Contract');
                    }
                });
            });

            // Sign Contract
            $('.sign-contract').on('click', function () {
                const id = $(this).data('id');

                if (confirm('Are you sure you want to sign and activate this contract?')) {
                    $.ajax({
                        url: '/contracts/' + id + '/sign',
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (response) {
                            if (response.status === 'success') {
                                toastr.success(response.message);
                                setTimeout(() => location.reload(), 1000);
                            }
                        },
                        error: function (xhr) {
                            toastr.error('Failed to sign contract');
                        }
                    });
                }
            });

            // Delete Contract
            $('.delete-contract').on('click', function () {
                const id = $(this).data('id');

                if (confirm('Are you sure you want to delete this contract?')) {
                    $.ajax({
                        url: '/contracts/' + id,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (response) {
                            if (response.status === 'success') {
                                toastr.success(response.message);
                                setTimeout(() => location.reload(), 1000);
                            }
                        },
                        error: function (xhr) {
                            toastr.error('Failed to delete contract');
                        }
                    });
                }
            });
        });
    </script>
@endpush