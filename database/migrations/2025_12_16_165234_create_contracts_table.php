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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('contract_templates')->onDelete('set null');
            $table->enum('contract_type', ['Probationary', 'Regular', 'Contractual', 'Project-Based'])->default('Probationary');
            $table->longText('content');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['Draft', 'Active', 'Expired', 'Terminated'])->default('Draft');
            $table->date('signed_date')->nullable();
            $table->unsignedBigInteger('signed_by')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->timestamps();
            
            $table->foreign('signed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
