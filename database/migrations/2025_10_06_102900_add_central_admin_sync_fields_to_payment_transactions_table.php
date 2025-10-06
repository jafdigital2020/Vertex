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
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->timestamp('central_admin_synced_at')->nullable();
            $table->enum('central_admin_sync_status', ['success', 'failed', 'pending', 'skipped'])->nullable();
            $table->json('central_admin_response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'central_admin_synced_at',
                'central_admin_sync_status',
                'central_admin_response',
            ]);
        });
    }
};
