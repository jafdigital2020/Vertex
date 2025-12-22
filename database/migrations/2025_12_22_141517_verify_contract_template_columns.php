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
        Schema::table('contract_templates', function (Blueprint $table) {
            // Check and add pdf_template_path if it doesn't exist
            if (!Schema::hasColumn('contract_templates', 'pdf_template_path')) {
                $table->string('pdf_template_path')->nullable()->after('content');
            }
            
            // Check and add pdf_fillable_content if it doesn't exist
            if (!Schema::hasColumn('contract_templates', 'pdf_fillable_content')) {
                $table->longText('pdf_fillable_content')->nullable()->after('pdf_template_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_templates', function (Blueprint $table) {
            if (Schema::hasColumn('contract_templates', 'pdf_template_path')) {
                $table->dropColumn('pdf_template_path');
            }
            if (Schema::hasColumn('contract_templates', 'pdf_fillable_content')) {
                $table->dropColumn('pdf_fillable_content');
            }
        });
    }
};
