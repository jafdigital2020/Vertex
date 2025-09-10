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
            $table->json('raw_request')->nullable();
            $table->json('raw_response')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_status_check')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn(['raw_request', 'raw_response', 'retry_count', 'last_status_check']);
        });
    }
};
