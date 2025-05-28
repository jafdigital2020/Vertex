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
        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->enum('type', ['recurring', 'custom']);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_rest_day')->default(false);
            $table->json('days_of_week')->nullable();
            $table->json('custom_dates')->nullable();
            $table->json('excluded_dates')->nullable();
            $table->timestamps();

            //Foreign Keys
            $table->foreign('shift_id')->references('id')->on('shift_lists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_assignments');
    }
};
