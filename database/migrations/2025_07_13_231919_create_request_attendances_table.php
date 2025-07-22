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
        Schema::create('request_attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->date('request_date');
            $table->timestamp('request_date_in')->nullable();
            $table->timestamp('request_date_out')->nullable();
            $table->integer('total_break_minutes')->default(0);
            $table->integer('total_request_minutes')->default(0);
            $table->integer('total_request_nd_minutes')->default(0);
            $table->string('file_attachment')->nullable();
            $table->string('reason')->nullable();
            $table->tinyInteger('current_step')->default(1);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
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
        Schema::dropIfExists('request_attendances');
    }
};
