<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GlobalRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('global_role')->insert([
        [
            'global_role_name' => 'super_admin',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
         [
            'global_role_name' => 'tenant_admin',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
    ]);
    }
}
