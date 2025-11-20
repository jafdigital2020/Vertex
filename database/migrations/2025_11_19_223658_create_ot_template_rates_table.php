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
        Schema::create('ot_template_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ot_template_id')->constrained('ot_templates')->onDelete('cascade');
            $table->string('type');  // regular, rest_day, special_holiday, legal_holiday
            $table->decimal('normal', 5, 4)->default(0.0);
            $table->decimal('overtime', 5, 4)->default(0.0);
            $table->decimal('night_differential', 5, 4)->default(0.0);
            $table->decimal('night_differential_overtime', 5, 4)->default(0.0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ot_template_rates');
    }
};
