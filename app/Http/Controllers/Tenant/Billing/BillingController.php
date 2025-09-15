<?php

namespace App\Http\Controllers\Tenant\Billing;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\LicenseUsageLog;
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

        // Get subscription
        $subscription = Subscription::where('tenant_id', $tenantId)->first();

        if ($subscription) {
            // Get current period
            $currentPeriod = $subscription->getCurrentPeriod();

            // ✅ FIX: Count ALL currently active licenses (existing + new)
            $activeLicenseCount = User::where('tenant_id', $tenantId)
                ->where('active_license', true)
                ->count();

            // ✅ FIX: Get usage summary that includes existing active licenses
            $usageSummary = $this->getEnhancedUsageSummary($tenantId, $currentPeriod, $subscription);

            // Check for overage and create invoice if needed
            $this->licenseOverageService->checkAndCreateOverageInvoice($tenantId);
        } else {
            $usageSummary = null;
            $activeLicenseCount = 0;
            $currentPeriod = null;
        }

        // Get invoices with pagination
        $invoice = Invoice::where('tenant_id', $tenantId)
            ->with(['subscription.plan', 'paymentTransactions'])
            ->orderBy('issued_at', 'desc')
            ->paginate(10);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'subscription' => $subscription,
                    'invoice' => $invoice,
                    'tenantId' => $tenantId,
                    'activeLicenseCount' => $activeLicenseCount,
                    'usageSummary' => $usageSummary,
                    'currentPeriod' => $currentPeriod
                ]
            ]);
        }

        return view('tenant.billing.billing', [
            'subscription' => $subscription,
            'invoice' => $invoice,
            'tenantId' => $tenantId,
            'activeLicenseCount' => $activeLicenseCount,
            'usageSummary' => $usageSummary,
            'currentPeriod' => $currentPeriod
        ]);
    }

    /**
     * ✅ NEW: Enhanced usage summary that includes existing active licenses
     */
    private function getEnhancedUsageSummary($tenantId, $currentPeriod, $subscription)
    {
        // Get usage logs for current period (new activations)
        $periodUsageLogs = LicenseUsageLog::getUsageForPeriod($tenantId, $currentPeriod['start'], $currentPeriod['end']);

        // Get all currently active users
        $currentlyActiveUsers = User::where('tenant_id', $tenantId)
            ->where('active_license', true)
            ->get();

        // Users activated during this period (from logs)
        $newlyActivatedUserIds = $periodUsageLogs->pluck('user_id')->unique();

        // Users that were already active before this period started
        $existingActiveUsers = $currentlyActiveUsers->whereNotIn('id', $newlyActivatedUserIds);

        // ✅ TOTAL BILLABLE = Existing Active + New Activations in Period
        $totalBillableLicenses = $existingActiveUsers->count() + $periodUsageLogs->unique('user_id')->count();

        // Currently active (all users with active_license = true)
        $currentlyActive = $currentlyActiveUsers->count();

        // Users activated then deactivated in this period
        $activatedThenDeactivated = $periodUsageLogs->whereNotNull('deactivated_at')->unique('user_id')->count();

        // Enhanced usage details including existing active users
        $usageDetails = collect();

        // Add existing active users (not in period logs)
        foreach ($existingActiveUsers as $user) {
            // ✅ FIX: Use period start as reference for existing users
            $activatedDate = \Carbon\Carbon::parse($currentPeriod['start']);
            $endDate = \Carbon\Carbon::parse($currentPeriod['end']);

            $usageDetails->push([
                'user_id' => $user->id,
                'user_name' => $user->personalInformation->full_name ?? 'Unknown',
                'activated_at' => $activatedDate,
                'deactivated_at' => null,
                'is_billable' => true,
                'days_active' => $activatedDate->diffInDays($endDate), // Period duration
                'license_type' => 'existing' // Mark as existing license
            ]);
        }

        // Add period-specific activations
        foreach ($periodUsageLogs as $log) {
            // ✅ FIX: Proper days active calculation
            $activatedAt = \Carbon\Carbon::parse($log->activated_at);

            if ($log->deactivated_at) {
                // If deactivated, calculate days between activation and deactivation
                $deactivatedAt = \Carbon\Carbon::parse($log->deactivated_at);
                $daysActive = $activatedAt->diffInDays($deactivatedAt);
            } else {
                // If still active, calculate days from activation to period end
                $periodEnd = \Carbon\Carbon::parse($currentPeriod['end']);
                $daysActive = $activatedAt->diffInDays($periodEnd);
            }

            // ✅ ENSURE: Days active is always positive
            $daysActive = max(1, $daysActive); // At least 1 day if activated

            $usageDetails->push([
                'user_id' => $log->user_id,
                'user_name' => $log->user->personalInformation->full_name ?? 'Unknown',
                'activated_at' => $log->activated_at,
                'deactivated_at' => $log->deactivated_at,
                'is_billable' => $log->is_billable,
                'days_active' => $daysActive,
                'license_type' => 'period_activation' // Mark as period activation
            ]);
        }

        return [
            'total_billable_licenses' => $totalBillableLicenses,
            'currently_active' => $currentlyActive,
            'activated_then_deactivated' => $activatedThenDeactivated,
            'existing_active_count' => $existingActiveUsers->count(),
            'period_activations_count' => $periodUsageLogs->unique('user_id')->count(),
            'usage_details' => $usageDetails->sortBy('user_name')->values()
        ];
    }
}
