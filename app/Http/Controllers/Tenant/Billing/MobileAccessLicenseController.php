<?php

namespace App\Http\Controllers\Tenant\Billing;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MobileAccessLicense;
use App\Models\MobileAccessAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use App\Services\HitPayService;

class MobileAccessLicenseController extends Controller
{
    /**
     * Get the authenticated user (supports both web and global auth).
     */
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    /**
     * Display the mobile access license management page.
     */
    public function index(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;
        
        // Get permissions for the mobile access license module
        $permissions = PermissionHelper::get(58); // Assuming sub-module ID 58 for Mobile Access License

        // Get the license pool for this tenant (create if doesn't exist)
        $licensePool = MobileAccessLicense::forTenant($tenantId)->active()->first();
        
        if (!$licensePool) {
            $licensePool = MobileAccessLicense::create([
                'tenant_id' => $tenantId,
                'total_licenses' => 0,
                'used_licenses' => 0,
                'available_licenses' => 0,
                'license_price' => 49.00,
                'status' => 'active',
                'created_by_type' => get_class($authUser),
                'created_by_id' => $authUser->id,
            ]);
        }

        // Get all users for this tenant (both employees and global users)
        $tenantEmployees = User::where('tenant_id', $tenantId)
            ->with(['personalInformation', 'employmentDetail.department', 'employmentDetail.branch'])
            ->get();
        
        // Get global users associated with this tenant
        $globalUsers = \App\Models\GlobalUser::where('tenant_id', $tenantId)
            ->get()
            ->map(function($globalUser) {
                // Transform global user to match employee structure
                $fullName = trim(($globalUser->first_name ?? '') . ' ' . ($globalUser->last_name ?? ''));
                if (empty($fullName)) {
                    $fullName = $globalUser->username; // Fallback to username
                }
                
                $globalUser->personalInformation = (object)[
                    'full_name' => $fullName,
                    'first_name' => $globalUser->first_name ?: $globalUser->username,
                    'last_name' => $globalUser->last_name ?: '',
                ];
                $globalUser->employmentDetail = (object)[
                    'employee_id' => 'ADMIN-' . str_pad($globalUser->id, 3, '0', STR_PAD_LEFT),
                    'department' => (object)['department_name' => 'Administration'],
                    'designation' => (object)['designation_name' => 'Global Admin'],
                    'branch' => null,
                ];
                $globalUser->user_type = 'global_admin';
                return $globalUser;
            });
        
        // Combine both user types and paginate
        $allUsers = $tenantEmployees->concat($globalUsers);
        $employees = new \Illuminate\Pagination\LengthAwarePaginator(
            $allUsers->forPage(request('page', 1), 15),
            $allUsers->count(),
            15,
            request('page', 1),
            ['path' => request()->url(), 'pageName' => 'page']
        );

        // Get mobile access assignments for this tenant
        $rawAssignments = MobileAccessAssignment::forTenant($tenantId)
            ->orderBy('status', 'asc')
            ->orderBy('assigned_at', 'desc')
            ->get();

        // Manually load user data for both tenant users and global users
        $assignments = $rawAssignments->map(function ($assignment) {
            // Try to find as tenant user first
            $tenantUser = User::with(['personalInformation', 'employmentDetail.department', 'employmentDetail.designation'])
                ->find($assignment->user_id);
            
            if ($tenantUser) {
                $assignment->user = $tenantUser;
                $assignment->user_type = 'tenant_user';
            } else {
                // Try to find as global user
                $globalUser = \App\Models\GlobalUser::find($assignment->user_id);
                if ($globalUser) {
                    // Transform global user to match expected structure
                    $fullName = trim(($globalUser->first_name ?? '') . ' ' . ($globalUser->last_name ?? ''));
                    if (empty($fullName)) {
                        $fullName = $globalUser->username; // Fallback to username
                    }
                    
                    $globalUser->personalInformation = (object)[
                        'full_name' => $fullName,
                        'first_name' => $globalUser->first_name ?: $globalUser->username,
                        'last_name' => $globalUser->last_name ?: '',
                    ];
                    $globalUser->employmentDetail = (object)[
                        'employee_id' => 'ADMIN-' . str_pad($globalUser->id, 3, '0', STR_PAD_LEFT),
                        'department' => (object)['department_name' => 'Administration'],
                        'designation' => (object)['designation_name' => 'Global Admin'],
                    ];
                    $assignment->user = $globalUser;
                    $assignment->user_type = 'global_user';
                }
            }
            
            return $assignment;
        })->filter(function ($assignment) {
            // Only keep assignments where we found the user
            return isset($assignment->user);
        });

        // Get summary statistics
        $stats = [
            'total_employees' => User::where('tenant_id', $tenantId)->count(),
            'employees_with_mobile_access' => $assignments->where('status', 'active')->count(),
            'total_licenses' => $licensePool->total_licenses,
            'available_licenses' => $licensePool->available_licenses,
            'monthly_cost' => $licensePool->total_licenses * $licensePool->license_price,
        ];

        return view('tenant.billing.mobile-access-license', compact(
            'licensePool',
            'employees', 
            'assignments',
            'stats',
            'permissions'
        ));
    }

