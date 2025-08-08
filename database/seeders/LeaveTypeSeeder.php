<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            // Vacation Leave
            [
                'name' => 'Vacation Leave',
                'default_entitle' => 5, // Default entitlement in the Philippines
                'accrual_frequency' => 'ANNUAL', // Accrual Frequency (can be yearly, monthly, etc.)
                'max_carryover' => 10, // Max carryover days
                'is_earned' => 0, // Set to null
                'earned_rate' => null, // Set to null
                'earned_interval' => null, // Set to null
                'is_paid' => true, // Paid leave
                'status' => 'active', // Active or inactive status
                'tenant_id' => 1, // Foreign key to tenant if needed
                'is_cash_convertible' => true, // If leave can be converted to cash
                'conversion_rate' => 1, // Conversion rate (typically 1:1, or define a custom conversion)
            ],

            // Sick Leave
            [
                'name' => 'Sick Leave',
                'default_entitle' => 10, // Default entitlement in the Philippines
                'accrual_frequency' => 'ANNUAL', // Accrual Frequency (can be yearly, monthly, etc.)
                'max_carryover' => 15, // Max carryover days
                'is_earned' => 0, // Set to null
                'earned_rate' => null, // Set to null
                'earned_interval' => null, // Set to null
                'is_paid' => true, // Paid leave
                'status' => 'active', // Active or inactive status
                'tenant_id' => 1, // Foreign key to tenant if needed
                'is_cash_convertible' => false, // Sick leave typically isn't convertible to cash
                'conversion_rate' => 0, // No conversion rate
            ],
        ];

        // Insert leave types into the database
        foreach ($leaveTypes as $leaveType) {
            LeaveType::create($leaveType);
        }
    }
}
