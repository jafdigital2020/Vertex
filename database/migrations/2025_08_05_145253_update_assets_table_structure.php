<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) { 
            if (Schema::hasColumn('assets', 'status')) {
                $table->dropColumn('status');
            } 
            $table->date('deployment_date')->nullable();
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('processor')->nullable(); 
        });
    }

    public function down()
    {
        Schema::table('assets', function (Blueprint $table) { 
            $table->dropColumn([
                'deployment_date',
                'model',
                'manufacturer',
                'serial_number',
                'processor',  
            ]);
  
        });
    }
};