    /**
     * Purchase additional mobile access licenses.
     */
    public function purchaseLicenses(Request $request)
    {
        try {
            $request->validate([
                'license_count' => 'required|integer|min:1|max:100',
            ]);

            $authUser = $this->authUser();
            $tenantId = $authUser->tenant_id;

            DB::beginTransaction();

            $licensePool = MobileAccessLicense::forTenant($tenantId)->active()->first();
            
            if (!$licensePool) {
                $licensePool = MobileAccessLicense::create([
                    'tenant_id' => $tenantId,
                    'total_licenses' => 0,
                    'used_licenses' => 0,
                    'available_licenses' => 0,
                    'license_price' => 49.00,
                    'status' => 'active',
                    'created_by_type' => get_class($authUser),
                    'created_by_id' => $authUser->id,
                ]);
            }

            $licenseCount = $request->license_count;
            $licensePrice = $licensePool->license_price;
            $subtotal = $licenseCount * $licensePrice;
            $vatPercentage = 12.0; // 12% VAT
            $vatAmount = $subtotal * ($vatPercentage / 100);
            $totalCost = $subtotal + $vatAmount;

            // Create an invoice for the mobile access license purchase
            $invoiceNumber = 'MAL-' . strtoupper(uniqid());
            
            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'subscription_id' => null, // No subscription for mobile access licenses
                'invoice_type' => 'license_overage',
                'invoice_number' => $invoiceNumber,
                'amount_due' => $totalCost,
                'amount_paid' => 0,
                'currency' => 'PHP',
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'vat_percentage' => $vatPercentage,
                'status' => 'pending',
                'due_date' => now()->addDays(7),
                'issued_at' => now(),
                'license_overage_count' => $licenseCount, // Reuse this field for mobile access count
                'license_overage_rate' => $licensePrice,
                'license_overage_amount' => $subtotal,
            ]);

            // Create HitPay payment request
            $hitPayService = new HitPayService();
            $paymentResult = $hitPayService->createPaymentRequest(
                $invoice,
                route('billing.mobile-access-license.payment-return', ['invoice' => $invoice->id])
            );

