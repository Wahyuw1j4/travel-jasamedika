<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();      // nama jadwal, misal "Solo - Jakarta Pagi"
            $table->string('origin')->nullable();    // kota asal (opsional)
            $table->string('destination');           // kota tujuan
            $table->dateTime('departure_datetime');  // tanggal & jam berangkat
            $table->unsignedInteger('quota_total');      // kuota awal
            $table->unsignedBigInteger('price'); // harga per tiket (gunakan integer, mis. disimpan dalam sen)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travels');
    }
};
