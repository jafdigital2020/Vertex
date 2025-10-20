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
       Schema::create('resignation_attachment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resignation_id');
            $table->unsignedBigInteger('uploaded_by');
            $table->enum('uploader_role', ['employee', 'hr']);
            $table->string('filename');
            $table->string('filepath');
            $table->string('filetype')->nullable();
            $table->timestamps();

            $table->foreign('resignation_id')
                ->references('id')
                ->on('resignations')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resignation_attachment');
    }
};
