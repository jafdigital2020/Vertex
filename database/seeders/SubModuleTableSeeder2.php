<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubModuleTableSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
           DB::table('sub_modules')->insert([
        [   
             'id' => 53,
            'sub_module_name' => 'Employee Salary Record', 
            'module_id' => 4,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
    ]);
    
    }
}
