<?php

$page = 'bills-payment'; ?>
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
                <!-- ✅ ENHANCED: Period-Based License Overage Warning -->
                {{-- @if ($subscription && $usageSummary && $usageSummary['total_billable_licenses'] > 0)
                @php
                $overageCount = $usageSummary['total_billable_licenses']; // ✅ Already calculated as additional only
                $overageAmount = $overageCount * 1; // ✅ Updated rate to ₱1
                @endphp
                <div class="col-12">
                    <div class="alert alert-warning mb-3">
                        <h6><i class="ti ti-alert-triangle me-2"></i>Additional License Usage Detected</h6>
                        <p class="mb-2 mt-3">
                            <strong>Current Billing Period:</strong>
                            {{ \Carbon\Carbon::parse($currentPeriod['start'])->format('M d, Y') }} -
                            {{ \Carbon\Carbon::parse($currentPeriod['end'])->format('M d, Y') }}
                        </p>
                        <p class="mb-2">
                            Your plan includes
                            <strong>{{ $subscription->plan->license_limit ?? ($subscription->active_license ?? 0)
                                }}</strong>
                            licenses.
                            You have used <strong>{{ $overageCount }}</strong> additional license(s) beyond your plan.
                        </p>
                        <p class="mb-2">
                            Additional charges: <strong>₱{{ number_format($overageAmount, 2) }}</strong>
                            (₱49.00 per additional license)
                        </p>
                        <small>
                            <strong>Note:</strong> Your base plan price
                            (₱{{ number_format($subscription->amount_paid ?? 0, 2) }})
                            already includes
                            {{ $subscription->plan->license_limit ?? ($subscription->active_license ?? 0) }} licenses.
                            Additional licenses are charged separately.
                        </small>
                    </div>
                </div>
                @endif --}}

                <!-- Subscription Plan Card -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $subscription->plan->name ?? 'No Active Plan' }}</h5>

                            <div class="mb-3">
                                <p class="text-muted mb-1">
                                    Status:
                                    @if ($subscription && $subscription->status)
                                        <span
                                            class="badge bg-{{ $subscription->status === 'active' ? 'success' : ($subscription->status === 'expired' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">No Subscription</span>
                                    @endif
                                </p>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                @php
                                    $subscriptionAmount = $invoice->first()->subscription_amount ?? 0;
                                @endphp
                                <h3 class="mb-0 me-2">₱{{ number_format($subscriptionAmount, 2) }}</h3>
                                <span class="text-muted">/ {{ $subscription->billing_cycle ?? 'N/A' }}</span>
                            </div>

                            <!-- ENHANCED: License Usage with Period-Based Tracking -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Currently Active: {{ $activeLicenseCount }} /
                                        {{ $subscription->active_license ?? '0' }}</span>
                                    @if ($activeLicenseCount > ($subscription->active_license ?? 0))
                                        <span class="text-warning">
                                            <i class="ti ti-alert-triangle"></i>
                                            +{{ $activeLicenseCount - ($subscription->active_license ?? 0) }} additional
                                            license(s)

                                        </span>
                                    @endif
                                </div>
                                <div class="progress" style="height: 8px;">
                                    @php
                                        $employeeLimit = $subscription->active_license ?? 1;
                                        $progressPercent =
                                            $employeeLimit > 0
                                            ? min(100, ($activeLicenseCount / $employeeLimit) * 100)
                                            : 0;
                                    @endphp
                                    <div class="progress-bar {{ $activeLicenseCount > $employeeLimit ? 'bg-warning' : 'bg-primary' }}"
                                        role="progressbar" style="width: {{ $progressPercent }}%"></div>
                                </div>
                            </div>

                            @if (!$subscription)
                                <div class="alert alert-warning">
                                    <i class="ti ti-alert-triangle me-2"></i>
                                    No active subscription found. Please contact support.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- License Usage Details Card -->
                @if ($subscription && $usageSummary)
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">License Usage This Period</h6>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($currentPeriod['start'])->format('M d, Y') }} -
                                    {{ \Carbon\Carbon::parse($currentPeriod['end'])->format('M d, Y') }}
                                </small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <h4 class="mb-1">{{ $usageSummary['total_billable_licenses'] }}</h4>
                                            <small class="text-muted">Additional Licenses</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <h4 class="mb-1">{{ $usageSummary['currently_active'] }}</h4>
                                            <small class="text-muted">Currently Active</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#usageDetailsModal">
                                        View Usage Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Enhanced Invoices Card -->
                <div class="card mt-2">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Invoices</h5>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-outline-primary btn-sm">Download All</button>
                        </div>
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
                                        <th>Period</th>
                                        <th>Pay</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoice as $inv)
                                        <tr>
                                            <td>
                                                <a href="#" class="text-primary invoice-details-btn" data-bs-toggle="modal"
                                                    data-bs-target="#view_invoice" data-invoice-id="{{ $inv->id }}"
                                                    data-invoice-number="{{ $inv->invoice_number }}"
                                                    data-invoice-type="{{ $inv->invoice_type ?? 'subscription' }}"
                                                    data-amount-due="{{ $inv->amount_due }}"
                                                    data-amount-paid="{{ $inv->amount_paid }}"
                                                    data-subscription-amount="{{ $inv->subscription_amount ?? $inv->amount_due }}"
                                                    data-license-overage-count="{{ $inv->license_overage_count ?? 0 }}"
                                                    data-license-overage-amount="{{ $inv->license_overage_amount ?? 0 }}"
                                                    data-license-overage-rate="{{ $inv->license_overage_rate ?? 49 }}"
                                                    data-implementation-fee="{{ $inv->implementation_fee ?? 0 }}"
                                                    data-vat-percentage="{{ $inv->calculated_vat_percentage ?? ($inv->subscription->plan->vat_percentage ?? 12) }}"
                                                    data-vat-amount="{{ $inv->calculated_vat_amount ?? ($inv->vat_amount ?? 0) }}"
                                                    data-subtotal="{{ $inv->calculated_subtotal ?? ($inv->amount_due ?? 0) - ($inv->vat_amount ?? 0) }}"
                                                    data-currency="{{ $inv->currency }}" data-due-date="{{ $inv->due_date }}"
                                                    data-status="{{ $inv->status }}"
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

                                                    {{-- ✅ UPDATED: Badge logic for unified INV invoices --}}
                                                    @if (($inv->invoice_type ?? 'subscription') === 'license_overage')
                                                        <span class="badge bg-info ms-1">License</span>
                                                    @elseif(($inv->invoice_type ?? 'subscription') === 'plan_upgrade')
                                                        <span class="badge bg-success ms-1">Plan Upgrade</span>
                                                    @elseif(($inv->invoice_type ?? 'subscription') === 'implementation_fee')
                                                        <span class="badge bg-warning ms-1">Implementation Fee</span>
                                                    @elseif(($inv->invoice_type ?? 'subscription') === 'subscription' && $inv->license_overage_count > 0)
                                                        <span class="badge bg-primary ms-1">License & Subscription</span>
                                                    @elseif(($inv->invoice_type ?? 'subscription') === 'consolidated')
                                                        <span class="badge bg-secondary ms-1">Consolidated</span>
                                                    @endif

                                                    {{-- ✅ NEW: Wizard invoice indicator --}}
                                                    @if($inv->is_wizard_generated ?? false)
                                                        <span class="badge bg-success bg-opacity-10 text-success ms-1">
                                                            <i class="ti ti-wand me-1"></i>Detailed
                                                        </span>
                                                    @endif
                                                </a>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($inv->issued_at)->format('Y-m-d') }}</td>
                                            <td>₱{{ number_format($inv->amount_due ?? 0, 2) }}</td>
                                            <td>
                                                @if (($inv->invoice_type ?? 'subscription') === 'license_overage')
                                                    @if ($inv->status === 'consolidated_pending')
                                                        <small class="text-muted">
                                                            <i class="ti ti-arrow-right me-1"></i>
                                                            Consolidated into next billing
                                                        </small>
                                                        <br>
                                                    @endif
                                                    License Overage: {{ $inv->license_overage_count ?? 0 }} licenses @
                                                    ₱{{ number_format($inv->license_overage_rate ?? 49, 2) }}
                                                @elseif(($inv->invoice_type ?? 'subscription') === 'plan_upgrade')
                                                    Plan Upgrade: {{ $inv->upgradePlan->name ?? 'Plan Upgrade' }}
                                                    @if ($inv->billing_cycle)
                                                        <span class="badge bg-primary ms-1">{{ ucfirst($inv->billing_cycle) }}</span>
                                                    @endif
                                                    <br><small class="text-info">
                                                        <i class="ti ti-arrow-up me-1"></i>
                                                        Upgrading from
                                                        {{ $inv->subscription->plan->name ?? 'Current Plan' }}
                                                        ({{ ucfirst($inv->subscription->billing_cycle ?? 'N/A') }})
                                                    </small>
                                                @elseif(($inv->invoice_type ?? 'subscription') === 'implementation_fee')
                                                    Implementation Fee:
                                                    @if($inv->items && $inv->items->isNotEmpty())
                                                        {{ $inv->items->first()->description ?? ($inv->subscription->plan->name ?? 'Plan') }}
                                                    @else
                                                        {{ $inv->subscription->plan->name ?? 'Plan' }}
                                                    @endif
                                                    <br><small class="text-primary">
                                                        <i class="ti ti-tools me-1"></i>
                                                        One-time setup fee
                                                    </small>
                                                @elseif(($inv->invoice_type ?? 'subscription') === 'subscription')
                                                    @if ($inv->license_overage_count > 0)
                                                        {{ $inv->subscription->plan->name ?? 'Subscription' }} +
                                                        {{ $inv->license_overage_count }} License Overage
                                                        <br><small class="text-success">
                                                            <i class="ti ti-check me-1"></i>
                                                            Includes additional fees for license overage
                                                        </small>
                                                    @else
                                                        {{ $inv->subscription->plan->name ?? 'Subscription' }}
                                                    @endif
                                                @elseif(($inv->invoice_type ?? 'subscription') === 'custom_order')
                                                    Custom Order
                                                @elseif(($inv->invoice_type ?? 'subscription') === 'consolidated')
                                                    🔗 Consolidated into another invoice
                                                @else
                                                    {{ $inv->subscription->plan->name ?? '-' }}
                                                @endif
                                            </td>
                                            <td>
                                                <small>
                                                    {{ \Carbon\Carbon::parse($inv->period_start)->format('M d') }} -
                                                    {{ \Carbon\Carbon::parse($inv->period_end)->format('M d, Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                @if (in_array($inv->status, ['paid', 'consolidated', 'consolidated_pending']))
                                                    @if ($inv->status === 'consolidated_pending')
                                                        <small class="text-muted">
                                                            <i class="ti ti-clock me-1"></i>
                                                            Included in
                                                            @if ($inv->consolidated_into_invoice_id)
                                                                @php
                                                                    $consolidatedInvoice = \App\Models\Invoice::find(
                                                                        $inv->consolidated_into_invoice_id,
                                                                    );
                                                                @endphp
                                                                <a href="#" class="text-primary invoice-details-btn" data-bs-toggle="modal"
                                                                    data-bs-target="#view_invoice"
                                                                    data-invoice-id="{{ $consolidatedInvoice->id ?? '' }}"
                                                                    data-invoice-number="{{ $consolidatedInvoice->invoice_number ?? '' }}"
                                                                    data-invoice-type="{{ $consolidatedInvoice->invoice_type ?? 'subscription' }}"
                                                                    data-amount-due="{{ $consolidatedInvoice->amount_due ?? 0 }}"
                                                                    data-amount-paid="{{ $consolidatedInvoice->amount_paid ?? 0 }}"
                                                                    data-subscription-amount="{{ $consolidatedInvoice->subscription_amount ?? 0 }}"
                                                                    data-license-overage-count="{{ $consolidatedInvoice->license_overage_count ?? 0 }}"
                                                                    data-license-overage-amount="{{ $consolidatedInvoice->license_overage_amount ?? 0 }}"
                                                                    data-license-overage-count="{{ $consolidatedInvoice->license_overage_count ?? 0 }}"
                                                                    data-currency="{{ $consolidatedInvoice->currency ?? 'PHP' }}"
                                                                    data-due-date="{{ $consolidatedInvoice->due_date }}"
                                                                    data-status="{{ $consolidatedInvoice->status }}"
                                                                    data-period-start="{{ $consolidatedInvoice->period_start }}"
                                                                    data-period-end="{{ $consolidatedInvoice->period_end }}"
                                                                    data-issued-at="{{ $consolidatedInvoice->issued_at }}"
                                                                    data-bill-to-name="{{ $consolidatedInvoice->tenant->tenant_name ?? 'N/A' }}"
                                                                    data-bill-to-address="{{ $consolidatedInvoice->tenant->tenant_address ?? 'N/A' }}"
                                                                    data-bill-to-email="{{ $consolidatedInvoice->tenant->tenant_email ?? 'N/A' }}"
                                                                    data-plan="{{ $consolidatedInvoice->invoice_type === 'plan_upgrade' && $consolidatedInvoice->upgradePlan ? $consolidatedInvoice->upgradePlan->name : $consolidatedInvoice->subscription->plan->name ?? 'N/A' }}"
                                                                    data-current-plan="{{ $consolidatedInvoice->subscription->plan->name ?? 'N/A' }}"
                                                                    data-billing-cycle="{{ $consolidatedInvoice->invoice_type === 'plan_upgrade' && $consolidatedInvoice->billing_cycle ? $consolidatedInvoice->billing_cycle : $consolidatedInvoice->subscription->billing_cycle ?? 'N/A' }}">
                                                                    >
                                                                    {{ $consolidatedInvoice->invoice_number ?? 'INV-XXXX' }}
                                                                </a>
                                                            @else
                                                                next invoice
                                                            @endif
                                                        </small>
                                                    @else
                                                        -
                                                    @endif
                                                @else
                                                    <button class="btn btn-outline-primary btn-sm pay-invoice-btn"
                                                        data-invoice-id="{{ $inv->id }}" data-amount="{{ $inv->amount_due }}">
                                                        Pay Now
                                                    </button>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inv->status === 'paid')
                                                    <span class="badge bg-success">
                                                        <i class="ti ti-check me-1"></i>
                                                        Paid on
                                                        {{ \Carbon\Carbon::parse($inv->paid_at)->format('n/j/Y g:i A') }}
                                                    </span>
                                                @elseif ($inv->status === 'consolidated')
                                                    <span class="badge bg-secondary">
                                                        <i class="ti ti-link me-1"></i>
                                                        Consolidated
                                                    </span>
                                                @elseif ($inv->status === 'consolidated_pending')
                                                    <span class="badge bg-info">
                                                        <i class="ti ti-clock me-1"></i>
                                                        Pending in
                                                        @if ($inv->consolidated_into_invoice_id)
                                                            @php
                                                                $consolidatedInvoice = \App\Models\Invoice::find(
                                                                    $inv->consolidated_into_invoice_id,
                                                                );
                                                            @endphp
                                                            {{ $consolidatedInvoice->invoice_number ?? 'INV-XXXX' }}
                                                        @else
                                                            next invoice
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">{{ ucfirst($inv->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="#" class="text-primary download-invoice-btn"
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
                                                    data-vat-percentage="{{ $inv->calculated_vat_percentage ?? ($inv->subscription->plan->vat_percentage ?? 12) }}"
                                                    data-vat-amount="{{ $inv->calculated_vat_amount ?? ($inv->vat_amount ?? 0) }}"
                                                    data-subtotal="{{ $inv->calculated_subtotal ?? ($inv->amount_due ?? 0) - ($inv->vat_amount ?? 0) }}"
                                                    data-currency="{{ $inv->currency }}" data-due-date="{{ $inv->due_date }}"
                                                    data-status="{{ $inv->status }}"
                                                    data-period-start="{{ $inv->period_start }}"
                                                    data-period-end="{{ $inv->period_end }}"
                                                    data-issued-at="{{ $inv->issued_at }}"
                                                    data-bill-to-name="{{ $inv->tenant->tenant_name ?? 'N/A' }}"
                                                    data-bill-to-address="{{ $inv->tenant->tenant_address ?? 'N/A' }}"
                                                    data-bill-to-email="{{ $inv->tenant->tenant_email ?? 'N/A' }}"
                                                    data-plan="{{ $inv->invoice_type === 'plan_upgrade' && $inv->upgradePlan ? $inv->upgradePlan->name : $inv->subscription->plan->name ?? 'N/A' }}"
                                                    data-current-plan="{{ $inv->subscription->plan->name ?? 'N/A' }}"
                                                    data-billing-cycle="{{ $inv->invoice_type === 'plan_upgrade' && $inv->billing_cycle ? $inv->billing_cycle : $inv->subscription->billing_cycle ?? 'N/A' }}"
                                                    data-has-wizard-items="{{ ($inv->is_wizard_generated ?? false) ? 'true' : 'false' }}">
                                                    <i class="ti ti-download me-1"></i>Download
                                                </a>
                                            </td>
                                        </tr>

                                        {{-- ✅ NEW: Enhanced invoice items display for wizard invoices --}}
                                        @if($inv->is_wizard_generated ?? false)
                                            <tr class="invoice-details-row">
                                                <td colspan="6" class="p-0">
                                                    <div class="collapse" id="invoiceDetails{{ $inv->id }}">
                                                        <div class="p-3 bg-light border-top">
                                                            @include('tenant.billing.components.invoice-items', ['invoice' => $inv])
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- Toggle button for detailed view --}}
                                                    <div class="text-center py-2 bg-light bg-opacity-50 border-top">
                                                        <button class="btn btn-sm btn-outline-primary" type="button" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#invoiceDetails{{ $inv->id }}" 
                                                            aria-expanded="false" 
                                                            aria-controls="invoiceDetails{{ $inv->id }}">
                                                            <i class="ti ti-eye me-1"></i>View Details ({{ $inv->item_counts['total'] ?? 0 }} items)
                                                        </button>
                                                        
                                                        @if(($inv->item_counts['one_time'] ?? 0) > 0)
                                                            <span class="badge bg-warning ms-2">{{ $inv->item_counts['one_time'] }} One-time</span>
                                                        @endif
                                                        @if(($inv->item_counts['recurring'] ?? 0) > 0)
                                                            <span class="badge bg-info ms-1">{{ $inv->item_counts['recurring'] }} Recurring</span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-light border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    {{ $invoice->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ ENHANCED: Usage Details Modal -->
        @if ($subscription && $usageSummary)
            <div class="modal fade" id="usageDetailsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">License Usage Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <h6>Current Period: {{ \Carbon\Carbon::parse($currentPeriod['start'])->format('M d, Y') }}
                                    - {{ \Carbon\Carbon::parse($currentPeriod['end'])->format('M d, Y') }}</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Activated</th>
                                            <th>Deactivated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($usageSummary['usage_details'] as $detail)
                                            <tr>
                                                <td>{{ $detail['user_name'] }}</td>
                                                <td>{{ \Carbon\Carbon::parse($detail['activated_at'])->format('M d, Y H:i') }}
                                                </td>
                                                <td>
                                                    @if ($detail['deactivated_at'])
                                                        {{ \Carbon\Carbon::parse($detail['deactivated_at'])->format('M d, Y H:i') }}
                                                    @else
                                                        <span class="text-success">Still Active</span>
                                                    @endif
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- View Invoice Modal -->
        <div class="modal fade" id="view_invoice">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-body p-5">

                        <div class="row justify-content-between align-items-center mb-3">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <img src="{{ URL::asset('build/img/JAF-LOGO.png') }}" class="img-fluid" alt="logo"
                                        style="max-width: 150px; height: auto;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-end mb-3">
                                    <h5 class="text-dark mb-1">Invoice</h5>
                                    <p class="mb-1 fw-normal">
                                        <i class="ti ti-file-invoice me-1"></i><span id="inv-number">—</span>
                                        <span id="inv-type-badge" class="badge ms-1">—</span>
                                    </p>
                                    <p class="mb-1 fw-normal">
                                        <i class="ti ti-calendar me-1"></i>Issue date : <span id="inv-issued-at">—</span>
                                    </p>
                                    <p class="mb-1 fw-normal">
                                        <i class="ti ti-calendar me-1"></i>Due date : <span id="inv-due-date">—</span>
                                    </p>
                                    <p class="fw-normal" id="inv-billing-cycle-row" style="display: none;">
                                        <i class="ti ti-refresh me-1"></i>Billing Cycle : <span id="inv-billing-cycle"
                                            class="badge bg-info">—</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3 d-flex justify-content-between">
                            <div class="col-md-7">
                                <p class="text-dark mb-2 fw-medium fs-16">Invoice From :</p>
                                <div>
                                    <p class="mb-1">JAF Digital Group Inc.</p>
                                    <p class="mb-1">Unit D 49th Floor PBCom Tower, 6795 Ayala Avenue, corner V.A.
                                        Rufino St, Makati City, Metro Manila, Philippines</p>
                                    <p class="mb-1">support@timora.ph</p>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <p class="text-dark mb-2 fw-medium fs-16">Invoice To :</p>
                                <div id="inv-to">
                                    <p class="mb-1" id="inv-to-name">—</p>
                                    <p class="mb-1" id="inv-to-address">—</p>
                                    <p class="mb-1" id="inv-to-email">—</p>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ CHECK: Invoice Items Table -->
                        <div class="mb-4">
                            <div class="table-responsive mb-3">
                                <table class="table">
                                    <thead class="thead-light" id="inv-table-header">
                                        <tr>
                                            <th>Description</th>
                                            <th>Period</th>
                                            <th class="qty-rate-column">Quantity</th>
                                            <th class="qty-rate-column">Rate</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="inv-items">
                                        <!-- ✅ This is where the rows should appear -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- ✅ CHECK: Totals Section -->
                        <div class="row mb-3 d-flex justify-content-between">
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center pe-3">
                                    <p class="text-dark fw-medium mb-0">Sub Total</p>
                                    <p class="mb-2" id="inv-subtotal">—</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pe-3">
                                    <p class="text-dark fw-medium mb-0">VAT (<span id="inv-vat-percentage">12</span>%)</p>
                                    <p class="mb-2" id="inv-vat-amount">—</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pe-3">
                                    <p class="text-dark fw-medium mb-0">Total Amount</p>
                                    <p class="mb-2" id="inv-total-amount">—</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pe-3 border-top pt-2">
                                    <p class="text-dark fw-medium mb-0">Amount Paid</p>
                                    <p class="mb-2" id="inv-amount-paid">—</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pe-3">
                                    <p class="text-dark fw-medium mb-0">Balance Due</p>
                                    <p class="text-dark fw-medium mb-2" id="inv-balance">—</p>
                                </div>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="card border mb-0">
                            <div class="card-body">
                                <p class="text-dark fw-medium mb-2">Terms & Conditions:</p>
                                <p class="fs-12 fw-normal d-flex align-items-baseline mb-2">
                                    <i class="ti ti-point-filled text-primary me-1"></i>
                                    All payments must be made according to the agreed schedule.
                                </p>
                                <p class="fs-12 fw-normal d-flex align-items-baseline mb-2">
                                    <i class="ti ti-point-filled text-primary me-1"></i>
                                    License overage charges apply when employee count exceeds subscription limit during
                                    billing period.
                                </p>
                                <p class="fs-12 fw-normal d-flex align-items-baseline">
                                    <i class="ti ti-point-filled text-primary me-1"></i>
                                    We are not liable for any indirect, incidental, or consequential damages, including loss
                                    of profits, revenue, or data.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- /View Invoice -->

        @include('layout.partials.footer-company')
    </div>

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Handle pay button clicks with post-payment notification
            document.querySelectorAll('.pay-invoice-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const invoiceId = this.dataset.invoiceId;
                    const amount = this.dataset.amount;

                    // Show loading state
                    this.disabled = true;
                    this.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';

                    // Initiate payment
                    fetch(`/billing/payment/initiate/${invoiceId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').content
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Show payment info with post-payment warning
                                if (typeof toastr !== 'undefined') {
                                    toastr.info(
                                        'Redirecting to payment gateway. After payment, new invoice may be created for ongoing license overage.'
                                    );
                                }

                                // Redirect to payment gateway
                                window.location.href = data.payment_url;
                            } else {
                                // Show error
                                if (typeof toastr !== 'undefined') {
                                    toastr.error(data.message || 'Payment initiation failed');
                                } else {
                                    alert(data.message || 'Payment initiation failed');
                                }

                                // Reset button
                                this.disabled = false;
                                this.innerHTML = 'Pay Now';
                            }
                        })
                        .catch(error => {
                            console.error('Payment error:', error);

                            if (typeof toastr !== 'undefined') {
                                toastr.error('An error occurred while processing payment');
                            } else {
                                alert('An error occurred while processing payment');
                            }

                            // Reset button
                            this.disabled = false;
                            this.innerHTML = 'Pay Now';
                        });
                });
            });

            // Handle download invoice button clicks
            document.querySelectorAll('.download-invoice-btn').forEach(button => {
                button.addEventListener('click', function (e) {
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

                    // ✅ ENHANCED: Check if invoice has detailed items (wizard invoices, custom_order, implementation_fee)
                    const hasDetailedItems = this.dataset.hasWizardItems === 'true' || 
                                           invoiceData.invoiceType === 'custom_order' || 
                                           invoiceData.invoiceType === 'implementation_fee';
                    
                    // If invoice has detailed items, fetch them first before generating PDF
                    if (hasDetailedItems) {
                        fetch(`/billing/invoices/${invoiceData.invoiceId}/items`)
                            .then(response => response.json())
                            .then(items => {
                                invoiceData.items = items;
                                generateInvoicePDF(invoiceData);
                            })
                            .catch(error => {
                                console.error('Error fetching invoice items:', error);
                                if (typeof toastr !== 'undefined') {
                                    toastr.error('Failed to fetch invoice items');
                                } else {
                                    alert('Failed to fetch invoice items');
                                }
                            });
                    } else {
                        generateInvoicePDF(invoiceData);
                    }
                });
            });
        });

        // ✅ ENHANCED: Generate Invoice PDF with License Overage Support
        function generateInvoicePDF(data) {
            // Helper functions for PDF generation
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

            // Create a hidden container for the invoice content
            const invoiceContainer = document.createElement('div');
            invoiceContainer.style.position = 'absolute';
            invoiceContainer.style.left = '-9999px';
            invoiceContainer.style.width = '800px';
            invoiceContainer.style.backgroundColor = 'white';
            invoiceContainer.style.padding = '40px';
            invoiceContainer.style.fontFamily = 'Arial, sans-serif';

            const amountDue = Number(data.amountDue ?? 0);
            const amountPaid = Number(data.amountPaid ?? 0);
            const subscriptionAmount = Number(data.subscriptionAmount ?? 0);
            const licenseOverageAmount = Number(data.licenseOverageAmount ?? 0);
            const licenseOverageCount = Number(data.licenseOverageCount ?? 0);
            const licenseOverageRate = Number(data.licenseOverageRate ?? 49);
            const implementationFee = Number(data.implementationFee ?? 0);
            const vatPercentage = Number(data.vatPercentage ?? 12);
            const vatAmount = Number(data.vatAmount ?? 0);
            const subtotalFromData = Number(data.subtotal ?? 0);

            // Calculate VAT if not provided
            let subtotal = subtotalFromData;
            let calculatedVatAmount = vatAmount;

            if (subtotal === 0 && amountDue > 0) {
                // Calculate from VAT-inclusive amount
                subtotal = amountDue / (1 + (vatPercentage / 100));
                calculatedVatAmount = amountDue - subtotal;
            }

            const balance = Math.max(amountDue - amountPaid, 0);

            // ✅ ENHANCED: Determine if we should show Quantity and Rate columns in PDF
            const hasOverage = (data.invoiceType === 'subscription' && licenseOverageCount > 0) ||
                data.invoiceType === 'license_overage' ||
                data.invoiceType === 'combo' ||
                data.invoiceType === 'custom_order';
            const hasWizardItems = data.items && Array.isArray(data.items) && data.items.length > 0;
            const showQtyRateInPDF = hasOverage || hasWizardItems;

            // Generate invoice items based on type
            let invoiceItemsHTML = '';

            // ✅ NEW: Handle wizard invoice items first
            if (hasWizardItems) {
                data.items.forEach(item => {
                    const itemType = item.metadata?.type || 'standard';
                    const period = item.period === 'one-time' ? 'One-time' : (item.period || '—');
                    
                    invoiceItemsHTML += `
                        <tr>
                            <td>
                                ${item.description}
                                ${itemType === 'biometric_device' && item.metadata?.device?.model ? 
                                    `<br><small style="color: #666;">Model: ${item.metadata.device.model}</small>` : ''}
                            </td>
                            <td>${period}</td>
                            <td class="text-center">${item.quantity || 1}</td>
                            <td class="text-end">${fmtMoney(item.rate, data.currency)}</td>
                            <td class="text-end"><strong>${fmtMoney(item.amount, data.currency)}</strong></td>
                        </tr>
                    `;
                });
            } else {

            if (data.invoiceType === 'combo' || data.invoiceType === 'subscription') {
                // Implementation Fee
                if (implementationFee > 0) {
                    invoiceItemsHTML += showQtyRateInPDF ? `
                            <tr>
                                <td>Implementation Fee</td>
                                <td>${fmtDate(data.periodStart)} - ${fmtDate(data.periodEnd)}</td>
                                <td>1</td>
                                <td>${fmtMoney(implementationFee, data.currency)}</td>
                                <td class="text-end">${fmtMoney(implementationFee, data.currency)}</td>
                            </tr>
                        ` : `
                            <tr>
                                <td>Implementation Fee</td>
                                <td>${fmtDate(data.periodStart)} - ${fmtDate(data.periodEnd)}</td>
                                <td class="text-end">${fmtMoney(implementationFee, data.currency)}</td>
                            </tr>
                        `;
                }
                // Subscription + License Overage
                if (subscriptionAmount > 0) {
                    invoiceItemsHTML += showQtyRateInPDF ? `
                            <tr>
                                <td>${data.plan || '—'} Subscription</td>
                                <td>${fmtDate(data.periodStart)} - ${fmtDate(data.periodEnd)}</td>
                                <td>1</td>
                                <td>${fmtMoney(subscriptionAmount, data.currency)}</td>
                                <td class="text-end">${fmtMoney(subscriptionAmount, data.currency)}</td>
                            </tr>
                        ` : `
                            <tr>
                                <td>${data.plan || '—'} Subscription</td>
                                <td>${fmtDate(data.periodStart)} - ${fmtDate(data.periodEnd)}</td>
                                <td class="text-end">${fmtMoney(subscriptionAmount, data.currency)}</td>
                            </tr>
                        `;
                }
                if (licenseOverageCount > 0) {
                    invoiceItemsHTML += `
                            <tr>
                                <td>License Overage</td>
                                <td>${fmtDate(data.periodStart)} - ${fmtDate(data.periodEnd)}</td>
                                <td>${licenseOverageCount}</td>
                                <td>${fmtMoney(licenseOverageRate, data.currency)}</td>
                                <td class="text-end">${fmtMoney(licenseOverageAmount, data.currency)}</td>
                            </tr>
                        `;
                }
            } else if (data.invoiceType === 'license_overage') {
                // License Overage Only
                invoiceItemsHTML = `
                        <tr>
                            <td>License Overage</td>
                            <td>${fmtDate(data.periodStart)} - ${fmtDate(data.periodEnd)}</td>
                            <td>${licenseOverageCount}</td>
                            <td>${fmtMoney(licenseOverageRate, data.currency)}</td>
                            <td class="text-end">${fmtMoney(licenseOverageAmount, data.currency)}</td>
                        </tr>
                    `;
            } else if (data.invoiceType === 'plan_upgrade') {
                // Plan Upgrade - Show detailed breakdown WITHOUT Quantity and Rate
                const implementationFee = Number(data.implementationFee || 0);
                const planUpgradeAmount = Number(data.subscriptionAmount || 0);

                let upgradeItemsHTML = '';

                // Add implementation fee difference if it exists
                if (implementationFee > 0) {
                    upgradeItemsHTML += `
                            <tr>
                                <td>Implementation Fee Difference<br><small style="color: #666;">Upgrading to ${data.plan || 'New Plan'}</small></td>
                                <td>${fmtDate(data.periodStart)} - ${fmtDate(data.periodEnd)}</td>
                                <td class="text-end">${fmtMoney(implementationFee, data.currency)}</td>
                            </tr>
                        `;
                }

                // Add plan price difference
                upgradeItemsHTML += `
                        <tr>
                            <td>Plan Price Difference<br><small style="color: #666;">From ${data.currentPlan || 'Current Plan'} to ${data.plan || 'New Plan'} (${data.billingCycle ? data.billingCycle.charAt(0).toUpperCase() + data.billingCycle.slice(1) : 'N/A'})</small></td>
                            <td>${fmtDate(data.periodStart)} - ${fmtDate(data.periodEnd)}</td>
                            <td class="text-end">${fmtMoney(planUpgradeAmount, data.currency)}</td>
                        </tr>
                    `;

                invoiceItemsHTML = upgradeItemsHTML;
            } else if (data.invoiceType === 'implementation_fee') {
                // Implementation Fee - Use items description if available
                if (data.items && data.items.length > 0) {
                    const item = data.items[0];
                    invoiceItemsHTML = `
                            <tr>
                                <td>${item.description}<br><small style="color: #666;">One-time setup fee</small></td>
                                <td>${item.period || (fmtDate(data.periodStart) + ' - ' + fmtDate(data.periodEnd))}</td>
                                <td class="text-end">${fmtMoney(item.amount, data.currency)}</td>
                            </tr>
                        `;
                } else {
                    // Fallback to default
                    invoiceItemsHTML = `
                            <tr>
                                <td>Implementation Fee: ${data.plan || 'Plan'}<br><small style="color: #666;">One-time setup fee</small></td>
                                <td>${fmtDate(data.periodStart)} - ${fmtDate(data.periodEnd)}</td>
                                <td class="text-end">${fmtMoney(amountDue, data.currency)}</td>
                            </tr>
                        `;
                }
            } else if (data.invoiceType === 'custom_order') {
                // For custom orders, use fetched items from AJAX
                if (data.items && data.items.length > 0) {
                    invoiceItemsHTML = '';
                    data.items.forEach(item => {
                        invoiceItemsHTML += `
                    <tr>
                        <td>${item.description}</td>
                        <td>${item.period || (fmtDate(data.periodStart) + ' - ' + fmtDate(data.periodEnd))}</td>
                        <td>${item.quantity}</td>
                        <td>${fmtMoney(item.rate, data.currency)}</td>
                        <td class="text-end">${fmtMoney(item.amount, data.currency)}</td>
                    </tr>
                `;
                    });
                } else {
                    // Fallback if no items found
                    invoiceItemsHTML = `<tr><td colspan="5" class="text-center">No items found</td></tr>`;
                }
            } else {
                // Default - use showQtyRateInPDF to determine
                invoiceItemsHTML = showQtyRateInPDF ? `
                        <tr>
                            <td>${data.plan || '—'} Subscription</td>
                            <td>${fmtDate(data.periodStart)} - ${fmtDate(data.periodEnd)}</td>
                            <td>1</td>
                            <td>${fmtMoney(amountDue, data.currency)}</td>
                            <td class="text-end">${fmtMoney(amountDue, data.currency)}</td>
                        </tr>
                    ` : `
                        <tr>
                            <td>${data.plan || '—'} Subscription</td>
                            <td>${fmtDate(data.periodStart)} - ${fmtDate(data.periodEnd)}</td>
                            <td class="text-end">${fmtMoney(amountDue, data.currency)}</td>
                        </tr>
                    `;
            }
            // ✅ End of legacy invoice generation logic
            }

            invoiceContainer.innerHTML = `
                    <div style="margin-bottom: 30px;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 30px;">
                            <div>
                                <img src="{{ URL::asset('build/img/JAF-LOGO.png') }}" style="max-width: 150px; height: auto;" alt="JAF Digital Group Inc. Logo">
                            </div>
                            <div style="text-align: right;">
                                <h2 style="margin: 0 0 10px 0; color: #333;">Invoice</h2>
                                <p style="margin: 5px 0; font-size: 14px;">${getInvoiceIcon(data.invoiceType)} ${data.invoiceNumber || '—'}</p>
                                ${hasWizardItems ? '<p style="margin: 5px 0; font-size: 12px; color: #28a745;">🪄 Detailed Breakdown Included</p>' : ''}
                                <p style="margin: 5px 0; font-size: 14px;">📅 Issue date: ${fmtDate(data.issuedAt)}</p>
                                <p style="margin: 5px 0; font-size: 14px;">📅 Due date: ${fmtDate(data.dueDate)}</p>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
                            <div style="width: 45%;">
                                <h4 style="margin: 0 0 15px 0; color: #333;">Invoice From:</h4>
                                <p style="margin: 5px 0; line-height: 1.5;">JAF Digital Group Inc.</p>
                                <p style="margin: 5px 0; line-height: 1.5;">Unit D 49th Floor PBCom Tower, 6795 Ayala Avenue, corner V.A. Rufino St, Makati City, Metro Manila, Philippines</p>
                                <p style="margin: 5px 0; line-height: 1.5;">support@timora.ph</p>
                            </div>
                            <div style="width: 45%;">
                                <h4 style="margin: 0 0 15px 0; color: #333;">Invoice To:</h4>
                                <p style="margin: 5px 0; line-height: 1.5;">${data.billToName || '—'}</p>
                                <p style="margin: 5px 0; line-height: 1.5;">${data.billToAddress || '—'}</p>
                                <p style="margin: 5px 0; line-height: 1.5;">${data.billToEmail || '—'}</p>
                            </div>
                        </div>

                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                            <thead>
                                <tr style="background-color: #f8f9fa;">
                                    <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Description</th>
                                    <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Period</th>
                                    ${showQtyRateInPDF ? '<th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Quantity</th>' : ''}
                                    ${showQtyRateInPDF ? '<th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Rate</th>' : ''}
                                    <th style="padding: 12px; border: 1px solid #dee2e6; text-align: right;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${invoiceItemsHTML}
                            </tbody>
                        </table>

                        <div style="display: flex; justify-content: flex-end; margin-bottom: 30px;">
                            <div style="width: 300px;">
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                                    <span>Sub Total:</span>
                                    <span>${fmtMoney(subtotal, data.currency)}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                                    <span>VAT (${vatPercentage}%):</span>
                                    <span>${fmtMoney(calculatedVatAmount, data.currency)}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 2px solid #333; font-weight: bold;">
                                    <span>Total Amount:</span>
                                    <span>${fmtMoney(amountDue, data.currency)}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                                    <span>Amount Paid:</span>
                                    <span>${fmtMoney(amountPaid, data.currency)}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 12px 0; font-weight: bold; border-top: 2px solid #333;">
                                    <span>Balance Due:</span>
                                    <span>${fmtMoney(balance, data.currency)}</span>
                                </div>
                            </div>
                        </div>

                        <div style="border: 1px solid #dee2e6; padding: 20px; background-color: #f8f9fa;">
                            <h4 style="margin: 0 0 15px 0; color: #333;">Terms & Conditions:</h4>
                            <p style="margin: 8px 0; font-size: 12px; display: flex; align-items: baseline;">
                                <span style="color: #007bff; margin-right: 8px;">•</span>
                                All payments must be made according to the agreed schedule.
                            </p>
                            <p style="margin: 8px 0; font-size: 12px; display: flex; align-items: baseline;">
                                <span style="color: #007bff; margin-right: 8px;">•</span>
                                License overage charges apply when employee count exceeds subscription limit during billing period.
                            </p>
                            <p style="margin: 8px 0; font-size: 12px; display: flex; align-items: baseline;">
                                <span style="color: #007bff; margin-right: 8px;">•</span>
                                We are not liable for any indirect, incidental, or consequential damages, including loss of profits, revenue, or data.
                            </p>
                        </div>
                    </div>
                `;

            document.body.appendChild(invoiceContainer);

            // Use window.print() to generate PDF
            const originalContent = document.body.innerHTML;
            document.body.innerHTML = invoiceContainer.innerHTML;

            // Set print styles
            const style = document.createElement('style');
            style.textContent = `
                    @media print {
                        body { margin: 0; }
                        @page { margin: 1in; }
                    }
                `;
            document.head.appendChild(style);

            window.print();

            // Restore original content
            document.body.innerHTML = originalContent;
            document.head.removeChild(style);

            // Re-attach event listeners
            location.reload();
        }

        // Helper function to get invoice icon
        function getInvoiceIcon(type) {
            switch (type) {
                case 'license_overage':
                    return '📊';
                case 'combo':
                    return '🔥';
                case 'consolidated':
                    return '🔗';
                default:
                    return '📄';
            }
        }
    </script>

    <script>
        // Simple helpers
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

        // Updated Invoice modal population
        document.addEventListener('DOMContentLoaded', function () {
            const invoiceModal = document.getElementById('view_invoice');
            if (invoiceModal) {
                invoiceModal.addEventListener('show.bs.modal', function (event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const d = btn.dataset;

                    // Header elements
                    const invNumber = document.getElementById('inv-number');
                    if (invNumber) invNumber.textContent = d.invoiceNumber || '—';

                    const invIssuedAt = document.getElementById('inv-issued-at');
                    if (invIssuedAt) invIssuedAt.textContent = fmtDate(d.issuedAt);

                    const invDueDate = document.getElementById('inv-due-date');
                    if (invDueDate) invDueDate.textContent = fmtDate(d.dueDate);

                    // Show billing cycle for plan upgrade invoices
                    const billingCycleRow = document.getElementById('inv-billing-cycle-row');
                    const billingCycleEl = document.getElementById('inv-billing-cycle');
                    if (billingCycleRow && billingCycleEl && d.invoiceType === 'plan_upgrade' && d
                        .billingCycle) {
                        billingCycleRow.style.display = 'block';
                        billingCycleEl.textContent = d.billingCycle.charAt(0).toUpperCase() + d.billingCycle
                            .slice(1);
                        billingCycleEl.className = d.billingCycle === 'yearly' ? 'badge bg-success' :
                            'badge bg-info';
                    } else if (billingCycleRow) {
                        billingCycleRow.style.display = 'none';
                    }

                    // Invoice type badge
                    const typeBadge = document.getElementById('inv-type-badge');
                    if (typeBadge) {
                        const invoiceType = d.invoiceType || 'subscription';
                        const licenseOverageCount = Number(d.licenseOverageCount || 0);

                        switch (invoiceType) {
                            case 'license_overage':
                                typeBadge.textContent = 'License';
                                typeBadge.className = 'badge bg-info ms-1';
                                break;
                            case 'custom_order':
                                typeBadge.textContent = 'Custom Order';
                                typeBadge.className = 'badge bg-warning ms-1';
                                break;
                            case 'subscription':
                                if (licenseOverageCount > 0) {
                                    typeBadge.textContent = 'Inc. Overage';
                                    typeBadge.className = 'badge bg-primary ms-1';
                                } else {
                                    typeBadge.textContent = 'Subscription';
                                    typeBadge.className = 'badge bg-success ms-1';
                                }
                                break;
                            case 'consolidated':
                                typeBadge.textContent = 'Consolidated';
                                typeBadge.className = 'badge bg-secondary ms-1';
                                break;
                            default:
                                typeBadge.textContent = invoiceType.charAt(0).toUpperCase() + invoiceType
                                    .slice(1);
                                typeBadge.className = 'badge bg-success ms-1';
                                break;
                        }
                    }

                    // Bill To
                    const nameEl = document.getElementById('inv-to-name');
                    if (nameEl) nameEl.textContent = d.billToName || '—';

                    const addrEl = document.getElementById('inv-to-address');
                    if (addrEl) addrEl.textContent = d.billToAddress || '—';

                    const emailEl = document.getElementById('inv-to-email');
                    if (emailEl) emailEl.textContent = d.billToEmail || '—';

                    // Table rows
                    const tbody = document.getElementById('inv-items');
                    if (tbody) {
                        tbody.innerHTML = '';

                        const subscriptionAmount = Number(d.subscriptionAmount || 0);
                        const licenseOverageAmount = Number(d.licenseOverageAmount || 0);
                        const licenseOverageCount = Number(d.licenseOverageCount || 0);
                        const licenseOverageRate = Number(d.licenseOverageRate || 49);
                        const implementationFee = Number(d.implementationFee || 0);
                        const amountDue = Number(d.amountDue || 0);
                        const invoiceType = d.invoiceType || 'subscription';

                        // Determine if we should show Quantity and Rate columns
                        const hasOverage = (invoiceType === 'subscription' && licenseOverageCount > 0) ||
                            invoiceType === 'license_overage' ||
                            invoiceType === 'custom_order';
                        const showQtyRate = hasOverage || (invoiceType === 'subscription' && implementationFee > 0);

                        // Show/hide Quantity and Rate columns in header
                        document.querySelectorAll('.qty-rate-column').forEach(col => {
                            col.style.display = showQtyRate ? '' : 'none';
                        });

                        // Handle custom_order type - use AJAX to fetch items
                        if (invoiceType === 'custom_order') {
                            // Make AJAX call to fetch invoice items
                            fetch(`/billing/invoices/${d.invoiceId}/items`)
                                .then(response => response.json())
                                .then(items => {
                                    tbody.innerHTML = '';
                                    if (items && items.length > 0) {
                                        items.forEach(item => {
                                            const tr = document.createElement('tr');
                                            tr.innerHTML = `
                                            <td>${item.description}</td>
                                            <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                            <td>${item.quantity}</td>
                                            <td>${fmtMoney(item.rate, d.currency)}</td>
                                            <td class="text-end">${fmtMoney(item.amount, d.currency)}</td>
                                        `;
                                            tbody.appendChild(tr);
                                        });
                                    } else {
                                        // Fallback if no items found
                                        const tr = document.createElement('tr');
                                        tr.innerHTML = `
                                        <td colspan="${showQtyRate ? 5 : 3}" class="text-center">No items found</td>
                                    `;
                                        tbody.appendChild(tr);
                                    }
                                })
                                .catch(error => {
                                    console.error('Error fetching invoice items:', error);
                                    const tr = document.createElement('tr');
                                    tr.innerHTML = `
                                    <td colspan="${showQtyRate ? 5 : 3}" class="text-center text-danger">Error loading items</td>
                                `;
                                    tbody.appendChild(tr);
                                });
                        } else if (invoiceType === 'subscription') {
                            if (implementationFee > 0) {
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

                            // Existing subscription logic
                            if (subscriptionAmount > 0) {
                                const tr1 = document.createElement('tr');
                                tr1.innerHTML = showQtyRate ? `
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
                                tbody.appendChild(tr1);
                            }

                            if (licenseOverageCount > 0) {
                                const tr2 = document.createElement('tr');
                                tr2.innerHTML = `
                                <td>New Licenses (Consolidated)</td>
                                <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                <td>${licenseOverageCount}</td>
                                <td>${fmtMoney(licenseOverageRate, d.currency)}</td>
                                <td class="text-end">${fmtMoney(licenseOverageAmount, d.currency)}</td>
                            `;
                                tbody.appendChild(tr2);
                            }

                            if (subscriptionAmount === 0 && licenseOverageCount === 0 && amountDue > 0) {
                                const tr = document.createElement('tr');
                                tr.innerHTML = showQtyRate ? `
                                <td>${d.plan || '—'} Subscription</td>
                                <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                <td>1</td>
                                <td>${fmtMoney(amountDue, d.currency)}</td>
                                <td class="text-end">${fmtMoney(amountDue, d.currency)}</td>
                            ` : `
                                <td>${d.plan || '—'} Subscription</td>
                                <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                <td class="text-end">${fmtMoney(amountDue, d.currency)}</td>
                            `;
                                tbody.appendChild(tr);
                            }
                        } else if (invoiceType === 'license_overage') {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                            <td>License Overage</td>
                            <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                            <td>${licenseOverageCount || 1}</td>
                            <td>${fmtMoney(licenseOverageRate, d.currency)}</td>
                            <td class="text-end">${fmtMoney(licenseOverageAmount || amountDue, d.currency)}</td>
                        `;
                            tbody.appendChild(tr);
                        } else if (invoiceType === 'plan_upgrade') {
                            const implementationFee = Number(d.implementationFee || 0);
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
                        } else if (invoiceType === 'implementation_fee') {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                            <td>Implementation Fee: ${d.plan || 'Plan'}
                                <br><small class="text-muted">One-time setup fee</small>
                            </td>
                            <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                            <td class="text-end">${fmtMoney(amountDue, d.currency)}</td>
                        `;
                            tbody.appendChild(tr);
                        } else {
                            const tr = document.createElement('tr');
                            tr.innerHTML = showQtyRate ? `
                            <td>${d.plan || 'Subscription'}</td>
                            <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                            <td>1</td>
                            <td>${fmtMoney(amountDue, d.currency)}</td>
                            <td class="text-end">${fmtMoney(amountDue, d.currency)}</td>
                        ` : `
                            <td>${d.plan || 'Subscription'}</td>
                            <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                            <td class="text-end">${fmtMoney(amountDue, d.currency)}</td>
                        `;
                            tbody.appendChild(tr);
                        }
                    }

                    // Totals
                    const amountPaid = Number(d.amountPaid || 0);
                    const amountDue = Number(d.amountDue || 0);
                    const vatPercentage = Number(d.vatPercentage || 12);
                    const vatAmount = Number(d.vatAmount || 0);
                    const subtotal = Number(d.subtotal || 0);

                    let calculatedSubtotal = subtotal;
                    let calculatedVatAmount = vatAmount;

                    if (subtotal === 0 && amountDue > 0) {
                        calculatedSubtotal = amountDue / (1 + (vatPercentage / 100));
                        calculatedVatAmount = amountDue - calculatedSubtotal;
                    }

                    const subtotalEl = document.getElementById('inv-subtotal');
                    if (subtotalEl) subtotalEl.textContent = fmtMoney(calculatedSubtotal, d.currency);

                    const vatPercentageEl = document.getElementById('inv-vat-percentage');
                    if (vatPercentageEl) vatPercentageEl.textContent = vatPercentage;

                    const vatAmountEl = document.getElementById('inv-vat-amount');
                    if (vatAmountEl) vatAmountEl.textContent = fmtMoney(calculatedVatAmount, d.currency);

                    const totalAmountEl = document.getElementById('inv-total-amount');
                    if (totalAmountEl) totalAmountEl.textContent = fmtMoney(amountDue, d.currency);

                    const amountPaidEl = document.getElementById('inv-amount-paid');
                    if (amountPaidEl) amountPaidEl.textContent = fmtMoney(amountPaid, d.currency);

                    const balanceEl = document.getElementById('inv-balance');
                    if (balanceEl) balanceEl.textContent = fmtMoney(Math.max(amountDue - amountPaid, 0), d
                        .currency);
                });
            }
        });
    </script>

    {{-- ✅ ENHANCED: Populate Invoice Modal with License Overage Support --}}
    {{--
    <script>
        // Simple helpers
        function fmtMoney(value, currency) {
            const num = Number(value ?? 0);
            try {
                return new Intl.NumberFormat(undefined, {
                    style: 'currency',
                    currency: currency || 'PHP'
                }).format(num);
            } catch (_) {
                // Fallback if currency code is unexpected
                return `₱${num.toFixed(2)}`;
            }
        }

        function fmtDate(isoLike) {
            if (!isoLike) return '—';
            const d = new Date(isoLike);
            return isNaN(d) ? isoLike : d.toLocaleDateString();
        }

        // ✅ FIXED: Invoice modal population
        document.addEventListener('DOMContentLoaded', function () {
            // Invoice modal event listener
            const invoiceModal = document.getElementById('view_invoice');
            if (invoiceModal) {
                invoiceModal.addEventListener('show.bs.modal', function (event) {
                    const btn = event.relatedTarget; // <a ...> that opened the modal
                    if (!btn) {
                        console.log('No button found');
                        return;
                    }

                    console.log('Button clicked, processing data...'); // Debug log

                    // Read all data-* attributes
                    const d = btn.dataset;
                    console.log('Dataset:', d); // Debug log

                    // ✅ FIXED: Header elements with better error handling
                    const invNumber = document.getElementById('inv-number');
                    if (invNumber) {
                        invNumber.textContent = d.invoiceNumber || '—';
                        console.log('Invoice number set:', d.invoiceNumber);
                    }

                    const invIssuedAt = document.getElementById('inv-issued-at');
                    if (invIssuedAt) {
                        invIssuedAt.textContent = fmtDate(d.issuedAt);
                    }

                    const invDueDate = document.getElementById('inv-due-date');
                    if (invDueDate) {
                        invDueDate.textContent = fmtDate(d.dueDate);
                    }

                    // ✅ NEW: Show billing cycle for plan upgrade invoices
                    const billingCycleRow = document.getElementById('inv-billing-cycle-row');
                    const billingCycleEl = document.getElementById('inv-billing-cycle');
                    if (billingCycleRow && billingCycleEl && d.invoiceType === 'plan_upgrade' && d.billingCycle) {
                        billingCycleRow.style.display = 'block';
                        billingCycleEl.textContent = d.billingCycle.charAt(0).toUpperCase() + d.billingCycle.slice(1);
                        billingCycleEl.className = d.billingCycle === 'yearly' ? 'badge bg-success' : 'badge bg-info';
                    } else if (billingCycleRow) {
                        billingCycleRow.style.display = 'none';
                    }

                    // ✅ FIXED: Invoice type badge with null checks
                    const typeBadge = document.getElementById('inv-type-badge');
                    if (typeBadge) {
                        const invoiceType = d.invoiceType || 'subscription';
                        const licenseOverageCount = Number(d.licenseOverageCount || 0);

                        console.log('Invoice type:', invoiceType, 'Overage count:', licenseOverageCount);

                        switch (invoiceType) {
                            case 'license_overage':
                                typeBadge.textContent = 'License';
                                typeBadge.className = 'badge bg-info ms-1';
                                break;
                            case 'subscription':
                                if (licenseOverageCount > 0) {
                                    typeBadge.textContent = 'Inc. Overage';
                                    typeBadge.className = 'badge bg-primary ms-1';
                                } else {
                                    typeBadge.textContent = 'Subscription';
                                    typeBadge.className = 'badge bg-success ms-1';
                                }
                                break;
                            case 'consolidated':
                                typeBadge.textContent = 'Consolidated';
                                typeBadge.className = 'badge bg-secondary ms-1';
                                break;
                            default:
                                typeBadge.textContent = 'Subscription';
                                typeBadge.className = 'badge bg-success ms-1';
                                break;
                        }
                    }

                    // ✅ FIXED: Bill To with null checks
                    const nameEl = document.getElementById('inv-to-name');
                    if (nameEl) nameEl.textContent = d.billToName || '—';

                    const addrEl = document.getElementById('inv-to-address');
                    if (addrEl) addrEl.textContent = d.billToAddress || '—';

                    const emailEl = document.getElementById('inv-to-email');
                    if (emailEl) emailEl.textContent = d.billToEmail || '—';

                    // ✅ FIXED: Table rows with better error handling
                    const tbody = document.getElementById('inv-items');
                    if (tbody) {
                        tbody.innerHTML = ''; // reset

                        const subscriptionAmount = Number(d.subscriptionAmount || 0);
                        const licenseOverageAmount = Number(d.licenseOverageAmount || 0);
                        const licenseOverageCount = Number(d.licenseOverageCount || 0);
                        const licenseOverageRate = Number(d.licenseOverageRate || 49);
                        const implementationFee = Number(d.implementationFee || 0);
                        const amountDue = Number(d.amountDue || 0);
                        const invoiceType = d.invoiceType || 'subscription';

                        // ✅ Determine if we should show Quantity and Rate columns
                        const hasOverage = (invoiceType === 'subscription' && licenseOverageCount > 0) ||
                            invoiceType === 'license_overage';
                        const showQtyRate = hasOverage || (invoiceType === 'subscription' && implementationFee > 0);

                        // Show/hide Quantity and Rate columns in header
                        document.querySelectorAll('.qty-rate-column').forEach(col => {
                            col.style.display = showQtyRate ? '' : 'none';
                        });

                        // ✅ UPDATED: Table rows logic for subscription invoices with overage
                        if (invoiceType === 'subscription') {
                            if (implementationFee > 0) {
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

                            // Add subscription row if amount > 0
                            if (subscriptionAmount > 0) {
                                const tr1 = document.createElement('tr');
                                tr1.innerHTML = showQtyRate ? `
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
                                tbody.appendChild(tr1);
                            }

                            // Add license overage row if count > 0
                            if (licenseOverageCount > 0) {
                                const tr2 = document.createElement('tr');
                                tr2.innerHTML = `
                                    <td>New Licenses (Consolidated)</td>
                                    <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                    <td>${licenseOverageCount}</td>
                                    <td>${fmtMoney(licenseOverageRate, d.currency)}</td>
                                    <td class="text-end">${fmtMoney(licenseOverageAmount, d.currency)}</td>
                                `;
                                tbody.appendChild(tr2);
                            }

                            // If no specific amounts, show total as subscription
                            if (subscriptionAmount === 0 && licenseOverageCount === 0 && amountDue > 0) {
                                const tr = document.createElement('tr');
                                tr.innerHTML = showQtyRate ? `
                                    <td>${d.plan || '—'} Subscription</td>
                                    <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                    <td>1</td>
                                    <td>${fmtMoney(amountDue, d.currency)}</td>
                                    <td class="text-end">${fmtMoney(amountDue, d.currency)}</td>
                                ` : `
                                    <td>${d.plan || '—'} Subscription</td>
                                    <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                    <td class="text-end">${fmtMoney(amountDue, d.currency)}</td>
                                `;
                                tbody.appendChild(tr);
                            }
                        } else if (invoiceType === 'license_overage') {
                            // License Overage Only
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>License Overage</td>
                                <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                <td>${licenseOverageCount || 1}</td>
                                <td>${fmtMoney(licenseOverageRate, d.currency)}</td>
                                <td class="text-end">${fmtMoney(licenseOverageAmount || amountDue, d.currency)}</td>
                            `;
                            tbody.appendChild(tr);
                        } else if (invoiceType === 'plan_upgrade') {
                            // Plan Upgrade Invoice - Show detailed breakdown WITHOUT Quantity and Rate
                            const implementationFee = Number(d.implementationFee || 0);
                            const planUpgradeAmount = Number(d.subscriptionAmount || 0);

                            // Add implementation fee difference if it exists
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

                            // Add plan price difference
                            const trPlan = document.createElement('tr');
                            trPlan.innerHTML = `
                                    <td>Plan Price Difference
                                        <br><small class="text-muted">From ${d.currentPlan || 'Current Plan'} to ${d.plan || 'New Plan'} (${d.billingCycle ? d.billingCycle.charAt(0).toUpperCase() + d.billingCycle.slice(1) : 'N/A'})</small>
                                    </td>
                                    <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                    <td class="text-end">${fmtMoney(planUpgradeAmount, d.currency)}</td>
                                `;
                            tbody.appendChild(trPlan);
                        } else if (invoiceType === 'implementation_fee') {
                            // Implementation Fee Invoice - Fetch items to get description
                            fetch(`/billing/invoices/${d.invoiceId}/items`)
                                .then(response => response.json())
                                .then(items => {
                                    const tr = document.createElement('tr');
                                    if (items && items.length > 0) {
                                        const item = items[0];
                                        tr.innerHTML = `
                                                <td>${item.description}
                                                    <br><small class="text-muted">One-time setup fee</small>
                                                </td>
                                                <td>${item.period || (fmtDate(d.periodStart) + ' - ' + fmtDate(d.periodEnd))}</td>
                                                <td class="text-end">${fmtMoney(item.amount, d.currency)}</td>
                                            `;
                                    } else {
                                        // Fallback
                                        tr.innerHTML = `
                                                <td>Implementation Fee: ${d.plan || 'Plan'}
                                                    <br><small class="text-muted">One-time setup fee</small>
                                                </td>
                                                <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                                <td class="text-end">${fmtMoney(amountDue, d.currency)}</td>
                                            `;
                                    }
                                    tbody.appendChild(tr);
                                })
                                .catch(error => {
                                    console.error('Error fetching invoice items:', error);
                                    // Fallback on error
                                    const tr = document.createElement('tr');
                                    tr.innerHTML = `
                                            <td>Implementation Fee: ${d.plan || 'Plan'}
                                                <br><small class="text-muted">One-time setup fee</small>
                                            </td>
                                            <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                            <td class="text-end">${fmtMoney(amountDue, d.currency)}</td>
                                        `;
                                    tbody.appendChild(tr);
                                });
                        } else {
                            // Default fallback - use showQtyRate to determine columns
                            const tr = document.createElement('tr');
                            tr.innerHTML = showQtyRate ? `
                                <td>${d.plan || 'Subscription'}</td>
                                <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                <td>1</td>
                                <td>${fmtMoney(amountDue, d.currency)}</td>
                                <td class="text-end">${fmtMoney(amountDue, d.currency)}</td>
                            ` : `
                                <td>${d.plan || 'Subscription'}</td>
                                <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                <td class="text-end">${fmtMoney(amountDue, d.currency)}</td>
                            `;
                            tbody.appendChild(tr);
                        }

                        console.log('Table rows added, tbody content:', tbody.innerHTML);
                    }

                    // ✅ FIXED: Totals with VAT display
                    const amountPaid = Number(d.amountPaid || 0);
                    const amountDue = Number(d.amountDue || 0);
                    const vatPercentage = Number(d.vatPercentage || 12);
                    const vatAmount = Number(d.vatAmount || 0);
                    const subtotal = Number(d.subtotal || 0);

                    // If subtotal and VAT are not provided, calculate them
                    let calculatedSubtotal = subtotal;
                    let calculatedVatAmount = vatAmount;

                    if (subtotal === 0 && amountDue > 0) {
                        // Calculate VAT from total (VAT-inclusive)
                        calculatedSubtotal = amountDue / (1 + (vatPercentage / 100));
                        calculatedVatAmount = amountDue - calculatedSubtotal;
                    }

                    const subtotalEl = document.getElementById('inv-subtotal');
                    if (subtotalEl) {
                        subtotalEl.textContent = fmtMoney(calculatedSubtotal, d.currency);
                    }

                    const vatPercentageEl = document.getElementById('inv-vat-percentage');
                    if (vatPercentageEl) {
                        vatPercentageEl.textContent = vatPercentage;
                    }

                    const vatAmountEl = document.getElementById('inv-vat-amount');
                    if (vatAmountEl) {
                        vatAmountEl.textContent = fmtMoney(calculatedVatAmount, d.currency);
                    }

                    const totalAmountEl = document.getElementById('inv-total-amount');
                    if (totalAmountEl) {
                        totalAmountEl.textContent = fmtMoney(amountDue, d.currency);
                    }

                    const amountPaidEl = document.getElementById('inv-amount-paid');
                    if (amountPaidEl) {
                        amountPaidEl.textContent = fmtMoney(amountPaid, d.currency);
                    }

                    const balanceEl = document.getElementById('inv-balance');
                    if (balanceEl) {
                        balanceEl.textContent = fmtMoney(Math.max(amountDue - amountPaid, 0), d.currency);
                    }

                    console.log('Modal population completed'); // Debug log
                });
            }
        });
    </script> --}}
@endpush