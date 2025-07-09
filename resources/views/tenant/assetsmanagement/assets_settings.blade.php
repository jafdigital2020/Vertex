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
                                Administration
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Assets Settings</li>
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
                    @if (in_array('Create', $permission))
                    <div class="mb-2"> 
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_assets"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            Asset</a> 
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
                            <div class="form-group">
                                <select name="status_filter" id="status_filter" class="select2 form-select"
                                    onchange="user_filter()">
                                    <option value="" selected>All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="broken">Broken</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="retired">Retired</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <select name="sortby_filter" id="sortby_filter" class="select2 form-select"
                                    onchange="user_filter()">
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
                            <table class="table datatable" id="user_permission_table">
                                <thead class="thead-light">
                                    <tr> 
                                        <th>Name</th>
                                        <th class="text-center">Description</th> 
                                        <th class="text-center">Category</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-center">Status</th>
                                        @if (in_array('Update', $permission) || in_array('Delete',$permission))
                                            <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (in_array('Read', $permission))
                                        @foreach ($assets as $asset)
                                            <tr>  
                                                <td>{{ $asset->name ?? null }}</span>
                                                </td>
                                                <td class="text-center">
                                                    {{ $asset->description ?? 'No Specified Access' }}
                                                </td>
                                               <td class="text-center">
                                                    {{$asset->category->name }}
                                                </td>
                                               <td class="text-center">
                                                    {{$asset->price}}
                                                </td>
                                               <td class="text-center">
                                                    @php
                                                        $statusColors = [
                                                            'active' => 'success',
                                                            'broken' => 'danger',
                                                            'maintenance' => 'warning',
                                                            'retired' => 'secondary',
                                                        ];
                                                        $color = $statusColors[$asset->status ?? 'retired'] ?? 'secondary';
                                                    @endphp

                                                    <span class="badge bg-{{ $color }} text-capitalize">
                                                        {{ $asset->status ?? 'retired' }}
                                                    </span>
                                                </td>

                                                @if (in_array('Update', $permission))
                                                    <td class="text-center">
                                                        <div class="action-icon d-inline-flex">
                                                            <a href="#" class="me-2"
                                                                  ><i
                                                                    class="ti ti-shield"></i></a>
                                                            <a href="#" class="me-2"
                                                               ><i
                                                                class="ti ti-edit"></i></a>
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
            $(function() {
            $('#existingCategory').on('change', function() {
                if ($(this).val() === 'new') {
                $('#newCategoryInput').show();
                } else {
                $('#newCategoryInput').hide().val('');
                }
            });
            });
        </script>
    @endpush
