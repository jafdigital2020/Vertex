<?php

namespace App\Http\Controllers\Tenant\Billing;

use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
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

        // Fetch all invoices for this tenant with payments + subscription + branch
        $invoices = Invoice::with(['payments', 'subscription', 'branch'])
            ->whereHas('branch', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->orderByDesc('issued_at')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => $invoices
            ]);
        }

        return view('tenant.billing.billing', [
            'invoices' => $invoices,
            'tenantId' => $tenantId
        ]);
    }


    public function fetchInvoices(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;

        $perPage = $request->get('per_page', 10);

        $invoices = Invoice::with(['payments', 'subscription', 'branch'])
            ->whereHas('branch', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->orderByDesc('issued_at')
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data'   => $invoices
        ]);
    }


}
