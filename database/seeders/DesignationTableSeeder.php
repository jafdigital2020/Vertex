<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DesignationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define designations by department code
        $designationsByDepartment = [
            'HR' => [
                [
                    'designation_name' => 'HR Manager',
                    'job_description' => 'Oversees all HR operations, employee relations, and organizational development',
                ],
                [
                    'designation_name' => 'HR Specialist',
                    'job_description' => 'Handles recruitment, employee onboarding, and HR administrative tasks',
                ],
                [
                    'designation_name' => 'Recruitment Officer',
                    'job_description' => 'Responsible for talent acquisition, interviews, and hiring processes',
                ],
                [
                    'designation_name' => 'Payroll Specialist',
                    'job_description' => 'Manages payroll processing, benefits administration, and compensation',
                ],
            ],
            'IT' => [
                [
                    'designation_name' => 'IT Manager',
                    'job_description' => 'Leads IT strategy, infrastructure management, and team supervision',
                ],
                [
                    'designation_name' => 'Software Developer',
                    'job_description' => 'Develops, maintains, and enhances software applications and systems',
                ],
                [
                    'designation_name' => 'System Administrator',
                    'job_description' => 'Manages servers, networks, and IT infrastructure maintenance',
                ],
                [
                    'designation_name' => 'IT Support Specialist',
                    'job_description' => 'Provides technical support and troubleshooting for end users',
                ],
                [
                    'designation_name' => 'Database Administrator',
                    'job_description' => 'Manages database systems, backup, recovery, and optimization',
                ],
            ],
            'FIN' => [
                [
                    'designation_name' => 'Finance Manager',
                    'job_description' => 'Oversees financial planning, analysis, and reporting activities',
                ],
                [
                    'designation_name' => 'Accountant',
                    'job_description' => 'Handles financial transactions, bookkeeping, and account reconciliation',
                ],
                [
                    'designation_name' => 'Financial Analyst',
                    'job_description' => 'Analyzes financial data, prepares reports, and supports decision-making',
                ],
                [
                    'designation_name' => 'Accounts Payable Clerk',
                    'job_description' => 'Processes vendor invoices, payments, and maintains supplier records',
                ],
                [
                    'designation_name' => 'Accounts Receivable Clerk',
                    'job_description' => 'Manages customer billing, collections, and receivables tracking',
                ],
            ],
            'OPS' => [
                [
                    'designation_name' => 'Operations Manager',
                    'job_description' => 'Oversees daily operations, process improvement, and operational efficiency',
                ],
                [
                    'designation_name' => 'Operations Supervisor',
                    'job_description' => 'Supervises operational activities and ensures quality standards',
                ],
                [
                    'designation_name' => 'Process Analyst',
                    'job_description' => 'Analyzes business processes and implements optimization strategies',
                ],
                [
                    'designation_name' => 'Operations Coordinator',
                    'job_description' => 'Coordinates operational activities and supports cross-functional teams',
                ],
            ],
            'SALES' => [
                [
                    'designation_name' => 'Sales Manager',
                    'job_description' => 'Leads sales team, develops strategies, and manages key client relationships',
                ],
                [
                    'designation_name' => 'Sales Representative',
                    'job_description' => 'Generates leads, presents products, and closes sales deals',
                ],
                [
                    'designation_name' => 'Marketing Specialist',
                    'job_description' => 'Develops marketing campaigns, content creation, and brand promotion',
                ],
                [
                    'designation_name' => 'Account Executive',
                    'job_description' => 'Manages client accounts, maintains relationships, and ensures satisfaction',
                ],
                [
                    'designation_name' => 'Business Development Officer',
                    'job_description' => 'Identifies new business opportunities and develops strategic partnerships',
                ],
            ],
            'ADM' => [
                [
                    'designation_name' => 'Administrative Manager',
                    'job_description' => 'Oversees administrative functions and office management',
                ],
                [
                    'designation_name' => 'Administrative Assistant',
                    'job_description' => 'Provides administrative support, scheduling, and office coordination',
                ],
                [
                    'designation_name' => 'Executive Secretary',
                    'job_description' => 'Supports executives with scheduling, correspondence, and meeting coordination',
                ],
                [
                    'designation_name' => 'Office Clerk',
                    'job_description' => 'Handles filing, data entry, and general clerical duties',
                ],
                [
                    'designation_name' => 'Receptionist',
                    'job_description' => 'Manages front desk operations, visitor reception, and phone handling',
                ],
            ],
        ];

        // Get all departments and create designations for each
        $departments = Department::all();

        foreach ($departments as $department) {
            $departmentCode = $department->department_code;
            
            if (isset($designationsByDepartment[$departmentCode])) {
                foreach ($designationsByDepartment[$departmentCode] as $designationData) {
                    // Check if designation already exists for this department
                    $existingDesignation = DB::table('designations')
                        ->where('department_id', $department->id)
                        ->where('designation_name', $designationData['designation_name'])
                        ->first();

                    if (!$existingDesignation) {
                        DB::table('designations')->insert([
                            'department_id' => $department->id,
                            'designation_name' => $designationData['designation_name'],
                            'job_description' => $designationData['job_description'],
                            'status' => 'active',
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                }
            }
        }
    }
}