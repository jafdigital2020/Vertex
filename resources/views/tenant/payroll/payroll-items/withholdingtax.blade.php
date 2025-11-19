<?php $page = 'withholdingtax-table'; ?>
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
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Payrpll
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Payroll Items</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if(in_array('Export',$permission))
                    <div class="mb-2">
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
                    @endif
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
                    <a href="{{ route('withholding-taxTable') }}" class="btn btn-white active border me-2">Withholding
                        Tax</a>
                    <a href="{{ route('ot-table') }}" class="btn btn-white border me-2">OT Table</a>
                    <a href="{{ route('custom-ot-rate') }}" class="btn btn-white border me-2">Custom OT</a>
                    <a href="{{ route('de-minimis-benefits') }}" class="btn btn-white border me-2">De Minimis</a>
                    <a href="{{ route('earnings') }}" class="btn btn-white border me-2">Earnings</a>
                    <a href="{{ route('deductions') }}" class="btn btn-white border me-2">Deductions</a>
                    <a href="{{ route('allowance') }}" class="btn btn-white border me-2">Allowance</a>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- Payroll list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Withholding Tax</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                       <div class="form-group">
                            <select id="sort_by" name="sort_by" class="select form-select select2" onchange="filter()">
                                <option value="" selected>Sort by</option>
                                <option value="recent">Recently Added</option>
                                <option value="asc">Ascending</option>
                                <option value="desc">Descending</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th class="text-center">Frequency</th>
                                    <th class="text-center">Range From</th>
                                    <th class="text-center">Range To</th>
                                    <th class="text-center">Fix</th>
                                    <th class="text-center">Rate</th>
                                    <th class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="withholdingTableBody">
                                @foreach ($withholdingTaxes as $tax)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td class="text-center">{{ ucfirst($tax->frequency) }}</td>
                                        <td class="text-center">{{ number_format($tax->range_from, 2) }}</td>
                                        <td class="text-center">{{ $tax->range_to !== null ? number_format($tax->range_to, 2) : '—' }}</td>
                                        <td class="text-center">{{ number_format($tax->fix, 2) }}</td>
                                        <td class="text-center">{{ number_format($tax->rate, 2) }}%</td>
                                        <td class="text-center"></td>
                                    </tr>
                                @endforeach
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
    @push('scripts')
        <script>
            function filter(){
                var sort_by = $('#sort_by').val();
                $.ajax({
                    url: '{{ route('withholding-taxTable-filter') }}',
                    type: 'GET',
                    data: {
                        sort_by: sort_by
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#withholdingTableBody').html(response.html);
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
    @endpush
    @component('components.modal-popup')
    @endcomponent
@endsection
