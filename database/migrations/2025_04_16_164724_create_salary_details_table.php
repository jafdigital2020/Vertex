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
        Schema::create('salary_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('salary_id')->nullable();
            $table->string('sss_contribution');
            $table->decimal('sss_contribution_override', 10, 2)->nullable();
            $table->string('philhealth_contribution');
            $table->decimal('philhealth_contribution_override', 10, 2)->nullable();
            $table->string('pagibig_contribution');
            $table->decimal('pagibig_contribution_override', 10, 2)->nullable();
            $table->string('withholding_tax');
            $table->decimal('withholding_tax_override', 10, 2)->nullable();
            $table->string('worked_days_per_year')->nullable();
            $table->timestamps();

            //Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('salary_id')->references('id')->on('salary_records')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_details');
    }
};
