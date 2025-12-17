<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        DB::table('violation_attachments')
        ->whereNotIn('attachment_type', [
            'information_report',
            'nowe',
            'employee_reply',
            'dam',
            'other',
            'verbal_reprimand_file',
            'written_reprimand_file',
            'suspension_file',
            'termination_file'
        ])
        ->update(['attachment_type' => 'other']);
        
        Schema::table('violation_attachments', function (Blueprint $table) {
            $table->enum('attachment_type', [
                'information_report',
                'nowe',
                'employee_reply',
                'dam',
                'other',
                'verbal_reprimand_file',
                'written_reprimand_file',
                'suspension_file',
                'termination_file'
            ])->change();
        });

    }
 
    public function down(): void
    {
        //
    }
};
