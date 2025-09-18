<?php $page = 'users'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Assets Settings</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Assets Management
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Assets Settings</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if (in_array('Create', $permission))
                        <div class="me-2 mb-2">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#add_assets"
                                class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                                Asset</a>
                        </div>
                    @endif

                    @if (in_array('Create', $permission))
                        <div class="me-2 mb-2">
                            <a href="{{ route('assets-settings-history') }}"
                                class="btn btn-secondary d-flex align-items-center"><i class="ti ti-eye me-2"></i>View
                                History</a>
                        </div>
                    @endif

                    @if (in_array('Export', $permission))
                        <div class="me-2 mb-2">
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
                <div class="card-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h5 class="mb-0">Assets Settings</h5>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-group me-2" style="max-width:200px;">
                                <select name="branch_filter" id="branch_filter" class="select2 form-select"
                                    oninput="filter()">
                                    <option value="" selected>All Branches</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <select name="category_filter" id="category_filter" class="select2 form-select"
                                    onchange="filter()">
                                    <option value="" selected>All Categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <select name="manufacturer_filter" id="manufacturer_filter" class="select2 form-select"
                                    onchange="filter()">
                                    <option value="" selected>All Manufacturers</option>
                                    @foreach ($manufacturers as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <select name="status_filter" id="status_filter" class="select2 form-select"
                                    onchange="filter()">
                                    <option value="" selected>All Status</option>
                                    <option value="Available">Available</option>
                                    <option value="Deployed">Deployed</option>
                                    <option value="Return">Return</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select name="condition_filter" id="condition_filter" class="select2 form-select"
                                    onchange="filter()">
                                    <option value="" selected>All Conditions</option>
                                    <option value="New">New</option>
                                    <option value="Good">Good</option>
                                    <option value="Damaged">Damaged</option>
                                    <option value="Under Maintenance">Under Maintenance</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select name="sortby_filter" id="sortby_filter" class="select2 form-select"
                                    onchange="filter()">
                                    <option value="" selected>All Sort By</option>
                                    <option value="ascending">Ascending</option>
                                    <option value="descending">Descending</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="last_7_days">Last 7 days</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="custom-datatable-filter table-responsive">
                            <table class="table datatable" id="assetsSettingsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Branch</th>
                                        <th class="text-center">Description</th>
                                        <th class="text-center">Category</th>
                                        <th class="text-center">Model</th>
                                        <th class="text-center">Manufacturer</th>
                                        <th class="text-center">Serial Number</th>
                                        <th class="text-center">Processor</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-center">Price</th>
                                        @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                            <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="assetsSettingsTableBody">
                                    @if (in_array('Read', $permission))
                                        @foreach ($assets as $asset)
                                            <tr>
                                                <td>{{ $asset->name ?? null }}</span>
                                                </td>
                                                <td>{{ $asset->branch->name ?? null }}</td>
                                                <td class="text-center">{{ $asset->description }}</td>
                                                <td class="text-center">
                                                    {{ $asset->category->name ?? 'NA' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $asset->model ?? 'NA' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $asset->manufacturer ?? 'NA' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $asset->serial_number ?? 'NA' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $asset->processor ?? 'NA' }}
                                                </td>
                                                <td class="text-center">{{ $asset->assetsDetails->count() }}</td>
                                                <td class="text-center">
                                                    {{ $asset->price }}
                                                </td>

                                                @if (in_array('Update', $permission))
                                                    <td class="text-center">
                                                        <div class="action-icon d-inline-flex">
                                                            @if (in_array('Update', $permission))
                                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                                    data-bs-target="#edit_assetsCondition"
                                                                    data-id="{{ $asset->id }}"
                                                                    data-name="{{ $asset->name }}"
                                                                    data-category="{{ $asset->category->name }}"><i
                                                                        class="ti ti-tools"></i></a>
                                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                                    data-bs-target="#edit_assets"
                                                                    data-id="{{ $asset->id }}"
                                                                    data-name="{{ $asset->name }}"
                                                                    data-description="{{ $asset->description }}"
                                                                    data-quantity="{{ $asset->quantity }}"
                                                                    data-categoryname="{{ $asset->category->id }}"
                                                                    data-price="{{ $asset->price }}"
                                                                    data-status="{{ $asset->status }}"
                                                                    data-model="{{ $asset->model }}"
                                                                    data-manufacturer="{{ $asset->manufacturer }}"
                                                                    data-serial_number="{{ $asset->serial_number }}"
                                                                    data-processor="{{ $asset->processor }}"><i
                                                                        class="ti ti-edit"></i></a>
                                                            @endif
                                                            @if (in_array('Delete', $permission))
                                                                <a href="#" class="btn-delete"
                                                                    data-bs-toggle="modal" data-bs-target="#delete_assets"
                                                                    data-id="{{ $asset->id }}"
                                                                    data-name="{{ $asset->name }}"><i
                                                                        class="ti ti-trash"></i></a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('layout.partials.footer-company')
    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup', [
        'categories' => $categories,
    ])
    @endcomponent
