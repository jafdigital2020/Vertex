<?php $page = 'users'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee Assets</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Assets Management
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee Assets</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if (in_array('Create', $permission))
                        <div class="me-2 mb-2">
                            <a href="{{ route('employee-assets-history') }}"
                                class="btn btn-primary d-flex align-items-center"><i class="ti ti-eye me-2"></i>View
                                History</a>
                        </div>
                    @endif
                    @if (in_array('Export', $permission))
                        {{-- <div class="me-2 mb-2">
                            <div class="dropdown">
                                <a href="javascript:void(0);"
                                    class="dropdown-toggle btn btn-orange d-inline-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <i class="ti ti-file-export me-1"></i>Export
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                            <i class="ti ti-file-type-pdf me-1"></i>Export as PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                            <i class="ti ti-file-type-xls me-1"></i>Export as Excel
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div> --}}
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

            <!-- Performance Indicator list -->
            <div class="card">
                <div class="card-header">
                   <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3 bg-primary">
                    <h5 class="text-white">Employee Assets</h5>
                        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                            <div class="form-group me-2" style="max-width:200px;">
                                <select name="branch_filter" id="branch_filter" class="select2 form-select"
                                    oninput="filter()" style="width:150px;">
                                    <option value="" selected>All Branches</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group me-2">
                                <select name="department_filter" id="department_filter" class="select2 form-select"
                                    oninput="filter()" style="width:150px;">
                                    <option value="" selected>All Departments</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group me-2">
                                <select name="designation_filter" id="designation_filter" class="select2 form-select"
                                    oninput="filter()" style="width:150px;">
                                    <option value="" selected>All Designations</option>
                                    @foreach ($designations as $designation)
                                        <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group me-2">
                                <select name="status_filter" id="status_filter" class="select2 form-select"
                                    onchange="filter()">
                                    <option value="" selected>All Status</option>
                                    <option value="Available">Available</option>
                                    <option value="Deployed">Deployed</option>
                                    <option value="Return">Return</option>
                                </select>
                            </div>
                            <div class="form-group me-2">
                                <select name="condition_filter" id="condition_filter" class="select2 form-select"
                                    onchange="filter()">
                                    <option value="" selected>All Conditions</option>
                                    <option value="New">New</option>
                                    <option value="Good">Good</option>
                                    <option value="Damaged">Damaged</option>
                                    <option value="Under Maintenance">Under Maintenance</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="custom-datatable-filter table-responsive">
                            <table class="table datatable" id="user_permission_table_assets">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        @if (in_array('Update', $permission))
                                            <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="employeeAssetsTableBody">
                                    {{-- @if (in_array('Read', $permission))
                                        @foreach ($users as $user)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center file-name-icon">
                                                        <a href="#" class="avatar avatar-md avatar-rounded">
                                                            <img src="{{ URL::asset('build/img/users/user-32.jpg') }}"
                                                                class="img-fluid" alt="img">
                                                        </a>
                                                        <div class="ms-2">
                                                            <h6 class="fw-medium"><a
                                                                    href="#">{{ $user->personalInformation->first_name ?? '' }}
                                                                    {{ $user->personalInformation->last_name ?? '' }} </a></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                @if (in_array('Update', $permission))
                                                    <td class="text-center">
                                                        <div class="action-icon d-inline-flex">
                                                            <a href="#" class="me-2" onclick='addEmployeeAssets(@json($user))'>
                                                                <i class="ti ti-edit"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endif --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('layout.partials.footer-company')
    </div>
    @component('components.modal-popup', [
        'categories' => $categories,
    ])
    @endcomponent
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let assetsTable;

        function initializeAssetsTable() {
            if ($.fn.DataTable.isDataTable('#user_permission_table_assets')) {
                $('#user_permission_table_assets').DataTable().clear().destroy();
            }

            assetsTable = $('#user_permission_table_assets').DataTable({
                pageLength: 10,
                responsive: true
            });
        }

        $('#dateRange_filter').on('apply.daterangepicker', function() {
            filter();
        });

        function filter() {
            const branch = $('#branch_filter').val();
            const department = $('#department_filter').val();
            const designation = $('#designation_filter').val();
            const status = $('#status_filter').val();
            const condition = $('#condition_filter').val();

            $.ajax({
                url: '{{ route('employee-assets-filter') }}',
                type: 'GET',
                data: {
                    branch,
                    department,
                    designation,
                    condition,
                    status,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        if ($.fn.DataTable.isDataTable('#user_permission_table_assets')) {
                            $('#user_permission_table_assets').DataTable().destroy();
                        }
                        $('#employeeAssetsTableBody').html(response.html);

                        assetsTable = $('#user_permission_table_assets').DataTable({
                            pageLength: 10,
                            responsive: true
                        });
                    } else {
                        toastr.error(response.message || 'Something went wrong.');
                    }
                },
                error: function(xhr) {
                    let message = 'An unexpected error occurred.';
                    if (xhr.status === 403) {
                        message = 'You are not authorized to perform this action.';
                    } else if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }
                    toastr.error(message);
                }
            });
        }

        function addEmployeeAssets(user) {
            $('#employee-id').val(user.id);
            $('#addEmployeeAssetsTableBody').empty();

            $.ajax({
                url: `/employee-assets/${user.id}`,
                type: 'GET',
                success: function(response) {
                    const assets = response.data;

                    $('#addEmployeeAssetsTableBody').empty(); // clear old rows

                    assets.forEach(asset => {
                        let formattedPrice = parseFloat(asset.assets?.price ?? 0)
                            .toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });

                        const row = `
                    <tr>
                        <td class="text-center">
                            ${asset.assets?.name ?? 'N/A'} [Item No. ${asset.order_no ?? ''}]
                        </td>
                        <td class="text-center">${asset.assets?.category?.name ?? 'N/A'}</td>
                        <td class="text-center">${formattedPrice}</td>
                        <td class="text-center">
                            <select class="select select2"
                                name="condition${asset.id}"
                                onchange="checkCondition(this, ${asset.id}, '${asset.asset_condition}')">
                                <option value="New" ${asset.asset_condition === 'New' ? 'selected' : ''}>New</option>
                                <option value="Good" ${asset.asset_condition === 'Good' ? 'selected' : ''}>Good</option>
                                <option value="Damaged" ${asset.asset_condition === 'Damaged' ? 'selected' : ''}>Damaged</option>
                                <option value="Under Maintenance" ${asset.asset_condition === 'Under Maintenance' ? 'selected' : ''}>Under Maintenance</option>
                            </select>

                        </td>
                        <td class="text-center d-flex justify-content-center">
                            <button type="button" id="edit_employee_assets_remarksBTN${asset.id}"
                                class="btn btn-success btn-sm"
                                onclick="showRemarksModal(${asset.id})" style="display:none;">
                                <i class="fa fa-edit"></i>
                            </button>
                            <input id="remarks_hidden_${asset.id}" name="remarks_hidden_${asset.id}" type="hidden">
                            <button
                                type="button"
                                class="btn btn-warning btn-sm"
                                style="${asset.asset_condition === 'Damaged' ? 'display:block;' : 'display:none;'}"
                                onclick="showRemarks(${asset.id})">
                                <i class="fa fa-sticky-note"></i>
                            </button>
                        </td>
                        <td class="text-center">
                            <select class="select select2" name="status${asset.id}">
                                <option value="Deployed" ${asset.status === 'Deployed' ? 'selected' : ''}>Deployed</option>
                                <option value="Return" ${asset.status === 'Return' ? 'selected' : ''}>Return</option>
                            </select>
                        </td>
                        <td class="text-center">
                            <btn class="btn btn-danger btn-sm remove-asset-row" onclick="removeAssetDetail('${asset.id}')">Remove</btn>
                        </td>
                    </tr>
                `;

                        $('#addEmployeeAssetsTableBody').append(row);
                    });

                    $('.select2').select2();
                    $('#add_employee_assets').modal('show');
                },
                error: function() {
                    toastr.error('Failed to load assigned assets.');
                }
            });

        }

        let canceled = true;

        function checkCondition(selectElement, assetId, prevCondition) {
            let $select = $(selectElement);
            let selectedValue = $select.val();

            if (prevCondition !== "Damaged" && selectedValue === "Damaged") {
                canceled = true;
                $('#remarksAssetId').val(assetId);
                let currentRemarks = $('#remarks_hidden_' + assetId).val();
                $('#remarksText').val(currentRemarks);
                $('#employeeAssetsRemarksModal').modal('show');
                let remarks = $('#remarksText').val().trim();

                $("#employeeAssetsRemarksModal")
                    .off("hidden.bs.modal")
                    .on("hidden.bs.modal", function() {
                        if (canceled && remarks === '') {
                            $select.val(prevCondition).trigger("change.select2");
                        }
                    });
            }
        }
        $('#saveEmployeeAssetsRemarks').on('click', function() {
            let assetId = $('#remarksAssetId').val();
            let remarks = $('#remarksText').val();

            $('#remarks_hidden_' + assetId).val(remarks);

            canceled = false;

            $('#employeeAssetsRemarksModal').modal('hide');
            $('#edit_employee_assets_remarksBTN' + assetId).show();
        });

        function showRemarksModal(assetId) {
            $('#remarksAssetId').val(assetId);
            let currentRemarks = $('#remarks_hidden_' + assetId).val();
            $('#remarksText').val(currentRemarks);
            $('#employeeAssetsRemarksModal').modal('show');
        }

        function showRemarks(assetId) {
            $.get(`/employee-assets/${assetId}/remarks`, function(data) {
                $("#conditionRemarksText").val(data.condition_remarks);
                $("#employeeAssetsViewRemarksModal").modal('show');
            });
        }

        function removeAssetDetail(assetId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you really want to remove this asset from the employee?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    let hiddenInput = document.getElementById('removeAssetDetail_ids');
                    let currentIds = hiddenInput.value ? hiddenInput.value.split(',') : [];

                    if (!currentIds.includes(assetId.toString())) {
                        currentIds.push(assetId);
                    }

                    hiddenInput.value = currentIds.join(',');

                    document.querySelector(`[onclick="removeAssetDetail('${assetId}')"]`)
                        ?.closest('tr')
                        ?.remove();
                    updateTotalPrice();
                }
            });
        }

        $(document).ready(function() {


            function updateTotalPrice() {
                let total = 0;
                $('#addEmployeeAssetsTableBody tr').each(function() {
                    let priceText = $(this).find('td:eq(2)').text().trim();
                    let price = parseFloat(priceText.replace(/[^0-9.-]+/g, "")) || 0;
                    total += price;
                });
                $('#totalPrice').text(
                    total.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                );
            }

            $('#add_employee_assets').on('show.bs.modal', updateTotalPrice);
            $('#addEmployeeAssetsTableBody').on('DOMSubtreeModified', updateTotalPrice);

            $('#addEmployeeAssetButton').on('click', function() {
                let assetSelect = $('#selectAvailableAssets');
                let selectedOption = assetSelect.find(':selected');

                if (!selectedOption.val()) return;
                let assetId = selectedOption.val();
                let assetName = selectedOption.text();
                let assetCategory = selectedOption.data('category') || '-';
                let assetPrice = selectedOption.data('price');
                assetPrice = assetPrice ? parseFloat(assetPrice).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) : '-';
                let assetCondition = selectedOption.data('condition') || '-';
                let assetStatus = selectedOption.data('status') || '-';
                if ($(`#addEmployeeAssetsTableBody input[value="${assetId}"]`).length > 0) {
                    toastr.info("Asset already added.", "Info");
                    return;
                }
                let row = `
                <tr>
                    <td class="text-center">${assetName}<input type="hidden" name="assets_details_ids[]" value="${assetId}"></td>
                    <td class="text-center">${assetCategory}</td>
                    <td class="text-center">${assetPrice}</td>
                    <td class="text-center">${assetCondition}</td>
                    <td class="text-center"></td>
                    <td class="text-center">${assetStatus}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-asset-row">
                            Remove
                        </button>
                    </td>
                </tr>
            `;
                $('#addEmployeeAssetsTableBody').append(row);
                updateTotalPrice();
            });

            $(document).on('click', '.remove-asset-row', function() {
                $(this).closest('tr').remove();
                updateTotalPrice();
            });

            $('#selectCategory').on('change', function() {
                let categoryId = $(this).val();
                let assetDropdown = $('#selectAvailableAssets');

                assetDropdown.html('<option disabled selected>Loading...</option>');

                $.ajax({
                    url: '/employee-assets/by-category/' + categoryId,
                    type: 'GET',
                    success: function(data) {
                        let options = '<option disabled selected>Select Asset</option>';

                        data.forEach(assetDetails => {
                            let asset = assetDetails.assets;
                            if (!asset) return;

                            options += `<option value="${assetDetails.id}"
                            data-category="${asset.category?.name || '-'}"
                            data-price="${asset.price}"
                            data-condition="${assetDetails.asset_condition}"
                            data-status="${assetDetails.status}">
                            ${asset.name} [ Item No. ${assetDetails.order_no} ]
                        </option>`;
                        });

                        assetDropdown.html(options).prop('disabled', false);
                    },
                    error: function() {
                        assetDropdown.html(
                            '<option disabled selected>Error loading assets</option>');
                    }
                });
            });
        });
        $(document).ready(function() {
            $('#editAssetsForm').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        form.find('button[type="submit"]').prop('disabled', true).text(
                            'Updating...');
                    },
                    success: function(response) {
                        toastr.success('Assets updated successfully!');
                        filter();
                        $('#add_employee_assets').modal('hide');
                    },
                    error: function(xhr) {
                        toastr.error('An error occurred while updating assets.');
                    },
                    complete: function() {
                        form.find('button[type="submit"]').prop('disabled', false).text(
                            'Update');
                    }
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
