<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Subscription;
use Illuminate\Console\Command;
use App\Services\LicenseOverageService;

class GenerateMonthlyOverageInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-monthly-overage {--dry-run : Run without creating actual invoices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly overage invoices for yearly subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $licenseService = app(LicenseOverageService::class);
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('Running in DRY RUN mode - no invoices will be created');
        }

        // Get all active yearly subscriptions
        $yearlySubscriptions = Subscription::where('status', 'active')
            ->where('billing_cycle', 'yearly')
            ->get();

        $this->info("Processing {$yearlySubscriptions->count()} yearly subscriptions");

        $invoicesCreated = 0;
        $subscriptionsProcessed = 0;

        foreach ($yearlySubscriptions as $subscription) {
            try {
                $subscriptionsProcessed++;

                // For yearly subscriptions, skip renewal period check for immediate billing
                $nextRenewal = Carbon::parse($subscription->next_renewal_date);
                $currentDate = Carbon::now();
                $daysUntilRenewal = $currentDate->diffInDays($nextRenewal, false);

                // Only skip if really close to renewal (1 day instead of 7)
                if ($daysUntilRenewal <= 1) {
                    $this->line("Subscription {$subscription->id}: Skipping - in renewal period ({$daysUntilRenewal} days until renewal)");
                    continue;
                }

                if (!$isDryRun) {
                    // Use immediate monthly overage invoice creation
                    $currentPeriod = $licenseService->getCurrentMonthlyPeriod($subscription);
                    $invoice = $licenseService->createImmediateMonthlyOverageInvoice($subscription, $currentPeriod);

                    if ($invoice) {
                        $invoicesCreated++;
                        $this->info("✓ Monthly overage invoice created for subscription {$subscription->id}: {$invoice->invoice_number} (Amount: ₱{$invoice->amount_due})");
                    } else {
                        $this->line("- No overage invoice needed for subscription {$subscription->id}");
                    }
                } else {
                    // Dry run logic remains the same
                    $currentPeriod = $licenseService->getCurrentMonthlyPeriod($subscription);
                    $overageCount = $licenseService->calculateMonthlyOverageLicenses($subscription->tenant_id, $currentPeriod);

                    if ($overageCount > 0) {
                        $amount = $overageCount * \App\Services\LicenseOverageService::OVERAGE_RATE_PER_LICENSE;
                        $this->info("[DRY RUN] Would create overage invoice for subscription {$subscription->id}: {$overageCount} licenses (₱{$amount})");
                        $invoicesCreated++;
                    } else {
                        $this->line("[DRY RUN] No overage needed for subscription {$subscription->id}");
                    }
                }
            } catch (\Exception $e) {
                $this->error("Failed to process subscription {$subscription->id}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Subscriptions processed: {$subscriptionsProcessed}");
        $this->info("- Invoices created: {$invoicesCreated}");

        return self::SUCCESS;
    }
}
