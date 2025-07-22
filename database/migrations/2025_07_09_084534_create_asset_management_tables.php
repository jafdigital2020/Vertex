<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetManagementTables extends Migration
{
    public function up(): void
    { 
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
 
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable(); 
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');  
            $table->integer('quantity');
            $table->decimal('price', 10, 2); 
            $table->enum('status', ['active', 'broken', 'maintenance', 'retired'])->default('active'); 
            $table->timestamps();
        });
 
        Schema::create('employee_assets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade'); 
            $table->integer('quantity');
            $table->decimal('price', 10, 2); 
            $table->timestamp('assigned_at')->nullable();
            $table->enum('status', ['assigned', 'returned', 'damaged'])->default('assigned'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_assets');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('categories');
    }
}
