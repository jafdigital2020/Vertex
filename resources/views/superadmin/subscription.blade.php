<?php $page = 'subscription'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Subscription</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Superadmin
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Subscription</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
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
                    <div class="head-icons">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->


            <div class="row">
                <div class="col-xl-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body ">
                            <div class="border-bottom pb-3 mb-3">
                                <div class="row align-items-center">
                                    <div class="col-7">
                                        <div>
                                            <span class="fs-14 fw-normal text-truncate mb-1">Total Transaction</span>
                                            <h5>$5,340</h5>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <div>
                                            <span class="subscription-line-1" data-width="100%">6,2,8,4,3,8,1,3,6,5,9,2,8,1,4,8,9,8,2,1</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <p class="fs-12 fw-normal d-flex align-items-center text-truncate">
                                    <span class="text-primary fs-12 d-flex align-items-center me-1">
                                    <i class="ti ti-arrow-wave-right-up me-1"></i>+19.01%</span>from
                                    last week
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body ">
                            <div class="border-bottom pb-3 mb-3">
                                <div class="row align-items-center">
                                    <div class="col-7">
                                        <div>
                                            <span class="fs-14 fw-normal text-truncate mb-1">Total Subscribers</span>
                                            <h5>600</h5>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <div>
                                            <span class="subscription-line-2" data-width="100%">6,2,8,4,3,8,1,3,6,5,9,2,8,1,4,8,9,8,2,1</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <p class="fs-12 fw-normal d-flex align-items-center text-truncate">
                                    <span class="text-primary fs-12 d-flex align-items-center me-1">
                                    <i class="ti ti-arrow-wave-right-up me-1"></i>+19.01%</span>from
                                    last week
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body ">
                            <div class="border-bottom pb-3 mb-3">
                                <div class="row align-items-center">
                                    <div class="col-7">
                                        <div>
                                            <span class="fs-14 fw-normal text-truncate mb-1">Active Subscribers</span>
                                            <h5>560</h5>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <div>
                                            <span class="subscription-line-3" data-width="100%">6,2,8,4,3,8,1,3,6,5,9,2,8,1,4,8,9,8,2,1</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <p class="fs-12 fw-normal d-flex align-items-center text-truncate">
                                    <span class="text-primary fs-12 d-flex align-items-center me-1">
                                    <i class="ti ti-arrow-wave-right-up me-1"></i>+19.01%</span>from
                                    last week
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body ">
                            <div class="border-bottom pb-3 mb-3">
                                <div class="row align-items-center">
                                    <div class="col-7">
                                        <div>
                                            <span class="fs-14 fw-normal text-truncate mb-1">Expired Subscribers</span>
                                            <h5>40</h5>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <div>
                                            <span class="subscription-line-4" data-width="100%">6,2,8,4,3,8,1,3,6,5,9,2,8,1,4,8,9,8,2,1</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <p class="fs-12 fw-normal d-flex align-items-center text-truncate">
                                    <span class="text-primary fs-12 d-flex align-items-center me-1">
                                    <i class="ti ti-arrow-wave-right-up me-1"></i>+19.01%</span>from
                                    last week
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Subscription List</h5>
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
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Advanced (Monthly)</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Basic (Yearly)</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Enterprise (Monthly)</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                Select Status
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Paid</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Unpaid</a>
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
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Subscriber</th>
                                    <th>Plan</th>
                                    <th>Billing Cycle</th>
                                    <th>Payment Method</th>
                                    <th>Amount</th>
                                    <th>Created Date</th>
                                    <th>Expiring On</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($subscriptions as $subscription)
                                    @php
                                        $latestInvoice = $subscription->invoices->first();
                                        $vatPercentage = $latestInvoice && $latestInvoice->subscription && $latestInvoice->subscription->plan
                                            ? ($latestInvoice->subscription->plan->vat_percentage ?? 12)
                                            : 12;
                                        $subtotal = $latestInvoice
                                            ? (($latestInvoice->amount_due ?? 0) - ($latestInvoice->vat_amount ?? 0))
                                            : 0;
                                        $calculatedSubtotal = $subtotal;
                                        $calculatedVatAmount = $latestInvoice->vat_amount ?? 0;
                                        if ($latestInvoice && $subtotal === 0 && ($latestInvoice->amount_due ?? 0) > 0) {
                                            $calculatedSubtotal = ($latestInvoice->amount_due ?? 0) / (1 + ($vatPercentage / 100));
                                            $calculatedVatAmount = ($latestInvoice->amount_due ?? 0) - $calculatedSubtotal;
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="#" class="avatar avatar-md border rounded-circle">
                                                    <img src="{{ URL::asset('build/img/company/company-01.svg') }}" class="img-fluid" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium">
                                                        <a href="#">{{ $subscription->tenant->tenant_name ?? 'N/A' }}</a>
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $subscription->plan->name ?? 'N/A' }}</td>
                                        <td>{{ ucfirst($subscription->billing_cycle ?? 'N/A') }}</td>
                                        <td>—</td>
                                        <td>{{ $latestInvoice ? '₱' . number_format($latestInvoice->amount_due ?? 0, 2) : '—' }}</td>
                                        <td>{{ $latestInvoice && $latestInvoice->issued_at ? \Carbon\Carbon::parse($latestInvoice->issued_at)->format('d M Y') : '—' }}</td>
                                        <td>{{ $subscription->subscription_end ? \Carbon\Carbon::parse($subscription->subscription_end)->format('d M Y') : '—' }}</td>
                                        <td>
                                            <span class="badge badge-success d-flex align-items-center badge-xs">
                                                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($latestInvoice->status ?? 'paid') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                @if ($latestInvoice)
                                                    <a href="#" class="me-2 invoice-details-btn" data-bs-toggle="modal" data-bs-target="#view_invoice"
                                                        data-invoice-id="{{ $latestInvoice->id }}"
                                                        data-invoice-number="{{ $latestInvoice->invoice_number }}"
                                                        data-invoice-type="{{ $latestInvoice->invoice_type ?? 'subscription' }}"
                                                        data-amount-due="{{ $latestInvoice->amount_due }}"
                                                        data-amount-paid="{{ $latestInvoice->amount_paid }}"
                                                        data-subscription-amount="{{ $latestInvoice->subscription_amount ?? $latestInvoice->amount_due }}"
                                                        data-license-overage-count="{{ $latestInvoice->license_overage_count ?? 0 }}"
                                                        data-license-overage-amount="{{ $latestInvoice->license_overage_amount ?? 0 }}"
                                                        data-license-overage-rate="{{ $latestInvoice->license_overage_rate ?? 49 }}"
                                                        data-implementation-fee="{{ $latestInvoice->implementation_fee ?? 0 }}"
                                                        data-vat-percentage="{{ $vatPercentage }}"
                                                        data-vat-amount="{{ $calculatedVatAmount }}"
                                                        data-subtotal="{{ $calculatedSubtotal }}"
                                                        data-currency="{{ $latestInvoice->currency ?? 'PHP' }}"
                                                        data-due-date="{{ $latestInvoice->due_date }}"
                                                        data-status="{{ $latestInvoice->status }}"
                                                        data-period-start="{{ $latestInvoice->period_start }}"
                                                        data-period-end="{{ $latestInvoice->period_end }}"
                                                        data-issued-at="{{ $latestInvoice->issued_at }}"
                                                        data-bill-to-name="{{ $subscription->tenant->tenant_name ?? 'N/A' }}"
                                                        data-bill-to-address="{{ $subscription->tenant->tenant_address ?? 'N/A' }}"
                                                        data-bill-to-email="{{ $subscription->tenant->tenant_email ?? 'N/A' }}"
                                                        data-plan="{{ $latestInvoice->invoice_type === 'plan_upgrade' && $latestInvoice->upgradePlan ? $latestInvoice->upgradePlan->name : $subscription->plan->name ?? 'N/A' }}"
                                                        data-current-plan="{{ $subscription->plan->name ?? 'N/A' }}"
                                                        data-billing-cycle="{{ $latestInvoice->invoice_type === 'plan_upgrade' && $latestInvoice->billing_cycle ? $latestInvoice->billing_cycle : $subscription->billing_cycle ?? 'N/A' }}"
                                                        data-has-wizard-items="{{ $latestInvoice->items && $latestInvoice->items->count() > 0 ? 'true' : 'false' }}">
                                                        <i class="ti ti-file-invoice"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted me-2"><i class="ti ti-file-invoice"></i></span>
                                                @endif
                                                <a href="#" class="me-2"><i class="ti ti-download"></i></a>
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">No subscriptions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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

