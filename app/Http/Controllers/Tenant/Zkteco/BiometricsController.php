<?php

namespace App\Http\Controllers\Tenant\Zkteco;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\ZktecoDevice;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class BiometricsController extends Controller
{
    public function getRequest(Request $request)
    {
        $sn = $request->query('SN');

        // (optional) register/update device
        if ($sn) {
            \App\Models\ZktecoDevice::updateOrCreate(
                ['serial_number' => $sn],
                ['name' => 'ZKTeco ' . $sn, 'status' => 'active', 'ip_address' => $request->ip(), 'last_activity' => now()]
            );
        }

        // WIDEN window + set real-time
        $start = now('Asia/Manila')->subDays(30)->format('Y-m-d H:i:s');
        $end   = now('Asia/Manila')->addHours(1)->format('Y-m-d H:i:s');

        $cmds = [];
        $cmds[] = "C:SET OPTION RealTime=1";
        $cmds[] = "C:SET OPTION TransTimes=00:00;23:59";
        $cmds[] = "C:SET OPTION Encrypt=0";
        // Force re-upload (enable muna habang debug; pwede mong alisin pag okay na)
        $cmds[] = "C:SET OPTION LogStamp=0";
        $cmds[] = "C:SET OPTION AttLogStamp=0";
        $cmds[] = "C:DATA QUERY ATTLOG StartTime={$start} EndTime={$end}";

        $payload = implode("\n", $cmds) . "\n";

        Log::info('Sending commands via getrequest', ['sn' => $sn, 'payload' => $payload]);

        return response($payload, 200)->header('Content-Type', 'text/plain');
    }