            if ($paymentResult['success']) {
                DB::commit();
                
                Log::info('Mobile access license invoice created for HitPay payment', [
                    'tenant_id' => $tenantId,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoiceNumber,
                    'license_count' => $licenseCount,
                    'total_cost' => $totalCost,
                    'payment_url' => $paymentResult['payment_url'],
                    'transaction_id' => $paymentResult['transaction_id'],
                ]);

                return response()->json([
                    'success' => true,
                    'requires_payment' => true,
                    'payment_url' => $paymentResult['payment_url'],
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoiceNumber,
                    'transaction_id' => $paymentResult['transaction_id'],
                    'amount' => $totalCost,
                    'license_count' => $licenseCount,
                    'message' => 'Invoice created. Redirecting to payment...',
                ]);
            } else {
                // Check if this is a network/configuration issue for development mode
                $error = $paymentResult['error'];
                $isNetworkError = strpos($error, 'Could not resolve host') !== false 
                    || strpos($error, 'connect to HitPay') !== false;
                
                if ($isNetworkError && app()->environment(['local', 'development', 'testing'])) {
                    // For development mode, simulate successful payment flow
                    DB::rollBack();
                    
                    Log::warning('HitPay unavailable in development mode, simulating payment', [
                        'tenant_id' => $tenantId,
                        'invoice_id' => $invoice->id,
                        'error' => $error
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'development_mode' => true,
                        'message' => 'HitPay API is not available. In production, this would redirect to payment gateway.',
                        'error_details' => $error,
                    ], 503);
                }
                
                DB::rollBack();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment request: ' . $error,
                ], 500);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to purchase mobile access licenses', [
                'tenant_id' => $authUser->tenant_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create license purchase: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle payment return from HitPay for mobile access license purchase.
     */
    public function paymentReturn($invoiceId)
    {
        try {
            $authUser = $this->authUser();
            $invoice = Invoice::where('id', $invoiceId)
                ->where('tenant_id', $authUser->tenant_id)
                ->where('invoice_type', 'license_overage')
                ->with(['paymentTransactions'])
                ->first();

            if (!$invoice) {
                return redirect()->route('billing.mobile-access-license')
                    ->with('error', 'Mobile access license invoice not found');
            }

            // Get the latest transaction for this invoice
            $transaction = $invoice->paymentTransactions()->latest()->first();

            if ($transaction) {
                // Check payment status from HitPay
                $hitPayService = new HitPayService();
                $statusResult = $hitPayService->getPaymentStatus($transaction->transaction_reference);

                if ($statusResult['success']) {
                    $paymentStatus = strtolower($statusResult['status']);

                    // Map HitPay status
                    $mappedStatus = match ($paymentStatus) {
                        'completed', 'succeeded', 'success' => 'paid',
                        'failed', 'error' => 'failed',
                        default => 'pending'
                    };

                    // Update transaction status
                    $transaction->update([
                        'status' => $mappedStatus,
                        'last_status_check' => now(),
                    ]);

                    // If payment completed, process the license purchase
                    if (in_array($paymentStatus, ['completed', 'succeeded', 'success'])) {
                        $this->processMobileAccessLicensePayment($invoice, $transaction);

                        return redirect()->route('billing.mobile-access-license')
                            ->with('success', 'Mobile access licenses purchased successfully!');
                    } else if (in_array($paymentStatus, ['failed', 'error'])) {
                        return redirect()->route('billing.mobile-access-license')
                            ->with('error', 'Payment failed. Please try again.');
                    }
                }
            }

            return redirect()->route('billing.mobile-access-license')
                ->with('warning', 'Payment status could not be verified. Please contact support.');

        } catch (\Exception $e) {
            Log::error('Mobile access license payment return processing failed: ' . $e->getMessage());

            return redirect()->route('billing.mobile-access-license')
                ->with('error', 'Payment processing failed. Please contact support.');
        }
    }

    /**
     * Process mobile access license payment after successful payment.
     */
    private function processMobileAccessLicensePayment($invoice, $transaction)
    {
        try {
            DB::beginTransaction();

            // Update invoice as paid
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'amount_paid' => $invoice->amount_due,
            ]);

            // Update transaction
            $transaction->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Add licenses to the pool
            $licensePool = MobileAccessLicense::forTenant($invoice->tenant_id)->active()->first();
            
            if ($licensePool) {
                $licenseCount = $invoice->license_overage_count; // We stored license count here
                $licensePool->addLicenses($licenseCount);
                
                Log::info('Mobile access licenses added after successful payment', [
                    'invoice_id' => $invoice->id,
                    'tenant_id' => $invoice->tenant_id,
                    'licenses_added' => $licenseCount,
                    'new_total' => $licensePool->fresh()->total_licenses,
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process mobile access license payment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Assign mobile access to an employee.
     */
    public function assignAccess(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|integer',
                'user_type' => 'required|in:tenant_user,global_user',
            ]);

            $authUser = $this->authUser();
            $tenantId = $authUser->tenant_id;

            DB::beginTransaction();

            // Find user based on type
            if ($request->user_type === 'global_user') {
                $user = \App\Models\GlobalUser::where('id', $request->user_id)
                    ->where('tenant_id', $tenantId)
                    ->first();
            } else {
                $user = User::where('id', $request->user_id)
                    ->where('tenant_id', $tenantId)
                    ->first();
            }

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            // Check if user already has active mobile access
            $existingAssignment = MobileAccessAssignment::forTenant($tenantId)
                ->forUser($user->id)
                ->active()
                ->first();

            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee already has active mobile access.',
                ], 409);
            }

            $licensePool = MobileAccessLicense::forTenant($tenantId)->active()->first();

            if (!$licensePool || !$licensePool->canAssignLicense()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No available mobile access licenses. Please purchase more licenses.',
                ], 400);
            }

            // Create the assignment
            $branchId = null;
            if ($request->user_type === 'tenant_user' && isset($user->employmentDetail)) {
                $branchId = $user->employmentDetail->branch_id ?? null;
            }

            $assignment = MobileAccessAssignment::create([
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'mobile_access_license_id' => $licensePool->id,
                'branch_id' => $branchId,
                'status' => 'active',
                'assigned_at' => now(),
                'assigned_by_type' => get_class($authUser),
                'assigned_by_id' => $authUser->id,
            ]);

            DB::commit();

            // Get user name based on user type
            if ($request->user_type === 'global_user') {
                $employeeName = $user->first_name . ' ' . $user->last_name;
            } else {
                $employeeName = $user->personalInformation->full_name ?? $user->username;
            }
            
            return response()->json([
                'success' => true,
                'message' => "Mobile access successfully assigned to {$employeeName}.",
                'assignment' => $assignment->load(['user.personalInformation']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign mobile access', [
                'tenant_id' => $authUser->tenant_id ?? null,
                'user_id' => $request->user_id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign mobile access: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Revoke mobile access from an employee.
     */
    public function revokeAccess(Request $request)
    {
        try {
            $request->validate([
                'assignment_id' => 'required|exists:mobile_access_assignments,id',
                'reason' => 'nullable|string|max:500',
            ]);

            $authUser = $this->authUser();
            $tenantId = $authUser->tenant_id;

            DB::beginTransaction();

            $assignment = MobileAccessAssignment::where('id', $request->assignment_id)
                ->forTenant($tenantId)
                ->active()
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active mobile access assignment not found.',
                ], 404);
            }

            $assignment->revoke($request->reason, $authUser);

            DB::commit();

            $employeeName = $assignment->user->personalInformation->full_name ?? $assignment->user->username;
            
            return response()->json([
                'success' => true,
                'message' => "Mobile access revoked from {$employeeName}.",
                'assignment' => $assignment->load(['user.personalInformation']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to revoke mobile access', [
                'tenant_id' => $authUser->tenant_id ?? null,
                'assignment_id' => $request->assignment_id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke mobile access: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get mobile access status for a specific employee.
     */
    public function getEmployeeStatus($userId)
    {
        try {
            $authUser = $this->authUser();
            $tenantId = $authUser->tenant_id;

            $user = User::where('id', $userId)
                ->where('tenant_id', $tenantId)
                ->with(['personalInformation'])
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.',
                ], 404);
            }

            $assignment = MobileAccessAssignment::forTenant($tenantId)
                ->forUser($userId)
                ->active()
                ->with(['assignedBy'])
                ->first();

            return response()->json([
                'success' => true,
                'user' => $user,
                'has_mobile_access' => $assignment !== null,
                'assignment' => $assignment,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get employee mobile access status', [
                'tenant_id' => $authUser->tenant_id ?? null,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get employee status.',
            ], 500);
        }
    }

    /**
     * Filter employees for mobile access assignment.
     */
    public function filterEmployees(Request $request)
    {
        try {
            $authUser = $this->authUser();
            $tenantId = $authUser->tenant_id;

            $query = User::where('tenant_id', $tenantId)
                ->with(['personalInformation', 'employmentDetail.department', 'employmentDetail.branch']);

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('personalInformation', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhere('username', 'like', "%{$search}%");
            }

            if ($request->filled('department_id')) {
                $query->whereHas('employmentDetail', function ($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }

            if ($request->filled('mobile_access_status')) {
                $status = $request->mobile_access_status;
                if ($status === 'with_access') {
                    $query->whereHas('mobileAccessAssignments', function ($q) {
                        $q->where('status', 'active');
                    });
                } elseif ($status === 'without_access') {
                    $query->whereDoesntHave('mobileAccessAssignments', function ($q) {
                        $q->where('status', 'active');
                    });
                }
            }

            $employees = $query->paginate(15);

            return view('tenant.billing.partials.employee-list', compact('employees'))->render();

        } catch (\Exception $e) {
            Log::error('Failed to filter employees', [
                'tenant_id' => $authUser->tenant_id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to filter employees.',
            ], 500);
        }
    }

    /**
     * Test HitPay connectivity and configuration.
     */
    public function testHitPayConnection()
    {
        try {
            $config = config('services.hitpay');
            $environment = $config['environment'] ?? 'sandbox';
            
            // Determine the base URL
            if ($environment === 'production') {
                $baseUrl = 'https://api.hitpay.com/';
            } else {
                $baseUrl = 'https://api.sandbox.hitpay.com/';
            }
            
            // Override if config provides a custom URL
            if (!empty($config['base_url'])) {
                $baseUrl = $config['base_url'];
            }
            
            $diagnostics = [
                'environment' => $environment,
                'base_url' => $baseUrl,
                'api_key_configured' => !empty($config['api_key']),
                'api_key_length' => !empty($config['api_key']) ? strlen($config['api_key']) : 0,
                'salt_configured' => !empty($config['salt']),
                'webhook_url' => $config['webhook_url'] ?? 'not configured',
                'connection_test' => 'pending'
            ];
            
            // Test basic connectivity
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $baseUrl);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only
                
                $result = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                if ($result !== false && empty($error)) {
                    $diagnostics['connection_test'] = 'success';
                    $diagnostics['http_code'] = $httpCode;
                } else {
                    $diagnostics['connection_test'] = 'failed';
                    $diagnostics['curl_error'] = $error;
                    $diagnostics['http_code'] = $httpCode;
                }
            } catch (\Exception $e) {
                $diagnostics['connection_test'] = 'failed';
                $diagnostics['curl_exception'] = $e->getMessage();
            }
            
            // Test with actual HitPay service instantiation
            try {
                $hitPayService = new HitPayService();
                $diagnostics['service_instantiation'] = 'success';
            } catch (\Exception $e) {
                $diagnostics['service_instantiation'] = 'failed';
                $diagnostics['service_error'] = $e->getMessage();
            }
            
            return response()->json([
                'success' => true,
                'diagnostics' => $diagnostics,
                'message' => 'HitPay diagnostic completed'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}