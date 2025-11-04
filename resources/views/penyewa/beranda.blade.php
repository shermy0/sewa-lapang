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
            <div class="carousel-item active">
                <img src="https://images.pexels.com/photos/114296/pexels-photo-114296.jpeg?auto=compress&cs=tinysrgb&w=1200"
                     class="d-block w-100" alt="Lapangan Futsal">
            </div>
            <div class="carousel-item">
                <img src="https://images.pexels.com/photos/1103829/pexels-photo-1103829.jpeg?auto=compress&cs=tinysrgb&w=1200"
                     class="d-block w-100" alt="Lapangan Basket">
            </div>
        </div>
    </div>

    {{-- FILTER KATEGORI --}}
    <div class="d-flex gap-2 flex-wrap my-4">
        <a href="{{ route('penyewa.beranda', ['kategori' => 'all']) }}"
           class="btn {{ ($kategori ?? 'all') === 'all' ? 'btn-success' : 'btn-outline-success' }}">
            Semua
        </a>

        @foreach($kategoris as $k)
            <a href="{{ route('penyewa.beranda', ['kategori' => $k->id]) }}"
               class="btn {{ $kategori == $k->id ? 'btn-success' : 'btn-outline-success' }}">
               {{ $k->nama_kategori }}
            </a>
        @endforeach
    </div>

    {{-- GRID LAPANGAN --}}
    <div class="row g-4">
        @forelse ($lapangan as $item)
            @php
                $fotoArray = is_array($item->foto) ? $item->foto : json_decode($item->foto ?? '[]', true);
                $sections = $item->sections ?? collect(); // pastikan selalu ada relasi
                $totalSections = $sections->count();
                $totalJadwal = 0;
                $hargaRataRata = 0;

                foreach ($sections as $section) {
                    $totalJadwal += $section->jadwal->count();
                    if ($section->jadwal->count() > 0) {
                        $hargaRataRata += $section->jadwal->avg('harga_sewa');
                    }
                }
                if ($totalSections > 0) {
                    $hargaRataRata = $hargaRataRata / $totalSections;
                }
            @endphp

            <div class="col-lg-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden hover-lift">

                    {{-- FOTO / CAROUSEL --}}
                    <div class="position-relative" style="height: 220px; overflow: hidden;">
                        @if (!empty($fotoArray))
                            @if (count($fotoArray) > 1)
                                <div id="carouselLapangan{{ $item->id }}" class="carousel slide h-100" data-bs-ride="carousel">
                                    <div class="carousel-inner h-100">
                                        @foreach ($fotoArray as $index => $foto)
                                            <div class="carousel-item h-100 {{ $index == 0 ? 'active' : '' }}">
                                                <img src="{{ asset('storage/' . $foto) }}" class="d-block w-100 h-100"
                                                     alt="{{ $item->nama_lapangan }}" style="object-fit: cover;">
                                            </div>
                                        @endforeach
                                    </div>
                                    <button class="carousel-control-prev" type="button"
                                            data-bs-target="#carouselLapangan{{ $item->id }}" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button"
                                            data-bs-target="#carouselLapangan{{ $item->id }}" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            @else
                                <img src="{{ asset('storage/' . $fotoArray[0]) }}" class="w-100 h-100" style="object-fit: cover;">
                            @endif
                        @else
                            <img src="https://images.unsplash.com/photo-1459865264687-595d652de67e?w=600&h=400&fit=crop"
                                 class="w-100 h-100" alt="Default Image" style="object-fit: cover;">
                        @endif

                        {{-- BADGES --}}
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-primary px-3 py-2"><i class="fa-solid fa-layer-group me-1"></i>{{ $totalSections }} Section</span>
                        </div>
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-success px-3 py-2"><i class="fa-solid fa-calendar me-1"></i>{{ $totalJadwal }} Jadwal</span>
                        </div>
                        <div class="position-absolute bottom-0 start-0 m-3" style="z-index: 10;">
                            <span class="badge bg-dark bg-opacity-75 px-3 py-2"><i class="fa-solid fa-tag me-1"></i>{{ ucfirst($item->kategori) }}</span>
                        </div>
                    </div>

                    {{-- BODY --}}
                    <div class="card-body">
                        <h5 class="fw-bold">{{ $item->nama_lapangan }}</h5>
                        <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot text-success me-1"></i>{{ Str::limit($item->lokasi, 50) }}</p>
                        <p class="text-muted small">{{ Str::limit($item->deskripsi, 100) }}</p>

                        {{-- HARGA --}}
                        @if ($hargaRataRata > 0)
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fa-solid fa-money-bill-wave text-success me-1"></i> Harga Rata-rata
                                </small>
                                <span class="fw-bold text-success">
                                    Rp {{ number_format($hargaRataRata, 0, ',', '.') }} / jam
                                </span>
                            </div>
                        @endif

                        {{-- TOMBOL --}}
                        <div class="d-flex gap-2 mt-4">
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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection