<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ModulesTableSeeder extends Seeder
{
   
    public function run(): void
    {
        DB::table('modules')->insert([
        [
            'module_name' => 'Dashboard', 
            'menu_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'module_name' => 'Super Admin', 
            'menu_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'module_name' => 'Branch', 
            'menu_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'module_name' => 'Employees', 
            'menu_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'module_name' => 'Holidays', 
            'menu_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'module_name' => 'Attendance',
            'menu_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'module_name' => 'Leaves', 
            'menu_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'module_name' => 'Resignation',
            'menu_id' => 2, 
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'module_name' => 'Termination', 
            'menu_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'module_name' => 'Payroll', 
            'menu_id' => 3,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'module_name' => 'Payslip', 
            'menu_id' => 3,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
             [
            'module_name' => 'Help & Support', 
            'menu_id' => 4,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],     [
            'module_name' => 'User Management', 
            'menu_id' => 4,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],     [
            'module_name' => 'Reports', 
            'menu_id' => 4,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],     [
            'module_name' => 'Settings', 
            'menu_id' => 4,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [ 
        'module_name' => 'Bank', 
        'menu_id'=> 3,  
        'created_at' => now(),
        'updated_at' => now(),
        ],
         [
            'module_name' => 'Official Business', 
            'menu_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'module_name' => 'Assets Management', 
            'menu_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
    ]); 
    }
}
