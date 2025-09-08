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
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('subscription_id');
            $table->string('invoice_number')->unique();
            $table->decimal('amount_due', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('currency', 3)->default('PHP');
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue', 'canceled'])->default('pending');
            $table->dateTime('issued_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
