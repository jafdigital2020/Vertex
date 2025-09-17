<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\LicenseUsageLog;
use Illuminate\Support\Facades\Log;

class LicenseOverageService
{
    const OVERAGE_RATE_PER_LICENSE = 1.00;

    /**
     * Check and create overage invoice for current period
     */
    public function checkAndCreateOverageInvoice($tenantId)
    {
        $subscription = Subscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            return null;
        }

        $currentPeriod = $subscription->getCurrentPeriod();

        // Check if there was a recent payment that included overage consolidation
        $recentConsolidatedPayment = Invoice::where('tenant_id', $tenantId)
            ->where('invoice_type', 'subscription')
            ->where('status', 'paid')
            ->where('license_overage_count', '>', 0)
            ->where('paid_at', '>=', Carbon::now()->subDays(1))
            ->first();

        if ($recentConsolidatedPayment) {
            Log::info('Recent consolidated payment found, skipping separate overage invoice creation', [
                'tenant_id' => $tenantId,
                'recent_payment_invoice_id' => $recentConsolidatedPayment->id,
                'recent_payment_overage_count' => $recentConsolidatedPayment->license_overage_count,
                'paid_at' => $recentConsolidatedPayment->paid_at
            ]);
            return null;
        }

        // Check renewal period
        $nextRenewalDate = $subscription->getNextPeriod()['start'];
        $currentDate = Carbon::now();
        $daysUntilRenewal = $currentDate->diffInDays($nextRenewalDate, false);

        if ($daysUntilRenewal <= 7) {
            Log::info('Skipping separate overage invoice - renewal period active', [
                'tenant_id' => $tenantId,
                'days_until_renewal' => $daysUntilRenewal,
                'next_renewal_date' => $nextRenewalDate->format('Y-m-d')
            ]);
            return null;
        }

        // Prevent duplicates
        $existingInvoiceForPeriod = Invoice::where('tenant_id', $tenantId)
            ->where('period_start', $currentPeriod['start'])
            ->where('period_end', $currentPeriod['end'])
            ->whereIn('status', ['pending', 'paid', 'consolidated'])
            ->first();

        if ($existingInvoiceForPeriod) {
            Log::info('Invoice already exists for current period', [
                'tenant_id' => $tenantId,
                'existing_invoice_id' => $existingInvoiceForPeriod->id,
                'existing_invoice_type' => $existingInvoiceForPeriod->invoice_type,
                'period' => $currentPeriod
            ]);
            return $existingInvoiceForPeriod;
        }

        // ✅ CALCULATE: Only additional licenses beyond base plan
        $billableLicensesCount = $this->calculateTotalBillableLicenses($tenantId, $currentPeriod);

        Log::info('License overage check (additional licenses only)', [
            'tenant_id' => $tenantId,
            'period' => $currentPeriod,
            'base_license_count' => $subscription->plan->license_limit ?? $subscription->active_license,
            'billable_overage_licenses' => $billableLicensesCount, // ✅ Only additional
            'days_until_renewal' => $daysUntilRenewal
        ]);

        if ($billableLicensesCount > 0) {
            return $this->createOverageInvoice($subscription, $billableLicensesCount, $currentPeriod);
        }

