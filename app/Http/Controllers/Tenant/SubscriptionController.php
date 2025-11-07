<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Plan;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    /**
     * Display subscription and billing history
     */
    public function index(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;

        // Get current subscription
        $subscription = Subscription::where('tenant_id', $tenantId)
            ->with('plan')
            ->first();

        // Get ALL invoice data for the table (including pending, failed, etc.)
        $invoices = Invoice::where('tenant_id', $tenantId)
            ->with(['subscription.plan', 'tenant'])
            ->whereIn('status', ['pending', 'paid', 'failed', 'consolidated', 'consolidated_pending', 'trial'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Calculate summary statistics
        $summaryData = $this->getSummaryData($subscription, $tenantId);

        return view('tenant.subscriptions.subscriptions', compact(
            'subscription',
            'invoices',
            'summaryData'
        ));
    }

    /**
     * Get summary data for cards
     */
    private function getSummaryData($subscription, $tenantId)
    {
        $data = [
            'subscription_name' => 'Free Trial',
            'users_current' => 1,
            'users_limit' => 100,
            'plan_cost' => 0,
            'renewal_date' => 'June 16, 2025',
        ];

        if ($subscription) {
            $data['subscription_name'] = $subscription->plan->name ?? $subscription->billing_cycle;
            $data['plan_cost'] = $subscription->plan->price ?? 0;
            
            // Get current period
            $currentPeriod = $subscription->getCurrentPeriod();
            $data['renewal_date'] = $currentPeriod['end'] ?? null;
            
            // Get active users count
            $activeUsersCount = \App\Models\User::where('tenant_id', $tenantId)
                ->where('active_license', true)
                ->count();
            
            $data['users_current'] = $activeUsersCount;
            $data['users_limit'] = $subscription->plan->license_limit ?? $subscription->active_license ?? 0;
        }

        return $data;
    }

    /**
     * Filter invoices for AJAX requests
     */
    public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;

        $query = Invoice::where('tenant_id', $tenantId)
            ->with(['subscription.plan', 'tenant'])
            ->whereIn('status', ['pending', 'paid', 'failed', 'consolidated', 'consolidated_pending', 'trial']);

        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('amount_due', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('tenant.subscriptions.subscriptions-filter', compact('invoices'));
    }

    /**
     * Get available plans for upgrade
     */
    public function getAvailablePlans(Request $request)
    {
        try {
            $authUser = $this->authUser();
            $tenantId = $authUser->tenant_id;

            // Get current subscription
            $subscription = Subscription::where('tenant_id', $tenantId)
                ->with('plan')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found'
                ], 404);
            }

            // Get current active users
            $currentActiveUsers = User::where('tenant_id', $tenantId)
                ->where('active_license', true)
                ->count();

            $currentPlan = $subscription->plan;
            $currentPlanLimit = $currentPlan->employee_limit ?? $subscription->active_license ?? 0;

            // Get all active plans that have higher limits than current plan
            $availablePlans = Plan::where('is_active', true)
                ->where('employee_limit', '>', $currentPlanLimit)
                ->where('billing_cycle', $subscription->billing_cycle) // Same billing cycle
                ->orderBy('employee_limit', 'asc')
                ->get()
                ->map(function ($plan) use ($currentPlan, $subscription, $currentActiveUsers) {
                    $currentImplFee = $subscription->implementation_fee_paid ?? 0;
                    $newImplFee = $plan->implementation_fee ?? 0;
                    $implFeeDifference = max(0, $newImplFee - $currentImplFee);

                    return [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'description' => $plan->description,
                        'price' => $plan->price,
                        'currency' => $plan->currency ?? 'PHP',
                        'employee_limit' => $plan->employee_limit,
                        'implementation_fee' => $newImplFee,
                        'impl_fee_difference' => $implFeeDifference,
                        'billing_cycle' => $plan->billing_cycle,
                        'is_recommended' => $plan->employee_limit >= ($currentActiveUsers + 10), // Recommend plans with buffer
                    ];
                });

            return response()->json([
                'success' => true,
                'current_plan' => [
                    'id' => $currentPlan->id,
                    'name' => $currentPlan->name,
                    'employee_limit' => $currentPlanLimit,
                    'implementation_fee_paid' => $subscription->implementation_fee_paid ?? 0,
                ],
                'current_active_users' => $currentActiveUsers,
                'available_plans' => $availablePlans
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching available plans: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch available plans'
            ], 500);
        }
    }

    /**
     * Process plan upgrade
     */
    public function upgradePlan(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:plans,id'
            ]);

            $authUser = $this->authUser();
            $tenantId = $authUser->tenant_id;

            // Get current subscription
            $subscription = Subscription::where('tenant_id', $tenantId)
                ->with('plan')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found'
                ], 404);
            }

            // Get new plan
            $newPlan = Plan::findOrFail($request->plan_id);

            // Validate upgrade (can only upgrade to higher tier)
            if ($newPlan->employee_limit <= $subscription->plan->employee_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only upgrade to a plan with higher employee limit'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Calculate implementation fee difference
                $currentImplFee = $subscription->implementation_fee_paid ?? 0;
                $newImplFee = $newPlan->implementation_fee ?? 0;
                $implFeeDifference = max(0, $newImplFee - $currentImplFee);

                // Create invoice for implementation fee difference (if any)
                if ($implFeeDifference > 0) {
                    $currentPeriod = $subscription->getCurrentPeriod();
                    
                    $invoice = Invoice::create([
                        'tenant_id' => $tenantId,
                        'subscription_id' => $subscription->id,
                        'invoice_number' => 'INV-UPGRADE-' . strtoupper(uniqid()),
                        'invoice_type' => 'plan_upgrade',
                        'amount_due' => $implFeeDifference,
                        'amount_paid' => 0,
                        'currency' => $newPlan->currency ?? 'PHP',
                        'status' => 'pending',
                        'due_date' => now()->addDays(7),
                        'subscription_period_start' => $currentPeriod['start'],
                        'subscription_period_end' => $currentPeriod['end'],
                        'subscription_amount' => 0,
                        'billing_cycle' => $subscription->billing_cycle,
                    ]);

                    $message = 'Upgrade initiated. Please complete payment to activate the new plan.';
                    $requiresPayment = true;
                } else {
                    // No implementation fee difference, upgrade immediately
                    $subscription->update([
                        'plan_id' => $newPlan->id,
                        'active_license' => $newPlan->employee_limit,
                        'implementation_fee_paid' => $newImplFee,
                    ]);

                    $message = 'Plan upgraded successfully!';
                    $requiresPayment = false;
                    $invoice = null;
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'requires_payment' => $requiresPayment,
                    'invoice' => $invoice ? [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'amount_due' => $invoice->amount_due,
                        'currency' => $invoice->currency,
                    ] : null
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error upgrading plan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upgrade plan: ' . $e->getMessage()
            ], 500);
        }
    }
}
