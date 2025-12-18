<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestingSeeder extends Seeder
{
    /**
     * Run the database seeds for testing environment.
     * This creates a complete test setup with sample data.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Testing Seeder...');

        // 1. Create test tenant organization
        $this->seedTenant();
        
        // 2. Create global admin user
        $this->seedGlobalUser();
        
        // Note: Skipping complex subscriptions, plans and mobile licenses for basic testing
        // Focus on core login functionality
        $this->command->info('Skipping subscription/mobile license setup for basic testing');

        $this->command->info('✅ Testing Seeder completed successfully!');
        $this->command->info('🔑 Login Credentials:');
        $this->command->info('   Email: admin@test.com');
        $this->command->info('   Password: password123');
        $this->command->info('   Organization: Test Company');
    }

    private function seedTenant(): void
    {
        $this->command->info('Creating test tenant...');
        
        // Check if tenant already exists
        $existingTenant = DB::table('tenants')->where('tenant_code', 'TEST001')->first();
        if ($existingTenant) {
            $this->command->info('Test tenant already exists, skipping...');
            return;
        }
        
        $tenantId = DB::table('tenants')->insertGetId([
            'tenant_name' => 'Test Company',
            'tenant_code' => 'TEST001',
            'tenant_url' => 'test.timora.ph',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Create tenant domain mapping
        DB::table('domains')->insert([
            'domain' => 'test.timora.ph',
            'tenant_id' => $tenantId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    private function seedGlobalUser(): void
    {
        $this->command->info('Creating global admin user...');
        
        // Check if user already exists
        $existingUser = DB::table('global_users')->where('email', 'admin@test.com')->first();
        if ($existingUser) {
            $this->command->info('Test global user already exists, skipping...');
            return;
        }
        
        // Get the tenant we just created
        $tenant = DB::table('tenants')->where('tenant_code', 'TEST001')->first();
        
        DB::table('global_users')->insert([
            'username' => 'testadmin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'tenant_id' => $tenant->id,
            'global_role_id' => 1, // Assuming 1 is admin role
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    private function seedPlans(): void
    {
        $this->command->info('Creating subscription plans...');
        
        $plans = [
            [
                'id' => 1,
                'plan_name' => 'Free Plan',
                'plan_description' => 'Basic features for small teams',
                'plan_price_monthly' => 0,
                'plan_price_yearly' => 0,
                'max_employees' => 5,
                'base_license_count' => 1,
                'implementation_fee' => 0,
                'features' => json_encode(['basic_hr', 'attendance', 'leave']),
                'status' => 'active',
            ],
            [
                'id' => 2,
                'plan_name' => 'Starter Plan',
                'plan_description' => 'Perfect for growing businesses',
                'plan_price_monthly' => 299,
                'plan_price_yearly' => 2990,
                'max_employees' => 25,
                'base_license_count' => 5,
                'implementation_fee' => 500,
                'features' => json_encode(['basic_hr', 'attendance', 'leave', 'payroll']),
                'status' => 'active',
            ],
            [
                'id' => 3,
                'plan_name' => 'Pro Plan',
                'plan_description' => 'Advanced features for medium companies',
                'plan_price_monthly' => 599,
                'plan_price_yearly' => 5990,
                'max_employees' => 100,
                'base_license_count' => 10,
                'implementation_fee' => 1000,
                'features' => json_encode(['basic_hr', 'attendance', 'leave', 'payroll', 'reports', 'mobile_access']),
                'status' => 'active',
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('plans')->updateOrInsert(
                ['id' => $plan['id']], 
                array_merge($plan, [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ])
            );
        }
    }

    private function seedSubscription(): void
    {
        $this->command->info('Creating test subscription...');
        
        // Get the tenant we just created
        $tenant = DB::table('tenants')->where('tenant_code', 'TEST001')->first();
        
        // Create subscription
        DB::table('subscriptions')->insert([
            'tenant_id' => $tenant->id,
            'plan_id' => 2, // Starter Plan
            'billing_cycle' => 'monthly',
            'amount_paid' => 299.00,
            'active_license' => 5,
            'implementation_fee' => 500.00,
            'implementation_fee_paid' => 0.00,
            'subscription_start' => Carbon::now()->format('Y-m-d'),
            'subscription_end' => Carbon::now()->addMonth()->subDay()->format('Y-m-d'),
            'next_renewal_date' => Carbon::now()->addMonth()->format('Y-m-d'),
            'status' => 'active',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Skip invoice creation for now - complex table structure
        // Focus on basic functionality
    }

    private function seedMobileAccessLicenses(): void
    {
        $this->command->info('Creating mobile access license pool...');
        
        // Get the tenant we just created
        $tenant = DB::table('tenants')->where('tenant_code', 'TEST001')->first();
        
        // Create mobile access license pool
        DB::table('mobile_access_licenses')->insert([
            'tenant_id' => $tenant->id,
            'total_licenses' => 10,
            'used_licenses' => 2,
            'price_per_license' => 49.00,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Note: Mobile access assignments will be created when you assign licenses to users
        // through the UI or when you create tenant users
    }
}