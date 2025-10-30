<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets_details_remarks', function (Blueprint $table) {
            $table->integer('item_no')->nullable()->after('asset_detail_id');
        });
    }

    public function down(): void
    {
        Schema::table('assets_details_remarks', function (Blueprint $table) {
            $table->dropColumn('item_no');
        });
    }
};
