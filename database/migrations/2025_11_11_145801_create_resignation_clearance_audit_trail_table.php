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
      Schema::create('resignation_clearance_audit_trail', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('resignation_id');
        $table->unsignedBigInteger('asset_detail_id')->nullable();
        $table->string('previous_asset_status')->nullable();  
        $table->unsignedBigInteger('attachment_id')->nullable();
        $table->unsignedBigInteger('performed_by'); 
        $table->string('action'); 
        $table->timestamps(); 
        $table->foreign('resignation_id')->references('id')->on('resignations')->onDelete('cascade');
    });

    }
 
    public function down(): void
    {
        Schema::dropIfExists('resignation_clearance_audit_trail');
    }
};
