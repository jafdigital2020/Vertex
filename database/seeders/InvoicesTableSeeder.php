<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InvoicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the subscription to link the invoice to
        $subscription = DB::table('subscriptions')->where('tenant_id', 1)->first();

        if (!$subscription) {
            $this->command->warn('No subscription found. Skipping invoice seeder.');
            return;
        }

        // Default billing cycle (will match subscription's billing cycle)
        $billingCycle = $subscription->billing_cycle ?? 'monthly';

        // Calculate period dates based on billing cycle
        $periodStart = Carbon::now()->toDateString();

        if ($billingCycle === 'yearly') {
            $periodEnd = Carbon::now()->addYear()->subDay()->toDateString();
        } else { // monthly
            $periodEnd = Carbon::now()->addMonth()->subDay()->toDateString();
        }

        // Calculate amounts
        $subscriptionAmount = $subscription->amount_paid ?? 0.00;
        $implementationFee = $subscription->implementation_fee ?? 0.00;
        $vatPercentage = 12.00; // Default VAT percentage

        // Calculate subtotal (subscription + implementation fee)
        $subtotal = $subscriptionAmount + $implementationFee;

        // Calculate VAT amount
        $vatAmount = $subtotal * ($vatPercentage / 100);

        // Calculate total amount due
        $amountDue = $subtotal + $vatAmount;

        // Generate invoice number
        $invoiceNumber = 'INV-' . strtoupper(uniqid());

        DB::table('invoices')->insert([
            'tenant_id' => 1,
            'subscription_id' => $subscription->id,
            'invoice_type' => 'subscription',
            'billing_cycle' => $billingCycle,
            'invoice_number' => $invoiceNumber,
            'amount_due' => $amountDue,
            'amount_paid' => $amountDue, // Paid in full
            'subscription_amount' => $subscriptionAmount,
            'currency' => 'PHP',
            'due_date' => Carbon::now()->toDateString(),
            'status' => 'paid',
            'issued_at' => Carbon::now(),
            'paid_at' => Carbon::now(),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'license_overage_count' => 0,
            'license_overage_rate' => 0.00,
            'license_overage_amount' => 0.00,
            'unused_overage_count' => 0,
            'unused_overage_amount' => 0.00,
            'gross_overage_count' => 0,
            'gross_overage_amount' => 0.00,
            'implementation_fee' => $implementationFee,
            'vat_amount' => $vatAmount,
            'vat_percentage' => $vatPercentage,
            'subtotal' => $subtotal,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->command->info('Invoice created successfully: ' . $invoiceNumber);
    }
}
