<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{   

    public function up(): void
    {
        Schema::table('resignation_clearance_audit_trail', function (Blueprint $table) {
            $table->date('previous_deployed_date')->nullable()->after('previous_asset_status');
        });
    }

    public function down(): void
    {
        Schema::table('resignation_clearance_audit_trail', function (Blueprint $table) {
            $table->dropColumn('previous_deployed_date');
        });
    }
    
     
};
