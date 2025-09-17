<?php

namespace App\Console\Commands;

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
    protected $signature = 'invoices:generate-monthly-overage';

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

        // Get all active yearly subscriptions
        $yearlySubscriptions = Subscription::where('status', 'active')
            ->where('billing_cycle', 'yearly')
            ->get();

        $this->info("Processing {$yearlySubscriptions->count()} yearly subscriptions");

        foreach ($yearlySubscriptions as $subscription) {
            try {
                $invoice = $licenseService->checkAndCreateOverageInvoice($subscription->tenant_id);

                if ($invoice) {
                    $this->info("Monthly overage invoice created for subscription {$subscription->id}: {$invoice->invoice_number}");
                } else {
                    $this->line("No overage invoice needed for subscription {$subscription->id}");
                }
            } catch (\Exception $e) {
                $this->error("Failed to create monthly overage for subscription {$subscription->id}: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
