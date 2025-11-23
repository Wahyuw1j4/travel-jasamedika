<?php

namespace App\Http\Controllers;

use App\Models\Travel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TravelController extends Controller
{
    public function index()
    {
        return response()->json(Travel::orderBy('departure_datetime')->paginate(15));
    }

    public function show(Travel $travel)
    {
        return response()->json($travel);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string',
            'origin' => 'nullable|string',
            'destination' => 'required|string',
            'departure_datetime' => 'required|date',
            'quota_total' => 'required|integer',
            'price' => 'required|integer',
        ]);

        $travel = Travel::create($data);

        if ($request->expectsJson()) {
            return response()->json($travel, 201);
        }

        return redirect()->route('jadwal.travel');
    }

    public function update(Request $request, Travel $travel)
    {
        $data = $request->validate([
            'name' => 'nullable|string',
            'origin' => 'nullable|string',
            'destination' => 'required|string',
            'departure_datetime' => 'required|date',
            'quota_total' => 'required|integer',
            'price' => 'required|integer',
        ]);

        $travel->update($data);
        return response()->json($travel);
    }

    public function destroy(Travel $travel)
    {
        $travel->delete();
        return response()->json(null, 204);
    }

    public function page(Request $request)
    {
        $query = Travel::query();

        if ($request->has('departure_date')) {
            try {
                $dd = $request->query('departure_date');
                $dateOnly = Carbon::parse($dd)->toDateString();
                $query->whereDate('departure_datetime', $dateOnly);
            } catch (\Throwable $e) {
            }
        }

        if ($request->has('origin')) {
            $origin = $request->query('origin');
            if (is_array($origin)) {
                $origin = $origin['value'] ?? $origin['label'] ?? null;
            }
            if ($origin) {
                $query->where('origin', 'like', '%' . $origin . '%');
            }
        }

        if ($request->has('destination')) {
            $destination = $request->query('destination');
            if (is_array($destination)) {
                $destination = $destination['value'] ?? $destination['label'] ?? null;
            }
            if ($destination) {
                $query->where('destination', 'like', '%' . $destination . '%');
            }
        }

        // include sum of booked tickets (exclude cancelled) to compute remaining seats on frontend
        $query->withSum(['bookings as booked_tickets_sum' => function($q) {
            $q->where('status', '!=', 'cancelled');
        }], 'ticket_count');

        $travel = $query->orderBy('departure_datetime')->paginate(10)->withQueryString();

        return Inertia::render('JadwalTravel', ['travel' => $travel]);
    }
    
    public function createPage()
    {
        return Inertia::render('Travel/Create');
    }

    public function editPage(Travel $travel)
    {
        return Inertia::render('Travel/Edit', ['travel' => $travel]);
    }
    public function showPage(Travel $travel)
    {
        $user = Auth::user();
        $bookings = $travel->bookings;
        return Inertia::render('Travel/Show', ['travel' => $travel, 'user' => $user, 'bookings' => $bookings]);
    }
}
