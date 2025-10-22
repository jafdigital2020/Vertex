<?php

namespace App\Console\Commands;

use App\Models\ZktecoDevice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Tenant\Zkteco\BiometricsController;

class BioTimeSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biotime:sync {--device_id=} {--minutes=15}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync attendance data from BioTime servers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deviceId = $this->option('device_id');
        $minutes = (int) $this->option('minutes');

        // Get BioTime devices
        $query = ZktecoDevice::where('connection_method', 'biotime')
            ->where('status', 'active');

        if ($deviceId) {
            $query->where('id', $deviceId);
        }

        $devices = $query->get();

        if ($devices->isEmpty()) {
            $this->warn('No active BioTime devices found');
            return 1;
        }

        $this->info("Starting BioTime sync for " . $devices->count() . " device(s)");

        foreach ($devices as $device) {
            try {
                $this->info("Syncing device: {$device->name} ({$device->biotime_server_url})");

                // Create request with date range (last X minutes to now)
                $startDate = now()->subMinutes($minutes)->format('Y-m-d H:i:s');
                $endDate = now()->format('Y-m-d H:i:s');

                $request = new \Illuminate\Http\Request([
                    'device_id' => $device->id,
                    'start_date' => now()->subMinutes($minutes)->format('Y-m-d'),
                    'end_date' => now()->format('Y-m-d'),
                    'start_time' => $startDate,
                    'end_time' => $endDate
                ]);

                // Call the controller method
                $controller = app(BiometricsController::class);
                $response = $controller->fetchAttendanceFromBioTime($request);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    $processed = $data['processed_records'] ?? 0;
                    $total = $data['total_records'] ?? 0;

                    $this->info("✅ {$device->name}: {$processed}/{$total} records processed");

                    Log::info('Scheduled BioTime sync completed', [
                        'device_id' => $device->id,
                        'device_name' => $device->name,
                        'processed' => $processed,
                        'total' => $total,
                        'date_range' => $data['date_range'] ?? null
                    ]);
                } else {
                    $this->error("❌ {$device->name}: Sync failed");

                    Log::error('Scheduled BioTime sync failed', [
                        'device_id' => $device->id,
                        'device_name' => $device->name,
                        'status' => $response->getStatusCode()
                    ]);
                }
            } catch (\Exception $e) {
                $this->error("❌ {$device->name}: Error - " . $e->getMessage());

                Log::error('Scheduled BioTime sync exception', [
                    'device_id' => $device->id,
                    'device_name' => $device->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info('BioTime sync completed');
        return 0;
    }
}