@push('styles')
    <style>
        #view_invoice .invoice-modal {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 24px 60px rgba(23, 37, 84, 0.15);
            overflow: hidden;
        }

        #view_invoice .invoice-modal__body {
            position: relative;
            background: linear-gradient(160deg, #f7f5f0 0%, #ffffff 45%, #f4f7fb 100%);
        }

        #view_invoice .invoice-topbar {
            position: absolute;
            inset: 0 0 auto 0;
            height: 10px;
            background: linear-gradient(90deg, #ff6c37 0%, #f2b705 50%, #2b6cb0 100%);
        }

        #view_invoice .invoice-parties {
            padding: 16px 18px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(148, 163, 184, 0.2);
            backdrop-filter: blur(4px);
        }

        #view_invoice .invoice-items__table thead th {
            background: #f1f5f9;
            color: #0f172a;
            border: 0;
            font-weight: 600;
        }

        #view_invoice .invoice-items .table-responsive {
            overflow-x: visible;
        }

        #view_invoice .invoice-items__table {
            table-layout: fixed;
            width: 100%;
            font-size: 11px;
        }

        #view_invoice .invoice-items__table th,
        #view_invoice .invoice-items__table td {
            white-space: normal;
            word-break: break-word;
            font-size: 11px;
        }

        #view_invoice .invoice-items__table th:nth-child(1),
        #view_invoice .invoice-items__table td:nth-child(1) {
            width: 42%;
        }

        #view_invoice .invoice-items__table th:nth-child(2),
        #view_invoice .invoice-items__table td:nth-child(2) {
            width: 18%;
        }

        #view_invoice .invoice-items__table th:nth-child(3),
        #view_invoice .invoice-items__table td:nth-child(3) {
            width: 12%;
        }

        #view_invoice .invoice-items__table th:nth-child(4),
        #view_invoice .invoice-items__table td:nth-child(4) {
            width: 14%;
        }

        #view_invoice .invoice-items__table th:nth-child(5),
        #view_invoice .invoice-items__table td:nth-child(5) {
            width: 14%;
        }

        #view_invoice .invoice-items__table tbody tr {
            background: #ffffff;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        }

        #view_invoice .invoice-items__table tbody tr:nth-child(even) {
            background: #ffffff;
        }

        #view_invoice .invoice-items__table td {
            vertical-align: middle;
        }

        #view_invoice .invoice-summary {
            padding: 0;
            background: transparent;
            border: 0;
            box-shadow: none;
            font-size: 13px;
        }

        #view_invoice .invoice-summary__row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 13px;
        }

        #view_invoice .invoice-summary__divider {
            height: 1px;
            background: rgba(15, 23, 42, 0.08);
            margin: 6px 0;
        }

        #view_invoice .invoice-summary__row--total {
            border-top: 1px solid rgba(15, 23, 42, 0.08);
            margin-top: 4px;
            padding-top: 8px;
        }

        #view_invoice .invoice-summary__row--muted {
            border-top: 1px dashed rgba(148, 163, 184, 0.5);
            margin-top: 4px;
            padding-top: 8px;
            color: #64748b;
        }

        #view_invoice .invoice-summary__row--balance {
            border-top: 2px solid #0f172a;
            margin-top: 4px;
            padding-top: 8px;
            font-size: 13px;
        }

        #view_invoice .badge {
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        #view_invoice .modal.fade .modal-dialog {
            transform: translateY(12px);
        }

        #view_invoice .modal.show .modal-dialog {
            transform: translateY(0);
            transition: transform 280ms ease;
        }

        @media (max-width: 767px) {
            #view_invoice .invoice-modal__body {
                padding: 24px !important;
            }

            #view_invoice .invoice-summary {
                margin-top: 16px;
            }

            #view_invoice .invoice-parties {
                padding: 12px 14px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
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

        function setBreakdown(oneTimeTotal, recurringTotal, currency) {
            const oneTimeEl = document.getElementById('inv-one-time');
            const recurringEl = document.getElementById('inv-recurring');
            const oneTimeLabel = document.getElementById('inv-one-time-label');
            const recurringLabel = document.getElementById('inv-recurring-label');

            const showOneTime = Number(oneTimeTotal || 0) > 0;
            const showRecurring = Number(recurringTotal || 0) > 0;

            if (oneTimeEl) oneTimeEl.textContent = showOneTime ? fmtMoney(oneTimeTotal, currency) : '—';
            if (recurringEl) recurringEl.textContent = showRecurring ? fmtMoney(recurringTotal, currency) : '—';

            if (oneTimeLabel) oneTimeLabel.style.display = showOneTime || showRecurring ? '' : 'none';
            if (oneTimeEl) oneTimeEl.style.display = showOneTime || showRecurring ? '' : 'none';
            if (recurringLabel) recurringLabel.style.display = showOneTime || showRecurring ? '' : 'none';
            if (recurringEl) recurringEl.style.display = showOneTime || showRecurring ? '' : 'none';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const invoiceModal = document.getElementById('view_invoice');
            if (!invoiceModal) return;

            invoiceModal.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                if (!btn) return;

                const d = btn.dataset;

                const invNumber = document.getElementById('inv-number');
                if (invNumber) invNumber.textContent = d.invoiceNumber || '—';

                const invIssuedAt = document.getElementById('inv-issued-at');
                if (invIssuedAt) invIssuedAt.textContent = fmtDate(d.issuedAt);

                const invDueDate = document.getElementById('inv-due-date');
                if (invDueDate) invDueDate.textContent = fmtDate(d.dueDate);

                const billingCycleRow = document.getElementById('inv-billing-cycle-row');
                const billingCycleEl = document.getElementById('inv-billing-cycle');
                if (billingCycleRow && billingCycleEl && d.invoiceType === 'plan_upgrade' && d.billingCycle) {
                    billingCycleRow.style.display = 'block';
                    billingCycleEl.textContent = d.billingCycle.charAt(0).toUpperCase() + d.billingCycle.slice(1);
                    billingCycleEl.className = d.billingCycle === 'yearly' ? 'badge bg-success' : 'badge bg-info';
                } else if (billingCycleRow) {
                    billingCycleRow.style.display = 'none';
                }

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
                            typeBadge.textContent = invoiceType.charAt(0).toUpperCase() + invoiceType.slice(1);
                            typeBadge.className = 'badge bg-success ms-1';
                            break;
                    }
                }

                const nameEl = document.getElementById('inv-to-name');
                if (nameEl) nameEl.textContent = d.billToName || '—';

                const addrEl = document.getElementById('inv-to-address');
                if (addrEl) addrEl.textContent = d.billToAddress || '—';

                const emailEl = document.getElementById('inv-to-email');
                if (emailEl) emailEl.textContent = d.billToEmail || '—';

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

                    const hasOverage = (invoiceType === 'subscription' && licenseOverageCount > 0) ||
                        invoiceType === 'license_overage' ||
                        invoiceType === 'custom_order';
                    const showQtyRate = hasOverage || (invoiceType === 'subscription' && implementationFee > 0);

                    document.querySelectorAll('.qty-rate-column').forEach(col => {
                        col.style.display = showQtyRate ? '' : 'none';
                    });

                    if (invoiceType === 'custom_order' || d.hasWizardItems === 'true') {
                        fetch(`/billing/invoices/${d.invoiceId}/items`)
                            .then(response => response.json())
                            .then(data => {
                                tbody.innerHTML = '';
                                if (data.success && data.items && data.items.length > 0) {
                                    let oneTimeTotal = 0;
                                    let recurringTotal = 0;

                                    data.items.forEach(item => {
                                        const itemAmount = Number(item.amount || 0);
                                        const itemPeriod = String(item.period || '').toLowerCase();
                                        const itemType = String(item.type || '').toLowerCase();

                                        if (itemPeriod === 'one-time' || itemType === 'implementation_fee' || itemType === 'addon_onetime') {
                                            oneTimeTotal += itemAmount;
                                        } else {
                                            recurringTotal += itemAmount;
                                        }

                                        const tr = document.createElement('tr');

                                        let periodBadge = '';
                                        if (item.period === 'one-time') {
                                            periodBadge = '<span class="badge bg-warning bg-opacity-10 text-warning">One-time</span>';
                                        } else if (item.period) {
                                            periodBadge = `<span class="badge bg-info bg-opacity-10 text-info">${item.period.charAt(0).toUpperCase() + item.period.slice(1)}</span>`;
                                        } else {
                                            periodBadge = '<span class="text-muted">-</span>';
                                        }

                                        tr.innerHTML = `
                                            <td>
                                                <div class="fw-medium">${item.description}</div>
                                            </td>
                                            <td class="text-center">${periodBadge}</td>
                                            <td class="text-center">${item.quantity}</td>
                                            <td class="text-end">${item.formatted_rate}</td>
                                            <td class="text-end fw-medium">${item.formatted_amount}</td>
                                        `;
                                        tbody.appendChild(tr);
                                    });

                                    setBreakdown(oneTimeTotal, recurringTotal, d.currency);

                                    document.querySelectorAll('.qty-rate-column').forEach(col => {
                                        col.style.display = '';
                                    });
                                } else {
                                    const tr = document.createElement('tr');
                                    tr.innerHTML = `
                                        <td colspan="5" class="text-center">No detailed items available</td>
                                    `;
                                    tbody.appendChild(tr);
                                }

                                if (data.success && data.summary) {
                                    const subtotalEl = document.getElementById('inv-subtotal');
                                    if (subtotalEl) subtotalEl.textContent = data.summary.formatted_subtotal;

                                    const vatPercentageEl = document.getElementById('inv-vat-percentage');
                                    if (vatPercentageEl) vatPercentageEl.textContent = data.summary.vat_percentage;

                                    const vatAmountEl = document.getElementById('inv-vat-amount');
                                    if (vatAmountEl) vatAmountEl.textContent = data.summary.formatted_vat_amount;

                                    const totalAmountEl = document.getElementById('inv-total-amount');
                                    if (totalAmountEl) totalAmountEl.textContent = data.summary.formatted_total;

                                    const amountPaid = Number(d.amountPaid || 0);
                                    const amountPaidEl = document.getElementById('inv-amount-paid');
                                    if (amountPaidEl) amountPaidEl.textContent = fmtMoney(amountPaid, d.currency);

                                    const balanceEl = document.getElementById('inv-balance');
                                    if (balanceEl) balanceEl.textContent = fmtMoney(Math.max(data.summary.total - amountPaid, 0), d.currency);
                                } else {
                                    setBreakdown(0, 0, d.currency);
                                }
                            })
                            .catch(() => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td colspan="5" class="text-center text-danger">Error loading items</td>
                                `;
                                tbody.appendChild(tr);
                                setBreakdown(0, 0, d.currency);
                            });
                        return;
                    } else if (invoiceType === 'subscription') {
                        let oneTimeTotal = 0;
                        let recurringTotal = 0;

                        if (implementationFee > 0) {
                            oneTimeTotal += implementationFee;
                        }
                        recurringTotal += subscriptionAmount + licenseOverageAmount;

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

                        setBreakdown(oneTimeTotal, recurringTotal, d.currency);
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
                        setBreakdown(0, licenseOverageAmount || amountDue, d.currency);
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
                        setBreakdown(implementationFee, planUpgradeAmount, d.currency);
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
                        setBreakdown(amountDue, 0, d.currency);
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
                        setBreakdown(0, amountDue, d.currency);
                    }
                }

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
                if (balanceEl) balanceEl.textContent = fmtMoney(Math.max(amountDue - amountPaid, 0), d.currency);
            });
        });
    </script>
@endpush
