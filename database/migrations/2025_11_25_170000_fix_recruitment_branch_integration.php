<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration ensures branch_id is properly added to recruitment tables
     * on fresh database deployments where the tables are created with branch_id already included.
     */
    public function up(): void
    {
        // For fresh deployments, the main recruitment migrations should already include branch_id
        // This migration is a safety net for existing databases that need the branch_id added
        
        // Only run if we detect this is an upgrade scenario (tables exist without branch_id)
        $isUpgradeScenario = false;
        
        $tablesToCheck = ['job_postings', 'candidates', 'manpower_requests', 'recruitment_settings'];
        
        foreach ($tablesToCheck as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'branch_id')) {
                $isUpgradeScenario = true;
                break;
            }
        }
        
        // If this is an upgrade scenario, run the branch integration
        if ($isUpgradeScenario) {
            $this->addBranchIntegration();
        }
        
        // Always ensure the recruitment_approvals table has proper foreign keys
        if (Schema::hasTable('recruitment_approvals')) {
            $this->ensureRecruitmentApprovalsIntegrity();
        }
    }
    
    /**
     * Add branch integration to recruitment tables
     */
    private function addBranchIntegration(): void
    {
        if (Schema::hasTable('job_postings') && !Schema::hasColumn('job_postings', 'branch_id')) {
            Schema::table('job_postings', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
                $table->index(['branch_id', 'status'], 'job_postings_branch_status_idx');
            });
        }

        if (Schema::hasTable('candidates') && !Schema::hasColumn('candidates', 'branch_id')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('role_id');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
                $table->index(['branch_id', 'status'], 'candidates_branch_status_idx');
            });
        }

        if (Schema::hasTable('manpower_requests') && !Schema::hasColumn('manpower_requests', 'branch_id')) {
            Schema::table('manpower_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
                $table->index(['branch_id', 'status'], 'manpower_requests_branch_status_idx');
            });
        }

        if (Schema::hasTable('recruitment_settings') && !Schema::hasColumn('recruitment_settings', 'branch_id')) {
            Schema::table('recruitment_settings', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
                $table->index(['branch_id', 'group'], 'recruitment_settings_branch_group_idx');
            });
        }
    }
    
    /**
     * Ensure recruitment_approvals table has proper relationships
     */
    private function ensureRecruitmentApprovalsIntegrity(): void
    {
        // Check if foreign key constraints exist
        try {
            Schema::table('recruitment_approvals', function (Blueprint $table) {
                // Only add if not exists - Laravel will skip if they already exist
                if (!$this->foreignKeyExists('recruitment_approvals', 'recruitment_approvals_branch_id_foreign')) {
                    $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
                }
                if (!$this->foreignKeyExists('recruitment_approvals', 'recruitment_approvals_approver_id_foreign')) {
                    $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');
                }
            });
        } catch (\Exception $e) {
            // Foreign keys might already exist, ignore
        }
    }
    
    /**
     * Check if a foreign key exists
     */
    private function foreignKeyExists($table, $foreignKey): bool
    {
        $databaseName = Schema::getConnection()->getDatabaseName();
        $count = Schema::getConnection()
            ->table('information_schema.key_column_usage')
            ->where('table_schema', $databaseName)
            ->where('table_name', $table)
            ->where('constraint_name', $foreignKey)
            ->count();
        return $count > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // In down, we would remove branch_id columns if they were added by this migration
        // But since we want to preserve data integrity, we'll leave them
    }
};