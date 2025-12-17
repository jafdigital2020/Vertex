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
      Schema::table('violations', function (Blueprint $table) {
            $table->date('verbal_reprimand_date')->nullable()->after('disciplinary_action');
            $table->date('written_reprimand_date')->nullable()->after('verbal_reprimand_date');
            $table->date('termination_date')->nullable()->after('return_to_work_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            
        });
    }
};
