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
            // Drop the old unique constraint
            $table->dropIndex('unique_active_assignment');
            
            // Add new unique constraint that includes user_type
            $table->unique(['tenant_id', 'user_id', 'user_type', 'status'], 'unique_active_assignment_with_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobile_access_assignments', function (Blueprint $table) {
            // Drop new constraint
            $table->dropIndex('unique_active_assignment_with_type');
            
            // Restore old constraint
            $table->unique(['tenant_id', 'user_id', 'status'], 'unique_active_assignment');
        });
    }
};