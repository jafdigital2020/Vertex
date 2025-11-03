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
        Schema::table('suspensions', function (Blueprint $table) {
            // Change disciplinary_action from ENUM to TEXT
            $table->text('disciplinary_action')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suspensions', function (Blueprint $table) {
            // Revert back to ENUM
            $table->enum('disciplinary_action', [
                'verbal_warning',
                'written_warning',
                'suspension',
                'termination'
            ])->nullable()->change();
        });
    }
};
