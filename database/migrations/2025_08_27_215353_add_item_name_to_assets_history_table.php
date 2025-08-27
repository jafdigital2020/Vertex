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
        Schema::table('assets_history', function (Blueprint $table) {
            $table->string('item_name')->nullable()->after('name');  
        });
    }
 
    public function down(): void
    {
        Schema::table('assets_history', function (Blueprint $table) {
            $table->dropColumn('item_name');
        });
    }
};
