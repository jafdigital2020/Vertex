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
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('addon_key')->nullable();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->enum('type', ['monthly', 'one_time', 'annually'])->default('monthly');
            $table->enum('addon_category', ['addon', 'upgrade'])->default('addon');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('module_ids')->nullable();
            $table->text('submodule_ids')->nullable();
            $table->json('features')->nullable();
            $table->string('icon')->nullable();
            $table->unique(['name']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addons');
    }
};
