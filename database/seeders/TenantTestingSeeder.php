<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TenantTestingSeeder extends Seeder
{
    /**
     * Run the database seeds for tenant-specific testing data.
     * This should be run in tenant context to create sample employees and HR data.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Tenant Testing Seeder...');

        // 1. Create roles and permissions
        $this->seedRolesAndPermissions();
        
        // 2. Create departments and designations
        $this->seedDepartmentsAndDesignations();
        
        // 3. Create branches
        $this->seedBranches();
        
        // 4. Create sample users (employees)
        $this->seedUsers();
        
        // 5. Create mobile access assignments
        $this->seedMobileAccessAssignments();

        $this->command->info('✅ Tenant Testing Seeder completed successfully!');
        $this->command->info('📊 Sample Data Created:');
        $this->command->info('   • 3 Departments (HR, IT, Sales)');
        $this->command->info('   • 5 Sample Employees');
        $this->command->info('   • 2 Mobile Access Assignments');
        $this->command->info('   • Complete HR structure');
    }

    private function seedRolesAndPermissions(): void
    {
        $this->command->info('Creating roles and permissions...');
        
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $hrRole = Role::create(['name' => 'hr']);
        $employeeRole = Role::create(['name' => 'employee']);
        
        // Create some basic permissions
        $permissions = [
            'view-employees',
            'create-employees', 
            'edit-employees',
            'delete-employees',
            'view-attendance',
            'manage-leaves',
            'view-payroll',
            'manage-mobile-access',
        ];
        
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        
        // Assign permissions to roles
        $adminRole->givePermissionTo($permissions);
        $hrRole->givePermissionTo(['view-employees', 'create-employees', 'edit-employees', 'view-attendance', 'manage-leaves']);
        $employeeRole->givePermissionTo(['view-attendance']);
    }

    private function seedDepartmentsAndDesignations(): void
    {
        $this->command->info('Creating departments and designations...');
        
        // Create departments
        $departments = [
            ['id' => 1, 'department_name' => 'Human Resources', 'department_head' => null, 'status' => 'active'],
            ['id' => 2, 'department_name' => 'Information Technology', 'department_head' => null, 'status' => 'active'],
            ['id' => 3, 'department_name' => 'Sales & Marketing', 'department_head' => null, 'status' => 'active'],
        ];
        
        foreach ($departments as $dept) {
            DB::table('departments')->insert(array_merge($dept, [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]));
        }
        
        // Create designations
        $designations = [
            ['id' => 1, 'designation_name' => 'HR Manager', 'department_id' => 1],
            ['id' => 2, 'designation_name' => 'HR Assistant', 'department_id' => 1],
            ['id' => 3, 'designation_name' => 'IT Manager', 'department_id' => 2],
            ['id' => 4, 'designation_name' => 'Software Developer', 'department_id' => 2],
            ['id' => 5, 'designation_name' => 'Sales Manager', 'department_id' => 3],
            ['id' => 6, 'designation_name' => 'Sales Representative', 'department_id' => 3],
        ];
        
        foreach ($designations as $designation) {
            DB::table('designations')->insert(array_merge($designation, [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]));
        }
    }

    private function seedBranches(): void
    {
        $this->command->info('Creating branches...');
        
        DB::table('branches')->insert([
            'id' => 1,
            'branch_name' => 'Main Office',
            'branch_address' => '123 Business District, Manila',
            'branch_contact' => '+63 2 1234 5678',
            'status' => 'active',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    private function seedUsers(): void
    {
        $this->command->info('Creating sample employees...');
        
        $users = [
            [
                'id' => 1,
                'username' => 'admin.user',
                'email' => 'admin@testcompany.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ],
            [
                'id' => 2, 
                'username' => 'john.doe',
                'email' => 'john.doe@testcompany.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
            ],
            [
                'id' => 3,
                'username' => 'jane.smith',
                'email' => 'jane.smith@testcompany.com', 
                'password' => Hash::make('password123'),
                'role' => 'hr',
            ],
            [
                'id' => 4,
                'username' => 'mike.johnson',
                'email' => 'mike.johnson@testcompany.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
            ],
            [
                'id' => 5,
                'username' => 'sarah.wilson',
                'email' => 'sarah.wilson@testcompany.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
            ],
        ];
        
        foreach ($users as $userData) {
            // Create user
            $userId = DB::table('users')->insertGetId([
                'username' => $userData['username'],
                'email' => $userData['email'],
                'email_verified_at' => Carbon::now(),
                'password' => $userData['password'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            // Create personal information
            $personalInfo = [
                [1, 'Admin', 'User', 'Admin User', 'Male', '1990-01-15', '+63 917 123 4567', 'Manila, Philippines'],
                [2, 'John', 'Doe', 'John Doe', 'Male', '1985-03-20', '+63 917 234 5678', 'Quezon City, Philippines'],
                [3, 'Jane', 'Smith', 'Jane Smith', 'Female', '1988-07-12', '+63 917 345 6789', 'Makati, Philippines'],
                [4, 'Mike', 'Johnson', 'Mike Johnson', 'Male', '1992-09-05', '+63 917 456 7890', 'Taguig, Philippines'],
                [5, 'Sarah', 'Wilson', 'Sarah Wilson', 'Female', '1990-11-28', '+63 917 567 8901', 'Pasig, Philippines'],
            ][$userId - 1];
            
            DB::table('personal_informations')->insert([
                'user_id' => $userId,
                'first_name' => $personalInfo[1],
                'last_name' => $personalInfo[2],
                'full_name' => $personalInfo[3],
                'gender' => $personalInfo[4],
                'date_of_birth' => $personalInfo[5],
                'phone_number' => $personalInfo[6],
                'address' => $personalInfo[7],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            // Create employment details
            $employmentData = [
                [1, 'EMP001', 1, 1, 1, '2023-01-15', 80000, 'active'],
                [2, 'EMP002', 2, 4, 1, '2023-02-01', 45000, 'active'],
                [3, 'EMP003', 1, 1, 1, '2023-01-20', 60000, 'active'],
                [4, 'EMP004', 2, 3, 1, '2023-03-01', 75000, 'active'],
                [5, 'EMP005', 3, 6, 1, '2023-03-15', 35000, 'active'],
            ][$userId - 1];
            
            DB::table('employment_details')->insert([
                'user_id' => $userId,
                'employee_id' => $employmentData[1],
                'department_id' => $employmentData[2],
                'designation_id' => $employmentData[3],
                'branch_id' => $employmentData[4],
                'date_hired' => $employmentData[5],
                'basic_salary' => $employmentData[6],
                'status' => $employmentData[7] === 'active' ? 1 : 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            // Assign role to user
            $user = \App\Models\User::find($userId);
            if ($user) {
                $user->assignRole($userData['role']);
            }
        }
    }

    private function seedMobileAccessAssignments(): void
    {
        $this->command->info('Creating mobile access assignments...');
        
        // Assign mobile access to first 2 employees
        $assignments = [
            [
                'user_id' => 2, // John Doe
                'status' => 'active',
                'assigned_at' => Carbon::now()->subDays(5),
                'revoked_at' => null,
            ],
            [
                'user_id' => 3, // Jane Smith  
                'status' => 'active',
                'assigned_at' => Carbon::now()->subDays(3),
                'revoked_at' => null,
            ],
        ];
        
        foreach ($assignments as $assignment) {
            DB::table('mobile_access_assignments')->insert(array_merge($assignment, [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]));
        }
    }
}