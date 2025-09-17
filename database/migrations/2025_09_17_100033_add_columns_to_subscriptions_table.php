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
            $table->integer('paid_overage_license_count')->default(0)->after('base_license_count');
            $table->decimal('overage_monthly_rate', 10, 2)->default(49.00)->after('paid_overage_license_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('paid_overage_license_count');
            $table->dropColumn('overage_monthly_rate');
        });
    }
};
