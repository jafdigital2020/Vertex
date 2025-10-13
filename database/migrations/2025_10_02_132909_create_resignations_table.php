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
        Schema::create('resignations', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_filed')->nullable();  
            $table->unsignedBigInteger('user_id'); 
            $table->string('resignation_file')->nullable(); 
            $table->string('reason', 500)->nullable();   
            $table->integer('status')->default(0);  
            $table->string('status_remarks', 1000)->nullable(); 
            $table->dateTime('status_date')->nullable();   
            $table->unsignedBigInteger('accepted_by')->nullable(); 
            $table->string('accepted_remarks', 1000)->nullable(); 
            $table->dateTime('accepted_date')->nullable();  
            $table->date('resignation_date')->nullable();   
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resignations');
    }
};
