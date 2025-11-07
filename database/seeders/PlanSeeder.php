<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Monthly Plans
        Plan::create([
            'name' => 'Core Starter Monthly Plan',
            'description' => 'Up to 20 employees.',
            'price' => 5000.00,
            'currency' => 'PHP',
            'billing_cycle' => 'monthly',
            'employee_limit' => 10,
            'employee_price' => 49.00,
            'trial_days' => 0,
            'is_active' => true,
            'price_per_license' => 49.00,
            'implementation_fee' => 4999.00,
            'base_license_count' => 10,
        ]);

        Plan::create([
            'name' => 'Core Monthly Plan',
            'description' => 'Up to 100 employees.',
            'price' => 5500.00,
            'currency' => 'PHP',
            'billing_cycle' => 'monthly',
            'employee_limit' => 100,
            'employee_price' => 49.00,
            'trial_days' => 0,
            'is_active' => true,
            'price_per_license' => 49.00,
            'implementation_fee' => 14999.00,
            'base_license_count' => 100,
        ]);

        Plan::create([
            'name' => 'Pro Monthly Plan',
            'description' => 'Up to 200 employees.',
            'price' => 9500.00,
            'currency' => 'PHP',
            'billing_cycle' => 'monthly',
            'employee_limit' => 200,
            'employee_price' => 49.00,
            'trial_days' => 0,
            'is_active' => true,
            'price_per_license' => 49.00,
            'implementation_fee' => 39999.00,
            'base_license_count' => 200,
        ]);

        Plan::create([
            'name' => 'Elite Monthly Plan',
            'description' => 'Up to 500 employees.',
            'price' => 14500.00,
            'currency' => 'PHP',
            'billing_cycle' => 'monthly',
            'employee_limit' => 500,
            'employee_price' => 49.00,
            'trial_days' => 0,
            'is_active' => true,
            'price_per_license' => 49.00,
            'implementation_fee' => 79999.00,
            'base_license_count' => 500,
        ]);

        //  ==============  Yearly Plans ================ //
        Plan::create([
            'name' => 'Core Starter Yearly Plan',
            'description' => 'Up to 20 employees.',
            'price' => 57000.00,
            'currency' => 'PHP',
            'billing_cycle' => 'yearly',
            'employee_limit' => 10,
            'employee_price' => 49.00,
            'trial_days' => 0,
            'is_active' => true,
            'price_per_license' => 49.00,
            'implementation_fee' => 4999.00,
            'base_license_count' => 10,
        ]);

        Plan::create([
            'name' => 'Core Yearly Plan',
            'description' => 'Up to 100 employees.',
            'price' => 62700.00,
            'currency' => 'PHP',
            'billing_cycle' => 'yearly',
            'employee_limit' => 100,
            'employee_price' => 49.00,
            'trial_days' => 0,
            'is_active' => true,
            'price_per_license' => 49.00,
            'implementation_fee' => 14999.00,
            'base_license_count' => 100,
        ]);

        Plan::create([
            'name' => 'Pro Yearly Plan',
            'description' => 'Up to 200 employees.',
            'price' => 108300.00,
            'currency' => 'PHP',
            'billing_cycle' => 'yearly',
            'employee_limit' => 200,
            'employee_price' => 49.00,
            'trial_days' => 0,
            'is_active' => true,
            'price_per_license' => 49.00,
            'implementation_fee' => 39999.00,
            'base_license_count' => 200,
        ]);

        Plan::create([
            'name' => 'Elite Yearly Plan',
            'description' => 'Up to 500 employees.',
            'price' => 165300.00,
            'currency' => 'PHP',
            'billing_cycle' => 'yearly',
            'employee_limit' => 500,
            'employee_price' => 49.00,
            'trial_days' => 0,
            'is_active' => true,
            'price_per_license' => 49.00,
            'implementation_fee' => 79999.00,
            'base_license_count' => 500,
        ]);
    }
}
