<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalToEmployeeAssetsTable extends Migration
{
    public function up()
    {
        Schema::table('employee_assets', function (Blueprint $table) {
            $table->decimal('total', 12, 2)->after('price')->default(0);
        });
    }

    public function down()
    {
    }
}
