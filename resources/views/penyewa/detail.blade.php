
@extends('layouts.sidebar')

@section('title', 'Detail Lapangan')

@section('content')
<style>
.swal-rating i {
    font-size: 2rem;
    margin: 0 3px;
    cursor: pointer;
    transition: 0.2s;
}
.swal-rating i.active {
    color: #ffc107 !important;
}
#swalKomentar {
    width: 100%;
    resize: none;
}
</style>

<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">

<div class="container py-4">
    <h1 class="fw-bold" style="color: var(--primary-green);">Detail {{ $lapangan->nama_lapangan }}</h1>

    <!-- ALERT ERROR / SUCCESS -->
    @if(session('error'))
        <div id="alert-error" class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div id="alert-success" class="alert alert-success">{{ session('success') }}</div>
    @endif

    @php
        // normalisasi foto: selalu -> array $fotoList
        $fotoList = [];
        if (is_array($lapangan->foto)) {
            $fotoList = $lapangan->foto;
        } else {
            $decoded = @json_decode($lapangan->foto, true);
            if (is_array($decoded)) {
                $fotoList = $decoded;
            } elseif (!empty($lapangan->foto)) {
                $fotoList = [$lapangan->foto];
            }
        }

        // helper untuk menghasilkan url gambar
        function foto_url($file) {
            if (!$file) return null;
            // kalau sudah url lengkap
            if (strpos($file, 'http://') === 0 || strpos($file, 'https://') === 0) {
                return $file;
            }
            // coba gunakan storage path dulu
            return asset('storage/' . ltrim($file, '/'));
        }

        $reportCategories = \App\Models\LaporanPenyalahgunaan::CATEGORIES;
        $bolehLaporkan = false;
        $bolehUlas = false;
        $ratingSudahDiberikan = false;
        $existingRatingValue = null;

        if (Auth::check()) {
            $pemesananDasar = \App\Models\Pemesanan::where('penyewa_id', Auth::id())
                ->where('lapangan_id', $lapangan->id);

            $bolehLaporkan = (clone $pemesananDasar)->exists();

            $bolehUlas = (clone $pemesananDasar)
                ->where(function ($query) {
                    if (\Illuminate\Support\Facades\Schema::hasColumn('pemesanan', 'status_scan')) {
                        $query->where('status_scan', 'sudah_scan');
                    } else {
                        $query->where('is_scanned', true);
                    }
                })
                ->exists();

            $existingRatingRecord = \App\Models\Ulasan::where('penyewa_id', Auth::id())
                ->whereHas('pemesanan', function ($query) use ($lapangan) {
                    $query->where('lapangan_id', $lapangan->id);
                })
                ->whereNotNull('rating')
                ->orderBy('created_at')
                ->first();

            if ($existingRatingRecord) {
                $ratingSudahDiberikan = true;
                $existingRatingValue = $existingRatingRecord->rating;
            }
        }
    @endphp

    <div class="row g-4 align-items-start">
        <!-- FOTO (kiri) -->
        <div class="col-md-5">
            @if(count($fotoList) > 1)
                <div id="carouselLapanganDetail" class="carousel slide shadow-sm rounded-4 overflow-hidden"
                     data-bs-ride="carousel" data-bs-interval="3500">
                    <div class="carousel-inner">
                        @foreach($fotoList as $i => $f)
                            <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                <img src="{{ foto_url($f) }}" class="d-block w-100" alt="Foto {{ $lapangan->nama_lapangan }}"
                                     style="height: 350px; object-fit: cover;">
                            </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselLapanganDetail" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselLapanganDetail" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            @else
                @php $single = $fotoList[0] ?? null; @endphp
                <div class="card shadow-sm rounded-4 overflow-hidden">
                    <img src="{{ $single ? foto_url($single) : 'https://images.unsplash.com/photo-1459865264687-595d652de67e?w=1200&h=800&fit=crop' }}"
                         class="d-block w-100" alt="Foto {{ $lapangan->nama_lapangan }}"
                         style="height: 350px; object-fit: cover;">
                </div>
            @endif
        </div>

        <!-- INFORMASI (kanan) -->
        <div class="col-md-7">
            <h2 class="fw-bold mb-1">{{ $lapangan->nama_lapangan }}</h2>

            {{-- kategori/status --}}
            <div class="mb-2 d-flex align-items-center gap-2">
                @if(!empty($lapangan->nama_kategori))
                    <span class="badge bg-primary">{{ $lapangan->nama_kategori }}</span>
                @endif
            </div>

            {{-- deskripsi --}}
            <p class="text-muted mb-3">{{ $lapangan->deskripsi ?? 'Belum ada deskripsi.' }}</p>

            {{-- alamat --}}
            <div class="mb-2">
                <i class="fa-solid fa-location-dot text-success me-2"></i>
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($lapangan->lokasi ?? '') }}" target="_blank" class="text-decoration-none text-secondary">
                    {{ $lapangan->lokasi ?? 'Alamat tidak tersedia' }}
                </a>
            </div>

            {{-- harga --}}
            @php
                $hargaPerJam = $lapangan->harga_per_jam ?? $lapangan->harga_sewa ?? 0;

                if (!is_numeric($hargaPerJam) || $hargaPerJam <= 0) {
                    $sections = $lapangan->sections ?? collect();
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
            @endphp
            <div class="mb-3">
                <i class="fa-solid fa-tag text-success me-2"></i><b>Harga Rata-Rata:</b>
                @if($hargaPerJam > 0)
                    <span class="text-danger fw-semibold">
                        Rp {{ number_format($hargaPerJam, 0, ',', '.') }} / jam
                    </span>
                @else
                    <span class="text-muted">Harga belum tersedia</span>
                @endif
            </div>

            {{-- rating --}}
            <div class="mb-3">
                <strong>Rating:</strong>
                @if(($totalUlasan ?? 0) > 0)
                    @php $avg = round($avgRating ?? 0, 1); @endphp
                    <span class="ms-2">
                        @for($i=1; $i<=5; $i++)
                            @if($i <= floor($avg))
                                <i class="fa-solid fa-star text-warning"></i>
                            @elseif($i == ceil($avg) && ($avg - floor($avg)) >= 0.5)
                                <i class="fa-solid fa-star-half-stroke text-warning"></i>
                            @else
                                <i class="fa-regular fa-star text-warning"></i>
                            @endif
                        @endfor
                        <span class="ms-2 text-muted">({{ number_format($avg,1) }}/5 dari {{ $totalUlasan }} ulasan)</span>
                    </span>
                @else
                    <span class="ms-2 text-muted">Belum ada ulasan</span>
                @endif
            </div>

            {{-- tombol aksi --}}
            <div class="d-flex gap-2 mt-3">
                {{-- Lihat (ulasan) --}}
                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#ulasanModal">
                    <i class="fa-solid fa-comment-dots me-1"></i> Ulasan
                </button>

                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#jadwalModal">
                    <i class="fa-solid fa-calendar-days me-1"></i> Jadwal
                </button>

                {{-- Pesan --}}
                <a href="{{ route('pemesanan.create', $lapangan->id) }}" class="btn btn-success">
                    <i class="fa-solid fa-cart-plus me-1"></i> Pesan
                </a>

                {{-- Favorit (hanya untuk user penyewa) --}}
                @if (Auth::check() && Auth::user()->role === 'penyewa')
                    @php $favoritAktif = !empty($isFavorit) && $isFavorit; @endphp
                    <button
                        type="button"
                        class="btn {{ $favoritAktif ? 'btn-danger text-white' : 'btn-outline-danger' }} favorite-toggle-btn"
                        data-favorite-toggle="true"
                        data-is-favorit="{{ $favoritAktif ? 'true' : 'false' }}"
                        data-store-url="{{ route('favorit.store', $lapangan->id) }}"
                        data-destroy-url="{{ route('favorit.destroy', $lapangan->id) }}"
                    >
                        <i class="fa-solid fa-heart me-1"></i>
                        <span>{{ $favoritAktif ? 'Hapus Favorit' : 'Favorit' }}</span>
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#laporLapanganModal"
                        @disabled(!$bolehLaporkan)
                        title="{{ $bolehLaporkan ? 'Laporkan penyalahgunaan' : 'Laporkan hanya jika sudah pernah memesan lapangan ini' }}"
                    >
                        <i class="fa-solid fa-flag me-1"></i> Laporkan
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Ulasan (sama seperti sebelumnya) --}}
    <div class="modal fade" id="ulasanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ulasan {{ $lapangan->nama_lapangan }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if(($ulasans ?? collect())->count() > 0)
                        @php $ratingShownForUser = []; @endphp
                        <div class="ulasan-list" style="max-height:400px; overflow-y:auto;">
                            @foreach($ulasans as $ulasan)
                                @php
                                    $penyewaUlasan = optional($ulasan->pemesanan)->penyewa;
                                    $avatarUlasan = $penyewaUlasan?->foto_profil
                                        ? foto_url($penyewaUlasan->foto_profil)
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($penyewaUlasan->name ?? 'Penyewa') . '&background=41A67E&color=fff';
                                    $tampilkanRating = false;
                                    if (!in_array($ulasan->penyewa_id, $ratingShownForUser, true)) {
                                        $ratingShownForUser[] = $ulasan->penyewa_id;
                                        $tampilkanRating = true;
                                    }
                                @endphp
                                <div class="d-flex align-items-start mb-3">
                                    <img src="{{ $avatarUlasan }}"
                                         class="rounded-circle me-3" width="50" height="50" alt="{{ $penyewaUlasan->name ?? 'Penyewa' }}">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-1">{{ $penyewaUlasan->name ?? 'Penyewa' }}</h6>
                                            @if(auth()->check() && $ulasan->penyewa_id == auth()->id())
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('ulasan.edit', $ulasan->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                    <form action="{{ route('ulasan.hapus', $ulasan->id) }}" method="POST" class="d-inline"
                                                          data-confirm="Hapus ulasan ini?"
                                                          data-confirm-title="Konfirmasi Hapus"
                                                          data-confirm-icon="warning"
                                                          data-confirm-button="Ya, hapus"
                                                          data-cancel-button="Batal">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                        @if($tampilkanRating && !is_null($ulasan->rating))
                                            <p class="mb-1">
                                                @for($i=1; $i<=5; $i++)
                                                    @if($i <= $ulasan->rating)
                                                        <i class="fa-solid fa-star text-warning"></i>
                                                    @else
                                                        <i class="fa-regular fa-star text-warning"></i>
                                                    @endif
                                                @endfor
                                            </p>
                                        @elseif(auth()->check() && $ulasan->penyewa_id == auth()->id())
                                            <p class="mb-1 text-muted small">Komentar tambahan (rating tetap {{ $ulasan->rating }}/5)</p>
                                        @endif
                                        <p>{{ $ulasan->komentar }}</p>
                                    </div>
                                </div>
                                <hr>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Belum ada ulasan untuk lapangan ini.</p>
                    @endif

                    {{-- tombol tambah ulasan (jika bisa) --}}
                    <div class="mt-3">
                        @if ($bolehUlas && !$ratingSudahDiberikan)

<button type="button" class="btn btn-success px-4" id="btnTambahUlasan">
    + Tambah Ulasan
</button>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// =============================================
// 0. Fungsi: buka modal ulasan dengan aman
// =============================================
function openUlasanModal() {
    let el = document.getElementById("ulasanModal");
    if (!el) return;
    let modal = bootstrap.Modal.getOrCreateInstance(el);
    modal.show();
}

// =============================================
// 1. Tombol Tambah Ulasan
// =============================================
document.getElementById('btnTambahUlasan')?.addEventListener('click', function () {

    // Tutup modal agar tidak double
    let ulasanModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('ulasanModal'));
    ulasanModal.hide();

    setTimeout(() => {
        Swal.fire({
            title: 'Tambah Ulasan',
            html: `
                <div class="swal-rating mb-3">
                    ${[1,2,3,4,5].map(i =>
                        `<i class="fa-regular fa-star" data-rate="${i}"></i>`
                    ).join('')}
                </div>

                <textarea id="swalKomentar" class="form-control" rows="4"
                    placeholder="Bagikan pengalamanmu"></textarea>

                <input type="hidden" id="swalRating" value="0">
            `,
            showCancelButton: true,
            confirmButtonText: 'Kirim',
            width: 500,
            allowOutsideClick: false,

            didOpen: () => {
                const popup = Swal.getPopup();
                const stars = popup.querySelectorAll('.swal-rating i');
                const ratingInput = popup.querySelector('#swalRating');

                stars.forEach(star => {
                    star.addEventListener('click', () => {
                        let val = parseInt(star.dataset.rate);
                        ratingInput.value = val;

                        stars.forEach((s, idx) => {
                            if (idx < val) {
                                s.classList.remove('fa-regular');
                                s.classList.add('fa-solid', 'active');
                            } else {
                                s.classList.remove('fa-solid', 'active');
                                s.classList.add('fa-regular');
                            }
                        });
                    });
                });
            },

            preConfirm: () => {
                const komentar = Swal.getPopup().querySelector('#swalKomentar').value;
                const rating = Swal.getPopup().querySelector('#swalRating').value;

                if (!rating || rating == "0") Swal.showValidationMessage("Pilih rating dulu!");
                if (!komentar.trim()) Swal.showValidationMessage("Komentar tidak boleh kosong!");

                return { rating, komentar };
            }

        }).then((result) => {

            // Buka modal ulasan lagi (biar tidak bingung)
            openUlasanModal();

            if (result.isConfirmed) {

fetch("{{ route('ulasan.simpan', $lapangan->id) }}", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}"
    },
    body: JSON.stringify({
        rating: result.value.rating,
        komentar: result.value.komentar
    })
})
.then(res => res.json())
.then(data => {
    if (data.success) {

        // simpan penanda untuk buka modal setelah reload
        localStorage.setItem("openUlasanAfterReload", "1");

        // tampilkan alert sukses dulu
        Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: data.message,
        }).then(() => {

            // reload setelah user klik OK
            window.location.reload();
        });

    } else {
        Swal.fire("Gagal!", data.message, "error");
    }
});


            }
        });

    }, 150);
});

