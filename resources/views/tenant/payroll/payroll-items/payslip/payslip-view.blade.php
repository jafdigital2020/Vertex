<?php $page = 'payslip'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Payslip</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Payslip</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="mb-2">
                        <a href="#" class="btn btn-dark d-flex align-items-center"><i
                                class="ti ti-download me-2"></i>Download</a>
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

            <!-- Invoices -->
            <div class="printable-area">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row justify-content-between align-items-center border-bottom mb-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="mb-2">
                                                @if($payslips->user && $payslips->user->employmentDetail && $payslips->user->employmentDetail->branch && $payslips->user->employmentDetail->branch->branch_logo)
                                                    <img src="{{ asset('storage/' . $payslips->user->employmentDetail->branch->branch_logo) }}" class="img-fluid" alt="logo" style="max-width: 120px; height: auto;">
                                                @else
                                                    <img src="{{ URL::asset('build/img/Timora-logo.png') }}" class="img-fluid" alt="logo" style="max-width: 120px; height: auto;">
                                                @endif
                                            </div>
                                            <p>{{ $payslips->user->employmentDetail->branch->location ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class=" text-end mb-3">
                                            <h5 class="text-gray mb-1">Payslip No <span class="text-primary"> #PS4283</span>
                                            </h5>
                                            <p class="fw-medium">Salary Month : <span class="text-dark">October 2024</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row border-bottom align-items-center mb-3">
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <p class="text-dark mb-2 fw-semibold">From</p>
                                            <div>
                                                <h4 class="mb-1">XYZ Technologies</h4>
                                                <p class="mb-1">2077 Chicago Avenue Orosi, CA 93647</p>
                                                <p class="mb-1">Email : <span class="text-dark">xyztech@example.com</span>
                                                </p>
                                                <p>Phone : <span class="text-dark">+1 987 654 3210</span></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <p class="text-dark mb-2 fw-semibold">To</p>
                                            <div>
                                                <h4 class="mb-1">Anthony Lewis</h4>
                                                <p class="mb-1">Web Designer</p>
                                                <p class="mb-1">Email : <span class="text-dark">anthony@example.com</span>
                                                </p>
                                                <p>Phone : <span class="text-dark">+1 458 268 4738</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="text-center mb-4">Payslip for the moth of October 2024</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="list-group mb-3">
                                                <div class="list-group-item bg-light p-3 border-bottom-0">
                                                    <h6>Earnings</h6>
                                                </div>
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <p class="mb-0">Basic Salary</p>
                                                        <h6 class="fw-medium">$3000</h6>
                                                    </div>
                                                </div>
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <p class="mb-0">House Rent Allowance (H.R.A.)</p>
                                                        <h6 class="fw-medium">$1000</h6>
                                                    </div>
                                                </div>
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <p class="mb-0">Conveyance</p>
                                                        <h6 class="fw-medium">$200</h6>
                                                    </div>
                                                </div>
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <p class="mb-0">Other Allowance</p>
                                                        <h6 class="fw-medium">$100</h6>
                                                    </div>
                                                </div>
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <p class="mb-0">Total Earnings</p>
                                                        <h6 class="fw-medium">$4300</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="list-group mb-3">
                                                <div class="list-group-item bg-light p-3 border-bottom-0">
                                                    <h6>Deductions</h6>
                                                </div>
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <p class="mb-0">Tax Deducted at Source (T.D.S.)</p>
                                                        <h6 class="fw-medium">$200</h6>
                                                    </div>
                                                </div>
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <p class="mb-0">Provident Fund</p>
                                                        <h6 class="fw-medium">$300</h6>
                                                    </div>
                                                </div>
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <p class="mb-0">ESI</p>
                                                        <h6 class="fw-medium">$150</h6>
                                                    </div>
                                                </div>
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <p class="mb-0">Loan</p>
                                                        <h6 class="fw-medium">$50</h6>
                                                    </div>
                                                </div>
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <p class="mb-0">Total Earnings</p>
                                                        <h6 class="fw-medium">$700</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <p>Net Salary : <span class="text-gray-9 fw-medium"> $3600(Three thousand six
                                                hundred only)</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Invoices -->
        </div>

        @include('layout.partials.footer-company')

    </div>
@endsection
