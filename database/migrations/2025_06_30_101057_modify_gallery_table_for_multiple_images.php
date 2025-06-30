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
        Schema::table('gallery', function (Blueprint $table) {
            $table->json('images')->nullable();
            $table->dropColumn(['imageUrl', 'public_id', 'alt', 'mimeType', 'size']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gallery', function (Blueprint $table) {
            $table->dropColumn('images');
            $table->string('imageUrl')->nullable();
            $table->string('public_id')->nullable();
            $table->string('alt')->nullable();
            $table->string('mimeType')->nullable();
            $table->integer('size')->nullable();
        });
    }
};
