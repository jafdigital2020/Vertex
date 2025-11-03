<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include 'Floating'
        DB::statement("ALTER TABLE employment_details MODIFY employment_state ENUM('Active', 'AWOL', 'Resigned', 'Terminated', 'Suspended', 'Floating') DEFAULT 'Active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the enum to original values (without 'Floating')
        DB::statement("ALTER TABLE employment_details MODIFY employment_state ENUM('Active', 'AWOL', 'Resigned', 'Terminated', 'Suspended') DEFAULT 'Active'");
    }
};
