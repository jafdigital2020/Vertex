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
            // Try to drop the problematic constraint if it exists
            try {
                $table->dropIndex('unique_active_user_assignment');
            } catch (\Exception $e) {
                // Index might not exist, ignore
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobile_access_assignments', function (Blueprint $table) {
            // Re-add the constraint if needed
            $table->unique(['tenant_id', 'user_id', 'user_type'], 'unique_active_user_assignment');
        });
    }
};
