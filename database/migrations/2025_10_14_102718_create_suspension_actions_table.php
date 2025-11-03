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
        Schema::create('suspension_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('suspension_id');
            $table->foreign('suspension_id')
                ->references('id')->on('suspensions')
                ->onDelete('cascade');

            $table->enum('action_type', [
                'report_received',
                'nowe_issued',
                'employee_reply',
                'investigation',
                'dam_issued',
                'suspension_implemented',
                'return_to_work'
            ]);

            $table->unsignedBigInteger('action_by')->nullable();
            $table->foreign('action_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->dateTime('action_date')->nullable();
            $table->string('file_path')->nullable();
            $table->text('description')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suspension_actions');
    }
};
