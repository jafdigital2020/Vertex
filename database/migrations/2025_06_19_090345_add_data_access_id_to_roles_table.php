<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('role', function (Blueprint $table) {
            $table->unsignedBigInteger('data_access_id')->nullable()->after('tenant_id');
        });
    }

    public function down()
    {
        Schema::table('role', function (Blueprint $table) {
            $table->dropColumn('data_access_id');
        });
    }

};
