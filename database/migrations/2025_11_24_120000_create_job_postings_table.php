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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('job_code')->unique();
            $table->string('title');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('designation_id')->nullable();
            $table->string('location')->nullable();
            $table->text('description');
            $table->json('requirements')->nullable();
            $table->json('skills')->nullable();
            $table->enum('employment_type', ['full-time', 'part-time', 'contract', 'internship'])->default('full-time');
            $table->decimal('salary_min', 15, 2)->nullable();
            $table->decimal('salary_max', 15, 2)->nullable();
            $table->integer('vacancies')->default(1);
            $table->enum('status', ['draft', 'open', 'closed', 'cancelled'])->default('draft');
            $table->date('posted_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('assigned_recruiter')->nullable();
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->nullableMorphs('updated_by');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_recruiter')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['branch_id', 'status']);
            $table->index(['status', 'is_active']);
            $table->index(['department_id', 'status']);
            $table->index(['expiration_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};