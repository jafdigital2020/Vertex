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
        // Force update the invoice_type enum to include new types
        DB::statement("ALTER TABLE invoices MODIFY COLUMN invoice_type ENUM('subscription', 'license_overage', 'combo', 'implementation_fee', 'plan_upgrade', 'custom_order') NOT NULL DEFAULT 'subscription'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to previous enum values (optional - you might want to keep the new values)
        DB::statement("ALTER TABLE invoices MODIFY COLUMN invoice_type ENUM('subscription', 'license_overage', 'combo') NOT NULL DEFAULT 'subscription'");
    }
};
