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
        Schema::table('mobile_access_licenses', function (Blueprint $table) {
            $table->timestamp('pool_expires_at')->nullable()->after('status')->comment('Expiration date for the entire license pool - all assignments expire together');
            $table->timestamp('pool_started_at')->nullable()->after('pool_expires_at')->comment('When the current billing cycle started');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobile_access_licenses', function (Blueprint $table) {
            $table->dropColumn(['pool_expires_at', 'pool_started_at']);
        });
    }
};
