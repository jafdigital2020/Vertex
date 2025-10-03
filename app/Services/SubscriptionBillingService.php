<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\BranchAddon;
use App\Models\BranchSubscription;
use App\Models\EmploymentDetail;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionBillingService
{
    /** Per active employee per period (PHP). Change to integer cents if you prefer. */
    private const PER_EMPLOYEE_RATE = 49.00;

    /**
     * Generate a branch subscription renewal invoice (forward-billed for the next period).
     * Total = (#active employees * PER_EMPLOYEE_RATE) + (sum of active add-ons).
     */
    public function createBranchSubscriptionRenewalInvoice(BranchSubscription $subscription): Invoice
    {
        $currentPeriod = method_exists($subscription, 'getCurrentPeriod')
            ? $subscription->getCurrentPeriod()
            : $this->deriveCurrentPeriod($subscription);

        $nextPeriod = method_exists($subscription, 'getNextPeriod')
            ? $subscription->getNextPeriod()
            : $this->deriveNextPeriod($subscription, $currentPeriod);

        // Prevent duplicates for the same next period
        $existing = Invoice::query()
            ->where('branch_subscription_id', $subscription->id)
            ->where('invoice_type', 'branch_subscription')
            ->where('period_start', $nextPeriod['start'])
            ->where('period_end', $nextPeriod['end'])
            ->whereIn('status', ['pending', 'paid'])
            ->first();

        if ($existing) {
            Log::info('Branch renewal invoice already exists', [
                'invoice_id' => $existing->id,
                'branch_id'  => $subscription->branch_id,
                'period'     => $nextPeriod,
            ]);
            return $existing;
        }

        // Compute forward (next period) billing
        $employeeCount  = $this->countActiveEmployeesForPeriod(
            $subscription->tenant_id,
            $nextPeriod,
            $subscription->branch_id
        );
        $employeeAmount = $employeeCount * self::PER_EMPLOYEE_RATE;

        $addonSummary = $this->sumBranchAddonAmount($subscription->branch_id, $nextPeriod);
        $addonsAmount = $addonSummary['total_amount'];

        $totalAmount = $employeeAmount + $addonsAmount;

        // Create invoice transactionally
        return DB::transaction(function () use (
            $subscription,
            $nextPeriod,
            $employeeCount,
            $employeeAmount,
            $addonSummary,
            $addonsAmount,
            $totalAmount
        ) {
            $invoiceType   = 'branch_subscription';
            $invoiceNumber = $this->generateInvoiceNumber($invoiceType);

            $payload = [
                'tenant_id'               => $subscription->tenant_id,
                'branch_id'               => $subscription->branch_id,
                'branch_subscription_id'  => $subscription->id,
                'invoice_type'            => $invoiceType,
                'invoice_number'          => $invoiceNumber,
                'currency'                => 'PHP',
                'status'                  => 'pending',
                'due_date'                => $nextPeriod['start'], // adjust to your policy
                'issued_at'               => now(),
                'period_start'            => $nextPeriod['start'],
                'period_end'              => $nextPeriod['end'],
                'amount_due'              => $totalAmount,
                'amount_paid'             => 0,
                'subscription_amount'     => $totalAmount,
                'subscription_due_date'   => $nextPeriod['start'],
            ];

            // If your invoices table has a JSON column (e.g., `details` or `meta`), store breakdown:
            if (Schema()->hasColumn('invoices', 'details')) {
                $payload['details'] = [
                    'employee_count'  => $employeeCount,
                    'employee_rate'   => self::PER_EMPLOYEE_RATE,
                    'employee_amount' => $employeeAmount,
                    'addon_count'     => $addonSummary['count'],
                    'addons_amount'   => $addonsAmount,
                    'period'          => $nextPeriod,
                ];
            }

            $invoice = Invoice::create($payload);

            Log::info('Branch subscription renewal invoice created', [
                'invoice_id'      => $invoice->id,
                'invoice_number'  => $invoice->invoice_number,
                'branch_id'       => $subscription->branch_id,
                'employee_count'  => $employeeCount,
                'employee_amount' => $employeeAmount,
                'addon_count'     => $addonSummary['count'],
                'addons_amount'   => $addonsAmount,
                'total_amount'    => $totalAmount,
                'period'          => $nextPeriod,
            ]);

            return $invoice;
        });
    }

    /**
     * List recipient emails for a branch (all active employees with an email).
     * Adjust to filter by role if you only want admins/managers.
     */
    public function getBranchRecipientEmails(int $tenantId, int $branchId): array
    {
        $userIds = EmploymentDetail::query()
            ->where('branch_id', $branchId)
            ->where('status', 1)
            ->pluck('user_id')
            ->unique()
            ->all();

    Log::debug('getBranchRecipientEmails: Found user IDs', [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'user_ids' => $userIds,
            'user_count' => count($userIds)
        ]);

        $emails = User::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $userIds)
            ->where('active_subscription', true)
            ->whereNotNull('email')
            ->pluck('email')
            ->unique()
            ->values()
            ->all();

        $email = !empty($emails) ? $emails[0] : null;

        Log::debug('getBranchRecipientEmails: Found email', [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'email' => $email,
            'has_email' => !empty($email)
        ]);

        return $email ? [$email] : [];
    }

    /* -------------------- Internals -------------------- */

    private function countActiveEmployeesForPeriod(int $tenantId, array $period, ?int $branchId = null): int
    {
        $userIds = EmploymentDetail::query()
            ->where('status', 1)
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->pluck('user_id')
            ->unique()
            ->all();

        return User::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $userIds)
            ->where(function ($q) {
                $q->where('active_subscription', true)
                  ->orWhere('active_subscription', 1);
            })
            ->count();
    }

    private function sumBranchAddonAmount(int $branchId, array $period): array
    {
        // Get branch addons for the branch
        $branchAddOns = BranchAddon::query()
            ->where('branch_id', $branchId)
            ->where('active', true)
            ->get();

        $totalAmount = 0.0;
        $activeCount = 0;

        if ($branchAddOns->isNotEmpty()) {
            $addonIds = $branchAddOns->pluck('addon_id')->unique()->all();
            $addons   = Addon::whereIn('id', $addonIds)->get()->keyBy('id');

            foreach ($branchAddOns as $ba) {
                $addon = $addons->get($ba->addon_id);
                if ($addon && $addon->is_active) {
                    $totalAmount += (float) $addon->price;
                    $activeCount++;
                }
            }
        }

        // Add 12% VAT to total amount
        $vatAmount = $totalAmount * 0.12;
        $totalAmountWithVat = $totalAmount + $vatAmount;

        Log::debug('sumBranchAddonAmount', [
            'branch_id'    => $branchId,
            'period'       => $period,
            'addon_count'  => $activeCount,
            'total_amount' => $totalAmount,
            'vat_amount'   => $vatAmount,
            'total_amount_with_vat' => $totalAmountWithVat,
            'branch_addons_found' => $branchAddOns->count(),
            'branch_addons_ids' => $branchAddOns->pluck('id')->all(),
            'addon_ids' => isset($addonIds) ? $addonIds : [],
        ]);

        return [
            'count' => $activeCount,
            'total_amount' => $totalAmountWithVat,
            'vat_amount' => $vatAmount,
            'base_amount' => $totalAmount
        ];
    }

    private function generateInvoiceNumber(string $type): string
    {
        $prefix = match ($type) {
            'branch_subscription' => 'MS-',
            default               => 'INV-',
        };

        $date = now()->format('Ymd');

        $sequence = Invoice::query()
            ->where('invoice_type', $type)
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return sprintf('%s%s-%03d', $prefix, $date, $sequence);
    }

    private function deriveCurrentPeriod(BranchSubscription $subscription): array
    {
        $now     = Carbon::now();
        $billing = strtolower((string) $subscription->billing_period);

        if ($billing === 'yearly') {
            $start = Carbon::parse($subscription->subscription_start)->startOfDay();
            while ($start->copy()->addYear()->lte($now)) {
                $start->addYear();
            }
            $end = $start->copy()->addYear()->subDay()->endOfDay();
        } else {
            $start = $now->copy()->startOfMonth();
            $end   = $now->copy()->endOfMonth();
        }

        return ['start' => $start->toDateString(), 'end' => $end->toDateString()];
    }

    private function deriveNextPeriod(BranchSubscription $subscription, array $current): array
    {
        $billing = strtolower((string) $subscription->billing_period);
        $start   = Carbon::parse($current['start']);
        $end     = Carbon::parse($current['end']);

        if ($billing === 'yearly') {
            $nextStart = $start->copy()->addYear()->toDateString();
            $nextEnd   = $end->copy()->addYear()->toDateString();
        } else {
            $nextStart = $start->copy()->addMonthNoOverflow()->toDateString();
            $nextEnd   = Carbon::parse($nextStart)->endOfMonth()->toDateString();
        }

        return ['start' => $nextStart, 'end' => $nextEnd];
    }
}

/**
 * Tiny helper so we can check columns without importing the facade at top.
 */
if (! function_exists('Schema')) {
    function Schema()
    {
        return app('db.schema');
    }
}
