<?php

namespace App\Http\Controllers\Tenant\Payroll;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\PayrollBatchSettings;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Tenant\Payroll\PayrollController;
use App\Http\Controllers\Tenant\Payroll\BulkPayrollController;

class PayrollDispatcherController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function handlePayroll(Request $request)
    {
        $authUser = $this->authUser();

        // Subscription validation
        $subscription = Subscription::where('tenant_id', $authUser->tenant_id)->first();

        if (
            $subscription &&
            $subscription->status === 'trial' &&
            $subscription->trial_end &&
            now()->toDateString() >= \Carbon\Carbon::parse($subscription->trial_end)->toDateString()
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your 7-day trial period has ended. Payroll processing is no longer available.'
            ], 403);
        }

        if (
            $subscription &&
            $subscription->status === 'expired'
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your subscription has expired.'
            ], 403);
        }

        $payrollType = $request->input('payroll_type');

        // Base validation rules
        $rules = [
            'payroll_type'      => 'required|string',
            'assignment_type'   => 'required|string',
            'payroll_batch_id'  => 'nullable|exists:payroll_batch_settings,id',
            'transaction_date'  => 'nullable|date',
        ];

        $messages = [
            'user_id.required' => 'Please select at least one employee.',
            'user_id.array' => 'Invalid employee selection format.',
            'user_id.*.integer' => 'Invalid employee selection. Please select valid employees.',
            'user_id.*.exists' => 'One or more selected employees do not exist.',
        ];

        // Conditional validation based on payroll type
        if ($payrollType === '13th_month') {
            // For 13th month, validate year/month ranges
            $rules['from_year'] = 'required|integer|min:2020|max:2050';
            $rules['from_month'] = 'required|integer|min:1|max:12';
            $rules['to_year'] = 'required|integer|min:2020|max:2050';
            $rules['to_month'] = 'required|integer|min:1|max:12';
        } else {
            // For normal payroll, validate start/end dates
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
        }

        // Only require user_id if assignment_type is manual
        if ($request->input('assignment_type') === 'manual') {
            $rules['user_id'] = 'required|array';
            $rules['user_id.*'] = 'integer|exists:users,id';
        }

        $request->validate($rules, $messages);

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

            case '13th_month':
                return app(ThirteenthMonthPayController::class)->process($request);

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
