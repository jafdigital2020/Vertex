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
        Schema::create('mobile_access_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id'); // For multi-tenancy
            $table->integer('total_licenses')->default(0); // Total mobile access licenses purchased
            $table->integer('used_licenses')->default(0); // Currently assigned licenses
            $table->integer('available_licenses')->default(0); // Remaining unassigned licenses
            $table->decimal('license_price', 8, 2)->default(49.00); // Price per license (₱49)
            $table->text('notes')->nullable(); // Optional notes about the license pool
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->nullableMorphs('created_by');
            $table->nullableMorphs('updated_by');
            $table->timestamps();
            
            // Index for faster tenant-based queries
            $table->index('tenant_id');
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_access_licenses');
    }
};