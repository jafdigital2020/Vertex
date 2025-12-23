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
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('loan_type', ['Emergency Loan', 'Salary Loan', 'Personal Loan', 'Educational Loan', 'Housing Loan', 'Other']);
            $table->decimal('loan_amount', 15, 2);
            $table->integer('repayment_period')->comment('Repayment period in months');
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->text('purpose');
            $table->text('collateral')->nullable();
            $table->date('request_date');
            $table->string('file_attachment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->tinyInteger('current_step')->default(1);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_requests');
    }
};
