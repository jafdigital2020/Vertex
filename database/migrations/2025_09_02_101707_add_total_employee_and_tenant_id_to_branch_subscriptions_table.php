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
        Schema::table('branch_subscriptions', function (Blueprint $table) {
            //
            $table->unsignedInteger('total_employee')->nullable()->after('notes');
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('set null')->after('total_employee');
            $table->string('billing_period')->nullable()->after('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_subscriptions', function (Blueprint $table) {
            //
            $table->dropColumn('total_employee');
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
            $table->dropColumn('billing_period');
        });
    }
};
