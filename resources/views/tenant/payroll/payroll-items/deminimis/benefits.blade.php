<?php $page = 'de-minimis-benefits'; ?>
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
                                HR
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Payroll Items</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
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
                    <a href="{{ route('sss-contributionTable') }}" class="btn btn-white  border me-2">SSS
                        Contribution</a>
                    <a href="{{ route('philhealth') }}" class="btn btn-white border me-2">PhilHealth</a>
                    <a href="{{ route('withholding-taxTable') }}" class="btn btn-white border me-2">Withholding Tax</a>
                    <a href="{{ route('ot-table') }}" class="btn btn-white border">OT Table</a>
                    <a href="{{ route('de-minimis-benefits') }}" class="btn btn-white active border">De Minimis</a>
                    <a href="{{ route('earnings') }}" class="btn btn-white border">Earnings</a>
                    <a href="{{ route('deductions') }}" class="btn btn-white border">Deductions</a>
                </div>
                <div class="mb-2">
                    <a href="{{ route('de-minimis-user') }}" class="btn btn-primary d-flex align-items-center"><i
                            class="ti ti-eye me-2"></i>View Employee's
                        Deminimis</a>
                </div>
            </div>

            <!-- /Breadcrumb -->

            <!-- Payroll list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>De minimis benefits</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">

                        <div class="dropdown">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
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
                            </ul>
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
                                    <th>Name</th>
                                    <th>Max Amount</th>
                                    <th>Frequency</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($deMinimis as $dmb)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <h6 class="fs-14 fw-medium text-gray-9">
                                                {{ ucwords(str_replace('_', ' ', $dmb->name)) }}</h6>
                                        </td>
                                        <td>{{ number_format($dmb->maximum_amount, 2) }}</td>
                                        <td>
                                            {{ ucfirst($dmb->frequency) }}
                                        </td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_payroll"><i class="ti ti-edit"></i></a>
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i
                                                        class="ti ti-trash"></i></a>
                                            </div>
                                        </td>
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

    @component('components.modal-popup')
    @endcomponent
@endsection
