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
            <h2>{{ $company['name'] }}</h2>
            <p>{{ $company['address'] }}</p>
            <p>{{ $company['email'] }}</p>
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
                <td>{{ $invoice->branchSubscription->plan ?? 'Starter Plan' }}</td>
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

    <div class="totals">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>&#8369;{{ number_format($invoice->amount_due, 2) }}</span>
        </div>
        <div class="total-row">
            <span>Tax:</span>
            <span>&#8369;0.00</span>
        </div>
        <div class="total-row">
            <span>Amount Paid:</span>
            <span>&#8369;{{ number_format($invoice->amount_paid ?? 0, 2) }}</span>
        </div>
        <div class="total-row" style="border-top: 1px solid #ddd; padding-top: 5px; font-weight: bold;">
            <span>Balance Due:</span>
            <span>&#8369;{{ number_format(max(($invoice->amount_due ?? 0) - ($invoice->amount_paid ?? 0), 0), 2) }}</span>
        </div>
    </div>

    <div class="clear"></div>
    <div class="footer" style="position: relative; bottom: 0; width: 100%;">
        <div class="terms-section">
            <p class="terms-title">Terms & Conditions:</p>
            <ul class="terms-list">
                <li class="terms-item">
                    <span class="terms-bullet">&#8226;</span>
                    <span>All payments must be made according to the agreed schedule.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-bullet">&#8226;</span>
                    <span>Add-on charges apply for additional features or services purchased during the billing period.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-bullet">&#8226;</span>
                    <span>We are not liable for any indirect, incidental, or consequential damages, including loss of profits, revenue, or data.</span>
                </li>
            </ul>
        </div>
    </div>
</body>

</html>