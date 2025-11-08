<?php

namespace App\Http\Controllers\Tenant\Billing;

use App\Helpers\PermissionHelper;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\LicenseUsageLog;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\LicenseOverageService;

class BillingController extends Controller
{
    protected $licenseOverageService;

    public function __construct(LicenseOverageService $licenseOverageService)
    {
        $this->licenseOverageService = $licenseOverageService;
    }

    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    // Billing Index
    public function billingIndex(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;
        $permissions = PermissionHelper::get(57); // Sub-module ID for 'Bills & Payment'

        // Get subscription
        $subscription = Subscription::where('tenant_id', $tenantId)->first();

        if ($subscription) {
            // Get current period
            $currentPeriod = $subscription->getCurrentPeriod();

            // Count ALL currently active licenses
            $activeLicenseCount = User::where('tenant_id', $tenantId)
                ->where('active_license', true)
                ->count();

            // Get usage summary
            $usageSummary = $this->getEnhancedUsageSummary($tenantId, $currentPeriod, $subscription);

            // ✅ DISABLED: No automatic overage checking to prevent post-payment creation
            // Only allow manual overage checking or scheduled generation

        } else {
            $usageSummary = null;
            $activeLicenseCount = 0;
            $currentPeriod = null;
        }

        // Get invoices with pagination and calculate VAT for each
        $invoice = Invoice::where('tenant_id', $tenantId)
            ->with(['subscription.plan', 'tenant', 'upgradePlan']) // Load upgrade plan relationship
            ->whereIn('status', ['pending', 'paid', 'failed', 'consolidated', 'consolidated_pending'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Calculate VAT for each invoice if not already stored
        foreach ($invoice as $inv) {
            if (!$inv->vat_amount && $inv->subscription && $inv->subscription->plan) {
                $vatPercentage = $inv->subscription->plan->vat_percentage ?? 12;
                $subtotal = $inv->amount_due ?? 0;

                // Calculate VAT (assuming amount_due includes VAT)
                // To extract VAT from inclusive amount: VAT = amount / (1 + vat_rate) * vat_rate
                $inv->calculated_vat_percentage = $vatPercentage;
                $inv->calculated_subtotal = $subtotal / (1 + ($vatPercentage / 100));
                $inv->calculated_vat_amount = $subtotal - $inv->calculated_subtotal;
            } else {
                // Use stored VAT values or calculate from plan
                $vatPercentage = $inv->subscription->plan->vat_percentage ?? 12;
                $inv->calculated_vat_percentage = $vatPercentage;
                $inv->calculated_subtotal = ($inv->amount_due ?? 0) - ($inv->vat_amount ?? 0);
                $inv->calculated_vat_amount = $inv->vat_amount ?? 0;
            }
        }

        return view('tenant.billing.billing', compact(
            'subscription',
            'invoice',
            'usageSummary',
            'activeLicenseCount',
            'currentPeriod',
            'permissions'
        ));
    }

    /**
     * ✅ NEW: Enhanced usage summary that includes existing active licenses
     */
    private function getEnhancedUsageSummary($tenantId, $currentPeriod, $subscription)
    {
        // ✅ BASE LICENSE: From plan (included in subscription price)
        $baseLicenseCount = $subscription->plan->license_limit ?? $subscription->active_license ?? 0;

        // Get usage logs for current period
        $periodUsageLogs = LicenseUsageLog::where('tenant_id', $tenantId)
            ->where('subscription_period_start', $currentPeriod['start'])
            ->where('subscription_period_end', $currentPeriod['end'])
            ->where('is_billable', true)
            ->get();

        // Get all currently active users
        $currentlyActiveUsers = User::where('tenant_id', $tenantId)
            ->where('active_license', true)
            ->get();

        // Users activated during this period
        $newlyActivatedUserIds = $periodUsageLogs->pluck('user_id')->unique();

        // Users that were already active before this period
        $existingActiveUsers = $currentlyActiveUsers->whereNotIn('id', $newlyActivatedUserIds);

        // ✅ TOTAL ACTIVE: All users who used licenses during this period
        $totalActiveLicenses = $existingActiveUsers->count() + $periodUsageLogs->unique('user_id')->count();

        // ✅ BILLABLE OVERAGE: Only additional licenses beyond base plan
        $billableOverageLicenses = max(0, $totalActiveLicenses - $baseLicenseCount);

        // Currently active users count
        $currentlyActive = $currentlyActiveUsers->count();

        // Users activated then deactivated in this period
        $activatedThenDeactivated = $periodUsageLogs->whereNotNull('deactivated_at')->unique('user_id')->count();

        // Enhanced usage details
        $usageDetails = collect();

        // Add existing active users
        foreach ($existingActiveUsers as $user) {
            $activatedDate = \Carbon\Carbon::parse($currentPeriod['start']);
            $endDate = \Carbon\Carbon::parse($currentPeriod['end']);

            $usageDetails->push([
                'user_id' => $user->id,
                'user_name' => $user->personalInformation->full_name ?? 'Unknown',
                'activated_at' => $activatedDate,
                'deactivated_at' => null,
                'is_billable' => true,
                'days_active' => $activatedDate->diffInDays($endDate),
                'license_type' => 'existing'
            ]);
        }

        // Add period-specific activations
        foreach ($periodUsageLogs as $log) {
            $activatedAt = \Carbon\Carbon::parse($log->activated_at);

            if ($log->deactivated_at) {
                $deactivatedAt = \Carbon\Carbon::parse($log->deactivated_at);
                $daysActive = $activatedAt->diffInDays($deactivatedAt);
            } else {
                $periodEnd = \Carbon\Carbon::parse($currentPeriod['end']);
                $daysActive = $activatedAt->diffInDays($periodEnd);
            }

            $daysActive = max(1, $daysActive);

            $usageDetails->push([
                'user_id' => $log->user_id,
                'user_name' => $log->user->personalInformation->full_name ?? 'Unknown',
                'activated_at' => $log->activated_at,
                'deactivated_at' => $log->deactivated_at,
                'is_billable' => $log->is_billable,
                'days_active' => $daysActive,
                'license_type' => 'period_activation'
            ]);
        }

        return [
            'base_license_count' => $baseLicenseCount, // ✅ Included in plan price
            'total_active_licenses' => $totalActiveLicenses, // ✅ All active users
            'total_billable_licenses' => $billableOverageLicenses, // ✅ Only additional licenses
            'currently_active' => $currentlyActive,
            'activated_then_deactivated' => $activatedThenDeactivated,
            'existing_active_count' => $existingActiveUsers->count(),
            'period_activations_count' => $periodUsageLogs->unique('user_id')->count(),
            'usage_details' => $usageDetails->sortBy('user_name')->values()
        ];
    }

    /**
     * ✅ NEW: Method to manually trigger renewal invoice generation (for testing)
     */
    public function generateRenewalInvoice(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;

        $subscription = Subscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active subscription found'
            ], 404);
        }

        try {
            // ✅ PREVENT DUPLICATES: Check if renewal invoice already exists
            $nextPeriod = $subscription->getNextPeriod();
            $existingRenewal = Invoice::where('tenant_id', $tenantId)
                ->where('subscription_id', $subscription->id)
                ->where('invoice_type', 'subscription')
                ->where('period_start', $nextPeriod['start'])
                ->where('period_end', $nextPeriod['end'])
                ->whereIn('status', ['pending', 'paid'])
                ->first();

            if ($existingRenewal) {
                return response()->json([
                    'status' => 'info',
                    'message' => 'Renewal invoice already exists',
                    'invoice' => $existingRenewal
                ]);
            }

            $invoice = $this->licenseOverageService->createConsolidatedRenewalInvoice($subscription);

            return response()->json([
                'status' => 'success',
                'message' => 'Renewal invoice generated successfully',
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate renewal invoice', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate renewal invoice: ' . $e->getMessage()
            ], 500);
        }
    }
}
