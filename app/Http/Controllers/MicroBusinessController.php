<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\BranchAddon;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User;
use App\Models\Role;
use App\Models\UserPermission;
use App\Models\EmploymentPersonalInformation;
use App\Models\EmploymentDetail;
use App\Models\Tenant;
use App\Models\Payment;
use App\Models\BranchSubscription;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MicroBusinessController extends Controller
{
    public function createMicroBusinessIndex()
    {
        return view('affiliate.microbusiness.register');
    }

    public function verifyReferralCode(Request $request)
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'referral_code' => 'required|string|exists:tenants,tenant_code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('referral_code') ?? 'Invalid referral code.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // If valid, return success message
        return response()->json([
            'success' => true,
            'message' => 'Referral code is valid.',
        ]);
    }

    public function registerBranch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'referral_code'   => 'required|string|exists:tenants,tenant_code',

            // User
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'middle_name'     => 'nullable|string|max:255',
            'suffix'          => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'username'        => 'required|string|max:255|unique:users,username',
            'email'           => 'required|email|max:255|unique:users,email',
            'password'        => 'required|string|min:6|same:confirm_password',
            'confirm_password' => 'required|string|min:6',
            'role_id'         => 'required|integer|exists:role,id',
            'phone_number'    => 'nullable|string|max:255',

            // Branch
            'branch_name'     => 'required|string|max:255',
            'branch_location' => 'required|string|max:500',

            // Subscription/new payload
            'total_employees' => 'required|integer|min:1',
            'billing_period'  => 'required|string|in:monthly,annual',
            'is_trial'        => 'sometimes|boolean',
            'plan_slug'       => 'nullable|string',

            // Add-ons (features)
            'features'                  => 'nullable|array',
            'features.*.addon_id'       => 'nullable|integer|exists:addons,id',
            'features.*.addon_key'      => 'nullable|string|exists:addons,addon_key',
            'features.*.start_date'     => 'nullable|date',
            'features.*.end_date'       => 'nullable|date|after_or_equal:features.*.start_date',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return response()->json(['message' => $firstError, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // ===== Tenant =====
            $tenant = Tenant::where('tenant_code', $request->input('referral_code'))->first();
            if (!$tenant) {
                return response()->json(['message' => 'No matching tenant found for the provided referral code.'], 404);
            }

            // ===== Branch =====
            $branch = Branch::create([
                'tenant_id' => $tenant->id,
                'name'      => $request->branch_name,
                'location'  => $request->branch_location,
            ]);

            // ===== User =====
            $user = new User();
            $user->username  = $request->username;
            $user->tenant_id = $tenant->id;
            $user->email     = $request->email;
            $user->password  = bcrypt($request->password);
            $user->save();

            // ===== Role -> UserPermission =====
            $role = Role::find($request->role_id);
            $userPermission = new UserPermission();
            $userPermission->user_id = $user->id;
            $userPermission->role_id = $role->id;
            $userPermission->status  = 1;
            $userPermission->save();

            // ===== Profile picture (optional) =====
            $profileImagePath = null;
            if ($request->hasFile('profile_picture')) {
                $image    = $request->file('profile_picture');
                $filename = time() . '_' . $image->getClientOriginalName();
                $path     = storage_path('app/public/profile_images');
                if (!file_exists($path)) mkdir($path, 0755, true);

                $savePath = $path . '/' . $filename;
                $manager  = new ImageManager(new Driver());
                $manager->read($image->getRealPath())->resize(300, 300)->save($savePath);
                $profileImagePath = 'profile_images/' . $filename;
            }

            // ===== Employment info =====
            $epi = EmploymentPersonalInformation::create([
                'user_id'         => $user->id,
                'first_name'      => $request->first_name,
                'last_name'       => $request->last_name,
                'middle_name'     => $request->middle_name,
                'suffix'          => $request->suffix,
                'profile_picture' => $profileImagePath,
                'phone_number'    => $request->phone_number,
                'branch_id'       => $branch->id,
            ]);

            EmploymentDetail::create([
                'user_id'    => $user->id,
                'branch_id'  => $branch->id,
                'employee_id' => $epi->id,
                'status'     => 1,
            ]);

            // ===== Pricing calc (secure) =====
            $totalEmployees   = (int) $request->input('total_employees');
            $pricePerEmployee = 49.00;

            // Resolve selected features
            $featureInputs = collect($request->input('features', []))
                ->filter(fn($f) => !empty($f['addon_id']) || !empty($f['addon_key']))
                ->values();

            $addonIds  = $featureInputs->pluck('addon_id')->filter()->values()->all();
            $addonKeys = $featureInputs->pluck('addon_key')->filter()->values()->all();

            // Fetch ONLY the selected add-ons
            $addons = collect();
            if (!empty($addonIds) || !empty($addonKeys)) {
                $addons = DB::table('addons')
                    ->where('is_active', true)
                    ->where(function ($q) use ($addonIds, $addonKeys) {
                        if (!empty($addonIds)) {
                            $q->whereIn('id', $addonIds);
                        }
                        if (!empty($addonKeys)) {
                            // If both exist, OR; if only keys exist, just whereIn
                            if (!empty($addonIds)) {
                                $q->orWhereIn('addon_key', $addonKeys);
                            } else {
                                $q->whereIn('addon_key', $addonKeys);
                            }
                        }
                    })
                    ->get(['id', 'addon_key', 'name', 'price', 'type']);
            }

            // Sum add-on price (monthly by default; annual x12)
            $billingPeriod = $request->input('billing_period', 'monthly');
            $addonsPrice = $addons->sum(function ($a) use ($billingPeriod) {
                $base = (float) $a->price;
                return $billingPeriod === 'annual' ? ($base * 12) : $base;
            });

            // Employees price
            $employeePrice = $totalEmployees * $pricePerEmployee;
            if ($billingPeriod === 'annual') {
                $employeePrice *= 12;
            }

            $subtotal = $employeePrice + $addonsPrice;
            $vat      = $subtotal * 0.12;
            $final    = $subtotal + $vat;

            // Plan details (array; let $casts handle JSON)
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

            // ===== Trial & subscription windows =====
            $isTrial = (bool) $request->boolean('is_trial', true);
            if ($isTrial) {
                $trialStart = now();
                $trialEnd   = now()->addDays(7);
                $subStart   = $trialEnd;
            } else {
                $trialStart = null;
                $trialEnd   = null;
                $subStart   = now();
            }

            $subEnd = $billingPeriod === 'annual'
                ? (clone $subStart)->addYear()
                : (clone $subStart)->addDays(30);

            // ===== Payment (HitPay) =====
            $planSlug     = $request->input('plan_slug', 'starter');
            $amount       = 1;
            $reference    = 'checkout_' . now()->timestamp;
            $buyerEmail   = $request->input('email');
            $buyerName    = trim($request->input('first_name') . ' ' . $request->input('last_name'));
            $buyerPhone   = $request->input('phone_number');
            $purpose      = 'Get started with your subscription for Payroll Timora PH today.';
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
            } catch (\Exception $e) {
                Log::error('Payment creation failed', ['exception' => $e]);
            }

            // ===== BranchSubscription (NEW fields populated) =====
            $branchSubscription = BranchSubscription::create([
                'branch_id'             => $branch->id,
                'plan'                  => $planSlug,
                'plan_details'          => $planDetails,
                'amount_paid'           => $amount,
                'currency'              => env('HITPAY_CURRENCY', 'PHP'),
                'payment_status'        => 'pending',
                'subscription_start'    => $subStart,
                'subscription_end'      => $subEnd,
                'trial_start'           => $trialStart,
                'trial_end'             => $trialEnd,
                'status'                => 'active',
                'payment_gateway'       => 'hitpay',
                'transaction_reference' => $reference,
                'notes'                 => null,
                'mobile_number'         => $buyerPhone,

                // NEW payload
                'total_employee'        => $totalEmployees,
                'tenant_id'             => $tenant->id,
                'billing_period'        => $billingPeriod,
                'is_trial'              => $isTrial,
            ]);

            // ===== Payment record =====
            Payment::create([
                'branch_subscription_id' => $branchSubscription->id,
                'amount'                 => $amount,
                'currency'               => env('HITPAY_CURRENCY', 'PHP'),
                'status'                 => 'pending',
                'payment_gateway'        => 'hitpay',
                'transaction_reference'  => $reference,
                'gateway_response'       => $hitpayData ? json_encode($hitpayData) : null,
                'payment_method'         => 'hitpay',
                'payment_provider'       => $hitpayData['payment_provider']['code'] ?? null,
                'checkout_url'           => $hitpayData['url'] ?? null,
                'receipt_url'            => $hitpayData['receipt_url'] ?? null,
                'paid_at'                => null,
                'notes'                  => 'Payment pending for subscription',
            ]);

            // ===== Create BranchAddon rows (ONLY selected) =====
            if ($addons->count() > 0) {
                $datesById  = $featureInputs->filter(fn($f) => isset($f['addon_id']))->keyBy('addon_id');
                $datesByKey = $featureInputs->filter(fn($f) => isset($f['addon_key']))->keyBy('addon_key');

                foreach ($addons as $a) {
                    $start = null;
                    $end = null;

                    if ($datesById->has($a->id)) {
                        $start = $datesById[$a->id]['start_date'] ?? null;
                        $end   = $datesById[$a->id]['end_date'] ?? null;
                    } elseif ($datesByKey->has($a->addon_key)) {
                        $start = $datesByKey[$a->addon_key]['start_date'] ?? null;
                        $end   = $datesByKey[$a->addon_key]['end_date'] ?? null;
                    }

                    BranchAddon::firstOrCreate(
                        ['branch_id' => $branch->id, 'addon_id' => $a->id],
                        [
                            'active'     => true,
                            'start_date' => $start ?: now(),
                            'end_date'   => $end ?: null,
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'status'               => 'success',
                'message'              => 'Branch, user, subscription, payment, and add-ons created successfully.',
                'branch'               => $branch,
                'subscription'         => $branchSubscription,
                'payment_checkout_url' => $hitpayData['url'] ?? null,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating branch, user, subscription, and payment', ['exception' => $e]);

            return response()->json([
                'message' => 'Error creating branch, user, subscription, and payment.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function branchSubscriptions()
    {
        $subscriptions = BranchSubscription::with([
            'branch:id,name,location',
            'branch.employmentDetail.user.personalInformation'
        ])->get();

        $formatted = $subscriptions->map(function ($subscription) {
            // Decode plan_details JSON
            $planDetails = [];
            if ($subscription->plan_details) {
                $planDetails = json_decode($subscription->plan_details, true);
            }

            // Parse selected_addons to flatten as array of ['id' => ..., 'name' => ...]
            $addons = [];
            if (!empty($planDetails['selected_addons'])) {
                foreach ($planDetails['selected_addons'] as $idx => $addon) {
                    // If addon is a JSON string, decode it
                    if (is_string($addon) && $decoded = json_decode($addon, true)) {
                        $addons[] = [
                            'id' => $idx + 1,
                            'name' => $decoded['label'] ?? $decoded['name'] ?? $addon,
                        ];
                    } elseif (is_array($addon)) {
                        $addons[] = [
                            'id' => $idx + 1,
                            'name' => $addon['label'] ?? $addon['name'] ?? null,
                        ];
                    } else {
                        // Fallback: treat as string name
                        $addons[] = [
                            'id' => $idx + 1,
                            'name' => $addon,
                        ];
                    }
                }
            }

            // Additional employees (if present)
            $additionalEmployees = 0;
            if (!empty($planDetails['total_employees'])) {
                $additionalEmployees = (int)$planDetails['total_employees'];
            }

            return [
                'plan' => $subscription->plan,
                'amount_paid' => $subscription->amount_paid,
                'subscription_start' => $subscription->subscription_start,
                'subscription_end' => $subscription->subscription_end,
                'status' => $subscription->status,
                'addons' => $addons,
                'additional_employees' => $additionalEmployees,
                'branch' => [
                    'name' => $subscription->branch->name,
                    'location' => $subscription->branch->location,
                    'users' => $subscription->branch->employmentDetail->map(function ($employment) {
                        $user = $employment->user;
                        $info = $user->personalInformation;

                        return [
                            'id' => $user->id,
                            'first_name' => $info->first_name ?? null,
                            'last_name' => $info->last_name ?? null,
                            'email' => $user->email,
                            'phone' => $info->phone_number ?? null,
                        ];
                    }),
                ],
            ];
        });

        return response()->json([
            'subscriptions' => $formatted,
        ]);
    }

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

            // Use new values if provided, else fallback to existing
            $billingPeriod   = $request->input('billing_period', $subscription->billing_period ?? 'monthly');
            $totalEmployees  = (int) $request->input('total_employees', $subscription->total_employee ?? 1);
            $pricePerEmployee = $subscription->plan_details['price_per_employee'] ?? 49.00;

            // Resolve selected features
            $featureInputs = collect($request->input('features', []))
                ->filter(fn($f) => !empty($f['addon_id']) || !empty($f['addon_key']))
                ->values();

            $addonIds  = $featureInputs->pluck('addon_id')->filter()->values()->all();
            $addonKeys = $featureInputs->pluck('addon_key')->filter()->values()->all();

            // Fetch ONLY the selected add-ons
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

            // Sum add-on price (monthly by default; annual x12)
            $addonsPrice = $addons->sum(function ($a) use ($billingPeriod) {
                $base = (float) $a->price;
                return $billingPeriod === 'annual' ? ($base * 12) : $base;
            });

            // Employees price
            $employeePrice = $totalEmployees * $pricePerEmployee;
            if ($billingPeriod === 'annual') {
                $employeePrice *= 12;
            }

            $subtotal = $employeePrice + $addonsPrice;
            $vat      = $subtotal * 0.12;
            $final    = $subtotal + $vat;

            // Plan details (array; let $casts handle JSON)
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

            // Subscription window
            $subStart = now();
            $subEnd = $billingPeriod === 'annual'
                ? (clone $subStart)->addYear()
                : (clone $subStart)->addDays(30);

            // Payment (HitPay)
            $planSlug     = $subscription->plan ?? 'starter';
            $amount       = round($final, 2);
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
            } catch (\Exception $e) {
                Log::error('Payment creation failed (renewal)', ['exception' => $e]);
            }

            // Update subscription for renewal (do not overwrite old dates, just update status and details)
            $subscription->update([
                'plan_details'       => $planDetails,
                'amount_paid'        => $amount,
                'payment_status'     => 'pending',
                'subscription_start' => $subStart,
                'subscription_end'   => $subEnd,
                'status'             => 'active',
                'renewed_at'         => now(),
                'billing_period'     => $billingPeriod,
                'total_employee'     => $totalEmployees,
                'is_trial'           => false,
                'transaction_reference' => $reference,
            ]);

            // Create new Payment record
            $payment = Payment::create([
                'branch_subscription_id' => $subscription->id,
                'amount'                 => $amount,
                'currency'               => env('HITPAY_CURRENCY', 'PHP'),
                'status'                 => 'pending',
                'payment_gateway'        => 'hitpay',
                'transaction_reference'  => $reference,
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
            Log::error('Error renewing subscription', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Error renewing subscription.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    
    public function addOnFeatures()
    {
        // Use the Addon model instead of DB::table for Eloquent features and casting
        $addons = Addon::where('is_active', true)
            ->get(['id', 'addon_key', 'name', 'price', 'type', 'description']);

        return response()->json([
            'addons' => $addons,
        ]);
    }

    /**
     * Webhook to update payment status and subscription status.
     */
    public function paymentStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        $transactionReference = $request->input('transaction_reference');
        $paymentStatus = $request->input('payment_status');

        // Find the payment by transaction_reference
        $payment = Payment::where('transaction_reference', $transactionReference)->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found.',
            ], 404);
        }

        // Find the branch subscription using the payment's branch_subscription_id
        $subscription = BranchSubscription::find($payment->branch_subscription_id);

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found.',
            ], 404);
        }

        // Update payment status and paid_at if applicable
        $payment->status = $paymentStatus;
        if ($paymentStatus === 'paid') {
            $payment->paid_at = now();
        } else {
            $payment->paid_at = null;
        }
        $payment->save();

        // Update subscription payment_status and status accordingly
        $subscription->payment_status = $paymentStatus;
        if ($paymentStatus === 'paid') {
            $subscription->status = 'active';
        } elseif (in_array($paymentStatus, ['failed', 'refunded'])) {
            $subscription->status = 'expired';
        }
        $subscription->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment and subscription status updated.',
            'subscription' => $subscription,
            'payment' => $payment,
        ]);
    }
}