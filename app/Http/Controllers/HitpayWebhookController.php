<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\BranchSubscription;
use App\Models\BranchAddon;
use Carbon\Carbon;

class HitpayWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Received Hitpay unified webhook', ['payload' => $payload]);

        $status    = strtolower((string) data_get($payload, 'status', ''));
        $reference = data_get($payload, 'payment_request.reference_number')
            ?? data_get($payload, 'reference_number')
            ?? data_get($payload, 'payment.reference_number');

        if (!$reference || $status === '') {
            return response()->json(['success' => false, 'message' => 'Invalid webhook data'], 400);
        }

        $payment = Payment::where('transaction_reference', $reference)->first();
        if (!$payment) {
            Log::warning('Hitpay webhook: Payment not found', ['reference' => $reference]);
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        $payment->update(['gateway_response' => json_encode($payload)]);

        $meta = is_array($payment->meta)
            ? $payment->meta
            : (is_string($payment->meta) ? json_decode($payment->meta, true) : []);
        if (!is_array($meta)) $meta = [];

        $type = data_get($meta, 'type');

        return match ($type) {
            'employee_credits' => $this->processEmployeeCredits($payment, $meta, $status),
            'monthly_starter'  => $this->processMonthlyStarter($payment, $meta, $status),
            'addon'            => $this->processAddonPayment($payment, $meta, $status),
            default            => $this->processSubscriptionPayment($payment, $meta, $status),
        };
    }

    private function findInvoice(Payment $payment, ?array $meta = []): ?Invoice
    {
        $invoiceId = data_get($meta, 'invoice_id');
        if ($invoiceId) {
            return Invoice::find($invoiceId);
        }
        return Invoice::where('invoice_number', $payment->transaction_reference)->first();
    }

    private function processEmployeeCredits(Payment $payment, array $meta, string $status)
    {
        if (!in_array($status, ['succeeded', 'completed', 'paid', 'successful'])) {
            return response()->json(['success' => true, 'message' => 'Ignored non-paid credit event']);
        }

        $subscription = $payment->subscription;
        $credits      = (int) data_get($meta, 'additional_credits', 0);

        if ($subscription && $credits > 0 && !$payment->applied_at) {
            $subscription->increment('employee_credits', $credits);

            $payment->update([
                'status'    => 'paid',
                'paid_at'   => now(),
                'applied_at' => now()
            ]);

            // update subscription payment status
            $subscription->update([
                'payment_status' => 'paid',
                'status'         => 'active',
            ]);

            // update invoice
            $invoice = $this->findInvoice($payment, $meta);
            if ($invoice && $invoice->status !== 'paid') {
                $invoice->update([
                    'status'      => 'paid',
                    'paid_at'     => now(),
                    'amount_paid' => $invoice->amount_due,
                ]);
                Log::info('Invoice marked paid (employee credits)', ['invoice_id' => $invoice->id]);
            }

            Log::info('Employee credits applied', [
                'subscription_id' => $subscription->id,
                'credits'         => $credits,
                'payment_id'      => $payment->id,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Employee credits processed']);
    }

    private function processMonthlyStarter(Payment $payment, array $meta, string $status)
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
            'payment_status'     => 'paid',
            'renewed_at'         => $now,
        ]);

        $payment->update(['status' => 'paid', 'paid_at' => $now]);

        // update invoice
        $invoice = $this->findInvoice($payment, $meta);
        if ($invoice && $invoice->status !== 'paid') {
            $invoice->update([
                'status'      => 'paid',
                'paid_at'     => $now,
                'amount_paid' => $invoice->amount_due,
            ]);
            Log::info('Invoice marked paid (monthly starter)', ['invoice_id' => $invoice->id]);
        }

        Log::info('Monthly Starter renewed', [
            'subscription_id' => $subscription->id,
            'payment_id'      => $payment->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Monthly Starter processed']);
    }

    private function processAddonPayment(Payment $payment, array $meta, string $status)
    {
        if (!in_array($status, ['succeeded', 'completed', 'paid', 'successful'])) {
            return response()->json(['success' => true, 'message' => 'Ignored non-paid addon event']);
        }

        $branchAddonId = data_get($meta, 'branch_addon_id');
        if (!$branchAddonId) {
            Log::warning('Addon webhook: branch_addon_id not found in meta', ['payment_id' => $payment->id]);
            return response()->json(['success' => false, 'message' => 'Branch addon ID not found'], 400);
        }

        $branchAddon = BranchAddon::find($branchAddonId);
        if (!$branchAddon) {
            Log::warning('Addon webhook: BranchAddon not found', ['branch_addon_id' => $branchAddonId]);
            return response()->json(['success' => false, 'message' => 'Branch addon not found'], 404);
        }

        // Activate the addon
        if (!$branchAddon->active) {
            $branchAddon->update([
                'active' => true,
            ]);
            Log::info('Addon activated', [
                'branch_addon_id' => $branchAddon->id,
                'addon_id' => $branchAddon->addon_id,
                'branch_id' => $branchAddon->branch_id,
            ]);
        }

        // Update payment status
        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Update invoice
        $invoice = $this->findInvoice($payment, $meta);
        if ($invoice && $invoice->status !== 'paid') {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'amount_paid' => $invoice->amount_due,
            ]);
            Log::info('Invoice marked paid (addon)', ['invoice_id' => $invoice->id]);
        }

        return response()->json(['success' => true, 'message' => 'Addon payment processed and activated']);
    }

    private function processSubscriptionPayment(Payment $payment, array $meta, string $status)
    {
        $paidStatuses   = ['succeeded', 'completed', 'paid', 'successful'];
        $failedStatuses = ['failed', 'cancelled', 'canceled'];

        if (in_array($status, $paidStatuses)) {
            $payment->update(['status' => 'paid', 'paid_at' => now()]);

            $invoice = $this->findInvoice($payment, $meta);
            if ($invoice && $invoice->status !== 'paid') {
                $invoice->update([
                    'status'      => 'paid',
                    'paid_at'     => now(),
                    'amount_paid' => $invoice->amount_due,
                ]);
                Log::info('Invoice marked paid (subscription)', ['invoice_id' => $invoice->id]);
            }

            $subscription = $payment->subscription;
            if ($subscription) {
                // If trial_end exists and is in the future, start subscription after trial ends
                $startDate = $subscription->trial_end && $subscription->trial_end > Carbon::now()
                    ? $subscription->trial_end->copy()
                    : now();

                $subscription->update([
                    'payment_status'     => 'paid',
                    'status'             => 'active',
                    'subscription_start' => $startDate,
                    'subscription_end'   => $startDate->copy()->addDays(30),
                    'next_renewal_date'  => $startDate->copy()->addDays(30)->subDays(7),
                ]);
                Log::info('Subscription activated', ['subscription_id' => $subscription->id]);
            }

            return response()->json(['success' => true, 'message' => 'Subscription payment processed']);
        }

        $newStatus = in_array($status, $failedStatuses) ? 'failed' : $status;
        $payment->update(['status' => $newStatus]);

        if ($payment->subscription) {
            $payment->subscription->update([
                'payment_status' => $newStatus,
                'status'         => in_array($newStatus, ['failed', 'refunded', 'expired']) ? 'expired' : $newStatus,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Subscription payment updated']);
    }
}
