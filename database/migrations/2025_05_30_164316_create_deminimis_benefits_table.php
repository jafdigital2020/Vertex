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
        Schema::create('deminimis_benefits', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('maximum_amount', 15, 2)->default(0.00);
            $table->enum('frequency', ['monthly', 'quarterly', 'yearly', 'per_meal'])->default('monthly');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deminimis_benefits');
    }
};
