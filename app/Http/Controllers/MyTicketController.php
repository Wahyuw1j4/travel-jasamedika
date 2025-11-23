<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Booking;

class MyTicketController extends Controller
{
    /**
     * Display a listing of the authenticated user's bookings.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $bookings = Booking::with(['travel', 'details', 'payment'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('MyTickets', [
            'bookings' => $bookings,
        ]);
    }
}
