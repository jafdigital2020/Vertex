<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\LeaveType;
use Illuminate\Console\Command;
use App\Models\LeaveEntitlement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessEarnedLeaves extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaves:process-earned {--type=all : Process specific type (monthly/annual/all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process earned leave accruals based on frequency';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $now = Carbon::now();

        $this->info("Processing earned leaves for: {$type}");

        // Get earned leave types
        $leaveTypes = LeaveType::where('is_earned', true)
            ->where('status', 'active')
            ->get();

        $processedCount = 0;

        foreach ($leaveTypes as $leaveType) {
            if ($type !== 'all') {
                $intervalMatch = match ($type) {
                    'monthly' => $leaveType->earned_interval === 'MONTHLY',
                    'annual' => $leaveType->earned_interval === 'ANNUAL',
                    default => false
                };

                if (!$intervalMatch) continue;
            }

            $count = $this->processLeaveType($leaveType, $now);
            $processedCount += $count;

            $this->info("Processed {$count} entitlements for: {$leaveType->name}");
        }

        $this->info("Total processed: {$processedCount} entitlements");

        return Command::SUCCESS;
    }

    private function processLeaveType(LeaveType $leaveType, Carbon $now): int
    {
        $processedCount = 0;

        // Get all entitlements for this leave type
        $entitlements = LeaveEntitlement::where('leave_type_id', $leaveType->id)
            ->with(['user'])
            ->get();

        foreach ($entitlements as $entitlement) {
            if ($this->shouldProcessEntitlement($entitlement, $leaveType, $now)) {
                $this->addEarnedBalance($entitlement, $leaveType, $now);
                $processedCount++;
            }
        }

        return $processedCount;
    }

    private function shouldProcessEntitlement(LeaveEntitlement $entitlement, LeaveType $leaveType, Carbon $now): bool
    {
        // Check if user is still active
        if (!$entitlement->user || !$entitlement->user->active_license) {
            return false;
        }

        // Get last accrual date
        $lastAccrual = $entitlement->last_accrual_date
            ? Carbon::parse($entitlement->last_accrual_date)
            : Carbon::parse($entitlement->period_start);

        return match ($leaveType->earned_interval) {
            'MONTHLY' => $this->shouldProcessMonthly($lastAccrual, $now),
            'ANNUAL' => $this->shouldProcessAnnually($lastAccrual, $now),
            'EVERY-OTHER-MONTH' => $this->shouldProcessEveryOtherMonth($lastAccrual, $now),
            default => false
        };
    }

    private function shouldProcessMonthly(Carbon $lastAccrual, Carbon $now): bool
    {
        // Process if it's a new month
        return $lastAccrual->format('Y-m') !== $now->format('Y-m');
    }

    private function shouldProcessAnnually(Carbon $lastAccrual, Carbon $now): bool
    {
        // Process if it's been a year since last accrual
        return $lastAccrual->diffInYears($now) >= 1;
    }

    private function shouldProcessEveryOtherMonth(Carbon $lastAccrual, Carbon $now): bool
    {
        // Process if it's been 2 months since last accrual
        return $lastAccrual->diffInMonths($now) >= 2;
    }

    private function addEarnedBalance(LeaveEntitlement $entitlement, LeaveType $leaveType, Carbon $now): void
    {
        DB::transaction(function () use ($entitlement, $leaveType, $now) {
            $earnedAmount = $leaveType->earned_rate ?? 0;

            // Add to current balance
            $entitlement->current_balance += $earnedAmount;
            $entitlement->last_accrual_date = $now->toDateString();
            $entitlement->save();

            // Log the accrual
            Log::info("Earned leave accrual processed", [
                'user_id' => $entitlement->user_id,
                'leave_type_id' => $leaveType->id,
                'leave_type_name' => $leaveType->name,
                'earned_amount' => $earnedAmount,
                'new_balance' => $entitlement->current_balance,
                'accrual_date' => $now->toDateString()
            ]);
        });
    }
}
