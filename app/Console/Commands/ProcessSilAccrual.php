<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\LeaveType;
use Illuminate\Console\Command;
use App\Models\LeaveEntitlement;
use App\Models\EmploymentDetail;
use App\Models\SilAccrualHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessSilAccrual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sil:process-accrual';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Service Incentive Leave (SIL) accrual for employees on their employment anniversary';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing SIL accrual for employment anniversaries...');

        $today = Carbon::today();
        $processedCount = 0;
        $skippedCount = 0;

        // Get all active SIL leave types
        $silLeaveTypes = LeaveType::where('is_sil', true)
            ->where('status', 'active')
            ->get();

        if ($silLeaveTypes->isEmpty()) {
            $this->warn('No active SIL leave types found.');
            return Command::SUCCESS;
        }

        foreach ($silLeaveTypes as $silLeaveType) {
            $this->info("Processing SIL type: {$silLeaveType->name}");

            // Get all active employees with employment details
            $employees = User::whereHas('employmentDetail', function ($q) {
                $q->where('status', '1');
            })
                ->with(['employmentDetail', 'leaveEntitlement' => function ($query) use ($silLeaveType) {
                    $query->where('leave_type_id', $silLeaveType->id);
                }])
                ->get();

            foreach ($employees as $employee) {
                $employmentDetail = $employee->employmentDetail;

                if (!$employmentDetail || !$employmentDetail->date_hired) {
                    continue;
                }

                $hireDate = Carbon::parse($employmentDetail->date_hired);
                $monthsOfService = $hireDate->diffInMonths($today);
                $yearsOfService = floor($monthsOfService / 12);

                // Check if today is the employment anniversary
                $isAnniversary = ($hireDate->month === $today->month && $hireDate->day === $today->day);

                // Check if employee has met minimum service requirement
                $minimumMonths = $silLeaveType->sil_minimum_service_months ?? 12;

                if (!$isAnniversary || $monthsOfService < $minimumMonths) {
                    continue;
                }

                // Get or create leave entitlement for current year
                $currentYear = $today->year;
                $periodStart = Carbon::create($currentYear, 1, 1)->toDateString();
                $periodEnd = Carbon::create($currentYear, 12, 31)->toDateString();

                // Check if entitlement already exists for this period
                $entitlement = LeaveEntitlement::firstOrCreate(
                    [
                        'user_id' => $employee->id,
                        'leave_type_id' => $silLeaveType->id,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                    ],
                    [
                        'opening_balance' => 0,
                        'current_balance' => 0,
                        'last_accrual_date' => null,
                    ]
                );

                // Check if we already accrued for this anniversary
                $lastAccrualDate = $entitlement->last_accrual_date
                    ? Carbon::parse($entitlement->last_accrual_date)
                    : null;

                // If we already processed this year's anniversary, skip
                if (
                    $lastAccrualDate && $lastAccrualDate->year === $today->year &&
                    $lastAccrualDate->month === $today->month
                ) {
                    $skippedCount++;
                    continue;
                }

                // Get SIL days from leave settings or use default
                $leaveSetting = $silLeaveType->leaveSetting()->first();
                $silDaysPerYear = $leaveSetting?->sil_days_per_year ?? 5.00;

                // Add SIL days to the balance
                DB::transaction(function () use ($entitlement, $silDaysPerYear, $today, $employee, $silLeaveType, $yearsOfService, $hireDate) {
                    $entitlement->current_balance += $silDaysPerYear;
                    $entitlement->last_accrual_date = $today->toDateString();
                    $entitlement->save();

                    // Log to accrual history
                    SilAccrualHistory::create([
                        'user_id' => $employee->id,
                        'leave_type_id' => $silLeaveType->id,
                        'accrual_date' => $today->toDateString(),
                        'days_credited' => $silDaysPerYear,
                        'service_years' => $yearsOfService,
                        'employment_date' => $hireDate->toDateString(),
                        'anniversary_date' => $today->toDateString(),
                        'processed_by' => 'system',
                        'notes' => "Automatic SIL accrual on {$yearsOfService} year anniversary",
                    ]);
                });

                $processedCount++;
                $this->info("  ✓ Accrued {$silDaysPerYear} SIL days for {$employee->name} (Year {$yearsOfService} anniversary)");

                // Log the accrual
                Log::info('SIL Accrual processed', [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'leave_type' => $silLeaveType->name,
                    'days_accrued' => $silDaysPerYear,
                    'new_balance' => $entitlement->current_balance,
                    'years_of_service' => $yearsOfService,
                    'anniversary_date' => $today->toDateString(),
                ]);
            }
        }

        $this->info("\n" . str_repeat('=', 50));
        $this->info("SIL Accrual Processing Complete");
        $this->info("Processed: {$processedCount} employees");
        $this->info("Skipped: {$skippedCount} (already processed)");
        $this->info(str_repeat('=', 50));

        return Command::SUCCESS;
    }
}
