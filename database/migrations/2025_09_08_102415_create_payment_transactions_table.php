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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('subscription_id');
            $table->string('payment_gateway')->nullable();
            $table->string('transaction_reference')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('PHP');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
