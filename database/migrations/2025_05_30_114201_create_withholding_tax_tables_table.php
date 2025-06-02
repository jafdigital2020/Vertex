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
        Schema::create('withholding_tax_tables', function (Blueprint $table) {
            $table->id();
            $table->enum('frequency', ['daily', 'weekly', 'semi-monthly', 'monthly']);
            $table->decimal('range_from', 15, 2);
            $table->decimal('range_to', 15, 2)->nullable();
            $table->decimal('fix', 10, 2)->default(0.00);
            $table->decimal('rate', 5, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withholding_tax_tables');
    }
};
