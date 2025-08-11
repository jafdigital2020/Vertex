<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CRUDTableSeeder::class,
            DataAccessLevelTableSeeder::class,
            DeminimisBenefits::class,
            GlobalRoleTableSeeder::class,
            GlobalUserTableSeeder::class,
            MenuTableSeeder::class,
            ModulesTableSeeder::class,
            OtTable::class,
            PhilhealthContribution::class,
            RoleTableSeeder::class,
            SssContribution::class,
            SssContribution2023::class,
            SubModuleOrderNoSeeder::class,
            SubModulesTableSeeder::class,
            SubModuleTableSeeder2::class,
            TenantTableSeeder::class,
            UserPermissionTableSeeder::class,
            WithholdingTaxSeeder::class,
            HolidaySeeder::class,
            LeaveTypeSeeder::class,
            MenuModuleSubmoduleSeeder::class,
            SubmoduleTableSeeder3::class,
        ]);
    }
}
