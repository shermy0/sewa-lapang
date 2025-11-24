@extends('layouts.sidebar')

@section('title', 'Riwayat Pemesanan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">
<div class="container py-4">
    <div class="penyewa-page-header">
        <div>
            <p class="eyebrow">Riwayat Aktivitas</p>
            <h1>Riwayat Pemesanan</h1>
            <p class="subtitle">Lihat pesanan yang selesai, dibatalkan, atau sudah kadaluarsa.</p>
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
@elseif($p->status == 'kadaluarsa')
    <span class="status-chip status-chip--secondary">
        Kadaluarsa
        @if(optional($p->pembayaran)->status === 'berhasil')
            <small class="ms-1 text-success">(Sudah Dibayar)</small>
        @else
            <small class="ms-1 text-muted">(Belum Dibayar)</small>
        @endif
    </span>
@endif

                </div>
                <p class="text-muted mb-2">
                    <i class="fa-regular fa-calendar me-1"></i>
                    {{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}
                    &middot; {{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }}
                </p>
                @php
                    $hargaTampil = $p->total_harga;

                    if (!is_numeric($hargaTampil) || $hargaTampil <= 0) {
                        $sections = $p->lapangan->sections ?? collect();
                        $totalHarga = 0;
                        $jumlahJadwal = 0;

                        foreach ($sections as $section) {
                            foreach ($section->jadwal as $jadwal) {
                                if (is_numeric($jadwal->harga_sewa) && $jadwal->harga_sewa > 0) {
                                    $totalHarga += $jadwal->harga_sewa;
                                    $jumlahJadwal++;
                                }
                            }
                        }

                        if ($jumlahJadwal > 0) {
                            $hargaTampil = $totalHarga / $jumlahJadwal;
                        }
                    }
                @endphp
                <div class="history-card__meta">
                    <span><i class="fa-solid fa-map-pin me-1 text-success"></i>{{ $p->lapangan->lokasi ?? 'Lokasi belum tersedia' }}</span>
                    <span>
                        <i class="fa-solid fa-money-bill me-1 text-success"></i>
                        {{ $hargaTampil > 0 ? 'Rp' . number_format($hargaTampil, 0, ',', '.') : 'Harga belum tersedia' }}
                    </span>
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
