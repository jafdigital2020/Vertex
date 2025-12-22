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
            // Drop foreign key constraint first
            $table->dropForeign(['subscription_id']);
            
            // Make subscription_id nullable
            $table->unsignedBigInteger('subscription_id')->nullable()->change();
            
            // Re-add foreign key constraint
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['subscription_id']);
            
            // Make subscription_id non-nullable again
            $table->unsignedBigInteger('subscription_id')->nullable(false)->change();
            
            // Re-add foreign key constraint
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
        });
    }
};
