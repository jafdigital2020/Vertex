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
        Schema::create('application_workflow', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_application_id');
            $table->enum('from_status', ['applied', 'under_review', 'shortlisted', 'interview_scheduled', 'interviewed', 'evaluation', 'offer_made', 'offer_accepted', 'offer_rejected', 'hired', 'rejected', 'withdrawn'])->nullable();
            $table->enum('to_status', ['applied', 'under_review', 'shortlisted', 'interview_scheduled', 'interviewed', 'evaluation', 'offer_made', 'offer_accepted', 'offer_rejected', 'hired', 'rejected', 'withdrawn']);
            $table->unsignedBigInteger('changed_by');
            $table->text('notes')->nullable();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('job_application_id')->references('id')->on('job_applications')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index(['job_application_id']);
            $table->index(['changed_by']);
            $table->index(['to_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_workflow');
    }
};