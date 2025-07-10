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
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Administration
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee Assets</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if(in_array('Export',$permission))
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
                        @endif
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

            <!-- Performance Indicator list -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h5 class="mb-0">Employee Assets</h5>
                        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3"> 
                        <div class="form-group me-2" style="max-width:200px;">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select" oninput="filter()">
                                <option value="" selected>All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="department_filter" id="department_filter" class="select2 form-select"
                                oninput="filter()">
                                <option value="" selected>All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="designation_filter" id="designation_filter" class="select2 form-select"
                                oninput="filter()">
                                <option value="" selected>All Designations</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                @endforeach
                            </select>
                        </div>  
                    </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="custom-datatable-filter table-responsive">
                            <table class="table datatable" id="user_permission_table">
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
                                    @if (in_array('Read', $permission))
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
        @component('components.modal-popup')
        @endcomponent
    @endsection

    @push('scripts')
       <script>
        $('#dateRange_filter').on('apply.daterangepicker', function(ev, picker) {
            filter();
        });

        function filter() {
            const dateRange = $('#dateRange_filter').val();
            const branch = $('#branch_filter').val();
            const department = $('#department_filter').val();
            const designation = $('#designation_filter').val();
            const status = $('#status_filter').val();

            $.ajax({
                url: '{{ route('employee-assets-filter') }}',
                type: 'GET',
                data: {
                    branch,
                    department,
                    designation,
                    dateRange,
                    status,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#employeeAssetsTableBody').html(response.html);
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
    function addEmployeeAssets(user) {

        const tableBody = document.getElementById("addEmployeeAssetsTableBody");
        tableBody.innerHTML = ''; 
 
        const assets = Array.isArray(user.employee_assets) ? user.employee_assets : [];

        assets.forEach((asset) => {
            const row = document.createElement("tr");
            
         const displayStatus = (status) => {
            return status === 'active' ? 'assigned' : status || '';
            };

        row.innerHTML = `
            <td>${asset.asset.name || ''} <input type="hidden" name="assets[]" value="${asset.asset.id || ''}"></td>
            <td class="text-center">${asset.asset.category.name || ''} <input type="hidden" name="category[]" value="${asset.asset.category.name || ''}"></td>
            <td class="text-center">${asset.quantity || 1} <input type="hidden" name="quantity[]" value="${asset.quantity || 1}"></td>
            <td class="text-center">${(!isNaN(Number(asset.price)) ? Number(asset.price) : 0).toFixed(2)} <input type="hidden" name="price[]" value="${(!isNaN(Number(asset.price)) ? Number(asset.price) : 0).toFixed(2)}"></td>
            <td class="text-center">${displayStatus(asset.status)} <input type="hidden" name="status[]" value="${displayStatus(asset.status)}"></td>
            <td class="text-center">${(!isNaN(Number(asset.total)) ? Number(asset.total) : 0).toFixed(2)} <input type="hidden" name="total[]" value="${(!isNaN(Number(asset.total)) ? Number(asset.total) : 0).toFixed(2)}"></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-asset-btn">Remove</button></td>
        `; 
         
            tableBody.appendChild(row);
        });
 
        tableBody.querySelectorAll('.remove-asset-btn').forEach(button => {
            button.addEventListener('click', function () {
                this.closest('tr').remove();
            });
        }); 
        $('#employee-assets-id').val(user.id);
        $('#add_employee_assets').modal('show');
    } 
        
    function fetchAvailableAssets() { 
        fetch('/employee-assets/list')  
            .then(response => response.json())
            .then(data => { 
                const assetSelect = document.getElementById('assetSelect');
                assetSelect.innerHTML = '<option disabled selected>Select an asset</option>';

                data.forEach(asset => {
                    const option = document.createElement('option');
                    option.value = asset.id;
                    option.textContent = asset.name;
                    assetSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching assets:', error);
            });
    }

    function openAddAssetModal() {
        fetchAvailableAssets();
        const modal = new bootstrap.Modal(document.getElementById('addEmployeeAssetModal'), {
            backdrop: 'static',
            keyboard: false
        });
        modal.show();
    } 
    </script>   
    <script> 
  function addAsset() {
    const assetSelect = document.getElementById('assetSelect');
    const assetId = assetSelect.value;

    if (!assetId) {
        alert('Please select an asset');
        return;
    }

    const assetName = assetSelect.options[assetSelect.selectedIndex].text;
    const quantity = document.getElementById('quantity').value || 1;

    $.ajax({
        url: `/get-asset-info/${assetId}`,
        method: 'GET',
    success: function (data) {
    const price = parseFloat(data.price) || 0;
    const category = data.category ?? 'N/A';
    const status = data.status ?? 'unknown';
    const total = price * quantity;

    const row = document.createElement('tr');
    row.innerHTML = `
        <td >${assetName} <input type="hidden" name="assets[]" value="${assetId}"></td>
        <td class="text-center" >${category} <input type="hidden" name="category[]" value="${category}"></td>
        <td class="text-center">${quantity} <input type="hidden" name="quantity[]" value="${quantity}"></td>
        <td class="text-center">${price.toFixed(2)} <input type="hidden" name="price[]" value="${price.toFixed(2)} "></td>
        <td class="text-center">${status} <input type="hidden" name="status[]" value="${status}"></td>
        <td class="text-center">${total.toFixed(2)}<input type="hidden" name="total[]" value="${total.toFixed(2)}"></td>
        <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-asset-btn">Remove</button></td>
    `; 
    const tableBody = document.getElementById('addEmployeeAssetsTableBody');
    tableBody.appendChild(row);

    row.querySelector('.remove-asset-btn').addEventListener('click', function () {
        this.closest('tr').remove();
    });
 
    const addAssetModal = bootstrap.Modal.getInstance(document.getElementById('addEmployeeAssetModal'));
    addAssetModal.hide();
    assetSelect.selectedIndex = 0;
    document.getElementById('quantity').value = 1;
}
,
        error: function () {
            alert('Failed to fetch asset details. Please try again.');
        }
    });
}
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
