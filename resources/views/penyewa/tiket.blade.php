@extends('layouts.sidebar')

@section('title', 'Tiket Saya')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/tiket.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --green: #41A67E;
        --yellow: #F6C445;
        --gray-light: #f9f9f9;
    }

    .nav-pills .nav-link {
        color: var(--green);
        font-weight: 600;
        border-radius: 50px;
        padding: 8px 20px;
        transition: .3s;
    }

    .nav-pills .nav-link.active {
        background-color: var(--green);
        color: #fff;
    }

    .ticket-status-scan {
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 6px;
        padding: 4px 8px;
    }

    .ticket-status-scan.sudah {
        background: #E6F5EF;
        color: var(--green);
    }

    .ticket-status-scan.belum {
        background: #FFF5D6;
        color: #C78C00;
    }
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">
        <i class="fa-solid fa-ticket me-2"></i> Tiket Saya
    </h2>

    {{-- ====================== FILTER TAB ====================== --}}
    <ul class="nav nav-pills mb-4" id="scanTabs">
        <li class="nav-item">
            <button class="nav-link active" data-filter="all">Semua</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-filter="belum_scan">Belum Discan</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-filter="sudah_scan">Sudah Discan</button>
        </li>
    </ul>

    {{-- ====================== DAFTAR TIKET ====================== --}}
    <div class="row" id="ticketContainer">
        @forelse($sudahDibayar as $p)
            <div class="col-md-6 mb-4 ticket-card" data-status="{{ $p->status_scan }}">
                {{-- Notifikasi perubahan --}}
                @if($p->permintaanPerubahan)
                    @if($p->permintaanPerubahan->status === 'menunggu')
                        <div class="alert alert-warning py-2 px-3 small mb-2">
                            <i class="fa-solid fa-hourglass-half me-1"></i>
                            Menunggu persetujuan perubahan jadwal / section...
                        </div>
                    @elseif($p->permintaanPerubahan->status === 'disetujui')
                        <div class="alert alert-success py-2 px-3 small mb-2">
                            <i class="fa-solid fa-check-circle me-1"></i>
                            Perubahan jadwal / section telah disetujui dan diperbarui di tiket ini.
                        </div>
                    @elseif($p->permintaanPerubahan->status === 'ditolak')
                        <div class="alert alert-danger py-2 px-3 small mb-2">
                            <i class="fa-solid fa-xmark me-1"></i>
                            Permintaan perubahan ditolak.
                        </div>
                    @endif
                @endif

                {{-- ================== KARTU TIKET ================== --}}
                <div class="ticket shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="d-flex flex-column flex-md-row">
                        {{-- ================== KIRI ================== --}}
                        @php
                            $jadwalAktif = $p->jadwal;
                            $sectionAktif = $p->jadwal?->section;

                            if ($p->permintaanPerubahan && $p->permintaanPerubahan->status === 'disetujui') {
                                $jadwalAktif = $p->permintaanPerubahan->jadwalBaru ?? $jadwalAktif;
                                $sectionAktif = $p->permintaanPerubahan->sectionBaru ?? $sectionAktif;
                            }

                            \Carbon\Carbon::setLocale('id');
                            $tanggal = \Carbon\Carbon::parse($jadwalAktif->tanggal);
                            $hari = $tanggal->translatedFormat('l');
                        @endphp

                        <div class="ticket-left">
                            <div class="ticket-left-header text-center">
                                <div class="lapangan-name">{{ $p->lapangan->nama_lapangan }}</div>
                                <div class="lapangan-meta">
                                    <i class="fa-solid fa-tag me-1"></i>{{ ucfirst($p->lapangan->kategori) }}<br>
                                    <i class="fa-solid fa-location-dot me-1"></i>{{ $p->lapangan->lokasi }}
                                </div>

                                {{-- ✅ Section Info --}}
                                @if($sectionAktif)
                                    <div class="section-info mt-2">
                                        <i class="fa-solid fa-layer-group"></i>
                                        <span>{{ $sectionAktif->nama_section }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- ✅ Jadwal Info --}}
                            @if($jadwalAktif)
                                @php
                                    \Carbon\Carbon::setLocale('id');
                                    $tanggal = \Carbon\Carbon::parse($jadwalAktif->tanggal);
                                    $hari = $tanggal->translatedFormat('l');
                                @endphp
                                <div class="schedule mt-3 text-center">
                                    <div>
                                        <i class="fa-solid fa-calendar-day me-1"></i>
                                        {{ $hari }}, {{ $tanggal->translatedFormat('d F Y') }}
                                    </div>
                                    <div>
                                        <i class="fa-solid fa-clock me-1"></i>
                                        {{ $jadwalAktif->jam_mulai }} - {{ $jadwalAktif->jam_selesai }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- ================== KANAN ================== --}}
                        <div class="ticket-right p-4 bg-white flex-grow-1 position-relative">
                            <div class="ticket-info">
                                <p class="mb-1"><strong>Kode Tiket:</strong> {{ $p->kode_tiket }}</p>
                                <p class="mb-1">
                                    <strong>Status:</strong>
                                    <span class="badge bg-success">Dibayar</span>
                                </p>
                                <p class="mb-1">
                                    <strong>Harga:</strong> Rp {{ number_format($p->jadwal->harga_sewa, 0, ',', '.') }}
                                </p>

                                {{-- ✅ STATUS SCAN BADGE --}}
                                <p class="mt-2 mb-0">
                                    @if($p->status_scan === 'sudah_scan')
                                        <span class="ticket-status-scan sudah">
                                            <i class="fa-solid fa-check-circle me-1"></i> Sudah Discan
                                        </span>
                                    @else
                                        <span class="ticket-status-scan belum">
                                            <i class="fa-solid fa-hourglass-half me-1"></i> Belum Discan
                                        </span>
                                    @endif
                                </p>
                            </div>

                            {{-- QR Code --}}
                            <div class="qr text-center mt-3">
                                {!! DNS1D::getBarcodeHTML($p->kode_tiket, 'C128', 2, 60) !!}
                                <div class="fw-semibold mt-2">{{ $p->kode_tiket }}</div>
                            </div>

                            {{-- Aksi --}}
                            <div class="text-end mt-3 d-flex justify-content-between align-items-center">
                                <a href="{{ route('tiket.download', $p->id) }}" class="btn btn-outline-success btn-sm px-3">
                                    <i class="fa-solid fa-download me-1"></i> Download
                                </a>

                                @if(!$p->permintaanPerubahan)
                                    <button class="btn btn-warning btn-sm px-3"
                                        onclick="ajukanPerubahan({{ $p->id }}, {{ $p->lapangan->id }})">
                                        <i class="fa-solid fa-arrows-rotate me-1"></i> Ajukan Perubahan
                                    </button>
                                @else
                                    <button class="btn btn-outline-primary btn-sm px-3"
                                        onclick="lihatDetailPerubahan({{ $p->permintaanPerubahan->id }})">
                                        <i class="fa-solid fa-eye me-1"></i> Lihat Detail Permintaan
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">Belum ada pesanan yang dibayar.</p>
        @endforelse
    </div>
</div>

<script>
    // 🔹 Tab filter (scan)
    document.querySelectorAll('#scanTabs .nav-link').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('#scanTabs .nav-link').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;
            document.querySelectorAll('.ticket-card').forEach(card => {
                if (filter === 'all' || card.dataset.status === filter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
</script>
@endsection
