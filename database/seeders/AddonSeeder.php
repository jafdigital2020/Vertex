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
                'module_ids' => '17',
                'submodule_ids' => '47,48',
                'features' => json_encode([
                    'Track employee business trips',
                    'Manage travel requests',
                    'Automated approval workflow',
                    'Expense tracking integration'
                ]),
                'icon' => 'briefcase',
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
                'module_ids' => '18',
                'submodule_ids' => '49,50',
                'features' => json_encode([
                    'Complete asset inventory',
                    'Asset assignment tracking',
                    'Maintenance scheduling',
                    'Depreciation calculations',
                    'Asset history logs'
                ]),
                'icon' => 'box',
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
                'module_ids' => '16',
                'submodule_ids' => '46',
                'features' => json_encode([
                    'Multi-bank format support',
                    'Automated file generation',
                    'Secure data export',
                    'Custom field mapping'
                ]),
                'icon' => 'building-bank',
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
                'module_ids' => '10',
                'submodule_ids' => '51,52',
                'features' => json_encode([
                    'Bulk payroll processing',
                    'Multiple batch support',
                    'Automated calculations',
                    'Error detection & reporting'
                ]),
                'icon' => 'calculator',
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
                'module_ids' => '4',
                'submodule_ids' => '12',
                'features' => json_encode([
                    'Upload company policies',
                    'Version control',
                    'Employee acknowledgment tracking',
                    'Document organization'
                ]),
                'icon' => 'file-text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'addon_key' => 'custom_holiday',
                'name' => 'Custom Holiday',
                'price' => 800.00,
                'type' => 'monthly',
                'description' => 'Create and manage custom holiday schedules',
                'module_ids' => '5',
                'submodule_ids' => '13',
                'is_active' => true,
                'features' => json_encode([
                    'Custom holiday creation',
                    'Branch-specific holidays',
                    'Automated leave calculations',
                    'Holiday calendar sync'
                ]),
                'icon' => 'calendar-event',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
