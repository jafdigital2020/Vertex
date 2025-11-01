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
        Schema::create('thirteenth_month_pays', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->year('year');
            $table->integer('from_month'); // 1-12
            $table->integer('to_month');   // 1-12
            $table->json('monthly_breakdown'); // Array of monthly data
            $table->decimal('total_basic_pay', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('total_thirteenth_month', 15, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->string('processor_type')->nullable();
            $table->unsignedBigInteger('processor_id')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Released'])->default('Pending');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['tenant_id', 'user_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thirteenth_month_pays');
    }
};