    public function cdata(Request $request)
    {
        $sn    = $request->query('SN');
        $table = strtoupper($request->query('table', 'ATTLOG'));

        if ($request->isMethod('get')) {
            Log::info('ZKTeco cdata GET (handshake)', ['sn' => $sn, 'params' => $request->all()]);

            // TEMPORARY: also push commands here (some firmwares expect it here)
            $start = now('Asia/Manila')->subDays(30)->format('Y-m-d H:i:s');
            $end   = now('Asia/Manila')->addHours(1)->format('Y-m-d H:i:s');

            $cmds = [];
            $cmds[] = "C:SET OPTION RealTime=1";
            $cmds[] = "C:SET OPTION TransTimes=00:00;23:59";
            $cmds[] = "C:SET OPTION Encrypt=0";
            $cmds[] = "C:SET OPTION LogStamp=0";
            $cmds[] = "C:SET OPTION AttLogStamp=0";
            $cmds[] = "C:DATA QUERY ATTLOG StartTime={$start} EndTime={$end}";

            $payload = implode("\n", $cmds) . "\n";
            return response($payload, 200)->header('Content-Type', 'text/plain');
        }

        // POST (data upload) below...
        $content = $request->getContent();
        $len     = strlen($content);

        Log::info('ZKTeco cdata POST', [
            'sn' => $sn,
            'table' => $table,
            'ip' => $request->ip(),
            'content_length' => $len,
            'preview' => $len ? mb_substr($content, 0, 200) : 'EMPTY'
        ]);

        $device = $sn
            ? \App\Models\ZktecoDevice::where('serial_number', $sn)->first()
            : \App\Models\ZktecoDevice::where('ip_address', $request->ip())->first();

        if ($len === 0) {
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        $saved = 0;
        if ($table === 'ATTLOG') {
            $saved = $this->processAttendanceData($content, $device); // (yung dati mong parser)
        }

        if ($device) $device->update(['last_activity' => now(), 'ip_address' => $request->ip()]);

        Log::info('cdata processed', ['sn' => $sn, 'saved' => $saved]);
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }


    private function processAttendanceData($payload, $device)
    {
        Log::info('Processing attendance data', ['payload' => $payload]);

        // Get employee_id => user_id mapping
        $userMap = $this->buildUserMapping();

        Log::info('User mapping created', ['count' => count($userMap)]);

        $lines = preg_split("/\r\n|\n|\r/", trim($payload));
        $saved = 0;
        $tz = config('app.timezone', 'Asia/Manila');

        foreach ($lines as $line) {
            if (!trim($line)) continue;

            // Skip non-ATTLOG
            if (stripos($line, 'ATTLOG') === false) {
                Log::debug('Non-ATTLOG line skipped', ['line' => $line]);
                continue;
            }

            // 1) Try key=value style (comma/space/tab delimited)
            $kv = [];
            // Replace commas with tabs para mas madali i-split
            $normalized = str_replace([", ", ","], "\t", $line);
            $parts = preg_split("/\t|\s{2,}/", $normalized); // hatiin by tab or multiple spaces

            foreach ($parts as $p) {
                // find PIN=, Time=, VerifyType=/Verified=, Status=/State=, WorkCode=
                if (strpos($p, '=') !== false) {
                    [$k, $v] = array_pad(explode('=', trim($p), 2), 2, null);
                    if ($k && $v) $kv[trim($k)] = trim($v);
                }
            }

            $pin  = $kv['PIN'] ?? $kv['ID'] ?? $kv['CardNo'] ?? null;
            $time = $kv['Time'] ?? null;

            // 2) If still missing, try compact form: "ATTLOG PIN TIME X Y Z"
            if ((!$pin || !$time) && preg_match('/^ATTLOG\s+(\S+)\s+([0-9\-]{10}\s[0-9:]{8})/i', $line, $m)) {
                $pin  = $pin  ?: $m[1];
                $time = $time ?: $m[2];
            }

            if (!$pin || !$time) {
                Log::warning('Invalid ATTLOG (no PIN/Time)', ['line' => $line, 'kv' => $kv]);
                continue;
            }

            try {
                $ts = Carbon::parse($time, $tz);

                // VerifyType key varies: VerifyType or Verified
                $verifyType = $kv['VerifyType'] ?? $kv['Verified'] ?? null;
                // State/Status can be string/int
                $state = $kv['State'] ?? $kv['Status'] ?? 0;
                $workCode = $kv['WorkCode'] ?? null;

                // your mapping method already accepts data array
                $status = $this->determineStatus(['State' => $state, 'Status' => $state]);

                $userId = $userMap[$pin] ?? null;

                if ($userId) {
                    $user = User::with('employmentDetail')->find($userId);
                    $mappingType = !empty($user->employmentDetail->biometrics_id) && $user->employmentDetail->biometrics_id == $pin
                        ? 'biometrics_id'
                        : 'employee_id';

                    Log::debug('User found via mapping', [
                        'pin' => $pin,
                        'user_id' => $userId,
                        'mapping_type' => $mappingType,
                        'user_name' => $user->name ?? 'Unknown'
                    ]);
                } else {
                    Log::warning('No user mapping found', [
                        'pin' => $pin,
                        'available_mappings' => array_keys($userMap)
                    ]);
                }

                $attendanceLog = AttendanceLog::firstOrCreate(
                    [
                        'device_id'  => optional($device)->id,
                        'employee_id' => (string) $pin,
                        'check_time' => $ts,
                    ],
                    [
                        'user_id'     => $userId,
                        'status'      => $status,
                        'workcode'    => $workCode,
                        'verify_type' => $verifyType,
                        'raw_data'    => json_encode(['line' => $line, 'kv' => $kv], JSON_UNESCAPED_UNICODE),
                    ]
                );

                if ($attendanceLog->wasRecentlyCreated) {
                    $saved++;
                    if ($userId) {
                        $this->processAttendanceInRealTime($attendanceLog);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error parsing ATTLOG', ['line' => $line, 'error' => $e->getMessage()]);
            }
        }

        Log::info('Attendance processing complete', ['saved' => $saved]);
        return $saved;
    }

    // ✅ NEW: Real-time processing method
    private function processAttendanceInRealTime(AttendanceLog $attendanceLog)
    {
        if (!$attendanceLog->user_id) {
            Log::warning('No user found for attendance log', ['log_id' => $attendanceLog->id]);
            return;
        }

        $user = User::find($attendanceLog->user_id);
        $date = $attendanceLog->check_time->format('Y-m-d');

        // Find or create attendance record
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'attendance_date' => $date,
            ],
            [
                'status' => 'present',
            ]
        );

        // Update based on status (in/out)
        if ($attendanceLog->status === 'in') {
            // Clock IN
            if (!$attendance->date_time_in) {
                $attendance->date_time_in = $attendanceLog->check_time;
                $attendance->clock_in_method = 'biometric';
            } else {
                // Multiple login
                $multipleLogin = $attendance->multiple_login ?? [];
                $multipleLogin[] = [
                    'in' => $attendanceLog->check_time->toDateTimeString(),
                ];
                $attendance->multiple_login = $multipleLogin;
            }
        } else {
            // Clock OUT
            $attendance->date_time_out = $attendanceLog->check_time;
            $attendance->clock_out_method = 'biometric';

            // Calculate work minutes if both in and out exist
            if ($attendance->date_time_in && $attendance->date_time_out) {
                $attendance->total_work_minutes = $attendance->date_time_in
                    ->diffInMinutes($attendance->date_time_out);
            }
        }

        $attendance->save();

        Log::info('✅ REAL-TIME: Attendance processed from ZKTeco', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'log_id' => $attendanceLog->id,
            'processing_time' => microtime(true) - LARAVEL_START,
        ]);
    }

