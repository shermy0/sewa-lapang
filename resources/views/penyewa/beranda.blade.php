@extends('layouts.sidebar')

@section('title', 'Beranda Penyewa')

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container py-4">
    <h1 class="fw-bold" style="color: var(--primary-green);">Beranda Penyewa</h1>

    <!-- FORM SEARCH -->
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

    <!-- CAROUSEL -->
    <div id="lapanganCarousel" class="carousel slide mt-5 shadow rounded-4 overflow-hidden" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
            <div class="carousel-item">
                <img src="https://images.pexels.com/photos/114296/pexels-photo-114296.jpeg?auto=compress&cs=tinysrgb&w=1200"
                    class="d-block w-100"
                    alt="Lapangan Sepak Bola Estetik">
            </div>
            <div class="carousel-item active">
                <img src="https://images.pexels.com/photos/114296/pexels-photo-114296.jpeg?auto=compress&cs=tinysrgb&w=1200"
                    class="d-block w-100"
                    alt="Lapangan Futsal">
            </div>
            <div class="carousel-item">
                <img src="https://images.pexels.com/photos/1103829/pexels-photo-1103829.jpeg?auto=compress&cs=tinysrgb&w=1200"
                    class="d-block w-100"
                    alt="Lapangan Basket">
            </div>

        </div>
    </div>
    
    <!-- FILTER KATEGORI -->
    <div class="d-flex gap-2 flex-wrap my-4">
        <a href="{{ route('penyewa.beranda', ['kategori' => 'all']) }}" 
           class="btn {{ ($kategori ?? 'all') === 'all' ? 'btn-success' : 'btn-outline-success' }}">
           Semua
        </a>

        @foreach($kategoris as $k)
            <a href="{{ route('penyewa.beranda', ['kategori' => $k]) }}" 
               class="btn {{ $kategori == $k ? 'btn-success' : 'btn-outline-success' }}">
               {{ $k }}
            </a>
        @endforeach
    </div>

    <!-- DAFTAR LAPANGAN -->
    <div class="row">
        @forelse($lapangan as $item)
            <div class="col-md-4 mb-4">
                <a href="{{ route('penyewa.detail', $item->id) }}" class="text-decoration-none text-dark">
                    <div class="card shadow-sm border-0 h-100">
                        <img src="{{ $item->foto }}" alt="{{ $item->nama_lapangan }}" class="img-fluid rounded">
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->nama_lapangan }}</h5>
                            <p class="text-muted mb-1">
                                <i class="fa-solid fa-location-dot text-success me-1"></i>
                                {{ $item->lokasi }}
                            </p>
                            <p class="fw-semibold text-success">
                                Rp {{ number_format($item->harga_per_jam, 0, ',', '.') }}/jam
                            </p>
                            <span class="badge bg-success">{{ $item->kategori }}</span>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <p class="text-center text-muted mt-4">Tidak ada lapangan ditemukan.</p>
        @endforelse
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection