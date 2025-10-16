<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tiket {{ $pemesanan->kode_tiket }}</title>
    <link rel="stylesheet" href="{{ public_path('css/tiket.css') }}">
    <style>
        body { font-family: sans-serif; }
        .ticket { display: flex; border: 2px dashed #41A67E; border-radius: 12px; overflow: hidden; }
        .ticket-left { width: 35%; background: #41A67E; color: white; padding: 20px; }
        .ticket-right { width: 65%; background: white; padding: 20px; }
        .divider { border-left: 2px dashed #ccc; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="ticket-left">
            <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($pemesanan->jadwal->tanggal)->format('d M Y') }}</p>
            <p><strong>Jam:</strong> {{ $pemesanan->jadwal->jam_mulai }} - {{ $pemesanan->jadwal->jam_selesai }}</p>
        </div>
        <div class="divider"></div>
        <div class="ticket-right">
            <p><strong>Lapangan:</strong> {{ $pemesanan->lapangan->nama_lapangan }}</p>
            <p><strong>Kode Tiket:</strong> {{ $pemesanan->kode_tiket }}</p>
            <div style="margin-top:20px;">
                {!! DNS1D::getBarcodeHTML($pemesanan->kode_tiket, 'C128') !!}
            </div>
        </div>
    </div>
</body>
</html>
