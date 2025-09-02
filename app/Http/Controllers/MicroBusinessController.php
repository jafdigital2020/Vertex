<?php

namespace App\Http\Controllers;

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
        'referral_code' => 'required|string|exists:tenants,tenant_code',
        'first_name' => 'required|string|max:255',
        'last_name'  => 'required|string|max:255',
        'middle_name'=> 'nullable|string|max:255',
        'suffix'     => 'nullable|string|max:255',
        'profile_picture' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',

        'username' => 'required|string|max:255|unique:users,username',
        'email'    => 'required|email|max:255|unique:users,email',
        'password' => 'required|string|min:6|same:confirm_password',
        'confirm_password' => 'required|string|min:6',

        'role_id'  => 'required|integer|exists:role,id',
        'phone_number' => 'nullable|string|max:255',

        // Branch fields
        'branch_name'     => 'required|string|max:255',
        'branch_location' => 'required|string|max:500',
    ]);

    if ($validator->fails()) {
        $firstError = $validator->errors()->first();
        return response()->json(['message' => $firstError, 'errors' => $validator->errors()], 422);
    }

    DB::beginTransaction();
    try {
        // Check for referral code validity
        $tenant = Tenant::where('tenant_code', $request->input('referral_code'))->first();
        if (!$tenant) {
            return response()->json(['message' => 'No matching tenant found for the provided referral code.'], 404);
        }

        // Step 1: Create Branch
        $branch = Branch::create([
            'tenant_id' => $tenant->id,
            'name' => $request->branch_name,
            'location' => $request->branch_location,
        ]);

        // Step 2: Create User
        $user = new User();
        $user->username = $request->username;
        $user->tenant_id = $tenant->id;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        // Step 3: Assign Role -> UserPermission
        $role = Role::find($request->role_id);
        $userPermission = new UserPermission();
        $userPermission->user_id = $user->id;
        $userPermission->role_id = $role->id;
        $userPermission->status = 1;
        $userPermission->save();

        // Step 4: Handle Profile Picture Upload (optional)
        $profileImagePath = null;
        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = storage_path('app/public/profile_images');
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $savePath = $path . '/' . $filename;
            $manager = new ImageManager(new Driver());
            $manager->read($image->getRealPath())->resize(300, 300)->save($savePath);
            $profileImagePath = 'profile_images/' . $filename;
        }

        // Step 5: Save Employment Personal Info
        $employmentPersonalInfo = EmploymentPersonalInformation::create([
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'suffix' => $request->suffix,
            'profile_picture' => $profileImagePath,
            'phone_number' => $request->phone_number,
            'branch_id' => $branch->id,
        ]);

        // Step 6: Save Employment Detail
        EmploymentDetail::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'employee_id' => $employmentPersonalInfo->id,
            'status' => 1,
        ]);

        // Step 7: Plan Details (Calculate Pricing)
        $totalEmployees = $request->input('total_employees', 1); // Default to 1 if not provided
        $pricePerEmployee = 49.00; // Price per employee
        $addonsPrice = 0.00;

        // Calculate employee price
        $employeePrice = $totalEmployees * $pricePerEmployee;

        // Calculate add-ons price (from selected features)
        $selectedAddons = $request->input('features', []);
        $addonsPrice = 0;
        foreach ($selectedAddons as $addon) {
            $addonPrice = (float) $addon['price']; // Assuming 'price' is in the addon data
            $addonsPrice += $addonPrice;
        }

        // Calculate VAT (12%)
        $subtotal = $employeePrice + $addonsPrice;
        $vat = $subtotal * 0.12;

        // Final price including VAT
        $finalPrice = $subtotal + $vat;

        // Prepare plan details array
        $planDetails = [
            'total_employees' => $totalEmployees,
            'employee_price' => $employeePrice,
            'selected_addons' => $selectedAddons,
            'addons_price' => $addonsPrice,
            'vat' => $vat,
            'final_price' => $finalPrice,
        ];

        // Step 8: Payment Integration (create payment request)
        try {
            $planSlug = $request->input('plan_slug', 'starter');
            $amount = $finalPrice;
            $reference = 'checkout_' . now()->timestamp;

            $buyerEmail = $request->input('email');
            $buyerName = trim($request->input('first_name') . ' ' . $request->input('last_name'));
            $purpose = 'Get started with your subscription for Payroll Timora PH today.';
            $redirectUrl = env('HITPAY_REDIRECT_URL', config('app.url') . '/payment-success');
            $webhookUrl = env('HITPAY_WEBHOOK_URL');
            $buyerPhone = $request->input('phone_number');

            $client = new \GuzzleHttp\Client();
            $hitpayPayload = [
                'amount' => $amount,
                'currency' => env('HITPAY_CURRENCY', 'PHP'),
                'email' => $buyerEmail,
                'name' => $buyerName,
                'phone' => $buyerPhone,
                'purpose' => $purpose,
                'reference_number' => $reference,
                'redirect_url' => $redirectUrl,
                'webhook' => $webhookUrl,
                'send_email' => true,
            ];

            $response = $client->request('POST', env('HITPAY_URL'), [
                'form_params' => $hitpayPayload,
                'headers' => [
                    'X-BUSINESS-API-KEY' => env('HITPAY_API_KEY'),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $hitpayData = json_decode($response->getBody(), true);

            // Step 9: Create Branch Subscription and Payment
            $branchSubscription = BranchSubscription::create([
                'branch_id' => $branch->id,
                'plan' => $planSlug,
                'plan_details' => json_encode($planDetails),
                'amount_paid' => $amount,
                'currency' => env('HITPAY_CURRENCY', 'PHP'),
                'payment_status' => 'pending',
                'subscription_start' => now()->addDays(7),
                'subscription_end' => now()->addDays(37),
                'trial_start' => now(),
                'trial_end' => now()->addDays(7),
                'status' => 'active',
                'payment_gateway' => 'hitpay',
                'transaction_reference' => $reference,
                'raw_response' => json_encode($hitpayData),
                'mobile_number' => $buyerPhone,
            ]);

            // Create Payment Record
            $payment = Payment::create([
                'branch_subscription_id' => $branchSubscription->id,
                'amount' => $amount,
                'currency' => env('HITPAY_CURRENCY', 'PHP'),
                'status' => 'pending',
                'payment_gateway' => 'hitpay',
                'transaction_reference' => $reference,
                'gateway_response' => json_encode($hitpayData),
                'payment_method' => 'hitpay',
                'payment_provider' => $hitpayData['payment_provider']['code'] ?? null,
                'checkout_url' => $hitpayData['url'] ?? null,
                'receipt_url' => $hitpayData['receipt_url'] ?? null,
                'paid_at' => null,
                'notes' => 'Payment pending for subscription',
            ]);
        } catch (\Exception $e) {
            Log::error('Payment creation failed', ['exception' => $e]);
        }

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Branch, user, subscription, and payment created successfully.',
            'branch' => $branch,
            'payment_checkout_url' => $hitpayData['url'] ?? null,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error creating branch, user, subscription, and payment', ['exception' => $e]);
        return response()->json([
            'message' => 'Error creating branch, user, subscription, and payment.',
            'error' => $e->getMessage(),
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