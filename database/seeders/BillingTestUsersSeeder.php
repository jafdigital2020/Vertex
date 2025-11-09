<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\SalaryDetail;
use App\Models\SalaryRecord;
use Illuminate\Database\Seeder;
use App\Models\UserPermission;
use App\Models\EmploymentDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\EmploymentPersonalInformation;
use App\Models\EmploymentGovernmentId;
use App\Services\LicenseOverageService;

class BillingTestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates 80 test users to test billing functionality:
     * - Users 1-10: Within base Starter plan limit (no charges)
     * - User 11: Triggers implementation fee requirement
     * - Users 12-20: Trigger license overage (₱49 each)
     * - User 21+: Trigger plan upgrade requirement
     */
    public function run(): void
    {
        $licenseOverageService = app(LicenseOverageService::class);

        // Get the authenticated tenant (you may need to adjust this based on your setup)
        $tenantId = $this->getTenantId();

        if (!$tenantId) {
            $this->command->error('❌ No tenant found. Please ensure you have a tenant in your database.');
            return;
        }

        $this->command->info("🏢 Using Tenant ID: {$tenantId}");

        // Get required data
        $branch = Branch::where('tenant_id', $tenantId)->first();
        $department = Department::whereHas('branch', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })->first();
        $designation = Designation::whereHas('department.branch', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })->first();
        $role = Role::where('tenant_id', $tenantId)->first();

        if (!$branch || !$department || !$designation || !$role) {
            $this->command->error('❌ Missing required data (branch, department, designation, or role).');
            $this->command->error('   Please ensure your tenant has at least one of each.');
            return;
        }

        $this->command->info("📋 Using:");
        $this->command->info("   Branch: {$branch->name}");
        $this->command->info("   Department: {$department->department_name}");
        $this->command->info("   Designation: {$designation->designation_name}");
        $this->command->info("   Role: {$role->role_name}");

        // Filipino first names and last names for realistic test data
        $firstNames = [
            'Juan', 'Maria', 'Jose', 'Ana', 'Pedro', 'Rosa', 'Miguel', 'Carmen', 'Antonio', 'Isabel',
            'Luis', 'Teresa', 'Carlos', 'Elena', 'Ramon', 'Sofia', 'Fernando', 'Lucia', 'Ricardo', 'Patricia',
            'Roberto', 'Angela', 'Manuel', 'Diana', 'Jorge', 'Cristina', 'Francisco', 'Laura', 'Daniel', 'Monica',
            'Alejandro', 'Beatriz', 'Rafael', 'Gloria', 'Javier', 'Veronica', 'Eduardo', 'Silvia', 'Pablo', 'Gabriela',
            'Andres', 'Adriana', 'Oscar', 'Claudia', 'Sergio', 'Mariana', 'Victor', 'Fernanda', 'Raul', 'Valeria'
        ];

        $lastNames = [
            'Santos', 'Reyes', 'Cruz', 'Bautista', 'Ocampo', 'Garcia', 'Mendoza', 'Torres', 'Gonzales', 'Lopez',
            'Ramos', 'Flores', 'Rivera', 'Morales', 'Castro', 'Aquino', 'Villanueva', 'Santiago', 'Fernandez', 'Domingo',
            'Marquez', 'Valdez', 'Gutierrez', 'Perez', 'Cortez', 'Soriano', 'Mercado', 'Abad', 'Rosario', 'Diaz',
            'Valencia', 'Aguilar', 'Pascual', 'Romero', 'Navarro', 'Chavez', 'Castillo', 'Hernandez', 'Jimenez', 'Silva',
            'Rojas', 'Ortiz', 'Suarez', 'Vargas', 'Molina', 'Campos', 'Nunez', 'Medina', 'Herrera', 'Luna'
        ];

        $totalUsers = 80;
        $createdCount = 0;
        $errorCount = 0;
        $overageInvoices = 0;

        $this->command->info("\n🚀 Starting to create {$totalUsers} test users...\n");

        DB::beginTransaction();
        try {
            for ($i = 1; $i <= $totalUsers; $i++) {
                try {
                    $firstName = $firstNames[array_rand($firstNames)];
                    $lastName = $lastNames[array_rand($lastNames)];
                    $username = strtolower($firstName . $lastName . $i);
                    $email = strtolower($firstName . '.' . $lastName . $i . '@testbilling.com');
                    $employeeId = 'TEST-' . str_pad($i, 4, '0', STR_PAD_LEFT);

                    // Create user
                    $user = new User();
                    $user->username = $username;
                    $user->tenant_id = $tenantId;
                    $user->email = $email;
                    $user->password = Hash::make('password123'); // Default password for all test users
                    $user->active_license = true;
                    $user->save();

                    // Create user permission
                    $userPermission = new UserPermission();
                    $userPermission->user_id = $user->id;
                    $userPermission->data_access_id = $role->data_access_id;
                    $userPermission->role_id = $role->id;
                    $userPermission->menu_ids = $role->menu_ids;
                    $userPermission->module_ids = $role->module_ids;
                    $userPermission->user_permission_ids = $role->role_permission_ids;
                    $userPermission->status = 1;
                    $userPermission->save();

                    // Create personal information
                    EmploymentPersonalInformation::create([
                        'user_id' => $user->id,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'middle_name' => chr(65 + rand(0, 25)) . '.',
                        'suffix' => null,
                        'phone_number' => '+639' . rand(100000000, 999999999),
                        'gender' => rand(0, 1) ? 'Male' : 'Female',
                        'civil_status' => ['Single', 'Married', 'Widowed'][rand(0, 2)],
                    ]);

                    // Create employment detail
                    EmploymentDetail::create([
                        'user_id' => $user->id,
                        'designation_id' => $designation->id,
                        'department_id' => $department->id,
                        'status' => 1,
                        'date_hired' => Carbon::now()->subDays(rand(1, 365))->format('Y-m-d'),
                        'employee_id' => $employeeId,
                        'employment_type' => ['Full-time', 'Part-time', 'Contract'][rand(0, 2)],
                        'employment_status' => ['Regular', 'Probationary', 'Project-based'][rand(0, 2)],
                        'branch_id' => $branch->id,
                        'biometrics_id' => 'BIO-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    ]);

                    // Create government ID (optional but good for testing)
                    EmploymentGovernmentId::create([
                        'user_id' => $user->id,
                        'sss_number' => rand(10, 99) . '-' . rand(1000000, 9999999) . '-' . rand(0, 9),
                        'philhealth_number' => rand(10, 99) . '-' . rand(100000000, 999999999) . '-' . rand(0, 9),
                        'pagibig_number' => rand(1000, 9999) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999),
                        'tin_number' => rand(100, 999) . '-' . rand(100, 999) . '-' . rand(100, 999) . '-' . rand(000, 999),
                    ]);

                    // Create salary detail
                    $sss = $branch->sss_contribution_type === 'fixed'
                        ? $branch->fixed_sss_amount
                        : ($branch->sss_contribution_type === 'manual' ? 'manual' : 'system');

                    $philhealth = $branch->philhealth_contribution_type === 'fixed'
                        ? $branch->fixed_philhealth_amount
                        : ($branch->philhealth_contribution_type === 'manual' ? 'manual' : 'system');

                    $pagibig = $branch->pagibig_contribution_type === 'fixed'
                        ? $branch->fixed_pagibig_amount
                        : ($branch->pagibig_contribution_type === 'manual' ? 'manual' : 'system');

                    $withholding = $branch->withholding_tax_type === 'fixed'
                        ? $branch->fixed_withholding_tax_amount
                        : ($branch->withholding_tax_type === 'manual' ? 'manual' : 'system');

                    $workedDays = $branch->worked_days_per_year === 'custom'
                        ? $branch->custom_worked_days
                        : $branch->worked_days_per_year ?? null;

                    // Create salary record first
                    $salaryRecord = \App\Models\SalaryRecord::create([
                        'user_id' => $user->id,
                        'basic_salary' => rand(15000, 50000), // Random salary for testing
                        'effective_date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                        'is_active' => true,
                        'created_by_id' => 1, // Assuming admin user ID is 1
                        'created_by_type' => 'App\\Models\\User',
                        'salary_type' => 'monthly_fixed', // Valid enum value
                    ]);

                    // Create salary detail linked to salary record
                    SalaryDetail::create([
                        'user_id' => $user->id,
                        'salary_id' => $salaryRecord->id,
                        'sss_contribution' => $sss,
                        'philhealth_contribution' => $philhealth,
                        'pagibig_contribution' => $pagibig,
                        'withholding_tax' => $withholding,
                        'worked_days_per_year' => $workedDays,
                    ]);

                    // ✅ TRIGGER LICENSE OVERAGE CHECK
                    $overageInvoice = $licenseOverageService->handleEmployeeActivation($user->id);

                    if ($overageInvoice) {
                        $overageInvoices++;
                    }

                    $createdCount++;

                    // Progress indicator
                    if ($i <= 10) {
                        $status = '✅ Within base limit';
                    } elseif ($i == 11) {
                        $status = '⚠️  Implementation fee required';
                    } elseif ($i <= 20) {
                        $status = '💰 License overage (₱49)';
                    } else {
                        $status = '🚀 Plan upgrade needed';
                    }

                    $this->command->line("   [{$i}/{$totalUsers}] {$employeeId} - {$firstName} {$lastName} {$status}");

                } catch (\Exception $e) {
                    $errorCount++;
                    $this->command->error("   ❌ Error creating user {$i}: " . $e->getMessage());
                    Log::error("BillingTestUsersSeeder: Error creating user {$i}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            DB::commit();

            // Summary
            $this->command->info("\n" . str_repeat('=', 70));
            $this->command->info("✅ SEEDER COMPLETED SUCCESSFULLY!");
            $this->command->info(str_repeat('=', 70));
            $this->command->info("📊 Summary:");
            $this->command->info("   • Total users created: {$createdCount}/{$totalUsers}");
            $this->command->info("   • License overage invoices: {$overageInvoices}");
            $this->command->info("   • Errors: {$errorCount}");
            $this->command->info("\n🧪 Test Scenarios Created:");
            $this->command->info("   • Users 1-10:   Within Starter plan base limit (10 users)");
            $this->command->info("   • User 11:      Implementation fee required (₱2,000)");
            $this->command->info("   • Users 12-20:  License overage (₱49 per user)");
            $this->command->info("   • Users 21-80:  Plan upgrade required (to Core/Pro/Elite)");
            $this->command->info("\n🔐 Login Credentials:");
            $this->command->info("   Username format: firstname + lastname + number");
            $this->command->info("   Example: juansantos1, mariareyes2, etc.");
            $this->command->info("   Password: password123 (for all test users)");
            $this->command->info("\n💡 Next Steps:");
            $this->command->info("   1. Check /billing page for generated invoices");
            $this->command->info("   2. Test implementation fee payment flow (user 11)");
            $this->command->info("   3. Test license overage invoices (users 12-20)");
            $this->command->info("   4. Test plan upgrade flow (users 21+)");
            $this->command->info("   5. Run: php artisan invoices:generate (to test renewal)");
            $this->command->info(str_repeat('=', 70) . "\n");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("\n❌ SEEDER FAILED!");
            $this->command->error("Error: " . $e->getMessage());
            Log::error("BillingTestUsersSeeder: Transaction failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get the tenant ID for seeding.
     * Adjust this method based on your application's needs.
     */
    private function getTenantId()
    {
        // Option 1: Get the first available tenant
        $tenant = DB::table('tenants')->first();

        if ($tenant) {
            return $tenant->id;
        }

        // Option 2: You can hardcode a specific tenant ID for testing
        // return 1;

        // Option 3: Prompt for tenant ID
        // $tenantId = $this->command->ask('Enter tenant ID');
        // return $tenantId;

        return null;
    }
}
