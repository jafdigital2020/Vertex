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
        Schema::create('allowances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->string('allowance_name');
            $table->decimal('amount', 15, 2);
            $table->boolean('apply_to_all_employees')->default(false); // Indicates if this allowance applies to all employees
            $table->boolean('is_taxable')->default(false); // Indicates if the allowance is
            $table->string('description')->nullable(); // Optional description of the allowance
            $table->enum('status', ['active', 'inactive'])->default('active'); // e.g. 'active', 'inactive'
            $table->nullableMorphs('created_by'); // e.g. 'user', 'global_user'
            $table->nullableMorphs('updated_by'); // e.g. 'user', '
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allowances');
    }
};
