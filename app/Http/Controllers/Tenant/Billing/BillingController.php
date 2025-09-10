<?php

namespace App\Http\Controllers\Tenant\Billing;

use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
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

        // Subscription
        $subscription = Subscription::where('tenant_id', $tenantId)->first();

        // Paginated invoices with proper ordering
        $invoices = Invoice::where('tenant_id', $tenantId)
            ->with(['subscription.plan', 'paymentTransactions']) // Eager load relationships
            ->orderBy('issued_at', 'desc')
            ->paginate(10);

        if ($request->wantsJson()) {
            if (!$subscription) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No subscription found for this tenant.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'subscription' => $subscription,
                    'invoice' => $invoices,
                    'tenantId' => $tenantId
                ]
            ]);
        }

        // Web Route
        return view('tenant.billing.billing', [
            'subscription' => $subscription,
            'invoice' => $invoices,
            'tenantId' => $tenantId
        ]);
    }
}
