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
        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('implementation_fee', 15, 2)->default(0)->after('price_per_license');
            $table->integer('vat_percentage')->default(0)->after('implementation_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('implementation_fee');
            $table->dropColumn('vat_percentage');
        });
    }
};
