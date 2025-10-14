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
        $existingUlasan = $existingUlasan ?? null;
        $canReview = $canReview ?? false;
        $favoritTableExists = \Illuminate\Support\Facades\Schema::hasTable('favorit_lapangan');
        $isFavorit = $isFavorit ?? ($favoritTableExists && Auth::check() && Auth::user()->role === 'penyewa'
            ? Auth::user()->favoritLapangan()->where('lapangan_id', $lapangan->id)->exists()
            : false);
    @endphp

    @foreach (['success', 'error'] as $flash)
        @if (session($flash))
            <div class="alert alert-{{ $flash === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                {{ session($flash) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    @endforeach

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
                <a href="#" class="btn btn-success px-4" data-bs-toggle="modal" data-bs-target="#pemesananModal">Pesan</a>

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



<!-- Modal Lihat Ulasan -->
<div class="modal fade" id="ulasanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ulasan {{ $lapangan->nama_lapangan }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($ulasans->isEmpty())
                    <p class="text-muted mb-0">Belum ada ulasan untuk lapangan ini.</p>
                @else
                    <div class="list-group">
                        @foreach($ulasans as $ulasan)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $ulasan->penyewa->name ?? 'Penyewa' }}</h6>
                                        <div class="text-warning mb-1">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if($i <= $ulasan->rating)
                                                    <i class="fa-solid fa-star"></i>
                                                @else
                                                    <i class="fa-regular fa-star"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <p class="mb-1">{{ $ulasan->komentar ?? 'Tidak ada komentar.' }}</p>
                                        <small class="text-muted">Diberikan pada {{ optional($ulasan->created_at)->format('d M Y') }}</small>
                                    </div>
                                    @if(!empty($ulasan->is_mine))
                                        <span class="badge bg-success">Ulasan Kamu</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="modal-footer justify-content-between">
                <div class="text-muted small">
                    @if($canReview)
                        Kamu dapat menulis atau mengubah ulasanmu.
                    @else
                        Selesaikan pemesanan untuk menulis ulasan.
                    @endif
                </div>
                @if($canReview)
                    <div class="d-flex gap-2">
                        @if($existingUlasan)
                            <form action="{{ route('penyewa.ulasan.destroy', $existingUlasan) }}" method="POST" onsubmit="return confirm('Hapus ulasan kamu?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Hapus Ulasan</button>
                            </form>
                        @endif
                        <button type="button" class="btn btn-success" data-bs-target="#ulasanFormModal" data-bs-toggle="modal" data-bs-dismiss="modal">
                            {{ $existingUlasan ? 'Edit Ulasan' : 'Tulis Ulasan' }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Ulasan -->
@if($canReview)
<div class="modal fade" id="ulasanFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('penyewa.ulasan.store', $lapangan) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">{{ $existingUlasan ? 'Perbarui' : 'Tulis' }} Ulasan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="rating" class="form-label">Rating</label>
                    <select name="rating" id="rating" class="form-select" required>
                        <option value="" disabled {{ old('rating', optional($existingUlasan)->rating) ? '' : 'selected' }}>Pilih rating</option>
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ (int) old('rating', optional($existingUlasan)->rating) === $i ? 'selected' : '' }}>
                                {{ $i }} - {{ ['Sangat Buruk','Buruk','Cukup','Bagus','Sangat Bagus'][$i-1] }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="mb-3">
                    <label for="komentar" class="form-label">Komentar</label>
                    <textarea name="komentar" id="komentar" class="form-control" rows="4" placeholder="Bagikan pengalamanmu">{{ old('komentar', optional($existingUlasan)->komentar) }}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">{{ $existingUlasan ? 'Simpan Perubahan' : 'Kirim Ulasan' }}</button>
            </div>
        </form>
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => alert.classList.add('d-none'));
    }, 4000);
</script>
@endsection
