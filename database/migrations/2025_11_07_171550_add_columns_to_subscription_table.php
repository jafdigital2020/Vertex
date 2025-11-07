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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('implementation_fee_paid', 10, 2)->default(0)->after('overage_license_count');
            $table->decimal('vat_amount', 10, 2)->default(0)->after('implementation_fee_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('implementation_fee_paid');
            $table->dropColumn('vat_amount');
        });
    }
};
