<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek apakah user superadmin sudah ada
        if (!User::where('email', 'sottaart@gmail.com')->exists()) {
            $superadminRole = Role::where('name', 'superadmin')->first();

            User::create([
                'name' => 'Sotta Art',
                'email' => 'sottaart@gmail.com',
                'password' => Hash::make('rahasia'),
                'gender' => true,
                'avatar' => null,
                'phone_number' => '08175457300',
                'email_verified_at' => now(),
                'role_id' => $superadminRole->id,
            ]);
        }
    }
}