        return null;
    }

    /**
     * Calculate total billable licenses including existing active users
     */
    private function calculateTotalBillableLicenses($tenantId, $currentPeriod)
    {
        // Get subscription to know base license count
        $subscription = Subscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            return 0;
        }

        // ✅ BASE LICENSE: Get from plan (included in subscription price)
        $baseLicenseCount = $subscription->plan->license_limit ?? $subscription->active_license ?? 0;

        // Get all currently active users
        $currentlyActiveUsers = User::where('tenant_id', $tenantId)
            ->where('active_license', true)
            ->get();

        // Get usage logs for current period (new activations during this period)
        $periodUsageLogs = LicenseUsageLog::where('tenant_id', $tenantId)
            ->where('subscription_period_start', $currentPeriod['start'])
            ->where('subscription_period_end', $currentPeriod['end'])
            ->where('is_billable', true)
            ->get();

        // Users activated during this period
        $newlyActivatedUserIds = $periodUsageLogs->pluck('user_id')->unique();

        // Users that were already active before this period (not in period logs)
        $existingActiveUserIds = $currentlyActiveUsers->whereNotIn('id', $newlyActivatedUserIds)->pluck('id');

        // ✅ TOTAL ACTIVE: All users who used licenses during this period
        $totalActiveLicenses = $existingActiveUserIds->count() + $newlyActivatedUserIds->count();

        // ✅ ONLY BILL OVERAGE: Additional licenses beyond base plan
        $billableOverageLicenses = max(0, $totalActiveLicenses - $baseLicenseCount);

        Log::info('Billable license calculation (overage only)', [
            'tenant_id' => $tenantId,
            'base_license_count' => $baseLicenseCount, // ✅ From plan (included in subscription)
            'existing_active_users' => $existingActiveUserIds->count(),
            'period_activated_users' => $newlyActivatedUserIds->count(),
            'total_active_licenses' => $totalActiveLicenses,
            'billable_overage_licenses' => $billableOverageLicenses, // ✅ Only additional licenses
            'calculation' => "{$totalActiveLicenses} total - {$baseLicenseCount} base = {$billableOverageLicenses} billable"
        ]);

        return $billableOverageLicenses; // ✅ Return only overage licenses
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

        $subscription = Subscription::where('tenant_id', $user->tenant_id)
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            Log::info('No active subscription found for employee activation', [
                'user_id' => $userId,
                'tenant_id' => $user->tenant_id
            ]);
            return false;
        }

        // Get current subscription period
        $currentPeriod = $subscription->getCurrentPeriod();

        // Log the license usage for this subscription period
        $this->logLicenseUsage($user, $subscription, $currentPeriod, 'activated');

        // Check if we need to create/update overage invoice
        $invoice = $this->checkAndCreateOverageInvoice($user->tenant_id);

        Log::info('Employee activated and license usage logged', [
            'user_id' => $userId,
            'tenant_id' => $user->tenant_id,
            'subscription_period' => $currentPeriod,
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

        $subscription = Subscription::where('tenant_id', $user->tenant_id)
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            return false;
        }

        // Get current subscription period
        $currentPeriod = $subscription->getCurrentPeriod();

        // Update the license usage log to mark deactivation
        $this->logLicenseUsage($user, $subscription, $currentPeriod, 'deactivated');

        Log::info('Employee deactivated', [
            'user_id' => $userId,
            'tenant_id' => $user->tenant_id,
            'subscription_period' => $currentPeriod
        ]);

        return true;
    }


    /**
     * Log license usage for subscription period
     */
    public function logLicenseUsage($user, $action)
    {
        $subscription = Subscription::where('tenant_id', $user->tenant_id)
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            return;
        }

        $period = $subscription->getCurrentPeriod();

        $existingLog = LicenseUsageLog::where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->where('subscription_id', $subscription->id)
            ->where('subscription_period_start', $period['start'])
            ->where('subscription_period_end', $period['end'])
            ->first();

        if ($action === 'activated') {
            if (!$existingLog) {
                LicenseUsageLog::create([
                    'tenant_id' => $user->tenant_id,
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'subscription_period_start' => $period['start'],
                    'subscription_period_end' => $period['end'],
                    'activated_at' => now(),
                    'is_billable' => true,
                    'overage_rate' => self::OVERAGE_RATE_PER_LICENSE
                ]);
            } else {
                $existingLog->update([
                    'activated_at' => now(),
                    'deactivated_at' => null,
                    'is_billable' => true
                ]);
            }
        } elseif ($action === 'deactivated') {
            LicenseUsageLog::where('tenant_id', $user->tenant_id)
                ->where('user_id', $user->id)
                ->where('subscription_id', $subscription->id)
                ->where('subscription_period_start', $period['start'])
                ->where('subscription_period_end', $period['end'])
                ->whereNull('deactivated_at')
                ->update([
                    'deactivated_at' => now()
                ]);
        }
    }

    /**
     * Create consolidated invoice for subscription renewal
     */
    public function createConsolidatedRenewalInvoice($subscription)
    {
        $nextPeriod = $subscription->getNextPeriod();

        // Check if renewal invoice already exists
        $existingRenewalInvoice = Invoice::where('tenant_id', $subscription->tenant_id)
            ->where('subscription_id', $subscription->id)
            ->where('invoice_type', 'subscription')
            ->where('period_start', $nextPeriod['start'])
            ->where('period_end', $nextPeriod['end'])
            ->whereIn('status', ['pending', 'paid'])
            ->first();

        if ($existingRenewalInvoice) {
            Log::info('Renewal invoice already exists for next period', [
                'tenant_id' => $subscription->tenant_id,
                'existing_invoice_id' => $existingRenewalInvoice->id,
                'existing_invoice_number' => $existingRenewalInvoice->invoice_number,
                'next_period' => $nextPeriod
            ]);
            return $existingRenewalInvoice;
        }

        $currentPeriod = $subscription->getCurrentPeriod();

        // ✅ BASE SUBSCRIPTION: Use plan price (includes base licenses)
        $baseSubscriptionAmount = $subscription->plan->price ?? $subscription->amount_paid ?? 0;
        $baseLicenseCount = $subscription->plan->license_limit ?? $subscription->active_license ?? 0;

        // ✅ FIND ALL: Get existing unpaid license overage invoices
        $existingOverageInvoices = Invoice::where('tenant_id', $subscription->tenant_id)
            ->where('invoice_type', 'license_overage')
            ->whereIn('status', ['pending'])
            ->get();

        $totalExistingOverageAmount = $existingOverageInvoices->sum('license_overage_amount');
        $totalExistingOverageCount = $existingOverageInvoices->sum('license_overage_count');

        // ✅ CALCULATE: Current period overage (only additional licenses)
        $currentOverageLicenses = $this->calculateTotalBillableLicenses(
            $subscription->tenant_id,
            $currentPeriod
        ); // This returns only overage licenses beyond base

        // ✅ EXCLUDE: Overage already covered by existing invoices
        $alreadyInvoicedOverage = $totalExistingOverageCount;
        $newOverageCount = max(0, $currentOverageLicenses - $alreadyInvoicedOverage);
        $newOverageAmount = $newOverageCount * self::OVERAGE_RATE_PER_LICENSE;

        // ✅ TOTAL OVERAGE: Existing unpaid + new overage
        $totalOverageCount = $totalExistingOverageCount + $newOverageCount;
        $totalOverageAmount = $totalExistingOverageAmount + $newOverageAmount;
        $totalAmount = $baseSubscriptionAmount + $totalOverageAmount;

        // ✅ CREATE: Invoice with base plan + overage
        $invoiceNumber = $this->generateInvoiceNumber('subscription');

        $invoice = Invoice::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'invoice_type' => 'subscription',
            'invoice_number' => $invoiceNumber,
            'license_overage_count' => $totalOverageCount,
            'license_overage_rate' => self::OVERAGE_RATE_PER_LICENSE,
            'subscription_amount' => $baseSubscriptionAmount, // ✅ Plan price (includes base licenses)
            'license_overage_amount' => $totalOverageAmount, // ✅ Only additional licenses
            'amount_due' => $totalAmount,
            'currency' => 'PHP',
            'status' => 'pending',
            'due_date' => $nextPeriod['start'],
            'period_start' => $nextPeriod['start'],
            'period_end' => $nextPeriod['end'],
            'issued_at' => now(),
        ]);

        // Mark existing overage invoices as consolidated
        if ($existingOverageInvoices->count() > 0) {
            $existingOverageInvoices->each(function ($overageInvoice) use ($invoice) {
                $overageInvoice->update([
                    'status' => 'consolidated',
                    'consolidated_into_invoice_id' => $invoice->id
                ]);
            });
        }

        Log::info('Consolidated renewal invoice created with proper base/overage separation', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoiceNumber,
            'base_license_count' => $baseLicenseCount, // ✅ Included in plan price
            'base_subscription_amount' => $baseSubscriptionAmount, // ✅ Plan price
            'existing_overage_invoices' => $existingOverageInvoices->count(),
            'existing_overage_amount' => $totalExistingOverageAmount,
            'current_overage_licenses' => $currentOverageLicenses, // ✅ Only additional
            'new_overage_count' => $newOverageCount,
            'new_overage_amount' => $newOverageAmount,
            'total_overage_count' => $totalOverageCount,
            'total_overage_amount' => $totalOverageAmount,
            'total_amount' => $totalAmount,
            'period' => $nextPeriod
        ]);

        return $invoice;
    }

    /**
     * Create license overage invoice for current period
     */
    private function createOverageInvoice($subscription, $overageCount, $period)
    {
        $overageAmount = $overageCount * self::OVERAGE_RATE_PER_LICENSE;
        $invoiceNumber = $this->generateInvoiceNumber('license_overage');

        $invoice = Invoice::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'invoice_type' => 'license_overage',
            'invoice_number' => $invoiceNumber,
            'license_overage_count' => $overageCount,
            'license_overage_rate' => self::OVERAGE_RATE_PER_LICENSE,
            'license_overage_amount' => $overageAmount,
            'amount_due' => $overageAmount,
            'currency' => 'PHP',
            'status' => 'pending',
            'due_date' => Carbon::parse($period['end'])->addDays(7),
            'period_start' => $period['start'],
            'period_end' => $period['end'],
            'issued_at' => now(),
        ]);

        Log::info('License overage invoice created', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoiceNumber,
            'tenant_id' => $subscription->tenant_id,
            'overage_count' => $overageCount,
            'overage_amount' => $overageAmount,
            'period' => $period
        ]);

        return $invoice;
    }

    /**
     * Update existing overage invoice
     */
    public function updateOverageInvoice($invoice, $newOverageCount)
    {
        $newOverageAmount = $newOverageCount * self::OVERAGE_RATE_PER_LICENSE;

        $oldData = [
            'overage_count' => $invoice->license_overage_count,
            'overage_amount' => $invoice->license_overage_amount,
            'total_amount' => $invoice->amount_due
        ];

        $newTotalAmount = $invoice->subscription_amount + $newOverageAmount;

        $invoice->update([
            'license_overage_count' => $newOverageCount,
            'license_overage_amount' => $newOverageAmount,
            'amount_due' => $newTotalAmount,
        ]);

        Log::info('License overage invoice updated', [
            'invoice_id' => $invoice->id,
            'old_data' => $oldData,
            'new_overage_count' => $newOverageCount,
            'new_overage_amount' => $newOverageAmount,
            'new_total_amount' => $newTotalAmount
        ]);

        return $invoice;
    }

    /**
     * Generate invoice number based on type
     */
    private function generateInvoiceNumber($type = 'subscription')
    {
        $prefix = $type === 'license_overage' ? 'LO' : 'INV';
        $date = now()->format('Ymd');

        $lastInvoice = Invoice::where('invoice_number', 'like', "{$prefix}-{$date}%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -3);
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return "{$prefix}-{$date}-{$nextNumber}";
    }

    /**
     * Get usage summary for a subscription period
     */
    public function getUsageSummary($tenantId, $periodStart, $periodEnd)
    {
        $usageLogs = LicenseUsageLog::getUsageForPeriod($tenantId, $periodStart, $periodEnd);

        $summary = [
            'total_billable_licenses' => $usageLogs->unique('user_id')->count(),
            'currently_active' => $usageLogs->whereNull('deactivated_at')->count(),
            'activated_then_deactivated' => $usageLogs->whereNotNull('deactivated_at')->count(),
            'usage_details' => $usageLogs->map(function ($log) {
                return [
                    'user_id' => $log->user_id,
                    'user_name' => $log->user->personalInformation->first_name ?? 'Unknown',
                    'activated_at' => $log->activated_at,
                    'deactivated_at' => $log->deactivated_at,
                    'is_billable' => $log->is_billable,
                    'days_active' => $log->deactivated_at ?
                        $log->activated_at->diffInDays($log->deactivated_at) :
                        $log->activated_at->diffInDays(now())
                ];
            })
        ];

        return $summary;
    }

    public function calculateNextRenewalAmount($subscription)
    {
        $plan = $subscription->plan;
        $currentLicenseCount = $subscription->active_license ?? 0;

        if ($plan && $plan->price_per_license) {
            // Use per-license pricing if available
            return $currentLicenseCount * $plan->price_per_license;
        } else if ($plan && $plan->price && $plan->license_limit) {
            // Calculate per-license rate from plan price and limit
            $perLicenseRate = $plan->price / $plan->license_limit;
            return $currentLicenseCount * $perLicenseRate;
        } else {
            // Fallback to current amount_paid
            return $subscription->amount_paid ?? 0;
        }
    }
}
