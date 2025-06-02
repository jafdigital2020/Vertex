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
        Schema::create('user_deminimis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('deminimis_benefit_id');
            $table->decimal('amount', 10, 2)->default(0.00); // amount given
            $table->date('benefit_date'); // date when the benefit was given
            $table->decimal('taxable_excess', 10, 2)->default(0.00); // taxable excess amount
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deminimis_benefit_id')->references('id')->on('deminimis_benefits')->onDelete('cascade');

            $table->index(['user_id', 'benefit_date']);
            $table->index(['deminimis_benefit_id', 'benefit_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_deminimis');
    }
};
