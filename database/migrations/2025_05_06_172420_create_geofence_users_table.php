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
        Schema::create('geofence_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('geofence_id');
            $table->enum('assignment_type', ['manual', 'exempt']); // 'manual' for addition, 'exempt' for opt-out
            $table->nullableMorphs('created_by'); // Morphs for the user who assigned the geofence
            $table->nullableMorphs('updated_by'); // Morphs for the user who updated the assignment
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('geofence_id')
                ->references('id')->on('geofences')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geofence_users');
    }
};
