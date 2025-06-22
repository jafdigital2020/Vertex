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
        Schema::create('philhealth_contributions', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->decimal('min_salary', 15, 2);
            $table->decimal('max_salary', 15, 2);
            $table->decimal('monthly_premium', 15, 2);
            $table->decimal('employee_share', 15, 2);
            $table->decimal('employer_share', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('philhealth_contributions');
    }
};
