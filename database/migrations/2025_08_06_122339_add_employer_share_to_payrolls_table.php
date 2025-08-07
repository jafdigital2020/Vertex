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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('sss_contribution_employer', 15, 2)->default(0)->after('sss_contribution');
            $table->decimal('philhealth_contribution_employer', 15, 2)->default(0)->after('philhealth_contribution');
            $table->decimal('pagibig_contribution_employer', 15, 2)->default(0)->after('pagibig_contribution');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('sss_contribution_employer');
            $table->dropColumn('philhealth_contribution_employer');
            $table->dropColumn('pagibig_contribution_employer');
        });
    }
};
