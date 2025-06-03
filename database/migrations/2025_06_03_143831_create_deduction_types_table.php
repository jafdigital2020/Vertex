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
        Schema::create('deduction_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->enum('calculation_method', ['fixed', 'percentage']);
            $table->decimal('default_amount', 15, 2);
            $table->boolean('is_taxable')->default(false);
            $table->boolean('apply_to_all_employees')->default(false);
            $table->text('description')->nullable();
            $table->nullableMorphs('created_by');
            $table->nullableMorphs('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deduction_types');
    }
};
