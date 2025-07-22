<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; 
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Insert rows into 'module' table
         DB::table('modules')->insert([
        [
            'module_name' => 'Assets Management', 
            'menu_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],]); 
          DB::table('sub_modules')->insert([
        [
            'sub_module_name' => 'Employee Assets', 
            'module_id' => 18,
            'order_no' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
        [
            'sub_module_name' => 'Assets Settings', 
            'module_id' => 18,
            'order_no' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        ]);
       
    }

    public function down(): void
    {
     
    }
};
