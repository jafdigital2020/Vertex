<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseTestingSeeder extends Seeder
{
    /**
     * Seed the application's database for testing.
     * This is the main seeder that orchestrates the complete testing setup.
     */
    public function run(): void
    {
        $this->command->info('🎯 Starting Complete Testing Database Setup...');
        $this->command->info('════════════════════════════════════════════');

        // 1. Seed central/global data
        $this->command->info('📋 Step 1: Seeding Central Database...');
        $this->call(TestingSeeder::class);

        $this->command->info('');
        $this->command->info('📋 Step 2: Switching to Tenant Database...');
        
        // 2. Switch to tenant context and seed tenant data
        try {
            // Initialize tenant by tenant code instead of ID
            $tenant = \App\Models\Tenant::where('tenant_code', 'TEST001')->first();
            if (!$tenant) {
                $this->command->error('❌ Test tenant not found! Make sure TestingSeeder ran successfully.');
                return;
            }

            $tenant->run(function () {
                $this->command->info('🏢 Now seeding tenant-specific data...');
                $this->call(TenantTestingSeeder::class);
            });

        } catch (\Exception $e) {
            $this->command->error('❌ Error seeding tenant data: ' . $e->getMessage());
            return;
        }

        $this->command->info('');
        $this->command->info('🎉 TESTING SETUP COMPLETE!');
        $this->command->info('════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('📍 ACCESS INFORMATION:');
        $this->command->info('   🌐 URL: https://test.timora.ph (or your local domain)');
        $this->command->info('   📧 Email: admin@test.com');
        $this->command->info('   🔑 Password: password123');
        $this->command->info('   🏢 Company: Test Company');
        $this->command->info('   💼 Company Code: TEST001');
        $this->command->info('');
        $this->command->info('👥 SAMPLE EMPLOYEES:');
        $this->command->info('   • admin.user - Admin User (admin@testcompany.com)');
        $this->command->info('   • john.doe - John Doe (john.doe@testcompany.com)');
        $this->command->info('   • jane.smith - Jane Smith (jane.smith@testcompany.com) [HR]');
        $this->command->info('   • mike.johnson - Mike Johnson (mike.johnson@testcompany.com)');
        $this->command->info('   • sarah.wilson - Sarah Wilson (sarah.wilson@testcompany.com)');
        $this->command->info('');
        $this->command->info('📱 MOBILE ACCESS:');
        $this->command->info('   • John Doe and Jane Smith have mobile access');
        $this->command->info('   • 8 licenses remaining (10 total, 2 used)');
        $this->command->info('');
        $this->command->info('💡 All sample employees use password: password123');
        $this->command->info('════════════════════════════════════════════');
    }
}