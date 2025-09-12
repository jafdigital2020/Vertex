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

        // Step 1: Validate the HMAC signature
        $receivedHmac = $payload['hmac'] ?? null;

        if (!$receivedHmac || !$this->isValidHitpaySignature($payload, $receivedHmac)) {
            Log::warning('Hitpay webhook: Invalid HMAC signature', ['payload' => $payload]);
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        // Step 2: Extract important fields
        $reference = $payload['payment_request']['reference_number'] ?? null;
        $status    = strtolower($payload['status'] ?? '');

        if (!$reference || !$status) {
            return response()->json(['success' => false, 'message' => 'Invalid webhook data'], 400);
        }

        // Step 3: Find the payment
        $payment = Payment::where('transaction_reference', $reference)->first();

        if (!$payment) {
            Log::warning('Hitpay webhook: Payment not found', ['reference' => $reference]);
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        // Step 4: Check if already processed
        if (method_exists($payment, 'isPaid') && $payment->isPaid()) {
            return response()->json(['success' => true, 'message' => 'Already processed.'], 200);
        }

        // Step 5: Handle payment statuses
        if (in_array($status, ['succeeded', 'completed', 'paid'])) {
            $payment->update([
                'status'           => 'paid',
                'paid_at'          => now(),
                'gateway_response' => $payload,
            ]);

            // Apply credits
            if (method_exists($payment, 'isCreditsTopup') && $payment->isCreditsTopup() && method_exists($payment, 'alreadyApplied') && !$payment->alreadyApplied()) {
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

        // Handle failed or cancelled payment
        $payment->update([
            'status'           => $status,
            'gateway_response' => $payload,
        ]);

        return response()->json(['success' => true, 'message' => 'Payment status updated.']);
    }

    private function isValidHitpaySignature(array $payload, string $receivedHmac): bool
    {
        $secret = config('services.hitpay.webhook_secret'); // Store your HitPay secret in config/services.php

        // Remove 'hmac' key
        unset($payload['hmac']);

        // Flatten nested array (like payment_request[reference_number]) into key-value strings
        $flatPayload = [];

        array_walk_recursive($payload, function ($value, $key) use (&$flatPayload) {
            $flatPayload[$key] = "{$key}{$value}";
        });

        ksort($flatPayload); // Sort by keys alphabetically
        $concatenated = implode('', array_values($flatPayload));

        $calculatedHmac = hash_hmac('sha256', $concatenated, $secret);

        return hash_equals($calculatedHmac, $receivedHmac);
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