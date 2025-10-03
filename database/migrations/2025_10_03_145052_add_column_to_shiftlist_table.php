<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shift_lists', function (Blueprint $table) {
            //
            $table->integer('allowed_minutes_before_clock_in')->default(0)->after('break_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_lists', function (Blueprint $table) {
            //
            $table->dropColumn('allowed_minutes_before_clock_in');
        });
    }
};
