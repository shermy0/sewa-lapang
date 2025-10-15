@extends('layouts.sidebar')

@section('title', 'Riwayat Pemesanan')

@section('content')
<style>
.card-long {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    padding: 16px;
    margin-bottom: 16px;
    background-color: #fff;
    display: flex;
    flex-direction: row;
    align-items: center;
    transition: transform 0.2s;
}
.card-long:hover {
    transform: translateY(-2px);
}
.card-info {
    flex: 1;
    margin-left: 16px;
}
.badge-status {
    padding: 0.35em 0.75em;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
}
.text-barcode {
    font-family: monospace;
    font-size: 0.9rem;
    margin-top: 8px;
}
.btn-pay-again {
    font-weight: 600;
    margin-top: 8px;
}
.section-divider {
    height: 2px;
    background-color: #e0e0e0;
    margin: 32px 0;
    border-radius: 2px;
}
.lapangan-image {
    width: 120px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">Riwayat Pemesanan</h2>

    {{-- Belum Dibayar --}}
    <h4 class="mt-4">Belum Dibayar</h4>
    <div>
        @foreach($belumDibayar as $p)
        <div class="card-long border-warning">
            <img src="{{ $p->lapangan->foto_lapangan ?? 'https://via.placeholder.com/120x80' }}" alt="Lapangan" class="lapangan-image">
            <div class="card-info">
                <p class="mb-1"><strong>Lapangan:</strong> {{ $p->lapangan->nama_lapangan }}</p>
                <p class="mb-1">
                    <strong>Jadwal:</strong>
                    {{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}
                    ({{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }})
                </p>
                <p class="mb-2"><span class="badge badge-status bg-warning text-dark">Belum Dibayar</span></p>
                <button class="btn btn-success btn-pay-again" data-id="{{ $p->id }}">Bayar Sekarang</button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Divider --}}
    <div class="section-divider"></div>

    {{-- Sudah Dibayar --}}
    <h4 class="mt-4">Sudah Dibayar</h4>
    <div>
        @foreach($sudahDibayar as $p)
        <div class="card-long border-success">
            <img src="{{ $p->lapangan->foto_lapangan ?? 'https://via.placeholder.com/120x80' }}" alt="Lapangan" class="lapangan-image">
            <div class="card-info">
                <p class="mb-1"><strong>Lapangan:</strong> {{ $p->lapangan->nama_lapangan }}</p>
                <p class="mb-1">
                    <strong>Jadwal:</strong>
                    @if($p->jadwal)
                        {{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}
                        ({{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }})
                    @else
                        <span class="text-danger">Jadwal tidak tersedia</span>
                    @endif
                </p>
                <p class="mb-1"><span class="badge badge-status bg-success">Dibayar</span></p>
                <p class="mb-2 text-barcode"><strong>Kode Tiket:</strong></p>
                <div class="text-center">
                    {!! DNS1D::getBarcodeHTML($p->kode_tiket ?? 'UNKNOWN', 'C128') !!}
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
