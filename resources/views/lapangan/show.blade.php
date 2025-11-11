@extends('layouts.sidebar')

@section('title', 'Detail Lapangan')

@section('content')
<div class="container py-4 py-lg-5">

    {{-- Header --}}
    <div class="row align-items-center mb-4 gy-3">
        <div class="col-lg-8">
            <div class="d-flex align-items-start gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fa-solid fa-futbol fa-lg"></i>
                </div>
                <div>
                    <h2 class="fw-bold text-dark mb-1">Detail Lapangan</h2>
                    <p class="text-muted mb-0">Informasi lengkap lapangan beserta section & jadwal</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a href="{{ route('lapangan.index') }}" class="btn btn-outline-secondary px-4">
                <i class="fa-solid fa-arrow-left-long me-2"></i> Kembali ke daftar
            </a>
        </div>
    </div>

    {{-- Alert --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
            <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
            <i class="fa-solid fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-10 mx-auto">
            <div class="row g-4 align-items-stretch flex-column flex-lg-row">
                {{-- Foto Lapangan --}}
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 h-100 overflow-hidden photo-card">
                        <div class="card-body p-0 bg-light h-100">
                            @php
                                $fotos = $lapangan->foto ?? [];
                                if (!is_array($fotos)) $fotos = [];
                            @endphp

                            @if(count($fotos) > 0)
                                @php $carouselId = 'carouselLapangan' . $lapangan->id; @endphp
                                <div id="{{ $carouselId }}" class="carousel slide carousel-lapangan h-100" data-bs-ride="carousel">
                                    <div class="carousel-inner rounded-top h-100">
                                        @foreach($fotos as $i => $foto)
                                            <div class="carousel-item {{ $i == 0 ? 'active' : '' }} h-100">
                                                <img src="{{ asset('storage/' . $foto) }}"
                                                     class="d-block w-100 h-100 object-fit-cover"
                                                     alt="Foto Lapangan {{ $i + 1 }}"
                                                     onerror="this.src='https://images.unsplash.com/photo-1459865264687-595d652de67e?w=1000&q=80'">
                                            </div>
                                        @endforeach
                                    </div>
                                    @if(count($fotos) > 1)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon"></span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon"></span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <div class="w-100 h-100 bg-light-subtle d-flex align-items-center justify-content-center">
                                    <img src="https://images.unsplash.com/photo-1459865264687-595d652de67e?w=1000&q=80"
                                         class="w-100 h-100 object-fit-cover"
                                         alt="Default Lapangan">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Detail Lapangan --}}
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4 p-lg-5 d-flex flex-column">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-8">
                                    <h3 class="fw-bold text-dark mb-2">{{ $lapangan->nama_lapangan ?? 'Nama Lapangan' }}</h3>
                                    <div class="d-flex flex-wrap gap-3 align-items-center text-muted">
                                        <span class="d-flex align-items-center">
                                            <i class="fa-solid fa-location-dot text-danger me-2"></i>
                                            {{ $lapangan->lokasi ?? 'Lokasi belum diisi' }}
                                        </span>
                                        <span class="badge bg-success text-uppercase px-3 py-2">
                                            {{ $lapangan->kategori ? ucfirst($lapangan->kategori) : 'Tanpa Kategori' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    @if($lapangan->harga_sewa)
                                        <div class="small text-muted">Tarif dasar per jam</div>
                                        <div class="display-6 fw-bold text-success">
                                            Rp {{ number_format($lapangan->harga_sewa, 0, ',', '.') }}
                                        </div>
                                    @else
                                        <div class="small text-muted">Harga belum diatur</div>
                                        <div class="text-muted">-</div>
                                    @endif
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <span class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                            <i class="fa-solid fa-calendar-check"></i>
                                        </span>
                                        <div>
                                            <div class="fw-semibold text-dark">Total Jadwal Aktif</div>
                                            <div class="fs-4 fw-bold">{{ $lapangan->jadwal->count() }} slot</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="bg-light rounded-3 p-3 h-100">
                                        <div class="fw-semibold text-dark mb-1">Deskripsi Lapangan</div>
                                        <p class="mb-0 text-muted">
                                            {{ $lapangan->deskripsi ?: 'Belum ada deskripsi yang ditambahkan untuk lapangan ini.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Informasi Sections --}}
                            @if($lapangan->sections && $lapangan->sections->count() > 0)
                            <div class="mt-4">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="fw-semibold text-dark">
                                        <i class="fa-solid fa-layer-group text-primary me-2"></i> 
                                        Total Section: {{ $lapangan->sections->count() }}
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        {{ $lapangan->sections->count() }} Area
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($lapangan->sections->take(4) as $section)
                                        <span class="badge bg-light text-dark border px-3 py-2">
                                            <i class="fa-solid fa-square me-1 text-primary"></i>
                                            {{ $section->nama_section }}
                                            <small class="text-muted ms-1">({{ $section->jadwal_count ?? $section->jadwal->count() }} jadwal)</small>
                                        </span>
                                    @endforeach
                                    @if($lapangan->sections->count() > 4)
                                        <span class="badge bg-light text-muted border">
                                            +{{ $lapangan->sections->count() - 4 }} lainnya
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endif

                          
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section Lapangan --}}
            @if($lapangan->sections && $lapangan->sections->count() > 0)
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-white border-0 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">
                                <i class="fa-solid fa-layer-group text-primary me-2"></i> Section Lapangan
                            </h5>
                            <span class="text-muted">Daftar bagian lapangan yang tersedia</span>
                        </div>
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 me-2">
                                Total: {{ $lapangan->sections->count() }} Section
                            </span>
                            <a href="#" class="btn btn-primary">
                                <i class="fa-solid fa-plus me-1"></i> Tambah Section
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="bg-primary bg-opacity-10 text-primary fw-semibold">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Nama Section</th>
                                    <th>Deskripsi</th>
                                    <th class="text-center">Harga Default / Jam</th>
                                    <th class="text-center">Jumlah Jadwal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lapangan->sections as $i => $section)
                                    <tr>
                                        <td class="text-center fw-semibold">{{ $i + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $section->nama_section ?? '-' }}</div>
                                            <small class="text-muted">ID: {{ $section->id }}</small>
                                        </td>
                                        <td>{{ $section->deskripsi ?? 'Tidak ada deskripsi' }}</td>
                                        <td class="text-center">
                                            @if($section->harga_per_jam && $section->harga_per_jam > 0)
                                                <span class="fw-bold text-success">
                                                    Rp {{ number_format($section->harga_per_jam, 0, ',', '.') }}
                                                </span>
                                                <div class="text-muted small">/ jam</div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                                {{ $section->jadwal_count ?? $section->jadwal->count() }} jadwal
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body text-center text-muted py-5">
                    <i class="fa-solid fa-layer-group fa-3x mb-3 opacity-50"></i>
                    <h5 class="fw-semibold">Belum ada section</h5>
                    <p class="mb-4">Tambahkan section untuk mengelola bagian-bagian lapangan ini.</p>
                    <a href="#" class="btn btn-primary">
                        <i class="fa-solid fa-plus me-2"></i> Tambah Section Pertama
                    </a>
                </div>
            </div>
            @endif

            {{-- Jadwal Lapangan --}}
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-white border-0 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">
                                <i class="fa-solid fa-calendar-days text-success me-2"></i> Jadwal Lapangan
                            </h5>
                            <span class="text-muted">Daftar slot waktu yang tersedia</span>
                        </div>
                        <div>
                            @if(isset($jadwalPaginate) && $jadwalPaginate->total() > 0)
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 me-2">
                                    Total: {{ $jadwalPaginate->total() }} jadwal
                                </span>
                            @endif
                            <a href="#" class="btn btn-success">
                                <i class="fa-solid fa-plus me-1"></i> Tambah Jadwal
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if(isset($jadwalPaginate) && $jadwalPaginate->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-success bg-opacity-10 text-success fw-semibold">
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Section</th>
                                        <th>Tanggal</th>
                                        <th>Rentang Waktu</th>
                                        <th class="text-center">Durasi</th>
                                        <th class="text-center">Harga Total</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jadwalPaginate as $i => $jadwal)
                                        @php
                                            try {
                                                $mulai = \Carbon\Carbon::parse($jadwal->jam_mulai);
                                                $selesai = \Carbon\Carbon::parse($jadwal->jam_selesai);
                                                $durasiMenit = $jadwal->durasi_sewa ?? $mulai->diffInMinutes($selesai);
                                                $durasiJam = $durasiMenit / 60;
                                                $hargaTotal = $jadwal->harga_sewa * $durasiJam;
                                                
                                                // Cari section untuk jadwal ini
                                                $sectionName = 'N/A';
                                                if ($jadwal->section_id && $lapangan->sections) {
                                                    $section = $lapangan->sections->firstWhere('id', $jadwal->section_id);
                                                    $sectionName = $section ? $section->nama_section : 'N/A';
                                                }
                                            } catch (\Exception $e) {
                                                $durasiJam = 0;
                                                $hargaTotal = 0;
                                                $sectionName = 'N/A';
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center fw-semibold">{{ $jadwalPaginate->firstItem() + $i }}</td>
                                            <td>
                                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                                    {{ $sectionName }}
                                                </span>
                                            </td>
                                            <td class="text-nowrap">
                                                {{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d M Y') }}
                                            </td>
                                            <td class="text-nowrap">
                                                <div class="d-flex flex-column small fw-semibold">
                                                    <span>{{ $mulai->format('H:i') }} WIB</span>
                                                    <span class="text-muted">s/d {{ $selesai->format('H:i') }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success px-3 py-2">
                                                    {{ rtrim(rtrim(number_format($durasiJam, 2, ',', '.'), '0'), ',') }} jam
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="fw-bold text-success">Rp {{ number_format($hargaTotal, 0, ',', '.') }}</div>
                                                <small class="text-muted d-block">Rp {{ number_format($jadwal->harga_sewa, 0, ',', '.') }} / jam</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge px-3 py-2 {{ $jadwal->tersedia ? 'bg-gradient bg-success' : 'bg-secondary' }}">
                                                    {{ $jadwal->tersedia ? 'Tersedia' : 'Tidak Tersedia' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if($jadwalPaginate->hasPages())
                            <div class="d-flex justify-content-between align-items-center p-4 border-top">
                                <div class="text-muted small">
                                    Menampilkan {{ $jadwalPaginate->firstItem() }} hingga {{ $jadwalPaginate->lastItem() }} 
                                    dari {{ $jadwalPaginate->total() }} entri
                                </div>
                                <div>
                                    {{ $jadwalPaginate->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fa-solid fa-calendar-xmark fa-3x mb-3 opacity-50"></i>
                            <h5 class="fw-semibold">Belum ada jadwal</h5>
                            <p class="mb-4">Tambahkan jadwal untuk mengelola waktu booking lapangan ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Styling tambahan --}}
<style>
    .bg-light-subtle { background: #f8f9fa; }
    .bg-success-subtle { background: rgba(25,135,84,.12); color:#198754!important; }
    .bg-primary-subtle { background: rgba(13,110,253,.12); color:#0d6efd!important; }
    .table> :not(caption)>*>* { vertical-align: middle; }
    .object-fit-cover { object-fit: cover; object-position: center; }
    .photo-card { max-width:540px; width:100%; margin:0 auto; }
    @media(min-width:992px){ .photo-card{max-width:100%;} }
    .carousel-lapangan { height: 400px; }
    .carousel-lapangan .carousel-item { height: 400px; }
    .carousel-lapangan .carousel-item img { height: 100%; object-fit: cover; }
</style>
@endsection