<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Carbon\Carbon;
use App\Models\Payroll;
use Illuminate\Http\Request;
use App\Models\ThirteenthMonthPay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ThirteenthMonthPayController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    /**
     * Process 13th Month Pay by aggregating existing payroll data across years
     */
    public function process(Request $request)
    {
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

            Log::info('13th Month Processing Started', [
                'tenant_id' => $tenantId,
                'user_ids' => $validated['user_id'],
                'from_year' => $validated['from_year'],
                'from_month' => $validated['from_month'],
                'to_year' => $validated['to_year'],
                'to_month' => $validated['to_month'],
            ]);

            foreach ($validated['user_id'] as $userId) {
                Log::info('Processing user', ['user_id' => $userId]);

                // Build query - COMPLETELY REWRITTEN
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
                    ->where('payroll_type', 'normal_payroll');

                // Apply date range logic using numeric comparison for accuracy
                $startDateValue = $validated['from_year'] * 100 + $validated['from_month'];
                $endDateValue = $validated['to_year'] * 100 + $validated['to_month'];

                $queryBuilder->whereRaw('(payroll_year * 100 + payroll_month) BETWEEN ? AND ?', [$startDateValue, $endDateValue]);

                Log::info('Date range query', [
                    'start_date_value' => $startDateValue, // e.g., 202411
                    'end_date_value' => $endDateValue,     // e.g., 202502
                    'from' => $validated['from_year'] . '-' . $validated['from_month'],
                    'to' => $validated['to_year'] . '-' . $validated['to_month']
                ]);

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

                Log::info('Query completed', [
                    'user_id' => $userId,
                    'from' => $validated['from_year'] . '-' . $validated['from_month'],
                    'to' => $validated['to_year'] . '-' . $validated['to_month']
                ]);

                Log::info('Query Results', [
                    'user_id' => $userId,
                    'records_found' => $monthlyData->count(),
                    'data' => $monthlyData->toArray()
                ]);

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

                Log::info('Grouped payroll data', [
                    'user_id' => $userId,
                    'groups' => $groupedData->keys()->toArray(),
                    'total_records' => $monthlyData->count(),
                    'records_per_group' => $groupedData->map(fn($group) => $group->count())->toArray()
                ]);

                foreach ($groupedData as $yearMonth => $payrolls) {
                    $firstPayroll = $payrolls->first();
                    $year = (int) $firstPayroll->payroll_year;
                    $month = (int) $firstPayroll->payroll_month;
                    $monthName = Carbon::createFromFormat('m', $month)->format('F');

                    // Sum all records for this month (semi-monthly, weekly, etc.)
                    $monthThirteenthAmount = $payrolls->sum(function ($p) {
                        return (float) ($p->thirteenth_month_pay ?? 0);
                    });
                    $monthBasicPay = $payrolls->sum(function ($p) {
                        return (float) ($p->basic_pay ?? 0);
                    });
                    $monthLeavePay = $payrolls->sum(function ($p) {
                        return (float) ($p->leave_pay ?? 0);
                    });
                    $monthLateDeduction = $payrolls->sum(function ($p) {
                        return (float) ($p->late_deduction ?? 0);
                    });
                    $monthUndertimeDeduction = $payrolls->sum(function ($p) {
                        return (float) ($p->undertime_deduction ?? 0);
                    });
                    $monthAbsentDeduction = $payrolls->sum(function ($p) {
                        return (float) ($p->absent_deduction ?? 0);
                    });

                    Log::info('Processing month group', [
                        'user_id' => $userId,
                        'year_month' => $yearMonth,
                        'payroll_count' => $payrolls->count(),
                        'month_thirteenth_amount' => $monthThirteenthAmount,
                        'month_basic_pay' => $monthBasicPay,
                        'individual_records' => $payrolls->map(function ($p) {
                            return [
                                'basic_pay' => $p->basic_pay,
                                'thirteenth_month_pay' => $p->thirteenth_month_pay,
                                'period' => $p->payroll_period_start . ' to ' . $p->payroll_period_end
                            ];
                        })->toArray()
                    ]);

                    // Get period start from earliest payroll and period end from latest
                    $periodStart = $payrolls->min('payroll_period_start');
                    $periodEnd = $payrolls->max('payroll_period_end');

                    $monthlyBreakdown[] = [
                        'year' => $year,
                        'month' => $month,
                        'month_name' => $monthName . ' ' . $year,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'payroll_count' => $payrolls->count(), // Track how many payrolls in this month
                        'basic_pay' => round($monthBasicPay, 2),
                        'leave_pay' => round($monthLeavePay, 2),
                        'late_deduction' => round($monthLateDeduction, 2),
                        'undertime_deduction' => round($monthUndertimeDeduction, 2),
                        'absent_deduction' => round($monthAbsentDeduction, 2),
                        'thirteenth_month_contribution' => round($monthThirteenthAmount, 2),
                    ];

                    Log::info('Added month to breakdown', [
                        'year_month' => $yearMonth,
                        'payroll_count' => $payrolls->count(),
                        'thirteenth_month_contribution' => round($monthThirteenthAmount, 2)
                    ]);

                    $totalThirteenthMonth += $monthThirteenthAmount;
                    $totalBasicPay += $monthBasicPay;
                    $totalDeductions += ($monthLateDeduction + $monthUndertimeDeduction + $monthAbsentDeduction);
                }

                Log::info('Monthly breakdown computed', [
                    'user_id' => $userId,
                    'months_processed' => count($monthlyBreakdown),
                    'total_thirteenth_month' => $totalThirteenthMonth,
                    'monthly_breakdown' => $monthlyBreakdown
                ]);

                // Save to thirteenth_month_pay table
                Log::info('Attempting to save 13th month record', [
                    'user_id' => $userId,
                    'tenant_id' => $tenantId,
                    'year' => $validated['to_year'],
                    'from_month' => $validated['from_month'],
                    'to_month' => $validated['to_month'],
                    'total_thirteenth_month' => round($totalThirteenthMonth, 2),
                    'total_basic_pay' => round($totalBasicPay, 2),
                ]);

                $thirteenthMonth = ThirteenthMonthPay::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'user_id' => $userId,
                        'year' => $validated['to_year'], // Use "to_year" as the primary year
                        'from_month' => $validated['from_month'],
                        'to_month' => $validated['to_month'],
                    ],
                    [
                        'monthly_breakdown' => $monthlyBreakdown,
                        'total_basic_pay' => round($totalBasicPay, 2),
                        'total_deductions' => round($totalDeductions, 2),
                        'total_thirteenth_month' => round($totalThirteenthMonth, 2),
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

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process 13th month pay: ' . $e->getMessage()
            ], 500);
        }
    }
}
