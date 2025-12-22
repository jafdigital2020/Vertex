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
            $table->enum('user_type', ['tenant_user', 'global_user'])->default('tenant_user')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobile_access_assignments', function (Blueprint $table) {
            $table->dropColumn('user_type');
        });
    }
};