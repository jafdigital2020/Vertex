<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Carbon\Carbon;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ThirteenthMonthPay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Exports\ThirteenthMonthPayExport;
use App\Helpers\ErrorLogger;
use App\Traits\ResponseTimingTrait;

class ThirteenthMonthPayController extends Controller
{
    use ResponseTimingTrait;
    private function logThirteenthMonthPayError(
        string $errorType,
        string $message,
        Request $request,
        ?float $startTime = null,
        ?array $responseData = null
    ): void {
        try {
            $processingTime = null;
            $timingData = null;

            if ($responseData && isset($responseData['timing'])) {
                $timingData = $responseData['timing'];
                $processingTime = $timingData['server_processing_time_ms'] ?? null;
            } elseif ($startTime) {
                $timingData = $this->getTimingData($startTime);
                $processingTime = $timingData ? $timingData['server_processing_time_ms'] : null;
            }

            $errorMessage = sprintf("[%s] %s", $errorType, $message);

            // Get authenticated user
            $authUser = $this->authUser();

            // ===== DEBUG LOG START =====
            Log::debug('logPayrollError - Auth User & Tenant Info', [
                'auth_user_id' => $authUser?->id,
                'auth_user_tenant_id' => $authUser?->tenant_id,
                'tenant_loaded' => isset($authUser->tenant),
                'tenant_name_from_relation' => $authUser->tenant?->tenant_name ?? null,
            ]);

            $clientName = $authUser->tenant?->tenant_name ?? 'Unknown Tenant';
            $clientId   = $authUser->tenant?->id ?? null;

            Log::debug('logPayrollError - Sending to ErrorLogger', [
                'client_name' => $clientName,
                'client_id' => $clientId,
                'error_message' => $errorMessage,
            ]);
            // ===== DEBUG LOG END =====

            // Log to remote system
            ErrorLogger::logToRemoteSystem(
                $errorMessage,
                $clientName,
                $clientId,
                $timingData
            );

            // Local Laravel log
            Log::error($errorType, [
                'clean_message' => $message,
                'full_error' => $responseData['full_error'] ?? null,
                'user_id' => $authUser->id ?? null,
                'client_name' => $clientName,
                'client_id' => $clientId,
                'processing_time_ms' => $processingTime,
                'url' => $request->fullUrl(),
                'request_data' => $request->except(['password', 'token', 'api_key'])
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log error', [
                'original_error' => $message,
                'logging_error' => $e->getMessage()
            ]);
        }
    }


    public function authUser()
    {
        $user = null;
        
        if (Auth::guard('global')->check()) {
            $user = Auth::guard('global')->user();
        } else {
            $user = Auth::guard('web')->user();
        }
        
        // Load tenant relationship if user exists
        if ($user) {
            $user->load('tenant');
        }
        
        return $user;
    }


    /**
     * Process 13th Month Pay by aggregating existing payroll data across years
     */
    public function process(Request $request)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;

        $validated = $request->validate([
            'user_id' => 'required|array',
            'user_id.*' => 'integer|exists:users,id',
            'from_year' => 'required|integer|min:2020|max:2050',
            'from_month' => 'required|integer|min:1|max:12',
            'to_year' => 'required|integer|min:2020|max:2050',
            'to_month' => 'required|integer|min:1|max:12',
            'payment_date' => 'nullable|date',
        ]);

        // Create "from" and "to" dates for validation
        $fromDate = Carbon::create($validated['from_year'], $validated['from_month'], 1)->startOfMonth();
        $toDate = Carbon::create($validated['to_year'], $validated['to_month'], 1)->endOfMonth();

