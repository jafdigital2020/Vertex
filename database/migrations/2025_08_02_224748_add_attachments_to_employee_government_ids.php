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
        Schema::table('employment_government_ids', function (Blueprint $table) {
            $table->string('sss_attachment')->nullable()->after('sss_number');
            $table->string('tin_attachment')->nullable()->after('tin_number');
            $table->string('philhealth_attachment')->nullable()->after('philhealth_number');
            $table->string('pagibig_attachment')->nullable()->after('pagibig_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment_government_ids', function (Blueprint $table) {
            $table->dropColumn('sss_attachment');
            $table->dropColumn('tin_attachment');
            $table->dropColumn('philhealth_attachment');
            $table->dropColumn('pagibig_attachment');
        });
    }
};
