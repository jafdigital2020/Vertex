<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('violations', function (Blueprint $table) {
        $table->enum('status', [
            'pending',
            'awaiting_reply',
            'under_investigation',
            'for_dam_issuance',
            'dam_issued',
            'completed'
        ])->default('pending')->change();
    });
}

public function down()
{
    Schema::table('violations', function (Blueprint $table) {
        $table->enum('status', [
            'pending',
            'awaiting_reply',
            'under_investigation',
            'for_dam_issuance',
            'suspended',
            'completed'
        ])->default('pending')->change();
    });
}

};
