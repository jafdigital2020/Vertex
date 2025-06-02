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
        Schema::create('sss_contribution_tables', function (Blueprint $table) {
            $table->id();
            $table->decimal('range_from', 15, 2);
            $table->decimal('range_to', 15, 2);

            // Monthly Salary Credit (MSC)
            $table->decimal('monthly_salary_credit', 15, 2);

            // Employer Share
            $table->decimal('employer_regular_ss', 15, 2);
            $table->decimal('employer_mpf', 15, 2)->nullable();
            $table->decimal('employer_ec', 15, 2);
            $table->decimal('employer_total', 15, 2);

            // Employee Share
            $table->decimal('employee_regular_ss', 15, 2);
            $table->decimal('employee_mpf', 15, 2)->nullable();
            $table->decimal('employee_total', 15, 2);

            // Total Contribution employer + employee
            $table->decimal('total_contribution', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sss_contribution_tables');
    }
};
