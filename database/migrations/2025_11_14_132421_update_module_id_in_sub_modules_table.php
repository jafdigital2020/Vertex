<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{ 
    public function up(): void
    {
        DB::table('sub_modules')
            ->whereIn('id', [60, 61])
            ->update(['module_id' => 19]);
    }
 
    public function down(): void
    { 
        DB::table('sub_modules')
            ->whereIn('id', [60, 61])
            ->update(['module_id' => 4]);
    }
};