@endsection

@push('scripts')
    <script>
        function filter() {
            const category = $('#category_filter').val();
            const sortBy = $('#sortby_filter').val();
            const branch = $('#branch_filter').val();
            const manufacturer = $('#manufacturer_filter').val();
            const status = $('#status_filter').val();
            const condition = $('#condition_filter').val();
            $.ajax({
                url: '{{ route('assets-settings-filter') }}',
                type: 'GET',
                data: {
                    category,
                    sortBy,
                    branch,
                    manufacturer,
                    status,
                    condition
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#assetsSettingsTable').DataTable().destroy();
                        $('#assetsSettingsTableBody').html(response.html);
                        $('#assetsSettingsTable').DataTable();
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
        $(function() {
            $('#existingCategory').on('change', function() {
                if ($(this).val() === 'new') {
                    $('#newCategoryInput').show();
                } else {
                    $('#newCategoryInput').hide().val('');
                }
            });
            $('#edit_existingCategory').on('change', function() {
                if ($(this).val() === 'new') {
                    $('#edit_newCategoryInput').show();
                } else {
                    $('#edit_newCategoryInput').hide().val('');
                }
            });
        });
        $('#edit_assets').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);

            var id = button.data('id');
            var name = button.data('name');
            var description = button.data('description');
            var categoryName = button.data('categoryname');
            var price = button.data('price');
            var quantity = button.data('quantity');
            var model = button.data('model');
            var manufacturer = button.data('manufacturer');
            var serial_number = button.data('serial_number');
            var processor = button.data('processor');
            var modal = $(this);
            modal.find('#edit_id').val(id);
            modal.find('#edit_name').val(name);
            modal.find('#edit_description').val(description);
            modal.find('#edit_existingCategory').val(categoryName).trigger("change");
            modal.find('#edit_price').val(price);
            modal.find('#edit_quantity').val(quantity);
            modal.find('#edit_model').val(model);
            modal.find('#edit_manufacturer').val(manufacturer);
            modal.find('#edit_serial_number').val(serial_number);
            modal.find('#edit_processor').val(processor);

            $('#edit_status').select2({
                dropdownParent: $('#edit_assets'),
                width: '100%',
                minimumResultsForSearch: 0
            });

        });

        $('#edit_assetsCondition').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);

            var id = button.data('id');
            var name = button.data('name');
            var category = button.data('category');

            var modal = $(this);
            modal.find('#editCondition_id').val(id);
            modal.find('#editCondition_name').text(name);
            modal.find('#editCondition_category').text(category);
            $.ajax({
                url: '{{ route('assets-settings-details') }}',
                method: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    if (response.status === 'success') {

                        let details = response.assets_details;
                        console.log(details);
                        let tableBody = $('#assetsConditionTableBody');
                        tableBody.empty();
                        details.forEach((item, index) => {
                            let row = `
                                    <tr class="text-center">
                                        <td>${item.order_no}</td>
                                        <td>
                                            <select class="select select2" name="condition[]" onchange="checkCondition(this, ${item.id}, '${item.asset_condition}')">
                                                <option value="New" ${item.asset_condition === 'New' ? 'selected' : ''}>New</option>
                                                <option value="Good" ${item.asset_condition === 'Good' ? 'selected' : ''}>Good</option>
                                                <option value="Damaged" ${item.asset_condition === 'Damaged' ? 'selected' : ''}>Damaged</option>
                                                <option value="Under Maintenance" ${item.asset_condition === 'Under Maintenance' ? 'selected' : ''}>Under Maintenance</option>
                                            </select>
                                        </td>
                                        <td class="text-center d-flex justify-content-center">
                                            <button type="button" id="edit_assets_settings_remarksBTN${item.id}"
                                                class="btn btn-success btn-sm"
                                                onclick="showRemarksModal(${item.id})" style="display:none;">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <input id="assets_settings_remarks_hidden_${item.id}" name="assets_settings_remarks_hidden_${item.id}" type="hidden">
                                            <button
                                            type="button"
                                            class="btn btn-warning btn-sm"
                                            style="${item.asset_condition === 'Damaged' ? 'display:block;' : 'display:none;'}"
                                            onclick="showRemarks(${item.id})">
                                            <i class="fa fa-sticky-note"></i>
                                        </button></td>
                                        <td >
                                            <select class="select select2" name="status[]" style="width:200px;">
                                                <option value="Available" ${item.status === 'Available' ? 'selected' : ''}>Available</option>
                                                <option value="Deployed" ${item.status === 'Deployed' ? 'selected' : ''}>Deployed</option>
                                                <option value="Return" ${item.status === 'Return' ? 'selected' : ''}>Return</option>
                                            </select>
                                        </td>
                                        <td>
                                        ${item.user?.personal_information
                                            ? item.user.personal_information.first_name + ' ' + item.user.personal_information.last_name
                                            : '-'}
                                        </td>
                                        <td>${item.deployed_date ? moment(item.deployed_date).format('MMM D, YYYY') : '-'}</td>
                                    </tr>
                                `;
                            tableBody.append(row);
                            $('.select2').select2();
                        });

                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            let $input = $('[name="' + field + '"]');
                            $input.addClass('is-invalid');
                            $input.next('.invalid-feedback').remove();
                            $input.after('<div class="invalid-feedback">' + messages[0] +
                                '</div>');
                        });
                    } else {
                        toastr.error('Something went wrong.');
                    }
                }
            });
        });

        let canceled = true;

        function checkCondition(selectElement, assetId, prevCondition) {
            let $select = $(selectElement);
            let selectedValue = $select.val();

            if (prevCondition !== "Damaged" && selectedValue === "Damaged") {
                canceled = true;
                $('#remarksAssetsSettingsId').val(assetId);
                let currentRemarks = $('#assets_settings_remarks_hidden_' + assetId).val();
                $('#remarksAssetsSettingsText').val(currentRemarks);
                $('#assetsSettingsRemarksModal').modal('show');
                let remarks = $('#remarksAssetsSettingsText').val().trim();

                $("#assetsSettingsRemarksModal")
                    .off("hidden.bs.modal")
                    .on("hidden.bs.modal", function() {
                        if (canceled && remarks === '') {
                            $select.val(prevCondition).trigger("change.select2");
                        }
                    });
            }
        }
        $('#saveAssetsSettingsRemarks').on('click', function() {
            let assetId = $('#remarksAssetsSettingsId').val();
            let remarks = $('#remarksAssetsSettingsText').val().trim();

            if (remarks !== '') {
                $('#assets_settings_remarks_hidden_' + assetId).val(remarks);
                canceled = false;
                $('#assetsSettingsRemarksModal').modal('hide');
                $('#edit_assets_settings_remarksBTN' + assetId).show();
            }
        });

        function showRemarksModal(assetId) {
            $('#remarksAssetsSettingsId').val(assetId);
            let currentRemarks = $('#assets_settings_remarks_hidden_' + assetId).val();
            $('#remarksAssetsSettingsText').val(currentRemarks);
            $('#assetsSettingsRemarksModal').modal('show');
        }

        function addNewItem() {
            let tableBody = document.getElementById('assetsConditionTableBody');
            let lastRow = tableBody.querySelector('tr:last-child td:first-child');
            let lastNumber = lastRow ? parseInt(lastRow.textContent) || 0 : 0;
            let nextNumber = lastNumber + 1;

            let newRow = `
                    <tr class="text-center">
                        <td>${nextNumber} <input type="hidden" name="new_order_no[]" value="${nextNumber}"></td>
                        <td>
                            <select class="select select2" name="new_condition[]">
                                <option value="New">New</option>
                                <option value="Good">Good</option>
                                <option value="Damaged">Damaged</option>
                                <option value="Under Maintenance">Under Maintenance</option>
                            </select>
                        </td>
                        <td></td>
                        <td >
                            <select class="select select2" name="new_status[]" style="width:100px;">
                                <option value="Available">Available</option>
                                <option value="Deployed">Deployed</option>
                                <option value="Return">Return</option>
                            </select>
                        </td>
                        <td>-</td>
                        <td>-</td>
                        <td><button class="btn btn-sm btn-danger" onclick="removeNewRow(this)">Remove</button></td>
                    </tr>
                `;

            tableBody.insertAdjacentHTML('beforeend', newRow);
            $('.select2').select2();
        }

        function removeNewRow(button) {
            button.closest('tr').remove();
            renumberRows();
        }

        function renumberRows() {
            const rows = document.querySelectorAll('#assetsConditionTableBody tr');
            rows.forEach((row, index) => {
                const numberCell = row.querySelector('td:first-child');
                const hiddenInput = numberCell.querySelector('input[name="new_order_no[]"]');
                let newNumber = index + 1;
                numberCell.childNodes[0].nodeValue = newNumber + " ";
                if (hiddenInput) {
                    hiddenInput.value = newNumber;
                }
            });
        }


        $('#assetsSettingsDetailsUpdateForm').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');
            var formData = form.serialize();

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toastr.success('Assets updated successfully!');
                    filter();
                    $('#edit_assetsCondition').modal('hide');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('An error occurred while updating the assets.');
                }
            });
        });




        $('#delete_assets').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var name = button.data('name');
            var modal = $(this);
            modal.find('#delete_assets_id').val(id);
            modal.find('#assetsPlaceholder').text(name);
        });

        function showRemarks(assetId) {
            $.get(`/employee-assets/${assetId}/remarks`, function(data) {
                $("#assetsSettings_conditionRemarksText").val(data.condition_remarks);
                $("#assetsSettingsViewRemarksModal").modal('show');
            });
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#addAssetsForm').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#add_assets').modal('hide');
                        $('#addAssetsForm')[0].reset();
                        $('#existingCategory').val('').trigger('change');
                        toastr.success('Asset added successfully!');
                        filter();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                let $input = $('[name="' + field + '"]');
                                $input.addClass('is-invalid');
                                $input.next('.invalid-feedback').remove();
                                $input.after('<div class="invalid-feedback">' +
                                    messages[0] + '</div>');
                            });
                        } else {
                            toastr.error('Something went wrong.');
                        }
                    }
                });
            });

            $('#existingCategory').on('change', function() {
                if ($(this).val() === 'new') {
                    $('#newCategoryInput').show().attr('required', true);
                } else {
                    $('#newCategoryInput').hide().val('').removeAttr('required');
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#editAssetsForm').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#edit_assets').modal('hide');
                        $('#editAssetsForm')[0].reset();
                        $('#edit_existingCategory').val('').trigger('change');
                        toastr.success('Asset updated successfully!');
                        filter();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                let $input = $('[name="' + field + '"]');
                                $input.addClass('is-invalid');
                                $input.next('.invalid-feedback').remove();
                                $input.after('<div class="invalid-feedback">' +
                                    messages[0] + '</div>');
                            });
                        } else {
                            toastr.error('Something went wrong.');
                        }
                    }
                });
            });

            $('#edit_existingCategory').on('change', function() {
                if ($(this).val() === 'new') {
                    $('#edit_newCategoryInput').show().attr('required', true);
                } else {
                    $('#edit_newCategoryInput').hide().val('').removeAttr('required');
                }
            });
        });
    </script>
    <script>
        $('#assetsConfirmDeleteBtn').on('click', function() {
            let assetId = $('#delete_assets_id').val();

            $.ajax({
                url: '/assets-settings/delete',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: assetId
                },
                success: function(response) {
                    $('#delete_assets').modal('hide');
                    toastr.success('Asset deleted successfully!');
                    filter();
                },
                error: function(xhr) {
                    toastr.error('Failed to delete asset.');
                }
            });
        });
    </script>
@endpush
