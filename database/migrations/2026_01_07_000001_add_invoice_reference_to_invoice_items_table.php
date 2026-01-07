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
        Schema::table('invoice_items', function (Blueprint $table) {
            // Add invoice reference fields for easier querying
            $table->string('invoice_number')->nullable()->after('invoice_id')->comment('Reference to invoice number');
            $table->enum('invoice_type', ['subscription', 'license_overage', 'combo', 'implementation_fee', 'plan_upgrade', 'custom_order'])
                ->nullable()
                ->after('invoice_number')
                ->comment('Type of invoice this item belongs to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'invoice_type']);
        });
    }
};
