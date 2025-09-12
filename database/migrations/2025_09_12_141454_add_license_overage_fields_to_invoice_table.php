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
            $table->enum('invoice_type', ['subscription', 'license_overage', 'combo'])->default('subscription')->after('subscription_id');
            $table->integer('license_overage_count')->default(0)->after('invoice_type');
            $table->decimal('license_overage_rate', 8, 2)->default(49.00)->after('license_overage_count');
            $table->decimal('subscription_amount', 10, 2)->default(0)->after('license_overage_rate');
            $table->decimal('license_overage_amount', 10, 2)->default(0)->after('subscription_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('invoice_type');
            $table->dropColumn('license_overage_count');
            $table->dropColumn('license_overage_rate');
            $table->dropColumn('subscription_amount');
            $table->dropColumn('license_overage_amount');
        });
    }
};
