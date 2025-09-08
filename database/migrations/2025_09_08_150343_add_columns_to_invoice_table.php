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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'period_start')) {
                $table->date('period_start')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('invoices', 'period_end')) {
                $table->date('period_end')->nullable()->after('period_start');
            }
            $table->index(['subscription_id', 'period_start', 'period_end'], 'invoice_period_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoice_period_idx');
            $table->dropColumn(['period_start', 'period_end']);
        });
    }
};
