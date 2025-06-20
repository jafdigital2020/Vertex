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
        DB::table('sub_modules')
            ->where('sub_module_name', 'Overtime')
            ->update(['sub_module_name' => 'Overtime(Admin)']);
 
        DB::table('sub_modules')->insert([
        [
            'sub_module_name' => 'Overtime(Employee)', 
            'module_id' => 6,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
        ]);
    }
 
    public function down(): void
    {
         
    }
};
