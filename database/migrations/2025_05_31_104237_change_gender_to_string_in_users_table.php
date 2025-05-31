<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah tipe kolom gender ke string
        Schema::table('users', function (Blueprint $table) {
            $table->string('gender')->change();
        });

        // Ubah data gender integer ke string
        DB::table('users')->where('gender', '1')->update(['gender' => 'male']);
        DB::table('users')->where('gender', '2')->update(['gender' => 'female']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan data gender string ke integer
        DB::table('users')->where('gender', 'male')->update(['gender' => 1]);
        DB::table('users')->where('gender', 'female')->update(['gender' => 2]);

        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('gender')->default(1)->change();
        });
    }
};
