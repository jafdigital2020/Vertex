<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

/**
 * @deprecated This seeder is deprecated. Use RecruitmentMenuModuleSeeder instead.
 * This class is kept for backward compatibility with existing databases.
 */
class RecruitmentModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if menu exists, create if not
        $existingMenu = DB::table('menu')->where('menu_name', 'RECRUITMENT')->first();
        if (!$existingMenu) {
            DB::table('menu')->insert([
                [
                    'id' => 7,
                    'menu_name' => 'RECRUITMENT',  
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],   
            ]);
        }

        // Check if module exists, create if not
        $existingModule = DB::table('modules')->where('module_name', 'Recruitment')->first();
        if (!$existingModule) {
            // Add Recruitment module
        DB::table('modules')->insert([
            [
                'id' => 21,
                'module_name' => 'Recruitment', 
                'menu_id' => 7,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

            // Add Recruitment sub-modules
            DB::table('sub_modules')->insert([
                [  
                    'id' => 58,
                    'sub_module_name' => 'Job Postings', 
                    'module_id' => 21,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],   
                [ 
                    'id' => 59,
                    'sub_module_name' => 'Candidates', 
                    'module_id' => 21,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [ 
                    'id' => 60,
                    'sub_module_name' => 'Job Applications', 
                    'module_id' => 21,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [ 
                    'id' => 61,
                    'sub_module_name' => 'Interviews', 
                    'module_id' => 21,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [ 
                    'id' => 62,
                    'sub_module_name' => 'Job Offers', 
                    'module_id' => 21,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [ 
                    'id' => 63,
                    'sub_module_name' => 'Manpower Requests', 
                    'module_id' => 21,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
        }
    }
}