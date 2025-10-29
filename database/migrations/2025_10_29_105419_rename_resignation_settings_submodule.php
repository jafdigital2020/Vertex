<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sub_modules')
            ->where('sub_module_name', 'Resignation Settings')
            ->update(['sub_module_name' => 'Resignation HR']);
    }

    public function down(): void
    {
        DB::table('sub_modules')
            ->where('sub_module_name', 'Resignation HR')
            ->update(['sub_module_name' => 'Resignation Settings']);
    }
};
