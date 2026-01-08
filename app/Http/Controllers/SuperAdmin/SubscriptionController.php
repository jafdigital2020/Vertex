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

        $subscriptions->each(function ($subscription) {
            $withItems = $subscription->invoices->first(function ($invoice) {
                return $invoice->items && $invoice->items->isNotEmpty();
            });
            $subscription->latest_invoice = $withItems ?: $subscription->invoices->first();
            
            // Process invoices similar to billing controller
            $subscription->invoices->each(function ($invoice) {
                // Enhanced wizard invoice support
                if ($invoice->items && $invoice->items->count() > 0) {
                    // Mark as wizard-generated invoice
                    $invoice->is_wizard_generated = true;
                    
                    // Calculate breakdown from items for better display
                    $invoice->items_subtotal = $invoice->items->sum('amount');
                    
                    // Categorize items for better UI display
                    $invoice->categorized_items = [
                        'base_subscription' => $invoice->items->where('metadata->type', 'base_subscription'),
                        'additional_employees' => $invoice->items->where('metadata->type', 'additional_employees'),
                        'mobile_access' => $invoice->items->where('metadata->type', 'mobile_access'),
                        'addons' => $invoice->items->whereIn('metadata->type', ['addon_monthly', 'addon_onetime']),
                        'biometric' => $invoice->items->whereIn('metadata->type', ['biometric_device', 'biometric_service']),
                        'implementation' => $invoice->items->where('metadata->type', 'implementation_fee'),
                    ];
                    
                    // Count different item types
                    $invoice->item_counts = [
                        'total' => $invoice->items->count(),
                        'subscription' => $invoice->categorized_items['base_subscription']->count(),
                        'addons' => $invoice->categorized_items['addons']->count(),
                        'biometric' => $invoice->categorized_items['biometric']->count(),
                        'one_time' => $invoice->items->where('period', 'one-time')->count(),
                        'recurring' => $invoice->items->where('period', '!=', 'one-time')->count(),
                    ];
                } else {
                    $invoice->is_wizard_generated = false;
                }
            });
        });

        return view('superadmin.subscription', compact('subscriptions'));
    }
}
