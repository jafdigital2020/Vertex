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
        Schema::table('thirteenth_month_pays', function (Blueprint $table) {
            $table->unsignedSmallInteger('from_year')->after('from_month');
            $table->unsignedSmallInteger('to_year')->after('from_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thirteenth_month_pays', function (Blueprint $table) {
            $table->dropColumn(['from_year', 'to_year']);
        });
    }
};
