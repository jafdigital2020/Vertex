<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecruitmentMenuModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if menu exists, create if not
        $existingMenu = DB::table('menu')->where('menu_name', 'RECRUITMENT')->first();
        if (!$existingMenu) {
            $menuId = DB::table('menu')->insertGetId([
                'menu_name' => 'RECRUITMENT',  
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            $menuId = $existingMenu->id;
        }

        // Check if module exists, create if not
        $existingModule = DB::table('modules')->where('module_name', 'Recruitment')->first();
        if (!$existingModule) {
            $moduleId = DB::table('modules')->insertGetId([
                'module_name' => 'Recruitment', 
                'menu_id' => $menuId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Add Recruitment sub-modules
            DB::table('sub_modules')->insert([
                [  
                    'sub_module_name' => 'Job Postings', 
                    'module_id' => $moduleId,
                    'order_no' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],   
                [ 
                    'sub_module_name' => 'Candidates', 
                    'module_id' => $moduleId,
                    'order_no' => 2,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [ 
                    'sub_module_name' => 'Job Applications', 
                    'module_id' => $moduleId,
                    'order_no' => 3,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [ 
                    'sub_module_name' => 'Interviews', 
                    'module_id' => $moduleId,
                    'order_no' => 4,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [ 
                    'sub_module_name' => 'Job Offers', 
                    'module_id' => $moduleId,
                    'order_no' => 5,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [ 
                    'sub_module_name' => 'Manpower Requests', 
                    'module_id' => $moduleId,
                    'order_no' => 6,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
        }
    }
}