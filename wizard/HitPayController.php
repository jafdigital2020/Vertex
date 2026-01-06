<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Addon;
use App\Models\BiometricDevice;
use App\Models\Company;
use App\Models\CompanySystem;
use App\Models\Payment;
use App\Models\PaymentIntent;
use App\Models\Plan;
use App\Models\ProvisioningStatus;
use App\Models\Subscription;
use App\Models\System;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use App\Mail\PaymentProcessed;
use App\Mail\AffiliateWelcome;
use App\Mail\WelcomeAffiliate;
use App\Mail\NewSubscriptionNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;


class HitPayController extends Controller
{
    /**
     * Create HitPay Payment Request and save our intent record.
     */
      public function createPaymentRequest(Request $request)
    {
        DB::beginTransaction();

        try {
            $systemSlug = $request->input('system'); // e.g., "payroll"
            $planSlug   = $request->input('plan');

            $system = System::where('slug', $systemSlug)->first();

            if (!$system) {
                return response()->json(['success' => false, 'message' => 'Invalid system.'], 400);
            }

            $systemId = $system->id;

            // Look up the plan for this system by matching the system_id and plan slug
            $plan = Plan::where('system_id', $systemId)
                ->where('slug', $planSlug)
                ->first();

            if (!$plan) {
                return response()->json(['success' => false, 'message' => 'Invalid plan for the selected system.'], 400);
            }

            $planId = $plan->id;

            // -------------------- Enhanced Pricing Calculation --------------------
            // Get pricing from database with fallbacks to match frontend
            $basePrice = $plan->price_monthly ?? $plan->price ?? 0;
            $vatRate = $plan->vat_rate ?? 0.12;
            
            // Fallback to hardcoded values for fields not in database yet
            $planDefaults = [
                'free' => ['included_employees' => 2, 'max_employees' => 2],
                'starter' => ['included_employees' => 1, 'max_employees' => 20],
                'core' => ['included_employees' => 21, 'max_employees' => 100],
                'pro' => ['included_employees' => 101, 'max_employees' => 200],
                'elite' => ['included_employees' => 201, 'max_employees' => 500],
            ];
            
            $includedEmployees = $planDefaults[$planSlug]['included_employees'] ?? 0;
            // Set rates based on plan - Free plan has no extra costs
            $extraUserRate = ($planSlug === 'free') ? 0 : 49;
            $mobileAppRate = ($planSlug === 'free') ? 0 : 49;
            
            // Implementation fee based on plan
            $implementationFees = [
                'free' => 0,
                'starter' => 4999,
                'core' => 14999,
                'pro' => 39999,
                'elite' => 79999,
            ];
            $implementationFee = $implementationFees[$planSlug] ?? 0;
            
            // Employee costs
            $requestedEmployees = (int) $request->input('additionalEmployees', $includedEmployees);
            
            // For starter plan, only charge for employees beyond 10
            $freeEmployeeLimit = ($planSlug === 'starter') ? 10 : $includedEmployees;
            $extraEmployees = max(0, $requestedEmployees - $freeEmployeeLimit);
            $extraCost = $extraEmployees * $extraUserRate;
            
            // Mobile app costs
            $mobileAccess = $request->input('mobileAccess', false);
            $mobileAppUsers = $mobileAccess ? (int) $request->input('mobileAppUsers', 0) : 0;
            $mobileCost = $mobileAccess ? ($mobileAppUsers * $mobileAppRate) : 0;
            
            // Add-ons processing
            $selectedAddOns = $request->input('selectedAddOns', []);
            $addonCost = 0;
            $oneTimeAddonCost = 0;
            
            // Get included addons based on plan
            $includedAddons = [];
            switch ($planSlug) {
                case 'pro':
                    $includedAddons = ['biometric'];
                    break;
                case 'elite':
                    $includedAddons = ['custom_logo', 'biometric'];
                    break;
            }
            
            // Calculate addon costs (this would ideally come from database)
            $addonPrices = [
                'custom_logo' => ['price' => 3999, 'type' => 'one-time'],
                'geofencing' => ['price' => 10000, 'type' => 'monthly'],
                'email' => ['price' => 456, 'type' => 'monthly'],
                'biometric_installation' => ['price' => 9999, 'type' => 'one-time'],
                'biometric' => ['price' => 23216, 'type' => 'one-time'], // Fixed key mapping
                'biometric_integration' => ['price' => 10000, 'type' => 'one-time'], // Legacy support
            ];
            
            foreach ($selectedAddOns as $addonKey) {
                if (in_array($addonKey, $includedAddons)) {
                    continue; // Skip free addons
                }
                
                if ($addonKey === 'biometric') {
                    continue; // Handle biometric separately
                }
                
                if (isset($addonPrices[$addonKey])) {
                    $addon = $addonPrices[$addonKey];
                    if ($addon['type'] === 'one-time') {
                        $oneTimeAddonCost += $addon['price'];
                    } else {
                        $addonCost += $addon['price'];
                    }
                }
            }
            
            // Enhanced biometric device cost calculation using selectedDevices array
            $selectedDevices = $request->input('selectedDevices', []);
            $selectedBiometricServices = $request->input('selectedBiometricServices', []);
            $biometricDeviceCount = (int) $request->input('biometricDeviceCount', 0);
            
            // Calculate device costs from selected devices array
            $biometricDeviceCost = 0;
            $deviceBreakdown = [];
            
            if (!empty($selectedDevices)) {
                foreach ($selectedDevices as $device) {
                    $deviceName = $device['name'] ?? 'Unknown Device';
                    $devicePrice = (float) ($device['price'] ?? 0);
                    $deviceQuantity = (int) ($device['quantity'] ?? 0);
                    $deviceTotal = $devicePrice * $deviceQuantity;
                    
                    $biometricDeviceCost += $deviceTotal;
                    $deviceBreakdown[] = [
                        'name' => $deviceName,
                        'unit_price' => $devicePrice,
                        'quantity' => $deviceQuantity,
                        'total' => $deviceTotal,
                        'type' => $device['type'] ?? 'biometric_device'
                    ];
                }
            } else if ($biometricDeviceCount > 0) {
                // Fallback to old calculation if selectedDevices is empty but count exists
                $freeBiometricDevices = 0;
                switch ($planSlug) {
                    case 'pro':
                        $freeBiometricDevices = 1;
                        break;
                    case 'elite':
                        $freeBiometricDevices = 2;
                        break;
                }
                
                $paidBiometricDevices = max(0, $biometricDeviceCount - $freeBiometricDevices);
                $biometricDeviceCost = $paidBiometricDevices * 23216;
                
                if ($paidBiometricDevices > 0) {
                    $deviceBreakdown[] = [
                        'name' => 'Standard Biometric Device',
                        'unit_price' => 23216,
                        'quantity' => $paidBiometricDevices,
                        'total' => $biometricDeviceCost,
                        'type' => 'biometric_device'
                    ];
                }
            }
            
            // Calculate biometric services cost
            $biometricServicesCost = 0;
            $servicesBreakdown = [];
            
            if (!empty($selectedBiometricServices)) {
                $hasWallMounted = $selectedBiometricServices['wall_mounted'] ?? false;
                $hasDoorAccess = $selectedBiometricServices['door_access'] ?? false;
                $hasLegacyInstallation = $selectedBiometricServices['biometric_installation'] ?? false;

                // Installation costs are site-dependent and not included in calculations
                if ($hasWallMounted) {
                    $servicesBreakdown[] = [
                        'service' => 'Wall Mounted Installation',
                        'cost' => 0,
                        'note' => 'Site assessment required'
                    ];
                }

                if ($hasDoorAccess) {
                    $servicesBreakdown[] = [
                        'service' => 'Door Access Installation', 
                        'cost' => 0,
                        'note' => 'Site assessment required'
                    ];
                }

                // Legacy flag fallback (treat as basic installation if new options are absent)
                if (!$hasWallMounted && !$hasDoorAccess && $hasLegacyInstallation) {
                    $servicesBreakdown[] = [
                        'service' => 'Biometric Installation',
                        'cost' => 0,
                        'note' => 'Site assessment required'
                    ];
                }

                if ($selectedBiometricServices['biometric_integration'] ?? false) {
                    $biometricServicesCost += 10000;
                    $servicesBreakdown[] = [
                        'service' => 'Biometric Integration',
                        'cost' => 10000
                    ];
                }
            }
            
            // Total biometric cost includes devices + services
            $totalBiometricCost = $biometricDeviceCost + $biometricServicesCost;
            
            // Monthly recurring subtotal
            $monthlySubtotal = $basePrice + $extraCost + $addonCost + $mobileCost;
            
            // Implementation fee logic - Starter plan exception
            if ($planSlug === 'starter' && $requestedEmployees < 11) {
                $implementationFee = 0;
            }
            
            // Billing period calculations
            $billingPeriod = strtolower($request->input('billingPeriod', 'monthly'));
            $recurringTotal = $monthlySubtotal;
            
            if ($billingPeriod === 'annually' || $billingPeriod === 'annual' || $billingPeriod === 'yearly') {
                // Apply annual discount (5% off)
                $recurringTotal = $monthlySubtotal * 12 * 0.95;
            }
            
            // Grand subtotal (recurring + one-time costs)
            $grandSubtotal = $recurringTotal + $implementationFee + $oneTimeAddonCost + $totalBiometricCost;

            // VAT base matches frontend (exclude biometric totals which are VAT-inclusive)
            $vatBase = $recurringTotal + $implementationFee + $oneTimeAddonCost;
            
            // VAT calculation - Free plan has no VAT
            $vat = ($planSlug === 'free') ? 0 : $vatBase * $vatRate;
            $totalAmount = $grandSubtotal + $vat;
            
            // Log pricing calculation for debugging
            Log::info('Backend Pricing Calculation', [
                'plan' => $planSlug,
                'base_price' => $basePrice,
                'requested_employees' => $requestedEmployees,
                'extra_employees' => $extraEmployees,
                'extra_cost' => $extraCost,
                'mobile_cost' => $mobileCost,
                'addon_cost' => $addonCost,
                'one_time_addon_cost' => $oneTimeAddonCost,
                'biometric_device_cost' => $biometricDeviceCost,
                'biometric_services_cost' => $biometricServicesCost,
                'total_biometric_cost' => $totalBiometricCost,
                'device_breakdown' => $deviceBreakdown,
                'services_breakdown' => $servicesBreakdown,
                'implementation_fee' => $implementationFee,
                'monthly_subtotal' => $monthlySubtotal,
                'recurring_total' => $recurringTotal,
                'grand_subtotal' => $grandSubtotal,
                'vat' => $vat,
                'total_amount' => $totalAmount,
                'billing_period' => $billingPeriod
            ]);
            // -------------------- END Pricing Calculation --------------------

            // 1. Prepare unique reference number
            $reference = $request->input('reference_number') ?? ('checkout_' . now()->timestamp);

            // 2. Handle Free plan - skip payment processing
            if ($planSlug === 'free') {
                // For Free plan, create PaymentIntent directly without HitPay
                $paymentIntent = PaymentIntent::create([
                    'reference_number'          => $reference,
                    'system'                    => $request->input('system'),
                    'system_id'                 => $systemId,
                    'plan'                      => $request->input('plan'),
                    'plan_id'                   => $planId,
                    'company_name'              => $request->input('companyName'),
                    'industry'                  => $request->input('industry'),
                    'country'                   => $request->input('country'),
                    'state'                     => $request->input('state'),
                    'city'                      => $request->input('city'),
                    'company_code'              => $request->input('code'),
                    'subdomain'                 => data_get($request->input('formData'), 'subdomain'),
                    'first_name'                => $request->input('firstName'),
                    'last_name'                 => $request->input('lastName'),
                    'email'                     => $request->input('email'),
                    'phone'                     => $request->input('phoneNumber'),
                    'username'                  => $request->input('username'),
                    'password'                  => bcrypt($request->input('password')),
                    'billing_period'            => $request->input('billingPeriod'),
                    'selected_add_ons'          => json_encode($request->input('selectedAddOns', [])),
                    'is_trial'                  => true, // Free plan is essentially a trial
                    'additional_employees'      => $requestedEmployees,
                    'mobile_app_users'          => $request->input('mobileAppUsers'),
                    'mobile_access'             => $request->input('mobileAccess'),
                    'biometric_device_count'    => $request->input('biometricDeviceCount', 0),
                    'amount'                    => 0, // Free plan
                    'pricing_breakdown'         => json_encode([
                        'base_price' => 0,
                        'extra_cost' => 0,
                        'mobile_cost' => 0,
                        'addon_cost' => 0,
                        'one_time_addon_cost' => 0,
                        'biometric_device_cost' => 0,
                        'biometric_services_cost' => 0,
                        'total_biometric_cost' => 0,
                        'device_breakdown' => [],
                        'services_breakdown' => [],
                        'implementation_fee' => 0,
                        'monthly_subtotal' => 0,
                        'recurring_total' => 0,
                        'vat' => 0,
                        'total_amount' => 0
                    ]),
                    'currency'                  => env('HITPAY_CURRENCY', 'PHP'),
                    'payment_method'            => 'free',
                    'payment_reference'         => $reference,
                    'payment_request_id'        => null,
                    'payment_gateway'           => null,
                    'payment_gateway_reference' => null,
                    'checkout_url'              => null,
                    'gateway_response'          => json_encode(['status' => 'free_plan']),
                    'raw_payload'               => json_encode($request->all()),
                    'status'                    => 'succeeded', // Free plan is immediately successful
                    'referral_code'             => $request->input('referralCode'),
                ]);

                DB::commit();

                // Process immediately as successful payment
                $this->processPaymentCompletion($reference, ['status' => 'succeeded', 'payment_method' => 'free']);

                return response()->json([
                    'success' => true,
                    'message' => 'Free plan activated successfully',
                    'free_plan' => true,
                    'reference' => $reference,
                ]);
            }

            // 3. Prepare payment details for paid plans
            $amount = $totalAmount;
            $buyerEmail  = $request->input('email');
            $companyName = $request->input('companyName');
            $buyerName   = trim($request->input('firstName') . ' ' . $request->input('lastName'));
            $purpose     = 'Get started with your ' . $request->input('system') . ' subscription for ' . $companyName . ' today.';
            $redirectUrl = env('HITPAY_REDIRECT_URL', config('app.url') . '/payment-success');
            $webhookUrl  = env('HITPAY_WEBHOOK_URL');

            // 3. Prepare full payload for storage
            $payload = $request->all();

            // Get the phone number from the request
            $buyerPhone = $request->input('phoneNumber');

            // 4. Call HitPay API
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
                    'X-Requested-With'   => 'XMLHttpRequest',
                ],
            ]);

            $hitpayData = json_decode($response->getBody(), true);

            // 5. Store payment intent
            $paymentIntent = PaymentIntent::create([
                'reference_number'          => $reference,
                'system'                    => $request->input('system'),
                'system_id'                 => $systemId,
                'plan'                      => $request->input('plan'),
                'plan_id'                   => $planId,
                'company_name'              => $request->input('companyName'),
                'industry'                  => $request->input('industry'),
                'country'                   => $request->input('country'),
                'state'                     => $request->input('state'),
                'city'                      => $request->input('city'),
                'company_code'              => $request->input('code'),
                'subdomain'                 => data_get($request->input('formData'), 'subdomain'),
                'first_name'                => $request->input('firstName'),
                'last_name'                 => $request->input('lastName'),
                'email'                     => $request->input('email'),
                'phone'                     => $request->input('phoneNumber'),
                'username'                  => $request->input('username'),
                'password'                  => bcrypt($request->input('password')),
                'billing_period'            => $request->input('billingPeriod'),
                'selected_add_ons'          => json_encode($request->input('selectedAddOns', [])),
                'is_trial'                  => $request->input('isTrial', false),
                'additional_employees'      => $requestedEmployees,
                'mobile_app_users'          => $request->input('mobileAppUsers'),
                'mobile_access'             => $request->input('mobileAccess'),
                'biometric_device_count'    => $request->input('biometricDeviceCount', 0),
                'amount'                    => $totalAmount,
                'pricing_breakdown'         => json_encode([
                    'base_price' => $basePrice,
                    'extra_cost' => $extraCost,
                    'mobile_cost' => $mobileCost,
                    'addon_cost' => $addonCost,
                    'one_time_addon_cost' => $oneTimeAddonCost,
                    'biometric_device_cost' => $biometricDeviceCost,
                    'biometric_services_cost' => $biometricServicesCost,
                    'total_biometric_cost' => $totalBiometricCost,
                    'device_breakdown' => $deviceBreakdown,
                    'services_breakdown' => $servicesBreakdown,
                    'implementation_fee' => $implementationFee,
                    'monthly_subtotal' => $monthlySubtotal,
                    'recurring_total' => $recurringTotal,
                    'vat' => $vat,
                    'total_amount' => $totalAmount
                ]),
                'currency'                  => env('HITPAY_CURRENCY', 'PHP'),
                'payment_method'            => $request->input('paymentMethod'),
                'payment_reference'         => $reference,
                'payment_request_id'        => $hitpayData['id'] ?? null,
                'payment_gateway'           => 'hitpay',
                'payment_gateway_reference' => $hitpayData['id'] ?? null,
                'checkout_url'              => $hitpayData['url'] ?? null,
                'gateway_response'          => json_encode($hitpayData),
                'raw_payload'               => json_encode($payload),
                'status'                    => 'pending',
                'referral_code'             => $request->input('referralCode'),
            ]);

            DB::commit();

            return response()->json([
                'success'           => true,
                'url'               => $hitpayData['url'] ?? null,
                'payment_request_id' => $hitpayData['id'] ?? null,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating payment request: ' . $e->getMessage(),
            ], 400);
        }
    }



    /**
     * HitPay webhook receiver.
     * - Validates HMAC
     * - Looks up PaymentIntent (by payment_request_id)
     * - Creates Subscription, PaymentTransaction, etc
     */
    public function paymentConfirmation(Request $request)
    {
        Log::info('HitPay webhook received', [
            'payload' => $request->getContent(),
            'headers' => $request->headers->all()
        ]);

        $secret = env('HITPAY_SECRET_SALT');
        $contentType = $request->header('Content-Type');

        if (stripos($contentType, 'application/json') !== false) {
            $rawPayload = $request->getContent();
            $receivedHmac = $request->header('Hitpay-Signature');
            $calculatedHmac = hash_hmac('sha256', $rawPayload, $secret);

            if (!hash_equals($calculatedHmac, $receivedHmac)) {
                Log::warning('Invalid JSON webhook HMAC', [
                    'calculatedHmac' => $calculatedHmac,
                    'receivedHmac' => $receivedHmac,
                    'rawPayload' => $rawPayload
                ]);
                return response('Invalid signature', 400)->header('Content-Type', 'text/plain');
            }

            $data = json_decode($rawPayload, true);

            // if (($data['status'] ?? '') === 'succeeded' || ($data['status'] ?? '') === 'completed') {
            //     $ref = data_get($data, 'payment_request.reference_number');
            //     DB::table('payment_transactions')
            //         ->where('transaction_reference', $ref)
            //         ->where('status', '!=', 'succeeded')
            //         ->update([
            //             'status' => 'succeeded',
            //             'paid_at' => now(),
            //             'payment_method' => data_get($data, 'payment_provider.code'),
            //             'payment_gateway' => 'hitpay',
            //             'gateway_response' => json_encode($data),
            //         ]);
            // }

            if (($data['status'] ?? '') === 'succeeded' || ($data['status'] ?? '') === 'completed') {
                $ref = data_get($data, 'payment_request.reference_number');

                Log::info('JSON webhook processing', [
                    'reference_from_webhook' => $ref,
                    'full_data' => $data
                ]);

                $this->processPaymentCompletion($ref, $data);
            }

            return response('Webhook received (JSON)', 200)->header('Content-Type', 'text/plain');
        }

        // Handle form-urlencoded fallback
        $receivedHmac = $request->input('hmac') ?? '';
        $payload = $request->except('hmac');

        $cleanPayload = [];
        foreach ($payload as $key => $value) {
            if (is_scalar($value) || is_null($value)) {
                $cleanPayload[$key] = (string) $value;
            }
        }

        // Ensure reference_number exists
        if (!isset($cleanPayload['reference_number'])) {
            $cleanPayload['reference_number'] = '';
        }

        ksort($cleanPayload);
        $signatureString = implode('', array_values($cleanPayload));
        $calculatedHmac = hash_hmac('sha256', $signatureString, $secret);

        if (!hash_equals($calculatedHmac, $receivedHmac)) {
            Log::warning('Invalid FORM webhook HMAC', [
                'calculatedHmac' => $calculatedHmac,
                'receivedHmac' => $receivedHmac,
                'signatureString' => $signatureString,
                'payload' => $cleanPayload
            ]);
            return response('Invalid signature', 400)->header('Content-Type', 'text/plain');
        }

        // if (($cleanPayload['status'] ?? '') === 'completed') {
        //     $ref = $cleanPayload['reference_number'] ?? '';
        //     DB::table('payment_transactions')
        //         ->where('transaction_reference', $ref)
        //         ->where('status', '!=', 'succeeded')
        //         ->update([
        //             'status' => 'succeeded',
        //             'paid_at' => now(),
        //             'payment_id' => $cleanPayload['payment_id'] ?? null,
        //             'payment_gateway' => 'hitpay',
        //             'gateway_response' => json_encode($cleanPayload),
        //         ]);
        // }

        if (($cleanPayload['status'] ?? '') === 'completed') {
            $ref = $cleanPayload['reference_number'] ?? '';

            Log::info('Form webhook processing', [
                'reference_from_webhook' => $ref,
                'full_payload' => $cleanPayload
            ]);

            $this->processPaymentCompletion($ref, $cleanPayload);
        }

        return response('Webhook received (FORM)', 200)->header('Content-Type', 'text/plain');
    }

    private function processPaymentCompletion($referenceNumber, $webhookData)
    {
        try {
            DB::beginTransaction();

            

            $intent = PaymentIntent::where('reference_number', $referenceNumber)->first();
            
            if (!$intent) {
                Log::error('Payment storage failed', [
                    'reference' => $referenceNumber,
                    'error' => 'PaymentIntent not found for reference number'
                ]);
                throw new \Exception('PaymentIntent not found for reference number: ' . $referenceNumber);
            }

             // 1. Create the Company
            $company = Company::create([
                'name'     => $intent->company_name,
                'industry' => $intent->industry,
                'country'  => $intent->country,
                'state'    => $intent->state,
                'city'     => $intent->city,
                'code'     => $intent->company_code,
                'status'   => 'active',  // Default to active or change as needed
            ]);

            // 2. Create the Company System
            $companySystem = CompanySystem::create([
                'company_id' => $company->id,
                'system_id'  => $intent->system_id,  // Assuming you have system_id in your intent
                'subdomain'  => $intent->subdomain,
            ]);

            // 3. Create the User (Admin/Owner)
            // Extract plain password from raw payload
            $rawPayload = $intent->raw_payload;
            $plain_password = null;
            if ($rawPayload) {
                $payloadArr = json_decode($rawPayload, true);
                if (isset($payloadArr['password'])) {
                    $plain_password = $payloadArr['password'];
                }
            }

            if (!$plain_password) {
                Log::warning('Plain password not found in PaymentIntent raw_payload for user creation', [
                    'payment_intent_id' => $intent->id,
                    'email' => $intent->email
                ]);
                throw new Exception('Password not available for user creation');
            }

            $user = User::create([
                'company_id' => $company->id,
                'email'      => $intent->email,
                'password'   => bcrypt($plain_password),  // Hash the password properly
                'first_name' => $intent->first_name,
                'last_name'  => $intent->last_name,
                'phone'      => $intent->phone,
                'username'   => $intent->username,
                'role'       => 'owner',  // Assuming the first user is the owner
                'is_active'  => true,
            ]);

             // Determine subscription status based on payment success
            $subscriptionStatus = ($webhookData['status'] ?? '') === 'succeeded' || ($webhookData['status'] ?? '') === 'completed' ? 'active' : 'pending';

            // 4. Create Subscription record
            // Calculate actual additional employees (total - base employees for the plan)
            $planPrices = [
                'free' => ['base_employees' => 2], // Free plan includes 2 employees
                'core'  => ['base_employees' => 21],
                'pro'   => ['base_employees' => 101],
                'elite' => ['base_employees' => 201],
                'starter' => ['base_employees' => 1], // Starter plan includes 1 employee
            ];
            
            $planSlug = $intent->plan;
            $baseEmployees = $planPrices[$planSlug]['base_employees'] ?? 0;
            $totalEmployees = $intent->additional_employees; // This is actually total employees from frontend
            $actualAdditionalEmployees = max(0, $totalEmployees - $baseEmployees);
            
            // Calculate subscription dates from the payload or use defaults
            $rawPayload = $intent->raw_payload;
            $payloadData = $rawPayload ? json_decode($rawPayload, true) : [];
            
            $billingPeriod = strtolower($intent->billing_period);
            $startDate = isset($payloadData['subscriptionStart']) 
                ? \Carbon\Carbon::parse($payloadData['subscriptionStart'])
                : now();
            $endDate = isset($payloadData['subscriptionEnd'])
                ? \Carbon\Carbon::parse($payloadData['subscriptionEnd'])
                : ($billingPeriod === 'annually' 
                    ? $startDate->copy()->addYear() 
                    : $startDate->copy()->addMonth());
            $nextRenewalDate = isset($payloadData['nextRenewalDate'])
                ? \Carbon\Carbon::parse($payloadData['nextRenewalDate'])
                : $endDate->copy();
            
            $subscription = Subscription::create([
                'user_id'               => $user->id,  // Associate with the created user
                'company_id'            => $company->id,  // Associate with the company
                'company_system_id'     => $companySystem->id,
                'system_id'             => $intent->system_id, // Use system_id from the intent
                'plan_id'               => $intent->plan_id,   // Use plan ID from the intent
                'billing_period'        => $intent->billing_period,
                'status'                => $subscriptionStatus,  // Set based on payment status
                'is_trial'              => $intent->is_trial,
                'trial_ends_at'         => $intent->is_trial ? now()->addDays(7) : null,
                'start_date'            => $startDate,
                'end_date'              => $endDate,
                'referral_code'         => $intent->referral_code,
                'additional_employees'  => $actualAdditionalEmployees, // Add the calculated additional employees
                'biometric_device_count' => $intent->biometric_device_count ?? 0, // Add biometric device count
                // New subscription detail fields
                'total_employees'       => $intent->additional_employees, // Total employees from intent
                'selected_addons'       => $intent->selected_add_ons, // JSON string of selected add-ons
                'mobile_access'         => $intent->mobile_access ?? false,
                'mobile_app_users'      => $intent->mobile_app_users ?? 0,
                'pricing_breakdown'     => $intent->pricing_breakdown, // JSON pricing details
                'subscription_metadata' => json_encode([
                    'system' => $intent->system,
                    'plan' => $intent->plan,
                    'company_name' => $intent->company_name,
                    'subdomain' => $intent->subdomain,
                    'created_from_payment_intent_id' => $intent->id,
                ]),
                // Payment tracking fields
                'implementation_fee'    => $payloadData['implementationFee'] ?? 0,
                'total_amount_paid'     => $payloadData['totalAmountPaid'] ?? $intent->amount,
                'next_renewal_date'     => $nextRenewalDate,
            ]);


            // 5. Create Payment record
            $payment = Payment::create([
                'subscription_id'   => $subscription->id,
                'payment_reference' => $referenceNumber,  // Use reference number for tracking
                'amount'            => $intent->amount,
                'currency'          => $intent->currency,
                'payment_method'    => 'hitpay',  // Assuming payment is via HitPay
                'payment_status'    => 'pending',  // Set to pending until webhook confirms
                'raw_response'      => json_encode($webhookData),  // Store full response
            ]);

            // 6. Save Add-Ons
            if (isset($intent->selected_add_ons) && !empty($intent->selected_add_ons)) {
                // Decode the JSON string into an array
                $selectedAddOns = json_decode($intent->selected_add_ons, true); // Decode JSON string to array

                // Ensure $selectedAddOns is an array
                if (is_array($selectedAddOns)) {
                    foreach ($selectedAddOns as $addonKey) {
                        // Skip biometric addon as it's handled through biometric services, not as a regular addon
                        if ($addonKey === 'biometric') {
                            continue;
                        }
                        
                        // Find the add-on by its key
                        $addon = Addon::where('addon_key', $addonKey)->first();
                        
                        if ($addon) {
                            DB::table('company_addons')->insert([
                                'company_system_id' => $companySystem->id,
                                'addon_id'          => $addon->id,  // Get the addon_id based on addon_key
                                'active'            => true,
                                'start_date'        => now(),
                                'end_date'          => $endDate, // Use same end date as main subscription
                                'created_at'        => now(),
                                'updated_at'        => now(),
                            ]);
                        } else {
                            // Log the missing add-on or handle the error
                            Log::warning('Addon not found for key', ['addon_key' => $addonKey]);
                        }
                    }
                } else {
                    Log::warning('Selected add-ons are not in the correct format', ['selected_add_ons' => $intent->selected_add_ons]);
                }
            }

            // 7. Save Biometric Devices
            // Get the selected devices from the raw payload
            $rawPayload = $intent->raw_payload;
            $payloadData = $rawPayload ? json_decode($rawPayload, true) : [];
            $selectedDevices = $payloadData['selectedDevices'] ?? [];
            $selectedBiometricServices = $payloadData['selectedBiometricServices'] ?? [];
            
            if (!empty($selectedDevices)) {
                foreach ($selectedDevices as $device) {
                    $deviceName = $device['name'] ?? 'Unknown Device';
                    $devicePrice = (float) ($device['price'] ?? 0);
                    $deviceQuantity = (int) ($device['quantity'] ?? 0);
                    $deviceType = $device['type'] ?? 'biometric_device';
                    $deviceModel = $device['model'] ?? null;
                    $deviceSpecs = $device['specifications'] ?? null;
                    $installationType = $device['installation_type'] ?? null;
                    
                    if ($deviceQuantity > 0 && $devicePrice > 0) {
                        BiometricDevice::create([
                            'payment_intent_id' => $intent->id,
                            'subscription_id' => $subscription->id,
                            'device_name' => $deviceName,
                            'device_type' => $deviceType,
                            'device_model' => $deviceModel,
                            'unit_price' => $devicePrice,
                            'quantity' => $deviceQuantity,
                            'total_price' => $devicePrice * $deviceQuantity,
                            'device_specifications' => $deviceSpecs ? json_encode($deviceSpecs) : null,
                            'installation_type' => $installationType,
                            'notes' => "Device selected during subscription creation for {$intent->company_name}"
                        ]);
                        
                        Log::info('Biometric device stored', [
                            'device_name' => $deviceName,
                            'quantity' => $deviceQuantity,
                            'total_price' => $devicePrice * $deviceQuantity,
                            'payment_intent_id' => $intent->id,
                            'subscription_id' => $subscription->id
                        ]);
                    }
                }
            }
            
            // Store biometric services as devices with zero quantity for tracking
            if (!empty($selectedBiometricServices)) {
                foreach ($selectedBiometricServices as $serviceKey => $isSelected) {
                    if ($isSelected) {
                        $serviceName = '';
                        $servicePrice = 0;
                        
                        switch ($serviceKey) {
                            case 'wall_mounted':
                                $serviceName = 'Wall Mounted Installation';
                                $servicePrice = 0; // Installation costs are site-dependent
                                break;
                            case 'door_access':
                                $serviceName = 'Door Access Installation';
                                $servicePrice = 0; // Installation costs are site-dependent
                                break;
                            case 'biometric_integration':
                                $serviceName = 'Biometric Integration Service';
                                $servicePrice = 10000;
                                break;
                        }
                        
                        if ($serviceName) {
                            BiometricDevice::create([
                                'payment_intent_id' => $intent->id,
                                'subscription_id' => $subscription->id,
                                'device_name' => $serviceName,
                                'device_type' => 'service',
                                'device_model' => null,
                                'unit_price' => $servicePrice,
                                'quantity' => 1,
                                'total_price' => $servicePrice,
                                'device_specifications' => null,
                                'installation_type' => $serviceKey,
                                'notes' => "Service selected during subscription creation for {$intent->company_name}"
                            ]);
                            
                            Log::info('Biometric service stored', [
                                'service_name' => $serviceName,
                                'service_price' => $servicePrice,
                                'payment_intent_id' => $intent->id,
                                'subscription_id' => $subscription->id
                            ]);
                        }
                    }
                }
            }

            // Update PaymentIntent with completion data
            $intent->update([
                'status' => 'succeeded',
                'payment_gateway_reference' => $webhookData['id'] ?? null, // HitPay payment ID
            ]);

            // Store detailed transaction data
            // Store detailed transaction data
            // Store detailed transaction data
            // Store detailed transaction data
            DB::table('payment_transactions')
                ->updateOrInsert(
                    ['transaction_reference' => $referenceNumber],
                    [
                        'payment_id'        => $webhookData['id'] ?? null,
                        'subscription_id'   => $subscription->id,
                        'company_id'        => $company->id,
                        'user_id'           => $user->id,
                        'amount'            => $webhookData['amount'] ?? $intent->amount,
                        'currency'          => strtoupper($webhookData['currency'] ?? $intent->currency),
                        'status'            => 'succeeded',
                        'paid_at'           => now(),
                        'payment_method'    => $webhookData['payment_provider']['code'] ?? 'hitpay',
                        'payment_gateway'   => 'hitpay',
                        'gateway_response'  => json_encode($webhookData),
                        'updated_at'        => now(),
                        'created_at'        => now(),
                    ]
                );


            // Create admin user in Timora system
            $userReferralCode = $intent->referral_code; // Default to input referrer code
            try {
                $adminUserResponse = $this->createAdminUser($intent);
                $userReferralCode = $adminUserResponse['user']['referral_code'] ?? $intent->referral_code;
                
                Log::info('Admin user created successfully during payment completion', [
                    'reference' => $referenceNumber,
                    'email' => $intent->email,
                    'new_referral_code' => $userReferralCode
                ]);
            } catch (Exception $e) {
                Log::error('Failed to create admin user during payment completion', [
                    'reference' => $referenceNumber,
                    'email' => $intent->email,
                    'error' => $e->getMessage()
                ]);
                // Don't rollback the entire transaction for admin user creation failure
            }

            // Send two separate emails after successful payment
            if ($subscriptionStatus === 'active' && config('mail.affiliate.enabled', true)) {
                
                // 1. Send Welcome & Affiliate Program Email
                try {
                    Mail::to($user->email)->send(new WelcomeAffiliate($user, $company, $userReferralCode));
                    Log::info('Welcome affiliate email sent successfully', [
                        'reference' => $referenceNumber,
                        'email' => $user->email,
                        'company' => $company->name,
                        'user_referral_code' => $userReferralCode
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to send welcome affiliate email', [
                        'reference' => $referenceNumber,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                    // Don't rollback the transaction for email failure
                }

                // Small delay between emails to avoid potential rate limiting
                sleep(2);

                // 2. Send Account Credentials Email  
                try {
                    Mail::to($user->email)->send(new AffiliateWelcome($user, $company, $plain_password, $userReferralCode));
                    Log::info('Account credentials email sent successfully', [
                        'reference' => $referenceNumber,
                        'email' => $user->email,
                        'company' => $company->name,
                        'referrer_code' => $intent->referral_code,
                        'user_referral_code' => $userReferralCode
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to send account credentials email', [
                        'reference' => $referenceNumber,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                    // Don't rollback the transaction for email failure
                }
            }

            // 8. Send Admin/Internal Subscription Notification
            $adminEmails = explode(',', env('ADMIN_NOTIFICATION_EMAILS', ''));
            $adminEmails = array_filter(array_map('trim', $adminEmails)); // Remove empty values
            
            // Always include the primary admin email
            $primaryAdminEmail = 'rebusquillojohnrey@gmail.com';
            if (!in_array($primaryAdminEmail, $adminEmails)) {
                $adminEmails[] = $primaryAdminEmail;
            }
            
            if (!empty($adminEmails)) {
                try {
                    // Get biometric devices for notification
                    $biometricDevices = BiometricDevice::where('payment_intent_id', $intent->id)->get();
                    
                    // Parse pricing breakdown for email
                    $pricingBreakdown = [];
                    $pricingBreakdownRaw = $intent->pricing_breakdown;
                    if (!empty($pricingBreakdownRaw)) {
                        $pricingBreakdown = is_array($pricingBreakdownRaw)
                            ? $pricingBreakdownRaw
                            : (json_decode($pricingBreakdownRaw, true) ?: []);
                    }
                    
                    // Send to all admin emails
                    foreach ($adminEmails as $adminEmail) {
                        if (filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                            Mail::to($adminEmail)->send(new NewSubscriptionNotification(
                                $user,
                                $company,
                                $subscription,
                                $pricingBreakdown,
                                $biometricDevices
                            ));
                            
                            Log::info('Admin subscription notification sent', [
                                'reference' => $referenceNumber,
                                'admin_email' => $adminEmail,
                                'company_name' => $company->name,
                                'subscription_id' => $subscription->id
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send admin subscription notification', [
                        'reference' => $referenceNumber,
                        'error' => $e->getMessage(),
                        'company_name' => $company->name ?? 'Unknown'
                    ]);
                }
            } else {
                Log::warning('No admin emails configured for subscription notifications', [
                    'reference' => $referenceNumber,
                    'env_variable' => 'ADMIN_NOTIFICATION_EMAILS'
                ]);
            }

            DB::commit();
            Log::info('Payment stored successfully', ['reference' => $referenceNumber]);
            
            // Initialize provisioning tracking for real-time status updates
            $this->initializeProvisioning($intent, $company);
            
            // Trigger GitHub Actions workflow for deployment
            $workflowTriggered = $this->triggerGitHubWorkflow($intent, $company);
            
            // Log the complete payment processing summary
            Log::info('=== PAYMENT PROCESSING COMPLETED ===', [
                'reference' => $referenceNumber,
                'company_code' => $company->code,
                'company_id' => $company->id,
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'workflow_triggered' => $workflowTriggered,
                'provisioning_initialized' => true,
                'next_steps' => [
                    'Monitor provisioning status at /api/wizard/provisioning/status',
                    'GitHub Actions should callback to /api/wizard/provisioning/github-callback',
                    'User will see real-time progress on loading page'
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment storage failed', [
                'reference' => $referenceNumber,
                'error' => $e->getMessage()
            ]);
        }
    }
    /**
     * Parse and validate the webhook payload, checking the HMAC signature.
     */
    private function parseHitpayPayload(Request $request)
    {
        $secretSalt = env('HITPAY_SECRET_SALT'); // Your Event Hooks salt
        $contentType = $request->header('Content-Type') ?? '';

        Log::info('Received Payload:', ['payload' => $request->getContent()]);

        if (strpos($contentType, 'application/json') !== false) {
            // JSON webhook validation
            $rawPayload = $request->getContent();
            $headerHmac = $request->header('Hitpay-Signature');
            $calculatedHmac = hash_hmac('sha256', $rawPayload, $secretSalt);

            if (!$headerHmac || !hash_equals($calculatedHmac, $headerHmac)) {
                Log::warning('Invalid JSON webhook HMAC', [
                    'expected' => $calculatedHmac, 'received' => $headerHmac
                ]);
                return ['error' => 'Invalid signature'];
            }

            return ['data' => json_decode($rawPayload, true)];
        }

        // Form webhook validation (application/x-www-form-urlencoded)
        $formData = $request->all();
        $receivedHmac = $formData['hmac'] ?? '';
        unset($formData['hmac']);

        foreach ($formData as $key => $val) {
            if ($val === null) {
                $formData[$key] = '';
            }
        }

        $hmacData = [];
        foreach ($formData as $key => $val) {
            $hmacData[$key] = $key . $val;
        }
        ksort($hmacData);
        $stringToHash = implode('', $hmacData);

        $calculatedHmac = hash_hmac('sha256', $stringToHash, $secretSalt);

        if (!hash_equals($calculatedHmac, $receivedHmac)) {
            Log::warning('Invalid FORM webhook HMAC', [
                'expected' => $calculatedHmac, 'received' => $receivedHmac
            ]);
            return ['error' => 'Invalid signature'];
        }

        return ['data' => $formData];
    }


    // provision webhook handler
    public function dispatchWebhook(Request $request)
    {
        $payload = $request->all();

        Log::info('Payment Gateway Webhook Payload Received', [
            'payload' => $payload
        ]);

        // 1. Extract Payment Gateway Reference (payment gateway reference is now the 'id' from the webhook)
        $paymentGatewayReference = $payload['id'] ?? null;

        if (!$paymentGatewayReference) {
            Log::error('Missing payment gateway reference (id) in webhook payload. Payload: ' . json_encode($payload));
            return response()->json(['error' => 'Missing payment gateway reference'], 400);
        }

        // 2. Fetch the PaymentIntent based on the payment gateway reference (using 'id' from the payload)
        $paymentIntent = PaymentIntent::where('payment_gateway_reference', $paymentGatewayReference)->first();

        if (!$paymentIntent) {
            Log::error('PaymentIntent not found for Payment Gateway Reference: ' . $paymentGatewayReference);
            return response()->json(['error' => 'PaymentIntent not found'], 404);
        }

        // 3. Extract details for GitHub Actions workflow
        $system = $paymentIntent->system;
        $plan = $paymentIntent->plan;
        $email = $paymentIntent->email;
        $companyName = $paymentIntent->company_name;
        $companyCode = $paymentIntent->company_code;
        $subdomain = $paymentIntent->subdomain;
        $username = $paymentIntent->username;
        $hash_password = $paymentIntent->password;
        $billing_cycle = $paymentIntent->billing_period;
        $amount = $paymentIntent->amount;

        // Normalize billing period values
        if ($billing_cycle === 'annually') {
            $billing_cycle = 'yearly';
        }

        $rawPayload = $paymentIntent->raw_payload;
        $plain_password = null;
        if ($rawPayload) {
            $payloadArr = json_decode($rawPayload, true);
            if (isset($payloadArr['password'])) {
                $plain_password = $payloadArr['password'];
                $encoded_password = base64_encode($plain_password);

            }
        }

        Log::info('Dispatching GitHub Actions Workflow with:', [
            'system' => $system,
            'plan' => $plan,
            'email' => $email,
            'company_name' => $companyName,
            'company_code' => $companyCode,
            'domain_name' => $subdomain,
            'username' => $username,
            'billing_cycle' => $billing_cycle,
            'amount' => $amount
        ]);

        // 4. Prepare data for GitHub Actions workflow dispatch
        $githubApiUrl = env('GITHUB_DISPATCH_URL');
        $githubToken = env('GITHUB_TOKEN');

        // Enhanced logging for GitHub Actions preparation
        Log::info('=== GITHUB ACTIONS PROVISIONING START ===', [
            'payment_gateway_reference' => $paymentGatewayReference,
            'github_api_url' => $githubApiUrl,
            'github_token_configured' => !empty($githubToken),
            'github_token_length' => strlen($githubToken ?? ''),
        ]);

        if (empty($githubApiUrl)) {
            Log::error('GitHub Dispatch URL not configured in environment variables');
            return response()->json(['error' => 'GitHub API URL not configured'], 500);
        }

        if (empty($githubToken)) {
            Log::error('GitHub Token not configured in environment variables');
            return response()->json(['error' => 'GitHub Token not configured'], 500);
        }

        $postData = [
            'ref' => 'main',
            'inputs' => [
                'system' => $system,
                'plan' => $plan,
                'email' => $email,
                'company_name' => $companyName,
                'domain_name' => $subdomain,
                'company_code' => $companyCode,
                'username' => $username,
                'password' => $encoded_password,
                'billing_cycle' => $billing_cycle,
                'amount' => (string)$amount,
            ],
        ];

        Log::info('GitHub Actions Workflow Dispatch Payload', [
            'url' => $githubApiUrl,
            'ref' => 'main',
            'inputs' => [
                'system' => $system,
                'plan' => $plan,
                'email' => $email,
                'company_name' => $companyName,
                'domain_name' => $subdomain,
                'company_code' => $companyCode,
                'username' => $username,
                'password' => '[REDACTED]',
                'billing_cycle' => $billing_cycle,
                'amount' => (string)$amount,
            ]
        ]);

        // 5. Send the request to GitHub Actions API with timeout and retry logic
        try {
            $startTime = microtime(true);
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/vnd.github+json',
                    'Authorization' => 'Bearer ' . $githubToken,
                    'X-GitHub-Api-Version' => '2022-11-28',
                    'User-Agent' => 'JAF-Wizard-Backend/1.0',
                ])->post($githubApiUrl, $postData);

            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2); // Duration in milliseconds

            Log::info('GitHub Actions Dispatch Response', [
                'duration_ms' => $duration,
                'status_code' => $response->status(),
                'successful' => $response->successful(),
                'failed' => $response->failed(),
                'client_error' => $response->clientError(),
                'server_error' => $response->serverError(),
                'response_headers' => $response->headers(),
                'response_body' => $response->body(),
                'response_json' => $response->json(),
            ]);

            // Check if the workflow dispatch was successful
            if ($response->successful()) {
                Log::info('✅ GitHub Actions workflow dispatched successfully', [
                    'company_code' => $companyCode,
                    'subdomain' => $subdomain,
                    'email' => $email,
                    'status_code' => $response->status()
                ]);
            } else {
                Log::error('❌ GitHub Actions workflow dispatch failed', [
                    'company_code' => $companyCode,
                    'subdomain' => $subdomain,
                    'email' => $email,
                    'status_code' => $response->status(),
                    'error_body' => $response->body(),
                    'possible_causes' => [
                        'Invalid GitHub token',
                        'Repository not found',
                        'Workflow file not found',
                        'Insufficient permissions',
                        'Rate limiting'
                    ]
                ]);
            }

        } catch (\Exception $e) {
            Log::error('❌ GitHub Actions API request failed with exception', [
                'company_code' => $companyCode,
                'email' => $email,
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString()
            ]);
            
            // Return error but don't stop the entire process
            $response = null;
        }

        // 6. Trigger Tenant API
        Log::info('=== TENANT API PROVISIONING START ===', [
            'company_code' => $companyCode,
            'company_name' => $companyName,
            'subdomain' => $subdomain,
            'tenant_url' => "https://{$subdomain}.timora.ph"
        ]);

        try {
            // Determine whether tenant provisioning should run based on mobile_access flag.
            // Accepts true/1/"1"/"true"/"yes"/"on" as enabled; false/0/"0"/"false"/null as disabled.
            $mobileAccessEnabled = filter_var($paymentIntent->mobile_access ?? false, FILTER_VALIDATE_BOOLEAN);

            if (!$mobileAccessEnabled) {
            Log::info('Skipping Tenant API provisioning because mobile_access is not enabled', [
                'company_code' => $companyCode,
                'subdomain' => $subdomain,
                'mobile_access' => $paymentIntent->mobile_access,
            ]);
            $tenantApiResponse = null;
            } else {
            $tenantPayload = [
                'tenant_code' => $companyCode,
                'tenant_name' => $companyName,
                'tenant_url' => "https://{$subdomain}.timora.ph",
                'active' => true
            ];

            $tenantStartTime = microtime(true);
            $tenantApiResponse = Http::timeout(15)->post('https://auth.timora.ph/api/tenants', $tenantPayload);
            $tenantEndTime = microtime(true);
            $tenantDuration = round(($tenantEndTime - $tenantStartTime) * 1000, 2);

            if ($tenantApiResponse->successful()) {
                Log::info('✅ Tenant API call successful', [
                'duration_ms' => $tenantDuration,
                'status_code' => $tenantApiResponse->status(),
                'company_code' => $companyCode,
                'tenant_url' => "https://{$subdomain}.timora.ph",
                'response_body' => $tenantApiResponse->body(),
                'response_json' => $tenantApiResponse->json(),
                ]);
            } else {
                Log::warning('⚠️ Tenant API call failed', [
                'duration_ms' => $tenantDuration,
                'status_code' => $tenantApiResponse->status(),
                'company_code' => $companyCode,
                'error_body' => $tenantApiResponse->body(),
                'possible_causes' => [
                    'Tenant already exists',
                    'Invalid tenant data',
                    'API authentication issue',
                    'Network connectivity'
                ]
                ]);
            }
            }
        } catch (Exception $e) {
            Log::error('❌ Tenant API request failed with exception', [
                'company_code' => $companyCode,
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString()
            ]);
        }

        // 7. Create admin user in Timora system
        $adminUserCreated = false;
        try {
            $this->createAdminUser($paymentIntent);
            $adminUserCreated = true;
            Log::info('✅ Admin user created successfully', [
                'email' => $email,
                'username' => $username,
                'company_name' => $companyName
            ]);
        } catch (Exception $e) {
            Log::error('❌ Failed to create admin user', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Final summary log
        Log::info('=== PROVISIONING PROCESS SUMMARY ===', [
            'company_code' => $companyCode,
            'subdomain' => $subdomain,
            'email' => $email,
            'system' => $system,
            'plan' => $plan,
            'results' => [
                'github_actions_dispatched' => isset($response) && $response && $response->successful(),
                'github_actions_status' => isset($response) && $response ? $response->status() : 'Failed',
                'tenant_api_called' => isset($tenantApiResponse) && $tenantApiResponse && $tenantApiResponse->successful(),
                'admin_user_created' => $adminUserCreated,
            ],
            'next_steps' => [
                'Monitor GitHub Actions workflow progress in repository',
                'Verify tenant creation at https://auth.timora.ph',
                'Check if subdomain resolves: https://' . $subdomain . '.timora.ph',
                'User should receive email with login credentials'
            ]
        ]);

        // Prepare response with detailed status
        $responseData = [
            'user_email' => $email,
            'company_name' => $companyName,
            'system_type' => $system,
            'plan_type' => $plan,
            'company_code' => $companyCode,
            'domain_name' => $subdomain,
            'user_name' => $username,
            'billing_cycle' => $billing_cycle,
            'amount' => $amount,
            'message' => 'Payment webhook processed successfully',
            'provisioning_status' => [
                'github_actions' => [
                    'dispatched' => isset($response) && $response && $response->successful(),
                    'status_code' => isset($response) && $response ? $response->status() : null,
                    'response' => isset($response) && $response ? $response->json() : null,
                ],
                'tenant_api' => [
                    'called' => isset($tenantApiResponse),
                    'successful' => isset($tenantApiResponse) && $tenantApiResponse && $tenantApiResponse->successful(),
                    'status_code' => isset($tenantApiResponse) && $tenantApiResponse ? $tenantApiResponse->status() : null,
                ],
                'admin_user_created' => $adminUserCreated,
            ]
        ];

        // Add GitHub response details if available
        if (isset($response) && $response) {
            $responseData['github_dispatch_status'] = $response->status();
            $responseData['github_dispatch_body'] = $response->json();
            $responseData['github_dispatch_raw'] = $response->body();
            $responseData['github_dispatch_headers'] = $response->headers();
        }

        return response()->json($responseData);
    }

    /**
     * Create admin user in Timora system after successful payment
     */
    private function createAdminUser(PaymentIntent $paymentIntent)
    {
        // Extract plain password from raw payload
        $rawPayload = $paymentIntent->raw_payload;
        $plain_password = null;
        $encoded_password = null;
        if ($rawPayload) {
            $payloadArr = json_decode($rawPayload, true);
            if (isset($payloadArr['password'])) {
                $plain_password = $payloadArr['password'];
                $encoded_password = base64_encode($plain_password);

            }
        }

        if (!$plain_password) {
            Log::warning('Plain password not found in PaymentIntent raw_payload for admin user creation', [
                'payment_intent_id' => $paymentIntent->id,
                'email' => $paymentIntent->email
            ]);
            throw new Exception('Password not available for admin user creation');
        }

        // Prepare admin user payload
        $adminUserPayload = [
            'name' => trim($paymentIntent->first_name . ' ' . $paymentIntent->last_name),
            'username' => $paymentIntent->username,
            'email' => $paymentIntent->email,
            'phone' => $paymentIntent->phone,
            'password' => $plain_password,
            'password_confirmation' => $plain_password,

            'role' => 'sales',
            'company_name' => $paymentIntent->company_name
        ];

        // Add referral code (use default if none provided)
        $referralCode = !empty($paymentIntent->referral_code) ? $paymentIntent->referral_code : 'AFLJDGI';
        $adminUserPayload['referrer_code'] = $referralCode;

        Log::info('Creating admin user with payload:', [
            'email' => $paymentIntent->email,
            'username' => $paymentIntent->username,
            'company_name' => $paymentIntent->company_name,
            'role' => 'sales',
            'referrer_code' => $referralCode
        ]);

        // Send request to Timora admin API
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->post('https://api-admin.timora.ph/api/wizard/users', $adminUserPayload);

        // Log the response
        Log::info('Timora admin user creation response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'email' => $paymentIntent->email
        ]);

        // Check if request was successful
        if (!$response->successful()) {
            Log::error('Failed to create admin user in Timora system', [
                'status' => $response->status(),
                'response' => $response->body(),
                'payload' => $adminUserPayload,
                'email' => $paymentIntent->email
            ]);
            
            throw new Exception('Failed to create admin user: ' . $response->body());
        }

        $responseData = $response->json();
        
        // Extract the new referral_code generated by the API
        $newReferralCode = $responseData['user']['referral_code'] ?? null;
        
        if ($newReferralCode) {
            Log::info('New referral code generated for user', [
                'email' => $paymentIntent->email,
                'new_referral_code' => $newReferralCode,
                'referrer_code' => $referralCode
            ]);
        }
        
        return $responseData;
    }

    /**
     * Initialize provisioning tracking for real-time status updates
     */
    private function initializeProvisioning($intent, $company)
    {
        try {
            // Create provisioning status record
            $provisioning = ProvisioningStatus::updateOrCreate(
                ['company_code' => $company->code],
                [
                    'reference_number' => $intent->reference_number,
                    'email' => $intent->email,
                    'status' => 'pending',
                    'current_step' => 'initialization',
                    'message' => 'Payment confirmed - preparing to deploy portal',
                    'metadata' => [
                        'domain' => strtolower($company->code) . '.timora.ph',
                        'plan' => $intent->plan,
                        'system' => $intent->system,
                        'company_name' => $company->name,
                        'billing_cycle' => $intent->billing_period,
                        'amount' => $intent->amount,
                        'username' => $intent->username,
                        'subdomain' => $intent->subdomain ?? strtolower($company->code),
                        'payment_reference' => $intent->reference_number,
                        'provisioning_started' => now()->toISOString()
                    ]
                ]
            );

            Log::info('Provisioning initialized', [
                'company_code' => $company->code,
                'reference_number' => $intent->reference_number,
                'provisioning_id' => $provisioning->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to initialize provisioning', [
                'company_code' => $company->code,
                'error' => $e->getMessage()
            ]);
            // Don't fail the entire payment process for provisioning tracking errors
        }
    }

    /**
     * Trigger GitHub Actions workflow for deployment
     */
    private function triggerGitHubWorkflow($intent, $company)
    {
        try {
            $githubToken = config('services.github.token') ?? env('GITHUB_TOKEN');
            $dispatchUrl = config('services.github.dispatch_url') ?? env('GITHUB_DISPATCH_URL');
            
            if (!$githubToken || !$dispatchUrl) {
                Log::warning('GitHub configuration missing', [
                    'has_token' => !empty($githubToken),
                    'has_url' => !empty($dispatchUrl)
                ]);
                return;
            }

            $payloadArr = json_decode($intent->raw_payload, true);
            $plain_password = $payloadArr['password'] ?? null;

            if (!$plain_password) {
                throw new \Exception('Plain password missing in raw_payload');
            }

            $encoded_password = base64_encode($plain_password);

            // Extract comprehensive subscription data for accurate invoice generation
            $rawPayload = json_decode($intent->raw_payload, true) ?? [];
            $pricingBreakdown = json_decode($intent->pricing_breakdown, true) ?? [];
            $selectedAddons = json_decode($intent->selected_add_ons, true) ?? [];
            $selectedDevices = $rawPayload['selectedDevices'] ?? [];
            $selectedBiometricServices = $rawPayload['selectedBiometricServices'] ?? [];

            $payload = [
                'event_type' => 'provision-timora',
                'client_payload' => [
                    // Basic provisioning data
                    'system' => $intent->system,
                    'plan' => $intent->plan,
                    'email' => $intent->email,
                    'company_name' => $company->name,
                    'domain_name' => strtolower($company->code),
                    'company_code' => $company->code,
                    'username' => $intent->username,
                    'password' => $encoded_password,
                    'billing_cycle' => $intent->billing_period,
                    'amount' => $intent->amount,
                    'callback_url' => config('app.url') . '/api/wizard/provisioning/github-callback',
                    'reference_number' => $intent->reference_number,
                    'wizard_api_url' => config('app.url') . '/api',
                    
                    // Enhanced subscription data for accurate invoicing
                    'subscription_details' => json_encode([
                        'plan_slug' => $intent->plan,
                        'system_slug' => $intent->system,
                        'billing_period' => $intent->billing_period,
                        'total_employees' => $intent->additional_employees,
                        'mobile_access' => $intent->mobile_access,
                        'mobile_app_users' => $intent->mobile_app_users,
                        'biometric_device_count' => $intent->biometric_device_count,
                        'selected_addons' => $selectedAddons,
                        'is_trial' => $intent->is_trial,
                        'referral_code' => $intent->referral_code,
                    ]),
                    
                    // Detailed pricing breakdown for accurate invoice generation
                    'pricing_breakdown' => json_encode([
                        'base_price' => $pricingBreakdown['base_price'] ?? 0,
                        'extra_cost' => $pricingBreakdown['extra_cost'] ?? 0,
                        'mobile_cost' => $pricingBreakdown['mobile_cost'] ?? 0,
                        'addon_cost' => $pricingBreakdown['addon_cost'] ?? 0,
                        'one_time_addon_cost' => $pricingBreakdown['one_time_addon_cost'] ?? 0,
                        'biometric_device_cost' => $pricingBreakdown['biometric_device_cost'] ?? 0,
                        'biometric_services_cost' => $pricingBreakdown['biometric_services_cost'] ?? 0,
                        'total_biometric_cost' => $pricingBreakdown['total_biometric_cost'] ?? 0,
                        'implementation_fee' => $pricingBreakdown['implementation_fee'] ?? 0,
                        'monthly_subtotal' => $pricingBreakdown['monthly_subtotal'] ?? 0,
                        'recurring_total' => $pricingBreakdown['recurring_total'] ?? 0,
                        'vat' => $pricingBreakdown['vat'] ?? 0,
                        'total_amount' => $pricingBreakdown['total_amount'] ?? $intent->amount,
                        'device_breakdown' => $pricingBreakdown['device_breakdown'] ?? [],
                        'services_breakdown' => $pricingBreakdown['services_breakdown'] ?? []
                    ]),
                    
                    // Device and service details for line item generation
                    'selected_devices' => json_encode($selectedDevices),
                    'selected_biometric_services' => json_encode($selectedBiometricServices),
                    
                    // Company and user details
                    'company_details' => json_encode([
                        'company_name' => $company->name,
                        'company_code' => $company->code,
                        'industry' => $intent->industry,
                        'country' => $intent->country,
                        'state' => $intent->state,
                        'city' => $intent->city,
                        'subdomain' => $intent->subdomain
                    ]),
                    
                    'user_details' => json_encode([
                        'first_name' => $intent->first_name,
                        'last_name' => $intent->last_name,
                        'email' => $intent->email,
                        'phone' => $intent->phone,
                        'username' => $intent->username
                    ])
                ]
            ];

            Log::info('Triggering GitHub Actions workflow', [
                'company_code' => $company->code,
                'domain' => strtolower($company->code) . '.timora.ph',
                'dispatch_url' => $dispatchUrl
            ]);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/vnd.github+json',
                    'Authorization' => 'Bearer ' . $githubToken,
                    'X-GitHub-Api-Version' => '2022-11-28',
                    'User-Agent' => 'JAF-Wizard-Backend/1.0',
                ])
                ->post($dispatchUrl, $payload);

            if ($response->successful()) {
                Log::info('✅ GitHub Actions workflow triggered successfully', [
                    'company_code' => $company->code,
                    'status_code' => $response->status(),
                    'callback_url' => config('app.url') . '/api/wizard/provisioning/github-callback',
                    'workflow_dispatch_id' => $response->json()['id'] ?? 'unknown'
                ]);
                return true;
            } else {
                Log::error('❌ Failed to trigger GitHub Actions workflow', [
                    'company_code' => $company->code,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'payload' => $payload
                ]);
                
                // Update provisioning status to indicate workflow trigger failed
                ProvisioningStatus::where('company_code', $company->code)
                    ->update([
                        'status' => 'failed',
                        'current_step' => 'failed',
                        'message' => 'Failed to trigger deployment workflow',
                        'logs' => 'GitHub Actions workflow dispatch failed: ' . $response->body()
                    ]);
                    
                return false;
            }

        } catch (\Exception $e) {
            Log::error('❌ Exception triggering GitHub Actions workflow', [
                'company_code' => $company->code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Update provisioning status to indicate workflow trigger exception
            try {
                ProvisioningStatus::where('company_code', $company->code)
                    ->update([
                        'status' => 'failed',
                        'current_step' => 'failed',
                        'message' => 'Exception occurred while triggering deployment',
                        'logs' => 'GitHub Actions workflow trigger exception: ' . $e->getMessage()
                    ]);
            } catch (\Exception $updateException) {
                Log::error('Failed to update provisioning status after workflow exception', [
                    'company_code' => $company->code,
                    'update_error' => $updateException->getMessage()
                ]);
            }
            
            return false;
        }
    }

    /**
     * Send payment processed email notification
     * Dedicated API endpoint for sending payment notification emails
     */
    public function sendPaymentEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'company_name' => 'required|string',
            'company_code' => 'required|string',
            'subdomain' => 'required|string',
            'system' => 'required|string',
            'plan' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Get email details from request input
        $email = $request->input('email');
        $companyName = $request->input('company_name');
        $companyCode = $request->input('company_code');
        $subdomain = $request->input('subdomain');
        $system = $request->input('system');
        $plan = $request->input('plan');
        $username = $request->input('username');
        $password = $request->input('password');

        Log::info('Processing payment email request', [
            'recipient' => $email,
            'company_name' => $companyName,
            'subdomain' => $subdomain,
            'system' => $system,
            'plan' => $plan
        ]);

        // Send email notification
        try {
            Mail::to($email)->send(new PaymentProcessed(
                $email,
                $companyName,
                $companyCode,
                $subdomain,
                $system,
                $plan,
                $username,
                $password
            ));
            
            Log::info('✅ Payment processed email sent successfully', [
                'recipient' => $email,
                'company' => $companyName,
                'subdomain' => $subdomain
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment email sent successfully',
                'data' => [
                    'recipient' => $email,
                    'company_name' => $companyName,
                    'subdomain' => $subdomain,
                    'sent_at' => now()->toISOString()
                ]
            ]);

        } catch (Exception $e) {
            Log::error('❌ Failed to send payment processed email', [
                'recipient' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send payment email: ' . $e->getMessage()
            ], 500);
        }
    }

}
