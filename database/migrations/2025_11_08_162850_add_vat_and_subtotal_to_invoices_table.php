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
            // Add VAT and subtotal columns if they don't exist
            if (!Schema::hasColumn('invoices', 'vat_percentage')) {
                $table->decimal('vat_percentage', 5, 2)->default(12.00)->after('implementation_fee')->comment('VAT percentage from plan');
            }
            if (!Schema::hasColumn('invoices', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('vat_percentage')->comment('Amount before VAT');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['vat_percentage', 'subtotal']);
        });
    }
};
