<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $travels = Travel::all();
        if ($travels->isEmpty()) return;

        $users = User::all();
        if ($users->isEmpty()) return;

        // Allowed statuses must match the bookings table enum
        $statuses = ['pending', 'waiting_payment', 'paid', 'cancelled'];

        foreach ($travels as $travel) {
            // desired number of bookings per travel (we'll pick that many distinct users)
            $desired = rand(1, 5);

            // pick distinct users (if available)
            $selectedUsers = $users->shuffle()->take(min($desired, $users->count()));

            DB::transaction(function () use ($travel, $selectedUsers, $statuses) {
                foreach ($selectedUsers as $user) {
                            // choose ticket count not exceeding available quota (use quota_total as simple upper bound)
                            $maxTickets = max(1, min(5, (int) $travel->quota_total ?: 5));
                    $ticketCount = rand(1, $maxTickets);

                    $totalPrice = $ticketCount * (int) $travel->price;

                    // Upsert-like behavior: find existing booking for this user+travel, update or create
                    $booking = Booking::firstOrNew([
                        'user_id' => $user->id,
                        'travel_id' => $travel->id,
                    ]);

                    $oldCount = $booking->exists ? (int) $booking->ticket_count : 0;

                    if (!$booking->exists) {
                        // only generate booking code on create
                        $booking->booking_code = 'BKG' . strtoupper(Str::random(8));
                    }

                    $booking->ticket_count = $ticketCount;
                    $booking->total_price = $totalPrice;
                    $booking->status = $statuses[array_rand($statuses)];
                    $booking->save();

                    // Maintain booking_details records: remove existing details and recreate per ticket
                    // (use column names matching migration: penumpang_name, penumpang_email, penumpang_phone, penumpang_nik, price)
                    DB::table('booking_details')->where('booking_id', $booking->id)->delete();

                    $details = [];
                    for ($d = 0; $d < $ticketCount; $d++) {
                        // generate a pseudo NIK (16 digits)
                        $nik = substr(str_shuffle(str_repeat('0123456789', 4)), 0, 16);

                        $details[] = [
                            'travel_id' => $travel->id,
                            'booking_id' => $booking->id,
                            'penumpang_name' => $user->name ?? ('Penumpang ' . ($d + 1)),
                            'penumpang_email' => $user->email ?? null,
                            'penumpang_phone' => null,
                            'penumpang_nik' => $nik,
                            'price' => (int) $travel->price,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    if (!empty($details)) {
                        DB::table('booking_details')->insert($details);
                    }

                    // Note: `quota_remaining` column removed. Seeder does not adjust travel quotas here.
                }
            });
        }
    }
}
