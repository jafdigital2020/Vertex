<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class MigrateBaseLicenseCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:migrate-base-license';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing subscriptions to use base license tracking';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subscriptions = Subscription::whereNull('base_license_count')
            ->orWhere('base_license_count', 0)
            ->with('plan')
            ->get();

        $this->info("Found {$subscriptions->count()} subscriptions to migrate");

        foreach ($subscriptions as $subscription) {
            // Use plan's license limit as base, or current active_license
            $baseLicenseCount = $subscription->plan->license_limit ?? $subscription->active_license ?? 0;
            $overageLicenseCount = max(0, $subscription->active_license - $baseLicenseCount);

            $subscription->update([
                'base_license_count' => $baseLicenseCount,
                'paid_overage_license_count' => $overageLicenseCount
            ]);

            $this->line("Migrated subscription {$subscription->id}: base={$baseLicenseCount}, overage={$overageLicenseCount}");
        }

        $this->info('Migration completed!');
        return self::SUCCESS;
    }
}
