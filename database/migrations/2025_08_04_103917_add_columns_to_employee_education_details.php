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
        Schema::table('employee_education_details', function (Blueprint $table) {
            $table->string('education_level')->nullable()->after('date_to');
            $table->string('year')->nullable()->after('education_level');
            $table->string('notes')->nullable()->after('year');
            $table->string('attachment')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_education_details', function (Blueprint $table) {
            $table->dropColumn('education_level');
            $table->dropColumn('year');
            $table->dropColumn('notes');
            $table->dropColumn('attachment');
        });
    }
};
