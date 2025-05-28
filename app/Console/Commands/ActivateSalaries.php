<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\SalaryRecord;
use Illuminate\Console\Command;

class ActivateSalaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary:activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate salary records based on their effective date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        // Get all salary records scheduled for today
        $recordsToActivate = SalaryRecord::whereDate('effective_date', $today)
            ->where('is_active', false)
            ->get();

        foreach ($recordsToActivate as $record) {
            // Deactivate existing active record for that user
            SalaryRecord::where('user_id', $record->user_id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Activate the new salary
            $record->update(['is_active' => true]);
        }

        $this->info("Salary activation complete. Activated: {$recordsToActivate->count()} record(s).");
    }
}
