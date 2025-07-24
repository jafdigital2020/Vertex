<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    { 
         DB::table('sub_modules')->insert([
        [
            'sub_module_name' => 'Admin Dashboard', 
            'module_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
        [
            'sub_module_name' => 'Employee Dashboard', 
            'module_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
            [
            'sub_module_name' => 'Dashboard', 
            'module_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
        [
            'sub_module_name' => 'Tenants', 
            'module_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
            [
            'sub_module_name' => 'Subscriptions', 
            'module_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
        [
            'sub_module_name' => 'Packages', 
            'module_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Payment Transaction', 
            'module_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Branch', 
            'module_id' => 3,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
          [
            'sub_module_name' => 'Employee Lists', 
            'module_id' => 4,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Departments', 
            'module_id' => 4,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
        [
            'sub_module_name' => 'Designations', 
            'module_id' => 4,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Policies', 
            'module_id' => 4,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Holidays', 
            'module_id' => 5,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],

            [
            'sub_module_name' => 'Payment Transaction', 
            'module_id' => 6,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
          [
            'sub_module_name' => 'Attendance (Admin)', 
            'module_id' => 6,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Attendance (Employee)', 
            'module_id' => 6,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
        [
            'sub_module_name' => 'Shift & Schedule', 
            'module_id' => 6,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Overtime(Admin)', 
            'module_id' => 6,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Attendance Settings', 
            'module_id' => 6,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Leaves (Admin)', 
            'module_id' => 7,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Leave (Employee)', 
            'module_id' => 7,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Leave Settings', 
            'module_id' => 7,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Resignation', 
            'module_id' => 8,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Termination', 
            'module_id' => 9,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
            [
            'sub_module_name' => 'Employee Salary', 
            'module_id' => 10,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Generated Payslips', 
            'module_id' => 10,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Payroll Items', 
            'module_id' => 10,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
          [
            'sub_module_name' => 'Payslip', 
            'module_id' => 11,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
          [
            'sub_module_name' => 'Knowledge Base', 
            'module_id' => 12,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Activities', 
            'module_id' => 12,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Users', 
            'module_id' => 13,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Roles & Permissions', 
            'module_id' => 13,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
           [
            'sub_module_name' => 'Expense Report', 
            'module_id' => 14,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Invoice Report', 
            'module_id' => 14,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Payment Report', 
            'module_id' => 14,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Project Report', 
            'module_id' => 14,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Task Report', 
            'module_id' => 14,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
             [
            'sub_module_name' => 'User Report', 
            'module_id' => 14,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Employee Report', 
            'module_id' => 14,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Payslip Report', 
            'module_id' => 14,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Attendance Report', 
            'module_id' => 14,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
            [
            'sub_module_name' => 'Leave Report', 
            'module_id' => 14,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Daily Report', 
            'module_id' => 14,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'App Settings', 
            'module_id' => 15,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Overtime(Employee)', 
            'module_id' => 6,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [ 
            'sub_module_name' => 'Bank', 
            'module_id'=> 16,  
            'order_no'=> 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'sub_module_name' => 'Official Business (Admin)', 
            'module_id' => 17,
            'order_no' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
        [
            'sub_module_name' => 'Official Business (Employee)', 
            'module_id' => 17,
            'order_no' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'sub_module_name' => 'Employee Assets', 
            'module_id' => 18,
            'order_no' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
        [
            'sub_module_name' => 'Assets Settings', 
            'module_id' => 18,
            'order_no' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
         [
            'sub_module_name' => 'Payroll Batch Users', 
            'module_id' => 10,
            'order_no' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
        [
            'sub_module_name' => 'Payroll Batch Settings', 
            'module_id' => 10,
            'order_no' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],


    ]);
    }
}
