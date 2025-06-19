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
        Schema::table('customer_review', function (Blueprint $table) {
            $table->string('gender')->nullable();
            $table->string('token')->nullable();
            $table->timestamp('token_expires_at')->nullable();

            $table->unique('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_review', function (Blueprint $table) {
            $table->dropUnique(['token']);
            $table->dropColumn(['gender', 'token', 'token_expires_at']);
        });
    }
};
