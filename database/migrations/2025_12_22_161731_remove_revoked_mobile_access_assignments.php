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
        // Remove all revoked mobile access assignment records
        DB::table('mobile_access_assignments')
            ->where('status', 'revoked')
            ->delete();
            
        // Update license counts to reflect the changes
        $licenses = DB::table('mobile_access_licenses')->get();
        
        foreach ($licenses as $license) {
            $activeCount = DB::table('mobile_access_assignments')
                ->where('mobile_access_license_id', $license->id)
                ->where('status', 'active')
                ->count();
                
            DB::table('mobile_access_licenses')
                ->where('id', $license->id)
                ->update([
                    'used_licenses' => $activeCount,
                    'available_licenses' => $license->total_licenses - $activeCount,
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot restore deleted records, this is irreversible
        // This is intentional as we're cleaning up revoked records
    }
};