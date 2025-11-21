<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .header {
            margin-bottom: 30px;
        }

        .company-info {
            float: left;
            width: 50%;
        }

        .invoice-info {
            float: right;
            width: 50%;
            text-align: right;
        }

        .bill-to {
            clear: both;
            margin: 30px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .items-table th {
            background-color: #f2f2f2;
        }

        .totals {
            float: right;
            width: 300px;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }

        .clear {
            clear: both;
        }

        .footer {
            margin-top: 80px;
            font-size: 11px;
            color: #555;
            border-top: 1px solid #ddd;
            padding-top: 30px;
        }

        .terms-section {
            margin-top: 10px;
        }

        .terms-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .terms-list {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .terms-item {
            margin-bottom: 4px;
            display: flex;
            align-items: flex-start;
        }

        .terms-bullet {
            margin-right: 6px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-info">
            @php
                $path = public_path('build/img/jaf.png');
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            @endphp
            <img src="{{ $base64 }}" alt="Timora Logo" style="height: 60px; margin-bottom: 10px;">
            {{-- <h2>{{ $company['name'] }}</h2> --}}
            <p style="margin: 2px 0;">{{ $company['address'] }}</p>
            <p style="margin: 2px 0;">{{ $company['email'] }}</p>
            <p style="margin: 2px 0;">TIN: 010868588000</p>
        </div>

        <div class="invoice-info">
            <h2>Invoice</h2>
            <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Issue Date:</strong>
                {{ $invoice->issued_at ? \Carbon\Carbon::parse($invoice->issued_at)->format('M d, Y') : 'N/A' }}</p>
            <p><strong>Due Date:</strong>
                {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'N/A' }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="bill-to">
        <h3>Bill To:</h3>
        <p>{{ $bill_to['name'] }}</p>
        <p>{{ $bill_to['email'] }}</p>
        <p>{{ $bill_to['address'] }}</p>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Period</th>
                <th>Quantity</th>
                <th>Rate</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                @php
                    // existing
                    $sub = $subscription ?? $invoice->subscription ?? $invoice->branchSubscription ?? null;

                    $plan = data_get($sub, 'plan', 'starter');
                    $totalEmployees = data_get($sub, 'plan_details.total_employees') ?? data_get($sub, 'total_employee');
                    $addons = data_get($sub, 'plan_details.addons', []);
                    $addonNames = collect($addons)->pluck('name')->filter()->implode(', ');

                    // 1) Collect and NORMALIZE payment metas
                    $metas = collect(data_get($invoice, 'payments', []))
                        ->map(function ($p) {
                            $m = data_get($p, 'meta');
                            if (is_string($m)) {
                                $decoded = json_decode($m, true);
                                if (is_string($decoded))
                                    $decoded = json_decode($decoded, true); // double-encoded
                                return is_array($decoded) ? $decoded : null;
                            }
                            return is_array($m) ? $m : (is_object($m) ? (array) $m : null);
                        })
                        ->filter()
                        ->values();

                    // 2) Prefer meta that matches this invoice id
                    $meta = $metas->firstWhere('invoice_id', data_get($invoice, 'id')) ?? $metas->first();

                    // 3) Build the line title (NAME ONLY changes)
                    $lineTitle = ucfirst($plan) . ' Plan';
                    if ($meta && !empty($meta['type'])) {
                        $type = $meta['type'];

                        if ($type === 'employee_credits') {
                            $extra = isset($meta['additional_credits']) ? ' (+' . $meta['additional_credits'] . ')' : '';
                            $lineTitle = ucfirst($plan) . ' - Credits' . $extra;
                        } elseif ($type === 'renewal') {
                            $lineTitle = ucfirst($plan) . ' - Renewal';
                        } elseif (is_string($type) && Str::startsWith($type, 'monthly_')) {
                            $lineTitle = ucfirst($plan) . ' - Monthly';
                        }
                    }
                @endphp

                <td>
                    <strong>{{ $lineTitle }}</strong><br>
                    @if(!is_null($totalEmployees))
                        <small>Total Employees: {{ $totalEmployees }}</small><br>
                    @endif
                    @if(!empty($addonNames))
                        <small>Add-ons: {{ $addonNames }}</small>
                    @else
                        <small>Add-ons: None</small>
                    @endif
                </td>
                <td>
                    {{ $invoice->period_start ? \Carbon\Carbon::parse($invoice->period_start)->format('M d, Y') : 'N/A' }}
                    -
                    {{ $invoice->period_end ? \Carbon\Carbon::parse($invoice->period_end)->format('M d, Y') : 'N/A' }}
                </td>
                <td>1</td>
                <td>&#8369;{{ number_format($invoice->amount_due, 2) }}</td>
                <td>&#8369;{{ number_format($invoice->amount_due, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @php
        // ---------- get subscription & plan_details ----------
        $sub = $subscription ?? $invoice->subscription ?? $invoice->branchSubscription ?? null;
        $pd = data_get($sub, 'plan_details', []);

        // ---------- normalize & pick meta ----------
        $metas = collect(data_get($invoice, 'payments', []))
            ->map(function ($p) {
                $m = data_get($p, 'meta');
                if (is_string($m)) {
                    $decoded = json_decode($m, true);
                    if (is_string($decoded))
                        $decoded = json_decode($decoded, true); // double-encoded
                    return is_array($decoded) ? $decoded : null;
                }
                return is_array($m) ? $m : (is_object($m) ? (array) $m : null);
            })
            ->filter()
            ->values();

        $meta = $metas->firstWhere('invoice_id', data_get($invoice, 'id')) ?? $metas->first();
        $metaType = $meta['type'] ?? null;

        $isCredits = ($metaType === 'employee_credits');
        $isRenewalOrMonthly = ($metaType === 'renewal') || (is_string($metaType) && Str::startsWith($metaType, 'monthly_'));

        // ---------- prepare line item display (Qty/Rate/Amount) ----------
        if ($isCredits) {
            // credits count (default 1) and per-credit price from invoice
            $creditsQty = max(1, (int) ($meta['additional_credits'] ?? 1));
            $lineAmount = (float) ($invoice->amount_due ?? 0);
            $lineRate = $creditsQty > 0 ? round($lineAmount / $creditsQty, 2) : $lineAmount;

            $lineQty = $creditsQty;
        } else {
            // keep previous single-line approach using amount_due
            $lineQty = 1;
            $lineRate = (float) ($invoice->amount_due ?? 0);
            $lineAmount = (float) ($invoice->amount_due ?? 0);
        }

        // ---------- totals logic ----------
        if ($isCredits) {
            // For ADD CREDITS invoices, show ONLY the credits amount (no subscription breakdown)
            $subtotal = round((float) ($invoice->amount_due ?? 0) / 1.12, 2);
            $tax = round($subtotal * 0.12, 2); // 12% VAT, credits are VAT inclusive
            $totalDue = $subtotal + $tax;
        } else {
            // Original subscription totals (renewal/monthly/others)
            $empCount = (int) (data_get($pd, 'total_employees') ?? data_get($sub, 'total_employee') ?? 0);
            $pricePerEmp = (float) data_get($pd, 'price_per_employee', 0);
            $employeeAmt = (float) data_get($pd, 'employee_price', $empCount * $pricePerEmp);
            $addonsAmt = (float) data_get($pd, 'addons_price', 0);
            $vatAmt = data_get($pd, 'vat');         // may be null
            $finalPrice = data_get($pd, 'final_price'); // may be null

            // Compute subtotal (pre-VAT)
            $computedSubtotal = round($employeeAmt + $addonsAmt, 2);
            $subtotal = (!is_null($vatAmt) && !is_null($finalPrice))
                ? round($finalPrice - $vatAmt, 2)
                : $computedSubtotal;

            // Compute tax (VAT)
            $tax = !is_null($vatAmt)
                ? (float) $vatAmt
                : max(0, round(((float) ($invoice->amount_due ?? 0)) - $subtotal, 2));

            // Total (amount due for the period)
            $totalDue = !is_null($finalPrice)
                ? (float) $finalPrice
                : round($subtotal + $tax, 2);
        }

        // payments/balance (same for both)
        $amountPaid = (float) ($invoice->amount_paid ?? 0);
        $balance = max(round($totalDue - $amountPaid, 2), 0);
    @endphp
    <div class="totals">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>&#8369;{{ number_format($subtotal, 2) }}</span>
        </div>
        <div class="total-row">
            <span>Tax (VAT):</span>
            <span>&#8369;{{ number_format($tax, 2) }}</span>
        </div>
        <div class="total-row">
            <span>Total:</span>
            <span>&#8369;{{ number_format($totalDue, 2) }}</span>
        </div>
        <div class="total-row">
            <span>Amount Paid:</span>
            <span>&#8369;{{ number_format($amountPaid, 2) }}</span>
        </div>
        <div class="total-row" style="border-top: 1px solid #ddd; padding-top: 5px; font-weight: bold;">
            <span>Balance Due:</span>
            <span>&#8369;{{ number_format($balance, 2) }}</span>
        </div>
    </div>


    <div class="clear"></div>
    <div class="footer" style="position: relative; bottom: 0; width: 100%;">
        <div class="terms-section">
            <p class="terms-title">Terms & Conditions:</p>
            <ul class="terms-list">
                <li class="terms-item">
                    <span class="terms-bullet">&#8226;</span>
                    <span>All subscription payments are non-refundable.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-bullet">&#8226;</span>
                    <span>Cancellations must be made through formal written communication at least thirty (30) days
                        before the next billing cycle.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-bullet">&#8226;</span>
                    span>Add-on charges apply for additional features or services purchased during the billing
                    period.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-bullet">&#8226;</span>
                    <span>Payments must be settled at least seven (7) days before the billing due date.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-bullet">&#8226;</span>
                    <span>If payment is not settled by the due date, the Client is granted a ten (10)-day grace
                        period.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-bullet">&#8226;</span>
                    <span>If fail to settle payment after the grace period, access to timekeeping and payroll processing
                        shall be automatically suspended.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-bullet">&#8226;</span>
                    <span>After thirty (30) days of unpaid balance, a six percent (6%) penalty will be added to the
                        outstanding amount.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-bullet">&#8226;</span>
                    <span>We are not liable for any indirect, incidental, or consequential damages, including loss of
                        profits, revenue, or data.</span>
                </li>
            </ul>
        </div>
    </div>
</body>

</html>