<?php

namespace App\Http\Controllers;

use App\Models\BranchSubscription;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HitpayWebhookController extends Controller
{
    //
    public function handleEmployeeCredits(Request $request)
    {
        $payload = $request->all();
        Log::info('Received Hitpay webhook', ['payload' => $payload]);

        // 1) Be flexible: HitPay sends different shapes
        $reference = data_get($payload, 'payment_request.reference_number')
            ?? data_get($payload, 'reference_number')
            ?? data_get($payload, 'payment.reference_number');

        $status = strtolower((string) data_get($payload, 'status', ''));

        if (!$reference || $status === '') {
            return response()->json(['success' => false, 'message' => 'Invalid webhook data'], 400);
        }

        // 2) Find the payment by our stored reference
        $payment = Payment::where('transaction_reference', $reference)->first();
        if (!$payment) {
            Log::warning('Hitpay webhook: Payment not found', ['reference' => $reference]);
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        // 3) Always keep latest gateway payload
        $payment->update(['gateway_response' => $payload]);

        // 4) Normalize statuses
        $paidStatuses   = ['succeeded', 'completed', 'paid', 'successful'];
        $failedStatuses = ['failed', 'cancelled', 'canceled'];

        if (in_array($status, $paidStatuses, true)) {
            // 4a) Mark paid if not yet; idempotent
            if (!$payment->isPaid()) {
                $payment->update([
                    'status'  => 'paid',
                    'paid_at' => now(),
                ]);
            }

            // 4b) Apply credits EXACTLY ONCE (even if already paid earlier)
            if ($payment->isCreditsTopup() && !$payment->alreadyApplied()) {
                $subscription      = $payment->subscription; // belongsTo BranchSubscription
                $additionalCredits = (int) data_get($payment, 'meta.additional_credits', 0);

                if ($subscription && $additionalCredits > 0) {
                    // Ensure employee_credits is NOT NULL in DB (default 0), or increment may no-op.
                    // Optional: $subscription->refresh(); // if you suspect stale value
                    $subscription->increment('employee_credits', $additionalCredits);

                    $payment->update(['applied_at' => now()]);

                    Log::info('Employee credits applied via webhook', [
                        'branch_subscription_id' => $subscription->id,
                        'credits'                => $additionalCredits,
                        'payment_id'             => $payment->id,
                    ]);
                } else {
                    Log::warning('Credits not applied (missing subscription or 0 credits)', [
                        'payment_id'         => $payment->id,
                        'additional_credits' => $additionalCredits,
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Processed.']);
        }

        // Non-paid transitions
        $newStatus = in_array($status, $failedStatuses, true) ? 'failed' : $status;
        if ($payment->status !== $newStatus) {
            $payment->update(['status' => $newStatus]);
        }

        return response()->json(['success' => true, 'message' => 'Payment status updated.']);
    }


    /**
     * Webhook to update payment status and subscription status.
     */
    public function paymentStatus(Request $request)
    {
        Log::info('Received paymentStatus webhook', ['payload' => $request->all()]);

        // Accept both raw and nested payloads
        $payload = $request->input('payload');
        if (!$payload && is_array($request->all())) {
            $payload = $request->all();
        }

        if (
            !$payload ||
            !isset($payload['payment_request']['reference_number']) ||
            !isset($payload['status'])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payload format.',
            ], 400);
        }

        // Manually extract and map fields from payload
        $transactionReference = $payload['payment_request']['reference_number'];
        $status = $payload['status'];

        // Convert HitPay status to your internal payment status
        $paymentStatus = match ($status) {
            'succeeded' => 'paid',
            'pending' => 'pending',
            'failed' => 'failed',
            'refunded' => 'refunded',
            default => 'failed',
        };

        // Now run validation on transformed data
        $validator = Validator::make([
            'transaction_reference' => $transactionReference,
            'payment_status' => $paymentStatus,
        ], [
            'transaction_reference' => 'required|string',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        // Lookup and update payment and subscription
        $payment = Payment::where('transaction_reference', $transactionReference)->first();

        if (!$payment) {
            Log::warning('Payment not found', ['reference' => $transactionReference]);
            return response()->json([
                'success' => false,
                'message' => 'Payment not found.',
            ], 404);
        }

        $subscription = BranchSubscription::find($payment->branch_subscription_id);

        if (!$subscription) {
            Log::warning('Subscription not found', ['branch_subscription_id' => $payment->branch_subscription_id]);
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found.',
            ], 404);
        }

        $payment->status = $paymentStatus;
        $payment->paid_at = $paymentStatus === 'paid' ? now() : null;
        $payment->save();

        $subscription->payment_status = $paymentStatus;
        if ($paymentStatus === 'paid') {
            $subscription->status = 'active';

            // If trial_end exists and is in the future, start subscription after trial
            if ($subscription->trial_end && now()->lt($subscription->trial_end)) {
            $subscription->subscription_start = $subscription->trial_end;
            $subscription->subscription_end = (clone \Illuminate\Support\Carbon::parse($subscription->trial_end))->addDays(30);
            } else {
            $subscription->subscription_start = now();
            $subscription->subscription_end = now()->addDays(30);
            }
        } elseif (in_array($paymentStatus, ['failed', 'refunded'])) {
            $subscription->status = 'expired';
        }
        $subscription->save();

        Log::info('Payment and subscription updated', [
            'transaction_reference' => $transactionReference,
            'payment_status' => $paymentStatus,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment and subscription status updated.',
            'subscription' => $subscription,
            'payment' => $payment,
        ]);
    }
}