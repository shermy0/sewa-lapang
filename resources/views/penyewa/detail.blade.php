@extends('layouts.sidebar')

@section('title', 'Detail Lapangan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="detail-lapangan container py-4">
    <h1 class="fw-bold" style="color: var(--primary-green);">Detail {{ $lapangan->nama_lapangan }}</h1>

    <div class="row g-4 align-items-start">
        <!-- FOTO (CAROUSEL) -->
        <div class="col-md-5">
            <div id="carouselLapanganDetail" class="carousel slide shadow-sm rounded-4 overflow-hidden" 
                 data-bs-ride="carousel" data-bs-interval="3000">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="{{ asset('poto/'.$lapangan->foto) }}" 
                             class="d-block w-100" 
                             alt="Foto Lapangan"
                             style="height: 350px; object-fit: cover;">
                    </div>
                    <div class="carousel-item">
                        <img src="{{ asset('poto/'.$lapangan->foto) }}" 
                             class="d-block w-100" 
                             alt="Foto Lapangan"
                             style="height: 350px; object-fit: cover;">
                    </div>
                </div>

                <!-- Panah Navigasi -->
                <button class="carousel-control-prev" type="button" 
                        data-bs-target="#carouselLapanganDetail" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" 
                        data-bs-target="#carouselLapanganDetail" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>

        <!-- INFORMASI -->
        <div class="col-md-7">
            <h3 class="fw-semibold">{{ $lapangan->nama_lapangan }}</h3>
            <span class="badge bg-success mb-2">Tersedia</span>

            <p class="text-muted">{{ $lapangan->deskripsi ?? 'Belum ada deskripsi.' }}</p>

            <div class="mb-2">
                <i class="fa-solid fa-location-dot text-success me-2"></i>
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($lapangan->lokasi) }}" 
                   target="_blank" 
                   class="text-secondary text-decoration-none">
                    {{ $lapangan->lokasi }}
                </a>
            </div>

            <div class="mb-2">
                <i class="fa-solid fa-tag text-success me-2"></i>
                <span class="text-danger fw-semibold">
                    Rp{{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}/jam
                </span>
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="#" class="btn btn-outline-success">Lihat Ulasan</a>
                <a href="#" class="btn btn-success px-4">Pesan</a>
            </div>
        </div>
    </div>

    <!-- LAPANGAN LAINNYA -->
    <div class="mt-5">
        <h5 class="fw-bold mb-3">Lapangan Lainnya</h5>
        <div class="row">
            @foreach($lainnya as $l)
                <div class="col-md-3 mb-4">
                    <a href="{{ route('penyewa.detail', $l->id) }}" class="text-decoration-none text-dark">
                        <div class="card shadow-sm border-0 h-100">
                            <img src="{{ asset('poto/'.$l->foto) }}" 
                                 class="card-img-top" 
                                 alt="Foto Lapangan"
                                 style="height: 150px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="card-title">{{ $l->nama_lapangan }}</h6>
                                <span class="text-success small">
                                    Rp{{ number_format($l->harga_per_jam, 0, ',', '.') }}/jam
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection