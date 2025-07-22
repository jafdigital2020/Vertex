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
        Schema::table('official_businesses', function (Blueprint $table) {
            $table->integer('ob_break_minutes')->default(0)->after('date_ob_out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('official_businesses', function (Blueprint $table) {
            $table->dropColumn('ob_break_minutes');
        });
    }
};
