<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Models\ThirteenthMonthPay;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;
use Illuminate\Support\Facades\DB;
use App\Helpers\ErrorLogger;
use App\Traits\ResponseTimingTrait;


class ThirteenthMonthPayslipController extends Controller
{
    use ResponseTimingTrait; 
    private function logThirteenthMonthPayslipError(
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


    // Thirteenth Month Payslips Admin Index
    public function thirteenthMonthPayslipadminIndex(Request $request)
    {
        $authUser = $this->authUser();
        $thirteenthMonthPayslips = ThirteenthMonthPay::where('status', 'Released')
            ->with(['user.personalInformation', 'user.employmentDetail.department', 'user.employmentDetail.branch'])
            ->get();
        $permission = PermissionHelper::get(25);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        if ($request->wantsJson()) {
            return response()->json([
                'thirteenthMonthPayslips' => $thirteenthMonthPayslips,
            ]);
        }

        return view('tenant.payroll.payslip.thirteenth_month.thirteenth-month-payslip_admin', compact('authUser', 'thirteenthMonthPayslips', 'permission', 'accessData'));
    }

    // Get Analytics Data (Admin)
    public function getAnalytics(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $year = $request->input('year');

        $query = ThirteenthMonthPay::where('tenant_id', $tenantId)
            ->where('status', 'Released')
            ->with(['user.personalInformation', 'user.employmentDetail.department', 'user.employmentDetail.branch']);

        if ($year) {
            $query->where('year', $year);
        }

        $payslips = $query->get();

        // ✅ Calculate Analytics from monthly_breakdown
        $totalBasicPay = 0;
        $totalLeavePay = 0;
        $totalLateDeduction = 0;
        $totalUndertimeDeduction = 0;
        $totalAbsentDeduction = 0;

        // Loop through all payslips and their monthly breakdowns
        foreach ($payslips as $payslip) {
            $monthlyBreakdown = $payslip->monthly_breakdown ?? [];

            if (is_array($monthlyBreakdown)) {
                foreach ($monthlyBreakdown as $month) {
                    $totalBasicPay += round((float) ($month['basic_pay'] ?? 0), 2);
                    $totalLeavePay += round((float) ($month['leave_pay'] ?? 0), 2);
                    $totalLateDeduction += round((float) ($month['late_deduction'] ?? 0), 2);
                    $totalUndertimeDeduction += round((float) ($month['undertime_deduction'] ?? 0), 2);
                    $totalAbsentDeduction += round((float) ($month['absent_deduction'] ?? 0), 2);
                }
            }
        }

        // ✅ Calculate Net Basic Pay: Basic Pay + Leave Pay - All Deductions
        $netBasicPay = round(
            $totalBasicPay +
            $totalLeavePay -
            $totalLateDeduction -
            $totalUndertimeDeduction -
            $totalAbsentDeduction,
            2
        );

        // Calculate analytics
        $totalThirteenthMonth = $payslips->sum('total_thirteenth_month');
        $employeesPaid = $payslips->count();
        $averagePerEmployee = $employeesPaid > 0 ? $totalThirteenthMonth / $employeesPaid : 0;

        // Group by year
        $byYear = $payslips->groupBy('year')->map(function ($items) {
            return $items->sum('total_thirteenth_month');
        });

        // Group by department
        $byDepartment = $payslips->groupBy(function ($item) {
            return $item->user->employmentDetail->department->department_name ?? 'Unknown';
        })->map(function ($items) {
            return [
                'amount' => $items->sum('total_thirteenth_month'),
                'count' => $items->count()
            ];
        });

        // Group by status
        $byStatus = $payslips->groupBy('status')->map(function ($items) {
            return $items->count();
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'totals' => [
                    'total_thirteenth_month' => round($totalThirteenthMonth, 2),
                    'total_basic_pay' => $netBasicPay, // ✅ Use calculated net basic pay
                    'total_leave_pay' => round($totalLeavePay, 2),
                    'total_late_deduction' => round($totalLateDeduction, 2),
                    'total_undertime_deduction' => round($totalUndertimeDeduction, 2),
                    'total_absent_deduction' => round($totalAbsentDeduction, 2),
                    'employees_paid' => $employeesPaid,
                    'average_per_employee' => round($averagePerEmployee, 2)
                ],
                'by_year' => $byYear,
                'by_department' => $byDepartment,
                'by_status' => $byStatus
            ]
        ]);
    }

    // User's 13th Month Payslip View
    public function generatedPayslips(Request $request, $id)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;

