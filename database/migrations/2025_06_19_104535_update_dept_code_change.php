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
        Schema::table('departments', function (Blueprint $table) {
            // Make department_code nullable and drop previous unique
            $table->string('department_code')->nullable()->change();

            // Drop old unique index if exists
            $table->dropUnique('departments_department_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('department_code')->unique(false)->nullable(false)->change();
            $table->unique('department_code');
        });
    }
};
