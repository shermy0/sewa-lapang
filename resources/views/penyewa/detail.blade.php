
@extends('layouts.sidebar')

@section('title', 'Detail Lapangan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#ulasanModal">
                    <i class="fa-solid fa-comment-dots me-1"></i> Ulasan
                </button>

                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#jadwalModal">
                    <i class="fa-solid fa-calendar-days me-1"></i> Jadwal
                </button>

                {{-- Pesan --}}
                <a href="{{ route('pemesanan.create', $lapangan->id) }}" class="btn btn-success">
                    <i class="fa-solid fa-cart-plus me-1"></i> Pesan
                </a>

                {{-- Favorit (hanya untuk user penyewa) --}}
                @if (Auth::check() && Auth::user()->role === 'penyewa')
                    @if (!empty($isFavorit) && $isFavorit)
                        <form action="{{ route('favorit.destroy', $lapangan->id) }}" method="POST" class="m-0 p-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fa-solid fa-heart-crack me-1"></i> Hapus Favorit
                            </button>
                        </form>
                    @else
                        <form action="{{ route('favorit.store', $lapangan->id) }}" method="POST" class="m-0 p-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fa-solid fa-heart me-1"></i> Favorit
                            </button>
                        </form>
                    @endif

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
                        <div class="ulasan-list" style="max-height:400px; overflow-y:auto;">
                            @foreach($ulasans as $ulasan)
                                <div class="d-flex align-items-start mb-3">
                                    <img src="{{ foto_url($ulasan->user_foto ?? null) ?? asset('poto/default.jpg') }}"
                                         class="rounded-circle me-3" width="50" height="50" alt="{{ $ulasan->username }}">
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

                    {{-- tombol tambah ulasan (jika bisa) --}}
                    <div class="mt-3">
                        @if ($bolehUlas)
                            <a href="#" class="btn btn-success px-4" data-bs-toggle="modal" data-bs-target="#tambahUlasanModal">
                                + Tambah Ulasan
                            </a>
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
        {{-- Modal Tambah Ulasan --}}
        <div class="modal fade" id="tambahUlasanModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Ulasan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('ulasan.simpan', $lapangan->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Rating</label>
                                <select name="rating" class="form-select" required>
                                    <option value="" disabled selected>Pilih rating</option>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}">{{ $i }} / 5</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Komentar</label>
                                <textarea name="komentar" class="form-control" rows="4" placeholder="Bagikan pengalamanmu" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">Kirim Ulasan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Edit Ulasan --}}
        @foreach(($ulasans ?? collect()) as $ulasan)
            @if(auth()->id() === $ulasan->user_id)
                <div class="modal fade" id="editUlasanModal{{ $ulasan->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Ulasan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('ulasan.update', $ulasan->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Rating</label>
                                        <select name="rating" class="form-select" required>
                                            @for ($i = 1; $i <= 5; $i++)
                                                <option value="{{ $i }}" {{ $ulasan->rating == $i ? 'selected' : '' }}>{{ $i }} / 5</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Komentar</label>
                                        <textarea name="komentar" class="form-control" rows="4" required>{{ $ulasan->komentar }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
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
            <div class="modal-header border-0 d-flex align-items-center justify-content-between">
                <h5 class="modal-title fw-bold text-dark" id="jadwalModalLabel">
                    Jadwal Lapangan {{ $lapangan->nama_lapangan }}
                </h5>

                {{-- Wrapper kanan: Reset, Total Jadwal, Close --}}
                <div class="d-flex align-items-center gap-2">
                    {{-- Tombol Reset --}}
                    <button class="btn btn-sm btn-success" id="resetFilters">
                        <i class="fa fa-rotate-left me-1"></i> Reset
                    </button>

                    @php
                        $totalTersedia = $lapangan->jadwal->where('tersedia', true)->count();
                    @endphp

                    {{-- Badge Total Jadwal --}}
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                        Total jadwal: {{ $totalTersedia }}
                    </span>

                    {{-- Tombol Close --}}
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body">
                {{-- Card Jadwal --}}
                <div class="card shadow-sm border-0">
                    <div class="card-body p-3">
                        @php
                            use Carbon\Carbon;
                            $jadwalTersedia = $lapangan->jadwal
                                ->where('tersedia', true)
                                ->sortBy(['tanggal', 'jam_mulai']);
                        @endphp

                        @if($jadwalTersedia->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 table-bordered">
                                    <thead class="text-white fw-semibold text-center align-middle" style="background-color: #198754;">
                                        <tr>
                                            <th>No</th>
                                            <th>
                                                Tanggal
                                                <i class="fa fa-filter ms-1" style="cursor:pointer;" onclick="toggleFilter('filterTanggal')"></i>
                                                <div class="mt-1" id="filterTanggalDiv" style="display:none;">
                                                    <input type="date" id="filterTanggal" class="form-control form-control-sm" />
                                                </div>
                                            </th>

                                            <th>
                                                Section
                                                <i class="fa fa-filter ms-1" style="cursor:pointer;" onclick="toggleFilter('filterSection')"></i>
                                                <div class="mt-1" id="filterSectionDiv" style="display:none;">
                                                    <select id="filterSection" class="form-select form-select-sm">
                                                        <option value="">Semua Section</option>
                                                        @foreach($lapangan->sections as $section)
                                                            <option value="{{ $section->nama_section }}">{{ $section->nama_section }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </th>

                                            <th>
                                                Rentang Waktu
                                                <i class="fa fa-filter ms-1" style="cursor:pointer;" onclick="toggleFilter('filterJamMulai')"></i>
                                                <div class="mt-1" id="filterJamMulaiDiv" style="display:none;">
                                                    <input type="time" id="filterJamMulai" class="form-control form-control-sm" placeholder="Jam Mulai" />
                                                </div>
                                            </th>
                                            <th>Durasi</th>
                                            <th>Harga Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
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
                                                <td class="text-nowrap">{{ Carbon::parse($jadwal->tanggal)->translatedFormat('d M Y') }}</td>
                                                <td>{{ $jadwal->section->nama_section ?? '-' }}</td>
                                                <td class="text-nowrap">
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
</div>

{{-- Script Filter --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterTanggal = document.getElementById('filterTanggal');
    const filterSection = document.getElementById('filterSection');
    const filterJamMulai = document.getElementById('filterJamMulai');
    const resetBtn = document.getElementById('resetFilters');
    const rows = document.querySelectorAll('#jadwalModal tbody tr');

    function filterRows() {
        const tanggalVal = filterTanggal.value;
        const sectionVal = filterSection.value;
        const jamMulaiVal = filterJamMulai.value;

        rows.forEach(row => {
            const rowTanggal = row.getAttribute('data-tanggal');
            const rowSection = row.getAttribute('data-section');
            const rowJamMulai = row.getAttribute('data-jam-mulai');

            const matchTanggal = !tanggalVal || rowTanggal === tanggalVal;
            const matchSection = !sectionVal || rowSection === sectionVal;
            const matchJam = !jamMulaiVal || rowJamMulai >= jamMulaiVal;

            row.style.display = (matchTanggal && matchSection && matchJam) ? '' : 'none';
        });
    }

    filterTanggal.addEventListener('change', filterRows);
    filterSection.addEventListener('change', filterRows);
    filterJamMulai.addEventListener('input', filterRows);

    resetBtn.addEventListener('click', function() {
        filterTanggal.value = '';
        filterSection.value = '';
        filterJamMulai.value = '';
        filterRows();
    });
});

function toggleFilter(id) {
    const el = document.getElementById(id + 'Div');
    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
}
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
    // hide alerts automatically
    setTimeout(() => {
        const alertError = document.getElementById('alert-error');
        if (alertError) alertError.style.display = 'none';
        const alertSuccess = document.getElementById('alert-success');
        if (alertSuccess) alertSuccess.style.display = 'none';
    }, 3000);
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
