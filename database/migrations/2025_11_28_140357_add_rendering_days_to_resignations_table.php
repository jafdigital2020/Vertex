<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('resignations', function (Blueprint $table) {
        $table->integer('added_rendering_days')->default(0)->after('resignation_date');
    });
}

public function down()
{
    Schema::table('resignations', function (Blueprint $table) {
        $table->dropColumn('added_rendering_days');
    });
}


};
