<?php $page = 'users'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Assets History</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Assets Management
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Assets History</li>
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
                        <h5 class="mb-0">Assets History</h5>
                        <div class="d-flex flex-wrap gap-3">   
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
                            <table class="table datatable" id="assetsHistoryTable">
                                <thead class="thead-light">
                                    <tr> 
                                        <th class="text-center">Asset</th>
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
                                <tbody id="assetsHistoryTableBody">
                                    @if (in_array('Read', $permission))
                                     @foreach ($assetsHistory as $asset)
                                    <tr class="text-center">
                                        <td>{{ $asset->assetDetail->assets->name ?? '' }}</td>
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
    @endpush
