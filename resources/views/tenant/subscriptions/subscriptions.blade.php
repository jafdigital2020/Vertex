<?php $page = 'subscriptions'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Subscription & Plans</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Subscription
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Manage Subscription</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- Current Plan Card -->
            @if ($subscription)
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                        style="width:48px; height:48px;">
                                        <i class="ti ti-package fs-20 text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-white">Your Current Plan</h5>
                                    <small class="opacity-75">{{ $subscription->plan->name ?? 'N/A' }} -
                                        {{ ucfirst($subscription->billing_cycle ?? 'monthly') }} Billing</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0 text-white">₱{{ number_format($subscription->plan->price ?? 0, 2) }}</h3>
                                <small class="opacity-75">per {{ $subscription->billing_cycle ?? 'month' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="col-lg-4 col-md-6">
                                <div class="card text-white position-relative overflow-hidden"
                                    style="border-radius:10px; background: linear-gradient(135deg, #0f8b8d 0%, #0b6b67 100%); min-height:120px;">
                                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                                        <div class="me-3" style="z-index:3;">
                                            <p class="fs-12 fw-medium mb-1 text-white-75">Total Employee</p>
                                            <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                                {{ str_pad($summaryData['users_current'] ?? 0, 2, '0', STR_PAD_LEFT) }}
                                            </h2>
                                            <small class="text-white-75">Employees</small>
                                        </div>

                                        <!-- Right icon circle group -->
                                        <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                            <div
                                                style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                                <i class="ti ti-users" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                            </div>
                                            <div
                                                style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                                <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                                    <i class="ti ti-users" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="card text-white position-relative overflow-hidden"
                                    style="border-radius:10px; background: linear-gradient(135deg, #a33658 0%, #8b2c48 100%); min-height:120px;">
                                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                                        <div class="me-3" style="z-index:3;">
                                            <p class="fs-12 fw-medium mb-1 text-white-75">User Limit</p>
                                            <h2 class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                                {{ str_pad($summaryData['users_limit'] ?? 0, 2, '0', STR_PAD_LEFT) }}
                                            </h2>
                                            <small class="text-white-75">Plan Capacity</small>
                                        </div>

                                        <!-- Right icon circle group -->
                                        <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                            <div
                                                style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                                <i class="ti ti-user-check" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                            </div>
                                            <div
                                                style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                                <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                                    <i class="ti ti-user-check" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="card text-white position-relative overflow-hidden"
                                     style="border-radius:10px; background: linear-gradient(135deg, #ed7464 0%, #f9c6b8 100%); min-height:120px;">
                                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                                        <div class="me-3" style="z-index:3;">
                                            <p class="fs-12 fw-medium mb-1 text-white-75">Renewal Date</p>
                                            <h2 id="renewalDate" class="mb-1 fw-bold text-white mt-3" style="font-size:28px;">
                                                {{ \Carbon\Carbon::parse($summaryData['renewal_date'] ?? now())->format('M d, Y') }}
                                            </h2>
                                            <small class="text-white-75">Next Renewal</small>
                                        </div>

                                        <!-- Right icon circle group -->
                                        <div style="position:relative; width:110px; height:110px; flex-shrink:0; z-index:2;">
                                            <div style="position:absolute; width:140px; height:140px; right:-40px; top:-30px; display:flex; align-items:center; justify-content:center;">
                                                <i class="ti ti-calendar" style="font-size:90px; color:rgba(255,255,255,0.07);"></i>
                                            </div>
                                            <div style="position:absolute; right:-45px; bottom:-45px; width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; z-index:4;">
                                                <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                                                    <i class="ti ti-calendar" style="font-size:20px;color:rgba(255,255,255,0.95);"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endif

            <!-- Available Plans Section -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <h5 class="mb-0">
                            <i class="ti ti-package me-2 text-primary"></i>Available Plans
                        </h5>

                        {{-- Billing Cycle Toggle --}}
                        <div class="d-flex align-items-center">
                            <span class="me-3 fw-medium" id="billing_cycle_label_monthly">Monthly</span>
                            <div class="form-check form-switch form-check-lg">
                                <input class="form-check-input" type="checkbox" role="switch" id="billing_cycle_toggle"
                                    style="cursor: pointer; width: 3.5rem; height: 1.75rem;">
                            </div>
                            <span class="ms-3 fw-medium" id="billing_cycle_label_yearly">
                                Yearly <span class="badge bg-success ms-1">Save more!</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="available_plans_container" class="row g-4">
                        <!-- Plans will be dynamically inserted here -->
                    </div>
                </div>
            </div>

            <!-- Invoice History -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="ti ti-receipt me-2 text-primary"></i>Invoice History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Status</th>

                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoices as $inv)
                                    <tr>
                                        <td>
                                            <a href="#" class="text-primary invoice-details-btn"
                                                data-bs-toggle="modal" data-bs-target="#view_invoice"
                                                data-invoice-id="{{ $inv->id }}"
                                                data-invoice-number="{{ $inv->invoice_number }}"
                                                data-invoice-type="{{ $inv->invoice_type ?? 'subscription' }}"
                                                data-amount-due="{{ $inv->amount_due }}"
                                                data-amount-paid="{{ $inv->amount_paid }}"
                                                data-subscription-amount="{{ $inv->subscription_amount ?? $inv->amount_due }}"
                                                data-license-overage-count="{{ $inv->license_overage_count ?? 0 }}"
                                                data-license-overage-amount="{{ $inv->license_overage_amount ?? 0 }}"
                                                data-license-overage-rate="{{ $inv->license_overage_rate ?? 49 }}"
                                                data-implementation-fee="{{ $inv->implementation_fee ?? 0 }}"
                                                data-vat-percentage="{{ $inv->calculated_vat_percentage ?? 12 }}"
                                                data-vat-amount="{{ $inv->calculated_vat_amount ?? 0 }}"
                                                data-subtotal="{{ $inv->calculated_subtotal ?? 0 }}"
                                                data-currency="{{ $inv->currency }}"
                                                data-due-date="{{ $inv->due_date }}" data-status="{{ $inv->status }}"
                                                data-period-start="{{ $inv->period_start }}"
                                                data-period-end="{{ $inv->period_end }}"
                                                data-issued-at="{{ $inv->issued_at }}"
                                                data-bill-to-name="{{ $inv->tenant->tenant_name ?? 'N/A' }}"
                                                data-bill-to-address="{{ $inv->tenant->tenant_address ?? 'N/A' }}"
                                                data-bill-to-email="{{ $inv->tenant->tenant_email ?? 'N/A' }}"
                                                data-plan="{{ $inv->invoice_type === 'plan_upgrade' && $inv->upgradePlan ? $inv->upgradePlan->name : $inv->subscription->plan->name ?? 'N/A' }}"
                                                data-current-plan="{{ $inv->subscription->plan->name ?? 'N/A' }}"
                                                data-billing-cycle="{{ $inv->invoice_type === 'plan_upgrade' && $inv->billing_cycle ? $inv->billing_cycle : $inv->subscription->billing_cycle ?? 'N/A' }}">
                                                {{ $inv->invoice_number }}

                                                @if (($inv->invoice_type ?? 'subscription') === 'plan_upgrade')
                                                    <span class="badge bg-success ms-1">Plan Upgrade</span>
                                                @elseif(($inv->invoice_type ?? 'subscription') === 'subscription')
                                                    <span class="badge bg-primary ms-1">Subscription</span>
                                                @endif
                                            </a>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($inv->issued_at)->format('Y-m-d') }}</td>
                                        <td>₱{{ number_format($inv->amount_due ?? 0, 2) }}</td>
                                        <td>
                                            @if (($inv->invoice_type ?? 'subscription') === 'plan_upgrade')
                                                Plan Upgrade: {{ $inv->upgradePlan->name ?? 'Plan Upgrade' }}
                                                @if ($inv->billing_cycle)
                                                    <span
                                                        class="badge bg-primary ms-1">{{ ucfirst($inv->billing_cycle) }}</span>
                                                @endif
                                                <br><small class="text-info">
                                                    <i class="ti ti-arrow-up me-1"></i>
                                                    Upgrading from {{ $inv->subscription->plan->name ?? 'Current Plan' }}
                                                    ({{ ucfirst($inv->subscription->billing_cycle ?? 'N/A') }})
                                                </small>
                                            @else
                                                {{ $inv->subscription->plan->name ?? 'Subscription' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($inv->status === 'paid')
                                                <span class="badge bg-success">
                                                    <i class="ti ti-check me-1"></i>Paid
                                                </span>
                                            @elseif($inv->status === 'pending')
                                                <span class="badge bg-warning">
                                                    <i class="ti ti-clock me-1"></i>Pending
                                                </span>
                                            @elseif($inv->status === 'failed')
                                                <span class="badge bg-danger">
                                                    <i class="ti ti-x me-1"></i>Failed
                                                </span>
                                            @endif
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="ti ti-inbox fs-2 text-muted"></i>
                                            <p class="text-muted mb-0">No invoices found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($invoices->hasPages())
                        <div class="card-footer bg-light border-top">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>

        @include('layout.partials.footer-company')

    </div>

    <!-- View Invoice Modal -->
    <div class="modal fade" id="view_invoice">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body p-5">

                    <div class="row justify-content-between align-items-center mb-3">
                        <div class="col-sm-6">
                            <img src="{{ URL::asset('build/img/JAF-LOGO.png') }}" class="inv-logo" alt="Logo"
                                style="max-width: 150px;">
                        </div>
                        <div class="col-sm-6 text-end">
                            <h4>Invoice</h4>
                            <p id="inv-number" class="mb-0"></p>
                        </div>
                    </div>

                    <div class="row mb-3 d-flex justify-content-between">
                        <div class="col-sm-6">
                            <h6>Invoice From:</h6>
                            <p class="mb-0">JAF Digital Group Inc.</p>
                            <p class="mb-0">Unit D 49th Floor PBCom Tower, 6795 Ayala Avenue,</p>
                            <p class="mb-0">corner V.A. Rufino St, Makati City, Metro Manila, Philippines</p>
                            <p class="mb-0">support@timora.ph</p>
                        </div>
                        <div class="col-sm-6 text-end">
                            <h6>Invoice To:</h6>
                            <p class="mb-0" id="inv-to-name">-</p>
                            <p class="mb-0" id="inv-to-address">-</p>
                            <p class="mb-0" id="inv-to-email">-</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <p><strong>Issue Date:</strong> <span id="inv-issued-at">-</span></p>
                        </div>
                        <div class="col-sm-6 text-end">
                            <p><strong>Due Date:</strong> <span id="inv-due-date">-</span></p>
                        </div>
                    </div>

                    <!-- Invoice Items Table -->
                    <div class="mb-4">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Period</th>
                                    <th class="qty-rate-column">Quantity</th>
                                    <th class="qty-rate-column">Rate</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="inv-items">
                                <!-- Will be populated dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals Section -->
                    <div class="row mb-3 d-flex justify-content-between">
                        <div class="col-sm-6"></div>
                        <div class="col-sm-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td class="text-end" id="inv-subtotal">-</td>
                                </tr>
                                <tr>
                                    <td><strong>VAT (<span id="inv-vat-percentage">12</span>%):</strong></td>
                                    <td class="text-end" id="inv-vat-amount">-</td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Total Amount:</strong></td>
                                    <td class="text-end"><strong id="inv-total-amount">-</strong></td>
                                </tr>
                                <tr>
                                    <td>Amount Paid:</td>
                                    <td class="text-end" id="inv-amount-paid">-</td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Balance:</strong></td>
                                    <td class="text-end"><strong id="inv-balance">-</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="card border mb-0">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Terms & Conditions</h6>
                            <ul class="list-unstyled">
                                <li><i class="ti ti-check text-success me-2"></i>Payment is due within 7 days of invoice
                                    date</li>
                                <li><i class="ti ti-check text-success me-2"></i>All prices are in Philippine Peso (PHP)
                                </li>
                                <li><i class="ti ti-check text-success me-2"></i>Please include invoice number in payment
                                    reference</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- /View Invoice -->

    <!-- Plan Upgrade Confirmation Modal -->
    <div class="modal fade" id="plan_upgrade_confirmation_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-arrow-up-circle fs-20 me-2"></i>
                        <h5 class="modal-title mb-0 text-white">Confirm Plan Upgrade</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="alert alert-info border-0 mb-4">
                        <i class="ti ti-info-circle me-2"></i>
                        You are about to upgrade to <strong id="confirm_plan_name">-</strong>
                    </div>

                    <!-- Cost Breakdown -->
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Cost Breakdown</h6>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Plan Price:</span>
                                <strong id="confirm_plan_price">₱0.00</strong>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">User Capacity:</span>
                                <strong>Up to <span id="confirm_plan_limit">0</span> users</strong>
                            </div>

                            <hr class="my-3">

                            <div class="d-flex justify-content-between align-items-center mb-2" id="confirm_impl_fee_row">
                                <span class="text-muted">Implementation Fee Difference:</span>
                                <strong id="confirm_impl_fee">₱0.00</strong>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Plan Price Difference:</span>
                                <strong id="confirm_price_diff">₱0.00</strong>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2"
                                style="border-bottom: 1px dashed rgba(0,0,0,0.2);">
                                <span class="text-muted">Subtotal:</span>
                                <strong id="confirm_subtotal">₱0.00</strong>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">VAT (<span id="confirm_vat_percent">12</span>%):</span>
                                <strong id="confirm_vat">₱0.00</strong>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 text-primary">Total Amount Due:</h5>
                                <h4 class="mb-0 text-primary fw-bold" id="confirm_total">₱0.00</h4>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning border-0 mb-0">
                        <i class="ti ti-alert-triangle me-2"></i>
                        An invoice will be generated and you'll be redirected to the billing page to complete the payment.
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmUpgradeBtn">
                        <i class="ti ti-check me-1"></i>Proceed with Upgrade
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Plan Upgrade Confirmation Modal -->

@endsection

@push('scripts')
    <script>
        // Helper functions
        function fmtMoney(value, currency) {
            const num = Number(value ?? 0);
            try {
                return new Intl.NumberFormat(undefined, {
                    style: 'currency',
                    currency: currency || 'PHP'
                }).format(num);
            } catch (_) {
                return `₱${num.toFixed(2)}`;
            }
        }

        function fmtDate(isoLike) {
            if (!isoLike) return '—';
            const d = new Date(isoLike);
            return isNaN(d) ? isoLike : d.toLocaleDateString();
        }

        // Fetch and display available plans
        async function loadAvailablePlans() {
            try {
                const response = await fetch('/subscriptions/available-plans');

                const data = await response.json();

                if (data.success) {
                    window.allPlans = data.available_plans || [];
                    window.currentBillingCycle = data.current_billing_cycle || 'monthly';

                    // Initialize toggle to match current billing cycle
                    $('#billing_cycle_toggle').prop('checked', window.currentBillingCycle === 'yearly');
                    updateBillingCycleLabels(window.currentBillingCycle);

                    // Render plans for current billing cycle
                    renderPlansForCycle(window.currentBillingCycle);
                } else {
                    console.error('❌ API returned success: false');
                    console.error('Message:', data.message);
                    toastr.error(data.message || 'Failed to load plans');

                    // Show error in container
                    $('#available_plans_container').html(`
                    <div class="col-12 text-center py-4">
                        <i class="ti ti-alert-circle fs-2 text-danger"></i>
                        <p class="text-danger mb-0">${data.message || 'Failed to load plans'}</p>
                    </div>
                `);
                }
            } catch (error) {
                console.error('💥 Error loading plans:', error);
                toastr.error('Failed to load available plans. Please refresh the page.');

                $('#available_plans_container').html(`
                <div class="col-12 text-center py-4">
                    <i class="ti ti-alert-circle fs-2 text-danger"></i>
                    <p class="text-danger mb-0">Failed to load plans. Please refresh the page.</p>
                </div>
            `);
            }
        }

        function updateBillingCycleLabels(cycle) {
            if (cycle === 'yearly') {
                $('#billing_cycle_label_monthly').removeClass('text-primary fw-bold').addClass('text-muted');
                $('#billing_cycle_label_yearly').removeClass('text-muted').addClass('text-primary fw-bold');
            } else {
                $('#billing_cycle_label_monthly').removeClass('text-muted').addClass('text-primary fw-bold');
                $('#billing_cycle_label_yearly').removeClass('text-primary fw-bold').addClass('text-muted');
            }
        }

        function renderPlansForCycle(billingCycle) {
            console.log('🎨 Rendering plans for cycle:', billingCycle);
            const container = $('#available_plans_container');
            container.empty();

            const filteredPlans = (window.allPlans || []).filter(plan => plan.billing_cycle === billingCycle);
            console.log('📋 Filtered plans:', filteredPlans.length);

            if (filteredPlans.length === 0) {
                console.warn('⚠️ No plans found for', billingCycle);
                container.html(`
                <div class="col-12 text-center py-5">
                    <i class="ti ti-info-circle fs-1 text-muted mb-3"></i>
                    <p class="text-muted">No ${billingCycle} plans available for upgrade</p>
                </div>
            `);
                return;
            }

            // ✅ Determine column size based on number of plans
            const planCount = filteredPlans.length;
            const colClass = planCount >= 4 ? 'col-lg-3 col-md-6' : 'col-lg-4 col-md-6';

            console.log(`📐 Using ${colClass} for ${planCount} plans`);

            filteredPlans.forEach((plan, index) => {
                const isRecommended = plan.is_recommended || false;

                // Define color schemes for each plan tier (matching the reference image)
                const planColors = {
                    0: { header: '#52C480', headerText: 'white', badge: 'Best for Start Up!' },  // Green - Free/Starter
                    1: { header: '#FDB913', headerText: 'white', badge: 'Best Value' },           // Yellow - Core
                    2: { header: '#D16074', headerText: 'white', badge: 'Most Popular!' },        // Red - Pro
                    3: { header: '#E57F7F', headerText: 'white', badge: 'Enterprise' }             // Coral - Elite
                };

                const colorScheme = planColors[index % 4] || planColors[0];

                // Ensure all numeric values are valid numbers
                const price = parseFloat(plan.price || 0);
                const employeeLimit = parseInt(plan.employee_limit || 0);
                const implementationFee = parseFloat(plan.implementation_fee || 0);
                const implementationFeeDiff = parseFloat(plan.implementation_fee_difference || 0);
                const planPriceDiff = parseFloat(plan.plan_price_difference || 0);
                const subtotal = parseFloat(plan.subtotal || 0);
                const vatPercentage = parseFloat(plan.vat_percentage || 12);
                const vatAmount = parseFloat(plan.vat_amount || 0);
                const totalUpgradeCost = parseFloat(plan.total_upgrade_cost || 0);

                const planCard = `
                <div class="${colClass}">
                    <div class="card plan-option h-100 shadow-sm border-0"
                         data-plan-id="${plan.id}"
                         style="cursor: pointer; transition: all 0.3s ease; border-radius: 12px; overflow: hidden;">

                        <!-- Colored Header with Badge -->
                        <div class="position-relative" style="background: ${colorScheme.header}; padding: 1.25rem 1.5rem;">
                            ${isRecommended ? `
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-white text-dark px-2 py-1" style="font-size: 0.7rem; font-weight: 600;">
                                    <i class="ti ti-star-filled" style="color: ${colorScheme.header};"></i> Recommended
                                </span>
                            </div>
                            ` : `
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-white bg-opacity-25 text-white px-2 py-1" style="font-size: 0.7rem; font-weight: 600;">
                                    ${colorScheme.badge}
                                </span>
                            </div>
                            `}

                            <!-- Plan Name with Icon -->
                            <div class="d-flex align-items-center justify-content-between mt-3">
                                <h4 class="fw-bold mb-0" style="color: ${colorScheme.headerText}; font-size: ${planCount >= 4 ? '1.3rem' : '1.5rem'};">
                                    ${plan.name}
                                </h4>
                                <i class="ti ti-package" style="font-size: ${planCount >= 4 ? '1.8rem' : '2rem'}; color: ${colorScheme.headerText}; opacity: 0.9;"></i>
                            </div>
                            <p class="mb-0 mt-1" style="color: ${colorScheme.headerText}; opacity: 0.9; font-size: ${planCount >= 4 ? '0.75rem' : '0.85rem'};">
                                Good for ${employeeLimit <= 20 ? 'Start Up' : employeeLimit <= 100 ? 'Micro-Small' : employeeLimit <= 200 ? 'Medium' : 'Large'} Businesses
                            </p>
                        </div>

                        <div class="card-body p-${planCount >= 4 ? '3' : '4'} d-flex flex-column">
                            <!-- Pricing Section -->
                            <div class="text-center mb-${planCount >= 4 ? '3' : '4'} pb-${planCount >= 4 ? '3' : '4'}" style="border-bottom: 2px solid #f0f0f0;">
                                <div class="d-flex align-items-start justify-content-center mb-2">
                                    <span style="color: #1a1a1a; font-size: ${planCount >= 4 ? '1.5rem' : '1.8rem'}; font-weight: 700; margin-right: 4px;">₱</span>
                                    <h2 class="fw-bold mb-0" style="color: #1a1a1a; font-size: ${planCount >= 4 ? '2.5rem' : '3rem'}; line-height: 1;">
                                        ${price.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0})}
                                    </h2>
                                </div>
                                <p class="text-muted mb-0" style="font-size: ${planCount >= 4 ? '0.8rem' : '0.9rem'};">
                                    ${price > 0 ? 'based price' : ''} ${plan.billing_cycle === 'monthly' ? '/month' : '/year'}
                                </p>
                            </div>

                            <!-- GET STARTED Button -->
                            <button class="btn w-100 py-2 rounded-3 fw-semibold mb-${planCount >= 4 ? '3' : '4'} select-plan-btn"
                                    style="background: ${colorScheme.header}; color: white; border: none; font-size: ${planCount >= 4 ? '0.9rem' : '1rem'}; transition: all 0.3s ease;">
                                GET STARTED
                            </button>

                            <!-- Employee Limit Badge -->
                            <div class="text-center mb-${planCount >= 4 ? '3' : '4'}">
                                <span class="badge px-3 py-2" style="background: rgba(${parseInt(colorScheme.header.slice(1,3), 16)}, ${parseInt(colorScheme.header.slice(3,5), 16)}, ${parseInt(colorScheme.header.slice(5,7), 16)}, 0.15); color: ${colorScheme.header}; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'}; font-weight: 600;">
                                    <i class="ti ti-users me-1"></i> Up to ${employeeLimit} Employees
                                </span>
                            </div>

                            <!-- What's Included Section -->
                            <div class="mb-${planCount >= 4 ? '3' : '4'}">
                                <h6 class="fw-bold mb-3" style="color: #1a1a1a; font-size: ${planCount >= 4 ? '0.9rem' : '1rem'};">What's Included</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-check" style="color: ${colorScheme.header}; font-size: 1.1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #333; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">Creation of Portal</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.75rem; color: #999; cursor: help;" title="Your company portal will be created"></i>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-check" style="color: ${colorScheme.header}; font-size: 1.1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #333; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">${employeeLimit <= 20 ? '2 Days' : employeeLimit <= 100 ? '7 Days' : employeeLimit <= 200 ? '7 Days' : '14 Days'} Free Training</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.75rem; color: #999; cursor: help;" title="Free training period"></i>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-check" style="color: ${colorScheme.header}; font-size: 1.1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #333; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">Knowledge Base</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.75rem; color: #999; cursor: help;" title="Access to knowledge base"></i>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-check" style="color: ${colorScheme.header}; font-size: 1.1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #333; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">${employeeLimit >= 200 ? 'User Video Tutorial' : 'Lifetime Email Support'}</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.75rem; color: #999; cursor: help;" title="Support included"></i>
                                        </div>
                                    </li>
                                    ${employeeLimit >= 200 ? `
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-check" style="color: ${colorScheme.header}; font-size: 1.1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #333; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">Lifetime Email & Call Support</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.75rem; color: #999; cursor: help;" title="Premium support"></i>
                                        </div>
                                    </li>
                                    ` : ''}
                                    ${employeeLimit >= 200 ? `
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-check" style="color: ${colorScheme.header}; font-size: 1.1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #333; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">Free ${employeeLimit >= 500 ? '2' : '1'} Biometrics Device${employeeLimit >= 500 ? 's' : ''}</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.75rem; color: #999; cursor: help;" title="Biometric devices included"></i>
                                        </div>
                                    </li>
                                    ` : ''}
                                    ${employeeLimit >= 500 ? `
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-check" style="color: ${colorScheme.header}; font-size: 1.1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #333; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">FREE Custom Company Logo</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.75rem; color: #999; cursor: help;" title="Custom branding included"></i>
                                        </div>
                                    </li>
                                    ` : ''}
                                </ul>
                            </div>

                            <!-- Available Add-Ons Section -->
                            <div class="mt-auto">
                                <h6 class="fw-bold mb-3" style="color: #1a1a1a; font-size: ${planCount >= 4 ? '0.9rem' : '1rem'};">Available Add-Ons</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-tool" style="color: #999; font-size: 1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #666; font-size: ${planCount >= 4 ? '0.8rem' : '0.85rem'};">Custom Company Logo</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.7rem; color: #999; cursor: help;"></i>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-device-mobile" style="color: #999; font-size: 1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #666; font-size: ${planCount >= 4 ? '0.8rem' : '0.85rem'};">Mobile App (iOS & Android)</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.7rem; color: #999; cursor: help;"></i>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-fingerprint" style="color: #999; font-size: 1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #666; font-size: ${planCount >= 4 ? '0.8rem' : '0.85rem'};">Biometrics Integration</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.7rem; color: #999; cursor: help;"></i>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-2">
                                        <i class="ti ti-tool" style="color: #999; font-size: 1rem; margin-right: 0.5rem; margin-top: 2px;"></i>
                                        <div>
                                            <span style="color: #666; font-size: ${planCount >= 4 ? '0.8rem' : '0.85rem'};">Biometric Labor Installation</span>
                                            <i class="ti ti-info-circle ms-1" style="font-size: 0.7rem; color: #999; cursor: help;"></i>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <!-- Cost Breakdown (Collapsible) -->
                            <div class="mt-3 pt-3" style="border-top: 2px solid #f0f0f0;">
                                <button class="btn btn-link w-100 text-start p-0 text-decoration-none d-flex align-items-center justify-content-between"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#costBreakdown${plan.id}"
                                        style="color: #666; font-size: ${planCount >= 4 ? '0.85rem' : '0.9rem'};">
                                    <span><i class="ti ti-receipt me-2"></i>View Upgrade Cost Breakdown</span>
                                    <i class="ti ti-chevron-down"></i>
                                </button>
                                <div class="collapse mt-3" id="costBreakdown${plan.id}">
                                    <div class="bg-light rounded-3 p-3">
                                        ${implementationFeeDiff > 0 ? `
                                        <div class="d-flex justify-content-between mb-2">
                                            <span style="font-size: 0.85rem; color: #666;">Implementation Fee Diff.</span>
                                            <span style="font-size: 0.85rem; font-weight: 600; color: #333;">₱${implementationFeeDiff.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                        </div>
                                        ` : ''}
                                        <div class="d-flex justify-content-between mb-2">
                                            <span style="font-size: 0.85rem; color: #666;">Plan Price Difference</span>
                                            <span style="font-size: 0.85rem; font-weight: 600; color: #333;">₱${planPriceDiff.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2 pb-2" style="border-bottom: 1px dashed #ddd;">
                                            <span style="font-size: 0.85rem; color: #666;">Subtotal</span>
                                            <span style="font-size: 0.85rem; font-weight: 600; color: #333;">₱${subtotal.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span style="font-size: 0.85rem; color: #666;">VAT (${vatPercentage}%)</span>
                                            <span style="font-size: 0.85rem; font-weight: 600; color: #333;">₱${vatAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center pt-2" style="border-top: 2px solid ${colorScheme.header};">
                                            <span style="font-size: 0.9rem; font-weight: 700; color: #1a1a1a;">Total Upgrade Cost</span>
                                            <span style="font-size: 1.1rem; font-weight: 700; color: ${colorScheme.header};">₱${totalUpgradeCost.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected State Indicator -->
                        <div class="position-absolute top-0 start-0 w-100 h-100"
                             style="opacity: 0; transition: opacity 0.3s ease; pointer-events: none; border: 3px solid #28c76f; border-radius: 12px; z-index: 5;"></div>
                    </div>
                </div>
            `;
                container.append(planCard);
            });

            console.log('✅ Plans rendered successfully');

            // Setup plan card interactions
            setupPlanCardHandlers(filteredPlans);
        }

        function setupPlanCardHandlers(plans) {
            // Add hover effects
            $('#available_plans_container').off('mouseenter mouseleave').on('mouseenter', '.plan-option', function() {
                $(this).css({
                    'transform': 'translateY(-8px) scale(1.02)',
                    'box-shadow': '0 20px 40px rgba(0, 0, 0, 0.15)'
                });
            }).on('mouseleave', '.plan-option', function() {
                if (!$(this).hasClass('selected-plan')) {
                    $(this).css({
                        'transform': 'translateY(0) scale(1)',
                        'box-shadow': ''
                    });
                }
            });

            // Handle plan selection
            $('#available_plans_container').off('click').on('click', '.plan-option', function() {
                console.log('🖱️ Plan card clicked!');
                const planId = $(this).data('plan-id');
                console.log('Selected plan ID:', planId);
                const plan = window.allPlans.find(p => p.id === planId);

                if (plan) {
                    console.log('✅ Plan found:', plan.name);

                    // Remove selection from all cards
                    $('.plan-option').removeClass('selected-plan').css({
                        'transform': 'translateY(0) scale(1)',
                        'box-shadow': ''
                    }).find('.position-absolute:last-child').css('opacity', '0');

                    // Add selection to clicked card
                    $(this).addClass('selected-plan').css({
                        'transform': 'translateY(-8px) scale(1.02)',
                        'box-shadow': '0 20px 40px rgba(40, 199, 111, 0.3)'
                    }).find('.position-absolute:last-child').css('opacity', '1');

                    // Show confirmation dialog
                    showUpgradeConfirmation(plan);
                } else {
                    console.error('❌ Plan not found for ID:', planId);
                }
            });
        }

        function showUpgradeConfirmation(plan) {
            // Populate modal with plan details
            $('#confirm_plan_name').text(plan.name || '-');
            $('#confirm_plan_price').text('₱' + parseFloat(plan.price || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#confirm_plan_limit').text(plan.employee_limit || 0);

            // Implementation fee (hide row if 0)
            const implFee = parseFloat(plan.implementation_fee_difference || 0);
            if (implFee > 0) {
                $('#confirm_impl_fee_row').show();
                $('#confirm_impl_fee').text('₱' + implFee.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            } else {
                $('#confirm_impl_fee_row').hide();
            }

            // Other costs
            $('#confirm_price_diff').text('₱' + parseFloat(plan.plan_price_difference || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#confirm_subtotal').text('₱' + parseFloat(plan.subtotal || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#confirm_vat_percent').text(plan.vat_percentage || 12);
            $('#confirm_vat').text('₱' + parseFloat(plan.vat_amount || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#confirm_total').text('₱' + parseFloat(plan.total_upgrade_cost || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            // Store plan ID in button
            $('#confirmUpgradeBtn').data('plan-id', plan.id);

            // Show modal
            $('#plan_upgrade_confirmation_modal').modal('show');
        }

        // Handle upgrade confirmation button
        $(document).on('click', '#confirmUpgradeBtn', function() {
            const planId = $(this).data('plan-id');
            $('#plan_upgrade_confirmation_modal').modal('hide');
            upgradeToSelectedPlan(planId);
        });

        async function upgradeToSelectedPlan(planId) {
            try {
                const response = await fetch('/employees/generate-plan-upgrade-invoice', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        new_plan_id: planId
                    })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    toastr.success('Plan upgrade invoice generated! Redirecting to billing...');
                    setTimeout(() => {
                        window.location.href = '/billing';
                    }, 2000);
                } else {
                    toastr.error(data.message || 'Failed to generate upgrade invoice');
                }
            } catch (error) {
                console.error('Error:', error);
                toastr.error('An error occurred while processing your upgrade request');
            }
        }

        // Invoice modal population
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 DOM Content Loaded - Initializing subscription page...');

            // Check if jQuery is loaded
            if (typeof $ === 'undefined') {
                console.error('❌ jQuery is not loaded!');
                document.getElementById('available_plans_container').innerHTML = `
                <div class="col-12 text-center py-4">
                    <i class="ti ti-x-circle fs-2 text-danger"></i>
                    <p class="text-danger mb-0">Error: jQuery not loaded. Please refresh the page.</p>
                </div>
            `;
                return;
            }

            // Show loading state
            $('#available_plans_container').html(`
            <div class="col-12 text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2 mb-0">Loading available plans...</p>
            </div>
        `);

            // Load plans
            loadAvailablePlans();

            // Billing cycle toggle
            $('#billing_cycle_toggle').on('change', function() {
                const isYearly = $(this).is(':checked');
                const cycle = isYearly ? 'yearly' : 'monthly';
                updateBillingCycleLabels(cycle);
                renderPlansForCycle(cycle);
            });

            // Invoice modal
            const invoiceModal = document.getElementById('view_invoice');
            if (invoiceModal) {
                invoiceModal.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const d = btn.dataset;

                    // Header
                    document.getElementById('inv-number').textContent = d.invoiceNumber || '—';
                    document.getElementById('inv-issued-at').textContent = fmtDate(d.issuedAt);
                    document.getElementById('inv-due-date').textContent = fmtDate(d.dueDate);

                    // Bill To
                    document.getElementById('inv-to-name').textContent = d.billToName || '—';
                    document.getElementById('inv-to-address').textContent = d.billToAddress || '—';
                    document.getElementById('inv-to-email').textContent = d.billToEmail || '—';

                    // Table rows
                    const tbody = document.getElementById('inv-items');
                    tbody.innerHTML = '';

                    const subscriptionAmount = Number(d.subscriptionAmount || 0);
                    const licenseOverageCount = Number(d.licenseOverageCount || 0);
                    const licenseOverageAmount = Number(d.licenseOverageAmount || 0);
                    const licenseOverageRate = Number(d.licenseOverageRate || 49);
                    const implementationFee = Number(d.implementationFee || 0);
                    const amountDue = Number(d.amountDue || 0);
                    const invoiceType = d.invoiceType || 'subscription';

                    const hasOverage = (invoiceType === 'subscription' && licenseOverageCount > 0) ||
                        invoiceType === 'license_overage';
                    const showQtyRate = hasOverage || (invoiceType === 'subscription' && implementationFee > 0);

                    // Show/hide columns
                    document.querySelectorAll('.qty-rate-column').forEach(col => {
                        col.style.display = showQtyRate ? '' : 'none';
                    });

                    // Generate rows based on invoice type
                    if (invoiceType === 'plan_upgrade') {
                        const planUpgradeAmount = Number(d.subscriptionAmount || 0);

                        if (implementationFee > 0) {
                            const trImpl = document.createElement('tr');
                            trImpl.innerHTML = `
                            <td>Implementation Fee Difference
                                <br><small class="text-muted">Upgrading to ${d.plan || 'New Plan'}</small>
                            </td>
                            <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                            <td class="text-end">${fmtMoney(implementationFee, d.currency)}</td>
                        `;
                            tbody.appendChild(trImpl);
                        }

                        const trPlan = document.createElement('tr');
                        trPlan.innerHTML = `
                        <td>Plan Price Difference
                            <br><small class="text-muted">From ${d.currentPlan || 'Current Plan'} to ${d.plan || 'New Plan'} (${d.billingCycle ? d.billingCycle.charAt(0).toUpperCase() + d.billingCycle.slice(1) : 'N/A'})</small>
                        </td>
                        <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                        <td class="text-end">${fmtMoney(planUpgradeAmount, d.currency)}</td>
                    `;
                        tbody.appendChild(trPlan);
                    } else if (subscriptionAmount > 0) {
                        if (invoiceType === 'subscription' && implementationFee > 0) {
                            const trImpl = document.createElement('tr');
                            trImpl.innerHTML = showQtyRate ? `
                        <td>Implementation Fee</td>
                        <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                        <td>1</td>
                        <td>${fmtMoney(implementationFee, d.currency)}</td>
                        <td class="text-end">${fmtMoney(implementationFee, d.currency)}</td>
                    ` : `
                        <td>Implementation Fee</td>
                        <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                        <td class="text-end">${fmtMoney(implementationFee, d.currency)}</td>
                    `;
                            tbody.appendChild(trImpl);
                        }

                        const tr = document.createElement('tr');
                        tr.innerHTML = showQtyRate ? `
                        <td>${d.plan || '—'} Subscription</td>
                        <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                        <td>1</td>
                        <td>${fmtMoney(subscriptionAmount, d.currency)}</td>
                        <td class="text-end">${fmtMoney(subscriptionAmount, d.currency)}</td>
                    ` : `
                        <td>${d.plan || '—'} Subscription</td>
                        <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                        <td class="text-end">${fmtMoney(subscriptionAmount, d.currency)}</td>
                    `;
                        tbody.appendChild(tr);
                    }

                    // Totals
                    const amountPaid = Number(d.amountPaid || 0);
                    const vatPercentage = Number(d.vatPercentage || 12);
                    const vatAmount = Number(d.vatAmount || 0);
                    let subtotal = Number(d.subtotal || 0);
                    let calculatedVatAmount = vatAmount;

                    if (subtotal === 0 && amountDue > 0) {
                        subtotal = amountDue / (1 + (vatPercentage / 100));
                        calculatedVatAmount = amountDue - subtotal;
                    }

                    document.getElementById('inv-subtotal').textContent = fmtMoney(subtotal, d.currency);
                    document.getElementById('inv-vat-percentage').textContent = vatPercentage;
                    document.getElementById('inv-vat-amount').textContent = fmtMoney(calculatedVatAmount, d
                        .currency);
                    document.getElementById('inv-total-amount').textContent = fmtMoney(amountDue, d
                        .currency);
                    document.getElementById('inv-amount-paid').textContent = fmtMoney(amountPaid, d
                        .currency);
                    document.getElementById('inv-balance').textContent = fmtMoney(Math.max(amountDue -
                        amountPaid, 0), d.currency);
                });
            }

            // Handle download invoice
            document.querySelectorAll('.download-invoice-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const invoiceData = {
                        invoiceId: this.dataset.invoiceId,
                        invoiceNumber: this.dataset.invoiceNumber,
                        invoiceType: this.dataset.invoiceType,
                        amountDue: this.dataset.amountDue,
                        amountPaid: this.dataset.amountPaid,
                        subscriptionAmount: this.dataset.subscriptionAmount,
                        licenseOverageCount: this.dataset.licenseOverageCount,
                        licenseOverageAmount: this.dataset.licenseOverageAmount,
                        licenseOverageRate: this.dataset.licenseOverageRate,
                        implementationFee: this.dataset.implementationFee,
                        vatPercentage: this.dataset.vatPercentage,
                        vatAmount: this.dataset.vatAmount,
                        subtotal: this.dataset.subtotal,
                        currency: this.dataset.currency,
                        dueDate: this.dataset.dueDate,
                        status: this.dataset.status,
                        periodStart: this.dataset.periodStart,
                        periodEnd: this.dataset.periodEnd,
                        issuedAt: this.dataset.issuedAt,
                        billToName: this.dataset.billToName,
                        billToAddress: this.dataset.billToAddress,
                        billToEmail: this.dataset.billToEmail,
                        plan: this.dataset.plan,
                        currentPlan: this.dataset.currentPlan,
                        billingCycle: this.dataset.billingCycle
                    };

                    window.print();
                });
            });
        });
    </script>
@endpush
