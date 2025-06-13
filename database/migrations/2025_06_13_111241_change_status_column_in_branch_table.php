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
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('branches', function (Blueprint $table) {
            // Recreate it as boolean
            $table->boolean('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            // Drop the boolean
            $table->dropColumn('status');
        });

        Schema::table('branches', function (Blueprint $table) {
            // Restore the enum
            $table->enum('status', ['active', 'inactive'])->default('active');
        });
    }
};
