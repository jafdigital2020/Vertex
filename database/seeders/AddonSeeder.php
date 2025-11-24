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
                'description' => 'Manage employee allowances and benefits.',
                'module_ids' => '4',
                'submodule_ids' => '53',
                'is_active' => true,
                'features' => json_encode([
                    [
                        'title' => 'Custom allowance types',
                        'tooltip' => 'Set up different types of allowances.'
                    ],
                    [
                        'title' => 'Employee allowance assignment',
                        'tooltip' => 'Easily assign specific allowances to individual employees.'
                    ],
                    [
                        'title' => 'Automatic payroll integration',
                        'tooltip' => 'Include allowances automatically in payroll.'
                    ],
                    [
                        'title' => 'Allowance history tracking',
                        'tooltip' => 'Track and review all past allowances for reference and reporting.'
                    ]
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
                'description' => 'Add on for managing employee official business requests.',
                'is_active' => true,
                'module_ids' => '17',
                'submodule_ids' => '47,48',
                'features' => json_encode([
                    [
                        'title' => 'Track employee business trips',
                        'tooltip' => 'Easily monitor where employees go for official business.'
                    ],
                    [
                        'title' => 'Manage travel requests',
                        'tooltip' => 'Let employees submit and update their travel requests in one place.'
                    ],
                    [
                        'title' => 'Automated approval workflow',
                        'tooltip' => 'Approve requests faster with automatic routing to the right approvers.'
                    ],
                    [
                        'title' => 'Expense tracking integration',
                        'tooltip' => 'Connect travel expenses for smooth and accurate reporting.'
                    ]
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
                'description' => 'Export payroll data into CSV format for banks.',
                'is_active' => true,
                'module_ids' => '16',
                'submodule_ids' => '46',
                'features' => json_encode([
                    [
                        'title' => 'Multi-bank format export',
                        'tooltip' => 'Export payroll files that work with different banks.'
                    ],
                    [
                        'title' => 'Automated file generation',
                        'tooltip' => 'Create required files automatically.'
                    ],
                    [
                        'title' => 'Secure data export',
                        'tooltip' => 'Safely export files with protected and encrypted data.'
                    ],
                    [
                        'title' => 'Custom field mapping',
                        'tooltip' => 'Match and arrange fields based on your bank\'s required format.'
                    ]
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
                'description' => 'Set and manage your holiday pay schedules.',
                'module_ids' => '5',
                'submodule_ids' => '13',
                'is_active' => true,
                'features' => json_encode([
                    [
                        'title' => 'Custom holiday pay creation',
                        'tooltip' => 'Create your own holiday pay rules.'
                    ],
                    [
                        'title' => 'Branch-specific holiday pay rates',
                        'tooltip' => 'Set different holiday pay rates for each branch.'
                    ],
                    [
                        'title' => 'Automated pay calculations',
                        'tooltip' => 'Holiday pay is calculated automatically.'
                    ],
                    [
                        'title' => 'Holiday pay calendar sync',
                        'tooltip' => 'Sync holiday pay with your company calendar.'
                    ]
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
                'description' => 'Generate BIR Alphalist reports for tax compliance.',
                'module_ids' => '19',
                'submodule_ids' => '55',
                'is_active' => true,
                'features' => json_encode([
                    [
                        'title' => 'BIR-compliant alphalist generation',
                        'tooltip' => 'Generate employee alphalists that meet BIR requirements.'
                    ],
                    [
                        'title' => 'Excel export format',
                        'tooltip' => 'Export reports easily in Excel for convenience and sharing.'
                    ],
                    [
                        'title' => 'Year-end tax reporting',
                        'tooltip' => 'Prepare accurate tax reports for year-end filing.'
                    ],
                    [
                        'title' => 'Employee tax summary',
                        'tooltip' => 'View a complete summary of each employee\'s taxes.'
                    ]
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
                'description' => 'Generate SSS contribution reports and remittance forms.',
                'module_ids' => '19',
                'submodule_ids' => '56',
                'is_active' => true,
                'features' => json_encode([
                    [
                        'title' => 'SSS R3 report generation',
                        'tooltip' => 'Generate official SSS R3 reports quickly and accurately.'
                    ],
                    [
                        'title' => 'Monthly contribution reports',
                        'tooltip' => 'Track and review monthly SSS contributions for all employees.'
                    ],
                    [
                        'title' => 'SSS loan reports',
                        'tooltip' => 'Access detailed reports on employee SSS loans.'
                    ],
                    [
                        'title' => 'Automated remittance forms',
                        'tooltip' => 'Automatically prepare SSS remittance forms for submission.'
                    ]
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
                'description' => 'ZKTeco biometric device hardware with integration setup.',
                'module_ids' => '6',
                'submodule_ids' => '14,18',
                'is_active' => true,
                'features' => json_encode([
                    [
                        'title' => 'ZKTeco biometric device hardware',
                        'tooltip' => 'Supply and install ZKTeco biometric devices.'
                    ],
                    [
                        'title' => 'Complete integration setup',
                        'tooltip' => 'Fully integrate the device with your HR/payroll system.'
                    ],
                    [
                        'title' => 'Employee biometric enrollment',
                        'tooltip' => 'Register employees\' fingerprints or facial data.'
                    ],
                    [
                        'title' => 'Device configuration',
                        'tooltip' => 'Configure device settings for your organization\'s needs.'
                    ],
                    [
                        'title' => 'Initial training and support',
                        'tooltip' => 'Provide training and support for smooth device use.'
                    ]
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
                'description' => 'Professional biometric device installation service.',
                'module_ids' => '6',
                'submodule_ids' => '14,18',
                'is_active' => true,
                'features' => json_encode([
                    [
                        'title' => 'Professional on-site installation',
                        'tooltip' => 'Expert setup of your device at your location.'
                    ],
                    [
                        'title' => 'Device configuration and testing',
                        'tooltip' => 'Configure settings and ensure the device works properly.'
                    ],
                    [
                        'title' => 'Network setup and connectivity',
                        'tooltip' => 'Connect the device to your network for smooth operation.'
                    ],
                    [
                        'title' => 'Staff training on device usage',
                        'tooltip' => 'Guide your team to use the device easily and correctly.'
                    ],
                    [
                        'title' => 'Installation warranty',
                        'tooltip' => 'Coverage and support after installation.'
                    ]
                ]),
                'icon' => 'tools',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'addon_key' => 'policies_management',
                'name' => 'Policies Management',
                'price' => 1200.00,
                'type' => 'monthly',
                'addon_category' => 'upgrade',
                'description' => 'Advanced policy management with company-wide distribution',
                'module_ids' => '4',
                'submodule_ids' => '12',
                'is_active' => true,
                'features' => json_encode([
                    'Company-wide policy distribution',
                    'Policy version control',
                    'Employee acknowledgment tracking',
                    'Policy templates',
                    'Automated notifications'
                ]),
                'icon' => 'file-check',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'addon_key' => 'advanced_approval_settings',
                'name' => 'Advanced Approval Settings',
                'price' => 800.00,
                'type' => 'monthly',
                'addon_category' => 'upgrade',
                'description' => 'Support for more than 2 approvers in approval workflows',
                'module_ids' => '6',
                'submodule_ids' => '18',
                'is_active' => true,
                'features' => json_encode([
                    'Unlimited approval levels',
                    'Multi-tier approval workflows',
                    'Custom approval chains',
                    'Conditional approval routing',
                    'Approval delegation'
                ]),
                'icon' => 'users-check',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'addon_key' => 'assets_management_upgrade',
                'name' => 'Assets Management (Full Access)',
                'price' => 2200.00,
                'type' => 'monthly',
                'addon_category' => 'upgrade',
                'description' => 'Complete asset management system for admin and employees',
                'module_ids' => '18',
                'submodule_ids' => '49,50',
                'is_active' => true,
                'features' => json_encode([
                    'Employee asset management',
                    'Asset settings configuration',
                    'Complete asset lifecycle tracking',
                    'Asset assignment & return',
                    'Depreciation tracking',
                    'Maintenance scheduling',
                    'Asset audit reports'
                ]),
                'icon' => 'box-check',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
