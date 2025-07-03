<?php

namespace App\Jobs;

use Exception;
use Carbon\Carbon;
use App\Models\EmploymentDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class BulkImportAttendanceJob implements ShouldQueue
{
    use Queueable;

    protected $path;
    protected $tenantId;

    public function __construct($path, $tenantId)
    {
        $this->path = $path;
        $this->tenantId = $tenantId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            Log::info('Bulk Import Attendance CSV: Start');

            $filePath = storage_path('app/private/' . $this->path);

            // Check if the file exists
            if (file_exists($filePath)) {
                $rows = array_map('str_getcsv', file($filePath)); // Read the CSV file
                Log::info('CSV file loaded successfully', ['rows_count' => count($rows)]);
            } else {
                Log::error('File not found', ['file' => $filePath]);
                return; // Return if file not found
            }

            $header = array_map('trim', $rows[0]);
            unset($rows[0]);

            $expectedHeader = [
                'Employee ID',
                'Employee Name',
                'Date/Period From',
                'Date/Period To',
                'Regular Working Days',
                'Regular Working Hours',
                'Regular OT Hours',
                'Regular ND Hours',
                'Regular OT + ND Hours',
                'Restday Work',
                'Restday OT',
                'Restday ND',
                'Regular Holiday Hours',
                'Special Holiday Hours',
                'Regular Holiday OT',
                'Special Holiday OT',
                'Regular Holiday ND',
                'Special Holiday ND'
            ];

            if ($header !== $expectedHeader) {
                Log::warning('CSV header mismatch', ['header' => $header, 'expected' => $expectedHeader]);
                return;
            }

            $employeeMap = EmploymentDetail::pluck('user_id', 'employee_id');
            Log::info('Employee map loaded', ['count' => $employeeMap->count()]);

            $imported = 0;
            $skipped = 0;
            $skippedDetails = [];

            $toInt = function ($val) {
                $val = trim((string)$val);
                return ($val === '' || strtolower($val) === 'null') ? null : (int)$val;
            };

            $toBool = function ($val) {
                $val = strtolower(trim((string)$val));
                return in_array($val, ['1', 'true', 'yes'], true) ? 1 : 0;
            };

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $data = array_combine($header, $row);

                $employeeId = trim($data['Employee ID']);
                $userId = $employeeMap[$employeeId] ?? null;

                if (!$userId) {
                    $skipped++;
                    $skippedDetails[] = "Row $rowNumber: Employee ID '{$employeeId}' not found.";
                    Log::warning('Employee ID not found', ['row' => $rowNumber, 'employee_id' => $employeeId]);
                    continue;
                }

                $validator = Validator::make([
                    'Employee ID'        => $employeeId,
                    'Date/Period From'   => $data['Date/Period From'],
                    'Date/Period To'     => $data['Date/Period To'],
                ], [
                    'Employee ID'        => 'required',
                    'Date/Period From'   => 'required|date',
                    'Date/Period To'     => 'required|date|after_or_equal:Date/Period From',
                ]);

                if ($validator->fails()) {
                    $skipped++;
                    $skippedDetails[] = "Row $rowNumber: " . implode(', ', $validator->errors()->all());
                    Log::warning('Validation failed', [
                        'row' => $rowNumber,
                        'errors' => $validator->errors()->all()
                    ]);
                    continue;
                }

                try {
                    $dateFrom = Carbon::parse($data['Date/Period From']);
                    $dateTo = Carbon::parse($data['Date/Period To']);
                } catch (Exception $e) {
                    $skipped++;
                    $skippedDetails[] = "Row $rowNumber: Invalid date format for Date/Period From or To.";
                    Log::warning('Invalid date format', [
                        'row' => $rowNumber,
                        'date_from' => $data['Date/Period From'],
                        'date_to' => $data['Date/Period To']
                    ]);
                    continue;
                }

                try {
                    DB::table('bulk_attendances')->insert([
                        'user_id'                   => $userId,
                        'date_from'                 => $dateFrom->toDateString(),
                        'date_to'                   => $dateTo->toDateString(),
                        'regular_working_days'      => $toInt($data['Regular Working Days'] ?? null),
                        'regular_working_hours'     => $toInt($data['Regular Working Hours'] ?? null),
                        'regular_overtime_hours'    => $toInt($data['Regular OT Hours'] ?? null),
                        'regular_nd_hours'          => $toInt($data['Regular ND Hours'] ?? null),
                        'regular_nd_overtime_hours' => $toInt($data['Regular OT + ND Hours'] ?? null),
                        'rest_day_work'             => $toBool($data['Restday Work'] ?? 0),
                        'rest_day_ot'               => $toBool($data['Restday OT'] ?? 0),
                        'rest_day_nd'               => $toBool($data['Restday ND'] ?? 0),
                        'regular_holiday_hours'     => $toInt($data['Regular Holiday Hours'] ?? null),
                        'special_holiday_hours'     => $toInt($data['Special Holiday Hours'] ?? null),
                        'regular_holiday_ot'        => $toInt($data['Regular Holiday OT'] ?? null),
                        'special_holiday_ot'        => $toInt($data['Special Holiday OT'] ?? null),
                        'regular_holiday_nd'        => $toInt($data['Regular Holiday ND'] ?? null),
                        'special_holiday_nd'        => $toInt($data['Special Holiday ND'] ?? null),
                        'created_at'                => now(),
                        'updated_at'                => now(),
                    ]);
                    $imported++;
                    Log::info('Bulk attendance saved', [
                        'row' => $rowNumber,
                        'user_id' => $userId,
                        'date_from' => $dateFrom->toDateString(),
                        'date_to' => $dateTo->toDateString()
                    ]);
                } catch (Exception $e) {
                    $skipped++;
                    $skippedDetails[] = "Row $rowNumber: Error saving bulk attendance. " . $e->getMessage();
                    Log::error('Error saving bulk attendance', [
                        'row' => $rowNumber,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            Log::info('Bulk Import Attendance CSV: Finished', [
                'imported' => $imported,
                'skipped' => $skipped
            ]);

            return 'success';
        } catch (Exception $e) {
            return 'fail';
        }
    }
}
