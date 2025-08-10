<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets_details_remarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_detail_id');
            $table->text('condition_remarks')->nullable();
            $table->text('status_remarks')->nullable();
            $table->timestamps(); 
            
            $table->foreign('asset_detail_id')
                  ->references('id')->on('assets_details')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets_details_remarks');
    }
};
