<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\LeaveType;
use App\Models\EarningType;
use App\Models\UserEarning;
use App\Models\LeaveConversion;
use Illuminate\Console\Command;
use App\Models\LeaveEntitlement;

class ConvertLeaveToCash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:convert-to-cash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert unused leaves into cash and assign earnings to employees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        // Step 1: Ensure earning type exists
        $leaveConversionType = EarningType::firstOrCreate(
            ['name' => 'Leave Conversion'],
            [
                'calculation_method' => 'fixed',
                'default_amount' => 0,
                'is_taxable' => true,
                'apply_to_all_employees' => false,
                'description' => 'Cash conversion of unused leaves at year-end',
                'created_by_type' => 'system',
                'created_by_id' => 0,
            ]
        );

        // Step 2: Process each convertible leave type
        $leaveTypes = LeaveType::where('is_cash_convertible', true)->get();

        foreach ($leaveTypes as $leaveType) {
            $entitlements = LeaveEntitlement::where('leave_type_id', $leaveType->id)
                ->where('current_balance', '>', 0)
                ->get();

            foreach ($entitlements as $entitlement) {
                $user = $entitlement->user;
                $remaining = $entitlement->current_balance;

                if ($remaining <= 0) {
                    continue;
                }

                $rate = $leaveType->conversion_rate ?? 1.0;
                $dailyRate = $user->daily_rate ?? 0;

                $convertedAmount = $remaining * $rate * $dailyRate;

                if ($convertedAmount <= 0) {
                    continue;
                }

                // Step 3: Create leave conversion record
                LeaveConversion::create([
                    'user_id' => $user->id,
                    'leave_type_id' => $leaveType->id,
                    'converted_days' => $remaining,
                    'rate_per_day' => $dailyRate,
                    'total_amount' => $convertedAmount,
                    'conversion_date' => $today,
                ]);

                // Step 4: Insert into user earnings
                UserEarning::create([
                    'user_id' => $user->id,
                    'earning_type_id' => $leaveConversionType->id,
                    'type' => 'fixed',
                    'amount' => $convertedAmount,
                    'frequency' => 'one_time',
                    'effective_start_date' => $today,
                    'status' => 'active',
                    'created_by_type' => 'system',
                    'created_by_id' => 0,
                ]);

                // Step 5: Optionally reset current_balance
                $entitlement->update(['current_balance' => 0]);

                $this->info("✔ Converted {$remaining} day(s) of leave for {$user->name} worth ₱{$convertedAmount}");
            }
        }

        $this->info('🎉 Leave cash conversion completed!');
        return 0;
    }
}
