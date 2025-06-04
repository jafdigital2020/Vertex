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
        Schema::create('user_earnings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('earning_type_id');
            $table->enum('type', ['include', 'exclude'])->default('include');
            $table->decimal('amount', 15, 2)->nullable();
            $table->enum('frequency', ['every_payroll', 'every_other', 'one_time'])->default('every_payroll');
            $table->date('effective_start_date')->nullable();
            $table->date('effective_end_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'completed', 'hold'])->default('active');
            $table->nullableMorphs('created_by');
            $table->nullableMorphs('updated_by');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('earning_type_id')->references('id')->on('earning_types')->onDelete('cascade');
            $table->index('earning_type_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('frequency');
            $table->index('effective_start_date');
            $table->index('effective_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_earnings');
    }
};
