<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\ZktecoDevice;
use App\Models\AttendanceLog;
use App\Services\ZktecoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncZkteco extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zkteco:sync {--device_id=}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all active ZKTeco devices (IP / API / hybrid)';

    /**
     * Execute the console command.
     */
    public function handle(ZktecoService $svc)
    {
        $tz = config('app.timezone', 'Asia/Manila');

        $devices = $this->option('device_id')
            ? ZktecoDevice::where('id', $this->option('device_id'))->get()
            : ZktecoDevice::where('status', 'active')->get();

        // employee_id -> user_id map
        $userMap = User::whereHas('employmentDetail')
            ->get()
            ->filter(fn($u) => !empty(optional($u->employmentDetail)->employee_id))
            ->mapWithKeys(fn($u) => [(string)$u->employmentDetail->employee_id => $u->id])
            ->toArray();

        foreach ($devices as $device) {
            $this->info("Syncing device: {$device->name} ({$device->device_type})");

            $rows = [];
            if ($device->device_type === 'ip' || $device->device_type === 'hybrid') {
                if ($device->ip_address) {
                    $rows = $svc->fetchByIp($device->ip_address, $device->port);
                }
                if (empty($rows) && $device->device_type === 'hybrid' && $device->api_url) {
                    $rows = $svc->fetchByApi($device->api_url);
                }
            } else {
                if ($device->api_url) {
                    $rows = $svc->fetchByApi($device->api_url);
                }
            }

            if (empty($rows)) {
                $this->warn("No rows from device {$device->name}");
                continue;
            }

            DB::transaction(function () use ($rows, $device, $userMap, $tz) {
                foreach ($rows as $r) {
                    $emp = (string)$r['employee_id'];
                    $ts  = Carbon::parse($r['timestamp'], $tz);

                    $userId = $userMap[$emp] ?? null;

                    AttendanceLog::firstOrCreate(
                        ['device_id' => $device->id, 'employee_id' => $emp, 'check_time' => $ts],
                        ['user_id' => $userId, 'status' => 'checkin', 'workcode' => $r['raw']['workcode'] ?? null]
                    );
                }
            });

            $this->info("Saved " . count($rows) . " rows from {$device->name}");
        }

        return 0;
    }
}
