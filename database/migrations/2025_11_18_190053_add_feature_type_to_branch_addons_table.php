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
        Schema::table('branch_addons', function (Blueprint $table) {
            //
            $table->enum('feature_type', ['addon', 'upgrade'])->default('addon')->after('addon_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_addons', function (Blueprint $table) {
            //
            $table->dropColumn('feature_type');
        });
    }
};
