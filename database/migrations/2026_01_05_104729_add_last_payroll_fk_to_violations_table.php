<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('violations', function (Blueprint $table) { 
           
            $table->foreign('last_payroll_id')
                  ->references('id')
                  ->on('payrolls')
                  ->nullOnDelete(); 
        });
    }

    public function down(): void
    { 
    }
};
