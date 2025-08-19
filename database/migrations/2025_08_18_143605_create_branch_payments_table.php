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
        Schema::create('branch_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_subscription_id');
            $table->dateTime('payment_date');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 100)->nullable();
            $table->string('transaction_reference', 255)->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('branch_subscription_id')
              ->references('id')
              ->on('branch_subscriptions')
              ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_payments');
    }
};
