<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing tenants
        $tenants = Tenant::all();

        // Create a default main branch for each tenant
        foreach ($tenants as $tenant) {
            // Check if tenant already has a main branch
            $existingMainBranch = DB::table('branches')
                ->where('tenant_id', $tenant->id)
                ->where('branch_type', 'main')
                ->first();

            if (!$existingMainBranch) {
                DB::table('branches')->insert([
                    'tenant_id' => $tenant->id,
                    'name' => 'Head Office',
                    'location' => 'Corporate Headquarters',
                    'contact_number' => '+63 2 8123 4567',
                    'branch_logo' => null,
                    'branch_type' => 'main',
                    'sss_contribution_type' => 'system',
                    'philhealth_contribution_type' => 'system',
                    'pagibig_contribution_type' => 'system',
                    'withholding_tax_type' => 'system',
                    'status' => 1, // 1 = active, 0 = inactive (boolean field)
                    'worked_days_per_year' => '261',
                    'custom_worked_days' => null,
                    'fixed_sss_amount' => null,
                    'fixed_philhealth_amount' => null,
                    'fixed_pagibig_amount' => null,
                    'fixed_withholding_tax_amount' => null,
                    'e_signature' => null,
                    'basic_salary' => null, // Leave blank as salaries may vary per employee
                    'salary_type' => null, // Leave blank as salary types may vary per employee
                    'salary_computation_type' => 'monthly',
                    'wage_order' => 'NCR-24', // DOLE NCR wage order 2024
                    'branch_tin' => null,
                    'sss_contribution_template' => '2025',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
