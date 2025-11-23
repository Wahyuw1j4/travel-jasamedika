<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\TravelSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'wahyuwijaya@gmail.com'],
            ['name' => 'Wahyu Wijaya', 'password' => bcrypt('wahyu123'), 'role' => 'admin']
        );
        User::firstOrCreate(
            ['email' => 'penumpang1@gmail.com'],
            ['name' => 'Penumpang Satu', 'password' => bcrypt('penumpang123'), 'role' => 'passenger']
        );
        User::firstOrCreate(
            ['email' => 'penumpang2@gmail.com'],
            ['name' => 'Penumpang Dua', 'password' => bcrypt('penumpang123'), 'role' => 'passenger']
        );
        $this->call(TravelSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(BookingSeeder::class);
    }
}
