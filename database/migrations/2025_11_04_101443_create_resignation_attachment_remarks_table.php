<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResignationAttachmentRemarksTable extends Migration
{
 
    public function up(): void
    {
        Schema::create('resignation_attachment_remarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resignation_attachment_id');
            $table->unsignedBigInteger('remarks_from');  
            $table->string('remarks_from_role');  
            $table->text('remarks')->nullable();
            $table->timestamps(); 
            $table->foreign('resignation_attachment_id')
                  ->references('id')
                  ->on('resignation_attachment')
                  ->onDelete('cascade'); 
            $table->foreign('remarks_from')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('resignation_attachment_remarks');
    }
}
