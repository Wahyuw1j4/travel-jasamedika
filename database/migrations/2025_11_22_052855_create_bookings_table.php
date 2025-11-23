<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();   // kode booking/invoice
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('travel_id')->constrained('travels')->onDelete('cascade');
            $table->unsignedInteger('ticket_count');   // jumlah kursi dipesan
            $table->unsignedBigInteger('total_price');
            $table->enum('status', ['pending','waiting_payment','paid','cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
