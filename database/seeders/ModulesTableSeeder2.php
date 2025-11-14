<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ModulesTableSeeder2 extends Seeder
{
   
    public function run(): void
    {
        DB::table('modules')->insert([
        [
            'module_name' => 'Suspension', 
            'menu_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ], 
    ]); 
    }
}
