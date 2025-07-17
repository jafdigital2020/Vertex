<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Payroll\PayrollController;
use App\Http\Controllers\Tenant\Payroll\BulkPayrollController;

class PayrollDispatcherController extends Controller
{
    public function handlePayroll(Request $request)
    {
        $request->validate([
            'payroll_type'   => 'required|string',
            'user_id'        => 'required|array',
            'user_id.*'      => 'integer|exists:users,id',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
        ]);

        $payrollType = $request->input('payroll_type');

        switch ($payrollType) {
            case 'normal_payroll':
                // Dispatch to Head Office Payroll
                return app(PayrollController::class)->payrollProcessStore($request);

            case 'bulk_attendance_payroll':
                // Dispatch to Security Guards Payroll
                return app(BulkPayrollController::class)->processBulkPayroll($request);

            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid payroll type selected.'
                ], 400);
        }
    }
}
