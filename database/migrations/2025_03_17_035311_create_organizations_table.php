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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Organization name
            $table->string('code')->unique(); // Unique organization code
            $table->unsignedBigInteger('package_id'); // Subscription package
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active'); // Organization status
            $table->timestamps();

            // Foreign key for packages
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
