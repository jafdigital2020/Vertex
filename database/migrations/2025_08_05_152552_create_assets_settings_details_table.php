<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsSettingsDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('assets_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->string('order_no')->nullable();
            $table->enum('asset_condition', ['New', 'Good', 'Damaged', 'Under Maintenance'])->nullable();
            $table->enum('status', ['Available', 'Deployed', 'Return'])->nullable();
            $table->timestamps(); 
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('assets_details');
    }
}
