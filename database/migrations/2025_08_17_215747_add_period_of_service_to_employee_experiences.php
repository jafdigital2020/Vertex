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
        Schema::table('employee_experiences', function (Blueprint $table) {
            $table->string('period_of_service')->nullable()->after('is_present');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_experiences', function (Blueprint $table) {
            $table->dropColumn('period_of_service');
        });
    }
};
