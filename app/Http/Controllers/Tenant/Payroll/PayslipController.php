<?php

namespace App\Http\Controllers\Tenant\Payroll;

use Exception;
use Carbon\Carbon;
use App\Models\Payroll;
use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;

class PayslipController extends Controller
{
    // Generated Payslip Index
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(14);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation');
        $status = $request->input('status');


        $query  = $accessData['payslips'];

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('payment_date', [$start, $end]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date range format.'
                ]);
            }
        }
        if ($branch) {
            $query->whereHas('user.employmentDetail', function ($q) use ($branch) {
                $q->where('branch_id', $branch);
            });
        }
        if ($department) {
            $query->whereHas('user.employmentDetail', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
        if ($designation) {
            $query->whereHas('user.employmentDetail', function ($q) use ($designation) {
                $q->where('designation_id', $designation);
            });
        }

        $payslips = $query->get();

        $html = view('tenant.payroll.payslip.generated-payslip_filter', compact('tenantId', 'payslips', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function generatedPayslipIndex(Request $request)
    {
        // Get the tenant ID from the request
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(25);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $payslips = $accessData['payslips']->get();
        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'payslips' => $payslips,
            ]);
        }
        return view('tenant.payroll.payslip.generated-payslip', compact('tenantId', 'payslips', 'permission', 'branches', 'departments', 'designations'));
    }

    // Payroll Chart Data for Index
    public function dashboardChartData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;


        $netSalaries = Payroll::selectRaw('MONTH(payment_date) as month, SUM(net_salary) as total')
            ->where('tenant_id', $tenantId)
            ->whereYear('payment_date', $year)
            ->where('status', 'Paid')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Prepare data for all 12 months (fill missing months with 0)
        $months = range(1, 12);
        $totals = [];
        foreach ($months as $m) {
            $row = $netSalaries->firstWhere('month', $m);
            $totals[] = $row ? floatval($row->total) : 0;
        }

        return response()->json([
            'months' => ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            'totals' => $totals,
        ]);
    }

    // Payroll Summary for Index
    public function payrollSummary(Request $request)
    {
        $range = $request->input('range', 'monthly');
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        if ($range == 'yearly') {
            $start = Carbon::createFromDate($year)->startOfYear()->format('Y-m-d');
            $end = Carbon::createFromDate($year)->endOfYear()->format('Y-m-d');
        } else {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
            $end = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
        }
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;


        $totalEarnings = Payroll::where('tenant_id', $tenantId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('total_earnings');

        $totalDeductions = Payroll::where('tenant_id', $tenantId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('total_deductions');

        $totalNetSalary = Payroll::where('tenant_id', $tenantId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('net_salary');

        $totalPayrollCount = Payroll::where('tenant_id', $tenantId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->count();


        return response()->json([
            'totalEarnings' => $totalEarnings,
            'totalDeductions' => $totalDeductions,
            'totalNetSalary' => $totalNetSalary,
            'totalPayrollCount' => $totalPayrollCount,
            'range' => $range,
            'year' => $year,
            'month' => $month,
            'start' => $start,
            'end' => $end
        ]);
    }

    // Generated Payslips
    public function generatedPayslips(Request $request, $id)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;

        $payslips = Payroll::findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'payslips' => $payslips,
            ]);
        }

        return view('tenant.payroll.payslip.payslip-view', compact('tenantId', 'payslips'));
    }

    // Revert Generated Payslip
    public function revertGeneratedPayslip($id)
    {
        try {
            $payslip = Payroll::findOrFail($id);
            $payslip->status = 'Pending';
            $payslip->save();

            $oldData = $payslip->getOriginal();
            $newData = $payslip->toArray();

            //Logging the revert action
            $userId = Auth::guard('web')->id();
            $globalUserId = Auth::guard('global')->id();

            UserLog::create([
                'user_id'         => $userId,
                'global_user_id'  => $globalUserId,
                'module'          => 'Payslip',
                'action'          => 'Revert',
                'description'     => 'Reverted Payslip with ID "' . $payslip->id . '" to Draft status',
                'affected_id'     => $payslip->id,
                'old_data'        => json_encode($oldData),
                'new_data'        => json_encode($newData),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payslip reverted to Draft status successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error reverting payslip: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error reverting payslip.'
            ], 500);
        }
    }

    // Delete Generated Payslip
    public function deleteGeneratedPayslip($id)
    {
        try {
            $payslip = Payroll::findOrFail($id);
            $payslip->delete();

            $oldData = $payslip->toArray();

            //Logging the deletion
            $userId = Auth::guard('web')->id();
            $globalUserId = Auth::guard('global')->id();

            UserLog::create([
                'user_id'         => $userId,
                'global_user_id'  => $globalUserId,
                'module'          => 'Payslip',
                'action'          => 'Delete',
                'description'     => 'Deleted Payslip with ID "' . $payslip->id . '"',
                'affected_id'     => $payslip->id,
                'old_data'        => json_encode($oldData),
                'new_data'        => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payslip deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting payslip: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting payslip.'
            ], 500);
        }
    }


    //  User Payslip Index
    public function userPayslipIndex(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $userId = $authUser->id;

        $payslips = Payroll::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'Paid')
            ->orderBy('payment_date', 'desc')
            ->latest('id')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'payslips' => $payslips,
            ]);
        }

        return view('tenant.payroll.payslip.userpayslip.payslip', compact('tenantId', 'userId', 'payslips'));
    }

    // User Payslip Dashboard Chart
    public function userDashboardChartData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $authUser = Auth::user();
        $tenantId = $authUser->tenant_id ?? null;
        $userId = $authUser->id;

        $netSalaries = Payroll::selectRaw('MONTH(payment_date) as month, SUM(net_salary) as total')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->whereYear('payment_date', $year)
            ->where('status', 'Paid')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Prepare data for all 12 months (fill missing months with 0)
        $months = range(1, 12);
        $totals = [];
        foreach ($months as $m) {
            $row = $netSalaries->firstWhere('month', $m);
            $totals[] = $row ? floatval($row->total) : 0;
        }

        return response()->json([
            'months' => ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            'totals' => $totals,
        ]);
    }

    // User Payslip Summary
    public function userPayrollSummary(Request $request)
    {
        $range = $request->input('range', 'monthly');
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        if ($range == 'yearly') {
            $start = Carbon::createFromDate($year)->startOfYear()->format('Y-m-d');
            $end = Carbon::createFromDate($year)->endOfYear()->format('Y-m-d');
        } else {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
            $end = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
        }

        $authUser = Auth::user();
        $tenantId = $authUser->tenant_id ?? null;
        $userId = $authUser->id;

        $totalEarnings = Payroll::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('total_earnings');

        $totalDeductions = Payroll::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('total_deductions');

        $totalNetSalary = Payroll::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('net_salary');

        $totalPayrollCount = Payroll::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'Paid')
            ->whereBetween('payment_date', [$start, $end])
            ->count();

        return response()->json([
            'totalEarnings' => $totalEarnings,
            'totalDeductions' => $totalDeductions,
            'totalNetSalary' => $totalNetSalary,
            'totalPayrollCount' => $totalPayrollCount,
            'range' => $range,
            'year' => $year,
            'month' => $month,
            'start' => $start,
            'end' => $end
        ]);
    }

    // Admin Generated Payslip Bulk Delete
    public function bulkDeletePayslip(Request $request)
    {
        $payslipIds = $request->input('payroll_ids', []);
        if (empty($payslipIds)) {
            return response()->json(['message' => 'Please select at least 1 payslip'], 400);
        }

        // Proceed with bulk deletion
        Payroll::whereIn('id', $payslipIds)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payslip records deleted successfully!',
        ]);
    }

    // Admin Generated Payslip Revert to Pending
    public function bulkRevertPayslip(Request $request)
    {
        $payslipIds = $request->input('payroll_ids', []);

        if (empty($payslipIds)) {
            return response()->json(['message' => 'Please select at least 1 payslip'], 400);
        }

        // Proceed with bulk revert
        $payslips = Payroll::whereIn('id', $payslipIds)->get();
        foreach ($payslips as $payslip) {
            $payslip->status = 'Pending';
            $payslip->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Payslip records reverted to Pending status successfully!',
        ]);
    }

    // User Generated Payslip
    public function userGeneratedPayslip(Request $request, $id)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $payslips = Payroll::findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'payslips' => $payslips,
            ]);
        }

        return view('tenant.payroll.payslip.userpayslip.payslipview', compact('tenantId', 'payslips'));
    }

    /**
     * Upload and process CSV payslip file
     */
    public function uploadPayslips(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(25);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sorry, you don\'t have permission to upload payslips. Please contact your administrator.'
            ], 403);
        }

        $request->validate([
            'payslip_file' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'payslip_file.required' => 'Please select a file to upload.',
            'payslip_file.file' => 'The uploaded file is invalid. Please try again.',
            'payslip_file.mimes' => 'Only CSV files are allowed. Please upload a .csv file.',
            'payslip_file.max' => 'The file is too large. Maximum file size is 10MB.',
        ]);

        try {
            $file = $request->file('payslip_file');

            // Read CSV file
            $csvData = array_map('str_getcsv', file($file->getRealPath()));

            if (empty($csvData)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The file you uploaded is empty. Please add some data and try again.'
                ], 400);
            }

            // Get headers from first row
            $headers = array_shift($csvData);

            // Validate required columns
            $requiredColumns = ['Employee ID', 'Payroll Month', 'Payroll Year', 'Net Salary'];
            $missingColumns = array_diff($requiredColumns, $headers);

            if (!empty($missingColumns)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your file is missing some important columns: ' . implode(', ', $missingColumns) . '. Please download the template and make sure all columns are included.'
                ], 400);
            }

            // Convert to associative array
            $formattedData = [];
            foreach ($csvData as $row) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                if (count($headers) === count($row)) {
                    $formattedData[] = array_combine($headers, $row);
                }
            }

            if (empty($formattedData)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No valid data found in your file. Please make sure you have filled in the employee information correctly.'
                ], 400);
            }

            $tenantId = $authUser->tenant_id ?? null;
            $processorId = $authUser->id;
            $processorType = get_class($authUser);

            // Dispatch job to process in background
            \App\Jobs\ImportPayslipsJob::dispatch(
                $formattedData,
                $tenantId,
                $processorId,
                $processorType
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Your file has been uploaded successfully! We are now processing ' . count($formattedData) . ' payslip records. This may take a few moments...',
                'total_rows' => count($formattedData)
            ]);

        } catch (\Exception $e) {
            Log::error('Error uploading payslips: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while uploading your file. Please try again or contact support if the problem persists.'
            ], 500);
        }
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Employee ID',
            'Payroll Month',
            'Payroll Year',
            'Payroll Period Start',
            'Payroll Period End',
            'Transaction Date',
            'Basic Pay',
            'Gross Pay',
            'Total Earnings',
            'Total Deductions',
            'Net Salary',
            'Holiday Pay',
            'Overtime Pay',
            'Night Differential Pay',
            'Leave Pay',
            'Late Deduction',
            'Undertime Deduction',
            'Absent Deduction',
            'SSS Contribution',
            'PhilHealth Contribution',
            'Pag-IBIG Contribution',
            'Withholding Tax',
        ];

        $sampleData = [
            [
                'EMP001',
                'January',
                '2024',
                '2024-01-01',
                '2024-01-15',
                '2024-01-20',
                '30000.00',
                '30000.00',
                '30000.00',
                '5000.00',
                '25000.00',
                '0.00',
                '0.00',
                '0.00',
                '1000.00',
                '200.00',
                '100.00',
                '50.00',
                '581.30',
                '437.50',
                '200.00',
                '1500.00',
            ],
            [
                'EMP002',
                '1',
                '2024',
                '01/01/2024',
                '01/15/2024',
                '01/20/2024',
                '25000.00',
                '25000.00',
                '25000.00',
                '4000.00',
                '21000.00',
                '0.00',
                '0.00',
                '0.00',
                '800.00',
                '150.00',
                '50.00',
                '0.00',
                '581.30',
                '437.50',
                '200.00',
                '1000.00',
            ],
            [
                'EMP003',
                'Feb',
                '2024',
                '2/1/2024',
                '2/15/2024',
                '2/20/2024',
                '28000.00',
                '28000.00',
                '28000.00',
                '4500.00',
                '23500.00',
                '0.00',
                '0.00',
                '0.00',
                '900.00',
                '180.00',
                '80.00',
                '40.00',
                '581.30',
                '437.50',
                '200.00',
                '1300.00',
            ]
        ];

        $callback = function() use ($headers, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payslip_upload_template.csv"',
        ]);
    }

    /**
     * Check import status
     */
    public function checkImportStatus(Request $request)
    {
        $authUser = $this->authUser();
        $processorId = $authUser->id;

        $result = cache()->get('payslip_import_result_' . $processorId);

        if ($result) {
            cache()->forget('payslip_import_result_' . $processorId);

            if (isset($result['error'])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $result['error']
                ]);
            }

            if (!empty($result['failed_rows'])) {
                return response()->json([
                    'status' => 'completed_with_errors',
                    'success_count' => $result['success_count'],
                    'failed_rows' => $result['failed_rows'],
                    'message' => 'Import completed! ' . $result['success_count'] . ' payslips were successfully imported, but ' . count($result['failed_rows']) . ' rows had errors. Please review the errors below and correct them in your file.'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'success_count' => $result['success_count'],
                'message' => 'Great! All ' . $result['success_count'] . ' payslips have been successfully imported!'
            ]);
        }

        return response()->json([
            'status' => 'processing',
            'message' => 'Your payslips are being processed. Please wait a moment...'
        ]);
    }
}
