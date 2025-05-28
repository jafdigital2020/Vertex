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
        Schema::create('salary_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('basic_salary', 15, 2);
            $table->enum('salary_type', ['monthly_fixed', 'daily_rate', 'hourly_rate'])->default('monthly_fixed');
            $table->date('effective_date')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('remarks')->nullable();
            $table->nullableMorphs('created_by');  // Polymorphic Field
            $table->timestamps();

            //Foreign Key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_records');
    }
};
