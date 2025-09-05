<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('addons')->insert([
            [
                'addon_key' => 'employee_official_business',
                'name' => 'Employee Official Business',
                'price' => 1200.00,
                'type' => 'monthly',
                'description' => 'Addon for managing employee official business requests',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'addon_key' => 'asset_management_tracking',
                'name' => 'Asset Management Tracking',
                'price' => 1900.00,
                'type' => 'monthly',
                'description' => 'Track and manage company assets efficiently',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'addon_key' => 'bank_data_export_csv',
                'name' => 'Bank Data Export (CSV)',
                'price' => 1000.00,
                'type' => 'monthly',
                'description' => 'Export payroll data into CSV format for banks',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'addon_key' => 'payroll_batch_processing',
                'name' => 'Payroll Batch Processing',
                'price' => 600.00,
                'type' => 'monthly',
                'description' => 'Process multiple payroll batches at once',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'addon_key' => 'policy_upload',
                'name' => 'Policy Upload',
                'price' => 500.00,
                'type' => 'monthly',
                'description' => 'Upload and manage HR policies in the system',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'addon_key' => 'custom_holiday',
                'name' => 'Custom Holiday',
                'price' => 800.00,
                'type' => 'monthly',
                'description' => 'Create and manage custom holiday schedules',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    
    }
}
