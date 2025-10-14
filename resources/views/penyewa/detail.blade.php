@extends('layouts.sidebar')

@section('title', 'Detail Lapangan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="detail-lapangan container py-4">
    @php
        $avgRating = $avgRating ?? 0;
        $totalUlasan = $totalUlasan ?? 0;
        $ulasans = $ulasans ?? collect();
        $favoritTableExists = \Illuminate\Support\Facades\Schema::hasTable('favorit_lapangan');
        $isFavorit = $isFavorit ?? ($favoritTableExists && Auth::check() && Auth::user()->role === 'penyewa'
            ? Auth::user()->favoritLapangan()->where('lapangan_id', $lapangan->id)->exists()
            : false);
    @endphp

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
                   target="_blank" class="text-secondary text-decoration-none">
                    {{ $lapangan->lokasi }}
                </a>
            </div>

            <div class="mb-2">
                <i class="fa-solid fa-tag text-success me-2"></i>
                <span class="text-danger fw-semibold">
                    Rp{{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}/jam
                </span>
            </div>

            <!-- RATA-RATA ULASAN -->
            <div class="mb-3">
                <strong>Rating:</strong>
                @if($totalUlasan > 0)
                    @for ($i = 1; $i <= 5; $i++)
                        @if($i <= floor($avgRating))
                            <i class="fa-solid fa-star text-warning"></i>
                        @elseif ($i == ceil($avgRating) && $avgRating - floor($avgRating) >= 0.5)
                            <i class="fa-solid fa-star-half-stroke text-warning"></i>
                        @else
                            <i class="fa-regular fa-star text-warning"></i>
                        @endif
                    @endfor
                    ({{ number_format($avgRating, 1) }}/5 dari {{ $totalUlasan }} ulasan)
                @else
                    <span class="text-muted">Belum ada ulasan</span>
                @endif
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="#" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#ulasanModal">Lihat Ulasan</a>
                <a href="#" class="btn btn-success px-4">Pesan</a>

                @if (Auth::check() && Auth::user()->role === 'penyewa')
                    @if ($isFavorit)
                        <form action="{{ route('penyewa.favorit.destroy', $lapangan) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus lapangan dari favorit?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fa-solid fa-heart-crack me-1"></i> Hapus Favorit
                            </button>
                        </form>
                    @else
                        <form action="{{ route('penyewa.favorit.store', $lapangan) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fa-solid fa-heart me-1"></i> Tambah Favorit
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- LAPANGAN LAINNYA -->
    <h4 class="fw-bold mt-5 mb-3">Lapangan Lainnya</h4>
    <div class="row">
        @forelse($lainnya as $item)
            <div class="col-md-4 mb-4">
                <a href="{{ route('penyewa.detail', $item->id) }}" class="text-decoration-none text-dark">
                    <div class="card shadow-sm border-0 h-100">
                        <img src="{{ asset('poto/'.$item->foto) }}" class="card-img-top"
                             alt="Foto Lapangan" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->nama_lapangan }}</h5>
                            <p class="text-muted mb-1">
                                <i class="fa-solid fa-location-dot text-success me-1"></i>{{ $item->lokasi }}
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
<script>
    setTimeout(() => {
        document.getElementById('alert-error')?.style.display = 'none';
        document.getElementById('alert-success')?.style.display = 'none';
    }, 3000);
</script>

<!-- Script tambah ulasan -->
<script>
    const stars = document.querySelectorAll('.star-rating i');
    const ratingInput = document.getElementById('ratingInput');

    stars.forEach(star => {
        star.addEventListener('mouseenter', () => highlightStars(star.getAttribute('data-value')));
        star.addEventListener('mouseleave', () => highlightStars(ratingInput.value));
        star.addEventListener('click', () => {
            ratingInput.value = star.getAttribute('data-value');
            highlightStars(star.getAttribute('data-value'));
        });
    });

    function highlightStars(rating) {
        stars.forEach(star => {
            if (star.getAttribute('data-value') <= rating) {
                star.classList.remove('fa-regular');
                star.classList.add('fa-solid');
            } else {
                star.classList.remove('fa-solid');
                star.classList.add('fa-regular');
            }
        });
    }
</script>

<!-- Script edit ulasan -->
@foreach($ulasans as $ulasan)
<script>
    const stars{{ $ulasan->id }} = document.querySelectorAll('#starRating{{ $ulasan->id }} i');
    const rating{{ $ulasan->id }} = document.getElementById('ratingInput{{ $ulasan->id }}');

    function highlight{{ $ulasan->id }}(rating) {
        stars{{ $ulasan->id }}.forEach(star => {
            if (star.getAttribute('data-value') <= rating) {
                star.classList.replace('fa-regular', 'fa-solid');
            } else {
                star.classList.replace('fa-solid', 'fa-regular');
            }
        });
    }

    highlight{{ $ulasan->id }}(rating{{ $ulasan->id }}.value);

    stars{{ $ulasan->id }}.forEach(star => {
        star.addEventListener('mouseenter', () => highlight{{ $ulasan->id }}(star.getAttribute('data-value')));
        star.addEventListener('mouseleave', () => highlight{{ $ulasan->id }}(rating{{ $ulasan->id }}.value));
        star.addEventListener('click', () => {
            rating{{ $ulasan->id }}.value = star.getAttribute('data-value');
            highlight{{ $ulasan->id }}(rating{{ $ulasan->id }}.value);
        });
    });
</script>
@endforeach
@endsection
