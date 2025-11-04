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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->date('payroll_period_start')->nullable()->change();
            $table->date('payroll_period_end')->nullable()->change();
            $table->date('payment_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->date('payroll_period_start')->nullable(false)->change();
            $table->date('payroll_period_end')->nullable(false)->change();
            $table->date('payment_date')->nullable(false)->change();
        });
    }
};
