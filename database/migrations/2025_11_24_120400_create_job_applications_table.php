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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_code')->unique();
            $table->unsignedBigInteger('job_posting_id');
            $table->unsignedBigInteger('candidate_id');
            $table->enum('status', ['applied', 'under_review', 'shortlisted', 'interview_scheduled', 'interviewed', 'evaluation', 'offer_made', 'offer_accepted', 'offer_rejected', 'hired', 'rejected', 'withdrawn'])->default('applied');
            $table->text('cover_letter')->nullable();
            $table->decimal('expected_salary', 15, 2)->nullable();
            $table->date('available_start_date')->nullable();
            $table->json('questionnaire_responses')->nullable();
            $table->unsignedBigInteger('assigned_recruiter')->nullable();
            $table->integer('stage')->default(1);
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->text('recruiter_notes')->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamp('last_updated_at')->nullable();
            $table->nullableMorphs('updated_by');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('job_posting_id')->references('id')->on('job_postings')->onDelete('cascade');
            $table->foreign('candidate_id')->references('id')->on('candidates')->onDelete('cascade');
            $table->foreign('assigned_recruiter')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->unique(['job_posting_id', 'candidate_id']);
            $table->index(['status']);
            $table->index(['job_posting_id', 'status']);
            $table->index(['assigned_recruiter']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};