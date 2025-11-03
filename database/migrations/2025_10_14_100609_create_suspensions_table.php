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
        Schema::create('suspensions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->text('offense_details')->nullable();
            $table->string('information_report_file')->nullable();
            $table->string('dam_file')->nullable();
            $table->dateTime('dam_issued_at')->nullable();
            $table->enum('disciplinary_action', [
                'verbal_warning',
                'written_warning',
                'suspension',
                'termination'
            ])->nullable();
            $table->enum('suspension_type', ['with_pay', 'without_pay'])->nullable();
            $table->date('suspension_start_date')->nullable();
            $table->date('suspension_end_date')->nullable();
            $table->integer('suspension_days')->nullable();
            $table->unsignedBigInteger('implemented_by')->nullable();
            $table->foreign('implemented_by')
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->text('implementation_remarks')->nullable();
            $table->dateTime('return_to_work_at')->nullable();
            $table->enum('status', [
                'pending',
                'awaiting_reply',
                'under_investigation',
                'for_dam_issuance',
                'suspended',
                'completed'
            ])->default('pending');

            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suspensions');
    }
};
