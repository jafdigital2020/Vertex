<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          DB::table('role')->insert([
        [
            'role_name' => 'Admin', 
            'menu_ids' => null,
            'module_ids'=> null,
            'role_permission_ids'=> null,
            'status'=> 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
        [
            'role_name' => 'Employee', 
            'menu_ids' => null,
            'module_ids'=> null,
            'role_permission_ids'=> null,
            'status'=> 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
            [
            'role_name' => 'HR', 
            'menu_ids' => null,
            'module_ids'=> null,
            'role_permission_ids'=> null,
            'status'=> 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ], 
    ]);
    }
}
