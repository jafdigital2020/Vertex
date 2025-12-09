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
        Schema::create('violation_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('violation_id');
            $table->foreign('violation_id')
                ->references('id')->on('violations')
                ->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable(); // in bytes
            $table->enum('attachment_type', [
                'information_report',
                'nowe',
                'employee_reply',
                'dam',
                'other'
            ])->default('information_report');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->foreign('uploaded_by')
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violation_attachments');
    }
};
