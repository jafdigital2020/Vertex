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
        Schema::table('payments', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('payments', 'meta')) {
                $table->json('meta')->nullable()->after('raw_response');  
            }
            if (!Schema::hasColumn('payments', 'applied_at')) {
                $table->timestamp('applied_at')->nullable()->after('paid_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'meta')) {
                $table->dropColumn('meta');
            }
            if (Schema::hasColumn('payments', 'applied_at')) {
                $table->dropColumn('applied_at');
            }
        });
    }
};
