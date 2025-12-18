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
        Schema::create('mobile_access_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id'); // For multi-tenancy
            $table->unsignedBigInteger('user_id'); // Employee who has mobile access
            $table->unsignedBigInteger('mobile_access_license_id'); // Reference to the license pool
            $table->unsignedBigInteger('branch_id')->nullable(); // Branch for data access control
            $table->enum('status', ['active', 'revoked'])->default('active');
            $table->timestamp('assigned_at');
            $table->timestamp('revoked_at')->nullable();
            $table->text('revoke_reason')->nullable(); // Reason for revoking access
            $table->nullableMorphs('assigned_by'); // Who assigned the license
            $table->nullableMorphs('revoked_by'); // Who revoked the license
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('mobile_access_license_id')->references('id')->on('mobile_access_licenses')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            
            // Indexes for performance
            $table->index('tenant_id');
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['user_id', 'status']);
            
            // Ensure one active assignment per user per tenant
            $table->unique(['tenant_id', 'user_id', 'status'], 'unique_active_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_access_assignments');
    }
};