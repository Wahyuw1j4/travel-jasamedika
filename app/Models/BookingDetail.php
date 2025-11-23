<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'penumpang_name',
        'penumpang_email',
        'penumpang_phone',
        'penumpang_nik',
        'price',
    ];

    protected $casts = [
        'price' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
