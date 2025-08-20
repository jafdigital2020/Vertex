<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use League\Csv\Reader;
use Illuminate\Support\Facades\DB;


class UserBranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed users, branches, permissions, and employment details from a single CSV
        $path = database_path('seeders/data/user_branch_permission.csv');
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $row) {
            // Insert or get user
            $userId = DB::table('users')->insertGetId([
                'tenant_id' => $row['user_tenant_id'] ?? null,
                'username' => $row['username'],
                'email' => $row['email'],
                'email_verified_at' => $row['email_verified_at'] ?? null,
                'password' => bcrypt($row['password']),
                'remember_token' => $row['remember_token'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert branch
            $branchId = DB::table('branches')->insertGetId([
                'tenant_id' => $row['branch_tenant_id'] ?? null,
                'name' => $row['branch_name'],
                'location' => $row['branch_location'],
                'contact_number' => $row['branch_contact_number'] ?? null,
                'branch_logo' => $row['branch_logo'] ?? null,
                'branch_type' => $row['branch_type'] ?? 'sub',
                'sss_contribution_type' => $row['sss_contribution_type'] ?? 'system',
                'philhealth_contribution_type' => $row['philhealth_contribution_type'] ?? 'system',
                'pagibig_contribution_type' => $row['pagibig_contribution_type'] ?? 'system',
                'withholding_tax_type' => $row['withholding_tax_type'] ?? 'system',
                'worked_days_per_year' => $row['worked_days_per_year'] ?? '261',
                'custom_worked_days' => $row['custom_worked_days'] ?? null,
                'fixed_sss_amount' => $row['fixed_sss_amount'] ?? null,
                'fixed_philhealth_amount' => $row['fixed_philhealth_amount'] ?? null,
                'fixed_pagibig_amount' => $row['fixed_pagibig_amount'] ?? null,
                'fixed_withholding_tax_amount' => $row['fixed_withholding_tax_amount'] ?? null,
                'e_signature' => $row['e_signature'] ?? null,
                'status' => $row['branch_status'] ?? 1,
                'basic_salary' => $row['basic_salary'] ?? null,
                'salary_type' => $row['salary_type'] ?? null,
                'salary_computation_type' => $row['salary_computation_type'] ?? 'monthly',
                'wage_order' => $row['wage_order'] ?? null,
                'branch_tin' => $row['branch_tin'] ?? null,
                'sss_contribution_template' => $row['sss_contribution_template'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert user permission
            DB::table('user_permission')->insert([
                'user_id' => $userId,
                'role_id' => $row['role_id'],
                'data_access_id' => $row['data_access_id'] ?? null,
                'menu_ids' => $row['menu_ids'] ?? null,
                'module_ids' => $row['module_ids'] ?? null,
                'user_permission_ids' => $row['user_permission_ids'] ?? null,
                'status' => $row['permission_status'] ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert employment details
            DB::table('employment_details')->insert([
                'user_id' => $userId,
                'employee_id' => $row['employee_id'],
                'branch_id' => $branchId,
                'department_id' => $row['department_id'],
                'designation_id' => $row['designation_id'],
                'employment_type' => $row['employment_type'],
                'employment_status' => $row['employment_status'],
                'date_hired' => $row['date_hired'],
                'end_of_contract' => $row['end_of_contract'] ?? null,
                'reporting_to' => $row['reporting_to'] ?? null,
                'status' => $row['employment_status_flag'] ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
                'security_license_number' => $row['security_license_number'] ?? null,
                'security_license_expiration' => $row['security_license_expiration'] ?? null,
            ]);
        }
    }
}
