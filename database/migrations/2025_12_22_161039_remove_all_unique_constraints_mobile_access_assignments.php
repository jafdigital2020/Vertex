<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all indexes for the table
        $indexes = DB::select("SHOW INDEX FROM mobile_access_assignments");
        
        Schema::table('mobile_access_assignments', function (Blueprint $table) use ($indexes) {
            // Check for and remove various possible constraint names
            $constraintsToRemove = [
                'unique_active_assignment_with_type',
                'unique_active_user_assignment',
                'unique_active_assignment'
            ];
            
            foreach ($indexes as $index) {
                if (in_array($index->Key_name, $constraintsToRemove)) {
                    try {
                        $table->dropIndex($index->Key_name);
                    } catch (\Exception $e) {
                        // Constraint doesn't exist, continue
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't restore the problematic constraints
        // They were causing issues with revoke functionality
    }
};