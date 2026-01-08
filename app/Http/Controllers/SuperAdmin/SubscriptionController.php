<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;

class SubscriptionController extends Controller
{
    public function subscriptionIndex()
    {
        $subscriptions = Subscription::with([
            'tenant',
            'plan',
            'invoices' => function ($query) {
                $query->with(['items', 'tenant', 'upgradePlan', 'subscription.plan'])
                    ->orderBy('created_at', 'desc');
            },
        ])->get();

        return view('superadmin.subscription', compact('subscriptions'));
    }
}
