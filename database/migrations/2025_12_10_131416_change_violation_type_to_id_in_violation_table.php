<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('violations', function (Blueprint $table) { 
            $table->dropColumn('violation_type');
        });

        Schema::table('violations', function (Blueprint $table) { 
            $table->unsignedBigInteger('violation_type_id')->nullable();
 
            $table->foreign('violation_type_id')->references('id')->on('violation_types');
        });
    }

    public function down()
    {
        Schema::table('violations', function (Blueprint $table) { 
            $table->dropColumn('violation_type_id');
            $table->enum('violation_type', ['with_pay', 'without_pay'])->nullable();
        });
    }

};
