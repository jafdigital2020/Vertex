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
        Schema::table('shift_lists', function (Blueprint $table) {
            // Drop the old unique index on `name`
            $table->dropUnique(['name']);

            // Add composite unique index for (branch_id, name)
            $table->unique(['branch_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_lists', function (Blueprint $table) {
            // Drop composite index
            $table->dropUnique(['branch_id', 'name']);

            // Restore old unique index on name
            $table->unique('name');
        });
    }
};
