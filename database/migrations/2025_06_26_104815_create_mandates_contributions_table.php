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
        Schema::create('mandates_contributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payroll_id')->nullable();
            $table->string('year');
            $table->string('month');
            $table->string('cutoff_period');
            $table->decimal('basic_pay', 15, 2)->nullable();
            $table->decimal('gross_pay', 15, 2)->nullable();
            $table->decimal('philhealth_contribution', 15, 2)->nullable();
            $table->decimal('sss_contribution', 15, 2)->nullable();
            $table->decimal('pagibig_contribution', 15, 2)->nullable();
            $table->decimal('withholding_tax', 15, 2)->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mandates_contributions');
    }
};
