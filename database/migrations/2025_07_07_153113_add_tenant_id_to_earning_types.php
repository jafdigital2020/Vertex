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
        Schema::table('earning_types', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');

            // Add foreign key constraint
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('earning_types', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['tenant_id']);

            // Drop the tenant_id column
            $table->dropColumn('tenant_id');
        });
    }
};
