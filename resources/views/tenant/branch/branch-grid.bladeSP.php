<?php $page = 'branch-grid'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Branches</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>

                            <li class="breadcrumb-item active" aria-current="page">Branches Grid</li>
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
                            </ul>
                        </div>
                    </div>
                    <div class="mb-2"> 
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_branch"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            Branch</a>
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

            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5>Branches Grid</h5>
                        <div class="dropdown">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
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
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Last Month</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7 Days</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Branch Card --}}
            <div class="row">
                @foreach ($branches as $branch)
                    @php
                        $logoPath = $branch->branch_logo ?? null;
                        $logoUrl = $logoPath
                            ? asset('storage/' . $logoPath)
                            : asset('build/img/company/company-13.svg');
                    @endphp
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="form-check form-check-md">
                                        <input class="form-check-input" type="checkbox">
                                    </div>
                                    <div>
                                        <a href="#"
                                            class="avatar avatar-xl avatar-rounded online border rounded-circle">
                                            <img src="{{ $logoUrl }}" class="img-fluid h-auto w-auto" alt="Branch Logo"
                                                onerror="this.onerror=null; this.src='{{ asset('build/img/company/company-13.svg') }}';">
                                        </a>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-icon btn-sm rounded-circle" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end p-3">
                                            <li>
                                                <a class="dropdown-item rounded-1" href="javascript:void(0);"
                                                    data-bs-toggle="modal" data-bs-target="#edit_branch"
                                                    data-id="{{ $branch->id }}"
                                                    data-branch-logo="{{ $branch->branch_logo ?? '' }}"
                                                    data-name="{{ $branch->name }}"
                                                    data-branch-type="{{ $branch->branch_type }}"
                                                    data-sss-type="{{ $branch->sss_contribution_type }}"
                                                    data-philhealth-type="{{ $branch->philhealth_contribution_type }}"
                                                    data-pagibig-type="{{ $branch->pagibig_contribution_type }}"
                                                    data-withholding-type="{{ $branch->withholding_tax_type }}"
                                                    data-days-per-year="{{ $branch->worked_days_per_year }}"
                                                    data-custom-days="{{ $branch->custom_worked_days }}"
                                                    data-fixed-sss="{{ $branch->fixed_sss_amount }}"
                                                    data-fixed-philhealth="{{ $branch->fixed_philhealth_amount }}"
                                                    data-fixed-pagibig="{{ $branch->fixed_pagibig_amount }}"
                                                    data-fixed-withholding="{{ $branch->fixed_withholding_amount }}"
                                                    data-contact-number="{{ $branch->contact_number }}"
                                                    data-location="{{ $branch->location }}"
                                                    data-salary-type="{{ $branch->salary_type }}"
                                                    data-basic-salary="{{ $branch->basic_salary }}"
                                                    data-salary-computation-type="{{ $branch->salary_computation_type }}"
                                                    data-wage-order="{{ $branch->wage_order }}"
                                                    data-branch-tin="{{ $branch->branch_tin }}"><i
                                                        class="ti ti-edit me-1"></i>Edit</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item rounded-1 btn-delete" href="javascript:void(0);"
                                                    data-bs-toggle="modal" data-bs-target="#delete_branch"
                                                    data-id="{{ $branch->id }}"
                                                    data-branch-name="{{ $branch->name }}"><i
                                                        class="ti ti-trash me-1"></i>Delete</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="text-center mb-3">
                                    <h6 class="mb-1"><a href="{{ url('company-details') }}">{{ $branch->name }}</a>
                                    </h6>
                                </div>
                                <div class="d-flex flex-column">
                                    <p class="text-dark d-inline-flex align-items-center mb-2">
                                        <i
                                            class="{{ $branch->branch_type === 'main' ? 'ti ti-star-filled text-warning' : 'ti ti-star text-gray-5' }} me-2"></i>
                                        {{ ucfirst($branch->branch_type) }}
                                    </p>
                                    <p class="text-dark d-inline-flex align-items-center mb-2">
                                        <i class="ti ti-phone text-gray-5 me-2"></i>
                                        {{ $branch->contact_number }}
                                    </p>
                                    <p class="text-dark d-inline-flex align-items-center">
                                        <i class="ti ti-map-pin text-gray-5 me-2"></i>
                                        {{ $branch->location }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            {{-- <div class="text-center mb-4">
                <a href="#" class="btn btn-white border"><i class="ti ti-loader-3 text-primary me-2"></i>Load More</a>
            </div> --}}
        </div>

       @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    {{-- Show Fixed Inputs --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 🧩 Mapping for Add and Edit
            const contributionMappings = [
                // Add Form
                {
                    selectId: 'branchSSSContributionType',
                    fixedFieldId: 'branchSSSFixedContribution'
                },
                {
                    selectId: 'branchPhilhealthContributionType',
                    fixedFieldId: 'branchPhilhealthFixedContribution'
                },
                {
                    selectId: 'branchPagibigContributionType',
                    fixedFieldId: 'branchPagibigFixedContribution'
                },
                {
                    selectId: 'branchWithholdingTaxType',
                    fixedFieldId: 'branchWithholdingTaxFixedContribution'
                },

                // Edit Form
                {
                    selectId: 'editBranchSSSContributionType',
                    fixedFieldId: 'editBranchSSSFixedContribution'
                },
                {
                    selectId: 'editBranchPhilhealthContributionType',
                    fixedFieldId: 'editBranchPhilhealthFixedContribution'
                },
                {
                    selectId: 'editBranchPagibigContributionType',
                    fixedFieldId: 'editBranchPagibigFixedContribution'
                },
                {
                    selectId: 'editBranchWithholdingTaxType',
                    fixedFieldId: 'editBranchWithholdingTaxFixedContribution'
                }
            ];

            // 🔁 Show/Hide Fixed Input Fields
            function toggleFixedField(selectElement, fixedFieldId) {
                const fixedField = document.getElementById(fixedFieldId);
                const parent = fixedField.closest('.col-md-6');
                if (selectElement.value === 'fixed') {
                    parent.style.display = 'block';
                } else {
                    parent.style.display = 'none';
                    fixedField.value = '';
                }
            }

            contributionMappings.forEach(mapping => {
                const selectEl = document.getElementById(mapping.selectId);
                const fixedFieldCol = document.getElementById(mapping.fixedFieldId).closest('.col-md-6');
                fixedFieldCol.style.display = 'none';

                selectEl.addEventListener('change', function() {
                    toggleFixedField(this, mapping.fixedFieldId);
                });

                // Handle default selection on page load
                toggleFixedField(selectEl, mapping.fixedFieldId);
            });

            // 🎯 Custom Worked Days Logic — Add Form
            const workedDaysAddSelect = document.getElementById('branchWorkedDaysPerYear');
            const customWorkedDaysAddField = document.getElementById('branchCustomWorkedDays');
            const customWorkedDaysAddWrapper = customWorkedDaysAddField.closest('.col-md-6');

            function toggleCustomWorkedDaysAdd() {
                if (workedDaysAddSelect.value === 'custom') {
                    customWorkedDaysAddWrapper.style.display = 'block';
                } else {
                    customWorkedDaysAddWrapper.style.display = 'none';
                    customWorkedDaysAddField.value = '';
                }
            }

            customWorkedDaysAddWrapper.style.display = 'none';
            workedDaysAddSelect.addEventListener('change', toggleCustomWorkedDaysAdd);

            // 🎯 Custom Worked Days Logic — Edit Form
            const workedDaysEditSelect = document.getElementById('editBranchWorkedDaysPerYear');
            const customWorkedDaysEditField = document.getElementById('editBranchCustomWorkedDays');
            const customWorkedDaysEditWrapper = customWorkedDaysEditField.closest('.col-md-6');

            function toggleCustomWorkedDaysEdit() {
                if (workedDaysEditSelect.value === 'custom') {
                    customWorkedDaysEditWrapper.style.display = 'block';
                } else {
                    customWorkedDaysEditWrapper.style.display = 'none';
                    customWorkedDaysEditField.value = '';
                }
            }

            customWorkedDaysEditWrapper.style.display = 'none';
            workedDaysEditSelect.addEventListener('change', toggleCustomWorkedDaysEdit);
        });
    </script>


    {{-- Form Submission w/branch logo input --}}
    <script>
        $(document).ready(function() {
            // 🖼️ Logo Preview
            $('#branchLogoInput').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#branchLogoPreview').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('#cancelLogoUpload').on('click', function() {
                $('#branchLogoInput').val('');
                $('#branchLogoPreview').attr('src',
                    "{{ URL::asset('build/img/profiles/avatar-30.jpg') }}");
            });

            // 📤 Form Submission
            $('#addBranchForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('api.branchCreate') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content')
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            $('#addBranchForm')[0].reset();
                            $('#branchLogoPreview').attr('src',
                                "{{ URL::asset('build/img/profiles/avatar-30.jpg') }}");
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message || "Something went wrong.");
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors || {};
                        for (const key in errors) {
                            toastr.error(errors[key][0]);
                        }
                    }
                });
            });
        });
    </script>

    {{-- Edit Form Submission --}}
    <script>
        $(document).ready(function() {
            // 🧩 Trigger modal & populate fields
            $('[data-bs-target="#edit_branch"]').on('click', function() {
                const modal = $('#edit_branch');
                const baseUrl = "{{ asset('storage') }}/";

                const branchId = $(this).data('id');
                const logo = $(this).data('branch-logo');

                // Store branch ID
                $('#editBranchForm').data('id', branchId);

                // Populate inputs
                $('#editBranchName').val($(this).data('name'));
                $('#editBranchContactNumber').val($(this).data('contact-number'));
                $('#editBranchType').val($(this).data('branch-type')).trigger('change');
                $('#editBranchAddress').val($(this).data('location'));
                $('#editBranchSalaryComputationType').val($(this).data('salary-computation-type')).trigger('change');

                $('#editBranchSSSContributionType').val($(this).data('sss-type')).trigger('change');
                $('#editBranchSSSFixedContribution').val($(this).data('fixed-sss'));

                $('#editBranchPhilhealthContributionType').val($(this).data('philhealth-type')).trigger(
                    'change');
                $('#editBranchPhilhealthFixedContribution').val($(this).data('fixed-philhealth'));

                $('#editBranchPagibigContributionType').val($(this).data('pagibig-type')).trigger('change');
                $('#editBranchPagibigFixedContribution').val($(this).data('fixed-pagibig'));

                $('#editBranchWithholdingTaxType').val($(this).data('withholding-type')).trigger('change');
                $('#editBranchWithholdingTaxFixedContribution').val($(this).data('fixed-withholding'));

                $('#editBranchWorkedDaysPerYear').val($(this).data('days-per-year')).trigger('change');
                $('#editBranchCustomWorkedDays').val($(this).data('custom-days'));

                $('#editBranchBasicSalary').val($(this).data('basic-salary'));
                $('#editBranchSalaryType').val($(this).data('salary-type')).trigger('change');
                $('#editBranchWageOrder').val($(this).data('wage-order'));
                $('#editBranchTIN').val($(this).data('branch-tin'));

                // Set logo preview
                const preview = $('#editBranchLogoPreview');
                if (logo && logo !== 'null') {
                    preview.attr('src', baseUrl + logo);
                    preview.data('default-src', baseUrl + logo);
                } else {
                    const defaultLogo = "{{ URL::asset('build/img/profiles/avatar-30.jpg') }}";
                    preview.attr('src', defaultLogo);
                    preview.data('default-src', defaultLogo);
                }
            });

            // 📷 Preview uploaded logo
            $('#editBranchLogoInput').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#editBranchLogoPreview').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // 🔄 Cancel uploaded logo
            $('#editCancelLogoUpload').on('click', function() {
                $('#editBranchLogoInput').val('');
                const defaultSrc = $('#editBranchLogoPreview').data('default-src');
                $('#editBranchLogoPreview').attr('src', defaultSrc);
            });

            // 📨 AJAX submit update
            $('#editBranchForm').on('submit', function(e) {
                e.preventDefault();

                const id = $(this).data('id');
                const formData = new FormData(this);

                $.ajax({
                    url: `/api/branches/update/${id}`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            $('#edit_branch').modal('hide');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        for (const key in errors) {
                            toastr.error(errors[key][0]);
                        }
                    }
                });
            });
        });
    </script>

    {{-- Delete Branch --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let deleteId = null;
            let userId = null;

            const deleteButtons = document.querySelectorAll('.btn-delete');
            const confirmBranchDeleteBtn = document.getElementById('confirmBranchDeleteBtn');
            const branchNamePlaceholder = document.getElementById('branchNamePlaceholder');

            // Set up the delete buttons to capture data
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    deleteId = this.getAttribute('data-id');
                    const branchName = this.getAttribute('data-branch-name');

                    if (branchNamePlaceholder) {
                        branchNamePlaceholder.textContent =
                            branchName; // Update the modal with the branch name
                    }
                });
            });

            // Confirm delete button click event
            confirmBranchDeleteBtn?.addEventListener('click', function() {
                if (!deleteId) return; // Ensure deleteId is available

                fetch(`/api/branches/delete/${deleteId}`, {
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
                            toastr.success("Branch deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_branch'));
                            deleteModal.hide(); // Hide the modal

                            setTimeout(() => window.location.reload(),
                                800); // Refresh the page after a short delay
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting branch.");
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
