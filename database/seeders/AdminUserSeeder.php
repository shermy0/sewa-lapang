<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@sewalap.id'],
            [
                'name' => 'Admin SewaLap',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ],
        );
    }
}
