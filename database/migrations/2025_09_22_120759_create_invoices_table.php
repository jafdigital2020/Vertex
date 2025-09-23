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
            $table->id();

            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('branch_subscription_id')->nullable()->constrained('branch_subscriptions')->nullOnDelete();

            $table->string('invoice_number')->unique();

            $table->decimal('amount_due', 12, 2);
            $table->decimal('amount_paid', 12, 2)->nullable();

            $table->decimal('subscription_amount', 12, 2)->nullable();
            $table->date('subscription_due_date')->nullable();

            $table->char('currency', 3)->default('PHP');
            $table->string('invoice_type')
                ->default('branch_subscription');

            $table->date('due_date')->nullable();
            $table->enum('status', ['draft', 'sent', 'pending', 'paid', 'partial', 'overdue', 'void'])->default('draft');

            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            $table->timestamps();

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
