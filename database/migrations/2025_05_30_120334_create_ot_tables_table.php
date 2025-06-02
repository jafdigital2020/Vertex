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
        Schema::create('ot_tables', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['ordinary', 'rest_day', 'special_holiday', 'regular_holiday', 'special_holiday_rest_day', 'regular_holiday_rest_day', 'double_holiday', 'double_holiday_rest_day']);
            $table->decimal('normal', 8, 3);
            $table->decimal('overtime', 8, 3);
            $table->decimal('night_differential', 8, 3)->nullable();
            $table->decimal('night_differential_overtime', 8, 3)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ot_tables');
    }
};
