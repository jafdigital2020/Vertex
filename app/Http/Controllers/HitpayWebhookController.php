<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\BranchSubscription;
use Carbon\Carbon;

class HitpayWebhookController extends Controller
{
    /**
     * Unified webhook endpoint for all HitPay events.
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Received Hitpay unified webhook', ['payload' => $payload]);

        // Normalize structure
        $status    = strtolower((string) data_get($payload, 'status', ''));
        $reference = data_get($payload, 'payment_request.reference_number')
            ?? data_get($payload, 'reference_number')
            ?? data_get($payload, 'payment.reference_number');

        if (!$reference || $status === '') {
            return response()->json(['success' => false, 'message' => 'Invalid webhook data'], 400);
        }

        // Find payment
        $payment = Payment::where('transaction_reference', $reference)->first();
        if (!$payment) {
            Log::warning('Hitpay webhook: Payment not found', ['reference' => $reference]);
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        // Update raw gateway response
        $payment->update(['gateway_response' => json_encode($payload)]);

        // Decode meta if stored
        $meta = is_array($payment->meta) ? $payment->meta : (is_string($payment->meta) ? json_decode($payment->meta, true) : []);
        if (!is_array($meta)) $meta = [];

        $type      = data_get($meta, 'type');
        $invoiceId = data_get($meta, 'invoice_id');

        // Route internally using match expression
        return match ($type) {
            'employee_credits' => $this->processEmployeeCredits($payment, $meta, $status),
            'monthly_starter'  => $this->processMonthlyStarter($payment, $status),
            default            => $this->processSubscriptionPayment($payment, $status, $invoiceId),
        };
    }

    /**
     * Process employee credit top-ups.
     */
    private function processEmployeeCredits(Payment $payment, array $meta, string $status)
    {
        if (!in_array($status, ['succeeded', 'completed', 'paid', 'successful'])) {
            return response()->json(['success' => true, 'message' => 'Ignored non-paid credit event']);
        }

        $subscription = $payment->subscription;
        $credits      = (int) data_get($meta, 'additional_credits', 0);

        if ($subscription && $credits > 0 && !$payment->applied_at) {
            $subscription->increment('employee_credits', $credits);
            $payment->update(['status' => 'paid', 'paid_at' => now(), 'applied_at' => now()]);

            Log::info('Employee credits applied', [
                'subscription_id' => $subscription->id,
                'credits' => $credits,
                'payment_id' => $payment->id,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Employee credits processed']);
    }

    /**
     * Process monthly starter renewals.
     */
    private function processMonthlyStarter(Payment $payment, string $status)
    {
        if (!in_array($status, ['succeeded', 'completed', 'paid', 'successful'])) {
            return response()->json(['success' => true, 'message' => 'Ignored non-paid renewal']);
        }

        $subscription = $payment->subscription;
        if (!$subscription) {
            Log::warning('Monthly Starter: Subscription not found', ['payment_id' => $payment->id]);
            return response()->json(['success' => false, 'message' => 'Subscription not found']);
        }

        $now = now();
        $currentEnd = $subscription->subscription_end ?? $now;
        $newEnd = Carbon::parse($currentEnd)->addDays(30);

        $subscription->update([
            'subscription_start' => $currentEnd,
            'subscription_end'   => $newEnd,
            'next_renewal_date'  => Carbon::parse($newEnd)->subDays(7),
            'status'             => 'active',
            'renewed_at'         => $now,
        ]);

        $payment->update(['status' => 'paid', 'paid_at' => $now]);

        Log::info('Monthly Starter renewed', [
            'subscription_id' => $subscription->id,
            'payment_id' => $payment->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Monthly Starter processed']);
    }

    /**
     * Process normal subscription payments.
     */
    private function processSubscriptionPayment(Payment $payment, string $status, $invoiceId = null)
    {
        $paidStatuses   = ['succeeded', 'completed', 'paid', 'successful'];
        $failedStatuses = ['failed', 'cancelled', 'canceled'];

        if (in_array($status, $paidStatuses)) {
            $payment->update(['status' => 'paid', 'paid_at' => now()]);

            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice && $invoice->status !== 'paid') {
                    $invoice->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'amount_paid' => $invoice->amount_due,
                    ]);
                    Log::info('Invoice marked paid', ['invoice_id' => $invoice->id]);
                }
            }
            return response()->json(['success' => true, 'message' => 'Subscription payment processed']);
        }

        $newStatus = in_array($status, $failedStatuses) ? 'failed' : $status;
        $payment->update(['status' => $newStatus]);

        return response()->json(['success' => true, 'message' => 'Subscription payment updated']);
    }
}
