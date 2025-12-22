<?php

namespace App\Console\Commands;

use App\Models\MobileAccessAssignment;
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
    protected $description = 'Automatically revoke expired mobile access assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired mobile access assignments...');

        // Find all expired assignments
        $expiredAssignments = MobileAccessAssignment::expired()->get();

        if ($expiredAssignments->isEmpty()) {
            $this->info('No expired mobile access assignments found.');
            return 0;
        }

        $count = 0;

        foreach ($expiredAssignments as $assignment) {
            try {
                // Check if auto-renewal is enabled
                if ($assignment->auto_renewal) {
                    $this->line("Auto-renewing assignment for user {$assignment->user_id}");
                    $assignment->renew();
                } else {
                    $this->line("Revoking expired assignment for user {$assignment->user_id}");
                    $assignment->revoke('Automatically revoked due to expiration');
                }
                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to process assignment {$assignment->id}: {$e->getMessage()}");
                Log::error('Failed to process expired mobile access assignment', [
                    'assignment_id' => $assignment->id,
                    'user_id' => $assignment->user_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Processed {$count} expired mobile access assignments.");
        
        Log::info('Mobile access expiration check completed', [
            'expired_count' => $expiredAssignments->count(),
            'processed_count' => $count
        ]);

        return 0;
    }
}