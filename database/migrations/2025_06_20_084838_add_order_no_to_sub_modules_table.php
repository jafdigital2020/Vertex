<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_modules', function (Blueprint $table) {
            $table->unsignedInteger('order_no')->nullable()->after('module_id');
        });
    }

    public function down(): void
    {
        Schema::table('sub_modules', function (Blueprint $table) {
            $table->dropColumn('order_no');
        });
    }
};

