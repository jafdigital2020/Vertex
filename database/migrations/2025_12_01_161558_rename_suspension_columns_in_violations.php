<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('violations', function (Blueprint $table) {
        $table->renameColumn('suspension_type', 'violation_type');
        $table->renameColumn('suspension_start_date', 'violation_start_date');
        $table->renameColumn('suspension_end_date', 'violation_end_date');
        $table->renameColumn('suspension_days', 'violation_days');
    });
}

public function down()
{
    Schema::table('violations', function (Blueprint $table) {
        $table->renameColumn('violation_type', 'suspension_type');
        $table->renameColumn('violation_start_date', 'suspension_start_date');
        $table->renameColumn('violation_end_date', 'suspension_end_date');
        $table->renameColumn('violation_days', 'suspension_days');
    });
}
};
