@if($invoice->items && $invoice->items->count() > 0)
    <div class="card mt-3">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="ti ti-receipt-2 me-2"></i>Invoice Details
                </h6>
                <small class="text-muted">{{ $invoice->items->count() }} line items</small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Period</th>
                            <th class="text-center">Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        @php
                            $metadata = is_string($item->metadata) ? json_decode($item->metadata, true) : ($item->metadata ?? []);
                            $itemType = $metadata['type'] ?? 'standard';
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $item->description }}</div>
                                @if(isset($metadata['device']) && $itemType === 'biometric_device')
                                    <small class="text-muted">
                                        Model: {{ $metadata['device']['model'] ?? 'Standard' }}
                                    </small>
                                @endif
                                @if(isset($metadata['employee_count']) && $itemType === 'additional_employees')
                                    <small class="text-muted">
                                        Beyond plan limit
                                    </small>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">₱{{ number_format($item->rate, 2) }}</td>
                            <td class="text-end fw-medium">₱{{ number_format($item->amount, 2) }}</td>
                            <td class="text-center">
                                @if($item->period === 'one-time')
                                    <span class="badge bg-warning bg-opacity-10 text-warning">One-time</span>
                                @elseif($item->period)
                                    <span class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($item->period) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @switch($itemType)
                                    @case('base_subscription')
                                        <span class="badge bg-primary bg-opacity-10 text-primary">Base Plan</span>
                                        @break
                                    @case('additional_employees')
                                        <span class="badge bg-info bg-opacity-10 text-info">Extra Users</span>
                                        @break
                                    @case('mobile_access')
                                        <span class="badge bg-success bg-opacity-10 text-success">Mobile</span>
                                        @break
                                    @case('addon_monthly')
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">Add-on</span>
                                        @break
                                    @case('addon_onetime')
                                        <span class="badge bg-warning bg-opacity-10 text-warning">Setup</span>
                                        @break
                                    @case('biometric_device')
                                        <span class="badge bg-dark bg-opacity-10 text-dark">Hardware</span>
                                        @break
                                    @case('biometric_service')
                                        <span class="badge bg-danger bg-opacity-10 text-danger">Service</span>
                                        @break
                                    @case('implementation_fee')
                                        <span class="badge" style="background: rgba(255, 108, 55, 0.1); color: #ff6c37;">Implementation</span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark">Standard</span>
                                @endswitch
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-medium">Subtotal:</td>
                            <td class="text-end fw-medium">₱{{ number_format($invoice->items->sum('amount'), 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                        @if($invoice->vat_amount > 0)
                        <tr>
                            <td colspan="3" class="text-end">VAT ({{ $invoice->vat_percentage ?? 12 }}%):</td>
                            <td class="text-end">₱{{ number_format($invoice->vat_amount, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                        @endif
                        <tr class="table-dark">
                            <td colspan="3" class="text-end fw-bold">Total:</td>
                            <td class="text-end fw-bold">₱{{ number_format($invoice->amount_due, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @if($invoice->invoice_type === 'subscription')
            <div class="card-footer bg-light py-2">
                <small class="text-success">
                    <i class="ti ti-wand me-1"></i>
                    Generated from wizard selections - complete pricing breakdown included
                </small>
            </div>
        @endif
    </div>
@endif