        // Validate that "to" date is after "from" date
        if ($toDate->lt($fromDate)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The "To" date must be after the "From" date.'
            ], 400);
        }

        $paymentDate = $request->input('payment_date', now()->toDateString());
        $results = [];

        try {
            DB::beginTransaction();

            foreach ($validated['user_id'] as $userId) {
                Log::info('Processing user', ['user_id' => $userId]);

                // Build query
                $queryBuilder = Payroll::select([
                    'payroll_year',
                    'payroll_month',
                    'payroll_period_start',
                    'payroll_period_end',
                    'thirteenth_month_pay',
                    'basic_pay',
                    'leave_pay',
                    'late_deduction',
                    'undertime_deduction',
                    'absent_deduction'
                ])
                    ->where('tenant_id', $tenantId)
                    ->where('user_id', $userId)
                    ->whereIn('payroll_type', ['normal_payroll', 'Regular']);

                // Apply date range logic using numeric comparison for accuracy
                $startDateValue = $validated['from_year'] * 100 + $validated['from_month'];
                $endDateValue = $validated['to_year'] * 100 + $validated['to_month'];

                $queryBuilder->whereRaw('(payroll_year * 100 + payroll_month) BETWEEN ? AND ?', [$startDateValue, $endDateValue]);

                // Get SQL for debugging
                $sql = $queryBuilder->toSql();
                $bindings = $queryBuilder->getBindings();

                Log::info('SQL Query Debug', [
                    'sql' => $sql,
                    'bindings' => $bindings
                ]);

                // Execute query
                $monthlyData = $queryBuilder->orderBy('payroll_year')
                    ->orderBy('payroll_month')
                    ->orderBy('payroll_period_start')
                    ->get();


                if ($monthlyData->isEmpty()) {
                    Log::warning('No payroll records found for user', [
                        'user_id' => $userId,
                        'tenant_id' => $tenantId
                    ]);
                    continue;
                }

                // Build monthly breakdown by aggregating multiple payrolls per month
                $monthlyBreakdown = [];
                $totalThirteenthMonth = 0;
                $totalBasicPay = 0;
                $totalDeductions = 0;

                // Group by year and month to aggregate semi-monthly/weekly payrolls
                $groupedData = $monthlyData->groupBy(function ($item) {
                    // Convert to integer to ensure proper grouping
                    $year = (int) $item->payroll_year;
                    $month = (int) $item->payroll_month;
                    return $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                });

                foreach ($groupedData as $yearMonth => $payrolls) {
                    $firstPayroll = $payrolls->first();
                    $year = (int) $firstPayroll->payroll_year;
                    $month = (int) $firstPayroll->payroll_month;
                    $monthName = Carbon::createFromFormat('m', $month)->format('F');

                    // Use number_format with 2 decimals BEFORE converting to float
                    $monthThirteenthAmount = $payrolls->sum(function ($p) {
                        return round((float) ($p->thirteenth_month_pay ?? 0), 2); // Round each value first
                    });

                    $monthBasicPay = $payrolls->sum(function ($p) {
                        return round((float) ($p->basic_pay ?? 0), 2);
                    });

                    $monthLeavePay = $payrolls->sum(function ($p) {
                        return round((float) ($p->leave_pay ?? 0), 2);
                    });

                    $monthLateDeduction = $payrolls->sum(function ($p) {
                        return round((float) ($p->late_deduction ?? 0), 2);
                    });

                    $monthUndertimeDeduction = $payrolls->sum(function ($p) {
                        return round((float) ($p->undertime_deduction ?? 0), 2);
                    });

                    $monthAbsentDeduction = $payrolls->sum(function ($p) {
                        return round((float) ($p->absent_deduction ?? 0), 2);
                    });

                    $periodStart = $payrolls->min('payroll_period_start');
                    $periodEnd = $payrolls->max('payroll_period_end');

                    // Store rounded values consistently
                    $monthlyBreakdown[] = [
                        'year' => $year,
                        'month' => $month,
                        'month_name' => $monthName . ' ' . $year,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'payroll_count' => $payrolls->count(),
                        'basic_pay' => number_format($monthBasicPay, 2, '.', ''), // Store as string with 2 decimals
                        'leave_pay' => number_format($monthLeavePay, 2, '.', ''),
                        'late_deduction' => number_format($monthLateDeduction, 2, '.', ''),
                        'undertime_deduction' => number_format($monthUndertimeDeduction, 2, '.', ''),
                        'absent_deduction' => number_format($monthAbsentDeduction, 2, '.', ''),
                        'thirteenth_month_contribution' => number_format($monthThirteenthAmount, 2, '.', ''),
                    ];

                    $totalThirteenthMonth += $monthThirteenthAmount;
                    $totalBasicPay += $monthBasicPay;
                    $totalDeductions += ($monthLateDeduction + $monthUndertimeDeduction + $monthAbsentDeduction);
                }

                $thirteenthMonth = ThirteenthMonthPay::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'user_id' => $userId,
                        'year' => $validated['to_year'],
                        'from_month' => $validated['from_month'],
                        'from_year' => $validated['from_year'],
                        'to_month' => $validated['to_month'],
                        'to_year' => $validated['to_year'],
                    ],
                    [
                        'monthly_breakdown' => $monthlyBreakdown,
                        'total_basic_pay' => number_format($totalBasicPay, 2, '.', ''),
                        'total_deductions' => number_format($totalDeductions, 2, '.', ''),
                        'total_thirteenth_month' => number_format($totalThirteenthMonth, 2, '.', ''),
                        'payment_date' => $paymentDate,
                        'processor_type' => get_class($authUser),
                        'processor_id' => $authUser->id,
                        'status' => 'Pending',
                    ]
                );

                Log::info('13th month record saved', [
                    'record_id' => $thirteenthMonth->id,
                    'user_id' => $userId
                ]);

                $results[] = [
                    'user_id' => $userId,
                    'user_name' => $thirteenthMonth->user->personalInformation->full_name ?? 'Unknown',
                    'coverage' => Carbon::create($validated['from_year'], $validated['from_month'])->format('F Y') .
                        ' - ' .
                        Carbon::create($validated['to_year'], $validated['to_month'])->format('F Y'),
                    'total_basic_pay' => round($totalBasicPay, 2),
                    'total_deductions' => round($totalDeductions, 2),
                    'thirteenth_month_pay' => round($totalThirteenthMonth, 2),
                    'monthly_breakdown' => $monthlyBreakdown,
                ];
            }

            Log::info('All users processed', ['total_results' => count($results)]);

            DB::commit();

            Log::info('Transaction committed successfully');

            return response()->json([
                'status' => 'success',
                'message' => '13th Month Pay processed successfully for ' . count($results) . ' employee(s).',
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('13th Month Processing Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

             $cleanMessage = "Failed to process 13th month pay. Please try again later.";

            $this->logThirteenthMonthPayError(
                '[ERROR_PROCESSING_13TH_MONTH_PAY]',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
        }
    }

    /**
     * Delete a single 13th month pay record
     */
    public function delete(Request $request, $id)
    {
        
        $startTime = microtime(true);
        $authUser = $this->authUser();
        try {
            $thirteenthMonth = ThirteenthMonthPay::findOrFail($id);
            $thirteenthMonth->delete();

            return response()->json([
                'status' => 'success',
                'message' => '13th Month Pay deleted successfully.'
            ]);
        } catch (\Exception $e) {
            $cleanMessage = "Failed to delete 13th month pay. Please try again later.";

            $this->logThirteenthMonthPayError(
                '[ERROR_DELETING_13TH_MONTH_PAY]',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
        }
    }

    /**
     * Bulk generate payslips (mark as Paid)
     */
    public function bulkGeneratePayslip(Request $request)
    {
        $ids = $request->input('thirteenth_month_ids', []);

        if (empty($ids)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No 13th month pay IDs provided.'
            ], 400);
        }

        ThirteenthMonthPay::whereIn('id', $ids)->update(['status' => 'Released']);

        return response()->json([
            'status' => 'success',
            'message' => 'Selected 13th month pay marked as Released.'
        ]);
    }

    /**
     * Bulk delete 13th month pay records
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('thirteenth_month_ids', []);

        if (empty($ids)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No 13th month pay IDs provided.'
            ], 400);
        }

        ThirteenthMonthPay::whereIn('id', $ids)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Selected 13th month pay deleted successfully.'
        ]);
    }

    /**
     * Export 13th Month Pay to Excel
     */
    public function exportExcel(Request $request)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
        try {
            $authUser = $this->authUser();

            // Get filters from request
            $filters = [
                'dateRange' => $request->input('dateRange'),
                'branch' => $request->input('branch'),
                'department' => $request->input('department'),
                'designation' => $request->input('designation'),
                'year' => $request->input('year'),
                'status' => $request->input('status'),
            ];

            $exporter = new ThirteenthMonthPayExport($authUser, $filters);
            $payrolls = $exporter->getData();

            if ($payrolls->isEmpty()) {
                $statusText = !empty($filters['status']) ? " with status '{$filters['status']}'" : '';
                return response()->json([
                    'status' => 'error',
                    'message' => "No 13th month pay records found{$statusText} for export."
                ], 404);
            }

            // Create new Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('Payroll System')
                ->setTitle('13th Month Pay Report')
                ->setSubject('13th Month Pay Export')
                ->setDescription('13th Month Pay records export');

            // Add title
            $sheet->setCellValue('A1', '13TH MONTH PAY REPORT');
            $sheet->mergeCells('A1:O1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Add export date
            $sheet->setCellValue('A2', 'Generated: ' . now()->format('F d, Y h:i A'));
            $sheet->mergeCells('A2:O2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Add headers
            $headers = $exporter->getHeaders();
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '4', $header);
                $sheet->getStyle($col . '4')->getFont()->setBold(true);
                $sheet->getStyle($col . '4')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF4472C4');
                $sheet->getStyle($col . '4')->getFont()->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle($col . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $col++;
            }

            // Add data
            $row = 5;
            foreach ($payrolls as $index => $payroll) {
                $data = $exporter->formatRow($payroll, $index);
                $col = 'A';
                foreach ($data as $value) {
                    $sheet->setCellValue($col . $row, $value);

                    // Center align number column
                    if ($col == 'A') {
                        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // Right align amount columns
                    if (in_array($col, ['I', 'J', 'K'])) {
                        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }

                    $col++;
                }
                $row++;
            }

            // Add summary totals
            $summaryRow = $row + 1;
            $totals = $exporter->getSummaryTotals($payrolls);

            $sheet->setCellValue('A' . $summaryRow, 'SUMMARY TOTALS');
            $sheet->mergeCells('A' . $summaryRow . ':H' . $summaryRow);
            $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $summaryRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE7E6E6');

            $sheet->setCellValue('I' . $summaryRow, number_format($totals['total_basic_pay'], 2));
            $sheet->setCellValue('J' . $summaryRow, number_format($totals['total_deductions'], 2));
            $sheet->setCellValue('K' . $summaryRow, number_format($totals['total_thirteenth_month'], 2));

            $sheet->getStyle('I' . $summaryRow . ':K' . $summaryRow)->getFont()->setBold(true);
            $sheet->getStyle('I' . $summaryRow . ':K' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('I' . $summaryRow . ':K' . $summaryRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE7E6E6');

            // Add employee count
            $countRow = $summaryRow + 1;
            $sheet->setCellValue('A' . $countRow, 'Total Employees: ' . $totals['total_employees']);
            $sheet->mergeCells('A' . $countRow . ':O' . $countRow);
            $sheet->getStyle('A' . $countRow)->getFont()->setBold(true);

            // Auto-size columns
            foreach (range('A', 'O') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Add borders to data area
            $lastRow = $row - 1;
            $sheet->getStyle('A4:O' . $lastRow)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            // Add borders to summary
            $sheet->getStyle('A' . $summaryRow . ':K' . $summaryRow)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            // Create Excel file
            $writer = new Xlsx($spreadsheet);
            $statusSuffix = !empty($filters['status']) ? '-' . strtolower($filters['status']) : '-all';
            $fileName = '13th-month-pay' . $statusSuffix . '-' . now()->format('Y-m-d-His') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);

            $writer->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('13th Month Pay Excel Export Failed: ' . $e->getMessage());

            $cleanMessage = "13th Month Pay Excel Export Failed. Please try again later.";

            $this->logThirteenthMonthPayError(
                '[FAILED_TO_EXPORT_13TH_MONTH_PAY_TO_EXCEL]',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
        }
    }

    /**
     * Export 13th Month Pay to PDF
     */
    public function exportPDF(Request $request)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
        try {
            $authUser = $this->authUser();

            // Get filters from request
            $filters = [
                'dateRange' => $request->input('dateRange'),
                'branch' => $request->input('branch'),
                'department' => $request->input('department'),
                'designation' => $request->input('designation'),
                'year' => $request->input('year'),
                'status' => $request->input('status'),
            ];

            $exporter = new ThirteenthMonthPayExport($authUser, $filters);
            $payrolls = $exporter->getData();

            if ($payrolls->isEmpty()) {
                $statusText = !empty($filters['status']) ? " with status '{$filters['status']}'" : '';
                return response()->json([
                    'status' => 'error',
                    'message' => "No 13th month pay records found{$statusText} for export."
                ], 404);
            }

            $headers = $exporter->getHeaders();
            $totals = $exporter->getSummaryTotals($payrolls);

            // Prepare data for PDF
            $data = [
                'payrolls' => $payrolls,
                'headers' => $headers,
                'totals' => $totals,
                'exporter' => $exporter,
                'generatedDate' => now()->format('F d, Y h:i A'),
                'filters' => $filters
            ];

            // Generate PDF
            $pdf = Pdf::loadView('tenant.payroll.exports.thirteenth-month-pay-pdf', $data);
            $pdf->setPaper('a4', 'landscape');

            $statusSuffix = !empty($filters['status']) ? '-' . strtolower($filters['status']) : '-all';
            $fileName = '13th-month-pay' . $statusSuffix . '-' . now()->format('Y-m-d-His') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('13th Month Pay PDF Export Failed: ' . $e->getMessage());

            $cleanMessage = "13th Month Pay PDF Export Failed. Please try again later.";

            $this->logThirteenthMonthPayError(
                '[FAILED_TO_EXPORT_13TH_MONTH_PAY_TO_PDF]',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
        }
    }
}
