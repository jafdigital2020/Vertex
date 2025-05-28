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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->decimal('default_entitle', 5, 2)->default(0.00);
            $table->enum('accrual_frequency', ['ANNUAL', 'MONTHLY', 'NONE'])->default('annual');
            $table->decimal('max_carryover', 5, 2)->default(0.00);
            $table->boolean('is_earned')->default(false);
            $table->decimal('earned_rate', 5, 2)->nullable();
            $table->enum('earned_interval', ['ANNUAL', 'MONTHLY', 'NONE'])->nullable();
            $table->boolean('is_paid')->default(true);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
