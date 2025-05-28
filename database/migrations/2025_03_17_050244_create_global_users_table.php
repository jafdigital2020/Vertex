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
        Schema::create('global_users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('organization_code')->nullable(); // Organization Code
            $table->enum('role', ['super_admin', 'tenant_admin'])->default('tenant_admin');
            $table->rememberToken();
            $table->timestamps();

            // Foreign Key Constraint (Optional)
            $table->foreign('organization_code')
                ->references('code')
                ->on('organizations')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_users');
    }
};
