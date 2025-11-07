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
                <i class="fas fa-receipt fa-2x mb-2"></i>
                <h6>No invoices found</h6>
                <p>Try adjusting your search filters.</p>
            </div>
        </td>
    </tr>
@endforelse
