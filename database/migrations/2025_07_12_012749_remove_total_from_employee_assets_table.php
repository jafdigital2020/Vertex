<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employee_assets', function (Blueprint $table) {
            $table->dropColumn('total');
        });
    }

    public function down(): void
    {
        Schema::table('employee_assets', function (Blueprint $table) {
            $table->decimal('total', 15, 2)->nullable(); // Adjust type/precision if needed
        });
    }
};
