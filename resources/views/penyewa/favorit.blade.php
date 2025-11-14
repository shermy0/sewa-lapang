@extends('layouts.sidebar')

@section('title', 'Lapangan Favorit')

@section('content')
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container py-4">
    <h1 class="fw-bold mb-4 d-flex align-items-center gap-2" style="color: var(--primary-green);">
        Lapangan Favorit
    </h1>

    {{-- Flash Messages --}}
    @foreach (['success', 'error'] as $flash)
        @if (session($flash))
            <div class="alert alert-{{ $flash === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                {{ session($flash) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    @endforeach

    {{-- Helper Foto --}}
    @php
        if (!function_exists('foto_url')) {
            function foto_url($file) {
                if (empty($file)) {
                    return 'https://via.placeholder.com/640x360?text=Lapangan';
                }
                if (preg_match('/^https?:\/\//', $file)) {
                    return $file;
                }
                return asset('storage/' . ltrim($file, '/'));
            }
        }
    @endphp

    {{-- Jika Tidak Ada Favorit --}}
    @if ($favoritLapangan->isEmpty())
        <div class="text-center py-5">
            <i class="fa-solid fa-heart-circle-plus text-success fs-1 mb-3"></i>
            <h5 class="fw-semibold">Belum ada lapangan favorit.</h5>
            <p class="text-muted mb-0">Tambahkan lapangan ke favorit dari halaman detail untuk memudahkan akses.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach ($favoritLapangan as $lapangan)
                @php
                    // Normalisasi Foto
                    $fotoArray = is_array($lapangan->foto) ? $lapangan->foto : (@json_decode($lapangan->foto, true) ?: [$lapangan->foto]);
                    $fotoArray = array_filter($fotoArray);

                    // Section & Jadwal
                    $sections = $lapangan->sections ?? collect();
                    $totalSections = $sections->count();
                    $totalJadwal = 0;
                    $hargaRataRata = 0;

                    foreach ($sections as $section) {
                        $jadwalCount = $section->jadwal->count();
                        $totalJadwal += $jadwalCount;
                        if ($jadwalCount > 0) {
                            $hargaRataRata += $section->jadwal->avg('harga_sewa');
                        }
                    }
                    if ($totalSections > 0) $hargaRataRata = $hargaRataRata / $totalSections;
                @endphp

                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm border-0 h-100 overflow-hidden hover-lift">

                        {{-- FOTO --}}
                        <div class="position-relative" style="height: 220px; overflow: hidden;">
                            @if(!empty($fotoArray))
                                @if(count($fotoArray) > 1)
                                    <div id="carouselLapangan{{ $lapangan->id }}" class="carousel slide h-100" data-bs-ride="carousel">
                                        <div class="carousel-inner h-100">
                                            @foreach($fotoArray as $i => $foto)
                                                <div class="carousel-item {{ $i == 0 ? 'active' : '' }} h-100">
                                                    <img src="{{ foto_url($foto) }}" class="w-100 h-100" style="object-fit: cover;">
                                                </div>
                                            @endforeach
                                        </div>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselLapangan{{ $lapangan->id }}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselLapangan{{ $lapangan->id }}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>
                                @else
                                    <img src="{{ foto_url($fotoArray[0] ?? null) }}" class="w-100 h-100" style="object-fit: cover;">
                                @endif
                            @else
                                <img src="{{ foto_url(null) }}" class="w-100 h-100" style="object-fit: cover;">
                            @endif

                            {{-- BADGES --}}
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge bg-primary px-3 py-2">
                                    <i class="fa-solid fa-layer-group me-1"></i>{{ $totalSections }} Section
                                </span>
                            </div>
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-success px-3 py-2">
                                    <i class="fa-solid fa-calendar me-1"></i>{{ $totalJadwal }} Jadwal
                                </span>
                            </div>
                            <div class="position-absolute bottom-0 start-0 m-3" style="z-index:10;">
                                <span class="badge bg-dark bg-opacity-75 px-3 py-2">
                                    <i class="fa-solid fa-tag me-1"></i>{{ ucfirst($lapangan->kategori ?? '') }}
                                </span>
                            </div>
                        </div>

                        {{-- BODY --}}
                        <div class="card-body d-flex flex-column">
                            <h5 class="fw-bold">{{ $lapangan->nama_lapangan }}</h5>
                            <p class="text-muted small mb-2">
                                <i class="fa-solid fa-location-dot text-success me-1"></i>{{ Str::limit($lapangan->lokasi, 50) }}
                            </p>
                            <p class="text-muted small">{{ Str::limit($lapangan->deskripsi, 100) }}</p>

                            {{-- HARGA --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="fa-solid fa-money-bill-wave text-success me-1"></i> Harga Rata-Rata
                                </small>
                                @if($hargaRataRata > 0)
                                    <span class="fw-bold text-success">
                                        Rp {{ number_format($hargaRataRata, 0, ',', '.') }} / jam
                                    </span>
                                @else
                                    <span class="text-muted">Belum tersedia</span>
                                @endif
                            </div>

                            {{-- TOMBOL --}}
                            <div class="mt-auto d-flex gap-2">
                                <a href="{{ route('penyewa.detail', $lapangan->id) }}" class="btn btn-outline-primary flex-fill">
                                    <i class="fa-solid fa-eye me-1"></i> Detail
                                </a>
                                <form action="{{ route('favorit.destroy', $lapangan) }}" method="POST" class="d-inline flex-fill">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100"
                                            onclick="return confirm('Hapus lapangan dari favorit?')">
                                        <i class="fa-solid fa-heart-crack me-1"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
