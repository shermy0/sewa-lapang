@extends('layouts.sidebar')

@section('title', 'Riwayat Pemesanan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">
<div class="container py-4">
    <div class="penyewa-page-header">
        <div>
            <p class="eyebrow">Riwayat Aktivitas</p>
            <h1>Riwayat Pemesanan</h1>
            <p class="subtitle">Lihat pesanan yang selesai, dibatalkan, atau sudah discan.</p>
        </div>
    </div>

    <div class="history-grid">
        @forelse($dibatalkan as $p)
        <div class="history-card">
            <div class="history-card__badge">
                <i class="fa-solid fa-ticket"></i>
            </div>
            <div class="history-card__content">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-1">{{ $p->lapangan->nama_lapangan }}</h5>
                    @if($p->status == 'batal')
                        <span class="status-chip status-chip--danger">Dibatalkan</span>
                    @elseif($p->status == 'di-scan')
                        <span class="status-chip status-chip--info">Sudah Discanned</span>
                    @endif
                </div>
                <p class="text-muted mb-2">
                    <i class="fa-regular fa-calendar me-1"></i>
                    {{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}
                    &middot; {{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }}
                </p>
                <div class="history-card__meta">
                    <span><i class="fa-solid fa-map-pin me-1 text-success"></i>{{ $p->lapangan->lokasi ?? 'Lokasi belum tersedia' }}</span>
                    <span><i class="fa-solid fa-money-bill me-1 text-success"></i>Rp{{ number_format($p->total_harga ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state-card">
            <i class="fa-solid fa-box-open"></i>
            <h5>Belum ada riwayat</h5>
            <p>Pemesanan yang dibatalkan atau telah discan akan muncul di sini.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
