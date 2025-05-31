<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        User::create([
            'name' => 'Alfian Persie',
            'email' => 'alfian@example.com',
            'password' => Hash::make('password'),
            'gender' => true,
            'avatar' => null,
            'phone_number' => '085774801409',
            'email_verified_at' => now(),
            'role_id' => $adminRole?->id,
        ]);
    }
}
