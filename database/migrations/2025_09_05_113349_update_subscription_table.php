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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('plan');
            $table->dropColumn('status');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_id')->nullable()->after('id');
            $table->enum('status', ['active', 'expired', 'trial', 'canceled'])->default('active');

            // Foreign Key Constraint
            $table->foreign('plan_id')
                ->references('id')
                ->on('plans')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['plan_id']);

            $table->enum('status', ['active', 'expired', 'trial'])->default('active'); // Tracks status
            $table->string('plan')->nullable()->after('id');
        });
    }
};
