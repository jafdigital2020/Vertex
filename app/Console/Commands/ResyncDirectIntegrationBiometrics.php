<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\AttendanceLog;
use Illuminate\Console\Command;
use App\Http\Controllers\Tenant\Zkteco\BiometricsController;

class ResyncDirectIntegrationBiometrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zkteco:resync-direct
                            {--start= : Start datetime (Y-m-d H:i:s)}
                            {--end=   : End datetime   (Y-m-d H:i:s)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-process missed ATTLOG entries from direct ZKTeco integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start = $this->option('start')
            ? Carbon::parse($this->option('start'))
            : Carbon::now()->subDay()->startOfDay();

        $end = $this->option('end')
            ? Carbon::parse($this->option('end'))
            : Carbon::now();

        $this->info("Re-processing logs from {$start} to {$end}");

        $controller = app(BiometricsController::class);

        AttendanceLog::whereBetween('check_time', [$start, $end])
            ->chunkById(200, function ($logs) use ($controller) {
                foreach ($logs as $log) {
                    $this->line("→ Processing log #{$log->id} @ {$log->check_time}");
                    // uses the same real-time logic that will firstOrCreate Attendance
                    $controller->processAttendanceInRealTime($log);
                }
            });

        $this->info('Done. Any existing Attendance records were skipped automatically.');
        return 0;
    }
}
