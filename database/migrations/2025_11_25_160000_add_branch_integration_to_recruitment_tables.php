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
        // Add branch_id to recruitment tables for multi-branch support
        // Only add if tables exist and column doesn't exist
        
        if (Schema::hasTable('job_postings') && !Schema::hasColumn('job_postings', 'branch_id')) {
            Schema::table('job_postings', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
                $table->index(['branch_id', 'status']);
            });
        }

        if (Schema::hasTable('candidates') && !Schema::hasColumn('candidates', 'branch_id')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('role_id');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
                $table->index(['branch_id', 'status']);
            });
        }

        if (Schema::hasTable('manpower_requests') && !Schema::hasColumn('manpower_requests', 'branch_id')) {
            Schema::table('manpower_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
                $table->index(['branch_id', 'status']);
            });
        }

        if (Schema::hasTable('recruitment_settings') && !Schema::hasColumn('recruitment_settings', 'branch_id')) {
            Schema::table('recruitment_settings', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
                $table->index(['branch_id', 'group']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_settings', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id', 'group']);
            $table->dropColumn('branch_id');
        });

        Schema::table('manpower_requests', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id', 'status']);
            $table->dropColumn('branch_id');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id', 'status']);
            $table->dropColumn('branch_id');
        });

        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id', 'status']);
            $table->dropColumn('branch_id');
        });
    }
};