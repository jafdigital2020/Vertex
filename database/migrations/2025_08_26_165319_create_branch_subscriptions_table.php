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

            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');

            $table->string('plan')->nullable();
            $table->string('mobile_number')->nullable();
            $table->json('plan_details')->nullable();

            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->char('currency', 3)->default('PHP');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');

            $table->date('subscription_start')->nullable();
            $table->date('subscription_end')->nullable();
            $table->date('trial_start')->nullable();
            $table->date('trial_end')->nullable();

            $table->enum('status', ['active', 'expired', 'trial', 'cancelled'])->default('active');
            $table->dateTime('renewed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();

            $table->string('payment_gateway')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->text('notes')->nullable();
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
