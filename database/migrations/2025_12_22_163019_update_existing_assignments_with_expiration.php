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
        // Update existing active assignments to have expiration dates
        // Set them to expire 30 days from their assignment date
        DB::table('mobile_access_assignments')
            ->where('status', 'active')
            ->whereNull('expires_at')
            ->update([
                'expires_at' => DB::raw('DATE_ADD(assigned_at, INTERVAL 30 DAY)'),
                'auto_renewal' => true,
                'renewal_count' => 0,
                'updated_at' => now()
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove expiration dates from existing assignments
        DB::table('mobile_access_assignments')
            ->update([
                'expires_at' => null,
                'auto_renewal' => false,
                'renewal_count' => 0,
                'last_renewed_at' => null,
                'updated_at' => now()
            ]);
    }
};