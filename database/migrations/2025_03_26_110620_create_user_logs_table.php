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
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('global_user_id')->nullable(); // for global_users table
            $table->string('module');              // e.g., employee, leave
            $table->string('action');              // created, updated, deleted
            $table->text('description')->nullable();
            $table->unsignedBigInteger('affected_id')->nullable(); // Affected record ID
            $table->json('old_data')->nullable();   // Before change
            $table->json('new_data')->nullable();   // After change
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logs');
    }
};
