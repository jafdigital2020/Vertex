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
            ModulesTableSeeder2::class,
            OTTable::class,
            PhilhealthContribution::class,
            RoleTableSeeder::class,
            SssContribution::class,
            SssContribution2023::class,
            SubModulesTableSeeder::class,
            SubModuleTableSeeder2::class,
            SubModuleOrderNoSeeder::class,
            SubModuleTableSeeder3::class,
            SubModuleTableSeeder4::class,
            SubModuleTableSeeder5::class,
            ModulesTableSeeder2::class,
            SubModuleTableSeeder6::class,
            SubModuleTableSeeder7::class,
            SubModuleTableSeeder8::class,
            SubModuleTableSeeder9::class,
            TenantTableSeeder::class,
            UserPermissionTableSeeder::class,
            WithholdingTaxSeeder::class,
            ViolationTypeSeeder::class

        ]);
    }
}
