<?php $page = 'custom-table'; ?>
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
                            <li class="breadcrumb-item">
                                Payroll
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Payroll Items</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
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
                    <a href="{{ route('sss-contributionTable') }}" class="btn btn-white border me-2">SSS
                        Contribution</a>
                    <a href="{{ route('philhealth') }}" class="btn btn-white border me-2">PhilHealth</a>
                    <a href="{{ route('withholding-taxTable') }}" class="btn btn-white  border me-2">Withholding Tax</a>
                    <a href="{{ route('ot-table') }}" class="btn btn-white border me-2">OT Table</a>
                    <a href="{{ route('custom-ot-rate') }}" class="btn btn-white active border me-2">Custom OT</a>
                    <a href="{{ route('de-minimis-benefits') }}" class="btn btn-white border me-2">De Minimis</a>
                    <a href="{{ route('earnings') }}" class="btn btn-white border me-2">Earnings</a>
                    <a href="{{ route('deductions') }}" class="btn btn-white border me-2">Deductions</a>
                    <a href="{{ route('allowance') }}" class="btn btn-white border me-2">Allowance</a>
                </div>
                <div class="d-flex gap-2 mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#create_custom_ot_rate"
                        class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Create
                        Custom OT</a>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- Payroll list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Custom OT Table</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3 gap-2">
                        <div class="form-group mb-0">
                            <select id="template_filter" class="form-select" style="min-width: 250px;">
                                <option value="">Select a Template</option>
                                @foreach ($otTemplates as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table" id="otRatesTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Template Name</th>
                                    <th>Type</th>
                                    <th class="text-center">Normal</th>
                                    <th class="text-center">Overtime</th>
                                    <th class="text-center">ND</th>
                                    <th class="text-center">ND OT</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="otTableBody">
                                <tr id="emptyState">
                                    <td colspan="8" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="ti ti-filter-off" style="font-size: 48px; color: #ccc;"></i>
                                            <h5 class="mt-3 text-muted">No Template Selected</h5>
                                            <p class="text-muted">Please select a template from the dropdown above to view OT rates</p>
                                        </div>
                                    </td>
                                </tr>
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

    <!-- Create Custom OT Rate Modal -->
    <div class="modal fade" id="create_custom_ot_rate" tabindex="-1" aria-labelledby="createCustomOtRateLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <div>
                        <h5 class="modal-title mb-1 text-white" id="createCustomOtRateLabel">
                            <i class="ti ti-file-plus me-2 text-white"></i>Create Custom OT Template
                        </h5>
                        <small class="opacity-75">Configure overtime rates for different day types</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="createCustomOtForm">
                    @csrf
                    <div class="modal-body p-4" style="background-color: #f8f9fa;">
                        <!-- Template Information Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <h6 class="card-title text-primary mb-3">
                                    <i class="ti ti-info-circle me-2"></i>Template Information
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label for="name" class="form-label fw-semibold">
                                            Template Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-lg" id="name"
                                            name="name" placeholder="e.g., Regular Employee OT Rates" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="status" class="form-label fw-semibold">
                                            Status <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select form-select-lg" id="status" name="status" required>
                                            <option value="active" selected>Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label for="description" class="form-label fw-semibold">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="2"
                                            placeholder="Add notes or description about this template (optional)"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- OT Template Rates Card -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="card-title text-primary mb-1">
                                            <i class="ti ti-calculator me-2"></i>Overtime Rate Configuration
                                        </h6>
                                        <small class="text-muted">Define rates for different day types</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary add-rate-btn">
                                        <i class="ti ti-plus me-1"></i>Add Rate Type
                                    </button>
                                </div>

                                <div id="otRatesContainer" style="max-height: 450px; overflow-y: auto; overflow-x: hidden; padding-right: 10px; border: 1px solid #e0e0e0; border-radius: 5px; padding: 10px; background-color: #fafafa;">
                                    <!-- First Rate Row (Default) -->
                                    <div class="rate-row mb-3 p-3 border rounded bg-white" data-index="0"
                                        style="border-left: 4px solid #007bff !important;">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">
                                                    Day Type <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-select rate-type" name="rates[0][type]" required>
                                                    <option value="">Select Type</option>
                                                    <option value="Ordinary">Ordinary Day</option>
                                                    <option value="Rest Day">Rest Day</option>
                                                    <option value="Special Holiday">Special Holiday</option>
                                                    <option value="Regular Holiday">Regular Holiday</option>
                                                    <option value="Special Holiday Rest Day">Special Holiday + Rest Day
                                                    </option>
                                                    <option value="Regular Holiday Rest Day">Regular Holiday + Rest Day
                                                    </option>
                                                    <option value="Double Holiday">Double Holiday</option>
                                                    <option value="Double Holiday Rest Day">Double Holiday + Rest Day
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">
                                                    Normal <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" step="0.001" class="form-control"
                                                        name="rates[0][normal]" placeholder="1.000" required min="0">
                                                    <span class="input-group-text">x</span>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">
                                                    Overtime <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" step="0.001" class="form-control"
                                                        name="rates[0][overtime]" placeholder="1.250" required min="0">
                                                    <span class="input-group-text">x</span>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">
                                                    ND <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" step="0.001" class="form-control"
                                                        name="rates[0][night_differential]" placeholder="1.100" required
                                                        min="0">
                                                    <span class="input-group-text">x</span>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">
                                                    ND OT <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" step="0.001" class="form-control"
                                                        name="rates[0][night_differential_overtime]" placeholder="1.375"
                                                        required min="0">
                                                    <span class="input-group-text">x</span>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-success w-100 btn-add-first"
                                                    title="Add Rate">
                                                    <i class="ti ti-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info mt-3 d-flex align-items-start">
                                    <i class="ti ti-info-circle me-2 mt-1"></i>
                                    <small>
                                        <strong>Note:</strong> Multipliers are applied to the base hourly rate. Example:
                                        1.25x means 125% of base rate.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 p-3 gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ti ti-x me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="ti ti-device-floppy me-1"></i>Create Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Create Custom OT Rate Modal -->

    <!-- Edit Custom OT Rate Modal -->
    <div class="modal fade" id="edit_custom_ot_rate" tabindex="-1" aria-labelledby="editCustomOtRateLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white border-0">
                    <div>
                        <h5 class="modal-title mb-1 text-white" id="editCustomOtRateLabel">
                            <i class="ti ti-edit me-2 text-white"></i>Edit OT Rate
                        </h5>
                        <small class="opacity-75">Update overtime rate configuration</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="editCustomOtForm">
                    @csrf
                    <input type="hidden" id="edit_rate_id" name="rate_id">
                    <input type="hidden" id="edit_template_id" name="template_id">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Template</label>
                            <input type="text" class="form-control" id="edit_template_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Day Type</label>
                            <input type="text" class="form-control" id="edit_type" readonly>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_normal" class="form-label fw-semibold">
                                    Normal <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.001" class="form-control" id="edit_normal"
                                        name="normal" placeholder="1.000" required min="0">
                                    <span class="input-group-text">x</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_overtime" class="form-label fw-semibold">
                                    Overtime <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.001" class="form-control" id="edit_overtime"
                                        name="overtime" placeholder="1.250" required min="0">
                                    <span class="input-group-text">x</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_nd" class="form-label fw-semibold">
                                    Night Differential <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.001" class="form-control" id="edit_nd"
                                        name="night_differential" placeholder="1.100" required min="0">
                                    <span class="input-group-text">x</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_nd_ot" class="form-label fw-semibold">
                                    ND Overtime <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.001" class="form-control" id="edit_nd_ot"
                                        name="night_differential_overtime" placeholder="1.375" required min="0">
                                    <span class="input-group-text">x</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 p-3 gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ti ti-x me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-dark px-4">
                            <i class="ti ti-device-floppy me-1"></i>Update Rate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Edit Custom OT Rate Modal -->

    <!-- Delete OT Rate Modal -->
    <div class="modal fade" id="delete_ot_rate" tabindex="-1" aria-labelledby="deleteOtRateLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3">
                        <i class="ti ti-trash-x fs-36"></i>
                    </span>
                    <h4 class="mb-1">Confirm Delete</h4>
                    <p class="mb-3">Are you sure you want to delete the <strong id="delete_rate_type"></strong> rate from <strong id="delete_template_name"></strong>? This action cannot be undone.</p>
                    <input type="hidden" id="delete_rate_id">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteRate">Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete OT Rate Modal -->

    @component('components.modal-popup')
    @endcomponent

@endsection

@push('styles')
    <style>
        /* Custom scrollbar for OT Rates Container */
        #otRatesContainer::-webkit-scrollbar {
            width: 12px;
        }

        #otRatesContainer::-webkit-scrollbar-track {
            background: #e9ecef;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }

        #otRatesContainer::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #007bff 0%, #0056b3 100%);
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }

        #otRatesContainer::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #0056b3 0%, #003d82 100%);
        }

        /* Smooth scroll behavior */
        #otRatesContainer {
            scroll-behavior: smooth;
            scrollbar-width: thin;
            scrollbar-color: #007bff #e9ecef;
        }

        /* Animation for new rate rows */
        .rate-row {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Ensure modal body can scroll */
        .modal-body {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let rateIndex = 1;

            // Get all selected types
            function getSelectedTypes() {
                let selectedTypes = [];
                $('.rate-type').each(function() {
                    if ($(this).val()) {
                        selectedTypes.push($(this).val());
                    }
                });
                return selectedTypes;
            }

            // Update all dropdowns to disable selected options
            function updateTypeDropdowns() {
                let selectedTypes = getSelectedTypes();

                $('.rate-type').each(function() {
                    let currentValue = $(this).val();
                    $(this).find('option').each(function() {
                        if ($(this).val() && selectedTypes.includes($(this).val()) && $(this)
                            .val() !== currentValue) {
                            $(this).prop('disabled', true);
                        } else {
                            $(this).prop('disabled', false);
                        }
                    });
                });
            }

            // Add new rate row
            $(document).on('click', '.add-rate-btn, .btn-add-first', function() {
                const colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1',
                    '#fd7e14'
                ];
                const colorIndex = rateIndex % colors.length;

                let newRow = `
                <div class="rate-row mb-3 p-3 border rounded bg-white" data-index="${rateIndex}" style="border-left: 4px solid ${colors[colorIndex]} !important;">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Day Type <span class="text-danger">*</span></label>
                            <select class="form-select rate-type" name="rates[${rateIndex}][type]" required>
                                <option value="">Select Type</option>
                                <option value="Ordinary">Ordinary Day</option>
                                <option value="Rest Day">Rest Day</option>
                                <option value="Special Holiday">Special Holiday</option>
                                <option value="Regular Holiday">Regular Holiday</option>
                                <option value="Special Holiday Rest Day">Special Holiday + Rest Day</option>
                                <option value="Regular Holiday Rest Day">Regular Holiday + Rest Day</option>
                                <option value="Double Holiday">Double Holiday</option>
                                <option value="Double Holiday Rest Day">Double Holiday + Rest Day</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Normal <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.001" class="form-control" name="rates[${rateIndex}][normal]" placeholder="1.000" required min="0">
                                <span class="input-group-text">x</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Overtime <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.001" class="form-control" name="rates[${rateIndex}][overtime]" placeholder="1.250" required min="0">
                                <span class="input-group-text">x</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">ND <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.001" class="form-control" name="rates[${rateIndex}][night_differential]" placeholder="1.100" required min="0">
                                <span class="input-group-text">x</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">ND OT <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.001" class="form-control" name="rates[${rateIndex}][night_differential_overtime]" placeholder="1.375" required min="0">
                                <span class="input-group-text">x</span>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-success add-rate-btn" title="Add Rate">
                                    <i class="ti ti-plus"></i>
                                </button>
                                <button type="button" class="btn btn-danger remove-rate-btn" title="Remove Rate">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                $('#otRatesContainer').append(newRow);
                rateIndex++;
                updateTypeDropdowns();

                // Smooth scroll to the newly added row
                setTimeout(function() {
                    const container = document.getElementById('otRatesContainer');
                    container.scrollTop = container.scrollHeight;
                }, 100);
            });

            // Remove rate row
            $(document).on('click', '.remove-rate-btn', function() {
                $(this).closest('.rate-row').remove();
                updateTypeDropdowns();
            });

            // Update dropdowns when type is selected
            $(document).on('change', '.rate-type', function() {
                updateTypeDropdowns();
            });

            // Handle form submission
            $('#createCustomOtForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                // Convert FormData to JSON for API
                let jsonData = {
                    name: formData.get('name'),
                    description: formData.get('description'),
                    status: formData.get('status'),
                    rates: []
                };

                // Collect all rates
                $('.rate-row').each(function(index) {
                    let rateData = {
                        type: $(this).find('.rate-type').val(),
                        normal: $(this).find('input[name*="[normal]"]').val(),
                        overtime: $(this).find('input[name*="[overtime]"]').val(),
                        night_differential: $(this).find('input[name*="[night_differential]"]')
                            .val(),
                        night_differential_overtime: $(this).find(
                            'input[name*="[night_differential_overtime]"]').val()
                    };
                    jsonData.rates.push(rateData);
                });

                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

                $.ajax({
                    url: '/api/payroll/payroll-items/custom-ot-rate',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    data: JSON.stringify(jsonData),
                    success: function(response) {
                        $('#create_custom_ot_rate').modal('hide');

                        toastr.success(
                            response.message || 'Your OT template has been created successfully!',
                            'Template Created', {
                                closeButton: true,
                                progressBar: true,
                                timeOut: 3000
                            }
                        );

                        // Reset form
                        $('#createCustomOtForm')[0].reset();
                        $('.rate-row').not(':first').remove();
                        rateIndex = 1;

                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            // Validation errors
                            let errors = xhr.responseJSON.errors;
                            let errorMessage = '<ul class="mb-0 ps-3">';
                            $.each(errors, function(key, value) {
                                errorMessage += '<li>' + value[0] + '</li>';
                            });
                            errorMessage += '</ul>';
                            toastr.error(errorMessage, 'Please check your input', {
                                closeButton: true,
                                progressBar: true,
                                timeOut: 5000,
                                escapeHtml: false
                            });
                        } else {
                            // General error
                            let errorMessage = 'Something went wrong. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            toastr.error(errorMessage, 'Unable to Create Template', {
                                closeButton: true,
                                progressBar: true,
                                timeOut: 5000
                            });
                        }

                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Reset form when modal is closed
            $('#create_custom_ot_rate').on('hidden.bs.modal', function() {
                $('#createCustomOtForm')[0].reset();
                $('.rate-row').not(':first').remove();
                rateIndex = 1;
                updateTypeDropdowns();
                $(this).find('button[type="submit"]').prop('disabled', false).html(
                    '<i class="ti ti-device-floppy me-1"></i>Create Template');
            });

            // Template Filter Handler
            $('#template_filter').on('change', function() {
                const templateId = $(this).val();

                if (!templateId) {
                    $('#otTableBody').html(`
                    <tr id="emptyState">
                        <td colspan="8" class="text-center py-5">
                            <div class="empty-state">
                                <i class="ti ti-filter-off" style="font-size: 48px; color: #ccc;"></i>
                                <h5 class="mt-3 text-muted">No Template Selected</h5>
                                <p class="text-muted">Please select a template from the dropdown above to view OT rates</p>
                            </div>
                        </td>
                    </tr>
                `);
                    return;
                }

                // Show loading
                $('#otTableBody').html(`
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading template rates...</p>
                    </td>
                </tr>
            `);

                // Fetch template rates
                $.ajax({
                    url: `/api/payroll/payroll-items/custom-ot-rate/${templateId}`,
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success && response.template.ot_template_rates.length >
                            0) {
                            let rows = '';
                            response.template.ot_template_rates.forEach((rate, index) => {
                                rows += `
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" value="${rate.id}">
                                        </div>
                                    </td>
                                    <td><strong>${response.template.name}</strong></td>
                                    <td><span class="badge bg-primary-transparent">${rate.type}</span></td>
                                    <td class="text-center"><strong>${rate.normal}x</strong></td>
                                    <td class="text-center"><strong>${rate.overtime}x</strong></td>
                                    <td class="text-center"><strong>${rate.night_differential}x</strong></td>
                                    <td class="text-center"><strong>${rate.night_differential_overtime}x</strong></td>
                                    <td class="text-center">
                                        <div class="action-icon d-inline-flex">
                                            <a href="#" class="btn-edit-rate me-2" data-bs-toggle="modal"
                                                data-bs-target="#edit_custom_ot_rate"
                                                data-rate-id="${rate.id}"
                                                data-template-id="${response.template.id}"
                                                data-template-name="${response.template.name}"
                                                data-type="${rate.type}"
                                                data-normal="${rate.normal}"
                                                data-overtime="${rate.overtime}"
                                                data-nd="${rate.night_differential}"
                                                data-nd-ot="${rate.night_differential_overtime}">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <a href="#" class="btn-delete-rate" data-bs-toggle="modal"
                                                data-bs-target="#delete_ot_rate"
                                                data-rate-id="${rate.id}"
                                                data-type="${rate.type}"
                                                data-template-name="${response.template.name}">
                                                <i class="ti ti-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            `;
                            });
                            $('#otTableBody').html(rows);

                            toastr.success(
                                `Loaded ${response.template.ot_template_rates.length} rate(s) for ${response.template.name}`,
                                'Template Loaded', {
                                    closeButton: true,
                                    progressBar: true,
                                    timeOut: 2000
                                }
                            );
                        } else {
                            $('#otTableBody').html(`
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="ti ti-file-x" style="font-size: 48px; color: #ccc;"></i>
                                    <h5 class="mt-3 text-muted">No Rates Found</h5>
                                    <p class="text-muted">This template doesn't have any rates configured yet</p>
                                </td>
                            </tr>
                        `);

                            toastr.info(
                                'This template doesn\'t have any rates configured yet',
                                'No Rates Found', {
                                    closeButton: true,
                                    progressBar: true,
                                    timeOut: 3000
                                }
                            );
                        }
                    },
                    error: function(xhr) {
                        $('#otTableBody').html(`
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="ti ti-alert-circle" style="font-size: 48px; color: #dc3545;"></i>
                                <h5 class="mt-3 text-danger">Error Loading Data</h5>
                                <p class="text-muted">Failed to fetch template rates. Please try again.</p>
                            </td>
                        </tr>
                    `);

                        toastr.error(
                            'Unable to load the template rates. Please try again.',
                            'Loading Failed', {
                                closeButton: true,
                                progressBar: true,
                                timeOut: 4000
                            }
                        );
                    }
                });
            });

            // Edit rate button handler
            $(document).on('click', '.btn-edit-rate', function() {
                const rateId = $(this).data('rate-id');
                const templateId = $(this).data('template-id');
                const templateName = $(this).data('template-name');
                const type = $(this).data('type');
                const normal = $(this).data('normal');
                const overtime = $(this).data('overtime');
                const nd = $(this).data('nd');
                const ndOt = $(this).data('nd-ot');

                // Set values in the edit form
                $('#edit_rate_id').val(rateId);
                $('#edit_template_id').val(templateId);
                $('#edit_template_name').val(templateName);
                $('#edit_type').val(type);
                $('#edit_normal').val(normal);
                $('#edit_overtime').val(overtime);
                $('#edit_nd').val(nd);
                $('#edit_nd_ot').val(ndOt);

                // Show the edit modal
                $('#edit_custom_ot_rate').modal('show');
            });

            // Handle edit form submission
            $('#editCustomOtForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                // Convert FormData to JSON for API
                let jsonData = {
                    rate_id: formData.get('rate_id'),
                    normal: formData.get('normal'),
                    overtime: formData.get('overtime'),
                    night_differential: formData.get('night_differential'),
                    night_differential_overtime: formData.get('night_differential_overtime')
                };

                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

                $.ajax({
                    url: '/api/payroll/payroll-items/custom-ot-rate/update',
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    data: JSON.stringify(jsonData),
                    success: function(response) {
                        $('#edit_custom_ot_rate').modal('hide');

                        toastr.success(
                            response.message || 'The OT rate has been updated successfully!',
                            'Rate Updated', {
                                closeButton: true,
                                progressBar: true,
                                timeOut: 3000
                            }
                        );

                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            // Validation errors
                            let errors = xhr.responseJSON.errors;
                            let errorMessage = '<ul class="mb-0 ps-3">';
                            $.each(errors, function(key, value) {
                                errorMessage += '<li>' + value[0] + '</li>';
                            });
                            errorMessage += '</ul>';
                            toastr.error(errorMessage, 'Please check your input', {
                                closeButton: true,
                                progressBar: true,
                                timeOut: 5000,
                                escapeHtml: false
                            });
                        } else {
                            // General error
                            let errorMessage = 'Something went wrong. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            toastr.error(errorMessage, 'Unable to Update Rate', {
                                closeButton: true,
                                progressBar: true,
                                timeOut: 5000
                            });
                        }

                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Delete rate button handler
            $(document).on('click', '.btn-delete-rate', function() {
                const rateId = $(this).data('rate-id');
                const type = $(this).data('type');
                const templateName = $(this).data('template-name');

                // Set values in the delete confirmation
                $('#delete_rate_id').val(rateId);
                $('#delete_rate_type').text(type);
                $('#delete_template_name').text(templateName);

                // Show the delete confirmation modal
                $('#delete_ot_rate').modal('show');
            });

            // Confirm delete action
            $('#confirmDeleteRate').on('click', function() {
                const rateId = $('#delete_rate_id').val();

                $.ajax({
                    url: '/api/payroll/payroll-items/custom-ot-rate/delete',
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    data: JSON.stringify({ rate_id: rateId }),
                    success: function(response) {
                        $('#delete_ot_rate').modal('hide');

                        toastr.success(
                            response.message || 'The OT rate has been deleted successfully!',
                            'Rate Deleted', {
                                closeButton: true,
                                progressBar: true,
                                timeOut: 3000
                            }
                        );

                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    },
                    error: function(xhr) {
                        let errorMessage = 'Something went wrong. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        toastr.error(errorMessage, 'Unable to Delete Rate', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 5000
                        });
                    }
                });
            });
        });
    </script>
@endpush
