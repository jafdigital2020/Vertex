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
        // Clear existing addons to avoid duplicates
        DB::table('addons')->truncate();

        DB::table('addons')->insert([
            [
                'addon_key' => 'allowance_management',
                'name' => 'Allowance Management',
                'price' => 1500.00,
                'type' => 'monthly',
                'addon_category' => 'addon',
                'description' => 'Manage employee allowances and benefits',
                'module_ids' => '4',
                'submodule_ids' => '53',
                'is_active' => true,
                'features' => json_encode([
                    'Custom allowance types',
                    'Employee allowance assignment',
                    'Automatic payroll integration',
                    'Allowance history tracking'
                ]),
                'icon' => 'cash',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'addon_key' => 'employee_official_business',
                'name' => 'Employee Official Business',
                'price' => 1200.00,
                'type' => 'monthly',
                'addon_category' => 'addon',
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
                'addon_key' => 'bank_data_export_csv',
                'name' => 'Bank Data Export (CSV)',
                'price' => 1000.00,
                'type' => 'monthly',
                'addon_category' => 'addon',
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
                'addon_key' => 'custom_holiday_pay',
                'name' => 'Custom Holiday Pay',
                'price' => 800.00,
                'type' => 'monthly',
                'addon_category' => 'addon',
                'description' => 'Create and manage custom holiday pay schedules',
                'module_ids' => '5',
                'submodule_ids' => '13',
                'is_active' => true,
                'features' => json_encode([
                    'Custom holiday pay creation',
                    'Branch-specific holiday pay rates',
                    'Automated pay calculations',
                    'Holiday pay calendar sync'
                ]),
                'icon' => 'calendar-event',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'addon_key' => 'alphalist_report',
                'name' => 'Alphalist Report',
                'price' => 499.00,
                'type' => 'monthly',
                'addon_category' => 'addon',
                'description' => 'Generate BIR Alphalist reports for tax compliance',
                'module_ids' => '19',
                'submodule_ids' => '55',
                'is_active' => true,
                'features' => json_encode([
                    'BIR-compliant alphalist generation',
                    'Excel export format',
                    'Year-end tax reporting',
                    'Employee tax summary'
                ]),
                'icon' => 'report',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'addon_key' => 'sss_report',
                'name' => 'SSS Report',
                'price' => 499.00,
                'type' => 'monthly',
                'addon_category' => 'addon',
                'description' => 'Generate SSS contribution reports and remittance forms',
                'module_ids' => '19',
                'submodule_ids' => '56',
                'is_active' => true,
                'features' => json_encode([
                    'SSS R3 report generation',
                    'Monthly contribution reports',
                    'SSS loan reports',
                    'Automated remittance forms'
                ]),
                'icon' => 'file-spreadsheet',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'addon_key' => 'zkteco_biometric_device',
                'name' => 'ZKTeco Biometric Device + Integration',
                'price' => 23216.00,
                'type' => 'one_time',
                'addon_category' => 'addon',
                'description' => 'ZKTeco biometric device hardware with integration setup',
                'module_ids' => '6',
                'submodule_ids' => '14,18',
                'is_active' => true,
                'features' => json_encode([
                    'ZKTeco biometric device hardware',
                    'Complete integration setup',
                    'Employee biometric enrollment',
                    'Device configuration',
                    'Initial training and support'
                ]),
                'icon' => 'device-fingerprint',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'addon_key' => 'biometric_installation',
                'name' => 'Biometric Installation',
                'price' => 10000.00,
                'type' => 'one_time',
                'addon_category' => 'addon',
                'description' => 'Professional biometric device installation service',
                'module_ids' => '6',
                'submodule_ids' => '14,18',
                'is_active' => true,
                'features' => json_encode([
                    'Professional on-site installation',
                    'Device configuration and testing',
                    'Network setup and connectivity',
                    'Staff training on device usage',
                    'Installation warranty'
                ]),
                'icon' => 'tools',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
