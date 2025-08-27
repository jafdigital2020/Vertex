<?php $page = 'users'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee Assets History</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Assets Management
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee Assets History</li>
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
                        <h5 class="mb-0">Employee Assets History</h5>
                           <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3"> 
                        <div class="form-group me-2" style="max-width:200px;">
                            <select name="branch_filter" id="branch_filter" class="select2 form-select" oninput="filter()" style="width:150px;">
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
                        <div class="form-group">
                            <select name="status_filter" id="status_filter" class="select2 form-select" onchange="filter()">
                                <option value="" selected>All Status</option> 
                                <option value="Available">Available</option> 
                                <option value="Deployed">Deployed</option> 
                                <option value="Return">Return</option> 
                                <option value="For Disposal">For Disposal</option>
                                <option value="Disposed">Disposed</option>
                            </select>
                        </div>
                            <div class="form-group">
                            <select name="condition_filter" id="condition_filter" class="select2 form-select" onchange="filter()">
                                <option value="" selected>All Conditions</option>  
                                <option value="Brand New">Brand New</option> 
                                <option value="Good Working Condition">Good</option>  
                                <option value="Under Maintenance">Under Maintenance</option>  
                                <option value="Defective">Defective</option>
                                <option value="Unserviceable">Unserviceable</option>
                            </select>    
                            </div> 
                    </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="custom-datatable-filter table-responsive">
                            <table class="table datatable" id="assetsDetailsHistoryTable">
                                <thead class="thead-light">
                                    <tr> 
                                        <th class="text-center">Asset</th>
                                        <th class="text-center">Item Name</th>
                                        <th class="text-center">Branch</th>
                                        <th class="text-center">Category</th>
                                        <th class="text-center">Item No</th>  
                                        <th class="text-center">Deployed To</th> 
                                        <th class="text-center">Date Deployed</th> 
                                        <th class="text-center">Condition</th> 
                                        <th class="text-center">Condition Remarks</th> 
                                        <th class="text-center">Status</th> 
                                        <th class="text-center">Process</th> 
                                        <th class="text-center">Processed By</th> 
                                        <th class="text-center">Date Processed</th> 
                                        <th class="text-center">Created By</th> 
                                        <th class="text-center">Date Created</th> 
                                    </tr>
                                </thead>
                                <tbody id="assetsDetailsHistoryTableBody">
                                    @if (in_array('Read', $permission))
                                     @foreach ($assetsHistory as $asset)
                                    <tr class="text-center">
                                        <td>{{ $asset->assetDetail->assets->name ?? '' }}</td>
                                        <td>{{ $asset->assetDetail->assets->item_name ?? '' }}</td>
                                        <td>{{$asset->assetDetail->assets->branch->name ?? ''}}</td>
                                        <td>{{ $asset->assetDetail->assets->category->name ?? '' }}</td>
                                        <td>{{ $asset->item_no ?? '' }}</td>
                                        <td>{{ $asset->deployedToEmployee->personalInformation->first_name ?? '' }} {{ $asset->deployedToEmployee->personalInformation->last_name ?? '' }} </td>
                                        <td>{{ $asset->deployed_date ?? '' }}</td>
                                        <td>{{ $asset->condition ?? '' }}</td>
                                        <td>{{ $asset->condition_remarks ?? '' }}</td>
                                        <td>{{ $asset->status ?? '' }}</td>
                                        <td>{{ $asset->process ?? '' }}</td>
                                        <td>{{ $asset->updatedByUser->personalInformation->first_name ?? '' }} {{ $asset->updatedByUser->personalInformation->last_name ?? '' }}</td>
                                        <td>{{ $asset->updated_at ?? '' }}</td>
                                        <td>{{ $asset->createdByUser->personalInformation->first_name ?? '' }} {{ $asset->createdByUser->personalInformation->last_name ?? '' }}</td>
                                        <td>{{ $asset->created_at ?? '' }}</td>
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
    @endsection

    @push('scripts') 
    <script>
        function filter() { 
        const branch = $('#branch_filter').val();
        const department = $('#department_filter').val();
        const designation = $('#designation_filter').val();
        const status = $('#status_filter').val();
        const condition = $('#condition_filter').val();

        $.ajax({
            url: '{{ route('employee-assets-history-filter') }}',
            type: 'GET',
            data: {
                branch,
                department,
                designation,
                condition,
                status,
            },
            success: function (response) {
                if (response.status === 'success') { 
                    $('#assetsDetailsHistoryTable').DataTable().destroy(); 
                    $('#assetsDetailsHistoryTableBody').html(response.html); 
                    $('#assetsDetailsHistoryTable').DataTable();
                         
                } else {
                    toastr.error(response.message || 'Something went wrong.');
                }
            },
            error: function (xhr) {
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
