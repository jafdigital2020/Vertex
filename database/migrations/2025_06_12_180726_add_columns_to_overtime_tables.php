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
        Schema::table('overtimes', function (Blueprint $table) {
            $table->string('offset_date')->nullable()->after('overtime_date');
            $table->integer('total_night_diff_minutes')->default(0)->after('total_ot_minutes');
            $table->string('ot_login_type')->after('current_step');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropColumn('offset_date');
            $table->dropColumn('ot_login_type');
        });
    }
};
