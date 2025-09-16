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
            $table->integer('unused_overage_count')->default(0)->after('license_overage_amount');
            $table->decimal('unused_overage_amount', 10, 2)->default(0)->after('unused_overage_count');
            $table->integer('gross_overage_count')->default(0)->after('unused_overage_amount');
            $table->decimal('gross_overage_amount', 10, 2)->default(0)->after('gross_overage_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('unused_overage_count');
            $table->dropColumn('unused_overage_amount');
            $table->dropColumn('gross_overage_count');
            $table->dropColumn('gross_overage_amount');
        });
    }
};
