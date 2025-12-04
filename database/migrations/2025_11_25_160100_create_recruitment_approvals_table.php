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
        Schema::create('recruitment_approvals', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable'); // job_postings, manpower_requests, job_offers
            $table->unsignedBigInteger('branch_id');
            $table->unsignedTinyInteger('level');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('comments')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->nullableMorphs('updated_by');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['approvable_type', 'approvable_id'], 'rec_approvals_approvable_idx');
            $table->index(['branch_id', 'level', 'status'], 'rec_approvals_branch_level_idx');
            $table->index(['approver_id', 'status'], 'rec_approvals_approver_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_approvals');
    }
};