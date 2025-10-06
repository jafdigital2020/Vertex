<?php

namespace App\Http\Controllers\Tenant\Zkteco;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\ZktecoDevice;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\ShiftAssignment;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class BiometricsController extends Controller
{
    public function getRequest(Request $request)
    {
        $sn = $request->query('SN');

        $device = ZktecoDevice::where('serial_number', $sn)
            ->where('connection_method', 'direct')
            ->where('status', 'active')
            ->first();

        if (!$device) {
            Log::warning('Unauthorized or non-direct device tried to handshake', [
                'sn' => $sn,
                'ip' => $request->ip(),
            ]);
            return response('UNAUTHORIZED DEVICE', 403)->header('Content-Type', 'text/plain');
        }

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

        $device = ZktecoDevice::where('serial_number', $sn)
            ->where('status', 'active')
            ->where('connection_method', 'direct')
            ->first();

        if (!$device) {
            Log::warning('Unauthorized or non-direct device attempted to connect', [
                'sn' => $sn,
                'ip' => $request->ip(),
            ]);
            return response('UNAUTHORIZED DEVICE', 403)->header('Content-Type', 'text/plain');
        }

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

    // Real-time processing method
    private function processAttendanceInRealTime(AttendanceLog $attendanceLog)
    {
        if (!$attendanceLog->user_id) {
            Log::warning('No user found for attendance log', ['log_id' => $attendanceLog->id]);
            return;
        }

        $user = User::find($attendanceLog->user_id);
        $date = $attendanceLog->check_time->format('Y-m-d');
        $checkTime = $attendanceLog->check_time;
        $dayOfWeek = strtolower($checkTime->format('D')); // mon, tue, wed, etc.

        Log::info('Processing real-time attendance with shift validation', [
            'user_id' => $user->id,
            'employee_id' => $attendanceLog->employee_id,
            'date' => $date,
            'day_of_week' => $dayOfWeek,
            'check_time' => $checkTime->toDateTimeString(),
            'status' => $attendanceLog->status
        ]);

        // ✅ NEW: Find valid shift assignment for this day
        $validAssignment = $this->findValidShiftAssignment($user, $date, $dayOfWeek);

        if (!$validAssignment) {
            Log::warning('No valid shift assignment found - attendance stays in logs only', [
                'user_id' => $user->id,
                'employee_id' => $attendanceLog->employee_id,
                'date' => $date,
                'day_of_week' => $dayOfWeek
            ]);
            return; // ✅ Stay in attendance_logs only, don't sync to attendance table
        }

        $shift = $validAssignment->shift;

        Log::info('Valid shift assignment found', [
            'assignment_id' => $validAssignment->id,
            'shift_id' => $shift->id,
            'shift_name' => $shift->name,
            'is_flexible' => $shift->is_flexible,
            'grace_period' => $shift->grace_period,
            'max_hours' => $shift->maximum_allowed_hours
        ]);

        // ✅ NEW: Calculate late status with grace period
        $lateDetails = $this->calculateLateStatus($checkTime, $shift, $attendanceLog->status);

        // Find or create attendance record with shift information
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'attendance_date' => $date,
                'shift_id' => $shift->id,
                'shift_assignment_id' => $validAssignment->id,
            ],
            [
                'status' => $lateDetails['status'],
                'total_late_minutes' => $lateDetails['late_minutes'],
            ]
        );

        // Update based on status (in/out)
        if ($attendanceLog->status === 'in') {
            $this->processClockIn($attendance, $attendanceLog, $lateDetails);
        } else {
            $this->processClockOut($attendance, $attendanceLog, $shift);
        }

