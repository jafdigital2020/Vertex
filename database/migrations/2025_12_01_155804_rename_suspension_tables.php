<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{ 
    
    public function up()
    {
        Schema::rename('suspensions', 'violations');
        Schema::rename('suspension_hr', 'violation_hr');
        Schema::rename('suspension_actions', 'violation_actions');
    }

    public function down()
    {
        Schema::rename('violations', 'suspensions');
        Schema::rename('violation_hr', 'suspension_hr');
        Schema::rename('violation_actions', 'suspension_actions');
    }

};
