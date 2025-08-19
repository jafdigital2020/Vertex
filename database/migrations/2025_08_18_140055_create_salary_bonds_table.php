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
        Schema::create('salary_bonds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('salary_record_id')->nullable();
            $table->date('date_issued')->nullable();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->unsignedTinyInteger('payable_in')->default(0); // Number of Cutoffs to be paid
            $table->decimal('payable_amount', 15, 2)->default(0.00);
            $table->decimal('remaining_amount', 15, 2)->default(0.00);
            $table->date('date_completed')->nullable();
            $table->date('date_claimed')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled', 'claimed'])->default('pending');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('salary_record_id')->references('id')->on('salary_records')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_bonds');
    }
};
