<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubModuleTableSeeder9 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sub_modules')->insert([
            [
                'id' => 63,
                'sub_module_name' => 'Contract Templates',
                'module_id' => 15, // Settings Module
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 64,
                'sub_module_name' => 'Employee Contracts',
                'module_id' => 4, // Employees Module
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
