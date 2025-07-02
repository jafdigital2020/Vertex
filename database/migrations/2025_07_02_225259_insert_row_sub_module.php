<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         DB::table('modules')->insert([ 
        'module_name' => 'Bank', 
        'menu_id'=> 3,  
        'created_at' => now(),
        'updated_at' => now(),
         ]);
        DB::table('sub_modules')->insert([ 
        'sub_module_name' => 'Bank', 
        'module_id'=> 16,  
        'order_no'=> 1,
        'created_at' => now(),
        'updated_at' => now(),
         ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
