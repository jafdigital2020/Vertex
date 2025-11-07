<?php

$page = 'subscriptions'; ?>
@extends('layout.mainlayout')
@section('content')

<style>
.bg-purple {
    background-color: #6f42c1 !important;
}
</style>

    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Top breadcrumb -->
            <div class="row mb-3">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bg-white p-2 rounded">
                        <li class="breadcrumb-item"><a href="#">Manager</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Subscription & Billing</li>
                                                            </ol>
                                                        </nav>
                                                    </div>
                                                </div>

                                                <!-- Summary Cards -->
                                                <div class="row g-3">
                                                    <div class="col-6 col-md-3">
                                                        <div class="card shadow-sm h-100">
                                                            <div class="card-body d-flex gap-3 align-items-center">
                                                                <div class="bg-light rounded-circle p-3">
                                                                    <i class="ti ti-device-floppy text-primary" style="font-size:20px"></i>
                                                                </div>
                                                                <div>
                                                                    <small class="text-muted">Subscription</small>
                                                                    <div class="fw-bold">{{ $summaryData['subscription_name'] }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-6 col-md-3">
                                                        <div class="card shadow-sm h-100">
                                                            <div class="card-body d-flex gap-3 align-items-center">
                                                                <div class="bg-light rounded-circle p-3">
                                                                    <i class="ti ti-users text-success" style="font-size:20px"></i>
                                                                </div>
                                                                <div>
                                                                    <small class="text-muted">Users</small>
                                                                    <div class="fw-bold">{{ $summaryData['users_current'] }} / {{ $summaryData['users_limit'] }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-6 col-md-3">
                                                        <div class="card shadow-sm h-100">
                                                            <div class="card-body d-flex gap-3 align-items-center">
                                                                <div class="bg-light rounded-circle p-3">
                                                                    <i class="ti ti-currency-dollar text-warning" style="font-size:20px"></i>
                                                                </div>
                                                                <div>
                                                                    <small class="text-muted">Plan Cost</small>
                                                                    <div class="fw-bold">₱{{ number_format($summaryData['plan_cost'], 2) }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-6 col-md-3">
                                                        <div class="card shadow-sm h-100">
                                                            <div class="card-body d-flex gap-3 align-items-center">
                                                                <div class="bg-light rounded-circle p-3">
                                                                    <i class="ti ti-calendar-event text-info" style="font-size:20px"></i>
                                                                </div>
                                                                <div>
                                                                    <small class="text-muted">Renewal / Expiration</small>
                                                                    <div class="fw-bold">{{ $summaryData['renewal_date'] ? \Carbon\Carbon::parse($summaryData['renewal_date'])->format('F d, Y') : 'N/A' }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Title -->
                                                <div class="row mt-4">
                                                    <div class="col-12 text-center">
                                                        <h2 class="fw-bold">Unlock the power of Timora</h2>
                                                        <p class="text-muted">Upgrade anytime. Enjoy premium HR, payroll and operations features.</p>
                                                    </div>
                                                </div>

                                                <!-- Pricing Plans -->
                                                <div class="row g-4 mt-3">
                                                    @php
                                                        // Get current plan name and determine what plans to show
                                                        $currentPlan = $summaryData['subscription_name'] ?? 'Free Trial';
                                                        $currentPlanLower = strtolower($currentPlan);

                                                        // Determine which plans to show based on current subscription
                                                        $showCore = stripos($currentPlanLower, 'starter') !== false ||
                                                                   stripos($currentPlanLower, 'free') !== false ||
                                                                   stripos($currentPlanLower, 'trial') !== false;
                                                        $showPro = stripos($currentPlanLower, 'starter') !== false ||
                                                                  stripos($currentPlanLower, 'core') !== false ||
                                                                  stripos($currentPlanLower, 'free') !== false ||
                                                                  stripos($currentPlanLower, 'trial') !== false;
                                                        $showElite = stripos($currentPlanLower, 'starter') !== false ||
                                                                    stripos($currentPlanLower, 'core') !== false ||
                                                                    stripos($currentPlanLower, 'pro') !== false ||
                                                                    stripos($currentPlanLower, 'free') !== false ||
                                                                    stripos($currentPlanLower, 'trial') !== false;
                                                        $showCustom = true; // Always show custom plan as highest tier

                                                        // Calculate column class based on visible plans
                                                        $visiblePlans = 0;
                                                        if ($showCore) $visiblePlans++;
                                                        if ($showPro) $visiblePlans++;
                                                        if ($showElite) $visiblePlans++;
                                                        if ($showCustom) $visiblePlans++;

                                                        $colClass = $visiblePlans === 1 ? 'col-md-6 col-lg-4' :
                                                                   ($visiblePlans === 2 ? 'col-md-6' :
                                                                   ($visiblePlans === 3 ? 'col-md-4' : 'col-md-3'));
                                                    @endphp

                                                    @if($showCore)
                                                    <div class="{{ $colClass }}">
                                                        <div class="card h-100 border-light shadow-sm">
                                                            <div class="card-body d-flex flex-column">
                                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                                    <h5 class="mb-0">Core Plan</h5>
                                                                    <small class="text-muted">20-100 Employees</small>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <div class="text-center mb-2">
                                                                        <span class="badge bg-danger text-white">SAVE ₱5,000</span>
                                                                    </div>
                                                                    <div class="h2 fw-bold">₱20,499<span class="fs-6 fw-normal">/year</span></div>
                                                                    <div class="text-muted"><del>₱25,499</del></div>
                                                                    <small class="text-success fw-bold">Valid until December 2025</small>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <h6 class="text-primary">Good for Micro-Small Businesses</h6>
                                                                    <p class="small text-muted mb-2">Minimum 20 employees - ₱5,500 base price</p>
                                                                    <p class="small text-muted">₱49 per additional employee (Max 100)</p>
                                                                </div>

                                                                <ul class="list-unstyled flex-grow-1 small">
                                                                    <li class="mb-1">• Employee Management System</li>
                                                                    <li class="mb-1">• Secure storage & employee records</li>
                                                                    <li class="mb-1">• Customizable access controls</li>
                                                                    <li class="mb-1">• Biometric integration support</li>
                                                                    <li class="mb-1">• Employee directory</li>
                                                                    <li class="mb-1">• 7 Days Free Training</li>
                                                                    <li class="mb-1">• Knowledge Base Access</li>
                                                                    <li class="mb-1">• Email Support (Mon-Fri 9am-6pm)</li>
                                                                </ul>

                                                                <div class="mt-3">
                                                                    <button class="btn btn-outline-primary w-100 upgrade-plan-btn" data-plan="core">Upgrade to Core</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    @if($showPro)
                                                    <div class="{{ $colClass }}">
                                                        <div class="card h-100 shadow" style="background: linear-gradient(135deg,#1abc9c,#16a085); color: #fff;">
                                                            <div class="card-body d-flex flex-column">
                                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                                    <h5 class="mb-0">Pro Plan <span class="badge bg-light text-dark ms-2">Most Popular</span></h5>
                                                                    <small class="text-light">100-200 Employees</small>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <div class="text-center mb-2">
                                                                        <span class="badge bg-warning text-dark">SAVE ₱5,000</span>
                                                                    </div>
                                                                    <div class="h2 fw-bold">₱49,499<span class="fs-6 fw-normal">/year</span></div>
                                                                    <div class="text-light"><del>₱54,499</del></div>
                                                                    <small class="text-warning fw-bold">Valid until December 2025</small>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <h6 class="text-light">Good for Medium Businesses</h6>
                                                                    <p class="small text-light mb-2">₱9,500 base - Minimum 100 employees</p>
                                                                    <p class="small text-light">₱49 per additional employee (Max 200)</p>
                                                                </div>

                                                                <ul class="list-unstyled flex-grow-1 small">
                                                                    <li class="mb-1">• Advanced employee management</li>
                                                                    <li class="mb-1">• Enhanced customization & scaling</li>
                                                                    <li class="mb-1">• Role-based access control</li>
                                                                    <li class="mb-1">• Integration options</li>
                                                                    <li class="mb-1">• Reporting & analytics</li>
                                                                    <li class="mb-1">• FREE 1 Biometrics Device w/ Integration</li>
                                                                    <li class="mb-1">• Video Tutorial Access</li>
                                                                    <li class="mb-1">• Email & Call Support (Mon-Fri 9am-6pm)</li>
                                                                </ul>

                                                                <div class="mt-3">
                                                                    <button class="btn btn-light w-100 upgrade-plan-btn" data-plan="pro">Upgrade to Pro</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    @if($showElite)
                                                    <div class="{{ $colClass }}">
                                                        <div class="card h-100 border-0 shadow" style="background: linear-gradient(135deg,#083344,#5b2e8a); color: #fff;">
                                                            <div class="card-body d-flex flex-column text-white">
                                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                                    <h5 class="mb-0 text-white">Elite Plan <span class="badge bg-warning text-dark ms-2">Recommended</span></h5>
                                                                    <small class="text-white">200-500 Employees</small>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <div class="h2 fw-bold text-white">₱94,499<span class="fs-6 fw-normal text-white">/year</span></div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <h6 class="text-white">Good for Large Businesses</h6>
                                                                    <p class="small text-white mb-2">₱14,500 base - Minimum 200 employees</p>
                                                                    <p class="small text-white">₱49 per additional employee (Max 500)</p>
                                                                </div>

                                                                <ul class="list-unstyled flex-grow-1 small">
                                                                    <li class="mb-1">• Full HR & payroll management</li>
                                                                    <li class="mb-1">• Benefits & deductions management</li>
                                                                    <li class="mb-1">• Compliance & advanced analytics</li>
                                                                    <li class="mb-1">• Operations management tools</li>
                                                                    <li class="mb-1">• FREE 2 Biometrics Devices w/ Integration</li>
                                                                    <li class="mb-1">• FREE Custom Company Logo</li>
                                                                    <li class="mb-1">• 14 Days Training (All Roles)</li>
                                                                    <li class="mb-1">• Email, Chat & Call Support</li>
                                                                </ul>

                                                                <div class="mt-3">
                                                                    <button class="btn btn-warning w-100 upgrade-plan-btn" data-plan="elite">Upgrade to Elite</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    @if($showCustom)
                                                    <div class="{{ $colClass }}">
                                                        <div class="card h-100 border-warning shadow" style="background: linear-gradient(135deg,#f39c12,#e74c3c); color: #fff;">
                                                            <div class="card-body d-flex flex-column text-white">
                                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                                    <h5 class="mb-0 text-white">Custom Plan <span class="badge bg-light text-dark ms-2">Enterprise</span></h5>
                                                                    <small class="text-white">500+ Employees</small>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <div class="h2 fw-bold text-white">Contact Us<span class="fs-6 fw-normal text-white"></span></div>
                                                                    <small class="text-warning fw-bold">Custom Pricing Available</small>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <h6 class="text-white">Good for Enterprises</h6>
                                                                    <p class="small text-white mb-2">More than 500 employees</p>
                                                                    <p class="small text-white">Tailored solutions for your business</p>
                                                                </div>

                                                                <ul class="list-unstyled flex-grow-1 small">
                                                                    <li class="mb-1">• All Elite Plan features</li>
                                                                    <li class="mb-1">• Custom integrations</li>
                                                                    <li class="mb-1">• Dedicated account manager</li>
                                                                    <li class="mb-1">• 24/7 priority support</li>
                                                                    <li class="mb-1">• On-premise deployment options</li>
                                                                    <li class="mb-1">• Advanced security features</li>
                                                                    <li class="mb-1">• Custom training programs</li>
                                                                    <li class="mb-1">• SLA guarantees</li>
                                                                </ul>

                                                                <div class="mt-3">
                                                                    <a href="mailto:support@timora.ph" class="btn btn-light w-100">
                                                                        <i class="ti ti-mail me-1"></i>Contact Sales
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>

                                                <!-- Billing History Table -->
                                                <div class="card mt-5">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h5 class="mb-0">Billing History</h5>
                                                            <small class="text-muted">Current plan: <strong>{{ $subscription->plan->name ?? 'Free Trial' }}</strong> | All invoices and transactions</small>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <div class="input-group">
                                                                <input type="text" id="searchInput" class="form-control" placeholder="Search invoices..." aria-label="Search">
                                                                <button class="btn btn-outline-secondary" id="searchBtn">Search</button>
                                                            </div>
                                                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filter_modal">Filter</button>
                                                            <button class="btn btn-primary" id="exportBtn">Export</button>
                                                        </div>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover mb-0">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Invoice Type</th>
                                                                        <th>Plan/Description</th>
                                                                        <th>Amount</th>
                                                                        <th>Period</th>
                                                                        <th>Created Date</th>
                                                                        <th>Status</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="invoiceTableBody">
                                                                    @forelse($invoices as $invoice)
                                                                        <tr>
                                                                            <td>
                                                                                <div class="d-flex align-items-center">
                                                                                    <span class="me-2">
                                                                                        @php
                                                                                            $icon = match($invoice->invoice_type) {
                                                                                                'subscription' => '📄',
                                                                                                'license_overage' => '📊',
                                                                                                'combo' => '🔥',
                                                                                                'consolidated' => '🔗',
                                                                                                'plan_upgrade' => '🚀',
                                                                                                'implementation_fee' => '💼',
                                                                                                default => '📄'
                                                                                            };
                                                                                        @endphp
                                                                                        {{ $icon }}
                                                                                    </span>
                                                                                    @switch($invoice->invoice_type)
                                                                                        @case('subscription')
                                                                                            <span class="badge bg-primary">Subscription</span>
                                                                                            @break
                                                                                        @case('license_overage')
                                                                                            <span class="badge bg-info">License Overage</span>
                                                                                            @break
                                                                                        @case('combo')
                                                                                            <span class="badge bg-success">Combo</span>
                                                                                            @break
                                                                                        @case('consolidated')
                                                                                            <span class="badge bg-secondary">Consolidated</span>
                                                                                            @break
                                                                                        @case('plan_upgrade')
                                                                                            <span class="badge bg-warning">Upgrade</span>
                                                                                            @break
                                                                                        @case('implementation_fee')
                                                                                            <span class="badge bg-purple">Impl. Fee</span>
                                                                                            @break
                                                                                        @default
                                                                                            <span class="badge bg-light text-dark">{{ ucfirst($invoice->invoice_type) }}</span>
                                                                                    @endswitch
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                @if($invoice->invoice_type == 'subscription' || $invoice->invoice_type == 'combo')
                                                                                    <strong>{{ $invoice->subscription->plan->name ?? 'N/A' }}</strong>
                                                                                    <br><small class="text-muted">{{ ucfirst($invoice->subscription->billing_cycle ?? 'N/A') }} billing</small>
                                                                                @elseif($invoice->invoice_type == 'license_overage')
                                                                                    <strong>License Overage</strong>
                                                                                    <br><small class="text-muted">{{ $invoice->license_overage_count ?? 0 }} extra users</small>
                                                                                @elseif($invoice->invoice_type == 'plan_upgrade')
                                                                                    <strong>Plan Upgrade</strong>
                                                                                    <br><small class="text-muted">Implementation fee</small>
                                                                                @elseif($invoice->invoice_type == 'implementation_fee')
                                                                                    <strong>Implementation Fee</strong>
                                                                                    <br><small class="text-muted">Setup & configuration</small>
                                                                                @else
                                                                                    <strong>{{ $invoice->subscription->plan->name ?? 'N/A' }}</strong>
                                                                                    <br><small class="text-muted">{{ ucfirst($invoice->invoice_type) }}</small>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                <strong>₱{{ number_format($invoice->amount_due, 2) }}</strong>
                                                                                @if($invoice->amount_paid > 0 && $invoice->amount_paid != $invoice->amount_due)
                                                                                    <br><small class="text-success">Paid: ₱{{ number_format($invoice->amount_paid, 2) }}</small>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                @if($invoice->subscription_period_start && $invoice->subscription_period_end)
                                                                                    <small>
                                                                                        {{ \Carbon\Carbon::parse($invoice->subscription_period_start)->format('M d, Y') }}<br>
                                                                                        to {{ \Carbon\Carbon::parse($invoice->subscription_period_end)->format('M d, Y') }}
                                                                                    </small>
                                                                                @else
                                                                                    <small class="text-muted">One-time</small>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                <small>{{ \Carbon\Carbon::parse($invoice->created_at)->format('M d, Y') }}</small>
                                                                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($invoice->created_at)->format('h:i A') }}</small>
                                                                            </td>
                                                                            <td>
                                                                                @if($invoice->status == 'paid')
                                                                                    <span class="badge bg-success">Paid</span>
                                                                                @elseif($invoice->status == 'pending')
                                                                                    <span class="badge bg-warning">Pending</span>
                                                                                @elseif($invoice->status == 'trial')
                                                                                    <span class="badge bg-info">Trial</span>
                                                                                @elseif($invoice->status == 'failed')
                                                                                    <span class="badge bg-danger">Failed</span>
                                                                                @elseif($invoice->status == 'consolidated')
                                                                                    <span class="badge bg-secondary">Consolidated</span>
                                                                                @elseif($invoice->status == 'consolidated_pending')
                                                                                    <span class="badge bg-warning">Consolidating</span>
                                                                                @else
                                                                                    <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                <button class="btn btn-sm btn-outline-primary"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#view_invoice"
                                                                                    data-invoice-id="{{ $invoice->id }}"
                                                                                    data-invoice-number="{{ $invoice->invoice_number }}"
                                                                                    data-invoice-type="{{ $invoice->invoice_type }}"
                                                                                    data-amount-due="{{ $invoice->amount_due }}"
                                                                                    data-amount-paid="{{ $invoice->amount_paid }}"
                                                                                    data-subscription-amount="{{ $invoice->subscription_amount }}"
                                                                                    data-license-overage-count="{{ $invoice->license_overage_count }}"
                                                                                    data-license-overage-amount="{{ $invoice->license_overage_amount }}"
                                                                                    data-license-overage-rate="{{ $invoice->license_overage_rate }}"
                                                                                    data-currency="{{ $invoice->currency }}"
                                                                                    data-due-date="{{ $invoice->due_date }}"
                                                                                    data-status="{{ $invoice->status }}"
                                                                                    data-period-start="{{ $invoice->subscription_period_start }}"
                                                                                    data-period-end="{{ $invoice->subscription_period_end }}"
                                                                                    data-issued-at="{{ $invoice->created_at }}"
                                                                                    data-bill-to-name="{{ $invoice->tenant->tenant_name ?? 'N/A' }}"
                                                                                    data-bill-to-address="{{ $invoice->tenant->address ?? 'N/A' }}"
                                                                                    data-bill-to-email="{{ $invoice->tenant->email ?? 'N/A' }}"
                                                                                    data-plan="{{ $invoice->subscription->plan->name ?? 'N/A' }}"
                                                                                    data-billing-cycle="{{ $invoice->subscription->billing_cycle ?? 'N/A' }}">
                                                                                    <i class="fas fa-eye"></i> View
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr>
                                                                            <td colspan="7" class="text-center py-4">
                                                                                <div class="text-muted">
                                                                                    <i class="fas fa-receipt fa-3x mb-3"></i>
                                                                                    <h6>No invoices found</h6>
                                                                                    <p>Your billing history will appear here once you make a purchase.</p>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                    <div class="card-footer">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-muted">
                                                Showing {{ $invoices->firstItem() ?? 0 }} to {{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }} entries
                                            </div>
                                            <div>
                                                {{ $invoices->links() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

        <!-- ✅ CLEAN: View Invoice Modal -->
        <div class="modal fade" id="view_invoice">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-body p-5">

                        <div class="row justify-content-between align-items-center mb-3">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <img src="{{ URL::asset('build/img/Timora-logo.png') }}" class="img-fluid" alt="logo"
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
                                    <p class="mb-1">JAF Digital Group Inc.</p>
                                    <p class="mb-1">Unit D 49th Floor PBCom Tower, 6795 Ayala Avenue, corner V.A. Rufino St, Makati City, Metro Manila, Philippines</p>
                                    <p class="mb-1">support@timora.ph</p>
                                    <p class="mb-1">TIN: 010868588000</p>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <p class="text-dark mb-2 fw-medium fs-16">Invoice To :</p>
                                <div id="inv-to">
                                    <p class="mb-1" id="inv-to-name">—</p>
                                    <p class="mb-1" id="inv-to-email">—</p>
                                    <p class="mb-1" id="inv-to-address">—</p>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Items Table -->
                        <div class="mb-4">
                            <div class="table-responsive mb-3">
                                <table class="table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Description</th>
                                            <th>Period</th>
                                            <th>Quantity</th>
                                            <th>Rate</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="inv-items"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Totals -->
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

                        <!-- Terms -->
                        <div class="card border mb-0">
                            <div class="card-body">
                                <p class="text-dark fw-medium mb-2">Terms & Conditions:</p>
                                <p class="fs-12 fw-normal d-flex align-items-baseline mb-2">
                                    <i class="ti ti-point-filled text-primary me-1"></i>
                                    All payments must be made according to the agreed schedule.
                                </p>
                                <p class="fs-12 fw-normal d-flex align-items-baseline mb-2">
                                    <i class="ti ti-point-filled text-primary me-1"></i>
                                    License overage charges apply when employee count exceeds subscription limit during billing period.
                                </p>
                                <p class="fs-12 fw-normal d-flex align-items-baseline">
                                    <i class="ti ti-point-filled text-primary me-1"></i>
                                    We are not liable for any indirect, incidental, or consequential damages, including loss of profits, revenue, or data.
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                            <a href="#" id="download-invoice-btn" class="btn btn-success">
                                <i class="ti ti-download me-1"></i>Download PDF
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- /View Invoice -->

        {{-- Plan Upgrade Modal --}}
        <div class="modal fade" id="plan_upgrade_modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white"></h5>
                            <i class="ti ti-rocket me-2"></i>Upgrade Your Plan
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Unlock more features and increase your employee limit.</strong>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <small class="text-muted">Current Plan</small>
                                    <h5 class="mb-0 mt-1" id="upgrade_current_plan_name">{{ $summaryData['subscription_name'] }}</h5>
                                    <small id="upgrade_current_plan_limit">{{ $summaryData['users_limit'] }} employees</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <small class="text-muted">Current Active Users</small>
                                    <h5 class="mb-0 mt-1" id="upgrade_current_users">{{ $summaryData['users_current'] }}</h5>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <small class="text-muted">Current Impl. Fee Paid</small>
                                    <h5 class="mb-0 mt-1" id="upgrade_current_impl_fee">₱0.00</h5>
                                </div>
                            </div>
                        </div>

                        <h6 class="mb-3">
                            <i class="ti ti-package me-2"></i>Select Your Upgrade Plan
                        </h6>

                        <div id="available_plans_container" class="row">
                            <div class="col-12 text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading available plans...</p>
                            </div>
                        </div>

                        <div class="card bg-light mt-4" id="selected_plan_summary" style="display: none;">
                            <div class="card-body">
                                <h6 class="mb-3">
                                    <i class="ti ti-receipt me-2"></i>Upgrade Summary
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <small class="text-muted">Selected Plan:</small>
                                            <h6 id="summary_plan_name" class="mb-0">-</h6>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">Employee Limit:</small>
                                            <strong id="summary_plan_limit">-</strong>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">Monthly Price:</small>
                                            <strong id="summary_plan_price">-</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <small class="text-muted">Current Impl. Fee Paid:</small>
                                            <strong id="summary_current_impl_fee">-</strong>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">New Plan Impl. Fee:</small>
                                            <strong id="summary_new_impl_fee">-</strong>
                                        </div>
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-medium">Amount Due Now:</span>
                                            <h4 class="text-primary mb-0" id="summary_amount_due">-</h4>
                                        </div>
                                        <small class="text-muted">Implementation fee difference only</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmPlanUpgradeBtn" disabled>
                            <i class="ti ti-arrow-up-circle me-2"></i>Proceed with Upgrade
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- CSS for ribbon and plan cards --}}
        <style>
            .ribbon {
                position: absolute;
                overflow: hidden;
                width: 75px;
                height: 75px;
                z-index: 1;
            }
            .ribbon-top-right {
                top: -10px;
                right: -10px;
            }
            .ribbon span {
                position: absolute;
                display: block;
                width: 145px;
                padding: 5px 0;
                box-shadow: 0 5px 10px rgba(0,0,0,.1);
                color: #fff;
                font: 700 12px/1 'Lato', sans-serif;
                text-shadow: 0 1px 1px rgba(0,0,0,.2);
                text-transform: uppercase;
                text-align: center;
            }
            .ribbon-top-right span {
                right: -21px;
                top: 15px;
                transform: rotate(45deg);
            }
            .plan-option {
                cursor: pointer;
                transition: all 0.3s ease;
            }
            .plan-option:hover {
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
                transform: translateY(-2px);
            }
            .plan-option.selected {
                border: 3px solid #0d6efd !important;
                box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
            }
        </style>

        @include('layout.partials.footer-company')
    </div>

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle pay button clicks with post-payment notification
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

        // ✅ ENHANCED: Generate Invoice PDF with License Overage Support
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
            const subscriptionAmount = Number(data.subscriptionAmount ?? 0);
            const licenseOverageAmount = Number(data.licenseOverageAmount ?? 0);
            const licenseOverageCount = Number(data.licenseOverageCount ?? 0);
            const licenseOverageRate = Number(data.licenseOverageRate ?? 49);
            const tax = 0; // Assuming no tax for now
            const subtotal = amountDue - tax;
            const balance = Math.max(amountDue - amountPaid, 0);

            // Generate invoice items based on type
            let invoiceItemsHTML = '';

            if (data.invoiceType === 'combo') {
                // Subscription + License Overage
                if (subscriptionAmount > 0) {
                    invoiceItemsHTML += `
                        <tr>
                            <td>${data.plan || '—'} Subscription</td>
                            <td>${fmtDate(data.periodStart)} - ${fmtDate(data.periodEnd)}</td>
                            <td>1</td>
                            <td>${fmtMoney(subscriptionAmount, data.currency)}</td>
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
            } else {
                // Subscription Only
                invoiceItemsHTML = `
                    <tr>
                        <td>${data.plan || '—'} Subscription</td>
                        <td>${fmtDate(data.periodStart)} - ${fmtDate(data.periodEnd)}</td>
                        <td>1</td>
                        <td>${fmtMoney(amountDue, data.currency)}</td>
                        <td class="text-end">${fmtMoney(amountDue, data.currency)}</td>
                    </tr>
                `;
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
                                <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Quantity</th>
                                <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Rate</th>
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
                            <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; color: #666;">
                                <span style="font-style: italic;">12% VAT Inclusive</span>
                                <span></span>
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
                case 'plan_upgrade':
                    return '🚀';
                case 'implementation_fee':
                    return '💼';
                default:
                    return '📄';
            }
        }
    </script>

    {{-- ✅ ENHANCED: Populate Invoice Modal with License Overage Support --}}
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
        document.addEventListener('DOMContentLoaded', function() {
            // Invoice modal event listener
            const invoiceModal = document.getElementById('view_invoice');
            if (invoiceModal) {
                invoiceModal.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget; // <a ...> that opened the modal
                    if (!btn) {
                        console.log('No button found');
                        return;
                    }

                    console.log('Button clicked, processing data...'); // Debug log

                    // Read all data-* attributes
                    const d = btn.dataset;
                    console.log('Dataset:', d); // Debug log

                    // ✅ SIMPLIFIED: Header elements with better error handling
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
                        invDueDate.textContent = fmtDate(d.dueDate) || 'No due date';
                    }

                    // ✅ SIMPLIFIED: Invoice type badge with null checks
                    const typeBadge = document.getElementById('inv-type-badge');
                    if (typeBadge) {
                        const invoiceType = d.invoiceType || 'subscription';
                        const licenseOverageCount = Number(d.licenseOverageCount || 0);

                        console.log('Invoice type:', invoiceType, 'Overage count:', licenseOverageCount);

                        switch (invoiceType) {
                            case 'license_overage':
                                typeBadge.textContent = 'License Overage';
                                typeBadge.className = 'badge bg-info ms-1';
                                break;
                            case 'plan_upgrade':
                                typeBadge.textContent = 'Plan Upgrade';
                                typeBadge.className = 'badge bg-warning ms-1';
                                break;
                            case 'implementation_fee':
                                typeBadge.textContent = 'Implementation Fee';
                                typeBadge.className = 'badge bg-purple ms-1';
                                break;
                            case 'combo':
                                typeBadge.textContent = 'Combo Plan';
                                typeBadge.className = 'badge bg-success ms-1';
                                break;
                            case 'consolidated':
                                typeBadge.textContent = 'Consolidated';
                                typeBadge.className = 'badge bg-secondary ms-1';
                                break;
                            case 'subscription':
                                if (licenseOverageCount > 0) {
                                    typeBadge.textContent = 'Subscription + Overage';
                                    typeBadge.className = 'badge bg-primary ms-1';
                                } else {
                                    typeBadge.textContent = 'Subscription';
                                    typeBadge.className = 'badge bg-primary ms-1';
                                }
                                break;
                            default:
                                typeBadge.textContent = 'Subscription';
                                typeBadge.className = 'badge bg-primary ms-1';
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
                        const amountDue = Number(d.amountDue || 0);
                        const invoiceType = d.invoiceType || 'subscription';

                        console.log('Processing invoice items:', {
                            invoiceType,
                            subscriptionAmount,
                            licenseOverageAmount,
                            licenseOverageCount,
                            amountDue
                        });

                        // ✅ UPDATED: Table rows logic for subscription invoices with overage
                        if (invoiceType === 'subscription') {
                            // Add subscription row if amount > 0
                            if (subscriptionAmount > 0) {
                                const tr1 = document.createElement('tr');
                                tr1.innerHTML = `
                                <td>${d.plan || '—'} Subscription</td>
                                <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                <td>1</td>
                                <td>${fmtMoney(subscriptionAmount, d.currency)}</td>
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
                                tr.innerHTML = `
                                <td>${d.plan || '—'} Subscription</td>
                                <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                                <td>1</td>
                                <td>${fmtMoney(amountDue, d.currency)}</td>
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
                            // Plan Upgrade Implementation Fee
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                            <td>Plan Upgrade - Implementation Fee</td>
                            <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                            <td>1</td>
                            <td>${fmtMoney(amountDue, d.currency)}</td>
                            <td class="text-end">${fmtMoney(amountDue, d.currency)}</td>
                        `;
                            tbody.appendChild(tr);
                        } else if (invoiceType === 'implementation_fee') {
                            // Implementation Fee Only
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                            <td>Implementation Fee</td>
                            <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                            <td>1</td>
                            <td>${fmtMoney(amountDue, d.currency)}</td>
                            <td class="text-end">${fmtMoney(amountDue, d.currency)}</td>
                        `;
                            tbody.appendChild(tr);
                        } else {
                            // Default fallback
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                            <td>${d.plan || 'Subscription'}</td>
                            <td>${fmtDate(d.periodStart)} - ${fmtDate(d.periodEnd)}</td>
                            <td>1</td>
                            <td>${fmtMoney(amountDue, d.currency)}</td>
                            <td class="text-end">${fmtMoney(amountDue, d.currency)}</td>
                        `;
                            tbody.appendChild(tr);
                        }

                        console.log('Table rows added, tbody content:', tbody.innerHTML);
                    }

                    // ✅ SIMPLIFIED: Totals with null checks
                    const amountPaid = Number(d.amountPaid || 0);
                    const amountDue = Number(d.amountDue || 0);
                    const balance = amountDue - amountPaid;

                    const subtotalEl = document.getElementById('inv-subtotal');
                    if (subtotalEl) {
                        subtotalEl.textContent = fmtMoney(amountDue, d.currency);
                    }

                    const taxEl = document.getElementById('inv-tax');
                    if (taxEl) {
                        // Calculate 12% VAT that's included in the amount
                        const vatAmount = amountDue * 0.12;
                        taxEl.textContent = fmtMoney(vatAmount, d.currency);
                    }

                    const amountPaidEl = document.getElementById('inv-amount-paid');
                    if (amountPaidEl) {
                        amountPaidEl.textContent = fmtMoney(amountPaid, d.currency);
                    }

                    const balanceEl = document.getElementById('inv-balance');
                    if (balanceEl) {
                        balanceEl.textContent = fmtMoney(balance, d.currency);
                    }
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
    </script>

    {{-- Search and Filter Functionality --}}
    <script>
        $(document).ready(function() {
            // Search functionality
            $('#searchBtn').on('click', function() {
                performSearch();
            });

            $('#searchInput').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    performSearch();
                }
            });

            function performSearch() {
                const searchTerm = $('#searchInput').val();

                $.ajax({
                    url: '{{ route("subscriptions-filter") }}',
                    type: 'GET',
                    data: {
                        search: searchTerm
                    },
                    success: function(response) {
                        $('#invoiceTableBody').html(response);
                    },
                    error: function(xhr) {
                        console.error('Search failed:', xhr);
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Failed to search invoices');
                        }
                    }
                });
            }

            // Export functionality
            $('#exportBtn').on('click', function() {
                if (typeof toastr !== 'undefined') {
                    toastr.info('Export functionality coming soon');
                } else {
                    alert('Export functionality coming soon');
                }
            });
        });
    </script>

    {{-- Plan Upgrade Functionality --}}
    <script>
        $(document).ready(function() {
            let selectedPlanId = null;
            let availablePlansData = null;

            // Open upgrade modal
            $('.upgrade-plan-btn').on('click', function() {
                selectedPlanId = null;
                $('#confirmPlanUpgradeBtn').prop('disabled', true);
                $('#selected_plan_summary').hide();
                $('#plan_upgrade_modal').modal('show');
                loadAvailablePlans();
            });

            // Load available plans
            function loadAvailablePlans() {
                $('#available_plans_container').html(`
                    <div class="col-12 text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading available plans...</p>
                    </div>
                `);

                $.ajax({
                    url: '{{ route("subscriptions.available-plans") }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            availablePlansData = response;
                            renderPlans(response);

                            // Update current plan info
                            $('#upgrade_current_impl_fee').text('₱' + Number(response.current_plan.implementation_fee_paid || 0).toFixed(2));
                        } else {
                            showError(response.message || 'Failed to load plans');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading plans:', xhr);
                        showError('Failed to load available plans');
                    }
                });
            }

            // Render plans
            function renderPlans(data) {
                if (!data.available_plans || data.available_plans.length === 0) {
                    $('#available_plans_container').html(`
                        <div class="col-12 text-center py-4">
                            <i class="ti ti-info-circle fs-1 text-muted"></i>
                            <p class="mt-2 text-muted">You are already on the highest tier plan!</p>
                        </div>
                    `);
                    return;
                }

                let plansHtml = '';
                data.available_plans.forEach(plan => {
                    const isRecommended = plan.is_recommended;
                    const ribbonHtml = isRecommended ? `
                        <div class="ribbon ribbon-top-right">
                            <span style="background: linear-gradient(45deg, #f39c12, #e74c3c);">Recommended</span>
                        </div>
                    ` : '';

                    plansHtml += `
                        <div class="col-md-4 mb-3">
                            <div class="card plan-option h-100 position-relative" data-plan-id="${plan.id}" style="border: 2px solid #dee2e6;">
                                ${ribbonHtml}
                                <div class="card-body">
                                    <h5 class="card-title">${plan.name}</h5>
                                    <p class="text-muted small">${plan.description || ''}</p>
                                    <div class="my-3">
                                        <h3 class="mb-0">₱${Number(plan.price).toLocaleString()}</h3>
                                        <small class="text-muted">/${plan.billing_cycle}</small>
                                    </div>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="ti ti-check text-success me-2"></i>
                                            Up to ${plan.employee_limit} employees
                                        </li>
                                        <li class="mb-2">
                                            <i class="ti ti-check text-success me-2"></i>
                                            Implementation Fee: ₱${Number(plan.implementation_fee).toLocaleString()}
                                        </li>
                                        ${plan.impl_fee_difference > 0 ? `
                                        <li class="mb-2">
                                            <i class="ti ti-info-circle text-info me-2"></i>
                                            <strong>Pay Now: ₱${Number(plan.impl_fee_difference).toLocaleString()}</strong>
                                        </li>
                                        ` : `
                                        <li class="mb-2">
                                            <i class="ti ti-check text-success me-2"></i>
                                            <strong class="text-success">No additional fee!</strong>
                                        </li>
                                        `}
                                    </ul>
                                    <button class="btn btn-outline-primary w-100 select-plan-btn">
                                        Select Plan
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });

                $('#available_plans_container').html(plansHtml);

                // Attach click handlers
                $('.plan-option').on('click', function() {
                    const planId = $(this).data('plan-id');
                    selectPlan(planId);
                });
            }

            // Select a plan
            function selectPlan(planId) {
                selectedPlanId = planId;

                // Update UI
                $('.plan-option').removeClass('selected');
                $(`.plan-option[data-plan-id="${planId}"]`).addClass('selected');

                // Find plan data
                const plan = availablePlansData.available_plans.find(p => p.id == planId);
                if (!plan) return;

                // Update summary
                $('#summary_plan_name').text(plan.name);
                $('#summary_plan_limit').text(plan.employee_limit + ' employees');
                $('#summary_plan_price').text('₱' + Number(plan.price).toLocaleString() + '/' + plan.billing_cycle);
                $('#summary_current_impl_fee').text('₱' + Number(availablePlansData.current_plan.implementation_fee_paid || 0).toLocaleString());
                $('#summary_new_impl_fee').text('₱' + Number(plan.implementation_fee).toLocaleString());
                $('#summary_amount_due').text('₱' + Number(plan.impl_fee_difference).toLocaleString());

                // Show summary and enable button
                $('#selected_plan_summary').slideDown();
                $('#confirmPlanUpgradeBtn').prop('disabled', false);
            }

            // Confirm upgrade
            $('#confirmPlanUpgradeBtn').on('click', function() {
                if (!selectedPlanId) return;

                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

                $.ajax({
                    url: '{{ route("subscriptions.upgrade") }}',
                    type: 'POST',
                    data: {
                        plan_id: selectedPlanId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#plan_upgrade_modal').modal('hide');

                            if (response.requires_payment && response.invoice) {
                                // Show payment required message
                                if (typeof toastr !== 'undefined') {
                                    toastr.success('Upgrade initiated! Please complete payment.');
                                }

                                // Optionally redirect to payment or reload
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                // Free upgrade
                                if (typeof toastr !== 'undefined') {
                                    toastr.success(response.message);
                                }
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            }
                        } else {
                            showError(response.message || 'Upgrade failed');
                            btn.prop('disabled', false).html('<i class="ti ti-arrow-up-circle me-2"></i>Proceed with Upgrade');
                        }
                    },
                    error: function(xhr) {
                        console.error('Upgrade error:', xhr);
                        showError(xhr.responseJSON?.message || 'Failed to upgrade plan');
                        btn.prop('disabled', false).html('<i class="ti ti-arrow-up-circle me-2"></i>Proceed with Upgrade');
                    }
                });
            });

            // Helper function to show errors
            function showError(message) {
                $('#available_plans_container').html(`
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="ti ti-alert-circle me-2"></i>${message}
                        </div>
                    </div>
                `);
            }
        });

        // ✅ SIMPLIFIED: Download Invoice functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Download PDF button
            const downloadButton = document.getElementById('download-invoice-btn');
            if (downloadButton) {
                downloadButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    const invoiceNumber = document.getElementById('inv-number').textContent;
                    const invoiceId = document.querySelector('[data-bs-target="#view_invoice"]:last-child')?.dataset?.invoiceId;

                    if (invoiceId) {
                        // This would typically be a route to generate PDF server-side
                        const downloadUrl = `/invoices/${invoiceId}/download`;

                        // For now, show a message that this feature is coming soon
                        if (typeof toastr !== 'undefined') {
                            toastr.info('PDF download feature coming soon!');
                        } else {
                            alert('PDF download feature coming soon!');
                        }

                        // Uncomment when PDF generation is implemented:
                        // window.open(downloadUrl, '_blank');
                    }
                });
            }
        });
    </script>
@endpush
