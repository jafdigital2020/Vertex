<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{  
    public function up(): void
    {
        Schema::create('default_access', function (Blueprint $table) {
            $table->id();
            $table->longText('submodule_ids')->nullable();
            $table->date('effectivity_date')->nullable();
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('default_access');
    }
};
