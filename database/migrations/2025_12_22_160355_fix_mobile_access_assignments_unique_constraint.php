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
        Schema::table('mobile_access_assignments', function (Blueprint $table) {
            // Drop the problematic unique constraint that includes all statuses
            $table->dropIndex('unique_active_assignment_with_type');
            
            // Add indexes for better query performance
            $table->index(['tenant_id', 'user_id', 'user_type', 'status'], 'idx_tenant_user_type_status');
            $table->index('status', 'idx_assignment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobile_access_assignments', function (Blueprint $table) {
            // Drop the new indexes
            $table->dropIndex('idx_tenant_user_type_status');
            $table->dropIndex('idx_assignment_status');
            
            // Restore the old constraint (if needed)
            $table->unique(['tenant_id', 'user_id', 'user_type', 'status'], 'unique_active_assignment_with_type');
        });
    }
};
