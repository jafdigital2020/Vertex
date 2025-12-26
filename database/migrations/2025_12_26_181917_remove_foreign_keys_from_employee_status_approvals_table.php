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
        Schema::table('employee_status_approvals', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['approved_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_status_approvals', function (Blueprint $table) {
            // Restore foreign keys
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }
};
