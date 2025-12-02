@extends('layouts.sidebar')

@section('title', 'Riwayat Pemesanan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">
<link rel="stylesheet" href="{{ asset('css/tiket.css') }}">
<div class="container py-4">
    <div class="penyewa-page-header">
        <div>
            <p class="eyebrow">Riwayat Aktivitas</p>
            <h1>Riwayat Pemesanan</h1>
            <p class="subtitle">Lihat pesanan yang selesai, dibatalkan, atau sudah kadaluarsa.</p>
        </div>
    </div>

    {{-- ======================= TABS FILTER RIWAYAT ======================= --}}
    <ul class="nav nav-pills mb-4" id="riwayatTabs">
        <li class="nav-item">
            <button class="nav-link active" data-filter="all">Semua</button>
        </li>

        <li class="nav-item">
            <button class="nav-link" data-filter="batal">Dibatalkan</button>
        </li>

        <li class="nav-item">
            <button class="nav-link" data-filter="tidak_dipakai">Tidak Dipakai</button>
        </li>

        <li class="nav-item">
            <button class="nav-link" data-filter="pembayaran_gagal">Pembayaran Tidak Selesai</button>
        </li>
    </ul>
    {{-- ================================================================= --}}

    <div class="history-grid">
        @forelse($dibatalkan as $p)
            @php
                // tentukan data-status untuk filter
                if ($p->status === 'batal') {
                    $filterStatus = 'batal';
                } elseif ($p->status === 'kadaluarsa' && optional($p->pembayaran)->status === 'berhasil') {
                    $filterStatus = 'tidak_dipakai';
                } elseif ($p->status === 'kadaluarsa') {
                    $filterStatus = 'pembayaran_gagal';
                } else {
                    $filterStatus = $p->status ?? 'lainnya';
                }

                // harga tampil fallback
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

            <div class="history-card" data-status="{{ $filterStatus }}">
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
                <small class="ms-1 text-success">(Tidak Dipakai)</small>
            @else
                <small class="ms-1 text-muted">(Pembayaran Tidak Selesai)</small>
            @endif
        </span>
    @endif
</div>

{{-- Tampilkan nama komunitas --}}
@if($p->nama_komunitas)
    <p class="text-primary mb-1" style="font-weight: 600;">
        <i class="fa-solid fa-users me-1"></i> {{ $p->nama_komunitas }}
    </p>
@endif


                    <p class="text-muted mb-2">
                        <i class="fa-regular fa-calendar me-1"></i>
                        {{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}
                        &middot; {{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }}
                    </p>

                    <div class="history-card__meta">
                        <span>
                            <i class="fa-solid fa-map-pin me-1 text-success"></i>
                            {{ $p->lapangan->lokasi ?? 'Lokasi belum tersedia' }}
                        </span>

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
    </div> {{-- end history-grid --}}
</div>

<script>
document.querySelectorAll('#riwayatTabs .nav-link').forEach(tab => {
    tab.addEventListener('click', function() {
        // aktifkan tab yang diklik
        document.querySelectorAll('#riwayatTabs .nav-link')
            .forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');

        const filter = this.dataset.filter;
        document.querySelectorAll('.history-card').forEach(card => {
            const status = card.dataset.status;

            if (filter === 'all' || status === filter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>

@endsection
