<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequestModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder adds the Request module and its submodules to the permission system.
     */
    public function run(): void
    {
        // First, insert the Request module under HRM menu (menu_id = 2)
        $moduleId = DB::table('modules')->insertGetId([
            'module_name' => 'Requests',
            'menu_id' => 2, // HRM menu
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Insert submodules for each request type
        DB::table('sub_modules')->insert([
            [
                'sub_module_name' => 'Loan Requests (Employee)',
                'module_id' => $moduleId,
                'order_no' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_module_name' => 'Budget Requests (Employee)',
                'module_id' => $moduleId,
                'order_no' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_module_name' => 'Asset Requests (Employee)',
                'module_id' => $moduleId,
                'order_no' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_module_name' => 'HMO Requests (Employee)',
                'module_id' => $moduleId,
                'order_no' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_module_name' => 'COE Requests (Employee)',
                'module_id' => $moduleId,
                'order_no' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_module_name' => 'Loan Requests (Admin)',
                'module_id' => $moduleId,
                'order_no' => 6,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_module_name' => 'Budget Requests (Admin)',
                'module_id' => $moduleId,
                'order_no' => 7,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_module_name' => 'Asset Requests (Admin)',
                'module_id' => $moduleId,
                'order_no' => 8,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_module_name' => 'HMO Requests (Admin)',
                'module_id' => $moduleId,
                'order_no' => 9,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sub_module_name' => 'COE Requests (Admin)',
                'module_id' => $moduleId,
                'order_no' => 10,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        echo "Request module and submodules created successfully!\n";
        echo "Module ID: {$moduleId}\n";
    }
}
