<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assets_details_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_detail_id');  
            $table->string('item_no')->nullable();
            $table->unsignedBigInteger('deployed_to')->nullable();  
            $table->dateTime('deployed_date')->nullable();
            $table->string('condition')->nullable();
            $table->text('condition_remarks')->nullable();
            $table->string('status')->nullable();
            $table->text('status_remarks')->nullable();
            $table->string('process')->nullable();  
            $table->unsignedBigInteger('updated_by')->nullable();  
            $table->unsignedBigInteger('created_by')->nullable(); 
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
         
    }
};
