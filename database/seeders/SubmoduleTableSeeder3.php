<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubmoduleTableSeeder3 extends Seeder
{
    
    public function run(): void
    {
           DB::table('sub_modules')->insert([
        [   
            'id' => 57,
            'sub_module_name' => 'Inactive List', 
            'module_id' => 4,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
    ]);
}
}
