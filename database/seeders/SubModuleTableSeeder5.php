<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubModuleTableSeeder5 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
     
        DB::table('sub_modules')->insert([
        [   
            'id' => 59,
            'sub_module_name' => 'Resignation Settings', 
            'module_id' => 8,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
    ]);
    }
}
