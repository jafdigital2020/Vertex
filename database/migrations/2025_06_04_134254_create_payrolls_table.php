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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('payroll_type');
            $table->string('payroll_period')->nullable();
            $table->date('payroll_period_start');
            $table->date('payroll_period_end');

            // Time Tracking Fields
            $table->integer('total_worked_minutes')->default(0);
            $table->integer('total_late_minutes')->default(0);
            $table->integer('total_undertime_minutes')->default(0);
            $table->integer('total_overtime_minutes')->default(0);
            $table->integer('total_night_differential_minutes')->default(0);
            $table->integer('total_overtime_night_diff_minutes')->default(0);
            $table->integer('total_worked_days')->default(0);
            $table->integer('total_absent_days')->default(0);

            // Pay breakdown Fields
            $table->decimal('holiday_pay', 15, 2)->default(0.00);
            $table->decimal('overtime_pay', 15, 2)->default(0.00);
            $table->decimal('night_differential_pay', 15, 2)->default(0.00);
            $table->decimal('overtime_night_diff_pay', 15, 2)->default(0.00);
            $table->decimal('late_deduction', 15, 2)->default(0.00);
            $table->decimal('restday_pay', 15, 2)->default(0.00);
            $table->decimal('overtime_restday_pay', 15, 2)->default(0.00);
            $table->decimal('undertime_deduction', 15, 2)->default(0.00);
            $table->decimal('absent_deduction', 15, 2)->default(0.00);
            $table->json('earnings')->nullable();
            $table->decimal('total_earnings', 15, 2)->default(0.00);
            $table->decimal('taxable_income', 15, 2)->default(0.00);

            // De Minimis Benefits
            $table->json('deminimis')->nullable();

            // Dedctions Fields
            $table->decimal('sss_contribution', 15, 2)->default(0.00);
            $table->decimal('philhealth_contribution', 15, 2)->default(0.00);
            $table->decimal('pagibig_contribution', 15, 2)->default(0.00);
            $table->decimal('withholding_tax', 15, 2)->default(0.00);
            $table->json('loan_deductions')->nullable();
            $table->json('deductions')->nullable();
            $table->decimal('total_deductions', 15, 2)->default(0.00);


            // Salary Breakdown
            $table->decimal('basic_pay', 15, 2)->default(0.00);
            $table->decimal('gross_pay', 15, 2)->default(0.00);
            $table->decimal('net_salary', 15, 2)->default(0.00);

            // Payment Information
            $table->date('payment_date');
            $table->nullableMorphs('processor'); // creates processor_type and processor_id
            $table->nullableMorphs('updater');   // creates updater_type and updater_id

            // Additional Fields for Audit and Notes
            $table->enum('status', ['Pending', 'Processed', 'Paid', 'Draft'])->default('Pending');
            $table->string('remarks')->nullable();
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
