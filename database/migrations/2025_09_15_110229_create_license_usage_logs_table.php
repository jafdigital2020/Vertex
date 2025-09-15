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
        Schema::create('license_usage_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('subscription_id');
            $table->date('subscription_period_start');
            $table->date('subscription_period_end');
            $table->timestamp('activated_at');
            $table->timestamp('deactivated_at')->nullable();
            $table->boolean('is_billable')->default(true); // Once activated in period, always billable
            $table->decimal('overage_rate', 8, 2)->default(49.00);
            $table->timestamps();

            // Custom shorter index names
            $table->index(
                ['tenant_id', 'subscription_period_start', 'subscription_period_end'],
                'idx_tenant_period'
            );
            $table->index(
                ['user_id', 'subscription_id'],
                'idx_user_subscription'
            );
            $table->index(
                ['subscription_period_start', 'subscription_period_end'],
                'idx_period_range'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_usage_logs');
    }
};
