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
        // Safe-guard: only alter the table if it already exists to avoid errors
        // when migrations run out of chronological order in different environments.
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->enum('billing_cycle', ['monthly', 'yearly'])->nullable()->after('invoice_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'billing_cycle')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('billing_cycle');
            });
        }
    }
};
