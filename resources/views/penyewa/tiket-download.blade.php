<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tiket {{ $pemesanan->kode_tiket }}</title>
    <style>
        @page { size: A4 landscape; margin: 20px; }
        body {
            font-family: Arial, sans-serif;
            color: #222;
            padding: 20px;
        }
        .ticket {
            border: 2px solid #41A67E;
            border-radius: 10px;
            padding: 20px;
            width: 100%;
        }
        h1 {
            color: #41A67E;
            font-size: 24px;
            text-align: center;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        td {
            padding: 8px;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background: #f6f7fb;
        }
        .barcode {
            text-align: center;
            margin-top: 20px;
        }
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <h1>Tiket Pemesanan Lapangan</h1>
        <table>
            <tr>
                <td><strong>Kode Tiket</strong></td>
                <td>{{ $pemesanan->kode_tiket }}</td>
            </tr>
            <tr>
                <td><strong>Status</strong></td>
                <td>{{ ucfirst($pemesanan->status) }}</td>
            </tr>
            <tr>
                <td><strong>Nama Penyewa</strong></td>
                <td>{{ $pemesanan->user->name ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Nama Lapangan</strong></td>
                <td>{{ $pemesanan->lapangan->nama_lapangan ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Kategori</strong></td>
                <td>{{ $pemesanan->lapangan->kategori ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Lokasi</strong></td>
                <td>{{ $pemesanan->lapangan->lokasi ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Section</strong></td>
                <td>{{ $pemesanan->jadwal->section->nama_section ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Main</strong></td>
                <td>
                    @php
                        if ($pemesanan->jadwal) {
                            \Carbon\Carbon::setLocale('id');
                            echo \Carbon\Carbon::parse($pemesanan->jadwal->tanggal)->translatedFormat('l, d F Y');
                        } else {
                            echo '-';
                        }
                    @endphp
                </td>
            </tr>
            <tr>
                <td><strong>Jam</strong></td>
                <td>{{ $pemesanan->jadwal->jam_mulai ?? '-' }} - {{ $pemesanan->jadwal->jam_selesai ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Harga Sewa</strong></td>
                <td>Rp {{ number_format($pemesanan->jadwal->harga_sewa ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Status Scan</strong></td>
                <td>{{ ucfirst(str_replace('_',' ', $pemesanan->status_scan ?? '-')) }}</td>
            </tr>
            @if($pemesanan->waktu_scan)
            <tr>
                <td><strong>Waktu Scan</strong></td>
                <td>{{ \Carbon\Carbon::parse($pemesanan->waktu_scan)->translatedFormat('d F Y H:i') }}</td>
            </tr>
            @endif
        </table>

        <div class="barcode">
            {!! DNS1D::getBarcodeHTML($pemesanan->kode_tiket, 'C128', 2, 50) !!}
        </div>

        <div class="footer">
            <em>Tunjukkan tiket ini saat check-in. Berlaku untuk satu kali pemakaian.</em>
        </div>
    </div>
</body>
</html>
