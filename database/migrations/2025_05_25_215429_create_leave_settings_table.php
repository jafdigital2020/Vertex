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
        Schema::create('leave_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_type_id')
                ->constrained('leave_types')
                ->onDelete('cascade');
            $table->integer('advance_notice_days')->default(0);
            $table->boolean('allow_half_day')->default(false);
            $table->boolean('allow_backdated')->default(false);
            $table->integer('backdated_days')->default(0);
            $table->boolean('require_documents')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_settings');
    }
};
