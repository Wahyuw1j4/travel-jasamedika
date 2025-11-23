<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $target = 30;

        $existing = User::count();

        if ($existing >= $target) {
            return;
        }

        $toCreate = $target - $existing;
        User::factory()->count($toCreate)->create();
    }
}