    private function determineStatus($data)
    {
        $state = $data['State'] ?? $data['Status'] ?? 0;

        // ✅ FIXED: Return values that match your migration enum
        switch ($state) {
            case '0':
            case 0:
                return 'in';  // ✅ Changed from 'checkin'
            case '1':
            case 1:
                return 'out'; // ✅ Changed from 'checkout'
            case '2':
            case 2:
                return 'out'; // break_out -> out
            case '3':
            case 3:
                return 'in';  // break_in -> in
            case '4':
            case 4:
                return 'in';  // overtime_in -> in
            case '5':
            case 5:
                return 'out'; // overtime_out -> out
            default:
                return 'in';  // Default fallback
        }
    }

    private function processUserData($payload, $device)
    {
        Log::info('Processing user data', ['device' => $device?->id]);
        return 0;
    }

    private function getPendingCommands($serialNumber)
    {
        if (!$serialNumber) return null;

        $device = ZktecoDevice::where('serial_number', $serialNumber)->first();
        if (!$device) return null;

        return null; // Let device send attendance data freely
    }

    public function deviceCommand(Request $request)
    {
        $sn   = $request->query('SN');
        $body = $request->getContent();

        Log::info('ZKTeco devicecmd', [
            'sn'      => $sn,
            'ip'      => $request->ip(),
            'preview' => $body ? mb_substr($body, 0, 160) : '',
        ]);

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    public function deviceStatus(Request $request)
    {
        $sn = $request->query('SN');

        Log::info('Device status check', [
            'sn' => $sn,
            'ip' => $request->ip(),
            'timestamp' => now()
        ]);

        if ($sn) {
            $device = ZktecoDevice::where('serial_number', $sn)->first();
            if ($device) {
                $device->update([
                    'last_activity' => now(),
                    'ip_address' => $request->ip()
                ]);
            }
        }

        return response('OK');
    }

    // BIOTIME CONNECTION TEST
    public function testBioTimeConnection(Request $request)
    {
        $deviceId = $request->input('device_id');
        $device = ZktecoDevice::find($deviceId);

        if (!$device || !$device->usesBioTime()) {
            return response()->json([
                'error' => 'Device not found or not configured for BioTime'
            ], 404);
        }

        try {
            Log::info('Testing BioTime connection', [
                'device_id' => $deviceId,
                'server_url' => $device->biotime_server_url,
                'username' => $device->biotime_username
            ]);

            $response = Http::timeout(10)->post($device->biotime_server_url . '/jwt-api-token-auth/', [
                'username' => $device->biotime_username,
                'password' => $device->biotime_password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['token'] ?? null;

                Log::info('BioTime connection successful', [
                    'device_id' => $deviceId,
                    'token_length' => strlen($token ?? '')
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'BioTime connection successful',
                    'server' => $device->biotime_server_url,
                    'device_name' => $device->name,
                    'username' => $device->biotime_username,
                    'token_preview' => $token ? substr($token, 0, 20) . '...' : null
                ]);
            }

            Log::error('BioTime authentication failed', [
                'device_id' => $deviceId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'status' => $response->status(),
                'server' => $device->biotime_server_url,
                'response' => $response->body()
            ], 401);
        } catch (\Exception $e) {
            Log::error('BioTime connection error', [
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
                'server' => $device->biotime_server_url
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'server' => $device->biotime_server_url
            ], 500);
        }
    }

    /**
     * Fetch attendance from BioTime
     */
    public function fetchAttendanceFromBioTime(Request $request)
    {
        $deviceId = $request->input('device_id');
        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // ✅ NEW: Support for precise time filtering (for real-time sync)
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        if (!$startTime) {
            $startTime = $startDate . ' 00:00:00';
        }
        if (!$endTime) {
            $endTime = $endDate . ' 23:59:59';
        }

        $device = ZktecoDevice::find($deviceId);

        if (!$device || !$device->usesBioTime()) {
            return response()->json([
                'error' => 'Device not found or not configured for BioTime'
            ], 404);
        }

        // Step 1: Authenticate with BioTime
        try {
            Log::info('BioTime Real-time Sync', [
                'device_id' => $deviceId,
                'server' => $device->biotime_server_url,
                'time_range' => "{$startTime} to {$endTime}"
            ]);

            $authResponse = Http::timeout(10)->post($device->biotime_server_url . '/jwt-api-token-auth/', [
                'username' => $device->biotime_username,
                'password' => $device->biotime_password,
            ]);

            if (!$authResponse->successful()) {
                Log::error('BioTime authentication failed', [
                    'status' => $authResponse->status(),
                    'response' => $authResponse->body()
                ]);
                return response()->json([
                    'error' => 'Failed to authenticate with BioTime',
                    'status' => $authResponse->status()
                ], 401);
            }

            $token = $authResponse->json()['token'] ?? null;
            if (!$token) {
                return response()->json(['error' => 'No token received from BioTime'], 401);
            }

            // Step 2: Fetch attendance data with precise time filtering
            $url = $device->biotime_server_url . '/iclock/api/transactions/';

            $params = [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'page_size' => 1000,
                'ordering' => '-punch_time', // Latest first for real-time
            ];

            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'JWT ' . $token,
                'Content-Type' => 'application/json',
            ])->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                $transactions = $data['data'] ?? [];

                Log::info('BioTime attendance fetched successfully', [
                    'device_id' => $deviceId,
                    'count' => count($transactions),
                    'time_range' => "{$startTime} to {$endTime}",
                    'is_realtime' => $request->has('start_time') // Flag for real-time sync
                ]);

                $processed = $this->processBioTimeAttendance($transactions, $device);

                // ✅ Update device last activity
                $device->update(['last_activity' => now()]);

                return response()->json([
                    'success' => true,
                    'message' => 'Attendance data fetched from BioTime',
                    'device_name' => $device->name,
                    'server' => $device->biotime_server_url,
                    'total_records' => count($transactions),
                    'processed_records' => $processed,
                    'time_range' => "{$startTime} to {$endTime}",
                    'sync_type' => $request->has('start_time') ? 'real-time' : 'batch',
                    'sample_transaction' => count($transactions) > 0 ? $transactions[0] : null
                ]);
            }

            Log::error('Failed to fetch attendance data', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return response()->json([
                'error' => 'Failed to fetch attendance data',
                'status' => $response->status(),
                'response' => $response->body()
            ], 500);
        } catch (\Exception $e) {
            Log::error('BioTime fetch error', [
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Connection error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process BioTime attendance data
     */
    private function processBioTimeAttendance($transactions, ZktecoDevice $device)
    {
        Log::info('Processing BioTime attendance data', [
            'device_id' => $device->id,
            'count' => count($transactions)
        ]);

        // Get employee mapping
        $userMap = $this->buildUserMapping();

        Log::info('User mapping created', ['count' => count($userMap)]);

        $processed = 0;
        $tz = config('app.timezone', 'Asia/Manila');

        foreach ($transactions as $transaction) {
            try {
                $employeeCode = $transaction['emp_code'] ?? null;
                $punchTime = $transaction['punch_time'] ?? null;
                $punchState = $transaction['punch_state'] ?? 0;
                $verifyType = $transaction['verify_type'] ?? null;

                if (!$employeeCode || !$punchTime) {
                    Log::debug('Skipping invalid transaction', ['transaction' => $transaction]);
                    continue;
                }

                $checkTime = Carbon::parse($punchTime, $tz);
                $userId = $userMap[$employeeCode] ?? null;

                if ($userId) {
                    $user = User::with('employmentDetail')->find($userId);
                    $mappingType = !empty($user->employmentDetail->biometrics_id) && $user->employmentDetail->biometrics_id == $employeeCode
                        ? 'biometrics_id'
                        : 'employee_id';

                    Log::debug('BioTime user found via mapping', [
                        'employee_code' => $employeeCode,
                        'user_id' => $userId,
                        'mapping_type' => $mappingType,
                        'user_name' => $user->name ?? 'Unknown'
                    ]);
                } else {
                    Log::warning('No BioTime user mapping found', [
                        'employee_code' => $employeeCode,
                        'available_mappings' => array_keys($userMap)
                    ]);
                }


                $status = $this->determineBioTimeStatus($punchState);

                Log::debug('Processing transaction', [
                    'employee_code' => $employeeCode,
                    'punch_time' => $punchTime,
                    'punch_state' => $punchState,
                    'status' => $status,
                    'user_id' => $userId
                ]);

                $attendanceLog = AttendanceLog::firstOrCreate(
                    [
                        'device_id' => $device->id,
                        'employee_id' => (string) $employeeCode,
                        'check_time' => $checkTime,
                    ],
                    [
                        'user_id' => $userId,
                        'status' => $status,
                        'verify_type' => $verifyType,
                        'raw_data' => json_encode($transaction, JSON_UNESCAPED_UNICODE),
                    ]
                );

                if ($attendanceLog->wasRecentlyCreated) {
                    $processed++;
                    if ($userId) {
                        $this->processAttendanceInRealTime($attendanceLog);
                    }

                    Log::info('NEW ATTENDANCE LOG CREATED FROM BIOTIME!', [
                        'device_id' => $device->id,
                        'employee_id' => $employeeCode,
                        'status' => $status,
                        'time' => $checkTime->toDateTimeString(),
                        'user_id' => $userId
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error processing BioTime transaction', [
                    'device_id' => $device->id,
                    'transaction' => $transaction,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('BioTime attendance processing complete', [
            'device_id' => $device->id,
            'processed' => $processed
        ]);

        return $processed;
    }

    /**
     * Determine status from BioTime punch state
     */
    private function determineBioTimeStatus($punchState)
    {
        switch ($punchState) {
            case 0:
            case '0':
                return 'in';   // Check In
            case 1:
            case '1':
                return 'out';  // Check Out
            case 2:
            case '2':
                return 'out';  // Break Out
            case 3:
            case '3':
                return 'in';   // Break In
            case 4:
            case '4':
                return 'in';   // Overtime In
            case 5:
            case '5':
                return 'out';  // Overtime Out
            default:
                return 'in';            // Default
        }
    }

    private function buildUserMapping()
    {
        $users = User::whereHas('employmentDetail')
            ->with('employmentDetail')
            ->get();

        $userMap = [];

        foreach ($users as $user) {
            $employment = $user->employmentDetail;
            if (!$employment) continue;

            // Priority 1: Use biometrics_id if available
            if (!empty($employment->biometrics_id)) {
                $userMap[$employment->biometrics_id] = $user->id;
                Log::debug('User mapped via biometrics_id', [
                    'user_id' => $user->id,
                    'biometrics_id' => $employment->biometrics_id,
                    'name' => $user->name
                ]);
            }
            // Priority 2: Fallback to employee_id if biometrics_id is null
            elseif (!empty($employment->employee_id)) {
                $userMap[$employment->employee_id] = $user->id;
                Log::debug('User mapped via employee_id (fallback)', [
                    'user_id' => $user->id,
                    'employee_id' => $employment->employee_id,
                    'name' => $user->name
                ]);
            }
        }

        Log::info('User mapping created with biometrics_id priority', [
            'total_mappings' => count($userMap),
            'biometrics_mappings' => User::whereHas('employmentDetail', function ($q) {
                $q->whereNotNull('biometrics_id');
            })->count(),
            'employee_id_mappings' => User::whereHas('employmentDetail', function ($q) {
                $q->whereNull('biometrics_id')->whereNotNull('employee_id');
            })->count()
        ]);

        return $userMap;
    }
}
