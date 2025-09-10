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
            $table->uuid('id')->primary();

            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('branch_subscription_id')->nullable()->index();

            $table->string('invoice_number')->unique(); 

            $table->decimal('amount_due', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);

            $table->string('currency', 3)->default('PHP');

            $table->date('due_date')->nullable();
            $table->enum('status', ['draft', 'sent', 'paid', 'partial', 'overdue', 'void'])->default('draft');

            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            $table->timestamps();


            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('branch_subscription_id')->references('id')->on('branch_subscriptions')->nullOnDelete();

            // Helpful indexes
            $table->index(['status', 'due_date']);
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
