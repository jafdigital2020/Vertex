<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubmoduleTableSeeder3 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('sub_modules')->insert([
        [
            'sub_module_name' => 'SSS Reports', 
            'module_id' => 19,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
    ]);
    }
}
