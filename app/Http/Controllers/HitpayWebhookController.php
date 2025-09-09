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

        // Hitpay sends the main data as the root of the payload
        // The reference_number is inside $payload['payment_request']['reference_number']
        $reference = $payload['payment_request']['reference_number'] ?? null;
        $status    = strtolower($payload['status'] ?? '');

        if (!$reference || !$status) {
            return response()->json(['success' => false, 'message' => 'Invalid webhook data'], 400);
        }

        // Find the related payment
        $payment = Payment::where('transaction_reference', $reference)->first();

        if (!$payment) {
            Log::warning('Hitpay webhook: Payment not found', ['reference' => $reference]);
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        // If already marked as paid, skip
        if (method_exists($payment, 'isPaid') && $payment->isPaid()) {
            return response()->json(['success' => true, 'message' => 'Already processed.'], 200);
        }

        // Handle successful payment
        if (in_array($status, ['succeeded', 'completed', 'paid'])) {
            $payment->update([
                'status'           => 'paid',
                'paid_at'          => now(),
                'gateway_response' => $payload,
            ]);

            // If this was a credit top-up, apply credits once
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

        // Payment failed/cancelled
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

        $transactionReference = $request->input('payload.payment_request.reference_number');
        $status = $request->input('payload.status');

        // Convert external status to internal payment_status
        $mappedStatus = match ($status) {
            'succeeded' => 'paid',
            'failed' => 'failed',
            'refunded' => 'refunded',
            default => 'pending',
        };

        $validator = Validator::make([
            'transaction_reference' => $transactionReference,
            'payment_status' => $mappedStatus,
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

        // Continue as usual
        $payment = Payment::where('transaction_reference', $transactionReference)->first();
        if (!$payment) {
            Log::warning('paymentStatus webhook: Payment not found', ['reference' => $transactionReference]);
            return response()->json([
                'success' => false,
                'message' => 'Payment not found.',
            ], 404);
        }

        $subscription = BranchSubscription::find($payment->branch_subscription_id);
        if (!$subscription) {
            Log::warning('paymentStatus webhook: Subscription not found', ['branch_subscription_id' => $payment->branch_subscription_id]);
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found.',
            ], 404);
        }

        $payment->status = $mappedStatus;
        $payment->paid_at = $mappedStatus === 'paid' ? now() : null;
        $payment->save();

        $subscription->payment_status = $mappedStatus;
        if ($mappedStatus === 'paid') {
            $subscription->status = 'active';
        } elseif (in_array($mappedStatus, ['failed', 'refunded'])) {
            $subscription->status = 'expired';
        }
        $subscription->save();

        Log::info('Payment and subscription status updated', [
            'subscription_id' => $subscription->id,
            'payment_id' => $payment->id,
            'payment_status' => $mappedStatus,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment and subscription status updated.',
            'subscription' => $subscription,
            'payment' => $payment,
        ]);
    }
}