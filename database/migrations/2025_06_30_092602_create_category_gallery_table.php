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
        Schema::create('category_gallery', function (Blueprint $table) {
            $table->uuid('gallery_id');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');

            $table->foreign('gallery_id')->references('id')->on('gallery')->onDelete('cascade');
            $table->primary(['gallery_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_gallery');
    }
};
