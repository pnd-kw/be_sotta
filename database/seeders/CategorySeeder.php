<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            'Plakat',
            'Perlengkapan Wisuda',
            'Medali & Emblem',
            'Piala & Trophy',
            'Souvenir Miniatur',
            'Kerajinan Logam',
            'Artwork',
            'Souvenir Perusahaan',
        ];

        foreach ($categories as $name) {
            DB::table('categories')->insert([
                'name' => $name,
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
