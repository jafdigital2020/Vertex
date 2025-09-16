<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // ✅ FIX: Add 'consolidated_pending' to the status enum
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('pending', 'paid', 'failed', 'cancelled', 'refunded', 'consolidated', 'consolidated_pending') DEFAULT 'pending'");
    }

    public function down()
    {
        // Remove 'consolidated_pending' from enum
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('pending', 'paid', 'failed', 'cancelled', 'refunded', 'consolidated') DEFAULT 'pending'");
    }
};
