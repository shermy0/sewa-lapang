@extends('layouts.sidebar')

@section('title', 'Beranda Penyewa')

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container py-4">
    <h1 class="fw-bold" style="color: var(--primary-green);">Beranda Penyewa</h1>

    {{-- FORM SEARCH --}}
    <form method="GET" action="{{ route('penyewa.beranda') }}" class="search-form mt-3">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0">
                <i class="fa-solid fa-magnifying-glass text-secondary"></i>
            </span>
            <input type="text" name="search" class="form-control border-start-0"
                   placeholder="Cari lapangan..." value="{{ $keyword ?? '' }}">
            <button class="btn btn-success px-4" type="submit">Cari</button>
        </div>
    </form>

    {{-- CAROUSEL --}}
    <div id="lapanganCarousel" class="carousel slide mt-5 shadow rounded-4 overflow-hidden"
        data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
            @forelse ($banners as $index => $banner)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    <img src="{{ asset($banner->gambar) }}" 
                        class="d-block w-100" 
                        alt="{{ $banner->judul ?? 'Banner' }}">
                </div>
            @empty
                <div class="carousel-item active">
                    <img src="https://images.pexels.com/photos/114296/pexels-photo-114296.jpeg?auto=compress&cs=tinysrgb&w=1200"
                        class="d-block w-100" alt="Default Banner">
                </div>
            @endforelse
        </div>
    </div>

    {{-- FILTER KATEGORI --}}
    <div class="d-flex gap-2 flex-wrap my-4">
        <a href="{{ route('penyewa.beranda', ['kategori' => 'all']) }}"
        class="btn {{ ($kategori ?? 'all') === 'all' ? 'btn-success' : 'btn-outline-success' }}">
            Semua
        </a>

        @foreach($kategoris as $k)
            <a href="{{ route('penyewa.beranda', ['kategori' => $k->nama_kategori]) }}"
            class="btn {{ $kategori == $k->nama_kategori ? 'btn-success' : 'btn-outline-success' }}">
            {{ $k->nama_kategori }}
            </a>
        @endforeach
    </div>

    {{-- ✅ DEFINISI HELPER FOTO DI LUAR LOOP --}}
    @php
        if (!function_exists('foto_url')) {
            function foto_url($file) {
                if (empty($file)) {
                    return 'https://images.unsplash.com/photo-1459865264687-595d652de67e?w=600&h=400&fit=crop';
                }
                if (preg_match('/^https?:\/\//', $file)) {
                    return $file;
                }
                return asset('storage/' . ltrim($file, '/'));
            }
        }
    @endphp

   {{-- GRID LAPANGAN --}}
    <div class="row g-4">
        @forelse ($lapangan as $item)
            @php
                $today = \Carbon\Carbon::today();

                // --- Normalisasi foto ---
                $fotoArray = is_array($item->foto) ? $item->foto : (@json_decode($item->foto, true) ?: [$item->foto]);
                $fotoArray = array_filter($fotoArray);

                // --- Hitung harga per jam seperti di detail ---
                $hargaPerJam = $item->harga_per_jam ?? $item->harga_sewa ?? 0;

                if (!is_numeric($hargaPerJam) || $hargaPerJam <= 0) {
                    $sections = $item->sections ?? collect();
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
                        $hargaPerJam = $totalHarga / $jumlahJadwal;
                    }
                }

                // --- Hitung total section dan jadwal ---
                $sections = $item->sections ?? collect();
                $totalSections = $sections->count();
                $totalJadwal = 0;
                foreach ($sections as $section) {
                    $totalJadwal += $section->jadwal->count();
                }
            @endphp

        <div class="col-lg-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 overflow-hidden hover-lift">
                {{-- FOTO --}}
                <div class="position-relative" style="height: 220px; overflow: hidden;">
                    @if(!empty($fotoArray))
                        @if(count($fotoArray) > 1)
                            <div id="carouselLapangan{{ $item->id }}" class="carousel slide h-100" data-bs-ride="carousel">
                                <div class="carousel-inner h-100">
                                    @foreach($fotoArray as $i => $foto)
                                        <div class="carousel-item {{ $i == 0 ? 'active' : '' }} h-100">
                                            <img src="{{ foto_url($foto) }}" class="d-block w-100 h-100" style="object-fit: cover;">
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselLapangan{{ $item->id }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselLapangan{{ $item->id }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        @else
                            <img src="{{ foto_url(array_values($fotoArray)[0]) }}" class="w-100 h-100" style="object-fit: cover;">
                        @endif
                    @else
                        <img src="{{ foto_url(null) }}" class="w-100 h-100" style="object-fit: cover;">
                    @endif

                    {{-- BADGES --}}
                    <div class="position-absolute top-0 start-0 m-3">
                        <span class="badge bg-primary px-3 py-2">
                            <i class="fa-solid fa-layer-group me-1"></i>{{ $totalSections }} Lapangan
                        </span>
                    </div>
                    <div class="position-absolute top-0 end-0 m-3">
                        <span class="badge bg-success px-3 py-2">
                            <i class="fa-solid fa-calendar me-1"></i>{{ $totalJadwal }} Jadwal
                        </span>
                    </div>
                    <div class="position-absolute bottom-0 start-0 m-3" style="z-index:10;">
                        <span class="badge bg-dark bg-opacity-75 px-3 py-2">
                            <i class="fa-solid fa-tag me-1"></i>{{ ucfirst($item->kategori ?? '') }}
                        </span>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="card-body">
                    <h5 class="fw-bold">{{ $item->nama_lapangan }}</h5>
                    <p class="text-muted small mb-2">
                        <i class="fa-solid fa-location-dot text-success me-1"></i>{{ Str::limit($item->lokasi, 50) }}
                    </p>
                    <p class="text-muted small">{{ Str::limit($item->deskripsi, 100) }}</p>

                    {{-- HARGA --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">
                            <i class="fa-solid fa-money-bill-wave text-success me-1"></i> Harga Rata-Rata
                        </small>
                        @if($hargaPerJam > 0)
                            <span class="fw-bold text-success">
                                Rp {{ number_format($hargaPerJam, 0, ',', '.') }} / jam
                            </span>
                        @else
                            <span class="text-muted">Belum tersedia</span>
                        @endif
                    </div>

                    {{-- TOMBOL --}}
                    <div class="d-flex gap-2 mt-3">
                        <a href="{{ route('penyewa.detail', $item->id) }}" class="btn btn-outline-primary flex-fill">
                            <i class="fa-solid fa-eye me-1"></i> Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-5">
            <i class="fa-solid fa-futbol fa-2x mb-3"></i>
            <p>Tidak ada lapangan ditemukan.</p>
        </div>
    @endforelse
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection