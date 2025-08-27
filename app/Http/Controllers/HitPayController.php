<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HitPayController extends Controller
{
    //
       /**
     * Create HitPay Payment Request and save our intent record.
     */
    public function createPaymentRequest(Request $request)
    {
        DB::beginTransaction();

        try {
        // Look up the plan for this system by matching the system_id and plan slug
            $plan = Plan::where('system_id', $systemId)
                        ->where('slug', $planSlug)
                        ->first();

            if (!$plan) {
                return response()->json(['success' => false, 'message' => 'Invalid plan for the selected system.'], 400);
            }


            // 1. Prepare unique reference number
            $reference = $request->input('reference_number') ?? ('checkout_' . now()->timestamp);

            // 2. Prepare payment details
            $amount = 1.00; // For demo, or use $request->input('totals.total')
            $buyerEmail = $request->input('email'); // user email
            $buyerName = trim($request->input('firstName') . ' ' . $request->input('lastName')); // from personal details
            $purpose = 'Get started with your ' . $request->input('system') . ' subscription for Payroll Timora PH today.';
            $redirectUrl = env('HITPAY_REDIRECT_URL', config('app.url') . '/payment-success');
            $webhookUrl = env('HITPAY_WEBHOOK_URL');

            // 3. Prepare full payload for storage
            $payload = $request->all();

            //Get the phone number from the request
          	$buyerPhone = $request->input('phoneNumber');

            // 4. Call HitPay API (optional: skip if you only want to log intent, call on actual payment)
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
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
            ]);

            $hitpayData = json_decode($response->getBody(), true);

            // 5. Store payment intent as BranchSubscription and Payment

            // 5.1. Create BranchSubscription (intent)
            $branchSubscription = BranchSubscription::create([
                'branch_id'             => $request->input('branch_id'), 
                'plan'                  => $planSlug,
                'plan_details'          => json_encode($request->input('selectedAddOns', [])),
                'amount_paid'           => $amount,
                'currency'              => env('HITPAY_CURRENCY', 'PHP'),
                'payment_status'        => 'pending',
                'subscription_start'    => null,
                'subscription_end'      => null,
                'trial_start'           => $request->input('isTrial', false) ? now() : null,
                'trial_end'             => $request->input('isTrial', false) ? now()->addDays(7) : null,
                'status'                => 'pending',
                'payment_gateway'       => 'hitpay',
                'transaction_reference' => $reference,
                'raw_response'          => json_encode($payload),
                'mobile_number'         => $buyerPhone,
            ]);

            // 5.2. Create Payment record
            $payment = Payment::create([
                'branch_subscription_id' => $branchSubscription->id,
                'amount'                 => $amount,
                'currency'               => env('HITPAY_CURRENCY', 'PHP'),
                'status'                 => 'pending',
                'payment_gateway'        => 'hitpay',
                'transaction_reference'  => $reference,
                'gateway_response'       => $hitpayData,
                'payment_method'         => $request->input('paymentMethod', 'hitpay'),
                'payment_provider'       => $hitpayData['payment_provider']['code'] ?? null,
                'checkout_url'           => $hitpayData['url'] ?? null,
                'receipt_url'            => $hitpayData['receipt_url'] ?? null,
                'paid_at'                => null,
                'notes'                  => null,
            ]);
            

            DB::commit();

            return response()->json([
                'success' => true,
                'url' => $hitpayData['url'] ?? null,
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
            $user = User::create([
                'company_id' => $company->id,
                'email'      => $intent->email,
                'password'   => $intent->password,  // Use bcrypt() or hash method as needed
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
            $subscription = Subscription::create([
                'user_id'               => $user->id,  // Associate with the created user
                'company_id'            => $company->id,  // Associate with the company
                'company_system_id'     => $companySystem->id,
                'system_id'             => $intent->system_id, // Use system_id from the intent
                'plan_id'               => $intent->plan_id,   // Use plan ID from the intent
                'billing_period'        => $intent->billing_period,
                'status'                => $subscriptionStatus,  // Set based on payment status
                'is_trial'              => $intent->is_trial,
                'additional_employees'  => $intent->additional_employees ?? 0, // <-- Add this line
                'trial_ends_at'         => $intent->is_trial ? now()->addDays(7) : null,
                'start_date'            => now(),
                'end_date'              => now()->addMonths(1),  // Adjust as necessary
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
                        // Find the add-on by its key
                        $addon = Addon::where('addon_key', $addonKey)->first();
                        
                        if ($addon) {
                            DB::table('company_addons')->insert([
                                'company_system_id' => $companySystem->id,
                                'addon_id'          => $addon->id,  // Get the addon_id based on addon_key
                                'active'            => true,
                                'start_date'        => now(),
                                'end_date'          => now()->addMonths(1), // You can adjust this based on your logic
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
                
            if (!$intent) {
                Log::warning('PaymentIntent not found', ['reference' => $referenceNumber]);
                return;
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

                        // Extract customer ID
                        'gateway_customer_id' => $webhookData['customer']['id'] 
                                                ?? ($webhookData['customer_id'] ?? null),

                        // Extract payment_request details from gateway_response
                        'payment_request_id'     => $webhookData['payment_request']['id'] ?? null,
                        'payment_request_status' => $webhookData['payment_request']['status'] ?? null,

                        'gateway_response'  => json_encode($webhookData),
                        'updated_at'        => now(),
                        'created_at'        => now(),
                    ]
                );


            DB::commit();
            Log::info('Payment stored successfully', ['reference' => $referenceNumber]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment storage failed', [
                'reference' => $referenceNumber,
                'error' => $e->getMessage()
            ]);
        }
    }

}
