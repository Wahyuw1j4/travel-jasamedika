<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Travel extends Model
{
    use HasFactory;
    protected $table = 'travels';
    protected $fillable = [
        'name',
        'origin',
        'destination',
        'departure_datetime',
        'quota_total',
        'price',
    ];

    protected $casts = [
        'departure_datetime' => 'datetime',
        'price' => 'integer',
        'quota_total' => 'integer'
    ];

    public function bookings()
    {
        // Eager-load booking details when retrieving bookings from a travel
        return $this->hasMany(Booking::class)->with('details');
    }

    /**
     * Convenience relation method to get bookings with details (explicit name).
     */
    public function bookingsWithDetails()
    {
        return $this->hasMany(Booking::class)->with('details');
    }
}
