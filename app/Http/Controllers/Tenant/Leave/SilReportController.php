<?php

namespace App\Http\Controllers\Tenant\Leave;

use Carbon\Carbon;
use App\Models\User;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use App\Models\LeaveEntitlement;
use App\Models\EmploymentDetail;
use App\Models\SilAccrualHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SilReportController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        } else {
            return Auth::guard('web')->user();
        }
    }

    /**
     * Display SIL Eligibility Report
     */
    public function eligibilityReport(Request $request)
    {
        $today = Carbon::today();
        $silLeaveTypes = LeaveType::where('is_sil', true)
            ->where('status', 'active')
            ->get();

        // Get all employees with employment details
        $employees = User::whereHas('employmentDetail', function ($q) {
            $q->where('status', '1');
        })
            ->with(['employmentDetail', 'leaveEntitlement', 'personalInformation'])
            ->get()
            ->map(function ($employee) use ($today, $silLeaveTypes) {
                $employmentDetail = $employee->employmentDetail;
                $personalInfo = $employee->personalInformation;

                if (!$employmentDetail || !$employmentDetail->date_hired || !$personalInfo) {
                    return null;
                }

                $hireDate = Carbon::parse($employmentDetail->date_hired);
                $monthsOfService = $hireDate->diffInMonths($today);
                $yearsOfService = floor($monthsOfService / 12);

                // Calculate next anniversary
                $nextAnniversary = Carbon::create($today->year, $hireDate->month, $hireDate->day);
                if ($nextAnniversary->lt($today)) {
                    $nextAnniversary->addYear();
                }
                $daysUntilAnniversary = $today->diffInDays($nextAnniversary);

                // Check SIL eligibility for each leave type
                $silStatuses = [];
                foreach ($silLeaveTypes as $silType) {
                    $minimumMonths = $silType->sil_minimum_service_months ?? 12;
                    $isEligible = $monthsOfService >= $minimumMonths;

                    // Get current SIL balance
                    $entitlement = $employee->leaveEntitlement
                        ->where('leave_type_id', $silType->id)
                        ->first();

                    $currentBalance = $entitlement ? $entitlement->current_balance : 0;
                    $lastAccrual = $entitlement && $entitlement->last_accrual_date
                        ? Carbon::parse($entitlement->last_accrual_date)->format('M d, Y')
                        : 'Never';

                    $silStatuses[] = [
                        'leave_type_name' => $silType->name,
                        'is_eligible' => $isEligible,
                        'current_balance' => $currentBalance,
                        'last_accrual' => $lastAccrual,
                        'minimum_months' => $minimumMonths,
                    ];
                }

                $fullName = trim("{$personalInfo->first_name} {$personalInfo->last_name}");
                $employeeId = $employmentDetail->employee_id ?? 'N/A';

                return [
                    'id' => $employee->id,
                    'employee_id' => $employeeId,
                    'name' => $fullName,
                    'email' => $employee->email,
                    'hire_date' => $hireDate->format('M d, Y'),
                    'months_of_service' => $monthsOfService,
                    'years_of_service' => $yearsOfService,
                    'next_anniversary' => $nextAnniversary->format('M d, Y'),
                    'days_until_anniversary' => $daysUntilAnniversary,
                    'sil_statuses' => $silStatuses,
                ];
            })
            ->filter() // Remove nulls
            ->values();

        // Filters
        $eligibilityFilter = $request->get('eligibility', 'all'); // all, eligible, not_eligible
        $upcomingDays = $request->get('upcoming_days', null); // Filter by days until anniversary

        if ($eligibilityFilter === 'eligible') {
            $employees = $employees->filter(function ($emp) {
                return collect($emp['sil_statuses'])->contains('is_eligible', true);
            })->values();
        } elseif ($eligibilityFilter === 'not_eligible') {
            $employees = $employees->filter(function ($emp) {
                return !collect($emp['sil_statuses'])->contains('is_eligible', true);
            })->values();
        }

        if ($upcomingDays) {
            $employees = $employees->filter(function ($emp) use ($upcomingDays) {
                return $emp['days_until_anniversary'] <= (int)$upcomingDays;
            })->values();
        }

        return view('tenant.leave.sil-eligibility-report', compact(
            'employees',
            'silLeaveTypes',
            'eligibilityFilter',
            'upcomingDays'
        ));
    }

    /**
     * Display SIL Accrual History
     */
    public function accrualHistory(Request $request)
    {
        $query = SilAccrualHistory::with(['user.personalInformation', 'user.employmentDetail', 'leaveType'])
            ->orderBy('accrual_date', 'desc');

        // Filters
        if ($request->has('employee_id') && $request->employee_id) {
            $query->where('user_id', $request->employee_id);
        }

        if ($request->has('leave_type_id') && $request->leave_type_id) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->where('accrual_date', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->where('accrual_date', '<=', $request->to_date);
        }

        $accrualHistory = $query->paginate(50);

        // Get filter data
        $employees = User::whereHas('employmentDetail', function ($q) {
            $q->where('status', '1');
        })
            ->with([
                'personalInformation:user_id,first_name,last_name',
                'employmentDetail:user_id,employee_id'
            ])
            ->get(['id'])
            ->map(function ($user) {
                $fullName = $user->personalInformation
                    ? trim("{$user->personalInformation->first_name} {$user->personalInformation->last_name}")
                    : 'N/A';
                $employeeId = $user->employmentDetail->employee_id ?? 'N/A';
                return (object) [
                    'id' => $user->id,
                    'employee_id' => $employeeId,
                    'name' => $fullName,
                ];
            })
            ->sortBy('name')
            ->values();

        $silLeaveTypes = LeaveType::where('is_sil', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('tenant.leave.sil-accrual-history', compact(
            'accrualHistory',
            'employees',
            'silLeaveTypes'
        ));
    }

    /**
     * Export SIL Eligibility Report to CSV
     */
    public function exportEligibility(Request $request)
    {
        // Call the same logic as eligibilityReport to get filtered data
        $today = Carbon::today();
        $silLeaveTypes = LeaveType::where('is_sil', true)
            ->where('status', 'active')
            ->get();

        $employees = User::whereHas('employmentDetail', function ($q) {
            $q->where('status', '1');
        })
            ->with(['employmentDetail', 'leaveEntitlement', 'personalInformation'])
            ->get()
            ->map(function ($employee) use ($today, $silLeaveTypes) {
                $employmentDetail = $employee->employmentDetail;
                $personalInfo = $employee->personalInformation;

                if (!$employmentDetail || !$employmentDetail->date_hired || !$personalInfo) {
                    return null;
                }

                $hireDate = Carbon::parse($employmentDetail->date_hired);
                $monthsOfService = $hireDate->diffInMonths($today);
                $yearsOfService = floor($monthsOfService / 12);

                $nextAnniversary = Carbon::create($today->year, $hireDate->month, $hireDate->day);
                if ($nextAnniversary->lt($today)) {
                    $nextAnniversary->addYear();
                }
                $daysUntilAnniversary = $today->diffInDays($nextAnniversary);

                $fullName = trim("{$personalInfo->first_name} {$personalInfo->last_name}");
                $employeeId = $employmentDetail->employee_id ?? 'N/A';

                $silStatus = '';
                $silBalance = 0;
                foreach ($silLeaveTypes as $silType) {
                    $minimumMonths = $silType->sil_minimum_service_months ?? 12;
                    $isEligible = $monthsOfService >= $minimumMonths;
                    $entitlement = $employee->leaveEntitlement
                        ->where('leave_type_id', $silType->id)
                        ->first();
                    $silBalance = $entitlement ? $entitlement->current_balance : 0;
                    $silStatus = $isEligible ? 'Eligible' : 'Not Eligible';
                }

                return [
                    $employeeId,
                    $fullName,
                    $hireDate->format('Y-m-d'),
                    $yearsOfService,
                    $nextAnniversary->format('Y-m-d'),
                    $daysUntilAnniversary,
                    $silStatus,
                    $silBalance,
                ];
            })
            ->filter()
            ->values();

        $filename = 'sil_eligibility_report_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($employees) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee ID', 'Name', 'Hire Date', 'Years of Service', 'Next Anniversary', 'Days Until Anniversary', 'SIL Status', 'Current SIL Balance']);

            foreach ($employees as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
