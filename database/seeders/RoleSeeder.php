<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Role::count() === 0) {
            Role::insert([
                ['name' => 'superadmin', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'guest', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}
