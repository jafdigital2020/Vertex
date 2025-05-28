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
        Schema::table('branches', function (Blueprint $table) {
            $table->enum('worked_days_per_year', ['245', '261', '300', '313', '365', 'custom'])->default('261');
            $table->float('custom_worked_days', 8, 2)->nullable();
            $table->decimal('fixed_sss_amount', 10, 2)->nullable();
            $table->decimal('fixed_philhealth_amount', 10, 2)->nullable();
            $table->decimal('fixed_pagibig_amount', 10, 2)->nullable();
            $table->decimal('fixed_withholding_tax_amount', 10, 2)->nullable();
            $table->string('e_signature')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'worked_days_per_year',
                'custom_worked_days',
                'fixed_sss_amount',
                'fixed_philhealth_amount',
                'fixed_pagibig_amount',
                'fixed_withholding_tax_amount',
                'e_signature',
            ]);
        });
    }
};
