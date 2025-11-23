<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class JadwalTravelController extends Controller
{
    public function index()
    {
        return Inertia::render('JadwalTravel');
    }
}
