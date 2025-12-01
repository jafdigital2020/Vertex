<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{ 
    Schema::table('violation_hr', function (Blueprint $table) {  
        $table->renameColumn('suspension_id', 'violation_id');
    });
  
 
    Schema::table('violation_actions', function (Blueprint $table) {  
        $table->renameColumn('suspension_id', 'violation_id');
    });
  
}

public function down()
{ 
    Schema::table('violation_hr', function (Blueprint $table) { 
        $table->renameColumn('violation_id', 'suspension_id');
    });
 
 
    Schema::table('violation_actions', function (Blueprint $table) { 
        $table->renameColumn('violation_id', 'suspension_id');
    });
 
}

};
