<?php

namespace App\Http\Controllers\Tenant\Zkteco;

use Carbon\Carbon;
use App\Models\User;
use App\Models\ZktecoDevice;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class BiometricsController extends Controller
{
    public function getRequest(Request $request)
    {
        $sn = $request->query('SN');

        if ($sn) {
            ZktecoDevice::firstOrCreate([
                'serial_number' => $sn,
                'name' => 'Device ' . $sn,
                'staus' => 'active',
            ]);
        }
        return response('OK', 200);
    }

    public function cdata(Request $request)
    {
        $sn = $request->query('SN');
        $device = null;

        if ($sn) {
            $device = ZktecoDevice::where('serial_number', $sn)->first();
        }

        if (!$device) {
            $device = ZktecoDevice::where('ip_address', $request->ip())->first();
        }

        $payload = $request->getContent();
        if (empty($payload))

            return response('OK');

        // employee_id (employment detail) => user_id map
        $userMap = User::whereHas('employmentDetail')
            ->get()
            ->filter(fn($u) => !empty(optional($u->employmentDetail)->employee_id))
            ->mapWithKeys(fn($u) => [$u->employmentDetail->employee_id => $u->id])
            ->toArray();

        $lines = preg_split("/\r\n|\n|\r/", trim($payload));
        $saved = 0;
        $tz = config('app.timezone', 'Asia/Manila');

        foreach ($lines as $line) {
            if (!trim($line)) continue;
            $pairs = preg_split("/\t/", $line);
            $kv = [];
            foreach ($pairs as $p) {
                [$k, $v] = array_pad(explode('=', $p, 2), 2, null);
                if ($k) $kv[$k] = $v;
            }
            $emp = (string)($kv['PIN'] ?? $kv['ID'] ?? $kv['CardNo'] ?? null);
            $time = $kv['Time'] ?? null;
            if (!$emp || !$time) continue;

            $ts = Carbon::parse($time, $tz);
            $userId = $userMap[$emp] ?? null;

            AttendanceLog::firstOrCreate(
                ['device_id' => optional($device)->id, 'employee_id' => $emp, 'check_time' => $ts],
                ['user_id' => $userId, 'status' => 'checkin', 'workcode' => $kv['WorkCode'] ?? null]
            );
            $saved++;
        }

        Log::info("iClock cdata saved={$saved} SN={$sn}");
        return response('OK');
    }
}
