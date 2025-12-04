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
        Schema::create('manpower_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('request_number')->unique();
            $table->string('position');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('designation_id')->nullable();
            $table->integer('vacancies')->default(1);
            $table->decimal('salary_min', 15, 2)->nullable();
            $table->decimal('salary_max', 15, 2)->nullable();
            $table->enum('employment_type', ['full-time', 'part-time', 'contract', 'internship'])->default('full-time');
            $table->text('justification')->nullable(); // Why this position is needed
            $table->text('job_description')->nullable();
            $table->json('requirements')->nullable();
            $table->json('skills')->nullable();
            $table->date('target_start_date')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', [
                'pending', 
                'pending_coo_approval', 
                'approved', 
                'rejected', 
                'posted', 
                'filled', 
                'closed'
            ])->default('pending');
            $table->unsignedBigInteger('requested_by'); // Department manager
            $table->unsignedBigInteger('reviewed_by')->nullable(); // HR reviewer
            $table->unsignedBigInteger('approved_by')->nullable(); // COO approver
            $table->unsignedBigInteger('job_posting_id')->nullable(); // Linked job posting
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->nullableMorphs('updated_by');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('set null');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('job_posting_id')->references('id')->on('job_postings')->onDelete('set null');

            // Indexes
            $table->index(['branch_id', 'status']);
            $table->index(['status', 'department_id']);
            $table->index(['requested_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manpower_requests');
    }
};