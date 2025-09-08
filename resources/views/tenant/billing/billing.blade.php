<?php $page = 'bills-payment'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Bills & Payment</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Bills & Payment</li>
                        </ol>
                    </nav>

                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="row">
                <!-- Free Plan Card -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Plan</h5>
                            <p class="text-muted">Free for individuals with up to 1000 records</p>

                            <div class="d-flex align-items-center mb-3">
                                <h3 class="mb-0 me-2">₱{{ $subscription->amount_paid ?? '0.00' }}</h3>
                                <span class="text-muted">/ {{ $subscription->billing_cycle ?? '-' }}</span>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>120 / {{ $subscription->plan->employee_limit ?? '0' }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 12%"></div>
                                </div>
                            </div>

                            {{-- <div class="d-flex justify-content-end">
                                <button class="btn btn-dark">Upgrade</button>
                            </div> --}}
                        </div>
                    </div>
                </div>

                <!-- Payment Method Card -->
                {{-- <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Payment Method</h5>
                            <p class="text-muted">Change how you pay for your plan</p>

                            <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fab fa-cc-visa fs-4 me-3"></i>
                                    <div>
                                        <div>•••• •••• •••• 3627</div>
                                        <small class="text-muted">Expiry 02/2026</small>
                                    </div>
                                </div>
                                <button class="btn btn-outline-primary btn-sm">Change</button>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>

            <!-- Invoices Card -->
            <div class="card mt-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Invoices</h5>
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-3">Access all your previous invoices.</span>
                        <button class="btn btn-outline-primary btn-sm">Download All</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Plan</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>📄 Invoice #012 - Dec 2023</td>
                                    <td>Dec 1, 2023</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                    <td>$0.00</td>
                                    <td>Free Plan</td>
                                    <td><a href="#" class="text-primary">Download</a></td>
                                </tr>
                                <tr>
                                    <td>📄 Invoice #011 - Nov 2023</td>
                                    <td>Nov 1, 2023</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                    <td>$0.00</td>
                                    <td>Free Plan</td>
                                    <td><a href="#" class="text-primary">Download</a></td>
                                </tr>
                                <tr>
                                    <td>📄 Invoice #010 - Oct 2023</td>
                                    <td>Oct 1, 2023</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                    <td>$0.00</td>
                                    <td>Free Plan</td>
                                    <td><a href="#" class="text-primary">Download</a></td>
                                </tr>
                                <tr>
                                    <td>📄 Invoice #009 - Sep 2023</td>
                                    <td>Sep 1, 2023</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                    <td>$0.00</td>
                                    <td>Free Plan</td>
                                    <td><a href="#" class="text-primary">Download</a></td>
                                </tr>
                                <tr>
                                    <td>📄 Invoice #008 - Aug 2023</td>
                                    <td>Aug 1, 2023</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                    <td>$0.00</td>
                                    <td>Free Plan</td>
                                    <td><a href="#" class="text-primary">Download</a></td>
                                </tr>
                                <tr>
                                    <td>📄 Invoice #007 - Jul 2023</td>
                                    <td>Jul 1, 2023</td>
                                    <td><span class="badge bg-danger">Unpaid</span></td>
                                    <td>$20.00</td>
                                    <td>Standard</td>
                                    <td><a href="#" class="text-primary">Download</a></td>
                                </tr>
                                <tr>
                                    <td>📄 Invoice #006 - Jun 2023</td>
                                    <td>Jun 1, 2023</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                    <td>$20.00</td>
                                    <td>Standard</td>
                                    <td><a href="#" class="text-primary">Download</a></td>
                                </tr>
                                <tr>
                                    <td>📄 Invoice #005 - May 2023</td>
                                    <td>May 1, 2023</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                    <td>$20.00</td>
                                    <td>Standard</td>
                                    <td><a href="#" class="text-primary">Download</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-light border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <small>Showing 1 to 8 of 10 entries</small>
                            </div>
                            <nav aria-label="Invoice pagination">
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                            <i class="ti ti-chevron-left"></i>
                                        </a>
                                    </li>
                                    <li class="page-item active" aria-current="page">
                                        <a class="page-link" href="#">1</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">2</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">
                                            <i class="ti ti-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup')
    @endcomponent
@endsection
