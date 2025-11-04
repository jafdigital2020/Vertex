<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Payroll;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportPayslipsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $csvData;
    protected $tenantId;
    protected $processorId;
    protected $processorType;

    public $tries = 3;
    public $timeout = 300;

    public function __construct($csvData, $tenantId, $processorId, $processorType)
    {
        $this->csvData = $csvData;
        $this->tenantId = $tenantId;
        $this->processorId = $processorId;
        $this->processorType = $processorType;
    }

    public function handle(): void
    {
        $successCount = 0;
        $failedRows = [];

        foreach ($this->csvData as $index => $row) {
            try {
                // Validate Employee ID
                if (empty($row['Employee ID'])) {
                    $failedRows[] = [
                        'row' => $index + 1,
                        'error' => 'Employee ID is required',
                        'employee_id' => 'N/A'
                    ];
                    continue;
                }

                // Find user by Employee ID through employmentDetail
                $user = User::where('tenant_id', $this->tenantId)
                    ->whereHas('employmentDetail', function ($query) use ($row) {
                        $query->where('employee_id', $row['Employee ID']);
                    })
                    ->first();

                if (!$user) {
                    $failedRows[] = [
                        'row' => $index + 1,
                        'error' => 'Employee not found with this ID',
                        'employee_id' => $row['Employee ID']
                    ];
                    continue;
                }

                // Parse Payroll Month and Year
                $payrollMonth = $this->parseMonth($row['Payroll Month'] ?? null);
                $payrollYear = $this->parseYear($row['Payroll Year'] ?? null);

                if ($payrollMonth === null || $payrollYear === null) {
                    $failedRows[] = [
                        'row' => $index + 1,
                        'error' => 'Invalid Payroll Month or Year',
                        'employee_id' => $row['Employee ID']
                    ];
                    continue;
                }

                // Calculate 13th month pay
                $basicPay = $this->parseAmount($row['Basic Pay'] ?? 0);
                $paidLeave = $this->parseAmount($row['Paid Leave'] ?? 0);
                $lateDeduction = $this->parseAmount($row['Late Deduction'] ?? 0);
                $undertimeDeduction = $this->parseAmount($row['Undertime Deduction'] ?? 0);
                $absentDeduction = $this->parseAmount($row['Absent Deduction'] ?? 0);

                $thirteenthMonthPay = round(($basicPay + $paidLeave - $lateDeduction - $undertimeDeduction - $absentDeduction) / 12, 2);

                // Create payroll record
                Payroll::create([
                    'tenant_id' => $this->tenantId,
                    'user_id' => $user->id,
                    'payroll_type' => $row['Payroll Type'] ?? 'Regular',
                    'payroll_month' => $payrollMonth,
                    'payroll_year' => $payrollYear,
                    'payroll_period_start' => null, // Not required for uploaded payslips
                    'payroll_period_end' => null,   // Not required for uploaded payslips
                    'payment_date' => null,         // Not required for uploaded payslips
                    'basic_pay' => $basicPay,
                    'gross_pay' => $this->parseAmount($row['Gross Pay'] ?? 0),
                    'total_earnings' => $this->parseAmount($row['Total Earnings'] ?? 0),
                    'total_deductions' => $this->parseAmount($row['Total Deductions'] ?? 0),
                    'net_salary' => $this->parseAmount($row['Net Salary'] ?? 0),
                    'holiday_pay' => $this->parseAmount($row['Holiday Pay'] ?? 0),
                    'overtime_pay' => $this->parseAmount($row['Overtime Pay'] ?? 0),
                    'night_differential_pay' => $this->parseAmount($row['Night Differential Pay'] ?? 0),
                    'leave_pay' => $paidLeave,
                    'late_deduction' => $lateDeduction,
                    'undertime_deduction' => $undertimeDeduction,
                    'absent_deduction' => $absentDeduction,
                    'sss_contribution' => $this->parseAmount($row['SSS Contribution'] ?? 0),
                    'philhealth_contribution' => $this->parseAmount($row['PhilHealth Contribution'] ?? 0),
                    'pagibig_contribution' => $this->parseAmount($row['Pag-IBIG Contribution'] ?? 0),
                    'withholding_tax' => $this->parseAmount($row['Withholding Tax'] ?? 0),
                    'thirteenth_month_pay' => $thirteenthMonthPay,
                    'status' => $row['Status'] ?? 'Paid',
                    'processor_id' => $this->processorId,
                    'processor_type' => $this->processorType,
                    'transaction_date' => now(),
                ]);

                $successCount++;
            } catch (\Exception $e) {
                Log::error('Error importing payslip row ' . ($index + 1) . ': ' . $e->getMessage());
                $failedRows[] = [
                    'row' => $index + 1,
                    'error' => $e->getMessage(),
                    'employee_id' => $row['Employee ID'] ?? 'N/A'
                ];
            }
        }

        // Log summary
        Log::info('Payslip import completed', [
            'success_count' => $successCount,
            'failed_count' => count($failedRows),
            'tenant_id' => $this->tenantId
        ]);

        // Store results in cache for feedback
        cache()->put(
            'payslip_import_result_' . $this->processorId,
            [
                'success_count' => $successCount,
                'failed_rows' => $failedRows
            ],
            now()->addHours(24)
        );
    }

    private function parseMonth($month)
    {
        if (empty($month)) {
            return null;
        }

        // If it's already a number between 1-12
        if (is_numeric($month) && $month >= 1 && $month <= 12) {
            return (int) $month;
        }

        // Convert month name to number
        $monthNames = [
            'january' => 1, 'jan' => 1,
            'february' => 2, 'feb' => 2,
            'march' => 3, 'mar' => 3,
            'april' => 4, 'apr' => 4,
            'may' => 5,
            'june' => 6, 'jun' => 6,
            'july' => 7, 'jul' => 7,
            'august' => 8, 'aug' => 8,
            'september' => 9, 'sep' => 9, 'sept' => 9,
            'october' => 10, 'oct' => 10,
            'november' => 11, 'nov' => 11,
            'december' => 12, 'dec' => 12,
        ];

        $monthLower = strtolower(trim($month));
        return $monthNames[$monthLower] ?? null;
    }

    private function parseYear($year)
    {
        if (empty($year)) {
            return null;
        }

        // Validate year format (4 digits)
        if (is_numeric($year) && strlen($year) == 4 && $year >= 2000 && $year <= 2100) {
            return (int) $year;
        }

        return null;
    }

    private function parseAmount($amount)
    {
        if (empty($amount)) {
            return 0;
        }

        // Remove currency symbols and commas
        $cleaned = preg_replace('/[₱$,\s]/', '', $amount);
        return floatval($cleaned);
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Payslip import job failed: ' . $exception->getMessage(), [
            'tenant_id' => $this->tenantId,
            'processor_id' => $this->processorId,
            'exception' => $exception
        ]);

        cache()->put(
            'payslip_import_result_' . $this->processorId,
            [
                'error' => 'Import failed: ' . $exception->getMessage(),
                'success_count' => 0,
                'failed_rows' => []
            ],
            now()->addHours(24)
        );
    }
}
