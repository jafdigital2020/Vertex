<?php

namespace App\Http\Controllers\Tenant\Billing;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Http\Request;
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
            // Get current period usage summary
            $currentPeriod = $subscription->getCurrentPeriod();
            $usageSummary = $this->licenseOverageService->getUsageSummary(
                $tenantId,
                $currentPeriod['start'],
                $currentPeriod['end']
            );

            // Count currently active licenses
            $activeLicenseCount = User::where('tenant_id', $tenantId)
                ->where('active_license', true)
                ->count();

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
}
