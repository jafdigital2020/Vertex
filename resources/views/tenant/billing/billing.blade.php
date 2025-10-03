<?php $page = 'bills-payment'; ?>
@extends('layout.mainlayout')
@section('content')
            <!-- Page Wrapper -->
            <div class="page-wrapper">
                <div class="content">

                    @php
    // Add this $api variable to fix the undefined variable error
    $api = [
        'bill_to_full_name' => 'N/A',
        'bill_to_email' => 'N/A',
        'bill_to_address' => 'N/A'
    ];
                    @endphp

                    <!-- Breadcrumb -->
                    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                        <div class="my-auto mb-2">
                            <h2 class="mb-1">Subscriptions</h2>
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="#"><i class="ti ti-smart-home"></i></a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">Subscriptions</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- /Breadcrumb -->

                    {{-- Subscriptions Summary --}}
                    <div class="card mt-2">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">Subscriptions</h5>
                            <div class="d-flex gap-2">
                                {{-- <a href="{{ url('billing/manage') }}" class="btn btn-outline-secondary btn-sm">Manage Plan</a>
                                --}}
                                {{-- <a href="{{ url('billing/top-up') }}" class="btn btn-primary btn-sm">Top Up Credits</a> --}}
                            </div>
                        </div>

                        <div class="card-body">
                            <div id="subs-alert" class="alert alert-info d-none mb-3">
                                <strong>Heads up:</strong> Your subscription details are loading…
                            </div>

                            <div id="subs-empty" class="text-center text-muted d-none">
                                <i class="ti ti-package mb-2" style="font-size:28px;"></i>
                                <div>No active subscriptions found.</div>
                            </div>

                            <div id="subs-grid" class="row g-3">
                                <!-- Skeletons (shown while loading) -->
                                @for ($i = 0; $i < 2; $i++)
                                    <div class="col-xl-6 col-lg-6">
                                        <div class="border rounded-3 p-3">
                                            <div class="placeholder-glow">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="placeholder col-5"></span>
                                                    <span class="badge bg-secondary placeholder col-2">&nbsp;</span>
                                                </div>
                                                <div class="placeholder col-8 mb-2"></div>
                                                <div class="placeholder col-6 mb-3"></div>
                                                <div class="progress mb-2" style="height: 8px;">
                                                    <div class="progress-bar placeholder col-12" role="progressbar" style="width: 100%">
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="placeholder col-3"></span>
                                                    <span class="placeholder col-2"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>


                    <div class="card mt-2">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">Payments</h5>
                            <button class="btn btn-outline-primary btn-sm" id="downloadAllBtn">Download All</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0" id="payment-history-table">
                                    <thead>
                                        <tr>
                                            <th>Payment #</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                            <th>Pay</th>
                                            <th>Download</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer bg-light border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted">
                                        <small id="table-info"></small>
                                    </div>
                                    <nav aria-label="Invoice pagination">
                                        <ul class="pagination pagination-sm mb-0" id="pagination">
                                            <!-- Pagination will be rendered here -->
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- ✅ ENHANCED: View Invoice Modal -->
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
                                            <p class="mb-1">Timora</p>
                                            <p class="mb-1">Unit D 49th Floor PBCom Tower, 6795 Ayala Avenue, corner V.A.
                                                Rufino St, Makati City, Metro Manila, Philippines</p>
                                            <p class="mb-1">support@timora.ph</p>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <p class="text-dark mb-2 fw-medium fs-16">Invoice To :</p>
                                        <div id="inv-to">
                                            <p class="mb-1" id="inv-to-name">{{ $api['bill_to_full_name'] ?? '—' }}</p>

                                            <p class="mb-1" id="inv-to-email">{{ $api['bill_to_email'] ?? '—' }}</p>
                                            <p class="mb-1" id="inv-to-address">{{ $api['bill_to_address'] ?? '—' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- ✅ CHECK: Invoice Items Table -->
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
                                            Add-on charges apply for additional features or services purchased during the billing
                                            period.
                                        </p>
                                        <p class="fs-12 fw-normal d-flex align-items-baseline">
                                            <i class="ti ti-point-filled text-primary me-1"></i>
                                            We are not liable for any indirect, incidental, or consequential damages, including loss
                                            of profits, revenue, or data.
                                        </p>
                                    </div>
                                </div>

                                <!-- Download Actions -->
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

                @include('layout.partials.footer-company')
            </div>
            <!-- /Page Wrapper -->

            @component('components.modal-popup')
            @endcomponent

            @push('scripts')
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function () {
                                            const tableBody = document.querySelector('#payment-history-table tbody');
                                            const tableInfo = document.getElementById('table-info');
                                            const pagination = document.getElementById('pagination');
                                            const downloadAllBtn = document.getElementById('downloadAllBtn');
                                            let payments = [];
                                            let currentPage = 1;
                                            const perPage = 10;

                                            function fetchPayments() {
                                                fetch('{{ route('api.payment-history') }}')
                                                    .then(res => res.json())
                                                    .then(json => {
                                                        const subs = json.subscriptions || [];

                                                        payments = [];
                                                        subs.forEach(sub => {
                                                            if (Array.isArray(sub.payments) && sub.payments.length) {
                                                                sub.payments.forEach(p => {
                                                                    payments.push({
                                                                        ...p,
                                                                        subscription: sub,
                                                                        invoice: p.invoice ?? null,
                                                                        bill_to_full_name: json.bill_to_full_name,
                                                                        bill_to_address: json.bill_to_address,
                                                                        bill_to_email: json.bill_to_email,
                                                                    });
                                                                });
                                                            }
                                                        });

                                                        renderTable();
                                                        renderPagination();
                                                    })
                                                    .catch(() => {
                                                        tableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Failed to load payments.</td></tr>`;
                                                    });
                                            }


                                            function renderTable() {
                                                if (!payments.length) {
                                                    tableBody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">No payment history found.</td></tr>`;
                                                    tableInfo.textContent = '';
                                                    return;
                                                }
                                                const start = (currentPage - 1) * perPage;
                                                const end = start + perPage;
                                                const pagePayments = payments.slice(start, end);

                                                tableBody.innerHTML = pagePayments.map(payment => {
                                                    // Type: Starter or Employee Credits
                                                    let type = 'Starter';
                                                    if (payment.meta && payment.meta.type === 'employee_credits') {
                                                        type = `Employee Credits (+${payment.meta.additional_credits ?? ''})`;
                                                    } else if (payment.plan) {
                                                        type = payment.plan.charAt(0).toUpperCase() + payment.plan.slice(1);
                                                    }

                                                    // Date: paid_at or '-'
                                                    let date = payment.paid_at ? new Date(payment.paid_at).toLocaleDateString() : '-';

                                                    // Status badge
                                                    let statusClass = 'secondary';
                                                    if (payment.status === 'paid') statusClass = 'success';
                                                    else if (payment.status === 'pending') statusClass = 'warning';
                                                    else if (payment.status === 'failed' || payment.status === 'overdue') statusClass = 'danger';

                                                    // Pay button
                                                    let payBtn = '';
                                                    if (payment.status === 'pending' && payment.checkout_url) {
                                                        payBtn = `<a href="${payment.checkout_url}" target="_blank" class="btn btn-outline-primary btn-sm">Pay</a>`;
                                                    } else {
                                                        payBtn = `<button class="btn btn-outline-primary btn-sm" disabled>Pay</button>`;
                                                    }

                                                    // Download link with both View and PDF buttons
                                                    let downloadLink = '';
                                                    if (payment.invoice) {
                                                        downloadLink = `
                                                                            <div class="btn-group" role="group">
                                                                                <a href="#"
                                                                                   class="btn btn-outline-primary btn-sm view-invoice-btn"
                                                                                   data-bs-toggle="modal"
                                                                                   data-bs-target="#view_invoice"
                                                                                   data-invoice-id="${payment.invoice.id}"
                                                                                   data-invoice-number="${payment.invoice.invoice_number}"
                                                                                   data-amount-due="${payment.invoice.amount_due}"
                                                                                   data-amount-paid="${payment.invoice.amount_paid}"
                                                                                   data-status="${payment.invoice.status}"
                                                                                   data-bill-to-name="${payment.bill_to_full_name ?? '—'}"
                                                                                   data-bill-to-address="${payment.bill_to_address ?? '—'}"
                                                                                   data-bill-to-email="${payment.bill_to_email ?? '—'}"
                                                                                   data-period-start="${payment.invoice.period_start}"
                                                                                   data-period-end="${payment.invoice.period_end}"
                                                                                   data-issued-at="${payment.invoice.issued_at}"
                                                                                   data-due-date="${payment.invoice.due_date}"
                                                                                   data-plan="${payment.invoice.subscription?.plan?.name ?? 'N/A'}"
                                                                                      data-subscription='${JSON.stringify(payment.invoice.subscription || {})}'
                                                                                   data-meta='${JSON.stringify(payment.meta || {})}'>
                                                                                  <i class="ti ti-eye me-1"></i>View
                                                                                </a>
                                                                                <a href="{{ url('/invoice') }}/${payment.invoice.id}/download" 
                                                                                   class="btn btn-success btn-sm"
                                                                                   target="_blank">
                                                                                  <i class="ti ti-download me-1"></i>PDF
                                                                                </a>
                                                                            </div>`;
                                                    } else {
                                                        downloadLink = `<span class="text-muted">No Invoice</span>`;
                                                    }

                                                    return `
                                                                        <tr>
                                                                            <td>📄 ${payment.transaction_reference ?? payment.payment_id}</td>
                                                                            <td>${date}</td>
                                                                            <td><span class="badge bg-${statusClass}">${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}</span></td>
                                                                            <td>₱${parseFloat(payment.amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                                                                            <td>${type}</td>
                                                                            <td>${payBtn}</td>
                                                                            <td>${downloadLink}</td>
                                                                        </tr>
                                                                    `;
                                                }).join('');

                                                tableInfo.textContent = `Showing ${start + 1} to ${Math.min(end, payments.length)} of ${payments.length} entries`;
                                            }

                                            function renderPagination() {
                                                const totalPages = Math.ceil(payments.length / perPage);
                                                if (totalPages <= 1) {
                                                    pagination.innerHTML = '';
                                                    return;
                                                }
                                                let html = '';
                                                html += `<li class="page-item${currentPage === 1 ? ' disabled' : ''}">
                                                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="${currentPage === 1}" data-page="${currentPage - 1}">
                                                                        <i class="ti ti-chevron-left"></i>
                                                                    </a>
                                                                </li>`;
                                                for (let i = 1; i <= totalPages; i++) {
                                                    html += `<li class="page-item${currentPage === i ? ' active' : ''}">
                                                                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                                                                    </li>`;
                                                }
                                                html += `<li class="page-item${currentPage === totalPages ? ' disabled' : ''}">
                                                                    <a class="page-link" href="#" aria-disabled="${currentPage === totalPages}" data-page="${currentPage + 1}">
                                                                        <i class="ti ti-chevron-right"></i>
                                                                    </a>
                                                                </li>`;
                                                pagination.innerHTML = html;
                                            }

                                            pagination.addEventListener('click', function (e) {
                                                if (e.target.tagName === 'A' && e.target.dataset.page) {
                                                    e.preventDefault();
                                                    const page = parseInt(e.target.dataset.page);
                                                    if (!isNaN(page) && page >= 1 && page <= Math.ceil(payments.length / perPage)) {
                                                        currentPage = page;
                                                        renderTable();
                                                        renderPagination();
                                                    }
                                                }
                                            });

                                            downloadAllBtn.addEventListener('click', function () {
                                                window.location.href = '{{ url("/invoices/download-all") }}';
                                            });

                                            fetchPayments();
                                        });
                                    </script>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', function () {
                                            const subsGrid = document.getElementById('subs-grid');
                                            const subsEmpty = document.getElementById('subs-empty');
                                            const subsAlert = document.getElementById('subs-alert');

                                            // Helper: format date safely
                                            const fmtDate = (d) => {
                                                if (!d) return '-';
                                                const dt = new Date(d);
                                                return isNaN(dt.getTime()) ? '-' : dt.toLocaleDateString();
                                            };

                                            // Helper: status → badge class
                                            const statusBadge = (status) => {
                                                const s = (status || '').toLowerCase();
                                                if (s === 'active') return 'success';
                                                if (s === 'trial') return 'info';
                                                if (s === 'pending') return 'warning';
                                                if (s === 'overdue' || s === 'cancelled' || s === 'failed') return 'danger';
                                                return 'secondary';
                                            };

                                            // Helper: progress (credits used)
                                            const calcProgress = (used, total) => {
                                                if (!total || total <= 0) return 0;
                                                const pct = Math.max(0, Math.min(100, Math.round((used / total) * 100)));
                                                return isFinite(pct) ? pct : 0;
                                            };

                                            // Render one subscription card, including payment info
                                            const renderSubCard = (sub) => {
                                                const plan = (sub.plan || 'starter').toString();
                                                const planName = plan.charAt(0).toUpperCase() + plan.slice(1);
                                                const status = sub.status || sub.payment_status || 'active';
                                                const badge = statusBadge(status);
                                                const start = fmtDate(sub.subscription_start || sub.trial_start);
                                                const end = fmtDate(sub.subscription_end || sub.trial_end);
                                                const nextRenewal = fmtDate(sub.next_renewal_date || sub.renewed_at);
                                                const amountPaid = sub.amount_paid ? Number(sub.amount_paid) : null;

                                                // Try to read credits from top-level or plan_details
                                                const details = sub.plan_details || {};
                                                const totalCredits = sub.employee_credits ?? details.employee_credits ?? details.included_credits ?? 0;
                                                const usedCredits = details.used_credits ?? 0;
                                                const progress = calcProgress(usedCredits, totalCredits);

                                                // Optional: branch / tenant labels if present
                                                const branchName = sub.branch?.name || details.branch_name || null;
                                                const tenantName = sub.branch?.tenant?.tenant_name || details.tenant_name || null;

                                                // Payments summary (show latest payment status if available)
                                                let paymentSummary = '';
                                                if (Array.isArray(sub.payments) && sub.payments.length > 0) {
                                                    // Show latest payment status and amount
                                                    const latest = sub.payments[0];
                                                    paymentSummary = `
                                                                <div class="mt-2 small">
                                                                    <span class="text-muted">Latest Payment:</span>
                                                                    <span class="badge bg-${statusBadge(latest.status)}">${latest.status ? latest.status.charAt(0).toUpperCase() + latest.status.slice(1) : '-'}</span>
                                                                    <span class="ms-2">₱${parseFloat(latest.amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>
                                                                    <span class="ms-2">${fmtDate(latest.paid_at)}</span>
                                                                </div>
                                                                `;
                                                }

                                                // CTA links (adjust to your routes if you have them)
                                                const manageUrl = '{{ url('billing/manage') }}';
                                                const upgradeUrl = '{{ url('billing/upgrade') }}';
                                                const topupUrl = '{{ url('billing/top-up') }}';

                                                // Fetch employee credits for this branch (if branch_id exists)
                                                let creditsInfo = '';
                                                if (sub.branch_id) {
                                                    creditsInfo = `<span class="small text-muted" data-branch-id="${sub.branch_id}" id="branch-credits-${sub.branch_id}">Loading credits...</span>`;
                                                }

                                                return `
                                                              <div class="col-xl-6 col-lg-6">
                                                                <div class="border rounded-3 p-3 h-100">
                                                                  <div class="d-flex align-items-start justify-content-between">
                                                                <div>
                                                                  <h6 class="mb-1">${planName} Plan</h6>
                                                                  ${branchName || tenantName ? `
                                                                    <div class="text-muted small">
                                                                      ${tenantName ? `<span class="me-2"><i class="ti ti-building"></i> ${tenantName}</span>` : ''}
                                                                      ${branchName ? `<span><i class="ti ti-home-2"></i> ${branchName}</span>` : ''}
                                                                    </div>` : ''
                                                    }
                                                                </div>
                                                                <span class="badge bg-${badge}">
                                                                    ${(status === 'paid' ? 'Active' : status.charAt(0).toUpperCase() + status.slice(1))}
                                                                </span>
                                                                  </div>

                                                                  <div class="row mt-3 g-2">
                                                                <div class="col-6">
                                                                  <div class="small text-muted">Current</div>
                                                                  <div class="fw-medium">${start}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                  <div class="small text-muted">End / Next Renewal</div>
                                                                  <div class="fw-medium">${end !== '-' ? end : nextRenewal}</div>
                                                                </div>
                                                                  </div>

                                                                  <div class="mt-3">
                                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                                  <span class="small text-muted">Employee Credits</span>
                                                                  <span class="small fw-medium">${usedCredits}/${totalCredits}</span>
                                                                </div>
                                                                <div class="progress" style="height: 8px;">
                                                                  <div class="progress-bar ${progress >= 85 ? 'bg-danger' : (progress >= 60 ? 'bg-warning' : '')}" role="progressbar" style="width: ${progress}%"></div>
                                                                </div>
                                                                ${creditsInfo}
                                                                  </div>

                                                                <!--  <div class="d-flex flex-wrap gap-2 mt-3">
                                                                <a href="${manageUrl}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-settings me-1"></i>Manage</a>
                                                                <a href="${upgradeUrl}" class="btn btn-outline-primary btn-sm"><i class="ti ti-arrow-up-right me-1"></i>Upgrade</a>
                                                                <a href="${topupUrl}" class="btn btn-primary btn-sm"><i class="ti ti-wallet me-1"></i>Top Up</a>
                                                                  </div> -->

                                                                  ${amountPaid !== null ? `
                                                                <div class="mt-3 border-top pt-2 d-flex justify-content-between">
                                                                  <span class="small text-muted">Amount</span>
                                                                  <span class="fw-medium">₱${amountPaid.toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>
                                                                </div>` : ''
                                                    }
                                                                  ${paymentSummary}
                                                                </div>
                                                              </div>
                                                            `;
                                            };

                                            // Fetch and render
                                            fetch('{{ route('api.payment-history') }}')
                                                .then(res => res.json())
                                                .then(data => {
                                                    const subs = Array.isArray(data.subscriptions) ? data.subscriptions : [];

                                                    // Remove skeletons
                                                    subsGrid.innerHTML = '';

                                                    if (!subs.length) {
                                                        subsEmpty.classList.remove('d-none');
                                                        return;
                                                    }

                                                    // Optional: small reminder if any subscription near renewal/overdue
                                                    const now = new Date();
                                                    const warn = subs.some(s => {
                                                        const next = s.next_renewal_date ? new Date(s.next_renewal_date) : null;
                                                        if (!next || isNaN(next.getTime())) return false;
                                                        const diffDays = Math.ceil((next - now) / (1000 * 60 * 60 * 24));
                                                        return diffDays <= 7; // within 7 days
                                                    });

                                                    if (warn) {
                                                        subsAlert.classList.remove('d-none');
                                                        subsAlert.classList.remove('alert-info');
                                                        subsAlert.classList.add('alert-warning');
                                                        subsAlert.innerHTML = `<strong>Renewal reminder:</strong> You have a subscription due soon. Consider topping up or renewing early.`;
                                                    }

                                                    // Render cards, including payment info
                                                    subsGrid.innerHTML = subs.map(renderSubCard).join('');

                                                    // After rendering, fetch employee credits for each branch if needed
                                                    subs.forEach(sub => {
                                                        if (sub.branch_id) {
                                                            fetch(`{{ route('api.employee-credits') }}?branch_id=${sub.branch_id}`)
                                                                .then(res => res.json())
                                                                .then(creditsData => {
                                                                    const el = document.getElementById(`branch-credits-${sub.branch_id}`);
                                                                    if (el) {
                                                                        el.textContent = `Credits: ${creditsData.employee_credits ?? 0}`;
                                                                    }
                                                                })
                                                                .catch(() => {
                                                                    const el = document.getElementById(`branch-credits-${sub.branch_id}`);
                                                                    if (el) {
                                                                        el.textContent = 'Credits: N/A';
                                                                    }
                                                                });
                                                        }
                                                    });
                                                })
                                                .catch(() => {
                                                    subsAlert.classList.remove('d-none');
                                                    subsAlert.classList.remove('alert-info');
                                                    subsAlert.classList.add('alert-danger');
                                                    subsAlert.innerHTML = `<strong>Oops!</strong> We couldn't load your subscriptions right now.`;
                                                });
                                        });
                                    </script>

           <script>
document.addEventListener('DOMContentLoaded', function () {
  const invoiceModal = document.getElementById('view_invoice');

  const phpFmt = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' });
  const fmt = (v) => phpFmt.format(isFinite(+v) ? +v : 0);
  const num = (v, d = 0) => { const n = parseFloat(v); return isFinite(n) ? n : d; };

  const parseJSONSafe = (val) => {
    try {
      if (typeof val === 'string') {
        let x = JSON.parse(val);
        if (typeof x === 'string') x = JSON.parse(x); // handle double-encoded
        return x;
      }
      return val ?? null;
    } catch { return null; }
  };

  const escapeHtml = (s) =>
    String(s ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');

  invoiceModal.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    if (!btn) return;

    const d = btn.dataset;

    // Header
    document.getElementById('inv-number').textContent    = d.invoiceNumber || '—';
    document.getElementById('inv-issued-at').textContent = d.issuedAt ? new Date(d.issuedAt).toLocaleDateString() : '—';
    document.getElementById('inv-due-date').textContent  = d.dueDate ? new Date(d.dueDate).toLocaleDateString() : '—';

    // Bill To
    document.getElementById('inv-to-name').textContent    = d.billToName || '—';
    document.getElementById('inv-to-address').textContent = d.billToAddress || '—';
    document.getElementById('inv-to-email').textContent   = d.billToEmail || '—';

    // Parse subscription (contains plan_details)
    const subscription = parseJSONSafe(d.subscription) || {};
    const pd = subscription.plan_details || {};

    // ===== 1) Keep your original META logic (line title) =====
    let lineTitle = 'Starter'; // default
    if (d.meta) {
      try {
        let meta = d.meta;
        if (typeof meta === 'string') {
          meta = JSON.parse(meta);
          if (typeof meta === 'string') meta = JSON.parse(meta); // double-encoded safety
        }
        if (meta.type === 'monthly_starter') {
          lineTitle = 'Starter - Monthly';
        } else if (meta.type === 'employee_credits') {
          lineTitle = `Starter - Credits${meta.additional_credits ? ' (+' + meta.additional_credits + ')' : ''}`;
        } else if (meta.type === 'renewal') {
          lineTitle = 'Starter - Renewal';
        }
      } catch (e) {
        console.warn('Invalid meta JSON:', d.meta, e);
      }
    }

    // ===== 2) Build details block (employees + add-ons) unless employee_credits =====
    const isEmployeeCredits = lineTitle.toLowerCase().includes('credits');
    const emp = pd.total_employees ?? subscription.total_employee;
    const addons = Array.isArray(pd.addons) ? pd.addons : [];

    const addonItems = addons.map(a => {
      const name  = escapeHtml(a?.name ?? '');
      const price = (a?.price != null) ? fmt(a.price) : '';
      const type  = a?.type ? ` / ${escapeHtml(a.type)}` : '';
      return `<li>${name}${price ? ` — ${price}${type}` : ''}</li>`;
    }).join('');

    const detailsHtml = !isEmployeeCredits
      ? `
        ${emp != null ? `<div class="small text-muted">Total employees: ${escapeHtml(emp)}</div>` : ''}
        ${addons.length
          ? `<div class="small text-muted mt-1">Add-ons:</div>
             <ul class="mb-0 ps-3 small">${addonItems}</ul>`
          : `<div class="small text-muted mt-1">Add-ons: None</div>`
        }
      `
      : ''; // compact for employee_credits

    // ===== 3) Money (prefer plan_details with safe fallbacks) =====
    const employeeAmt = (pd.employee_price != null)
      ? num(pd.employee_price)
      : num(pd.total_employees) * num(pd.price_per_employee);

    const addonsAmt  = num(pd.addons_price, 0);
    const vatAmt     = (pd.vat != null) ? num(pd.vat) : null;
    const finalPrice = (pd.final_price != null) ? num(pd.final_price) : null;

    const fallbackAmountDue = num(d.amountDue, 0);

    // Subtotal = pre-VAT
    const subtotal = (vatAmt != null && finalPrice != null)
      ? (finalPrice - vatAmt)
      : (employeeAmt + addonsAmt);

    const tax = (vatAmt != null) ? vatAmt : Math.max(fallbackAmountDue - subtotal, 0);
    const totalDue = (finalPrice != null) ? finalPrice : (subtotal + tax);

    const amountPaid = num(d.amountPaid, 0);
    const balance = Math.max(totalDue - amountPaid, 0);

    // ===== 4) Items table (title + optional details below) =====
    const tbody = document.getElementById('inv-items');
    tbody.innerHTML = `
      <tr>
        <td>
          <div><strong>${escapeHtml(lineTitle)}</strong></div>
          ${detailsHtml}
        </td>
        <td>${d.periodStart ? new Date(d.periodStart).toLocaleDateString() : '—'} - ${d.periodEnd ? new Date(d.periodEnd).toLocaleDateString() : '—'}</td>
        <td>1</td>
        <td>${fmt(totalDue)}</td>
        <td class="text-end">${fmt(totalDue)}</td>
      </tr>
    `;

    // ===== 5) Totals (right column) =====
    document.getElementById('inv-subtotal').textContent    = fmt(subtotal);
    document.getElementById('inv-tax').textContent          = fmt(tax);
    document.getElementById('inv-amount-paid').textContent  = fmt(amountPaid);
    document.getElementById('inv-balance').textContent      = fmt(balance);

    // ===== 6) Download link =====
    const downloadBtn = document.getElementById('download-invoice-btn');
    if (downloadBtn && d.invoiceId) {
      downloadBtn.href = `{{ url('/invoice') }}/${d.invoiceId}/download`;
    }
  });
});
</script>

            @endpush
@endsection