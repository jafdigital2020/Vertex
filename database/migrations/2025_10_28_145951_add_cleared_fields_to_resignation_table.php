<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resignations', function (Blueprint $table) { 
            $table->boolean('cleared_status')->default(0)->after('resignation_date');  
            $table->unsignedBigInteger('cleared_by')->nullable()->after('cleared_status');
            $table->dateTime('cleared_date')->nullable()->after('cleared_by');
        });
    }

    public function down(): void
    {
        Schema::table('resignations', function (Blueprint $table) {
            $table->dropColumn(['cleared_status', 'cleared_by', 'cleared_date']);
        });
    }
};
