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
      
        DB::table('violations')
            ->where('status', 'completed')
            ->update(['status' => 'implemented']);

        
        Schema::table('violations', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'awaiting_reply',
                'under_investigation',
                'for_dam_issuance',
                'dam_issued',
                'implemented'
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('violations_status', function (Blueprint $table) {
            //
        });
    }
};
