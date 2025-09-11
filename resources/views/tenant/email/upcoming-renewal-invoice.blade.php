@component('mail::message')
# Subscription Renewal Notice

Dear {{ $invoice->customer_name ?? 'Valued Customer' }},

Your subscription will expire in **7 days**. Please renew your subscription to continue enjoying our services:

@component('mail::panel')
**Invoice Details**

**Invoice Number:** {{ $invoice->invoice_number }}<br>
**Amount Due:** {{ $invoice->currency }} {{ number_format($invoice->amount_due, 2) }}<br>
**Due Date:** {{ \Carbon\Carbon::parse($invoice->due_date)->toFormattedDateString() }}<br>
**Billing Period:** {{ \Carbon\Carbon::parse($invoice->period_start)->toFormattedDateString() }} – {{ \Carbon\Carbon::parse($invoice->period_end)->toFormattedDateString() }}
@endcomponent

Your payment method on file will be automatically charged on the due date. No action is required unless you wish to update your billing information.

@component('mail::button', ['url' => url('/billing'), 'color' => 'primary'])
View Invoice Details
@endcomponent

@component('mail::subcopy')
If you have any questions about your subscription or billing, please don't hesitate to contact our support team.
@endcomponent

Best regards,
The {{ config('app.name') }} <br>
Team
@endcomponent
