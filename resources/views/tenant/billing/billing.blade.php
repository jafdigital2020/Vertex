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
                <!-- License Usage Warning -->
                @if ($activeLicenseCount > ($subscription->active_license ?? 0))
                    @php
                        $overageCount = $activeLicenseCount - ($subscription->active_license ?? 0);
                        $overageAmount = $overageCount * 49;
                    @endphp
                    <div class="col-12">
                        <div class="alert alert-warning mb-3">
                            <h6><i class="ti ti-alert-triangle me-2"></i>License Overage Detected</h6>
                            <p class="mb-2">You are using <strong>{{ $overageCount }}</strong> more license(s) than your
                                plan allows.</p>
                            <p class="mb-2">Additional charges: <strong>₱{{ number_format($overageAmount, 2) }}</strong>
                                (₱49 per extra license)</p>
                            <small>An additional invoice will be generated for the overage usage.</small>
                        </div>
                    </div>
                @endif

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
                                <h3 class="mb-0 me-2">₱{{ number_format($subscription->amount_paid ?? 0, 2) }}</h3>
                                <span class="text-muted">/ {{ $subscription->billing_cycle ?? 'N/A' }}</span>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>License Usage: {{ $activeLicenseCount }} /
                                        {{ $subscription->active_license ?? '0' }}</span>
                                    @if ($activeLicenseCount > ($subscription->active_license ?? 0))
                                        <span class="text-warning">
                                            <i class="ti ti-alert-triangle"></i>
                                            +{{ $activeLicenseCount - ($subscription->active_license ?? 0) }} overage
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

                <!-- Invoices Card -->
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
                                        <th>Pay</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoice as $inv)
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
                                                    data-currency="{{ $inv->currency }}"
                                                    data-due-date="{{ $inv->due_date }}" data-status="{{ $inv->status }}"
                                                    data-period-start="{{ $inv->period_start }}"
                                                    data-period-end="{{ $inv->period_end }}"
                                                    data-issued-at="{{ $inv->issued_at }}"
                                                    data-bill-to-name="{{ $inv->tenant->tenant_name ?? 'N/A' }}"
                                                    data-bill-to-address="{{ $inv->tenant->tenant_address ?? 'N/A' }}"
                                                    data-bill-to-email="{{ $inv->tenant->tenant_email ?? 'N/A' }}"
                                                    data-plan="{{ $inv->subscription->plan->name ?? 'N/A' }}"
                                                    data-billing-cycle="{{ $inv->subscription->billing_cycle ?? 'N/A' }}">

                                                    @if (($inv->invoice_type ?? 'subscription') === 'license_overage')
                                                        📊 {{ $inv->invoice_number ?? '-' }}
                                                        <span class="badge bg-info ms-1">License</span>
                                                    @elseif(($inv->invoice_type ?? 'subscription') === 'combo')
                                                        📄 {{ $inv->invoice_number ?? '-' }}
                                                        <span class="badge bg-primary ms-1">Combo</span>
                                                    @else
                                                        📄 {{ $inv->invoice_number ?? '-' }}
                                                    @endif
                                                </a>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($inv->issued_at)->format('Y-m-d') }}</td>
                                            <td>₱{{ number_format($inv->amount_due ?? 0, 2) }}</td>
                                            <td>
                                                @if (($inv->invoice_type ?? 'subscription') === 'license_overage')
                                                    License Overage ({{ $inv->license_overage_count ?? 0 }} licenses)
                                                @elseif(($inv->invoice_type ?? 'subscription') === 'combo')
                                                    {{ $inv->subscription->plan->name ?? '-' }} + License Overage
                                                @else
                                                    {{ $inv->subscription->plan->name ?? '-' }}
                                                @endif
                                            </td>
                                            <td>
                                                @if ($inv->status === 'paid')
                                                    -
                                                @else
                                                    <button class="btn btn-outline-primary btn-sm pay-invoice-btn"
                                                        data-invoice-id="{{ $inv->id }}"
                                                        data-amount="{{ $inv->amount_due }}">
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
                                                    data-currency="{{ $inv->currency }}"
                                                    data-due-date="{{ $inv->due_date }}" data-status="{{ $inv->status }}"
                                                    data-period-start="{{ $inv->period_start }}"
                                                    data-period-end="{{ $inv->period_end }}"
                                                    data-issued-at="{{ $inv->issued_at }}"
                                                    data-bill-to-name="{{ $inv->tenant->tenant_name ?? 'N/A' }}"
                                                    data-bill-to-address="{{ $inv->tenant->tenant_address ?? 'N/A' }}"
                                                    data-bill-to-email="{{ $inv->tenant->tenant_email ?? 'N/A' }}"
                                                    data-plan="{{ $inv->subscription->plan->name ?? 'N/A' }}"
                                                    data-billing-cycle="{{ $inv->subscription->billing_cycle ?? 'N/A' }}">
                                                    <i class="ti ti-download me-1"></i>Download
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-light border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    <small>{{ $invoice->links() }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Invoice -->
        <div class="modal fade" id="view_invoice">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-body p-5">

                        <div class="row justify-content-between align-items-center mb-3">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <img src="{{ URL::asset('build/img/timora-logo.png') }}" class="img-fluid"
                                        alt="logo" style="max-width: 150px; height: auto;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-end mb-3">
                                    <h5 class="text-dark mb-1">Invoice</h5>
                                    <p class="mb-1 fw-normal">
                                        <i class="ti ti-file-invoice me-1"></i><span id="inv-number">—</span>
                                    </p>
                                    <p class="mb-1 fw-normal">
                                        <i class="ti ti-calendar me-1"></i>Issue date : <span id="inv-issued-at">—</span>
                                    </p>
                                    <p class="fw-normal">
                                        <i class="ti ti-calendar me-1"></i>Due date : <span id="inv-due-date">—</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3 d-flex justify-content-between">
                            <div class="col-md-7">
                                <p class="text-dark mb-2 fw-medium fs-16">Invoice From :</p>
                                <div>
                                    <p class="mb-1">Timora</p>
                                    <p class="mb-1">Unit D 49th Floor PBCom Tower, 6795 Ayala Avenue, corner V.A.
                                        Rufino
                                        St,
                                        Makati City, Metro Manila, Philippines</p>
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

                        <div class="mb-4">
                            <div class="table-responsive mb-3">
                                <table class="table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Plan</th>
                                            <th>Billing Cycle</th>
                                            <th>Start Date</th>
                                            <th>Expiring On</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="inv-items">
                                        <!-- rows inserted here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-3 d-flex justify-content-between">
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center pe-3">
                                    <p class="text-dark fw-medium mb-0">Sub Total</p>
                                    <p class="mb-2" id="inv-subtotal">—</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pe-3">
                                    <p class="text-dark fw-medium mb-0">Tax</p>
                                    <p class="mb-2" id="inv-tax">—</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pe-3">
                                    <p class="text-dark fw-medium mb-0">Amount Paid</p>
                                    <p class="mb-2" id="inv-amount-paid">—</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pe-3">
                                    <p class="text-dark fw-medium mb-0">Balance Due</p>
                                    <p class="text-dark fw-medium mb-2" id="inv-balance">—</p>
                                </div>
                            </div>
                        </div>

                        <div class="card border mb-0">
                            <div class="card-body">
                                <p class="text-dark fw-medium mb-2">Terms & Conditions:</p>
                                <p class="fs-12 fw-normal d-flex align-items-baseline mb-2">
                                    <i class="ti ti-point-filled text-primary me-1"></i>
                                    All payments must be made according to the agreed schedule.
                                </p>
                                <p class="fs-12 fw-normal d-flex align-items-baseline">
                                    <i class="ti ti-point-filled text-primary me-1"></i>
                                    We are not liable for any indirect, incidental, or consequential damages, including
                                    loss
                                    of
                                    profits, revenue, or data.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- /View Invoice -->

        @include('layout.partials.footer-company')
        <!-- /Page Wrapper -->

        @component('components.modal-popup')
        @endcomponent
    @endsection

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle pay button clicks
                document.querySelectorAll('.pay-invoice-btn').forEach(button => {
                    button.addEventListener('click', function() {
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
                    button.addEventListener('click', function(e) {
                        e.preventDefault();

                        const invoiceData = {
                            invoiceId: this.dataset.invoiceId,
                            invoiceNumber: this.dataset.invoiceNumber,
                            amountDue: this.dataset.amountDue,
                            amountPaid: this.dataset.amountPaid,
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
                            billingCycle: this.dataset.billingCycle
                        };

                        generateInvoicePDF(invoiceData);
                    });
                });
            });

            function generateInvoicePDF(data) {
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
                const tax = 0; // Assuming no tax for now
                const subtotal = amountDue - tax;
                const balance = Math.max(amountDue - amountPaid, 0);

                invoiceContainer.innerHTML = `
                <div style="margin-bottom: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 30px;">
                        <div>
                            <img src="{{ URL::asset('build/img/timora-logo.png') }}" style="max-width: 150px; height: auto;" alt="Timora Logo">
                        </div>
                        <div style="text-align: right;">
                            <h2 style="margin: 0 0 10px 0; color: #333;">Invoice</h2>
                            <p style="margin: 5px 0; font-size: 14px;">📄 ${data.invoiceNumber || '—'}</p>
                            <p style="margin: 5px 0; font-size: 14px;">📅 Issue date: ${fmtDate(data.issuedAt)}</p>
                            <p style="margin: 5px 0; font-size: 14px;">📅 Due date: ${fmtDate(data.dueDate)}</p>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
                        <div style="width: 45%;">
                            <h4 style="margin: 0 0 15px 0; color: #333;">Invoice From:</h4>
                            <p style="margin: 5px 0; line-height: 1.5;">Timora</p>
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
                                <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Plan</th>
                                <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Billing Cycle</th>
                                <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Start Date</th>
                                <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Expiring On</th>
                                <th style="padding: 12px; border: 1px solid #dee2e6; text-align: right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 12px; border: 1px solid #dee2e6;">${data.plan || '—'}</td>
                                <td style="padding: 12px; border: 1px solid #dee2e6;">${data.billingCycle || '—'}</td>
                                <td style="padding: 12px; border: 1px solid #dee2e6;">${fmtDate(data.periodStart)}</td>
                                <td style="padding: 12px; border: 1px solid #dee2e6;">${fmtDate(data.periodEnd)}</td>
                                <td style="padding: 12px; border: 1px solid #dee2e6; text-align: right;">${fmtMoney(data.amountDue, data.currency)}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="display: flex; justify-content: flex-end; margin-bottom: 30px;">
                        <div style="width: 300px;">
                            <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                                <span>Sub Total:</span>
                                <span>${fmtMoney(subtotal, data.currency)}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                                <span>Tax:</span>
                                <span>${fmtMoney(tax, data.currency)}</span>
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
        </script>

        {{-- Populate --}}
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
                    return `${currency || ''} ${num.toFixed(2)}`.trim();
                }
            }

            function fmtDate(isoLike) {
                if (!isoLike) return '—';
                const d = new Date(isoLike);
                return isNaN(d) ? isoLike : d.toLocaleDateString();
            }

            document.getElementById('view_invoice')
                .addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget; // <a ...> that opened the modal
                    if (!btn) return;

                    // Read all data-* attributes
                    const d = btn.dataset;

                    // Header
                    document.getElementById('inv-number').textContent = d.invoiceNumber || '—';
                    document.getElementById('inv-issued-at').textContent = fmtDate(d.issuedAt);
                    document.getElementById('inv-due-date').textContent = fmtDate(d.dueDate);

                    // Bill To (optional)
                    const nameEl = document.getElementById('inv-to-name');
                    if (nameEl) nameEl.textContent = d.billToName || '—';
                    const addrEl = document.getElementById('inv-to-address');
                    if (addrEl) addrEl.textContent = d.billToAddress || '—';
                    const emailEl = document.getElementById('inv-to-email');
                    if (emailEl) emailEl.textContent = d.billToEmail || '—';

                    // Table rows
                    const tbody = document.getElementById('inv-items');
                    tbody.innerHTML = ''; // reset

                    // If you passed multiple items as JSON
                    const items = (() => {
                        try {
                            return d.items ? JSON.parse(d.items) : null;
                        } catch {
                            return null;
                        }
                    })();

                    if (Array.isArray(items) && items.length) {
                        items.forEach(it => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
          <td>${it.plan ?? '—'}</td>
          <td>${it.billing_cycle ?? '—'}</td>
          <td>${fmtDate(it.created_at)}</td>
          <td>${fmtDate(it.expiring_on)}</td>
          <td class="text-end">${fmtMoney(it.amount, d.currency)}</td>
        `;
                            tbody.appendChild(tr);
                        });
                    } else {
                        // Single "row" using your existing fields
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
        <td>${d.plan ?? '—'}</td>
        <td>${d.billingCycle ?? '—'}</td>
        <td>${fmtDate(d.periodStart)}</td>
        <td>${fmtDate(d.periodEnd)}</td>
        <td class="text-end">${fmtMoney(d.amountDue, d.currency)}</td>
      `;
                        tbody.appendChild(tr);
                    }

                    // Totals
                    const amountDue = Number(d.amountDue ?? 0);
                    const amountPaid = Number(d.amountPaid ?? 0);
                    const tax = Number(d.tax ?? 0); // pass data-tax if you have it

                    document.getElementById('inv-subtotal').textContent = fmtMoney(amountDue - tax, d.currency);
                    document.getElementById('inv-tax').textContent = fmtMoney(tax, d.currency);
                    document.getElementById('inv-amount-paid').textContent = fmtMoney(amountPaid, d.currency);
                    document.getElementById('inv-balance').textContent = fmtMoney(Math.max(amountDue - amountPaid, 0), d
                        .currency);
                });
        </script>
    @endpush
