<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // This seeder provides a template subscription record that will be
        // adjusted by the deployment script (workflow) using sed to set
        // plan_id, billing_cycle, amount_paid, active_license,
        // implementation_fee, implementation_fee_paid, subscription_end,
        // next_renewal_date and status.

        // Default billing cycle (will be updated by workflow)
        $billingCycle = 'monthly';

        // Calculate dynamic dates based on billing cycle
        $subscriptionStart = Carbon::now()->toDateString();

        if ($billingCycle === 'yearly') {
            $nextRenewalDate = Carbon::now()->addYear()->toDateString();
            $subscriptionEnd = Carbon::now()->addYear()->subDay()->toDateString();
        } else { // monthly
            $nextRenewalDate = Carbon::now()->addMonth()->toDateString();
            $subscriptionEnd = Carbon::now()->addMonth()->subDay()->toDateString();
        }

        DB::table('subscriptions')->insert([
            'tenant_id' => 1,
            'plan_id' => 1,
            'amount_paid' => 0.00,
            'payment_status' => 'paid',
            'status' => 'active',
            'subscription_start' => $subscriptionStart,
            'subscription_end' => $subscriptionEnd,
            'trial_start' => null,
            'trial_end' => null,
            'renewed_at' => null,
            'billing_cycle' => $billingCycle,
            'next_renewal_date' => $nextRenewalDate,
            'active_license' => 1,
            'base_license_count' => 1,
            'overage_license_count' => 0,
            'implementation_fee' => 0.00,
            'implementation_fee_paid' => 0.00,
            'vat_amount' => 0.00,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
