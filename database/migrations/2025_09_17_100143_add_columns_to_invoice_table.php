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
            $table->boolean('is_recurring_overage')->default(false)->after('license_overage_amount');
            $table->date('overage_billing_start')->nullable()->after('is_recurring_overage');
            $table->date('overage_billing_end')->nullable()->after('overage_billing_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('is_recurring_overage');
            $table->dropColumn('overage_billing_start');
            $table->dropColumn('overage_billing_end');
        });
    }
};
