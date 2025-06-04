<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CRUDTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          DB::table('crud')->insert([
        [
            'control_name' => 'Create',  
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'control_name' => 'Read',  
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'control_name' => 'Update',  
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'control_name' => 'Delete',  
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'control_name' => 'Import',  
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'control_name' => 'Export',  
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        
    ]);
    }
}
