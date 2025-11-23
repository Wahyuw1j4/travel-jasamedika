<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ticket - {{ $booking->booking_code }}</title>
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; color: #222; }
    .container { max-width: 800px; margin: 0 auto; padding: 24px; }
    .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px }
    .title { font-size:18px; font-weight:700 }
    .muted { color:#666; font-size:13px }
    .box { border:1px solid #e5e7eb; padding:12px; border-radius:6px; margin-bottom:12px }
    table { width:100%; border-collapse:collapse }
    th, td { text-align:left; padding:6px 8px }
    .total { font-weight:700; font-size:16px }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div>
        <div class="title">Tiket - {{ $booking->booking_code }}</div>
        <div class="muted">{{ $booking->travel->name }} • {{ $booking->travel->origin }} → {{ $booking->travel->destination }}</div>
      </div>
      <div>
        <div class="muted">Tanggal</div>
        <div>{{ \Carbon\Carbon::parse($booking->travel->departure_datetime)->translatedFormat('d M Y H:i') }}</div>
      </div>
    </div>

    <div class="box">
      <div class="muted">Pemesan</div>
      <div>{{ $booking->user->name }} — {{ $booking->user->email }}</div>
      <div class="muted">Kode Booking</div>
      <div>{{ $booking->booking_code }}</div>
    </div>

    <div class="box">
      <div class="muted">Penumpang</div>
      <table>
        <thead>
          <tr><th>#</th><th>Nama</th><th>NIK</th><th>Harga</th></tr>
        </thead>
        <tbody>
          @foreach(($booking->details ?? []) as $i => $d)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $d->penumpang_name }}</td>
            <td>{{ $d->penumpang_nik }}</td>
            <td>{{ number_format($d->price ?? 0, 0, ',', '.') }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center;">
      <div class="muted">Total</div>
      <div class="total">Rp {{ number_format($booking->total_price ?? 0, 0, ',', '.') }}</div>
    </div>

    <div style="margin-top:18px; font-size:12px; color:#555">
      Terima kasih telah memesan. Harap tunjukkan tiket ini saat check-in.
    </div>
  </div>
</body>
</html>
