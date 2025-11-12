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
        Schema::create('zkteco_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address')->nullable();
            $table->integer('port')->default(4370);
            $table->string('api_url')->nullable();
            $table->string('serial_number')->nullable()->unique();
            $table->enum('device_type', ['ip', 'api', 'hybrid'])->default('ip');
            $table->string('location')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('last_activity')->nullable();
            $table->string('biotime_server_url')->nullable();
            $table->string('biotime_username')->nullable();
            $table->string('biotime_password')->nullable();
            $table->enum('connection_method', ['direct', 'biotime'])->default('biotime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zkteco_devices');
    }
};
