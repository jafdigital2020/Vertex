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
        Schema::create('employee_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('primary_name');
            $table->string('primary_relationship');
            $table->string('primary_phone_one');
            $table->string('primary_phone_two')->nullable();
            $table->string('secondary_name')->nullable();
            $table->string('secondary_relationship')->nullable();
            $table->string('secondary_phone_one')->nullable();
            $table->string('secondary_phone_two')->nullable();
            $table->timestamps();

            //Foreign Key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_emergency_contacts');
    }
};