// =============================================
// 2. AUTO BUKA MODAL ULASAN SETELAH RELOAD
// =============================================
document.addEventListener("DOMContentLoaded", function () {

    if (localStorage.getItem("openUlasanAfterReload") === "1") {

        // Hapus status agar tidak repeat
        localStorage.removeItem("openUlasanAfterReload");

        // Delay kecil agar DOM modal sudah siap
        setTimeout(() => {
            openUlasanModal();
        }, 300);
    }

});
</script>



@elseif($bolehUlas && $ratingSudahDiberikan)
    <p class="text-muted small mt-2 mb-0">
        Kamu sudah pernah mengirim ulasan. Silakan gunakan tombol Edit untuk mengubah ulasanmu.
    </p>
@else

                            <button class="btn btn-secondary px-4" disabled>
                                + Tambah Ulasan (scan tiket terlebih dahulu)
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @auth


    {{-- CSS rating bintang --}}
    <style>
        .rating-stars {
            display: flex;
            flex-direction: row; /* kiri ke kanan */
        }
        .rating-stars input[type="radio"] {
            display: none;
        }
        .rating-stars label {
            cursor: pointer;
            font-size: 1.5rem;
            margin-right: 0.2rem;
        }
        .rating-stars label i {
            transition: color 0.2s;
        }
        .rating-stars input[type="radio"]:checked ~ label i,
        .rating-stars label:hover ~ label i,
        .rating-stars label:hover i {
            color: #ffc107 !important;
        }
    </style>

    {{-- JS agar saat klik berubah ikon --}}
    <script>
        document.querySelectorAll('.rating-stars').forEach(starContainer => {
            const stars = starContainer.querySelectorAll('label i');
            const radios = starContainer.querySelectorAll('input[type="radio"]');

            stars.forEach((star, idx) => {
                star.addEventListener('click', () => {
                    radios[idx].checked = true;
                    updateStars(starContainer);
                });
            });

            starContainer.addEventListener('mouseover', () => updateStars(starContainer));
            starContainer.addEventListener('mouseout', () => updateStars(starContainer));
        });

        function updateStars(container) {
            const radios = container.querySelectorAll('input[type="radio"]');
            const stars = container.querySelectorAll('label i');
            let checkedIndex = Array.from(radios).findIndex(r => r.checked);
            stars.forEach((star, idx) => {
                if (idx <= checkedIndex) {
                    star.classList.remove('fa-regular');
                    star.classList.add('fa-solid');
                } else {
                    star.classList.remove('fa-solid');
                    star.classList.add('fa-regular');
                }
            });
        }
    </script>
