<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEnumsInAssetsDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('assets_details', function (Blueprint $table) { 
            $table->enum('asset_condition', [
                'Brand New',
                'Good Working Condition',
                'Under Maintenance',
                'Defective',
                'Unserviceable'
            ])->nullable()->change();

            $table->enum('status', [
                'Available',
                'Deployed',
                'Return',
                'For Disposal',
                'Disposed'
            ])->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('assets_details', function (Blueprint $table) {
            $table->enum('asset_condition', [
                'New', 
                'Good', 
                'Damaged', 
                'Under Maintenance'
            ])->nullable()->change();

            $table->enum('status', [
                'Available', 
                'Deployed', 
                'Return'
            ])->nullable()->change();
        });
    }
}
