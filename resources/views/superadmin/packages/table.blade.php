<?php $page = 'packages-table'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Packages</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Superadmin
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Packages List</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="me-2 mb-2">
                        <div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
                            <a href="{{url('packages')}}" class="btn btn-icon btn-sm active bg-primary text-white me-1"><i class="ti ti-list-tree"></i></a>
                            <a href="{{url('packages-grid')}}" class="btn btn-icon btn-sm"><i class="ti ti-layout-grid"></i></a>
                        </div>
                    </div>
                    <div class="me-2 mb-2">
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                <i class="ti ti-file-export me-1"></i>Export
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_plans" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Plan</a>
                    </div>
                    <div class="ms-2 head-icons">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>  
            <div class="row"> 
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <p class="fs-12 fw-medium mb-1 text-truncate">Total Plans</p>
                                   <h4>{{ sprintf('%02d', $packages->count()) }}</h4> 
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-lg bg-primary flex-shrink-0">
                                    <i class="ti ti-box fs-16"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div> 
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <p class="fs-12 fw-medium mb-1 text-truncate">Active Plans</p>
                                  <h4>{{ sprintf('%02d', $packages->where('status', 1)->count()) }}</h4> 
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-lg bg-success flex-shrink-0">
                                    <i class="ti ti-activity-heartbeat fs-16"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Total Plans -->

                <!-- Inactive Plans -->
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <p class="fs-12 fw-medium mb-1 text-truncate">Inactive Plans</p>
                                   <h4>{{ sprintf('%02d', $packages->where('status', 0)->count()) }}</h4> 
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-lg bg-danger flex-shrink-0">
                                    <i class="ti ti-player-pause fs-16"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Inactive Companies -->

                <!-- No of Plans  -->
                <div class="col-lg-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center overflow-hidden">
                                <div>
                                    <p class="fs-12 fw-medium mb-1 text-truncate">No of Plan Types</p>
                                    <h4>02</h4>
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-lg bg-skyblue flex-shrink-0">
                                    <i class="ti ti-mask fs-16"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /No of Plans --> 
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Plan List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange" placeholder="dd/mm/yyyy - dd/mm/yyyy">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                Select Plan
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Monthly</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Yearly</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                Select Status
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Inactive</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
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
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr  >
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Plan Name</th>
                                    <th>Plan Type</th>
                                    <th>Employee <br> Limit</th>
                                    <th>Monthly <br>Subscribers</th>
                                    <th>Monthly <br> Pricing</th>
                                    <th>Yearly <br>Subscribers</th>
                                    <th>Yearly <br>Pricing</th> 
                                    <th>Status</th>
                                    <th>Edit</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach ($packages as $package)
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td>
                                        <h6 class="fw-medium"><a href="#">{{$package->package_name}}</a></h6>
                                    </td> 
                                    <td>
                                        {{$package->pack_type->package_type}}
                                    </td> 
                                    <td>{{$package->employee_limit == -1 ? 'Unlimited' : $package->employee_limit }}</td>
                                    <td>{{$package->monthly_subscribers}}</td>
                                    <td>{{$package->monthly_pricing == -1 ? 'Custom Pricing' :  number_format($package->monthly_pricing, 2)}}</td>
                                    <td>{{$package->yearly_subscribers}}</td>
                                    <td>{{$package->yearly_pricing == -1 ? 'Custom Pricing' :  number_format($package->monthly_pricing, 2)  }}</td> 
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-sm">
                                            <i class="ti ti-point-filled me-1"></i>Active
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex">
                                            <a href="#" class="me-2" onclick="packageEdit({{$package->id}})"><i class="ti ti-edit"></i></a>  
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

        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2014 - 2025 &copy; SmartHR.</p>
            <p>Designed &amp; Developed By <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>

    </div>
     <div class="modal fade" id="edit_packageModal">
        <div class="modal-dialog modal-dialog-centered modal-lg w-100">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title ">Edit Package</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form action="{{url('experience-level')}}">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="form-group col-7">
                                <label for="">Package Name:</label>
                                <input type="text" name="package_name" id="package_name" class="form-control text-sm">
                            </div>
                             <div class="form-group col-5">
                                <label for="">Package Type:</label>
                                <select name="package_type" class="select2 form-control" id="package_type">
                                    <option value="" selected disabled>Select</option>
                                    @foreach ($package_type as $pType)
                                        <option value="{{$pType->id}}">{{$pType->package_type}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div> 
 
    <script> 
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function packageEdit(id) {
        $.ajax({
            url: '{{ route("superadmin-getpackageDetails") }}', 
            method: 'GET',
            data: { package_id: id },
            success: function(response) { 
                $('#edit_packageModal').modal('show'); 
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Failed to get package details');
            }
        });
    }
</script>
    @component('components.modal-popup')
    @endcomponent
@endsection
