<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Geofence;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireGeofences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geofence:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set geofences as inactive if expiration_date has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // Update geofences that have an expiration date and are past that date
        $updated = Geofence::whereNotNull('expiration_date')
            ->where('expiration_date', '<=', $now)
            ->where('status', 'active')
            ->update(['status' => 'inactive']);

        $this->info("Geofences updated to inactive: {$updated}");
    }
}
