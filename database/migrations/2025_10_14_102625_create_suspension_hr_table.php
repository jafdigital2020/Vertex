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
        Schema::create('suspension_hr', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('suspension_id');
            $table->foreign('suspension_id')
                ->references('id')->on('suspensions')
                ->onDelete('cascade');
            $table->unsignedBigInteger('hr_id');
            $table->foreign('hr_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->foreign('assigned_by')
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->string('status')->default('active'); 
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suspension_hr');
    }
};
