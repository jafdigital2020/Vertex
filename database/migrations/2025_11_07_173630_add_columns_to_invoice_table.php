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
            $table->decimal('implementation_fee', 10, 2)->default(0)->after('gross_overage_amount');
            $table->decimal('vat_amount', 10, 2)->default(0)->after('implementation_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('implementation_fee');
            $table->dropColumn('vat_amount');
        });
    }
};
