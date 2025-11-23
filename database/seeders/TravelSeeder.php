<?php

namespace Database\Seeders;

use App\Models\Travel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TravelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $destinations = [
            ['origin' => 'Jakarta', 'destination' => 'Bali'],
            ['origin' => 'Jakarta', 'destination' => 'Yogyakarta'],
            ['origin' => 'Jakarta', 'destination' => 'Surabaya'],
            ['origin' => 'Jakarta', 'destination' => 'Medan'],
            ['origin' => 'Jakarta', 'destination' => 'Makassar'],
            ['origin' => 'Jakarta', 'destination' => 'Lombok'],
            ['origin' => 'Jakarta', 'destination' => 'Malang'],
            ['origin' => 'Jakarta', 'destination' => 'Bandung'],
            ['origin' => 'Jakarta', 'destination' => 'Banda Aceh'],
            ['origin' => 'Jakarta', 'destination' => 'Pontianak'],
            ['origin' => 'Jakarta', 'destination' => 'Palembang'],
            ['origin' => 'Jakarta', 'destination' => 'Kupang'],
            ['origin' => 'Jakarta', 'destination' => 'Manado'],
            ['origin' => 'Jakarta', 'destination' => 'Ambon'],
            ['origin' => 'Jakarta', 'destination' => 'Padang'],
            ['origin' => 'Jakarta', 'destination' => 'Balikpapan'],
            ['origin' => 'Jakarta', 'destination' => 'Ternate'],
            ['origin' => 'Jakarta', 'destination' => 'Raja Ampat'],
            ['origin' => 'Jakarta', 'destination' => 'Belitung'],
            ['origin' => 'Jakarta', 'destination' => 'Semarang'],
        ];

        foreach ($destinations as $i => $d) {
            $attrs = [
                'origin' => $d['origin'],
                'destination' => $d['destination'],
                'departure_datetime' => Carbon::now()->addDays(3 + $i * 2)->toDateTimeString(),
            ];

            $values = [
                'name' => ($i + 1) . '. Trip to ' . $d['destination'],
                'quota_total' => 40 + ($i % 5) * 10,
                'price' => 100000 + ($i * 25000),
            ];

            // idempotent: update existing travel with same origin/destination/departure, or create
            Travel::updateOrCreate($attrs, array_merge($attrs, $values));
        }
    }
}
