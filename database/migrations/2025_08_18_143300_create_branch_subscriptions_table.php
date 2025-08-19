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
        Schema::create('branch_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('plan')->nullable();
            $table->decimal('amount_paid', 10, 2);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->date('subscription_start')->nullable();
            $table->date('subscription_end')->nullable();
            $table->date('trial_start')->nullable();
            $table->date('trial_end')->nullable();
            $table->enum('status', ['active', 'expired', 'trial'])->default('active');
            $table->dateTime('renewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_subscriptions');
    }
};
