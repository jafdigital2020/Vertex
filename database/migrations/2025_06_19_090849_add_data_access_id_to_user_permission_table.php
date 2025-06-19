<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_permission', function (Blueprint $table) {
            $table->unsignedBigInteger('data_access_id')->nullable()->after('role_id');
        });
    }

    public function down()
    {
        Schema::table('user_permission', function (Blueprint $table) {
            $table->dropColumn('data_access_id');
        });
    }
};
