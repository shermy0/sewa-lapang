<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'admin@sewalap.id'],
            [
                'name' => 'Admin SewaLap',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'aktif',
            ]
        );

        User::firstOrCreate(
            ['email' => 'pemilik@sewalap.id'],
            [
                'name' => 'Pemilik Contoh',
                'password' => Hash::make('password'),
                'role' => 'pemilik',
                'status' => 'aktif',
            ]
        );

        User::firstOrCreate(
            ['email' => 'penyewa@sewalap.id'],
            [
                'name' => 'Penyewa Contoh',
                'password' => Hash::make('password'),
                'role' => 'penyewa',
                'status' => 'aktif',
            ]
        );

        $this->call([
            AdminUserSeeder::class,
            SampleDataSeeder::class,
        ]);
    }
}
