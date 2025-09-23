<?php

namespace App\Http\Controllers\Tenant\Billing;

use App\Http\Controllers\Controller;
use App\Models\BranchSubscription;
use App\Models\EmploymentDetail;
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
        $user = $request->user();

        $employmentDetail = $user->employmentDetail;
        if (!$employmentDetail || !$employmentDetail->branch_id) {
            return response()->json(['message' => 'Branch not found for user.'], 404);
        }
        $branchId = $employmentDetail->branch_id;

        // Get first EmploymentDetail for the branch
        $firstEmploymentDetail = EmploymentDetail::where('branch_id', $branchId)->orderBy('id')->first();

        $billToEmail = null;
        $billToFullName = null;
        $billToAddress = null;

        if ($firstEmploymentDetail) {
            $firstUser = $firstEmploymentDetail->user;
            if ($firstUser) {
                $billToEmail = $firstUser->email;

                // Use correct relationship name: personalInformation
                $personalInfo = $firstUser->personalInformation;
                if ($personalInfo) {
                    // Use accessor if available, otherwise build manually
                    $billToFullName = trim(
                        $personalInfo->first_name . ' ' .
                        ($personalInfo->middle_name ? $personalInfo->middle_name . ' ' : '') .
                        $personalInfo->last_name .
                        ($personalInfo->suffix ? ' ' . $personalInfo->suffix : '')
                    );
                }
            }
        }

        // Get branch address
        $branch = \App\Models\Branch::find($branchId);
        if ($branch) {
            $billToAddress = $branch->location;
        }

        $subscriptions = BranchSubscription::with(['payments'])
            ->where('branch_id', $branchId)
            ->orderByDesc('subscription_start')
            ->get();

        $data = $subscriptions->map(function ($subscription) {
            return [
                'subscription_id'    => $subscription->id,
                'plan'               => $subscription->plan,
                'plan_details'       => $subscription->plan_details,
                'amount_paid'        => $subscription->amount_paid,
                'currency'           => $subscription->currency,
                'payment_status'     => $subscription->payment_status,
                'subscription_start' => $subscription->subscription_start,
                'subscription_end'   => $subscription->subscription_end,

                'payments' => $subscription->payments->map(function ($payment) {
                    // try to find invoice, may return null
                    $invoice = \App\Models\Invoice::where('invoice_number', $payment->transaction_reference)
                        ->with(['subscription', 'branch'])
                        ->first();

                    return [
                        'payment_id'            => $payment->id,
                        'amount'                => $payment->amount,
                        'currency'              => $payment->currency,
                        'status'                => $payment->status,
                        'payment_gateway'       => $payment->payment_gateway,
                        'transaction_reference' => $payment->transaction_reference,
                        'payment_method'        => $payment->payment_method,
                        'payment_provider'      => $payment->payment_provider,
                        'checkout_url'          => $payment->checkout_url,
                        'paid_at'               => $payment->paid_at,
                        'meta'                  => $payment->meta,
                        'applied_at'            => $payment->applied_at,

                        // ✅ attach invoice if found, otherwise null
                        'invoice' => $invoice ? [
                            'id'             => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                            'amount_due'     => $invoice->amount_due,
                            'amount_paid'    => $invoice->amount_paid,
                            'status'         => $invoice->status,
                            'issued_at'      => $invoice->issued_at,
                            'due_date'       => $invoice->due_date,
                            'period_start'   => $invoice->period_start,
                            'period_end'     => $invoice->period_end,
                            'subscription'   => $invoice->subscription,
                            'branch'         => $invoice->branch,
                        ] : null,
                    ];
                }),
            ];
        });

        return response()->json([
            'branch_id'        => $branchId,
            'subscriptions'    => $data,
            'bill_to_email'    => $billToEmail,
            'bill_to_full_name'=> $billToFullName,
            'bill_to_address'  => $billToAddress,
        ]);
    }
}
