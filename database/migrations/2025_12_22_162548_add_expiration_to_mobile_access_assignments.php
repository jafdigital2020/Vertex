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
            // Add expiration date for monthly subscription model
            $table->datetime('expires_at')->nullable()->after('assigned_at');
            
            // Add renewal tracking
            $table->datetime('last_renewed_at')->nullable()->after('expires_at');
            $table->integer('renewal_count')->default(0)->after('last_renewed_at');
            
            // Add auto-renewal flag
            $table->boolean('auto_renewal')->default(true)->after('renewal_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobile_access_assignments', function (Blueprint $table) {
            $table->dropColumn([
                'expires_at',
                'last_renewed_at', 
                'renewal_count',
                'auto_renewal'
            ]);
        });
    }
};