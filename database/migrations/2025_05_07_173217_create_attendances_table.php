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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shift_id');
            $table->unsignedBigInteger('shift_assignment_id')->nullable();
            $table->unsignedBigInteger('geofence_id')->nullable();
            $table->date('attendance_date');
            $table->timestamp('date_time_in')->nullable();
            $table->timestamp('date_time_out')->nullable();
            $table->json('multiple_login')->nullable();
            $table->json('multiple_logout')->nullable();
            $table->timestamp('break_in')->nullable();
            $table->timestamp('break_out')->nullable();
            $table->string('status');
            $table->decimal('time_in_latitude', 10, 8)->nullable();
            $table->decimal('time_in_longitude', 11, 8)->nullable();
            $table->string('time_in_address')->nullable();
            $table->decimal('time_out_latitude', 10, 8)->nullable();
            $table->decimal('time_out_longitude', 11, 8)->nullable();
            $table->string('time_out_address')->nullable();
            $table->boolean('within_geofence')->default(false);
            $table->string('time_in_photo_path')->nullable();
            $table->string('time_out_photo_path')->nullable();
            $table->string('clock_in_method')->nullable();
            $table->string('clock_out_method')->nullable();
            $table->boolean('is_rest_day')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->integer('total_work_minutes')->nullable();
            $table->integer('total_late_minutes')->nullable();
            $table->string('late_status_box')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('shift_lists')->onDelete('cascade');
            $table->foreign('shift_assignment_id')->references('id')->on('shift_assignments')->onDelete('set null');
            $table->foreign('geofence_id')->references('id')->on('geofences')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