        Log::info('✅ REAL-TIME: Attendance synced to main table with shift validation', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'shift_id' => $shift->id,
            'shift_name' => $shift->name,
            'log_id' => $attendanceLog->id,
            'status' => $lateDetails['status'],
            'late_minutes' => $lateDetails['late_minutes']
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

    /**
     * ✅ NEW: Find valid shift assignment for specific date and day
     */
    private function findValidShiftAssignment($user, $date, $dayOfWeek)
    {
        $assignments = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->get()
            ->filter(function ($assignment) use ($date, $dayOfWeek) {
                // ✅ Skip if no shift exists
                if (!$assignment->shift) {
                    Log::warning('Assignment has no shift', [
                        'assignment_id' => $assignment->id,
                        'shift_id' => $assignment->shift_id
                    ]);
                    return false;
                }

                // ✅ Skip excluded dates
                if ($assignment->excluded_dates && in_array($date, $assignment->excluded_dates)) {
                    return false;
                }

                // ✅ Check assignment type
                if ($assignment->type === 'recurring') {
                    $start = Carbon::parse($assignment->start_date);
                    $end = $assignment->end_date ? Carbon::parse($assignment->end_date) : now();

                    return $start->lte($date)
                        && $end->gte($date)
                        && in_array($dayOfWeek, $assignment->days_of_week ?? []);
                }

                if ($assignment->type === 'custom') {
                    return in_array($date, $assignment->custom_dates ?? []);
                }

                return false;
            })
            ->sortBy(function ($assignment) {
                return $assignment->shift->start_time ?? '00:00:00';
            });

        if ($assignments->isEmpty()) {
            return null;
        }

        // ✅ Check if already has attendance for any assignment today
        foreach ($assignments as $assignment) {
            $existingAttendance = Attendance::where('user_id', $user->id)
                ->where('attendance_date', $date)
                ->where('shift_id', $assignment->shift_id)
                ->where('shift_assignment_id', $assignment->id)
                ->first();

            if (!$existingAttendance) {
                return $assignment; // Return first available assignment
            }
        }

        // ✅ If all assignments have attendance, return the latest one for clock-out
        return $assignments->last();
    }

    /**
     * ✅ NEW: Calculate late status with grace period
     */
    private function calculateLateStatus($checkTime, $shift, $logStatus)
    {
        // ✅ If flexible shift, never late
        if ($shift->is_flexible) {
            return [
                'status' => 'present',
                'late_minutes' => 0,
                'is_within_grace' => true
            ];
        }

        // ✅ Only check lateness for clock-in
        if ($logStatus !== 'in' || !$shift->start_time) {
            return [
                'status' => 'present',
                'late_minutes' => 0,
                'is_within_grace' => true
            ];
        }

        $date = $checkTime->format('Y-m-d');
        $shiftStart = Carbon::parse("{$date} {$shift->start_time}");
        $gracePeriod = $shift->grace_period ?? 0;

        // ✅ Calculate lateness
        if ($checkTime->lte($shiftStart)) {
            // Early or on time
            return [
                'status' => 'present',
                'late_minutes' => 0,
                'is_within_grace' => true
            ];
        }

        $lateMinutes = floor($shiftStart->diffInMinutes($checkTime));
        $isWithinGrace = $lateMinutes <= $gracePeriod;

        return [
            'status' => $isWithinGrace ? 'present' : 'late',
            'late_minutes' => $isWithinGrace ? 0 : $lateMinutes,
            'is_within_grace' => $isWithinGrace,
            'shift_start' => $shiftStart->toTimeString(),
            'grace_period' => $gracePeriod
        ];
    }

    /**
     * ✅ NEW: Process clock-in with shift validation
     */
    private function processClockIn($attendance, $attendanceLog, $lateDetails)
    {
        if (!$attendance->date_time_in) {
            // First clock-in
            $attendance->update([
                'date_time_in' => $attendanceLog->check_time,
                'clock_in_method' => 'biometric',
                'status' => $lateDetails['status'],
                'total_late_minutes' => $lateDetails['late_minutes'],
            ]);

            Log::info('Clock-in processed', [
                'attendance_id' => $attendance->id,
                'check_time' => $attendanceLog->check_time->toDateTimeString(),
                'status' => $lateDetails['status'],
                'late_minutes' => $lateDetails['late_minutes'],
                'grace_period' => $lateDetails['grace_period'] ?? 0
            ]);
        } else {
            // Multiple clock-in (break return or multiple entry)
            $multipleLogin = $attendance->multiple_login ?? [];
            $multipleLogin[] = [
                'in' => $attendanceLog->check_time->toDateTimeString(),
                'status' => $lateDetails['status'],
                'late_minutes' => $lateDetails['late_minutes']
            ];

            $attendance->update([
                'multiple_login' => $multipleLogin
            ]);

            Log::info('Multiple clock-in processed', [
                'attendance_id' => $attendance->id,
                'check_time' => $attendanceLog->check_time->toDateTimeString(),
                'multiple_login_count' => count($multipleLogin)
            ]);
        }
    }

    /**
     * ✅ NEW: Process clock-out with max hours validation
     */
    private function processClockOut($attendance, $attendanceLog, $shift)
    {
        if (!$attendance->date_time_in) {
            Log::warning('Clock-out without clock-in detected', [
                'attendance_id' => $attendance->id,
                'employee_id' => $attendanceLog->employee_id
            ]);
            return;
        }

        $clockOutTime = $attendanceLog->check_time;
        $totalMinutes = $attendance->date_time_in->diffInMinutes($clockOutTime);

        // ✅ Apply maximum allowed hours cap
        $maxAllowedHours = $shift->maximum_allowed_hours;
        if ($maxAllowedHours) {
            $maxMinutes = $maxAllowedHours * 60;
            if ($totalMinutes > $maxMinutes) {
                Log::info('Work hours capped due to maximum limit', [
                    'attendance_id' => $attendance->id,
                    'actual_minutes' => $totalMinutes,
                    'max_allowed_hours' => $maxAllowedHours,
                    'capped_minutes' => $maxMinutes
                ]);
                $totalMinutes = $maxMinutes;
            }
        }

        // ✅ Calculate night differential (10 PM to 6 AM)
        $nightDiffMinutes = $this->calculateNightDifferential(
            $attendance->date_time_in,
            $clockOutTime
        );

        // ✅ Calculate undertime (if applicable)
        $undertimeMinutes = $this->calculateUndertime(
            $clockOutTime,
            $attendance->attendance_date,
            $shift
        );

        $attendance->update([
            'date_time_out' => $clockOutTime,
            'clock_out_method' => 'biometric',
            'total_work_minutes' => max(0, $totalMinutes - $nightDiffMinutes), // Regular work hours
            'total_night_diff_minutes' => $nightDiffMinutes,
            'total_undertime_minutes' => $undertimeMinutes,
        ]);

        Log::info('Clock-out processed with shift limits', [
            'attendance_id' => $attendance->id,
            'clock_out_time' => $clockOutTime->toDateTimeString(),
            'total_work_minutes' => $totalMinutes - $nightDiffMinutes,
            'night_diff_minutes' => $nightDiffMinutes,
            'undertime_minutes' => $undertimeMinutes,
            'max_allowed_hours' => $maxAllowedHours,
            'shift_end_time' => $shift->end_time
        ]);
    }

    /**
     * ✅ NEW: Calculate night differential (10 PM - 6 AM)
     */
    private function calculateNightDifferential($clockIn, $clockOut)
    {
        $nightDiffMinutes = 0;
        $currentStart = $clockIn->copy();
        $currentEnd = $clockOut->copy();

        while ($currentStart->lt($currentEnd)) {
            $dayStart = $currentStart->copy()->startOfDay();

            // Night shift window: 22:00 to 06:00 next day
            $nightStart = $dayStart->copy()->setTime(22, 0, 0);
            $nightEnd = $dayStart->copy()->addDay()->setTime(6, 0, 0);

            // Find overlap between work period and night period
            $workStart = max($currentStart->timestamp, $nightStart->timestamp);
            $workEnd = min($currentEnd->timestamp, $nightEnd->timestamp);

            if ($workEnd > $workStart) {
                $dayNightMinutes = ($workEnd - $workStart) / 60;
                $nightDiffMinutes += $dayNightMinutes;
            }

            // Move to next day
            $currentStart = $dayStart->copy()->addDay();
        }

        return max(0, floor($nightDiffMinutes));
    }

    /**
     * ✅ NEW: Calculate undertime based on shift end time
     */
    private function calculateUndertime($clockOut, $attendanceDate, $shift)
    {
        if ($shift->is_flexible || !$shift->end_time) {
            return 0; // No undertime for flexible shifts
        }

        $scheduledEnd = Carbon::parse($attendanceDate->toDateString() . ' ' . $shift->end_time);

        // Handle night shifts that end next day
        if ($shift->end_time < $shift->start_time) {
            $scheduledEnd->addDay();
        }

        if ($clockOut->lt($scheduledEnd)) {
            $undertimeMinutes = $clockOut->diffInMinutes($scheduledEnd);

            Log::info('Undertime calculated', [
                'clock_out' => $clockOut->toDateTimeString(),
                'scheduled_end' => $scheduledEnd->toDateTimeString(),
                'undertime_minutes' => $undertimeMinutes
            ]);

            return $undertimeMinutes;
        }

        return 0;
    }
}
