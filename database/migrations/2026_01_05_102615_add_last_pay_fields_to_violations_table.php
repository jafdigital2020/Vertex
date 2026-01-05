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
         Schema::table('violations', function (Blueprint $table) {
            $table->boolean('last_pay_status')
                  ->default(false)
                  ->after('termination_date');

            $table->date('last_pay_date')
                  ->nullable()
                  ->after('last_pay_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            //
        });
    }
};
