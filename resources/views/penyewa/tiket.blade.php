@extends('layouts.sidebar') 

@section('title', 'Tiket Saya')

@section('content')
<link rel="stylesheet" href="{{ asset('css/tiket.css') }}">

<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">Tiket Saya</h2>

    <div class="row">
        @forelse($sudahDibayar as $p)
        <div class="col-md-6 mb-4">
            <div class="ticket d-flex shadow-sm">
                <!-- Kiri: Jadwal -->
                <div class="ticket-left p-3 text-white">
                    <p class="mb-1 small-text">Tanggal</p>
                    <p class="mb-2 fw-bold">{{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}</p>
                    <p class="mb-1 small-text">Jam</p>
                    <p>{{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }}</p>
                </div>
                
                <!-- Kanan: Info lapangan & QR -->
                <div class="ticket-right p-3 bg-white flex-fill">
                    <p class="mb-1"><strong>Lapangan:</strong> {{ $p->lapangan->nama_lapangan }}</p>
                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-success">Dibayar</span></p>
                    <p class="mb-1"><strong>Kode Tiket:</strong> {{ $p->kode_tiket }}</p>
                    <div class="qr mt-3 text-center">
                        {!! DNS1D::getBarcodeHTML($p->kode_tiket, 'C128') !!}
                    </div>
                    <div class="mt-3 text-end">
                        <a href="{{ route('tiket.download', $p->id) }}" class="btn btn-sm btn-success">Download</a>
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
