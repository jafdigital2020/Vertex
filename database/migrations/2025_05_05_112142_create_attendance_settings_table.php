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
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('geotagging_enabled')->default(true);
            $table->boolean('geofencing_enabled')->default(false);
            $table->integer('geofence_buffer')->default(0);
            $table->boolean('geofence_allowed_geotagging')->default(false);
            $table->boolean('allow_multiple_clock_ins')->default(false);
            $table->boolean('require_photo_capture')->default(false);
            $table->boolean('enable_break_hour_buttons')->default(false);
            $table->integer('lunch_break_limit')->default(60); // in minutes
            $table->integer('coffee_break_limit')->default(30); // in minutes
            $table->boolean('rest_day_time_in_allowed')->default(false);
            $table->boolean('enable_late_status_box')->default(false);
            $table->integer('grace_period')->default(0); // in minutes
            $table->integer('maximum_allowed_hours')->default(8);
            $table->string('time_display_format')->default('24');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
