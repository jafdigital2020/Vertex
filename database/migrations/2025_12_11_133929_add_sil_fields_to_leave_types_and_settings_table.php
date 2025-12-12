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
        // Add SIL fields to leave_types table
        Schema::table('leave_types', function (Blueprint $table) {
            $table->boolean('is_sil')->default(false)->after('is_cash_convertible');
            $table->integer('sil_minimum_service_months')->default(12)->after('is_sil');
        });

        // Add SIL configuration fields to leave_settings table
        Schema::table('leave_settings', function (Blueprint $table) {
            $table->boolean('enable_anniversary_accrual')->default(false)->after('require_documents');
            $table->decimal('sil_days_per_year', 5, 2)->default(5.00)->after('enable_anniversary_accrual');
            $table->text('sil_info_tooltip')->nullable()->after('sil_days_per_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn(['is_sil', 'sil_minimum_service_months']);
        });

        Schema::table('leave_settings', function (Blueprint $table) {
            $table->dropColumn(['enable_anniversary_accrual', 'sil_days_per_year', 'sil_info_tooltip']);
        });
    }
};