@endauth

    @if (Auth::check() && Auth::user()->role === 'penyewa')
        <div class="modal fade" id="laporLapanganModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-flag text-danger me-2"></i> Laporkan Penyalahgunaan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('penyewa.laporan.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="lapangan_id" value="{{ old('lapangan_id', $lapangan->id) }}">
                        <div class="modal-body">
                            @if (!$bolehLaporkan)
                                <div class="alert alert-warning">
                                    Kamu hanya bisa melaporkan setelah memiliki riwayat pemesanan di lapangan ini.
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Lapangan</label>
                                <div class="form-control bg-light">{{ $lapangan->nama_lapangan }}</div>
                                @error('lapangan_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kategori Laporan</label>
                                <select
                                    name="kategori"
                                    class="form-select @error('kategori') is-invalid @enderror"
                                    @disabled(!$bolehLaporkan)
                                >
                                    <option value="">Pilih kategori</option>
                                    @foreach ($reportCategories as $key => $label)
                                        <option value="{{ $key }}" @selected(old('kategori') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('kategori')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Deskripsi Kejadian</label>
                                <textarea
                                    name="deskripsi"
                                    rows="5"
                                    class="form-control @error('deskripsi') is-invalid @enderror"
                                    placeholder="Tuliskan kronologi secara rinci"
                                    @disabled(!$bolehLaporkan)
                                >{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimal 20 karakter.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger" @disabled(!$bolehLaporkan)>
                                <i class="fa-solid fa-paper-plane me-1"></i> Kirim Laporan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL JADWAL LAPANGAN --}}
<div class="modal fade" id="jadwalModal" tabindex="-1" aria-labelledby="jadwalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            {{-- HEADER --}}
            <div class="modal-header border-0 d-flex align-items-center justify-content-between">
                <h5 class="modal-title fw-bold text-dark" id="jadwalModalLabel">
                    Jadwal Lapangan {{ $lapangan->nama_lapangan }}
                </h5>
                <div class="d-flex align-items-center gap-2">
                    @php
                        $totalTersedia = $lapangan->sections
                            ->flatMap->jadwal
                            ->where('tersedia', true)
                            ->count();
                    @endphp
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                        Total jadwal: {{ $totalTersedia }}
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            {{-- BODY --}}
            <div class="modal-body">

            {{-- FILTER --}}
                <div class="row g-3 mb-3 align-items-end">
                    {{-- Filter Tanggal / Bulan --}}
                    <div class="col">
                        <label for="filterTanggalBulan" class="form-label fw-semibold mb-1">Tanggal / Bulan</label>
                        <input type="month" id="filterTanggalBulan" class="form-control" placeholder="2025-03" />
                    </div>

                    {{-- Filter Section --}}
                    <div class="col">
                        <label for="filterSection" class="form-label fw-semibold mb-1">Section</label>
                        <select id="filterSection" class="form-select">
                            <option value="">Semua Section</option>
                            @foreach($lapangan->sections as $section)
                                <option value="{{ $section->nama_section }}">{{ $section->nama_section }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Jam Mulai --}}
                    <div class="col">
                        <label for="filterJamMulai" class="form-label fw-semibold mb-1">Jam Mulai</label>
                        <input type="time" id="filterJamMulai" class="form-control" />
                    </div>

                    {{-- Filter Jam Selesai --}}
                    <div class="col">
                        <label for="filterJamSelesai" class="form-label fw-semibold mb-1">Jam Selesai</label>
                        <input type="time" id="filterJamSelesai" class="form-control" />
                    </div>

                    {{-- Reset --}}
                    <div class="col-auto d-flex align-items-end">
                        <button class="btn btn-success w-100" id="resetFilters">
                            <i class="fa fa-rotate-left me-1"></i> Reset Filter
                        </button>
                    </div>
                </div>

                {{-- TABEL JADWAL --}}
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        @php
                            use Carbon\Carbon;
                            $jadwalTersedia = $lapangan->sections
                                ->flatMap->jadwal
                                ->where('tersedia', true)
                                ->sortBy(['tanggal', 'jam_mulai']);
                        @endphp

                        @if($jadwalTersedia->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 table-bordered text-center">
                                    <thead class="text-white fw-semibold" style="background-color: #198754;">
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Section</th>
                                            <th>Rentang Waktu</th>
                                            <th>Durasi</th>
                                            <th>Harga Total</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jadwalTersedia as $i => $jadwal)
                                            @php
                                                $mulai = Carbon::parse($jadwal->jam_mulai);
                                                $selesai = Carbon::parse($jadwal->jam_selesai);
                                                $durasiMenit = $jadwal->durasi_sewa ?? $mulai->diffInMinutes($selesai);
                                                $durasiJam = $durasiMenit / 60;
                                            @endphp
                                            <tr 
                                                data-tanggal="{{ Carbon::parse($jadwal->tanggal)->format('Y-m-d') }}"
                                                data-section="{{ $jadwal->section->nama_section ?? '' }}" 
                                                data-jam-mulai="{{ $mulai->format('H:i') }}"
                                            >
                                                <td class="fw-semibold">{{ $i + 1 }}</td>
                                                <td>{{ Carbon::parse($jadwal->tanggal)->translatedFormat('d M Y') }}</td>
                                                <td>{{ $jadwal->section->nama_section ?? '-' }}</td>
                                                <td>
                                                    <div class="d-flex flex-column small fw-semibold">
                                                        <span>{{ $mulai->format('H:i') }} WIB</span>
                                                        <span class="text-muted">s/d {{ $selesai->format('H:i') }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success-subtle text-success px-3 py-2">
                                                        {{ rtrim(rtrim(number_format($durasiJam, 2, ',', '.'), '0'), ',') }} jam
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-success">Rp {{ number_format($jadwal->harga_total, 0, ',', '.') }}</div>
                                                    <small class="text-muted d-block">Rp {{ number_format($jadwal->harga_sewa, 0, ',', '.') }} / jam</small>
                                                </td>
                                                <td>
                                                    <span class="badge px-3 py-2 {{ $jadwal->tersedia ? 'bg-gradient bg-success' : 'bg-secondary' }}">
                                                        {{ $jadwal->tersedia ? 'Tersedia' : 'Tidak Tersedia' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('pemesanan.create', $lapangan->id) }}" class="btn btn-outline-success">
                                                        <i class="fa-solid fa-cart-plus me-1"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- SUMMARY + PAGINATION --}}
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div id="pagination-summary" class="small text-muted"></div>
                                <ul class="pagination mb-0" id="pagination"></ul>
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
</div>

