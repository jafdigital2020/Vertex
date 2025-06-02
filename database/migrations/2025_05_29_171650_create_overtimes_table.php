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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('holiday_id')->nullable();
            $table->date('overtime_date');
            $table->timestamp('date_ot_in')->nullable();
            $table->timestamp('date_ot_out')->nullable();
            $table->string('ot_in_photo_path')->nullable();
            $table->string('ot_out_photo_path')->nullable();
            $table->integer('total_ot_minutes')->default(0);
            $table->boolean('is_rest_day')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('file_attachment')->nullable();
            $table->tinyInteger('current_step')->default(1);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('holiday_id')->references('id')->on('holidays')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
