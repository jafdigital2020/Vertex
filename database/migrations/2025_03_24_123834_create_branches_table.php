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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('tenant_id');
            $table->string('location');
            $table->string('contact_number')->nullable();
            $table->string('branch_logo')->nullable();
            $table->enum('branch_type', ['main', 'sub'])->default('sub');
            $table->enum('sss_contribution_type', ['system', 'manual', 'fixed', 'none'])->default('system');
            $table->enum('philhealth_contribution_type', ['system', 'manual', 'fixed', 'none'])->default('system');
            $table->enum('pagibig_contribution_type', ['system', 'manual', 'fixed', 'none'])->default('system');
            $table->enum('withholding_tax_type', ['system', 'manual', 'fixed', 'none'])->default('system');
            $table->boolean('status')->default(1); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
