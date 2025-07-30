<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\PayrollBatchSettings;
use App\Http\Controllers\Tenant\Payroll\PayrollController;
use App\Http\Controllers\Tenant\Payroll\BulkPayrollController;

class PayrollDispatcherController extends Controller
{
    public function handlePayroll(Request $request)
    {
        $rules = [
            'payroll_type'      => 'required|string',
            'assignment_type'   => 'required|string',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'payroll_batch_id'  => 'nullable|exists:payroll_batch_settings,id',
        ];

        // Only require user_id if assignment_type is manual
        if ($request->input('assignment_type') === 'manual') {
            $rules['user_id'] = 'required|array';
            $rules['user_id.*'] = 'integer|exists:users,id';
        }

        $request->validate($rules);

        $payrollType     = $request->input('payroll_type');
        $assignmentType  = $request->input('assignment_type');
        $payrollBatchId  = $request->input('payroll_batch_id');
        $userIds         = $request->input('user_id', []);

        // If assignment type is payroll_batch, get user_ids from batch settings
        if ($assignmentType === 'payroll_batch' && $payrollBatchId) {
            $batch = PayrollBatchSettings::with('batchUsers')->find($payrollBatchId);
            if (!$batch) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payroll batch not found.'
                ], 404);
            }
            $userIds = $batch->batchUsers->pluck('user_id')->toArray();

            $request->merge(['user_id' => $userIds]);
        }

        switch ($payrollType) {
            case 'normal_payroll':
                return app(PayrollController::class)->payrollProcessStore($request);

            case 'bulk_attendance_payroll':
                return app(BulkPayrollController::class)->processBulkPayroll($request);

            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid payroll type selected.'
                ], 400);
        }
    }
}
