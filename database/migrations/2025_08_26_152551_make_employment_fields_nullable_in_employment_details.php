<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employment_details', function (Blueprint $table) {
            //
            $table->string('employment_type')->nullable()->change();    
            $table->unsignedBigInteger('department_id')->nullable()->change();
            $table->unsignedBigInteger('designation_id')->nullable()->change();
            $table->string('employment_status')->nullable()->change();
            $table->date('date_hired')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment_details', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('department_id')->nullable(false)->change();
            $table->unsignedBigInteger('designation_id')->nullable(false)->change();
            $table->string('employment_type')->nullable(false)->change();
            $table->string('employment_status')->nullable(false)->change();
            $table->date('date_hired')->nullable(false)->change();
        });
    }
};
