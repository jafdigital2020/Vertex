<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubModuleTableSeeder4 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
           DB::table('sub_modules')->insert([
        [   
            'id' => 58,
            'sub_module_name' => 'Resignation Employee', 
            'module_id' => 8,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
    ]);
    }
}
