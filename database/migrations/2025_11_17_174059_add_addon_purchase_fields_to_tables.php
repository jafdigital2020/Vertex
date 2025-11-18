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
        // Add fields to branch_addons table
        Schema::table('branch_addons', function (Blueprint $table) {
            if (!Schema::hasColumn('branch_addons', 'billing_cycle')) {
                $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly')->after('end_date');
            }
            if (!Schema::hasColumn('branch_addons', 'price_paid')) {
                $table->decimal('price_paid', 10, 2)->nullable()->after('billing_cycle');
            }
            if (!Schema::hasColumn('branch_addons', 'invoice_id')) {
                $table->unsignedBigInteger('invoice_id')->nullable()->after('price_paid');
                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            }
            if (!Schema::hasColumn('branch_addons', 'metadata')) {
                $table->json('metadata')->nullable()->after('invoice_id');
            }
        });

        // Add fields to addons table
        Schema::table('addons', function (Blueprint $table) {
            if (!Schema::hasColumn('addons', 'features')) {
                $table->json('features')->nullable()->after('description');
            }
            if (!Schema::hasColumn('addons', 'icon')) {
                $table->string('icon', 50)->default('puzzle')->after('features');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove fields from branch_addons table
        Schema::table('branch_addons', function (Blueprint $table) {
            if (Schema::hasColumn('branch_addons', 'metadata')) {
                $table->dropColumn('metadata');
            }
            if (Schema::hasColumn('branch_addons', 'invoice_id')) {
                $table->dropForeign(['invoice_id']);
                $table->dropColumn('invoice_id');
            }
            if (Schema::hasColumn('branch_addons', 'price_paid')) {
                $table->dropColumn('price_paid');
            }
            if (Schema::hasColumn('branch_addons', 'billing_cycle')) {
                $table->dropColumn('billing_cycle');
            }
        });

        // Remove fields from addons table
        Schema::table('addons', function (Blueprint $table) {
            if (Schema::hasColumn('addons', 'icon')) {
                $table->dropColumn('icon');
            }
            if (Schema::hasColumn('addons', 'features')) {
                $table->dropColumn('features');
            }
        });
    }
};