        $payslips = ThirteenthMonthPay::findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'payslips' => $payslips,
            ]);
        }

        return view('tenant.payroll.payslip.thirteenth_month.users-thirteenth-month-payslip_admin', compact('tenantId', 'payslips'));
    }

    // Revert 13th Month Payslip
    public function revertThirteenthMonthPayslip(Request $request, $id)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
        try {
            $payslip = ThirteenthMonthPay::findOrFail($id);
            $payslip->status = 'Pending';
            $payslip->save();

            return response()->json([
                'success' => true,
                'message' => '13th Month Payslip reverted to Pending status successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error reverting 13th month payslip: ' . $e->getMessage());

            $cleanMessage = "Error reverting 13th month payslip. Please try again later.";

            $this->logThirteenthMonthPayslipError(
                '[ERROR_REVERTING_13TH_MONTH_PAYSLIP]',
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

    // Delete 13th Month Payslip
    public function deleteThirteenthMonthPayslip(Request $request, $id)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
        try {
            $payslip = ThirteenthMonthPay::findOrFail($id);
            $payslip->delete();

            return response()->json([
                'success' => true,
                'message' => '13th Month Payslip deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting 13th month payslip: ' . $e->getMessage());

            $cleanMessage = "Error deleting 13th month payslip. Please try again later.";

            $this->logThirteenthMonthPayslipError(
                '[ERROR_DELETING_13TH_MONTH_PAYSLIP]',
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

    // Bulk Delete 13th Month Payslips
    public function bulkDeleteThirteenthMonthPayslip(Request $request)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
        $payslipIds = $request->input('payslip_ids', []);

        if (empty($payslipIds)) {
            return response()->json(['message' => 'Please select at least 1 payslip'], 400);
        }

        try {
            ThirteenthMonthPay::whereIn('id', $payslipIds)->delete();

            return response()->json([
                'success' => true,
                'message' => '13th Month Payslip records deleted successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Error bulk deleting 13th month payslips: ' . $e->getMessage());

            $cleanMessage = "Error bulk deleting 13th month payslips. Please try again later.";

            $this->logThirteenthMonthPayslipError(
                '[ERROR_BULK_DELETING_13TH_MONTH_PAYSLIPS]',
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

    // Bulk Revert 13th Month Payslips
    public function bulkRevertThirteenthMonthPayslip(Request $request)
    {
        $startTime = microtime(true);
        $authUser = $this->authUser();
        $payslipIds = $request->input('payslip_ids', []);

        if (empty($payslipIds)) {
            return response()->json(['message' => 'Please select at least 1 payslip'], 400);
        }

        try {
            $payslips = ThirteenthMonthPay::whereIn('id', $payslipIds)->get();
            foreach ($payslips as $payslip) {
                $payslip->status = 'Pending';
                $payslip->save();
            }

            return response()->json([
                'success' => true,
                'message' => '13th Month Payslip records reverted to Pending status successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Error bulk reverting 13th month payslips: ' . $e->getMessage());

            $cleanMessage = "Error bulk reverting 13th month payslips. Please try again later.";

            $this->logThirteenthMonthPayslipError(
                '[ERROR_BULK_REVERTING_13TH_MONTH_PAYSLIPS]',
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

    // User's 13th Month Payslip Index
    public function thirteenthMonthPayslipIndex(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $userId = $authUser->id;

        $thirteenthMonthPayslips = ThirteenthMonthPay::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'Released')
            ->with(['processor', 'user.personalInformation', 'user.employmentDetail'])
            ->get();

        // ✅ Calculate Analytics from monthly_breakdown
        $totalBasicPay = 0;
        $totalLeavePay = 0;
        $totalLateDeduction = 0;
        $totalUndertimeDeduction = 0;
        $totalAbsentDeduction = 0;

        // Loop through all payslips and their monthly breakdowns
        foreach ($thirteenthMonthPayslips as $payslip) {
            $monthlyBreakdown = $payslip->monthly_breakdown ?? [];

            if (is_array($monthlyBreakdown)) {
                foreach ($monthlyBreakdown as $month) {
                    $totalBasicPay += round((float) ($month['basic_pay'] ?? 0), 2);
                    $totalLeavePay += round((float) ($month['leave_pay'] ?? 0), 2);
                    $totalLateDeduction += round((float) ($month['late_deduction'] ?? 0), 2);
                    $totalUndertimeDeduction += round((float) ($month['undertime_deduction'] ?? 0), 2);
                    $totalAbsentDeduction += round((float) ($month['absent_deduction'] ?? 0), 2);
                }
            }
        }

        // ✅ Calculate Net Basic Pay: Basic Pay + Leave Pay - All Deductions
        $netBasicPay = round(
            $totalBasicPay +
            $totalLeavePay -
            $totalLateDeduction -
            $totalUndertimeDeduction -
            $totalAbsentDeduction,
            2
        );

        // Compute analytics for this specific user
        $totalThirteenthMonth = $thirteenthMonthPayslips->sum('total_thirteenth_month');
        $payslipsCount = $thirteenthMonthPayslips->count();
        $averagePerRecord = $payslipsCount > 0 ? $totalThirteenthMonth / $payslipsCount : 0;

        $byYear = $thirteenthMonthPayslips->groupBy('year')->map(function ($items) {
            return $items->sum('total_thirteenth_month');
        });

        // Prepare analytics data
        $analyticsData = [
            'totals' => [
                'total_thirteenth_month' => round($totalThirteenthMonth, 2),
                'total_basic_pay' => $netBasicPay, // ✅ Use calculated net basic pay
                'total_leave_pay' => round($totalLeavePay, 2),
                'total_late_deduction' => round($totalLateDeduction, 2),
                'total_undertime_deduction' => round($totalUndertimeDeduction, 2),
                'total_absent_deduction' => round($totalAbsentDeduction, 2),
                'payslips_count' => $payslipsCount,
                'average_per_record' => round($averagePerRecord, 2)
            ],
            'by_year' => $byYear,
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'thirteenthMonthPayslips' => $thirteenthMonthPayslips,
                'analytics' => $analyticsData, // Include analytics in API response
            ]);
        }

        return view('tenant.payroll.payslip.thirteenth_month.auth_thirteenth_month_payslip.thirteenth_month_auth', compact('thirteenthMonthPayslips', 'analyticsData'));
    }
}
