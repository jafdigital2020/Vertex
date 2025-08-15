<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MenuModuleSubmoduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('menu')->insert([
        [
            'menu_name' => 'REPORTS',  
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
      ]);
         DB::table('modules')->insert([
        [
            'module_name' => 'Reports', 
            'menu_id' => 5,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
    ]);
         DB::table('sub_modules')->insert([
        [  
            'id' => 54,
            'sub_module_name' => 'Payroll Summary Report', 
            'module_id' => 19,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
        [ 
             'id' => 55,
            'sub_module_name' => 'Alphalist Report', 
            'module_id' => 19,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
    ]);
    }
}
