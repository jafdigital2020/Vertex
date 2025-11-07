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
        Schema::table('invoices', function (Blueprint $table) {
            // Add upgrade_plan_id column to store the new plan ID for plan_upgrade invoices
            $table->unsignedBigInteger('upgrade_plan_id')->nullable()->after('subscription_id');

            // Add foreign key constraint
            $table->foreign('upgrade_plan_id')->references('id')->on('plans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['upgrade_plan_id']);
            $table->dropColumn('upgrade_plan_id');
        });
    }
};
