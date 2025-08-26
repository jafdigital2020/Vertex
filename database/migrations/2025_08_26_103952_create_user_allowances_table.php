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
        Schema::create('user_allowances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('allowance_id');
            $table->enum('type', ['include', 'exclude'])->default('include');
            $table->string('frequency')->nullable();
            $table->boolean('override_enabled')->default(false);
            $table->decimal('override_amount', 15, 2)->nullable();
            $table->enum('calculation_basis', ['fixed', 'per_attended_day', 'per_attended_hour'])->nullable();
            $table->enum('status', ['active', 'inactive', 'complete', 'hold'])->default('active');
            $table->date('effective_start_date')->nullable();
            $table->date('effective_end_date')->nullable();
            $table->string('notes')->nullable();
            $table->nullableMorphs('created_by');
            $table->nullableMorphs('updated_by');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('allowance_id')->references('id')->on('allowances')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_allowances');
    }
};
