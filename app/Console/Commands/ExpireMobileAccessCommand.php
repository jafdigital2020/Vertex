<?php

namespace App\Console\Commands;

use App\Models\MobileAccessAssignment;
use App\Models\MobileAccessLicense;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireMobileAccessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mobile-access:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically revoke mobile access for expired license pools';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired mobile access license pools...');

        // Find all expired license pools
        $expiredPools = MobileAccessLicense::where('status', 'active')
            ->whereNotNull('pool_expires_at')
            ->where('pool_expires_at', '<=', now())
            ->get();

        if ($expiredPools->isEmpty()) {
            $this->info('No expired mobile access license pools found.');
            return 0;
        }

        $totalRevoked = 0;

        foreach ($expiredPools as $pool) {
            try {
                $this->line("Processing expired pool for tenant {$pool->tenant_id}");

                // Get all active assignments for this pool
                $activeAssignments = $pool->activeAssignments()->get();

                $this->line("  Found {$activeAssignments->count()} active assignments to revoke");

                // Revoke all assignments for this pool
                foreach ($activeAssignments as $assignment) {
                    $assignment->revoke('License pool expired - renewal payment required for entire pool');
                    $totalRevoked++;
                }

                Log::info('Revoked all assignments for expired mobile access pool', [
                    'tenant_id' => $pool->tenant_id,
                    'pool_id' => $pool->id,
                    'expired_at' => $pool->pool_expires_at,
                    'assignments_revoked' => $activeAssignments->count(),
                ]);

            } catch (\Exception $e) {
                $this->error("Failed to process pool {$pool->id}: {$e->getMessage()}");
                Log::error('Failed to process expired mobile access pool', [
                    'pool_id' => $pool->id,
                    'tenant_id' => $pool->tenant_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Processed {$expiredPools->count()} expired pools and revoked {$totalRevoked} assignments.");

        Log::info('Mobile access expiration check completed', [
            'expired_pools_count' => $expiredPools->count(),
            'total_assignments_revoked' => $totalRevoked
        ]);

        return 0;
    }
}