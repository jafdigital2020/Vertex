<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Branch;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing tenants test
        $tenants = Tenant::all();

        // Default departments to create for each tenant
        $defaultDepartments = [
            [
                'department_code' => 'HR',
                'department_name' => 'Human Resources',
                'description' => 'Manages employee relations, recruitment, benefits, and organizational development',
                'status' => 'active',
            ],
            [
                'department_code' => 'IT',
                'department_name' => 'Information Technology',
                'description' => 'Responsible for technology infrastructure, software development, and technical support',
                'status' => 'active',
            ],
            [
                'department_code' => 'FIN',
                'department_name' => 'Finance',
                'description' => 'Handles financial planning, accounting, budgeting, and financial reporting',
                'status' => 'active',
            ],
            [
                'department_code' => 'OPS',
                'department_name' => 'Operations',
                'description' => 'Manages day-to-day business operations and process optimization',
                'status' => 'active',
            ],
            [
                'department_code' => 'SALES',
                'department_name' => 'Sales & Marketing',
                'description' => 'Responsible for revenue generation, customer acquisition, and market expansion',
                'status' => 'active',
            ],
            [
                'department_code' => 'ADM',
                'department_name' => 'Administration',
                'description' => 'Provides administrative support and manages general office operations',
                'status' => 'active',
            ],
        ];

        // Create departments for each tenant and their branches
        foreach ($tenants as $tenant) {
            // Get all branches for this tenant
            $branches = Branch::where('tenant_id', $tenant->id)->get();
            
            foreach ($branches as $branch) {
                foreach ($defaultDepartments as $deptData) {
                    // Check if department already exists for this branch
                    $existingDepartment = DB::table('departments')
                        ->where('branch_id', $branch->id)
                        ->where('department_code', $deptData['department_code'])
                        ->first();

                    if (!$existingDepartment) {
                        DB::table('departments')->insert([
                            'branch_id' => $branch->id,
                            'department_code' => $deptData['department_code'],
                            'department_name' => $deptData['department_name'],
                            'description' => $deptData['description'],
                            'status' => $deptData['status'],
                            'head_of_department' => null, // Will be assigned later when employees are created
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                }
            }
        }
    }
}