<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('password_otps', function (Blueprint $table) {
            // Drop the old otp column if it exists
            if (Schema::hasColumn('password_otps', 'otp')) {
                $table->dropColumn('otp');
            }

            // Add otp_hash column (string, nullable false)
            if (!Schema::hasColumn('password_otps', 'otp_hash')) {
                $table->string('otp_hash');
            }

            // Make sure expires_at exists
            if (!Schema::hasColumn('password_otps', 'expires_at')) {
                $table->timestamp('expires_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('password_otps', function (Blueprint $table) {
            if (Schema::hasColumn('password_otps', 'otp_hash')) {
                $table->dropColumn('otp_hash');
            }

            if (!Schema::hasColumn('password_otps', 'otp')) {
                $table->string('otp')->nullable();
            }
        });
    }
};