{{-- SCRIPT PAGINATION + FILTER --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterTanggalBulan = document.getElementById('filterTanggalBulan');
    const filterSection = document.getElementById('filterSection');
    const filterJamMulai = document.getElementById('filterJamMulai');
    const filterJamSelesai = document.getElementById('filterJamSelesai');
    const resetBtn = document.getElementById('resetFilters');
    const tbody = document.querySelector('#jadwalModal tbody');
    const pagination = document.getElementById('pagination');
    const summaryEl = document.getElementById('pagination-summary');

    const rowsPerPage = 6;
    let currentPage = 1;

    function getFilteredRows() {
        return Array.from(tbody.querySelectorAll('tr')).filter(row => {
            const tgl = row.dataset.tanggal; // YYYY-MM-DD
            const section = row.dataset.section;
            const jam = row.dataset.jamMulai;

            let filterOk = true;

            if(filterTanggalBulan.value) {
                const input = filterTanggalBulan.value; // YYYY-MM
                filterOk = tgl.startsWith(input); // semua tanggal di bulan itu
            }

            return (
                filterOk &&
                (!filterSection.value || filterSection.value === section) &&
                (!filterJamMulai.value || jam === filterJamMulai.value) &&
                (!filterJamSelesai.value || jam === filterJamSelesai.value)
            );
        });
    }

    function showPage(page = 1) {
        const rows = getFilteredRows();
        const totalPages = Math.ceil(rows.length / rowsPerPage);
        currentPage = Math.min(Math.max(1, page), totalPages);

        tbody.querySelectorAll('tr').forEach(row => row.style.display = 'none');

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        let no = start + 1;
        rows.slice(start, end).forEach(row => {
            row.style.display = '';
            row.querySelector('td:first-child').textContent = no++;
        });

        summaryEl.textContent = rows.length === 0
            ? 'Jadwal tidak tersedia'
            : `Menampilkan ${start + 1} - ${Math.min(end, rows.length)} dari ${rows.length} jadwal | Halaman ${currentPage} / ${totalPages}`;

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        let html = '';

        html += currentPage > 1
            ? `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage-1}">&lt;</a></li>`
            : `<li class="page-item disabled"><span class="page-link">&lt;</span></li>`;

        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>`;
        }

        html += currentPage < totalPages
            ? `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage+1}">&gt;</a></li>`
            : `<li class="page-item disabled"><span class="page-link">&gt;</span></li>`;

        pagination.innerHTML = html;

        pagination.querySelectorAll('a.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                if(!isNaN(page)) showPage(page);
            });
        });
    }

    function filterAndPaginate() {
        currentPage = 1;
        showPage(currentPage);
    }

    filterTanggalBulan.addEventListener('change', filterAndPaginate);
    filterSection.addEventListener('change', filterAndPaginate);
    filterJamMulai.addEventListener('input', filterAndPaginate);
    filterJamSelesai.addEventListener('input', filterAndPaginate);

    resetBtn.addEventListener('click', function() {
        filterTanggalBulan.value = '';
        filterSection.value = '';
        filterJamMulai.value = '';
        filterJamSelesai.value = '';
        filterAndPaginate();
    });

    showPage(1);
});
</script>

    {{-- LAPANGAN LAINNYA (tampilan seperti beranda) --}}
    <h4 class="fw-bold mt-5 mb-3">Lapangan Lainnya</h4>
    <div class="row g-4">
        @forelse($lapanganLainnya ?? collect() as $item)
            @php
                $fotoArray = is_array($item->foto) ? $item->foto : (@json_decode($item->foto, true) ?: [$item->foto]);
                $fotoArray = array_filter($fotoArray);
                $sections = $item->sections ?? collect();
                $totalSections = $sections->count();
                $totalJadwal = 0;
                $hargaRataRata = 0;
                foreach ($sections as $section) {
                    $totalJadwal += $section->jadwal->count();
                    if ($section->jadwal->count()) {
                        $hargaRataRata += $section->jadwal->avg('harga_sewa');
                    }
                }
                if ($totalSections > 0) $hargaRataRata = $hargaRataRata / $totalSections;
            @endphp

            <div class="col-lg-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden hover-lift">
                    <div class="position-relative" style="height: 220px; overflow: hidden;">
                        @if(!empty($fotoArray))
                            <img src="{{ foto_url(array_values($fotoArray)[0]) }}" class="w-100 h-100" style="object-fit: cover;">
                        @else
                            <img src="https://images.unsplash.com/photo-1459865264687-595d652de67e?w=1200&h=800&fit=crop" class="w-100 h-100" style="object-fit: cover;">
                        @endif

                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-primary px-3 py-2"><i class="fa-solid fa-layer-group me-1"></i> {{ $totalSections }} Section</span>
                        </div>
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-success px-3 py-2"><i class="fa-solid fa-calendar me-1"></i> {{ $totalJadwal }} Jadwal</span>
                        </div>
                        <div class="position-absolute bottom-0 start-0 m-3" style="z-index:10;">
                            <span class="badge bg-dark bg-opacity-75 px-3 py-2"><i class="fa-solid fa-tag me-1"></i> {{ ucfirst($item->kategori ?? '') }}</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <h5 class="fw-bold">{{ $item->nama_lapangan }}</h5>
                        <p class="text-muted small mb-2"><i class="fa-solid fa-location-dot text-success me-1"></i> {{ Str::limit($item->lokasi, 50) }}</p>
                        <p class="text-muted small">{{ Str::limit($item->deskripsi, 100) }}</p>

                        @if ($hargaRataRata > 0)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted"><i class="fa-solid fa-money-bill-wave text-success me-1"></i> Harga Rata-rata</small>
                                <span class="fw-bold text-success">Rp {{ number_format($hargaRataRata,0,',','.') }} / jam</span>
                            </div>
                        @endif

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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            const alertError = document.getElementById('alert-error');
            if (alertError) alertError.style.display = 'none';
            const alertSuccess = document.getElementById('alert-success');
            if (alertSuccess) alertSuccess.style.display = 'none';
        }, 3000);

        const favoriteBtn = document.querySelector('[data-favorite-toggle="true"]');
        if (favoriteBtn) {
            favoriteBtn.addEventListener('click', () => {
                if (favoriteBtn.dataset.loading === 'true') return;
                favoriteBtn.dataset.loading = 'true';
                favoriteBtn.classList.add('disabled');

                const isFavorit = favoriteBtn.dataset.isFavorit === 'true';
                const url = isFavorit ? favoriteBtn.dataset.destroyUrl : favoriteBtn.dataset.storeUrl;

                fetch(url, {
                    method: isFavorit ? 'DELETE' : 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(response => {
                    if (!response.ok) throw new Error('failed');
                    return response.json();
                })
                .then(() => {
                    favoriteBtn.dataset.isFavorit = isFavorit ? 'false' : 'true';
                    favoriteBtn.querySelector('span').textContent = isFavorit ? 'Favorit' : 'Hapus Favorit';
                    favoriteBtn.classList.toggle('btn-outline-danger', isFavorit);
                    favoriteBtn.classList.toggle('btn-danger', !isFavorit);
                    favoriteBtn.classList.toggle('text-white', !isFavorit);
                })
                .catch(() => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Gagal', 'Tidak dapat memperbarui status favorit.', 'error');
                    } else {
                        alert('Tidak dapat memperbarui status favorit.');
                    }
                })
                .finally(() => {
                    favoriteBtn.dataset.loading = 'false';
                    favoriteBtn.classList.remove('disabled');
                });
            });
        }

    });


</script>

@endsection

@push('scripts')
@if (
    (old('lapangan_id') == $lapangan->id) &&
    ($errors->has('lapangan_id') || $errors->has('kategori') || $errors->has('deskripsi'))
)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalElement = document.getElementById('laporLapanganModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        });
    </script>
@endif
@endpush
