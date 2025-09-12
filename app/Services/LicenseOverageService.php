<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LicenseOverageService
{
    const OVERAGE_RATE_PER_LICENSE = 49.00;

    /**
     * Check and create overage invoice when license limit exceeded
     */
    public function checkAndCreateOverageInvoice($tenantId)
    {
        $subscription = Subscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            Log::info('No active subscription found for tenant', ['tenant_id' => $tenantId]);
            return null;
        }

        // Count users with active_license = true
        $activeLicenseUsers = User::where('tenant_id', $tenantId)
            ->where('active_license', true)
            ->count();

        $subscriptionLicenses = $subscription->active_license ?? 0;
        $overageCount = max(0, $activeLicenseUsers - $subscriptionLicenses);

        Log::info('License overage check', [
            'tenant_id' => $tenantId,
            'active_license_users' => $activeLicenseUsers,
            'subscription_licenses' => $subscriptionLicenses,
            'overage_count' => $overageCount
        ]);

        if ($overageCount > 0) {
            // Update subscription license count first
            $this->updateSubscriptionLicenses($subscription, $activeLicenseUsers);

            // Check if overage invoice already exists for this month
            $existingInvoice = Invoice::where('tenant_id', $tenantId)
                ->where('invoice_type', 'license_overage')
                ->whereMonth('issued_at', now()->month)
                ->whereYear('issued_at', now()->year)
                ->where('status', 'pending')
                ->first();

            if ($existingInvoice) {
                // Update existing invoice if overage count changed
                if ($existingInvoice->license_overage_count != $overageCount) {
                    $this->updateOverageInvoice($existingInvoice, $overageCount);
                }
                return $existingInvoice;
            }

            return $this->createOverageInvoice($subscription, $overageCount);
        }

        return null;
    }

    /**
     * Update subscription active_license count
     */
    public function updateSubscriptionLicenses($subscription, $actualLicenseCount)
    {
        $oldLicenseCount = $subscription->active_license;

        if ($actualLicenseCount > $oldLicenseCount) {
            $subscription->update([
                'active_license' => $actualLicenseCount
            ]);

            Log::info('Subscription license count updated', [
                'subscription_id' => $subscription->id,
                'old_license_count' => $oldLicenseCount,
                'new_license_count' => $actualLicenseCount,
                'increase' => $actualLicenseCount - $oldLicenseCount
            ]);
        }
    }

    /**
     * Handle employee activation - called when adding/activating employee
     */
    public function handleEmployeeActivation($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        // Set active_license to true
        $user->update(['active_license' => true]);

        // Check for overage and create invoice
        $invoice = $this->checkAndCreateOverageInvoice($user->tenant_id);

        Log::info('Employee activated and license overage checked', [
            'user_id' => $userId,
            'tenant_id' => $user->tenant_id,
            'invoice_created' => $invoice ? $invoice->id : null
        ]);

        return $invoice;
    }

    /**
     * Handle employee deactivation
     */
    public function handleEmployeeDeactivation($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        // Set active_license to false
        $user->update(['active_license' => false]);

        Log::info('Employee deactivated', [
            'user_id' => $userId,
            'tenant_id' => $user->tenant_id
        ]);

        return true;
    }

    /**
     * Create license overage invoice
     */
    public function createOverageInvoice($subscription, $overageCount)
    {
        $overageAmount = $overageCount * self::OVERAGE_RATE_PER_LICENSE;
        $invoiceNumber = $this->generateOverageInvoiceNumber();

        $invoice = Invoice::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'invoice_type' => 'license_overage',
            'invoice_number' => $invoiceNumber,
            'license_overage_count' => $overageCount,
            'license_overage_rate' => self::OVERAGE_RATE_PER_LICENSE,
            'subscription_amount' => 0,
            'license_overage_amount' => $overageAmount,
            'amount_due' => $overageAmount,
            'currency' => 'PHP',
            'status' => 'pending',
            'due_date' => now()->addDays(7),
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'issued_at' => now(),
        ]);

        Log::info('License overage invoice created', [
            'invoice_id' => $invoice->id,
            'overage_count' => $overageCount,
            'overage_amount' => $overageAmount,
            'invoice_number' => $invoiceNumber
        ]);

        return $invoice;
    }

    /**
     * Update existing overage invoice
     */
    public function updateOverageInvoice($invoice, $newOverageCount)
    {
        $newOverageAmount = $newOverageCount * self::OVERAGE_RATE_PER_LICENSE;

        $invoice->update([
            'license_overage_count' => $newOverageCount,
            'license_overage_amount' => $newOverageAmount,
            'amount_due' => $newOverageAmount,
        ]);

        Log::info('License overage invoice updated', [
            'invoice_id' => $invoice->id,
            'old_count' => $invoice->getOriginal('license_overage_count'),
            'new_count' => $newOverageCount,
            'new_amount' => $newOverageAmount
        ]);

        return $invoice;
    }

    /**
     * Generate unique invoice number for overage
     */
    private function generateOverageInvoiceNumber()
    {
        $prefix = 'LO-'; // License Overage
        $date = now()->format('Ymd');
        $sequence = Invoice::where('invoice_type', 'license_overage')
            ->whereDate('created_at', today())
            ->count() + 1;

        return $prefix . $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
