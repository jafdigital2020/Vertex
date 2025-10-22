<?php

namespace App\Services;

use GuzzleHttp\Client;
use Rats\Zkteco\Lib\ZKTeco;
use Carbon\Carbon;

class ZktecoService
{
    public function fetchByIp(string $ip, int $port = 4370): array
    {
        $sdk = new ZKTeco($ip, $port);
        if (!$sdk->connect()) return [];
        $rows = $sdk->getAttendance() ?: [];
        $sdk->disconnect();
        return $this->normalize($rows);
    }

    public function fetchByApi(string $apiUrl, array $params = []): array
    {
        $client = new Client(['timeout' => 30, 'verify' => false]);
        $resp = $client->get($apiUrl, ['query' => $params]);
        $data = json_decode((string)$resp->getBody(), true);
        // normalize according to your API's structure:
        return $this->normalize($data['data'] ?? $data);
    }

    protected function normalize(array $rows): array
    {
        $tz = config('app.timezone', 'Asia/Manila');
        $out = [];
        foreach ($rows as $r) {
            $emp = $r['id'] ?? $r['employee_id'] ?? $r['pin'] ?? ($r['uid'] ?? null);
            $ts  = $r['timestamp'] ?? $r['check_time'] ?? $r['time'] ?? null;
            if (!$emp || !$ts) continue;
            $out[] = [
                'employee_id' => (string)$emp,
                'timestamp'   => Carbon::parse($ts, $tz)->toDateTimeString(),
                'raw'         => $r,
            ];
        }
        return $out;
    }
}
