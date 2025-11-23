<?php

namespace App\Http\Controllers;

use App\Models\Travel;
use App\Models\Booking;
use App\Models\BookingDetail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index()
    {
        return response()->json(Booking::with(['travel', 'user'])->latest()->paginate(15));
    }

    public function show(Booking $booking)
    {
        return response()->json($booking->load(['travel', 'user', 'payment']));
    }

    public function store(Request $request)
    {
        // Only passengers may create bookings via API
        $user = Auth::user();
        if (!$user || $user->role === 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'travel_id' => 'required|exists:travels,id',
            'ticket_count' => 'required|integer|min:1',
            'details' => 'sometimes|array',
            'details.*.penumpang_name' => 'required_with:details|string',
            'details.*.penumpang_email' => 'nullable|email',
            'details.*.penumpang_phone' => 'nullable|string',
            'details.*.penumpang_nik' => 'required_with:details|string',
        ]);

        $travel = Travel::findOrFail($data['travel_id']);

        // compute available seats from quota_total minus already booked tickets
        $booked = Booking::where('travel_id', $travel->id)->sum('ticket_count');
        $available = max(0, (int)$travel->quota_total - (int)$booked);
        if ($data['ticket_count'] > $available) {
            return response()->json(['message' => 'Not enough quota'], 422);
        }

        // create booking for the authenticated user
        $bookingData = [
            'user_id' => $user->id,
            'travel_id' => $travel->id,
            'ticket_count' => $data['ticket_count'],
            'booking_code' => 'BKG' . strtoupper(Str::random(6)),
            'total_price' => $travel->price * $data['ticket_count'],
            'status' => 'waiting_payment',
        ];

        $booking = Booking::create($bookingData);

        // insert booking details if provided, otherwise create generic passengers
        DB::table('booking_details')->where('booking_id', $booking->id)->delete();

        $details = [];
        if (!empty($data['details']) && is_array($data['details'])) {
            foreach ($data['details'] as $d) {
                $details[] = [
                    'travel_id' => $travel->id,
                    'booking_id' => $booking->id,
                    'penumpang_name' => $d['penumpang_name'] ?? 'Penumpang',
                    'penumpang_email' => $d['penumpang_email'] ?? null,
                    'penumpang_phone' => $d['penumpang_phone'] ?? null,
                    'penumpang_nik' => $d['penumpang_nik'] ?? null,
                    'price' => (int) $travel->price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        } else {
            // generate placeholder passengers
            for ($i = 0; $i < $data['ticket_count']; $i++) {
                $nik = substr(str_shuffle(str_repeat('0123456789', 4)), 0, 16);
                $details[] = [
                    'travel_id' => $travel->id,
                    'booking_id' => $booking->id,
                    'penumpang_name' => 'Penumpang ' . ($i + 1),
                    'penumpang_email' => null,
                    'penumpang_phone' => null,
                    'penumpang_nik' => $nik,
                    'price' => (int) $travel->price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($details)) {
            DB::table('booking_details')->insert($details);
        }

        return response()->json($booking->load('travel'), 201);
    }

    public function getBookingByTravelId($travelId)
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            $bookings = Booking::where('travel_id', $travelId)->with('user')->get();
        } else {
            // passenger: only their own bookings for the given travel
            $bookings = Booking::where('travel_id', $travelId)
                ->where('user_id', $user->id)
                ->with('user', 'details', 'payment')
                ->get();
        }

        return response()->json($bookings);
    }

    public function getBookingDetails($travelId, $bookingId)
    {
        $user = Auth::user();
        $booking = BookingDetail::where('booking_id', $bookingId)
            ->where('travel_id', $travelId)
            ->get();

        if ($user->role !== 'admin' && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($booking);
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => 'nullable|in:pending,waiting_payment,paid,cancelled',
        ]);

        $booking->update($data);
        return response()->json($booking);
    }

    public function destroy(Booking $booking)
    {
        // restore quota when deleting a booking that wasn't cancelled? leave simple: delete
        $booking->delete();
        return response()->json(null, 204);
    }
}
