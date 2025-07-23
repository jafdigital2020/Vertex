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
            $table->integer('maximum_allowed_hours')->default(0)->after('end_time');
            $table->integer('grace_period')->default(0)->after('maximum_allowed_hours');
            $table->boolean('is_flexible')->default(false)->after('grace_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_lists', function (Blueprint $table) {
            $table->dropColumn('maximum_allowed_hours');
            $table->dropColumn('grace_period');
            $table->dropColumn('is_flexible');
        });
    }
};
