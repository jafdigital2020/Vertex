<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeployedFieldsToAssetsDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('assets_details', function (Blueprint $table) {
            $table->unsignedBigInteger('deployed_to')->nullable()->after('asset_id');  
            $table->date('deployed_date')->nullable()->after('deployed_to');
        });
    }

    public function down()
    {
        Schema::table('assets_details', function (Blueprint $table) {
            $table->dropColumn(['deployed_to', 'deployed_date']);
        });
    }
}
