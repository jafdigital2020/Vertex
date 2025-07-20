<?php

use Carbon\Carbon;
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
        DB::table('sub_modules')->insert([
        [
            'sub_module_name' => 'Payroll Batch Users', 
            'module_id' => 10,
            'order_no' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
        [
            'sub_module_name' => 'Payroll Batch Settings', 
            'module_id' => 10,
            'order_no' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    { 
    }
};
