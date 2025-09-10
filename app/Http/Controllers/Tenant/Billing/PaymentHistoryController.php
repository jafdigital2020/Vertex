<?php

namespace App\Http\Controllers\Tenant\Billing;

use App\Http\Controllers\Controller;
use App\Models\BranchSubscription;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentHistoryController extends Controller
{

    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function paymentIndex(Request $request)
    {
        // Get the authenticated user
        $authUser = $this->authUser();

        // Subscription
        return view('tenant.payments.payment', ['authUser' => $authUser]);
    }
    public function paymentHistory(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Get the user's branch_id from employment details
        $employmentDetail = $user->employmentDetail;
        if (!$employmentDetail || !$employmentDetail->branch_id) {
            return response()->json(['message' => 'Branch not found for user.'], 404);
        }
        $branchId = $employmentDetail->branch_id;

        // Get branch subscriptions
        $subscriptions = BranchSubscription::with(['payments'])
            ->where('branch_id', $branchId)
            ->orderByDesc('subscription_start')
            ->get();

        // Format the response
        $data = $subscriptions->map(function ($subscription) {
            return [
                'subscription_id' => $subscription->id,
                'plan' => $subscription->plan,
                'plan_details' => $subscription->plan_details,
                'amount_paid' => $subscription->amount_paid,
                'currency' => $subscription->currency,
                'payment_status' => $subscription->payment_status,
                'subscription_start' => $subscription->subscription_start,
                'subscription_end' => $subscription->subscription_end,
                'payments' => $subscription->payments->map(function ($payment) {
                    return [
                        'payment_id' => $payment->id,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'status' => $payment->status,
                        'payment_gateway' => $payment->payment_gateway,
                        'transaction_reference' => $payment->transaction_reference,
                        'payment_method' => $payment->payment_method,
                        'payment_provider' => $payment->payment_provider,
                        'checkout_url' => $payment->checkout_url,
                        'paid_at' => $payment->paid_at,
                        'meta' => $payment->meta,
                        'applied_at' => $payment->applied_at,
                    ];
                }),
            ];
        });

        return response()->json([
            'branch_id' => $branchId,
            'subscriptions' => $data,
        ]);
    }
}
