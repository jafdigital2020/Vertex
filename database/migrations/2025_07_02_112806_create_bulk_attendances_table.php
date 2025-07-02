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
        Schema::create('bulk_attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->date('date_from');
            $table->date('date_to');
            $table->integer('regular_working_days')->nullable();
            $table->integer('regular_working_hours')->nullable();
            $table->integer('regular_overtime_hours')->nullable();
            $table->integer('regular_nd_hours')->nullable();
            $table->integer('regular_nd_overtime_hours')->nullable();
            $table->integer('rest_day_work')->nullable();
            $table->integer('rest_day_ot')->nullable();
            $table->integer('rest_day_nd')->nullable();
            $table->integer('regular_holiday_hours')->nullable();
            $table->integer('special_holiday_hours')->nullable();
            $table->integer('regular_holiday_ot')->nullable();
            $table->integer('special_holiday_ot')->nullable();
            $table->integer('regular_holiday_nd')->nullable();
            $table->integer('special_holiday_nd')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_attendances');
    }
};
