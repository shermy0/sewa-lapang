@extends('layouts.sidebar')

@section('title', 'Detail Lapangan')

@section('content')
<div class="container py-4 py-lg-5">

    {{-- Header --}}
    <div class="row align-items-center mb-4 gy-3">
        <div class="col-lg-8">
            <div class="d-flex align-items-start gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fa-solid fa-futbol fa-lg"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-dark mb-1">Detail Lapangan</h2>
                    <p class="text-muted mb-0">Informasi lengkap lapangan beserta jadwal sewa</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a href="{{ route('lapangan.index') }}" class="btn btn-outline-secondary px-4">
                <i class="fa-solid fa-arrow-left-long me-2"></i> Kembali ke daftar
            </a>
        </div>
    </div>

    {{-- Alert --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
            <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Kolom kiri --}}
        <div class="col-xl-10 mx-auto">
            {{-- Foto Lapangan --}}
            <div class="card shadow-sm border-0 mb-4 overflow-hidden photo-card mx-auto">
                <div class="card-body p-0 bg-light">
                    @php
                        $fotos = $lapangan->foto ?? [];
                        if (!is_array($fotos)) $fotos = [];
                    @endphp

                    @if(count($fotos) > 0)
                        @php $carouselId = 'carouselLapangan' . $lapangan->id; @endphp
                        <div id="{{ $carouselId }}" class="carousel slide carousel-lapangan" data-bs-ride="carousel">
                            <div class="carousel-inner rounded-top">
                                @foreach($fotos as $i => $foto)
                                    <div class="carousel-item {{ $i == 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/' . $foto) }}"
                                             class="d-block w-100"
                                             alt="Foto Lapangan">
                                    </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>
                    @else
                        <div class="ratio ratio-1x1 bg-light-subtle rounded-top overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1459865264687-595d652de67e?w=1000&q=80"
                                 class="w-100 h-100 object-fit-cover"
                                 alt="Default">
                        </div>
                    @endif
                </div>
            </div>

            {{-- Detail Informasi --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-8">
                            <h3 class="fw-bold text-dark mb-2">{{ $lapangan->nama_lapangan }}</h3>
                            <div class="d-flex flex-wrap gap-3 align-items-center text-muted">
                                <span class="d-flex align-items-center">
                                    <i class="fa-solid fa-location-dot text-danger me-2"></i>
                                    {{ $lapangan->lokasi }}
                                </span>
                                <span class="badge bg-success text-uppercase px-3 py-2">
                                    {{ $lapangan->kategori ? ucfirst($lapangan->kategori) : 'Tanpa Kategori' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            @if($lapangan->harga_sewa)
                                <div class="small text-muted">Tarif dasar per jam</div>
                                <div class="display-6 fw-bold text-success">
                                    Rp {{ number_format($lapangan->harga_sewa, 0, ',', '.') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="d-flex align-items-start gap-3">
                                <span class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </span>
                                <div>
                                    <div class="fw-semibold text-dark">Jumlah Jadwal Aktif</div>
                                    <div class="fs-4 fw-bold">{{ $lapangan->jadwal->count() }} slot</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="bg-light rounded-3 p-3">
                                <div class="fw-semibold text-dark mb-1">Deskripsi Lapangan</div>
                                <p class="mb-0 text-muted">
                                    {{ $lapangan->deskripsi ?: 'Belum ada deskripsi yang ditambahkan untuk lapangan ini.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Jadwal Lapangan --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">
                                <i class="fa-solid fa-calendar-days text-success me-2"></i> Jadwal Lapangan
                            </h5>
                            <span class="text-muted">Daftar slot waktu yang tersedia maupun terisi</span>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                            Total jadwal: {{ $lapangan->jadwal->count() }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @php
                        use Carbon\Carbon;

                        // Ambil jadwal tersedia
                        $jadwalTersedia = $lapangan->jadwal
                            ->sortBy(['tanggal', 'jam_mulai']);
                    @endphp

                    @if($jadwalTersedia->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-success bg-opacity-10 text-success fw-semibold">
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Tanggal</th>
                                        <th>Rentang Waktu</th>
                                        <th class="text-center">Durasi</th>
                                        <th class="text-center">Harga Total</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jadwalTersedia as $i => $jadwal)
                                        @php
                                            try {
                                                $mulai = Carbon::parse($jadwal->jam_mulai);
                                                $selesai = Carbon::parse($jadwal->jam_selesai);
                                                $durasiMenit = $jadwal->durasi_sewa ?? $mulai->diffInMinutes($selesai);
                                                $durasiJam = $durasiMenit / 60;
                                            } catch (\Exception $e) {
                                                $durasiJam = 0;
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center fw-semibold">{{ $i + 1 }}</td>
                                            <td class="text-nowrap">{{ Carbon::parse($jadwal->tanggal)->translatedFormat('d M Y') }}</td>
                                            <td class="text-nowrap">
                                                <div class="d-flex flex-column small fw-semibold">
                                                    <span>{{ $mulai->format('H:i') }} WIB</span>
                                                    <span class="text-muted">s/d {{ $selesai->format('H:i') }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success px-3 py-2">
                                                    {{ rtrim(rtrim(number_format($durasiJam, 2, ',', '.'), '0'), ',') }} jam
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="fw-bold text-success">Rp {{ number_format($jadwal->harga_total, 0, ',', '.') }}</div>
                                                <small class="text-muted d-block">Rp {{ number_format($jadwal->harga_sewa, 0, ',', '.') }} / jam</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge px-3 py-2 {{ $jadwal->tersedia ? 'bg-gradient bg-success' : 'bg-secondary' }}">
                                                    {{ $jadwal->tersedia ? 'Tersedia' : 'Tidak Tersedia' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-5 text-center text-muted">
                            <i class="fa-solid fa-calendar-xmark fa-2x mb-3"></i>
                            <p class="mb-0">Belum ada jadwal yang ditambahkan untuk lapangan ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Styling tambahan --}}
<style>
    .bg-light-subtle {
        background: #f8f9fa;
    }
    .bg-success-subtle {
        background: rgba(25, 135, 84, 0.12);
    }
    .bg-success-subtle.text-success {
        color: #198754 !important;
    }
    .bg-success-subtle.text-success .fa-solid {
        color: inherit;
    }
    .table > :not(caption) > * > * {
        vertical-align: middle;
    }
    .ratio img {
        border-radius: 12px 12px 0 0;
    }
    .object-fit-cover {
        object-fit: cover;
        object-position: center;
    }
    .carousel-lapangan .carousel-inner {
        position: relative;
        border-radius: 12px 12px 0 0;
    }
    .photo-card {
        max-width: 540px;
    }
    .carousel-lapangan .carousel-item {
        position: relative;
        padding-top: 100%;
        background: #f8f9fa;
        overflow: hidden;
    }
    @media (min-width: 768px) {
        .carousel-lapangan .carousel-item {
            padding-top: 100%;
        }
    }
    .carousel-lapangan .carousel-item img {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        height: 100%;
        max-width: none;
        transform: translate(-50%, -50%);
        object-fit: cover;
        object-position: center;
    }
</style>
@endsection
