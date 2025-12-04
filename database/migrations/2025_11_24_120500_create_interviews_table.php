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
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->string('interview_code')->unique();
            $table->unsignedBigInteger('job_application_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['phone', 'video', 'in_person', 'technical', 'panel', 'hr'])->default('in_person');
            $table->enum('round', ['1', '2', '3', '4', '5', 'final'])->default('1');
            $table->datetime('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->enum('status', ['scheduled', 'confirmed', 'rescheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->unsignedBigInteger('primary_interviewer');
            $table->json('panel_interviewers')->nullable();
            $table->text('agenda')->nullable();
            $table->json('questions')->nullable();
            $table->text('feedback')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->enum('recommendation', ['strong_hire', 'hire', 'hold', 'no_hire'])->nullable();
            $table->datetime('actual_start_time')->nullable();
            $table->datetime('actual_end_time')->nullable();
            $table->text('notes')->nullable();
            $table->nullableMorphs('updated_by');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('job_application_id')->references('id')->on('job_applications')->onDelete('cascade');
            $table->foreign('primary_interviewer')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index(['job_application_id']);
            $table->index(['scheduled_at']);
            $table->index(['primary_interviewer']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};