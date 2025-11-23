<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;

class TicketController extends Controller
{
    /**
     * Render ticket as HTML (view)
     */
    public function show(Request $request, $id)
    {
        $booking = Booking::with(['travel', 'details', 'user', 'payment'])->findOrFail($id);

        // only allow owner or admin
        $user = $request->user();
        if (!$user || ($user->role !== 'admin' && $booking->user_id !== $user->id)) {
            abort(403);
        }

        return view('tickets.show', compact('booking'));
    }

    /**
     * Return ticket as PDF download if PDF generator available, otherwise fallback to HTML download
     */
    public function downloadPdf(Request $request, $id)
    {
        $booking = Booking::with(['travel', 'details', 'user', 'payment'])->findOrFail($id);

        $user = $request->user();
        if (!$user || ($user->role !== 'admin' && $booking->user_id !== $user->id)) {
            abort(403);
        }

        // If barryvdh/laravel-dompdf is installed, use it
        if (class_exists('\\Barryvdh\\DomPDF\\Facade\\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tickets.show', compact('booking'));
            $filename = 'ticket-' . ($booking->booking_code ?? $booking->id) . '.pdf';
            return $pdf->download($filename);
        }

        // Fallback: return HTML view as attachment (browser will download as .html)
        $html = view('tickets.show', compact('booking'))->render();
        $filename = 'ticket-' . ($booking->booking_code ?? $booking->id) . '.html';
        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
