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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('candidate_code')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('nationality')->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('linkedin_profile')->nullable();
            $table->text('summary')->nullable();
            $table->json('skills')->nullable();
            $table->string('current_position')->nullable();
            $table->string('current_company')->nullable();
            $table->decimal('current_salary', 15, 2)->nullable();
            $table->decimal('expected_salary', 15, 2)->nullable();
            $table->string('resume_path')->nullable();
            $table->string('photo_path')->nullable();
            $table->enum('availability', ['immediate', '2_weeks', '1_month', 'negotiable'])->default('negotiable');
            $table->text('notes')->nullable();
            $table->enum('status', ['new', 'screening', 'interview', 'evaluation', 'offer', 'hired', 'rejected', 'withdrawn'])->default('new');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_type')->default('job_application');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('role_id')->references('id')->on('role')->onDelete('set null');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('source_id')->references('id')->on('job_postings')->onDelete('set null');
            
            // Indexes
            $table->index(['branch_id', 'status']);
            $table->index(['status', 'is_active']);
            $table->index(['email']);
            $table->index(['source_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};