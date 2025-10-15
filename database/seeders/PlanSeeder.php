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
        Plan::create([
            'name' => 'Core',
            'description' => 'Up to 10 employees.',
            'price' => 5000.00,
            'currency' => 'PHP',
            'billing_cycle' => 'monthly',
            'employee_limit' => 10,
            'employee_price' => 49.00,
            'trial_days' => 7,
            'is_active' => true,
            'price_per_license' => 49.00,
            'base_license_count' => 10,
        ]);

        Plan::create([
            'name' => 'Pro',
            'description' => 'Up to 100 employees.',
            'price' => 9410.00,
            'currency' => 'PHP',
            'billing_cycle' => 'monthly',
            'employee_limit' => 100,
            'employee_price' => 49.00,
            'trial_days' => 7,
            'is_active' => true,
            'price_per_license' => 49.00,
            'base_license_count' => 100,
        ]);

        Plan::create([
            'name' => 'Elite',
            'description' => 'Up to 200 employees.',
            'price' => 14310.00,
            'currency' => 'PHP',
            'billing_cycle' => 'monthly',
            'employee_limit' => 200,
            'employee_price' => 49.00,
            'trial_days' => 7,
            'is_active' => true,
            'price_per_license' => 49.00,
            'base_license_count' => 100,
        ]);
    }
}
