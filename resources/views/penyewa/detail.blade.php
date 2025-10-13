@extends('layouts.sidebar')

@section('title', 'Detail Lapangan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="detail-lapangan container py-4">

    <!-- ALERT ERROR / SUCCESS -->
    @if(session('error'))
        <div id="alert-error" class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div id="alert-success" class="alert alert-success">{{ session('success') }}</div>
    @endif

    <h1 class="fw-bold" style="color: var(--primary-green);">Detail {{ $lapangan->nama_lapangan }}</h1>

    <div class="row g-4 align-items-start">
        <!-- FOTO -->
        <div class="col-md-5">
            <div id="carouselLapanganDetail" class="carousel slide shadow-sm rounded-4 overflow-hidden" 
                 data-bs-ride="carousel" data-bs-interval="3000">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="{{ asset('poto/'.$lapangan->foto) }}" 
                             class="d-block w-100" alt="Foto Lapangan"
                             style="height: 350px; object-fit: cover;">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselLapanganDetail" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselLapanganDetail" data-bs-slide="next">
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
                    @for ($i=1; $i<=5; $i++)
                        @if($i <= floor($avgRating))
                            <i class="fa-solid fa-star text-warning"></i>
                        @elseif ($i == ceil($avgRating) && $avgRating - floor($avgRating) >= 0.5)
                            <i class="fa-solid fa-star-half-stroke text-warning"></i>
                        @else
                            <i class="fa-regular fa-star text-warning"></i>
                        @endif
                    @endfor
                    ({{ number_format($avgRating,1) }}/5 dari {{ $totalUlasan }} ulasan)
                @else
                    <span class="text-muted">Belum ada ulasan</span>
                @endif
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="#" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#ulasanModal">Lihat Ulasan</a>
                <a href="#" class="btn btn-success px-4">Pesan</a>
            </div>
        </div>
    </div>

    <!-- Modal Lihat Ulasan -->
    <div class="modal fade" id="ulasanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ulasan {{ $lapangan->nama_lapangan }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($ulasans->count() > 0)
                        <div class="ulasan-list" style="max-height:400px; overflow-y:auto;">
                            @foreach($ulasans as $ulasan)
                                <div class="d-flex align-items-start mb-3">
                                    <img src="{{ asset('poto/'.$ulasan->user_foto ?? 'default.jpg') }}" 
                                         class="rounded-circle me-3" width="50" height="50">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-1">{{ $ulasan->username }}</h6>
                                            @if(auth()->check() && $ulasan->user_id == auth()->id())
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUlasanModal{{ $ulasan->id }}">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <form action="{{ route('ulasan.hapus', $ulasan->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                        <p class="mb-1">
                                            @for($i=1; $i<=5; $i++)
                                                @if($i <= $ulasan->rating)
                                                    <i class="fa-solid fa-star text-warning"></i>
                                                @else
                                                    <i class="fa-regular fa-star text-warning"></i>
                                                @endif
                                            @endfor
                                        </p>
                                        <p>{{ $ulasan->komentar }}</p>
                                    </div>
                                </div>
                                <hr>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Belum ada ulasan untuk lapangan ini.</p>
                    @endif
                    <div class="mt-3">
                        <a href="#" class="btn btn-success px-4" data-bs-toggle="modal" data-bs-target="#tambahUlasanModal">
                            + Tambah Ulasan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Ulasan -->
    <div class="modal fade" id="tambahUlasanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('ulasan.simpan', $lapangan->id) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Ulasan {{ $lapangan->nama_lapangan }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="star-rating" data-rating="0">
                            @for($i=1; $i<=5; $i++)
                                <i class="fa-regular fa-star fa-2x text-warning" data-value="{{ $i }}"></i>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="komentar" class="form-label">Komentar</label>
                        <textarea name="komentar" id="komentar" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Kirim</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Ulasan -->
    @foreach($ulasans as $ulasan)
    <div class="modal fade" id="editUlasanModal{{ $ulasan->id }}" tabindex="-1" aria-labelledby="editUlasanLabel{{ $ulasan->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('ulasan.update', $ulasan->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUlasanLabel{{ $ulasan->id }}">Edit Ulasan {{ $lapangan->nama_lapangan }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Rating dengan bintang -->
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <div class="star-rating" data-rating="{{ $ulasan->rating }}" id="starRating{{ $ulasan->id }}">
                                @for($i=1; $i<=5; $i++)
                                    <i class="fa-regular fa-star fa-2x text-warning" data-value="{{ $i }}"></i>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" id="ratingInput{{ $ulasan->id }}" value="{{ $ulasan->rating }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="komentar{{ $ulasan->id }}" class="form-label">Komentar</label>
                            <textarea name="komentar" id="komentar{{ $ulasan->id }}" rows="3" class="form-control">{{ $ulasan->komentar }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <!-- LAPANGAN LAINNYA -->
    <h4 class="fw-bold mt-5 mb-3">Lapangan Lainnya</h4>
    <div class="row">
        @forelse($lainnya as $item)
            <div class="col-md-4 mb-4">
                <a href="{{ route('penyewa.detail', $item->id) }}" class="text-decoration-none text-dark">
                    <div class="card shadow-sm border-0 h-100">
                        <img src="{{ asset('poto/'.$item->foto) }}" class="card-img-top" alt="Foto Lapangan" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->nama_lapangan }}</h5>
                            <p class="text-muted mb-1"><i class="fa-solid fa-location-dot text-success me-1"></i>{{ $item->lokasi }}</p>
                            <p class="fw-semibold text-success">Rp {{ number_format($item->harga_per_jam,0,',','.') }}/jam</p>
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
        const alertError = document.getElementById('alert-error');
        if(alertError) alertError.style.display = 'none';
        const alertSuccess = document.getElementById('alert-success');
        if(alertSuccess) alertSuccess.style.display = 'none';
    }, 3000);
</script>

<!-- Script tambah ulasan -->
<script>
    const stars = document.querySelectorAll('.star-rating i');
    const ratingInput = document.getElementById('ratingInput');

    stars.forEach(star => {
        star.addEventListener('mouseenter', () => {
            const val = star.getAttribute('data-value');
            highlightStars(val);
        });

        star.addEventListener('mouseleave', () => {
            const val = ratingInput.value;
            highlightStars(val);
        });

        star.addEventListener('click', () => {
            const val = star.getAttribute('data-value');
            ratingInput.value = val;
            highlightStars(val);
        });
    });

    function highlightStars(rating) {
        stars.forEach(star => {
            if(star.getAttribute('data-value') <= rating){
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
    const starRating{{ $ulasan->id }} = document.querySelectorAll('#starRating{{ $ulasan->id }} i');
    const ratingInput{{ $ulasan->id }} = document.getElementById('ratingInput{{ $ulasan->id }}');

    function highlightStars{{ $ulasan->id }}(rating) {
        starRating{{ $ulasan->id }}.forEach(star => {
            if(star.getAttribute('data-value') <= rating){
                star.classList.remove('fa-regular');
                star.classList.add('fa-solid');
            } else {
                star.classList.remove('fa-solid');
                star.classList.add('fa-regular');
            }
        });
    }

    // Set rating awal
    highlightStars{{ $ulasan->id }}(ratingInput{{ $ulasan->id }}.value);

    starRating{{ $ulasan->id }}.forEach(star => {
        star.addEventListener('mouseenter', () => {
            highlightStars{{ $ulasan->id }}(star.getAttribute('data-value'));
        });
        star.addEventListener('mouseleave', () => {
            highlightStars{{ $ulasan->id }}(ratingInput{{ $ulasan->id }}.value);
        });
        star.addEventListener('click', () => {
            ratingInput{{ $ulasan->id }}.value = star.getAttribute('data-value');
            highlightStars{{ $ulasan->id }}(ratingInput{{ $ulasan->id }}.value);
        });
    });
</script>
@endforeach
@endsection