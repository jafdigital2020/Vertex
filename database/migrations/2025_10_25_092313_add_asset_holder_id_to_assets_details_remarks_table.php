<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::table('assets_details_remarks', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_holder_id')->nullable()->after('asset_detail_id');
        });
    }

    public function down()
    {
        Schema::table('assets_details_remarks', function (Blueprint $table) {
            $table->dropColumn('asset_holder_id');
        });
    }

};
