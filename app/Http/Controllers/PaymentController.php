<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Display a paginated listing of payments.
     */
    public function index()
    {
        return response()->json(Payment::with('booking')->latest()->paginate(15));
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        return response()->json($payment->load('booking'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        // If file proof is provided, validate accordingly
        if ($request->hasFile('proof')) {
            $validated = $request->validate([
                'proof'      => ['required', 'file', 'image', 'max:5120'], // 5 MB
            ]);

            $booking = Booking::find($request['booking_id']);
            if (!$booking) {
                return response()->json(['message' => 'Booking not found'], 404);
            }

            $file = $request->file('proof');
            $path = $file->store('payments', 'public');

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'user_id'    => $user->id,
                'paid_amount' => $request->paid_amount,
                'status'     => 'confirmed',
                'proof_image' => $path,
            ]);

            $booking->update(['status' => 'paid']);

            // Use the asset helper to generate a public URL for files stored on the "public" disk
            $url = asset("storage/{$path}");

            return response()->json([
                'message' => 'Payment proof uploaded',
                'payment' => $payment,
                'url' => $url,
            ], 201);
        }
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'method' => 'nullable|string',
            'status' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        $payment->update($data);
        return response()->json($payment);
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response()->json(null, 204);
    }

    /**
     * Optional: render a reports page via Inertia (web route).
     */
    public function reports()
    {
        return Inertia::render('Reports');
    }
}
