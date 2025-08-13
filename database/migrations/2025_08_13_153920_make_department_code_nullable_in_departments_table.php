<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            // Make the column nullable while keeping it unique
            $table->string('department_code')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('department_code')->nullable(false)->unique()->change();
        });
    }
};
