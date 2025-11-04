<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Models\ThirteenthMonthPay;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;

class ThirteenthMonthPayslipController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
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

    // Get Analytics Data
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

        // Calculate analytics
        $totalThirteenthMonth = $payslips->sum('total_thirteenth_month');
        $totalBasicPay = $payslips->sum('total_basic_pay');
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
                    'total_basic_pay' => round($totalBasicPay, 2),
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
    public function revertThirteenthMonthPayslip($id)
    {
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
            return response()->json([
                'success' => false,
                'message' => 'Error reverting 13th month payslip.'
            ], 500);
        }
    }

    // Delete 13th Month Payslip
    public function deleteThirteenthMonthPayslip($id)
    {
        try {
            $payslip = ThirteenthMonthPay::findOrFail($id);
            $payslip->delete();

            return response()->json([
                'success' => true,
                'message' => '13th Month Payslip deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting 13th month payslip: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting 13th month payslip.'
            ], 500);
        }
    }

    // Bulk Delete 13th Month Payslips
    public function bulkDeleteThirteenthMonthPayslip(Request $request)
    {
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
            return response()->json([
                'success' => false,
                'message' => 'Error deleting 13th month payslips.'
            ], 500);
        }
    }

    // Bulk Revert 13th Month Payslips
    public function bulkRevertThirteenthMonthPayslip(Request $request)
    {
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
            return response()->json([
                'success' => false,
                'message' => 'Error reverting 13th month payslips.'
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
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'payslips' => $thirteenthMonthPayslips,
            ]);
        }

        return view('tenant.payroll.payslip.thirteenth_month.auth_thirteenth_month_payslip.thirteenth_month_auth', compact('thirteenthMonthPayslips'));
    }
}
