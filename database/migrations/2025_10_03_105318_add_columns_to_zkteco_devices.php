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
        Schema::table('zkteco_devices', function (Blueprint $table) {
            $table->string('biotime_server_url')->nullable();
            $table->string('biotime_username')->nullable();
            $table->string('biotime_password')->nullable();
            $table->enum('connection_method', ['direct', 'biotime'])->default('biotime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zkteco_devices', function (Blueprint $table) {
            $table->dropColumn('biotime_server_url');
            $table->dropColumn('biotime_username');
            $table->dropColumn('biotime_password');
            $table->dropColumn('connection_method');
        });
    }
};
