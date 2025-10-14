@extends('layouts.sidebar')

@section('title', 'Tiket Pemesanan')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">Tiket Lapangan</h2>

    <div class="card p-4 mb-3">
        <p><strong>Lapangan:</strong> {{ $pemesanan->lapangan->nama_lapangan }}</p>
        <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($pemesanan->jadwal->tanggal)->format('d M Y') }}</p>
        <p><strong>Jam:</strong> {{ $pemesanan->jadwal->jam_mulai }} - {{ $pemesanan->jadwal->jam_selesai }}</p>
        <p><strong>Status Pembayaran:</strong> 
            @if($pemesanan->status == 'dibayar')
                <span class="text-success">Dibayar</span>
            @else
                <span class="text-warning">Belum Dibayar</span>
            @endif
        </p>
        <p><strong>Jumlah Bayar:</strong> Rp {{ number_format($pemesanan->lapangan->harga_per_jam,0,',','.') }}</p>
    </div>

    <a href="{{ route('penyewa.dashboard') }}" class="btn btn-primary">Kembali ke Dashboard</a>
</div>
@endsection