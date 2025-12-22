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
        // This migration is no longer needed as the constraint removal
        // is handled by a later migration (2025_12_22_161039)
        // Keeping this empty to avoid conflicts
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
