<?php $page = 'bills-payment'; ?>
@extends('layout.mainlayout')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content">

        <!-- Breadcrumb -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Payment History</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="#"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Payment History</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /Breadcrumb -->

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
            .then(data => {
                payments = [];
                if (data.subscriptions && Array.isArray(data.subscriptions)) {
                    data.subscriptions.forEach(sub => {
                        if (sub.payments && Array.isArray(sub.payments)) {
                            sub.payments.forEach(payment => {
                                payments.push({
                                    ...payment,
                                    plan: sub.plan,
                                    plan_details: sub.plan_details,
                                });
                            });
                        }
                    });
                }
                renderTable();
                renderPagination();
            })
            .catch(() => {
                tableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Failed to load payment history.</td></tr>`;
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

            // Download link (dummy, replace with real if available)
            let downloadLink = `<a href="#" class="text-primary disabled" tabindex="-1" aria-disabled="true">Download</a>`;

            return `
                <tr>
                    <td>📄 ${payment.transaction_reference ?? payment.payment_id}</td>
                    <td>${date}</td>
                    <td><span class="badge bg-${statusClass}">${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}</span></td>
                    <td>₱${parseFloat(payment.amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
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
        // Dummy: Implement download all logic if available
        alert('Download all payments is not implemented.');
    });

    fetchPayments();
});
</script>
@endpush
@endsection