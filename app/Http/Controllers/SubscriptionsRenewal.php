<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BranchSubscription;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class SubscriptionsRenewal extends Controller
{
    //
    public function subscriptionRenewals(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_subscription_id' => 'required|exists:branch_subscriptions,id',
            'billing_period'         => 'nullable|in:monthly,annual',
            'total_employees'        => 'nullable|integer|min:1',
            'features'               => 'nullable|array',
            'features.*.addon_id'    => 'nullable|integer|exists:addons,id',
            'features.*.addon_key'   => 'nullable|string|exists:addons,addon_key',
            'features.*.start_date'  => 'nullable|date',
            'features.*.end_date'    => 'nullable|date|after_or_equal:features.*.start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $subscription = BranchSubscription::findOrFail($request->branch_subscription_id);

            [$planDetails, $amount, $subStart, $subEnd] = $this->calculatePlanDetails($request, $subscription);

            $hitpayData = $this->createHitpayPayment($subscription, $amount);

            $subscription->update([
                'plan_details'       => $planDetails,
                'amount_paid'        => $amount,
                'payment_status'     => 'pending',
                'subscription_start' => $subStart,
                'subscription_end'   => $subEnd,
                'status'             => 'active',
                'renewed_at'         => now(),
                'billing_period'     => $planDetails['billing_period'],
                'total_employee'     => $planDetails['total_employees'],
                'is_trial'           => false,
                'transaction_reference' => $hitpayData['reference'] ?? null,
            ]);

            $payment = Payment::create([
                'branch_subscription_id' => $subscription->id,
                'amount'                 => $amount,
                'currency'               => env('HITPAY_CURRENCY', 'PHP'),
                'status'                 => 'pending',
                'payment_gateway'        => 'hitpay',
                'transaction_reference'  => $hitpayData['reference'] ?? null,
                'gateway_response'       => $hitpayData ? json_encode($hitpayData) : null,
                'payment_method'         => 'hitpay',
                'payment_provider'       => $hitpayData['payment_provider']['code'] ?? null,
                'checkout_url'           => $hitpayData['url'] ?? null,
                'receipt_url'            => $hitpayData['receipt_url'] ?? null,
                'paid_at'                => null,
                'notes'                  => 'Payment pending for subscription renewal',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subscription renewed. Payment pending.',
                'subscription' => $subscription,
                'payment_checkout_url' => $hitpayData['url'] ?? null,
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            log::error('Error renewing subscription', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Error renewing subscription.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper to calculate plan details, amount, and subscription window.
     */
    private function calculatePlanDetails(Request $request, $subscription)
    {
        $billingPeriod   = $request->input('billing_period', $subscription->billing_period ?? 'monthly');
        $totalEmployees  = (int) $request->input('total_employees', $subscription->total_employee ?? 1);
        $pricePerEmployee = $subscription->plan_details['price_per_employee'] ?? 49.00;

        $featureInputs = collect($request->input('features', []))
            ->filter(fn($f) => !empty($f['addon_id']) || !empty($f['addon_key']))
            ->values();

        $addonIds  = $featureInputs->pluck('addon_id')->filter()->values()->all();
        $addonKeys = $featureInputs->pluck('addon_key')->filter()->values()->all();

        $addons = collect();
        if (!empty($addonIds) || !empty($addonKeys)) {
            $addons = DB::table('addons')
                ->where('is_active', true)
                ->where(function ($q) use ($addonIds, $addonKeys) {
                    if (!empty($addonIds)) {
                        $q->whereIn('id', $addonIds);
                    }
                    if (!empty($addonKeys)) {
                        if (!empty($addonIds)) {
                            $q->orWhereIn('addon_key', $addonKeys);
                        } else {
                            $q->whereIn('addon_key', $addonKeys);
                        }
                    }
                })
                ->get(['id', 'addon_key', 'name', 'price', 'type']);
        }

        $addonsPrice = $addons->sum(function ($a) use ($billingPeriod) {
            $base = (float) $a->price;
            return $billingPeriod === 'annual' ? ($base * 12) : $base;
        });

        $employeePrice = $totalEmployees * $pricePerEmployee;
        if ($billingPeriod === 'annual') {
            $employeePrice *= 12;
        }

        $subtotal = $employeePrice + $addonsPrice;
        $vat      = $subtotal * 0.12;
        $final    = $subtotal + $vat;

        $planDetails = [
            'billing_period'      => $billingPeriod,
            'total_employees'     => $totalEmployees,
            'price_per_employee'  => $pricePerEmployee,
            'employee_price'      => $employeePrice,
            'addons'              => $addons->map(fn($a) => [
                'id'    => $a->id,
                'key'   => $a->addon_key,
                'name'  => $a->name,
                'price' => (float) $a->price,
                'type'  => $a->type,
            ])->values()->all(),
            'addons_price'        => $addonsPrice,
            'vat'                 => $vat,
            'final_price'         => $final,
        ];

        $subStart = now();
        $subEnd = $billingPeriod === 'annual'
            ? (clone $subStart)->addYear()
            : (clone $subStart)->addDays(30);

        return [$planDetails, round($final, 2), $subStart, $subEnd];
    }

    /**
     * Helper to create Hitpay payment and return response data.
     */
    private function createHitpayPayment($subscription, $amount)
    {
        $reference    = 'renewal_' . now()->timestamp . '_' . $subscription->id;
        $buyerEmail   = optional($subscription->branch)->employmentDetail->first()->user->email ?? null;
        $buyerName    = optional($subscription->branch)->employmentDetail->first()->user->personalInformation->first_name ?? '';
        $buyerPhone   = optional($subscription->branch)->employmentDetail->first()->user->personalInformation->phone_number ?? null;
        $purpose      = 'Renew your subscription for Payroll Timora PH.';
        $redirectUrl  = env('HITPAY_REDIRECT_URL', config('app.url') . '/payment-success');
        $webhookUrl   = env('HITPAY_WEBHOOK_URL');

        $hitpayData = null;
        try {
            $client = new \GuzzleHttp\Client();
            $hitpayPayload = [
                'amount'           => $amount,
                'currency'         => env('HITPAY_CURRENCY', 'PHP'),
                'email'            => $buyerEmail,
                'name'             => $buyerName,
                'phone'            => $buyerPhone,
                'purpose'          => $purpose,
                'reference_number' => $reference,
                'redirect_url'     => $redirectUrl,
                'webhook'          => $webhookUrl,
                'send_email'       => true,
            ];

            $response = $client->request('POST', env('HITPAY_URL'), [
                'form_params' => $hitpayPayload,
                'headers'     => [
                    'X-BUSINESS-API-KEY' => env('HITPAY_API_KEY'),
                    'Content-Type'       => 'application/x-www-form-urlencoded',
                ],
            ]);

            $hitpayData = json_decode($response->getBody(), true);
            $hitpayData['reference'] = $reference;
        } catch (\Exception $e) {
            log::error('Payment creation failed (renewal)', ['exception' => $e]);
        }
        return $hitpayData;
    }
}
