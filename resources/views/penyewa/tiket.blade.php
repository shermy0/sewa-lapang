@extends('layouts.sidebar')

@section('title', 'Tiket Saya')

@section('content')
<link rel="stylesheet" href="{{ asset('css/tiket.css') }}">

<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">Tiket Saya</h2>

    <div class="row">
        @forelse($sudahDibayar as $p)
        <div class="col-md-6 mb-4">
            <div class="ticket shadow-sm">
                <!-- Kiri -->
                <div class="ticket-left">
                    <div class="ticket-left-content">
                        <p class="label">Tanggal</p>
                        <p class="value">{{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}</p>

                        <p class="label">Jam</p>
                        <p class="value">{{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }}</p>
                    </div>
                </div>

                <!-- Tengah: Garis potong -->
                <div class="ticket-divider">
                    <div class="cutout top"></div>
                    <div class="dotted-line"></div>
                    <div class="cutout bottom"></div>
                </div>

                <!-- Kanan -->
                <div class="ticket-right">
                    <div class="ticket-info">
                        <p><strong>Lapangan:</strong> {{ $p->lapangan->nama_lapangan }}</p>
                        <p><strong>Status:</strong> <span class="badge bg-success">Dibayar</span></p>
                        <p><strong>Kode Tiket:</strong> {{ $p->kode_tiket }}</p>
                    </div>

                    <div class="qr text-center mt-3">
                        {!! DNS1D::getBarcodeHTML($p->kode_tiket, 'C128') !!}
                    </div>

                    <div class="text-end mt-3">
                        <a href="{{ route('tiket.download', $p->id) }}" class="btn-download">Download Tiket</a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <p class="text-muted">Belum ada pesanan yang dibayar.</p>
        @endforelse
    </div>
</div>
@endsection
