<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    { 
        $overtime = DB::table('sub_modules')
            ->where('sub_module_name', 'Overtime(Employee)')
            ->first();

        $attendance = DB::table('sub_modules')
            ->where('sub_module_name', 'Attendance Settings')
            ->first();

        if ($overtime && $attendance) { 
            DB::table('sub_modules')
                ->where('id', $overtime->id)
                ->update(['order_no' => $attendance->order_no]);

            DB::table('sub_modules')
                ->where('id', $attendance->id)
                ->update(['order_no' => $overtime->order_no]);
        }
    }

    public function down(): void
    { 
        $overtime = DB::table('sub_modules')
            ->where('sub_module_name', 'Overtime(Employee)')
            ->first();

        $attendance = DB::table('sub_modules')
            ->where('sub_module_name', 'Attendance Settings')
            ->first();

        if ($overtime && $attendance) { 
            DB::table('sub_modules')
                ->where('id', $overtime->id)
                ->update(['order_no' => $attendance->order_no]);

            DB::table('sub_modules')
                ->where('id', $attendance->id)
                ->update(['order_no' => $overtime->order_no]);
        }
    }
};

