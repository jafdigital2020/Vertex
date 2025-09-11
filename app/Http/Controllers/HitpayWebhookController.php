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

        // HitPay includes the signature in a header or field (depends on their integration).
        // Let's assume it's in the "hmac" header.
        $receivedHmac = $request->header('hmac-signature') ?? $payload['hmac'] ?? null;

        if (!$receivedHmac) {
            Log::warning('Hitpay webhook: Missing HMAC signature', ['payload' => $payload]);
            return response()->json(['success' => false, 'message' => 'Missing HMAC'], 400);
        }

        // Compute our own HMAC
        $computedHmac = hash_hmac('sha256', json_encode($payload), env('HITPAY_SALT_CREDITS'));

        if (!hash_equals($computedHmac, $receivedHmac)) {
            Log::warning('Hitpay webhook: Invalid signature', [
                'expected' => $computedHmac,
                'received' => $receivedHmac,
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        Log::info('Received valid Hitpay webhook', ['payload' => $payload]);

        // The rest of your existing logic
        $reference = $payload['payment_request']['reference_number'] ?? null;
        $status    = strtolower($payload['status'] ?? '');

        if (!$reference || !$status) {
            return response()->json(['success' => false, 'message' => 'Invalid webhook data'], 400);
        }

        $payment = Payment::where('transaction_reference', $reference)->first();

        if (!$payment) {
            Log::warning('Hitpay webhook: Payment not found', ['reference' => $reference]);
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        if (method_exists($payment, 'isPaid') && $payment->isPaid()) {
            return response()->json(['success' => true, 'message' => 'Already processed.'], 200);
        }

        if (in_array($status, ['succeeded', 'completed', 'paid'])) {
            $payment->update([
                'status'           => 'paid',
                'paid_at'          => now(),
                'gateway_response' => $payload,
            ]);

            if (
                method_exists($payment, 'isCreditsTopup')
                && $payment->isCreditsTopup()
                && method_exists($payment, 'alreadyApplied')
                && !$payment->alreadyApplied()
            ) {
                $subscription = $payment->subscription;
                $additionalCredits = $payment->meta['additional_credits'] ?? 0;

                if ($subscription && $additionalCredits > 0) {
                    $subscription->increment('employee_credits', $additionalCredits);
                    $payment->update(['applied_at' => now()]);

                    Log::info('Employee credits applied via webhook', [
                        'branch_subscription_id' => $subscription->id,
                        'credits' => $additionalCredits,
                        'payment_id' => $payment->id,
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Credits applied.']);
        }

        $payment->update([
            'status'           => $status,
            'gateway_response' => $payload,
        ]);

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

        // HMAC verification
        $receivedHmac = $request->header('hmac-signature') ?? $payload['hmac'] ?? null;
        if (!$receivedHmac) {
            Log::warning('Hitpay subscription webhook: Missing HMAC signature', ['payload' => $payload]);
            return response()->json(['success' => false, 'message' => 'Missing HMAC'], 400);
        }
        $computedHmac = hash_hmac('sha256', json_encode($payload), env('HITPAY_SECRET_SALT_SUBSCRIPTION'));
        if (!hash_equals($computedHmac, $receivedHmac)) {
            Log::warning('Hitpay subscription webhook: Invalid signature', [
                'expected' => $computedHmac,
                'received' => $receivedHmac,
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
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
            'succeeded', 'completed', 'paid' => 'paid',
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
        $payment->gateway_response = $payload;
        $payment->save();

        $subscription->payment_status = $paymentStatus;
        if ($paymentStatus === 'paid') {
            $subscription->status = 'active';
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