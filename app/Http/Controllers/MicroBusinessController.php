<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\BranchAddon;
use App\Models\Department;
use App\Models\Designation;
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


    public function registerBranchWithVat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'referral_code'   => 'required|string|exists:tenants,tenant_code',
            'full_name'       => 'required|string|max:255',
            'middle_name'     => 'nullable|string|max:255',
            'suffix'          => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'username'        => 'required|string|max:255|unique:users,username',
            'email'           => 'required|email|max:255|unique:users,email',
            'password'        => 'required|string|min:6|same:confirm_password',
            'confirm_password' => 'required|string|min:6',
            'role_id'         => 'required|integer|exists:role,id',
            'phone_number'    => 'nullable|string|max:255',
            'branch_name'     => 'required|string|max:255',
            'branch_location' => 'required|string|max:500',
            'total_employees' => 'required|integer|min:1',
            'billing_period'  => 'required|string|in:monthly,annual',
            'is_trial'        => 'sometimes|boolean',
            'plan_slug'       => 'nullable|string',
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

        $fullName = trim($request->input('full_name'));
        $nameParts = preg_split('/\s+/', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        DB::beginTransaction();

        try {
            $tenant = $this->findTenant($request->input('referral_code'));
            if (!$tenant) {
                return response()->json(['message' => 'No matching tenant found for the provided referral code.'], 404);
            }

            $branch = $this->createBranch($tenant->id, $request->branch_name, $request->branch_location);

            $user = $this->createUser($request, $tenant->id);

            $this->assignUserPermission($user->id, $request->role_id);

            $profileImagePath = $this->handleProfilePicture($request);

            $epi = $this->createEmploymentPersonalInfo($user->id, $firstName, $lastName, $request, $profileImagePath, $branch->id);

            $this->createEmploymentDetail($user->id, $branch->id, $epi->id);

            // Create department and designation
            $department = $this->createDepartment($branch->id, null, $user->id);
            $designation = $this->createDesignation($department->id);

            [$addons, $featureInputs] = $this->resolveAddons($request);

            [$planDetails, $final, $addonsPrice, $employeePrice, $vat, $billingPeriod, $totalEmployees, $pricePerEmployee] = $this->calculatePlanDetailsWithVat($request, $addons);

            [$trialStart, $trialEnd, $subStart, $subEnd, $isTrial] = $this->calculateTrialAndSubscriptionWindows($request, $billingPeriod);

            $hitpayData = $this->createHitpayPayment($final, $request, $fullName);

            $branchSubscription = $this->createBranchSubscriptionWithVat($branch->id, $request, $planDetails, $final, $trialStart, $trialEnd, $subStart, $subEnd, $isTrial, $tenant->id, $addonsPrice, $employeePrice, $vat);

            $this->createPaymentRecord($branchSubscription->id, $final, $hitpayData);

            $this->createBranchAddons($addons, $featureInputs, $branch->id);

            DB::commit();

            return response()->json([
                'status'               => 'success',
                'message'              => 'Branch, user, subscription, payment, and add-ons created successfully.',
                'branch'               => $branch,
                'subscription'         => $branchSubscription,
                'payment_checkout_url' => $hitpayData['url'] ?? null,
                'department'           => $department,
                'designation'          => $designation,
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

    
    // Helper methods for registerBranchWithVat

    private function findTenant($referralCode)
    {
        return Tenant::where('tenant_code', $referralCode)->first();
    }

    private function createBranch($tenantId, $branchName, $branchLocation)
    {
        return Branch::create([
            'tenant_id' => $tenantId,
            'name'      => $branchName,
            'location'  => $branchLocation,
        ]);
    }

    private function createUser($request, $tenantId)
    {
        $user = new User();
        $user->username  = $request->username;
        $user->tenant_id = $tenantId;
        $user->email     = $request->email;
        $user->password  = bcrypt($request->password);
        $user->save();
        return $user;
    }

    private function assignUserPermission($userId, $roleId)
    {
        // Get the role by ID and its tenant_id
        $role = Role::find($roleId);

        if (!$role) {
            throw new \Exception('Role not found.');
        }

        // Get the user to determine the correct tenant_id
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('User not found.');
        }

        // Get the Admin role for the user's tenant
        $adminRole = Role::where('tenant_id', $user->tenant_id)
            ->where('role_name', 'Admin')
            ->first();

        if (!$adminRole) {
            throw new \Exception('Admin role not found for tenant.');
        }

        $userPermission = new UserPermission([
            'user_id' => $userId,
            'role_id' => $adminRole->id,
            'data_access_id' => 2,
            'menu_ids' => $adminRole->menu_ids ?? '1,2,3,4,5',
            'module_ids' => $adminRole->module_ids ?? '1,3,4,6,7,10,11,13,19',
            'user_permission_ids' => $adminRole->role_permission_ids ?? '2-1,2-2,2-3,2-4,2-5,2-6,8-1,8-2,8-3,8-4,8-5,8-6,9-1,9-2,9-3,9-4,9-5,9-6,10-1,10-2,10-3,10-4,10-5,10-6,11-1,11-2,11-3,11-4,11-5,11-6,53-1,53-2,53-3,53-4,53-5,53-6,57-1,57-2,57-3,57-4,57-5,57-6,14-1,14-2,14-3,14-4,14-5,14-6,15-1,15-2,15-3,15-4,15-5,15-6,17-1,17-2,17-3,17-4,17-5,17-6,45-1,45-2,45-3,45-4,45-5,45-6,19-1,19-2,19-3,19-4,19-5,19-6,20-1,20-2,20-3,20-4,20-5,20-6,24-1,24-2,24-3,24-4,24-5,24-6,25-1,25-2,25-3,25-4,25-5,25-6,26-1,26-2,26-3,26-4,26-5,26-6,27-1,27-2,27-3,27-4,27-5,27-6,30-1,30-2,30-3,30-4,30-5,30-6,54-1,54-2,54-3,54-4,54-5,54-6,55-1,55-2,55-3,55-4,55-5,55-6,56-1,56-2,56-3,56-4,56-5,56-6',
            'status' => 1,
        ]);
        $userPermission->save();
    }

    private function createDepartment($branchId, $departmentName = null, $userId = null)
    {
        // Use a default department name common to all industries if not provided
        // Examples: "Operations" is generic for public market, milktea, and most micro businesses
        $defaultDepartmentName = 'Operations';

        $departmentName = $departmentName ?: $defaultDepartmentName;

        // Generate a unique department code
        $departmentCode = 'DEPT-' . strtoupper(bin2hex(random_bytes(4)));

        return Department::create([
            'branch_id'           => $branchId,
            'department_code'     => $departmentCode,
            'department_name'     => $departmentName,
            'status'              => 'active',
            'head_of_department'  => $userId, 
        ]);
    }

    private function createDesignation($departmentId, $designationName = null, $jobDescription = null, $status = 'active')
    {
        // Use a default designation name if not provided
        $defaultDesignationName = 'Operations Manager';
        $designationName = $designationName ?: $defaultDesignationName;

        // Use a default job description if not provided
        $defaultJobDescription = 'Responsible for overseeing daily operations and ensuring business efficiency.';
        $jobDescription = $jobDescription ?: $defaultJobDescription;

        return Designation::create([
            'department_id'     => $departmentId,
            'designation_name'  => $designationName,
            'job_description'   => $jobDescription,
            'status'            => $status,
        ]);
    }

    private function handleProfilePicture($request)
    {
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
        return $profileImagePath;
    }

    private function createEmploymentPersonalInfo($userId, $firstName, $lastName, $request, $profileImagePath, $branchId)
    {
        return EmploymentPersonalInformation::create([
            'user_id'         => $userId,
            'first_name'      => $firstName,
            'last_name'       => $lastName,
            'middle_name'     => $request->middle_name,
            'suffix'          => $request->suffix,
            'profile_picture' => $profileImagePath,
            'phone_number'    => $request->phone_number,
            'branch_id'       => $branchId,
        ]);
    }

    private function createEmploymentDetail($userId, $branchId, $employeeId)
    {
        EmploymentDetail::create([
            'user_id'    => $userId,
            'branch_id'  => $branchId,
            'employee_id' => $employeeId,
            'status'     => 1,
        ]);
    }

    private function resolveAddons($request)
    {
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
        return [$addons, $featureInputs];
    }

    private function calculatePlanDetailsWithVat($request, $addons)
    {
        $billingPeriod = $request->input('billing_period', 'monthly');
        $totalEmployees   = (int) $request->input('total_employees');
        $pricePerEmployee = 43.75;

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

        return [$planDetails, $final, $addonsPrice, $employeePrice, $vat, $billingPeriod, $totalEmployees, $pricePerEmployee];
    }

    private function calculateTrialAndSubscriptionWindows($request, $billingPeriod)
    {
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

        return [$trialStart, $trialEnd, $subStart, $subEnd, $isTrial];
    }

    private function createHitpayPayment($final, $request, $buyerName)
    {
        $planSlug     = $request->input('plan_slug', 'starter');
        $amount       = round($final, 2);
        $reference    = 'checkout_' . now()->timestamp;
        $buyerEmail   = $request->input('email');
        $buyerPhone   = $request->input('phone_number');
        $purpose      = 'Get started with your subscription for Payroll Timora PH today.';
        $redirectUrl  = env('HITPAY_REDIRECT_URL', config('app.url') . '/payment-success');
        $webhookUrl   = env('HITPAY_WEBHOOK_URL');

        $hitpayData = null;
        try {
            $client = new \GuzzleHttp\Client();
            $hitpayPayload = [
                'amount'           => 1,
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
        return $hitpayData;
    }

    private function createBranchSubscriptionWithVat($branchId, $request, $planDetails, $amount, $trialStart, $trialEnd, $subStart, $subEnd, $isTrial, $tenantId, $addonsPrice, $employeePrice, $vat)
    {
        return BranchSubscription::create([
            'branch_id'             => $branchId,
            'plan'                  => $request->input('plan_slug', 'starter'),
            'plan_details'          => $planDetails,
            'amount_paid'           => $amount,
            'currency'              => env('HITPAY_CURRENCY', 'PHP'),
            'payment_status'        => 'pending',
            'subscription_start'    => $subStart,
            'subscription_end'      => $subEnd,
            'trial_start'           => $trialStart,
            'trial_end'             => $trialEnd,
            'status'                => 'trial',
            'payment_gateway'       => 'hitpay',
            'transaction_reference' => 'checkout_' . now()->timestamp,
            'notes'                 => null,
            'mobile_number'         => $request->input('phone_number'),
            'total_employee'        => (int) $request->input('total_employees'),
            'tenant_id'             => $tenantId,
            'billing_period'        => $request->input('billing_period', 'monthly'),
            'is_trial'              => $isTrial,
            'employee_credits'      => (int) $request->input('employee_credits', 11),
        ]);
    }

    private function createPaymentRecord($branchSubscriptionId, $amount, $hitpayData)
    {
        Payment::create([
            'branch_subscription_id' => $branchSubscriptionId,
            'amount'                 => $amount,
            'currency'               => env('HITPAY_CURRENCY', 'PHP'),
            'status'                 => 'pending',
            'payment_gateway'        => 'hitpay',
            'transaction_reference'  => $hitpayData['reference_number'] ?? null,
            'gateway_response'       => $hitpayData ? json_encode($hitpayData) : null,
            'payment_method'         => 'hitpay',
            'payment_provider'       => $hitpayData['payment_provider']['code'] ?? null,
            'checkout_url'           => $hitpayData['url'] ?? null,
            'receipt_url'            => $hitpayData['receipt_url'] ?? null,
            'paid_at'                => null,
            'notes'                  => 'Payment pending for subscription',
        ]);
    }

    private function createBranchAddons($addons, $featureInputs, $branchId)
    {
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
                    ['branch_id' => $branchId, 'addon_id' => $a->id],
                    [
                        'active'     => true,
                        'start_date' => $start ?: now(),
                        'end_date'   => $end ?: null,
                    ]
                );
            }
        }
    }


    public function branchSubscriptions()
    {
        $subscriptions = BranchSubscription::with([
            'branch:id,tenant_id,name,location',
            'branch.tenant:id,tenant_code,tenant_name',
            'branch.branchAddons.addon',
            'branch.employmentDetail.user.personalInformation'
        ])->get();

        $formatted = $subscriptions->map(function ($subscription) {
            $branch = $subscription->branch;

            // Get branch add-ons
            $addons = [];
            if ($branch && $branch->branchAddons) {
                foreach ($branch->branchAddons as $branchAddon) {
                    if ($branchAddon->addon) {
                        $addons[] = [
                            'id' => $branchAddon->addon->id,
                            'addon_key' => $branchAddon->addon->addon_key,
                            'name' => $branchAddon->addon->name,
                            'price' => $branchAddon->addon->price,
                            'type' => $branchAddon->addon->type,
                            'description' => $branchAddon->addon->description,
                            'active' => $branchAddon->active,
                            'start_date' => $branchAddon->start_date,
                            'end_date' => $branchAddon->end_date,
                        ];
                    }
                }
            }

            // Get total employees from subscription
            $totalEmployees = $subscription->total_employee ?? 0;

            // Get tenant info
            $tenant = $branch && $branch->tenant ? [
                'tenant_code' => $branch->tenant->tenant_code,
                'tenant_name' => $branch->tenant->tenant_name,
            ] : null;

            // Fetch users outside company
            $users = [];
            if ($branch && $branch->employmentDetail) {
                foreach ($branch->employmentDetail as $employment) {
                    $user = $employment->user;
                    $info = $user->personalInformation;
                    $users[] = [
                        'id' => $user->id,
                        'first_name' => $info->first_name ?? null,
                        'last_name' => $info->last_name ?? null,
                        'email' => $user->email,
                        'phone' => $info->phone_number ?? null,
                    ];
                }
            }

            return [
                'plan' => $subscription->plan,
                'amount_paid' => $subscription->amount_paid,
                'subscription_start' => $subscription->subscription_start,
                'subscription_end' => $subscription->subscription_end,
                'billing_period' => $subscription->billing_period,
                'status' => $subscription->status,
                'addons' => $addons,
                'total_employees' => $totalEmployees,
                'affiliate' => $tenant,
                'company' => [
                    'name' => $branch->name ?? null,
                    'location' => $branch->location ?? null,
                ],
                'users' => $users,
            ];
        });

        return response()->json([
            'subscriptions' => $formatted,
        ]);
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

    public function addEmployeeCredits(Request $request, $branchId)
    {
        // ✅ Validate input
        $validator = Validator::make($request->all(), [
            'additional_credits' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('additional_credits') ?? 'Invalid input.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $additionalCredits = (int) $request->input('additional_credits');

        // ✅ Find active subscription
        $subscription = BranchSubscription::where('branch_id', $branchId)
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found for this branch.',
            ], 404);
        }

        // ✅ Calculate amount
        $pricePerEmployee = 43.75;
        $amount = $additionalCredits * $pricePerEmployee;

        if ($subscription->billing_period === 'annual') {
            $amount *= 12;
        }

        $vat = $amount * 0.12;
        $finalAmount = round($amount + $vat, 2);

        // ✅ Buyer details
        $buyerName = optional($subscription->branch)->name ?? 'Branch';
        $buyerEmail = optional($subscription->branch->employmentDetail->first()->user)->email ?? null;
        $buyerPhone = optional($subscription->branch->employmentDetail->first()->user->personalInformation)->phone_number ?? null;

        $reference = 'addcredits_' . now()->timestamp;
        $purpose = "Add $additionalCredits employee credits to business $buyerName";

        // ✅ Send payment request to Hitpay
        try {
            $client = new \GuzzleHttp\Client();

            $hitpayPayload = [
                'amount'           => 1,
                'currency'         => env('HITPAY_CURRENCY', 'PHP'),
                'email'            => $buyerEmail,
                'name'             => $buyerName,
                'phone'            => $buyerPhone,
                'purpose'          => $purpose,
                'reference_number' => $reference,
                'redirect_url'     => env('HITPAY_SUCCESS_URL'),
                'webhook'          => env('HITPAY_WEBHOOK_URL'),
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

            // ✅ Store payment in DB
            $payment = Payment::create([
                'branch_subscription_id' => $subscription->id,
                'amount'                 => $finalAmount,
                'currency'               => env('HITPAY_CURRENCY', 'PHP'),
                'status'                 => 'pending',
                'payment_gateway'        => 'hitpay',
                'transaction_reference'  => $hitpayData['reference_number'] ?? $reference,
                'gateway_response'       => $hitpayData,
                'payment_method'         => 'hitpay',
                'payment_provider'       => $hitpayData['payment_provider']['code'] ?? null,
                'checkout_url'           => $hitpayData['url'] ?? null,
                'receipt_url'            => $hitpayData['receipt_url'] ?? null,
                'meta'                   => [
                    'type' => 'employee_credits',
                    'additional_credits' => $additionalCredits,
                ],
            ]);

            return response()->json([
                'success'     => true,
                'message'     => 'Payment created. Complete payment to add credits.',
                'checkoutUrl' => $hitpayData['url'] ?? null,
                'payment_id'  => $payment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Hitpay payment creation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed. Please try again later.',
            ], 500);
        }
    }



    

}