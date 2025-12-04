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
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->string('offer_code')->unique();
            $table->unsignedBigInteger('job_application_id');
            $table->string('position_title');
            $table->unsignedBigInteger('department_id');
            $table->decimal('salary_offered', 15, 2);
            $table->enum('salary_type', ['monthly', 'annual'])->default('monthly');
            $table->json('benefits')->nullable();
            $table->date('start_date');
            $table->enum('employment_type', ['full-time', 'part-time', 'contract', 'probationary'])->default('full-time');
            $table->integer('probation_period_months')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->date('offer_expiry_date');
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired', 'withdrawn'])->default('draft');
            $table->unsignedBigInteger('prepared_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->datetime('sent_at')->nullable();
            $table->datetime('responded_at')->nullable();
            $table->text('candidate_response_notes')->nullable();
            $table->string('offer_letter_path')->nullable();
            $table->text('internal_notes')->nullable();
            $table->nullableMorphs('updated_by');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('job_application_id')->references('id')->on('job_applications')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('prepared_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['job_application_id']);
            $table->index(['status']);
            $table->index(['offer_expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_offers');
    }
};