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
        Schema::table('branch_subscriptions', function (Blueprint $table) {
            //
            $table->integer('employee_credits')
                ->default(11)
                ->after('is_trial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_subscriptions', function (Blueprint $table) {
            //
            $table->dropColumn('employee_credits');
        });
    }
};
