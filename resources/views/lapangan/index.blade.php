@extends('layouts.sidebar')

@section('title', 'Data Lapangan')

@section('content')
    <div class="container-fluid py-4">
        {{-- Header Section --}}
        <div class="row align-items-center mb-4">
            <div class="col-lg-8">
                <h2 class="fw-bold text-dark mb-2">
                    <i class="fa-solid fa-layer-group me-2 text-success"></i> Kelola Lapangan
                </h2>
                <p class="text-muted mb-0">Kelola portofolio tempat olahraga Anda dengan mudah</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <button class="btn btn-success btn-lg px-4 shadow" data-bs-toggle="modal"
                    data-bs-target="#tambahLapanganModal">
                    <i class="fa-solid fa-plus-circle me-2"></i> Tambah Lapangan Baru
                </button>
            </div>
        </div>

        {{-- Filter & Search Section --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('lapangan.index') }}">
                    <div class="row g-3 align-items-center">
                        {{-- Search Bar --}}
                        <div class="col-lg-4">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fa-solid fa-search text-muted"></i>
                                </span>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Cari nama lapangan atau lokasi...">
                            </div>
                        </div>

                        {{-- Filter Kategori --}}
                        <div class="col-lg-2">
                            <input type="text" name="kategori" value="{{ request('kategori') }}"
                                class="form-control form-control-lg" placeholder="Kategori...">
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="col-lg-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> Cari
                            </button>
                            <a href="{{ route('lapangan.index') }}" class="btn btn-outline-secondary btn-lg w-100">
                                <i class="fa-solid fa-rotate-left me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Grid Layout Cards --}}
        <div class="row g-4">
            @foreach ($lapangan as $item)
                @php
                    $fotoArray = $item->foto;
                    if (!is_array($fotoArray)) {
                        $fotoArray = [];
                    }
                    $totalSections = $item->sections->count();
                    $totalJadwal = 0;

                    // Hitung total jadwal dari semua sections
                    foreach ($item->sections as $section) {
                        $totalJadwal += $section->jadwal->count();
                    }
                @endphp

                <div class="col-lg-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden hover-lift">
                        {{-- Image Section with Carousel --}}
                        <div class="position-relative" style="height: 220px; overflow: hidden;">
                            @if (!empty($fotoArray) && count($fotoArray) > 0)
                                @if (count($fotoArray) > 1)
                                    <div id="carouselLapangan{{ $item->id }}" class="carousel slide h-100"
                                        data-bs-ride="carousel">
                                        <div class="carousel-inner h-100">
                                            @foreach ($fotoArray as $index => $foto)
                                                <div class="carousel-item h-100 {{ $index == 0 ? 'active' : '' }}">
                                                    <img src="{{ asset('storage/' . $foto) }}" class="d-block w-100 h-100"
                                                        alt="{{ $item->nama_lapangan }}"
                                                        style="object-fit: cover; object-position: center;">
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
                                        <div class="position-absolute bottom-0 end-0 mb-2 me-2" style="z-index: 10;">
                                            <span class="badge bg-dark bg-opacity-75 px-2 py-1">
                                                <i class="fa-solid fa-images me-1"></i> {{ count($fotoArray) }} Foto
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <img src="{{ asset('storage/' . $fotoArray[0]) }}" class="w-100 h-100"
                                        alt="{{ $item->nama_lapangan }}"
                                        style="object-fit: cover; object-position: center;">
                                @endif
                            @else
                                <img src="https://images.unsplash.com/photo-1459865264687-595d652de67e?w=600&h=400&fit=crop"
                                    class="w-100 h-100" alt="Default Image"
                                    style="object-fit: cover; object-position: center;">
                            @endif

                            {{-- Badge Total Sections --}}
                            <div class="position-absolute top-0 start-0 m-3" style="z-index: 10;">
                                <span class="badge bg-primary px-3 py-2 shadow">
                                    <i class="fa-solid fa-layer-group me-1"></i>
                                    {{ $totalSections }} Section
                                </span>
                            </div>

                            {{-- Badge Total Jadwal --}}
                            <div class="position-absolute top-0 end-0 m-3" style="z-index: 10;">
                                <span class="badge bg-success px-3 py-2 shadow">
                                    <i class="fa-solid fa-calendar me-1"></i>
                                    {{ $totalJadwal }} Jadwal
                                </span>
                            </div>

                            {{-- Badge Kategori --}}
                            <div class="position-absolute bottom-0 start-0 m-3" style="z-index: 10;">
                                <span class="badge bg-dark bg-opacity-75 px-3 py-2">
                                    <i class="fa-solid fa-tag me-1"></i>
                                    {{ ucfirst($item->kategori) }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body">
                            <h5 class="card-title fw-bold text-dark mb-2">{{ $item->nama_lapangan }}</h5>
                            <p class="text-muted small mb-3">
                                <i class="fa-solid fa-location-dot text-success me-1"></i>
                                {{ Str::limit($item->lokasi, 50) }}
                            </p>
                            <p class="card-text text-muted small mb-3">
                                {{ Str::limit($item->deskripsi, 100) }}
                            </p>

                            {{-- Informasi Sections --}}
                            <div class="mb-3">
                                <small class="text-muted d-block mb-2">
                                    <i class="fa-solid fa-layer-group text-primary me-1"></i> Daftar Section:
                                </small>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($item->sections->take(3) as $section)
                                        <span class="badge bg-light text-dark border">
                                            {{ $section->nama_section }}
                                            <small class="text-muted">({{ $section->jadwal->count() }} jadwal)</small>
                                        </span>
                                    @endforeach
                                    @if($item->sections->count() > 3)
                                        <span class="badge bg-light text-muted border">
                                            +{{ $item->sections->count() - 3 }} lainnya
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Informasi Jadwal --}}
                            <div class="mb-3 p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="fa-solid fa-calendar text-primary me-1"></i> Total Jadwal
                                    </small>
                                    <span class="fw-bold text-primary">
                                        {{ $totalJadwal }} Slot
                                    </span>
                                </div>
                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-success flex-fill" data-bs-toggle="modal"
                                    data-bs-target="#editLapanganModal{{ $item->id }}">
                                    <i class="fa-solid fa-pen me-1"></i> Edit
                                </button>
                                <a href="{{ route('lapangan.show', $item->id) }}"
                                    class="btn btn-outline-primary flex-fill">
                                    <i class="fa-solid fa-eye me-1"></i> Detail
                                </a>

                                {{-- Tombol Jadwal --}}
                                <button class="btn btn-outline-info flex-fill" data-bs-toggle="modal"
                                    data-bs-target="#kelolaJadwalModal{{ $item->id }}">
                                    <i class="fa-solid fa-calendar me-1"></i> Jadwal
                                </button>

                                <button type="button" class="btn btn-outline-danger delete-lapangan-btn"
                                    data-id="{{ $item->id }}" data-nama="{{ $item->nama_lapangan }}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Edit untuk Setiap Lapangan --}}
                <div class="modal fade" id="editLapanganModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header border-0 bg-gradient"
                                style="background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);">
                                <h5 class="modal-title text-white fw-bold">
                                    <i class="fa-solid fa-pen me-2"></i> Edit Lapangan
                                </h5>
                                <button type="button" class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('lapangan.update', $item->id) }}" method="POST"
                                enctype="multipart/form-data" class="form-submit-lapangan">
                                @csrf
                                @method('PUT')
                                <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold text-dark">
                                                <i class="fa-solid fa-tag me-1 text-success"></i> Nama Lapangan
                                            </label>
                                            <input type="text" name="nama_lapangan"
                                                class="form-control form-control-lg" value="{{ $item->nama_lapangan }}"
                                                required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold text-dark">
                                                <i class="fa-solid fa-layer-group me-1 text-success"></i> Kategori
                                            </label>
                                            <select name="id_kategori" class="form-select form-select-lg" required>
                                                <option value="" disabled>Pilih Kategori</option>
                                                @foreach ($kategori as $kat)
                                                    <option value="{{ $kat->id }}"
                                                        {{ $item->id_kategori == $kat->id ? 'selected' : '' }}>
                                                        {{ $kat->nama_kategori }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-dark">
                                                <i class="fa-solid fa-location-dot me-1 text-success"></i> Alamat Lengkap
                                            </label>
                                            <input type="text" name="lokasi" class="form-control form-control-lg"
                                                value="{{ $item->lokasi }}" required>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-dark">
                                                <i class="fa-solid fa-align-left me-1 text-success"></i> Deskripsi
                                            </label>
                                            <textarea name="deskripsi" class="form-control" rows="4">{{ $item->deskripsi }}</textarea>
                                        </div>

                                        {{-- Section Management --}}
                                        <div class="col-12">
                                            <div class="card border-0 bg-light">
                                                <div class="card-header bg-transparent border-bottom">
                                                    <h6 class="mb-0 fw-bold text-dark">
                                                        <i class="fa-solid fa-layer-group me-2 text-primary"></i> Kelola Section
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div id="section-container-{{ $item->id }}">
                                                        @foreach($item->sections as $index => $section)
                                                            <div class="row g-3 mb-3 section-item">
                                                                <div class="col-md-5">
                                                                    <label class="form-label">Nama Section</label>
                                                                    <input type="text" name="sections[{{ $section->id }}][nama_section]"
                                                                        class="form-control" value="{{ $section->nama_section }}" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Deskripsi</label>
                                                                    <input type="text" name="sections[{{ $section->id }}][deskripsi]"
                                                                        class="form-control" value="{{ $section->deskripsi }}">
                                                                </div>
                                                                <div class="col-md-1">
                                                                    <label class="form-label">&nbsp;</label>
                                                                    @if($index > 0)
                                                                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-section">
                                                                            <i class="fa-solid fa-trash"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <button type="button" class="btn btn-outline-primary btn-sm mt-2"
                                                        onclick="tambahSection({{ $item->id }})">
                                                        <i class="fa-solid fa-plus me-1"></i> Tambah Section
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-dark">
                                                <i class="fa-solid fa-image me-1 text-success"></i> Upload Foto Lapangan
                                            </label>
                                            <input type="file" name="foto[]"
                                                class="form-control form-control-lg foto-input" accept="image/*" multiple>
                                            <div class="preview-container mt-3 d-flex flex-wrap gap-2"></div>

                                            @if (!empty($item->foto))
                                                <div class="mt-3">
                                                    <small class="text-muted d-block mb-2">Foto saat ini:</small>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach ($item->foto as $photo)
                                                            <div class="position-relative"
                                                                style="width: 100px; height: 80px;">
                                                                <img src="{{ asset('storage/' . $photo) }}"
                                                                    class="w-100 h-100 rounded border"
                                                                    style="object-fit: cover;" alt="Foto lapangan">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 bg-light p-4">
                                    <button type="button" class="btn btn-lg btn-outline-secondary px-4"
                                        data-bs-dismiss="modal">
                                        <i class="fa-solid fa-xmark me-2"></i> Batal
                                    </button>
                                    <button type="submit" class="btn btn-lg btn-primary px-5 shadow">
                                        <i class="fa-solid fa-check-circle me-2"></i> Update Lapangan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Modal Kelola Jadwal untuk Setiap Lapangan --}}
                <div class="modal fade" id="kelolaJadwalModal{{ $item->id }}" tabindex="-1" aria-hidden="true"
                    data-kelola-jadwal="true" data-lapangan-id="{{ $item->id }}">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header border-0 bg-gradient"
                                style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);">
                                <h5 class="modal-title text-white fw-bold">
                                    <i class="fa-solid fa-calendar me-2"></i> Kelola Jadwal - {{ $item->nama_lapangan }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                {{-- Pilih Section --}}
                                <div class="card border-0 bg-light mb-4">
                                    <div class="card-header bg-transparent border-0">
                                        <h6 class="mb-0 fw-bold text-dark">
                                            <i class="fa-solid fa-layer-group me-2 text-primary"></i> Pilih Section
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <select class="form-select" id="section-selector-{{ $item->id }}"
                                                    data-default-harga="{{ $item->harga_sewa ?? 0 }}"
                                                    onchange="tampilkanJadwalSection({{ $item->id }})">
                                                    <option value="">-- Pilih Section --</option>
                                                    @foreach($item->sections as $section)
                                                        <option value="{{ $section->id }}" data-harga="{{ $section->harga_per_jam ?? '' }}" data-label="{{ $section->nama_section }}">
                                                            {{ $section->nama_section }}
                                                            @if($section->deskripsi)
                                                                 - {{ $section->deskripsi }}
                                                            @endif
                                                            @if($section->harga_per_jam)
                                                                (Rp {{ number_format($section->harga_per_jam, 0, ',', '.') }}/jam)
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <div id="section-info-{{ $item->id }}" class="text-muted">
                                                    Pilih section untuk melihat dan mengelola jadwal
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Form Tambah Jadwal --}}
                                <div class="card border-0 bg-light mb-4" id="form-jadwal-container-{{ $item->id }}" style="display: none;">
                                    <div class="card-header bg-transparent border-0">
                                        <h6 class="mb-0 fw-bold text-dark">
                                            <i class="fa-solid fa-plus-circle me-2 text-success"></i>
                                            Tambah Jadwal Baru - <span id="section-name-{{ $item->id }}"></span>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $hariOptions = [
                                                'senin' => 'Senin',
                                                'selasa' => 'Selasa',
                                                'rabu' => 'Rabu',
                                                'kamis' => 'Kamis',
                                                'jumat' => 'Jumat',
                                                'sabtu' => 'Sabtu',
                                                'minggu' => 'Minggu',
                                            ];
                                        @endphp
                                        <form action="{{ route('lapangan.jadwal.store', $item->id) }}" method="POST"
                                            class="form-submit-jadwal jadwal-create-form" id="formJadwal{{ $item->id }}" data-jadwal-form="create">
                                            @csrf
                                            <input type="hidden" name="section_id" id="section-id-{{ $item->id }}">

                                            <div class="mb-4">
                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                                    <div>
                                                        <small class="text-uppercase text-muted fw-bold d-block">Langkah 1</small>
                                                        <h6 class="fw-bold text-dark mb-0">Pilih Mode Penjadwalan</h6>
                                                    </div>
                                                    <span class="badge bg-light text-muted border">Rekomendasi: Mode Sederhana</span>
                                                </div>
                                                <div class="row g-3" data-jadwal-type-cards>
                                                    <div class="col-md-6">
                                                        <input type="radio" class="btn-check" name="tipe_jadwal"
                                                            id="tipe-simple-{{ $item->id }}" value="simple" checked
                                                            data-jadwal-type-input>
                                                        <label class="schedule-type-card h-100" data-jadwal-type-card="simple"
                                                            for="tipe-simple-{{ $item->id }}">
                                                            <div class="d-flex align-items-center gap-3 mb-3">
                                                                <img src="{{ asset('images/logo-sewalap.svg') }}" alt="Logo Mode Sederhana" class="mode-card-logo">
                                                                <div>
                                                                    <div class="fw-bold text-dark mb-0">Mode Sederhana (Auto Slot)</div>
                                                                    <small class="text-muted">Isi jam buka & durasi, sistem membagi otomatis.</small>
                                                                </div>
                                                            </div>
                                                            <ul class="mb-0 ps-3 text-muted small">
                                                                <li>Tentukan rentang tanggal dan hari aktif</li>
                                                                <li>Pilih jam buka-tutup, durasi slot, dan (opsional) jeda</li>
                                                                <li>Cocok untuk set jadwal mingguan hanya sekali klik</li>
                                                            </ul>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="radio" class="btn-check" name="tipe_jadwal"
                                                            id="tipe-custom-{{ $item->id }}" value="custom"
                                                            data-jadwal-type-input>
                                                        <label class="schedule-type-card h-100" data-jadwal-type-card="custom"
                                                            for="tipe-custom-{{ $item->id }}">
                                                            <div class="d-flex align-items-center gap-3 mb-3">
                                                                <img src="{{ asset('images/logo-sewalap.svg') }}" alt="Logo Mode Custom" class="mode-card-logo">
                                                                <div>
                                                                    <div class="fw-bold text-dark mb-0">Mode Custom (Manual)</div>
                                                                    <small class="text-muted">Untuk event atau slot khusus satu kali.</small>
                                                                </div>
                                                            </div>
                                                            <ul class="mb-0 ps-3 text-muted small">
                                                                <li>Tentukan tanggal, jam mulai, jam selesai secara spesifik</li>
                                                                <li>Cocok untuk turnamen, friendly match, atau blok tanggal tertentu</li>
                                                            </ul>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border-0 shadow-sm mb-3" data-jadwal-section="simple">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                                        <div>
                                                            <small class="text-uppercase text-muted fw-bold d-block">Langkah 2</small>
                                                            <h6 class="fw-bold text-dark mb-0">Setel Periode & Slot Otomatis</h6>
                                                        </div>
                                                        <span class="badge bg-primary bg-opacity-10 text-primary">Sangat cepat</span>
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-lg-3 col-md-6">
                                                            <label class="form-label fw-semibold text-dark">Mulai Berlaku</label>
                                                            <input type="date" name="tanggal_mulai" class="form-control"
                                                                min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required
                                                                data-required-mode="simple" data-role="tanggal-mulai">
                                                        </div>
                                                        <div class="col-lg-3 col-md-6">
                                                            <label class="form-label fw-semibold text-dark">Sampai</label>
                                                            <input type="date" name="tanggal_selesai" class="form-control"
                                                                min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required
                                                                data-required-mode="simple" data-role="tanggal-selesai">
                                                            <div class="form-text text-muted">
                                                                Maksimal 90 hari dari tanggal mulai.
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <label class="form-label fw-semibold text-dark">Hari Aktif</label>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                @foreach ($hariOptions as $key => $label)
                                                                    @php
                                                                        $hariFieldId = 'hari-' . $item->id . '-' . $key;
                                                                    @endphp
                                                                    <input type="checkbox" class="btn-check"
                                                                        name="hari_repetisi[]" value="{{ $key }}"
                                                                        id="{{ $hariFieldId }}" data-hari-checkbox>
                                                                    <label class="btn btn-outline-secondary btn-sm"
                                                                        for="{{ $hariFieldId }}">{{ $label }}</label>
                                                                @endforeach
                                                            </div>
                                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                                                <button type="button" class="btn btn-sm btn-light border"
                                                                    data-hari-action="all">Semua hari</button>
                                                                <button type="button" class="btn btn-sm btn-light border"
                                                                    data-hari-action="weekday">Senin - Jumat</button>
                                                                <button type="button" class="btn btn-sm btn-light border"
                                                                    data-hari-action="weekend">Sabtu - Minggu</button>
                                                                <button type="button" class="btn btn-sm btn-link text-danger px-2"
                                                                    data-hari-action="clear">Hapus pilihan</button>
                                                            </div>
                                                            <div class="form-text text-muted mt-2">
                                                                Terpilih <span class="fw-semibold" data-hari-count>0</span> hari aktif.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr class="text-muted my-4">
                                                    <div class="row g-3 align-items-end">
                                                        <div class="col-lg-3 col-md-6">
                                                            <label class="form-label fw-semibold text-dark">Jam Buka</label>
                                                            <input type="time" name="jam_mulai_harian" class="form-control" required
                                                                data-required-mode="simple">
                                                        </div>
                                                        <div class="col-lg-3 col-md-6">
                                                            <label class="form-label fw-semibold text-dark">Jam Tutup</label>
                                                            <input type="time" name="jam_selesai_harian" class="form-control" required
                                                                data-required-mode="simple">
                                                            <div class="form-text text-muted">Slot terakhir tidak melewati jam ini.</div>
                                                        </div>
                                                        <div class="col-lg-3 col-md-6">
                                                            <label class="form-label fw-semibold text-dark">Durasi Slot (jam)</label>
                                                            <input type="number" name="durasi_slot" class="form-control"
                                                                min="1" max="8" step="1" placeholder="1" required
                                                                id="durasi-slot-{{ $item->id }}" data-durasi-slot-input
                                                                data-required-mode="simple" value="1">
                                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                                @foreach ([1, 2, 3, 4] as $jamPreset)
                                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                        data-duration-preset="{{ $jamPreset }}"
                                                                        data-duration-target="#durasi-slot-{{ $item->id }}"
                                                                        data-duration-group="simple-{{ $item->id }}">{{ $jamPreset }} jam</button>
                                                                @endforeach
                                                            </div>
                                                            <div class="form-text text-muted">
                                                                Slot otomatis dibuat dalam kelipatan 1 jam.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @php
                                                $durasiInputDefault = old('durasi_sewa', 1);
                                                if (!is_null($durasiInputDefault) && $durasiInputDefault !== '') {
                                                    $numericDefault = is_numeric($durasiInputDefault)
                                                        ? (float) $durasiInputDefault
                                                        : 0;
                                                    $durasiInputDefault = $numericDefault > 24
                                                        ? $numericDefault / 60
                                                        : $numericDefault;
                                                }
                                                $durasiPreviewDisplay = rtrim(rtrim(number_format($durasiInputDefault, 2, ',', '.'), '0'), ',');
                                            @endphp

                                            <div class="card border-0 shadow-sm mb-3" data-jadwal-section="custom" style="display: none;">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                                        <div>
                                                            <small class="text-uppercase text-muted fw-bold d-block">Langkah 2</small>
                                                            <h6 class="fw-bold text-dark mb-0">Atur Slot Custom</h6>
                                                        </div>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">Manual</span>
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-lg-4 col-md-6">
                                                            <label class="form-label fw-semibold text-dark">Tanggal</label>
                                                            <input type="date" name="tanggal" class="form-control"
                                                                min="{{ date('Y-m-d') }}" data-required-mode="custom">
                                                        </div>
                                                        <div class="col-lg-4 col-md-6">
                                                            <label class="form-label fw-semibold text-dark">Jam Mulai</label>
                                                            <input type="time" name="jam_mulai" class="form-control"
                                                                data-jam-mulai-input data-required-mode="custom">
                                                        </div>
                                                        <div class="col-lg-4 col-md-6">
                                                            <label class="form-label fw-semibold text-dark">Jam Selesai</label>
                                                            <input type="time" name="jam_selesai" class="form-control"
                                                                data-jam-selesai-input data-required-mode="custom">
                                                            <div class="form-text text-muted">Durasi menyesuaikan otomatis.</div>
                                                        </div>
                                                        <div class="col-lg-4 col-md-6">
                                                            <label class="form-label fw-semibold text-dark">Durasi (jam)</label>
                                                            <input type="text" name="durasi_sewa" class="form-control"
                                                                inputmode="decimal" pattern="^\d+([,.]\d{1,2})?$"
                                                                min="0.25" max="12" step="0.25" placeholder="1"
                                                                value="{{ $durasiInputDefault }}" data-durasi-jam-input
                                                                id="durasi-custom-{{ $item->id }}" data-required-mode="custom">
                                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                    data-duration-preset="0.5"
                                                                    data-duration-target="#durasi-custom-{{ $item->id }}"
                                                                    data-duration-group="custom-{{ $item->id }}">30 mnt</button>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                    data-duration-preset="1"
                                                                    data-duration-target="#durasi-custom-{{ $item->id }}"
                                                                    data-duration-group="custom-{{ $item->id }}">1 jam</button>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                    data-duration-preset="1.5"
                                                                    data-duration-target="#durasi-custom-{{ $item->id }}"
                                                                    data-duration-group="custom-{{ $item->id }}">1.5 jam</button>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                    data-duration-preset="2"
                                                                    data-duration-target="#durasi-custom-{{ $item->id }}"
                                                                    data-duration-group="custom-{{ $item->id }}">2 jam</button>
                                                            </div>
                                                            <div class="form-text text-muted">
                                                                <span data-durasi-jam-preview>{{ $durasiPreviewDisplay }}</span> jam.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border-0 shadow-sm mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                                        <div>
                                                            <small class="text-uppercase text-muted fw-bold d-block">Langkah 3</small>
                                                            <h6 class="fw-bold text-dark mb-0">Harga & Status Slot</h6>
                                                        </div>
                                                        <span class="badge bg-success bg-opacity-10 text-success">Siap publish</span>
                                                    </div>
                                                    <div class="row g-3 align-items-end">
                                                        <div class="col-lg-6">
                                                            <label class="form-label fw-semibold text-dark">Harga per Jam</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text bg-success text-white">Rp</span>
                                                                <input type="number" name="harga_sewa" class="form-control"
                                                                    placeholder="150000" value="{{ $item->harga_sewa ?? '' }}" required data-harga-per-jam-input>
                                                                <span class="input-group-text bg-light text-muted">/ jam</span>
                                                            </div>
                                                            <div class="form-text text-muted mt-2">
                                                                Nilai per slot: <span class="fw-semibold text-success"
                                                                    data-harga-total-display>Rp 0</span>
                                                                (<span data-durasi-jam-display>{{ $durasiPreviewDisplay }}</span> jam).
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <label class="form-label fw-semibold text-dark">Status Slot</label>
                                                            <select name="tersedia" class="form-select" required>
                                                                <option value="1">Tersedia</option>
                                                                <option value="0">Tidak Tersedia</option>
                                                            </select>
                                                            <div class="form-text text-muted">Gunakan "Tidak tersedia" untuk blok jadwal tertentu.</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-4 d-flex flex-wrap gap-2">
                                                <button type="submit" class="btn btn-success flex-grow-1 flex-grow-md-0">
                                                    <i class="fa-solid fa-plus me-1"></i> Tambah Jadwal
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary" data-reset-jadwal="{{ $item->id }}">
                                                    <i class="fa-solid fa-arrow-rotate-left me-1"></i> Bersihkan Form
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                {{-- Daftar Jadwal per Section --}}
                                <div id="jadwal-container-{{ $item->id }}">
                                    <div class="text-center text-muted py-4">
                                        <i class="fa-solid fa-calendar-times fa-2x mb-2"></i>
                                        <br>Pilih section untuk melihat jadwal
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 bg-light p-4">
                                <button type="button" class="btn btn-lg btn-outline-secondary px-4"
                                    data-bs-dismiss="modal">
                                    <i class="fa-solid fa-xmark me-2"></i> Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Edit Jadwal Modals --}}
                @foreach($item->sections as $section)
                    @foreach($section->jadwal->sortBy('tanggal')->sortBy('jam_mulai') as $jadwal)
                        {{-- Modal Edit Jadwal --}}
                        <div class="modal fade" id="editJadwalModal{{ $jadwal->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content border-0 shadow-lg">
                                    <div class="modal-header border-0"
                                        style="background: linear-gradient(135deg, #0d6efd 0%, #20c997 100%);">
                                        <h5 class="modal-title text-white fw-bold">
                                            <i class="fa-solid fa-clock-rotate-left me-2"></i> Edit Jadwal - {{ $section->nama_section }}
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white"
                                            data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('lapangan.jadwal.update', [$item->id, $jadwal->id]) }}" method="POST"
                                        class="form-submit-jadwal">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="section_id" value="{{ $section->id }}">
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold text-dark">Tanggal</label>
                                                    <input type="date" name="tanggal" class="form-control"
                                                        value="{{ \Carbon\Carbon::parse($jadwal->tanggal)->format('Y-m-d') }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold text-dark">Jam Mulai</label>
                                                    <input type="time" name="jam_mulai" class="form-control"
                                                        value="{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}" required data-jam-mulai-input>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold text-dark">Jam Selesai</label>
                                                    <input type="time" name="jam_selesai" class="form-control"
                                                        value="{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}" required data-jam-selesai-input>
                                                    <div class="form-text text-muted">Disesuaikan otomatis dari durasi.</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold text-dark">Durasi (jam)</label>
                                                    <input type="text" name="durasi_sewa" inputmode="decimal" pattern="^\d+([,.]\d{1,2})?$" class="form-control" min="0.25"
                                                        max="24" step="0.25" placeholder="1"
                                                        value="{{ $jadwal->durasi_sewa / 60 }}" data-durasi-jam-input>
                                                    <div class="form-text text-muted">
                                                        <span data-durasi-jam-preview>{{ $jadwal->durasi_sewa / 60 }}</span> jam
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold text-dark">Harga per Jam</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-success text-white">Rp</span>
                                                        <input type="number" name="harga_sewa" class="form-control"
                                                            value="{{ $jadwal->harga_sewa }}" min="0" step="1000"
                                                            required data-harga-per-jam-input>
                                                        <span class="input-group-text bg-light text-muted">/ jam</span>
                                                    </div>
                                                    <div class="form-text text-muted">
                                                        Total: <span class="fw-semibold text-success" data-harga-total-display>
                                                            Rp {{ number_format($jadwal->harga_sewa * ($jadwal->durasi_sewa / 60), 0, ',', '.') }}
                                                        </span>
                                                        (<span data-durasi-jam-display>{{ $jadwal->durasi_sewa / 60 }}</span> jam)
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold text-dark">Status</label>
                                                    <select name="tersedia" class="form-select" required>
                                                        <option value="1" {{ $jadwal->tersedia ? 'selected' : '' }}>Tersedia</option>
                                                        <option value="0" {{ !$jadwal->tersedia ? 'selected' : '' }}>Tidak Tersedia</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 bg-light p-4">
                                            <button type="button" class="btn btn-lg btn-outline-secondary px-4"
                                                data-bs-dismiss="modal">
                                                <i class="fa-solid fa-xmark me-2"></i> Batal
                                            </button>
                                            <button type="submit" class="btn btn-lg btn-success px-5 shadow">
                                                <i class="fa-solid fa-check-circle me-2"></i> Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            @endforeach
        </div>

        {{-- Empty State --}}
        @if ($lapangan->count() == 0)
            <div class="text-center py-5">
                <i class="fa-solid fa-layer-group fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Belum ada lapangan</h4>
                <p class="text-muted">Tambahkan lapangan pertama Anda dengan mengklik tombol di atas</p>
            </div>
        @endif

        {{-- Pagination --}}
        @if ($lapangan->hasPages())
            <div class="d-flex justify-content-center mt-5">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        @if ($lapangan->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">‹</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $lapangan->previousPageUrl() }}" rel="prev">‹</a>
                            </li>
                        @endif

                        @foreach ($lapangan->getUrlRange(1, $lapangan->lastPage()) as $page => $url)
                            @if ($page == $lapangan->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        @if ($lapangan->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $lapangan->nextPageUrl() }}" rel="next">›</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">›</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
    </div>

    {{-- Modal Tambah Lapangan --}}
    <div class="modal fade" id="tambahLapanganModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-gradient"
                    style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="fa-solid fa-plus-circle me-2"></i> Tambah Lapangan Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('lapangan.store') }}" method="POST" enctype="multipart/form-data"
                    id="formTambah" class="form-submit-lapangan">
                    @csrf
                    <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">
                                    <i class="fa-solid fa-tag me-1 text-success"></i> Nama Lapangan
                                </label>
                                <input type="text" name="nama_lapangan" class="form-control form-control-lg"
                                    placeholder="Contoh: GOR Nasional, Futsal Arena Pro" value="{{ old('nama_lapangan') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">
                                    <i class="fa-solid fa-layer-group me-1 text-success"></i> Kategori
                                </label>
                                <select name="id_kategori" class="form-select form-select-lg" required>
                                    <option value="" disabled selected>Pilih Kategori</option>
                                    @foreach ($kategori as $kat)
                                        <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">
                                    <i class="fa-solid fa-location-dot me-1 text-success"></i> Alamat Lengkap
                                </label>
                                <input type="text" name="lokasi" class="form-control form-control-lg"
                                    placeholder="Jl. Sudirman No.123, Jakarta" value="{{ old('lokasi') }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">
                                    <i class="fa-solid fa-align-left me-1 text-success"></i> Deskripsi
                                </label>
                                <textarea name="deskripsi" class="form-control" rows="4" placeholder="Jelaskan fasilitas lapangan...">{{ old('deskripsi') }}</textarea>
                            </div>

                            {{-- Section Management --}}
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-0 pb-0">
                                        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2">
                                            <h6 class="mb-0 fw-bold text-dark">
                                                <i class="fa-solid fa-layer-group me-2 text-primary"></i> Tambah Section Lapangan
                                            </h6>
                                            <span class="badge rounded-pill bg-light text-primary border border-primary fw-semibold px-3 py-2">
                                                Kelola Area
                                            </span>
                                        </div>
                                        <p class="text-muted small mt-2 mb-0">
                                            Kelompokkan lapangan menjadi beberapa section (contoh: Lapangan A, Court 1, VIP) agar penyewa lebih mudah memilih.
                                        </p>
                                    </div>
                                    <div class="card-body bg-light">
                                        <div class="rounded-3 border border-secondary border-opacity-25 bg-white p-3 p-md-4">
                                            <div class="d-flex align-items-start gap-2 mb-3">
                                                <i class="fa-solid fa-circle-info text-primary mt-1"></i>
                                                <div class="small text-muted">
                                                    Isi minimal satu section sebagai area utama. Tambahkan section baru jika lapangan memiliki lebih dari satu area.
                                                </div>
                                            </div>
                                            <div id="section-container" class="section-wrapper">
                                                {{-- Section Pertama --}}
                                                <div class="section-item rounded-3 border border-secondary border-opacity-25 bg-white p-3 p-md-4 mb-3 shadow-sm">
                                                    <div class="row g-3 align-items-end">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold">
                                                                Nama Section <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="text" name="sections[0][nama_section]"
                                                                class="form-control"
                                                                placeholder="Contoh: Lapangan A, Court 1"
                                                                value="{{ old('sections.0.nama_section', 'Lapangan Utama') }}"
                                                                required>
                                                            <div class="form-text">Contoh: Lapangan A, Court 1</div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Deskripsi</label>
                                                            <input type="text" name="sections[0][deskripsi]"
                                                                class="form-control"
                                                                placeholder="Deskripsi singkat section..."
                                                                value="{{ old('sections.0.deskripsi') }}">
                                                            <div class="form-text">Opsional, gunakan untuk membedakan fasilitas.</div>
                                                        </div>
                                                        <div class="col-md-1 d-flex align-items-end justify-content-md-end">
                                                            <span class="text-muted small">Section utama</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mt-3">
                                                <button type="button" class="btn btn-outline-primary btn-sm px-3" id="tambah-section">
                                                    <i class="fa-solid fa-plus me-1"></i> Tambah Section Lain
                                                </button>
                                                <span class="small text-muted">Section tambahan cocok untuk area indoor/outdoor, court berbeda, atau sesi eksklusif.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body bg-white p-3 p-md-4">
                                        <div class="d-flex align-items-start gap-3 mb-3">
                                            <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 2.5rem; height: 2.5rem;">
                                                <i class="fa-solid fa-image"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold text-dark mb-1">Upload Foto Lapangan</h6>
                                                <p class="text-muted small mb-0">
                                                    Tampilkan kondisi lapangan terbaik. Unggah beberapa foto untuk memberi gambaran yang jelas kepada penyewa.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="rounded-3 border border-secondary border-opacity-25 p-4 text-center mb-3" style="border-style: dashed;">
                                            <i class="fa-solid fa-cloud-arrow-up fa-2x text-primary mb-3"></i>
                                            <p class="fw-semibold text-dark mb-1">Tarik & lepaskan atau pilih foto dari perangkat</p>
                                            <p class="text-muted small mb-3">Format yang didukung: JPG, PNG, JPEG &middot; Maksimal 2MB per foto</p>
                                            <input type="file" name="foto[]" class="form-control foto-input"
                                                accept="image/*" multiple required>
                                        </div>
                                        <div class="preview-container mt-3 d-flex flex-wrap gap-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-4">
                        <button type="button" class="btn btn-lg btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark me-2"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-lg btn-success px-5 shadow">
                            <i class="fa-solid fa-check-circle me-2"></i> Simpan Lapangan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Hidden Forms untuk Delete --}}
    <form id="deleteLapanganForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <form id="deleteJadwalForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Animate.css for smooth animations --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <script>
        // ========== SECTION MANAGEMENT ==========
        let sectionCount = 1;
        const rupiahFormatter = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 });
        const formatRupiahValue = (value) => {
            const nominal = Number(value);
            return 'Rp ' + rupiahFormatter.format(nominal > 0 ? nominal : 0);
        };
        const jadwalPaginationState = {};
        const JADWAL_PER_PAGE_OPTIONS = [5, 10, 25, 50];
        const JADWAL_DEFAULT_PER_PAGE = 10;
        const getActiveJadwalType = (form) => form?.querySelector('input[name=\"tipe_jadwal\"]:checked')?.value || 'custom';

        function updateDefaultHargaInputs(lapanganId, harga) {
            const form = document.getElementById(`formJadwal${lapanganId}`);
            if (!form) return;
            const hargaInput = form.querySelector('[data-harga-per-jam-input]');
            const defaultDisplay = form.querySelector('[data-default-harga-display]');
            const defaultButton = form.querySelector('[data-apply-default-harga]');

            const parsedHarga = Number(harga);
            const hasValidHarga = !Number.isNaN(parsedHarga) && parsedHarga > 0;

            if (hargaInput) {
                hargaInput.dataset.defaultHarga = hasValidHarga ? parsedHarga : '';
                if (hasValidHarga) {
                    hargaInput.value = parsedHarga;
                }
                hargaInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (defaultButton) {
                defaultButton.disabled = !hasValidHarga;
                defaultButton.dataset.defaultHarga = hasValidHarga ? parsedHarga : '';
            }

            if (typeof form.__updateJadwalSummary === 'function') {
                form.__updateJadwalSummary();
            }
        }

        function resetJadwalView(lapanganId) {
            const formContainer = document.getElementById(`form-jadwal-container-${lapanganId}`);
            const infoEl = document.getElementById(`section-info-${lapanganId}`);
            const nameEl = document.getElementById(`section-name-${lapanganId}`);
            const jadwalContainer = document.getElementById(`jadwal-container-${lapanganId}`);
            const form = document.getElementById(`formJadwal${lapanganId}`);

            if (formContainer) formContainer.style.display = 'none';
            if (infoEl) {
                infoEl.innerHTML = 'Pilih section untuk melihat jadwal';
            }
            if (nameEl) {
                nameEl.textContent = '';
            }
            if (jadwalContainer) {
                jadwalContainer.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fa-solid fa-calendar-times fa-2x mb-2"></i>
                        <br>Pilih section untuk melihat jadwal
                    </div>
                `;
            }

            if (form) {
                if (typeof form.__resetHariCount === 'function') {
                    form.__resetHariCount();
                }
            }
        }

        function setSectionInfo(lapanganId, sectionName, hargaDefault) {
            const infoEl = document.getElementById(`section-info-${lapanganId}`);
            const nameEl = document.getElementById(`section-name-${lapanganId}`);

            if (infoEl) {
                infoEl.innerHTML = `<strong>${sectionName}</strong> - ${hargaText}. Pilih tanggal dan waktu untuk menambah jadwal`;
            }
            if (nameEl) {
                nameEl.textContent = hargaDefault > 0
                    ? `${sectionName} (${formatRupiahValue(hargaDefault)} / jam)`
                    : sectionName;
            }
        }

        function initJadwalTypeForms() {
            const hariMeta = {
                senin: { label: 'Senin', iso: 1 },
                selasa: { label: 'Selasa', iso: 2 },
                rabu: { label: 'Rabu', iso: 3 },
                kamis: { label: 'Kamis', iso: 4 },
                jumat: { label: 'Jumat', iso: 5 },
                sabtu: { label: 'Sabtu', iso: 6 },
                minggu: { label: 'Minggu', iso: 7 },
            };

            const formatTanggalId = (value) => {
                if (!value) return '';
                const dateObj = new Date(`${value}T00:00:00`);
                if (Number.isNaN(dateObj.getTime())) {
                    return value;
                }
                return dateObj.toLocaleDateString('id-ID', {
                    weekday: 'short',
                    day: '2-digit',
                    month: 'short',
                });
            };

            const normalizeNumber = (value) => {
                if (typeof value === 'number') {
                    return Number.isFinite(value) ? value : 0;
                }
                if (typeof value !== 'string') {
                    value = String(value ?? '');
                }
                const parsed = parseFloat(value.replace(',', '.'));
                return Number.isFinite(parsed) ? parsed : 0;
            };

            const timeStringToMinutes = (value) => {
                if (!value || !value.includes(':')) {
                    return null;
                }
                const [jamStr, menitStr] = value.split(':');
                const jam = Number(jamStr);
                const menit = Number(menitStr);
                if (!Number.isInteger(jam) || !Number.isInteger(menit)) {
                    return null;
                }
                if (jam < 0 || jam > 23 || menit < 0 || menit > 59) {
                    return null;
                }
                return (jam * 60) + menit;
            };

            const forms = document.querySelectorAll('[data-jadwal-form="create"]');
            forms.forEach(form => {
                const typeInputs = form.querySelectorAll('[data-jadwal-type-input]');
                if (!typeInputs.length) {
                    return;
                }

                const sections = form.querySelectorAll('[data-jadwal-section]');
                const typeCards = form.querySelectorAll('[data-jadwal-type-card]');
                const totalDisplay = form.querySelector('[data-harga-total-display]');
                const hargaInput = form.querySelector('[data-harga-per-jam-input]');
                const defaultHargaBtn = form.querySelector('[data-apply-default-harga]');
                const durationButtons = form.querySelectorAll('[data-duration-preset]');

                const slotDurasiInput = form.querySelector('[data-durasi-slot-input]');
                const jamMulaiHarian = form.querySelector('input[name="jam_mulai_harian"]');
                const jamSelesaiHarian = form.querySelector('input[name="jam_selesai_harian"]');
                const rangeStartInput = form.querySelector('input[name="tanggal_mulai"]');
                const rangeEndInput = form.querySelector('input[name="tanggal_selesai"]');
                const hariCounter = form.querySelector('[data-hari-count]');
                const hariCheckboxes = form.querySelectorAll('[data-hari-checkbox]');

                const customDateInput = form.querySelector('input[name="tanggal"]');
                const customJamMulai = form.querySelector('input[name="jam_mulai"]');
                const customJamSelesai = form.querySelector('input[name="jam_selesai"]');
                const customDurasiInput = form.querySelector('[data-durasi-jam-input]');

                const getActiveDurationInput = () => getActiveJadwalType(form) === 'simple'
                    ? slotDurasiInput
                    : customDurasiInput;

                const getDurasiJamAktif = () => normalizeNumber(getActiveDurationInput()?.value);

                const getTotalPerSlot = () => {
                    const durasiJam = getDurasiJamAktif();
                    const hargaPerJam = normalizeNumber(hargaInput?.value);
                    if (durasiJam <= 0 || hargaPerJam <= 0) {
                        return 0;
                    }
                    return Math.round(hargaPerJam * durasiJam);
                };

                const updateSummary = () => {
                    if (!totalDisplay) {
                        return;
                    }
                    totalDisplay.textContent = formatRupiahValue(getTotalPerSlot());
                };

                const setSectionVisibility = () => {
                    const activeType = getActiveJadwalType(form);
                    sections.forEach(section => {
                        const isActive = section.dataset.jadwalSection === activeType;
                        section.style.display = isActive ? '' : 'none';
                        section.querySelectorAll('input, select, textarea').forEach(input => {
                            if (isActive) {
                                input.disabled = false;
                                if (input.dataset.requiredMode) {
                                    input.required = input.dataset.requiredMode === activeType;
                                }
                            } else {
                                input.disabled = true;
                                if (input.dataset.requiredMode) {
                                    input.required = false;
                                }
                            }
                        });
                    });

                    typeCards.forEach(card => {
                        card.classList.toggle('active', card.dataset.jadwalTypeCard === activeType);
                    });
                };

                typeInputs.forEach(input => {
                    input.addEventListener('change', () => {
                        setSectionVisibility();
                        updateSummary();
                    });
                });

                const updateHariCount = () => {
                    if (hariCounter) {
                        hariCounter.textContent = form.querySelectorAll('[data-hari-checkbox]:checked').length;
                    }
                };

                form.__resetHariCount = updateHariCount;

                const getSelectedHariIso = () => Array.from(form.querySelectorAll('[data-hari-checkbox]:checked'))
                    .map(cb => hariMeta[cb.value]?.iso)
                    .filter(Boolean);

                hariCheckboxes.forEach(cb => {
                    cb.addEventListener('change', () => {
                        updateHariCount();
                        updateSummary();
                    });
                });

                form.querySelectorAll('[data-hari-action]').forEach(button => {
                    button.addEventListener('click', event => {
                        event.preventDefault();
                        const action = button.dataset.hariAction;
                        if (action === 'all') {
                            hariCheckboxes.forEach(cb => cb.checked = true);
                        } else if (action === 'weekday') {
                            hariCheckboxes.forEach(cb => {
                                const iso = hariMeta[cb.value]?.iso || 0;
                                cb.checked = iso >= 1 && iso <= 5;
                            });
                        } else if (action === 'weekend') {
                            hariCheckboxes.forEach(cb => {
                                const iso = hariMeta[cb.value]?.iso || 0;
                                cb.checked = iso === 6 || iso === 7;
                            });
                        } else if (action === 'clear') {
                            hariCheckboxes.forEach(cb => cb.checked = false);
                        }
                        updateHariCount();
                        updateSummary();
                    });
                });

                const calculateSimpleSlots = () => {
                    const selectedIso = getSelectedHariIso();
                    if (!selectedIso.length) {
                        return 0;
                    }
                    if (!rangeStartInput?.value || !rangeEndInput?.value) {
                        return 0;
                    }
                    const startDate = new Date(`${rangeStartInput.value}T00:00:00`);
                    const endDate = new Date(`${rangeEndInput.value}T00:00:00`);
                    if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime()) || endDate < startDate) {
                        return 0;
                    }
                    const slotMinutes = Math.round(normalizeNumber(slotDurasiInput?.value) * 60);
                    if (!slotMinutes || slotMinutes <= 0) {
                        return 0;
                    }
                    const startMinutes = timeStringToMinutes(jamMulaiHarian?.value);
                    const endMinutes = timeStringToMinutes(jamSelesaiHarian?.value);
                    if (startMinutes === null || endMinutes === null || endMinutes <= startMinutes) {
                        return 0;
                    }
                    if (slotMinutes > endMinutes - startMinutes) {
                        return 0;
                    }
                    const slotsPerDay = Math.floor((endMinutes - startMinutes) / slotMinutes);
                    if (slotsPerDay <= 0) {
                        return 0;
                    }
                    let dayMatches = 0;
                    for (let cursor = new Date(startDate); cursor <= endDate; cursor.setDate(cursor.getDate() + 1)) {
                        const iso = cursor.getDay() === 0 ? 7 : cursor.getDay();
                        if (selectedIso.includes(iso)) {
                            dayMatches += 1;
                        }
                    }
                    return dayMatches * slotsPerDay;
                };

                const calculateCustomSlots = () => {
                    if (!customDateInput?.value) {
                        return 0;
                    }
                    if (!customJamMulai?.value || !customJamSelesai?.value) {
                        return 0;
                    }
                    const startMinutes = timeStringToMinutes(customJamMulai.value);
                    const endMinutes = timeStringToMinutes(customJamSelesai.value);
                    if (startMinutes === null || endMinutes === null || endMinutes <= startMinutes) {
                        return 0;
                    }
                    const durasiJam = normalizeNumber(customDurasiInput?.value);
                    if (!durasiJam || durasiJam <= 0) {
                        return 0;
                    }
                    return 1;
                };

                const handleDurationPreset = (event) => {
                    event.preventDefault();
                    const value = parseFloat(event.currentTarget.dataset.durationPreset);
                    const targetSelector = event.currentTarget.dataset.durationTarget;
                    if (!targetSelector || Number.isNaN(value) || value <= 0) {
                        return;
                    }
                    const targetInput = form.querySelector(targetSelector);
                    if (!targetInput) {
                        return;
                    }
                    targetInput.value = value;
                    targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                    const group = event.currentTarget.dataset.durationGroup;
                    if (group) {
                        durationButtons.forEach(btn => {
                            if (btn.dataset.durationGroup === group) {
                                btn.classList.toggle('active', btn === event.currentTarget);
                            }
                        });
                    }
                    updateSummary();
                };

                durationButtons.forEach(btn => btn.addEventListener('click', handleDurationPreset));

                const clearPresetState = (inputEl) => {
                    if (!inputEl?.id) {
                        return;
                    }
                    durationButtons.forEach(btn => {
                        if (btn.dataset.durationTarget === `#${inputEl.id}`) {
                            btn.classList.remove('active');
                        }
                    });
                };

                slotDurasiInput?.addEventListener('input', () => {
                    clearPresetState(slotDurasiInput);
                    updateSummary();
                });
                customDurasiInput?.addEventListener('input', () => {
                    clearPresetState(customDurasiInput);
                    updateSummary();
                });

                const summarySelectors = [
                    '[name="tanggal"]',
                    '[name="tanggal_mulai"]',
                    '[name="tanggal_selesai"]',
                    '[name="jam_mulai"]',
                    '[name="jam_selesai"]',
                    '[name="jam_mulai_harian"]',
                    '[name="jam_selesai_harian"]',
                    '[name="durasi_sewa"]',
                    '[name="durasi_slot"]',
                    '[name="harga_sewa"]',
                    '[name="tersedia"]',
                ];

                summarySelectors.forEach(selector => {
                    form.querySelectorAll(selector).forEach(element => {
                        element.addEventListener('input', updateSummary);
                        element.addEventListener('change', updateSummary);
                    });
                });

                if (rangeStartInput && rangeEndInput) {
                    const syncEndMin = () => {
                        const minValue = rangeStartInput.value || rangeStartInput.min;
                        if (minValue) {
                            rangeEndInput.min = minValue;
                            if (rangeEndInput.value && rangeEndInput.value < minValue) {
                                rangeEndInput.value = minValue;
                            }
                        }
                        updateSummary();
                    };
                    rangeStartInput.addEventListener('change', syncEndMin);
                    syncEndMin();
                }

                setSectionVisibility();
                updateHariCount();
                updateSummary();
            });
        }

        initJadwalTypeForms();


        function resetJadwalFormValues(lapanganId) {
            if (!lapanganId) return;
            const form = document.getElementById(`formJadwal${lapanganId}`);
            if (!form) return;

            const sectionInput = form.querySelector('input[name="section_id"]');
            const currentSectionId = sectionInput ? sectionInput.value : '';

            form.reset();

            if (sectionInput && currentSectionId) {
                sectionInput.value = currentSectionId;
            }

            const defaultMode = form.querySelector('input[name="tipe_jadwal"][value="simple"]');
            if (defaultMode) {
                defaultMode.checked = true;
            }

            form.querySelectorAll('[data-jadwal-type-input]').forEach(input => {
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });

            form.querySelector('[data-durasi-jam-input]')?.dispatchEvent(new Event('input', { bubbles: true }));
            form.querySelector('[data-durasi-slot-input]')?.dispatchEvent(new Event('input', { bubbles: true }));
            form.querySelector('[data-harga-per-jam-input]')?.dispatchEvent(new Event('input', { bubbles: true }));
            form.querySelector('input[name="jam_mulai_harian"]')?.dispatchEvent(new Event('input', { bubbles: true }));
            form.querySelector('input[name="jam_selesai_harian"]')?.dispatchEvent(new Event('input', { bubbles: true }));
            form.querySelector('input[name="jam_mulai"]')?.dispatchEvent(new Event('input', { bubbles: true }));
            form.querySelector('input[name="jam_selesai"]')?.dispatchEvent(new Event('input', { bubbles: true }));

            if (typeof form.__resetHariCount === 'function') {
                form.__resetHariCount();
            }

            if (typeof form.__updateJadwalSummary === 'function') {
                form.__updateJadwalSummary();
            }

            form.querySelectorAll('[data-duration-preset]').forEach(btn => btn.classList.remove('active'));
        }

        document.querySelectorAll('[data-reset-jadwal]').forEach(button => {
            button.addEventListener('click', event => {
                event.preventDefault();
                const lapanganId = button.dataset.resetJadwal;
                resetJadwalFormValues(lapanganId);
                if (typeof Toast !== 'undefined') {
                    Toast.fire({
                        icon: 'success',
                        title: 'Form jadwal dibersihkan',
                    });
                }
            });
        });

        // Tambah section baru di form tambah lapangan
        document.getElementById('tambah-section').addEventListener('click', function() {
            const container = document.getElementById('section-container');
            const newSection = document.createElement('div');
            newSection.classList.add(
                'section-item',
                'rounded-3',
                'border',
                'border-secondary',
                'border-opacity-25',
                'bg-white',
                'p-3',
                'p-md-4',
                'mb-3',
                'shadow-sm'
            );
            newSection.innerHTML = `
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Nama Section <span class="text-danger">*</span></label>
                        <input type="text" name="sections[${sectionCount}][nama_section]"
                            class="form-control"
                            placeholder="Contoh: Lapangan B, Court 2" required>
                        <div class="form-text">Contoh: Lapangan B, Court 2</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <input type="text" name="sections[${sectionCount}][deskripsi]"
                            class="form-control"
                            placeholder="Deskripsi singkat section...">
                        <div class="form-text">Opsional, gunakan jika ada informasi tambahan.</div>
                    </div>

                    <div class="col-md-1 d-flex align-items-end justify-content-md-end">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-section">
                            <i class="fa-solid fa-trash me-1"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newSection);
            const firstInput = newSection.querySelector('input');
            if (firstInput) {
                firstInput.focus();
            }
            sectionCount++;
        });

        // Hapus section
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-section') ||
                e.target.closest('.remove-section')) {
                const btn = e.target.classList.contains('remove-section') ? e.target : e.target.closest('.remove-section');
                btn.closest('.section-item').remove();
            }
        });

        // Tambah section di form edit
        function tambahSection(lapanganId) {
            const container = document.getElementById(`section-container-${lapanganId}`);
            const newSection = document.createElement('div');
            newSection.classList.add(
                'section-item',
                'rounded-3',
                'border',
                'border-secondary',
                'border-opacity-25',
                'bg-white',
                'p-3',
                'p-md-4',
                'mb-3',
                'shadow-sm'
            );
            newSection.innerHTML = `
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Nama Section <span class="text-danger">*</span></label>
                        <input type="text" name="sections[new_${sectionCount}][nama_section]"
                            class="form-control"
                            placeholder="Contoh: Lapangan B, Court 2" required>
                        <div class="form-text">Contoh: Lapangan B, Court 2</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <input type="text" name="sections[new_${sectionCount}][deskripsi]"
                            class="form-control"
                            placeholder="Deskripsi singkat section...">
                        <div class="form-text">Opsional, gunakan jika ada informasi tambahan.</div>
                    </div>

                    <div class="col-md-1 d-flex align-items-end justify-content-md-end">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-section">
                            <i class="fa-solid fa-trash me-1"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newSection);
            sectionCount++;
        }

        // ========== TAMPILKAN JADWAL PER SECTION ==========
        function tampilkanJadwalSection(lapanganId) {
            const selector = document.getElementById(`section-selector-${lapanganId}`);
            if (!selector) return;
            const sectionId = selector.value;
            const defaultHargaLapangan = Number(selector.dataset.defaultHarga || 0);

            if (!sectionId) {
                resetJadwalView(lapanganId);
                updateDefaultHargaInputs(lapanganId, defaultHargaLapangan);
                return;
            }

            const selectedOption = selector.options[selector.selectedIndex];
            const sectionName = selectedOption?.dataset.label || selectedOption?.text || 'Section';
            const sectionHarga = Number(selectedOption?.dataset.harga || 0);
            const hargaTerpilih = sectionHarga > 0 ? sectionHarga : defaultHargaLapangan;

            // Tampilkan form jadwal
            document.getElementById(`form-jadwal-container-${lapanganId}`).style.display = 'block';
            document.getElementById(`section-id-${lapanganId}`).value = sectionId;
            setSectionInfo(lapanganId, sectionName, hargaTerpilih);
            updateDefaultHargaInputs(lapanganId, hargaTerpilih);

            // Load jadwal via AJAX
            fetch(`/lapangan/${lapanganId}/section/${sectionId}/jadwal`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
                .then(async response => {
                    let payload = null;

                    // Coba parse JSON jika tersedia
                    try {
                        payload = await response.clone().json();
                    } catch (_) {
                        payload = null;
                    }

                    if (!response.ok) {
                        const message = payload?.message ?? 'Gagal memuat data jadwal.';
                        throw new Error(message);
                    }

                    if (!payload) {
                        throw new Error('Respons server tidak valid.');
                    }

                    return payload;
                })
                .then(data => {
                    const defaultHargaSection = Number(data.section.harga_per_jam || 0);
                    setJadwalPaginationState(lapanganId, data.jadwal || [], defaultHargaSection);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById(`jadwal-container-${lapanganId}`).innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fa-solid fa-exclamation-triangle me-2"></i>
                            ${error.message || 'Gagal memuat data jadwal'}
                        </div>
                    `;
                });
        }

        document.querySelectorAll('[data-kelola-jadwal=\"true\"]').forEach(modalEl => {
            modalEl.addEventListener('shown.bs.modal', function () {
                const lapanganId = this.dataset.lapanganId;
                const selector = document.getElementById(`section-selector-${lapanganId}`);
                if (!selector) {
                    resetJadwalView(lapanganId);
                    return;
                }

                if (!selector.value) {
                    const firstOption = Array.from(selector.options).find(opt => opt.value);
                    if (firstOption) {
                        selector.value = firstOption.value;
                    } else {
                        resetJadwalView(lapanganId);
                        return;
                    }
                }

                tampilkanJadwalSection(lapanganId);
            });
        });

        // ========== SWEETALERT CONFIGURATION ==========
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            showClass: {
                popup: 'animate__animated animate__fadeInRight animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutRight animate__faster'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Custom Success Alert
        const SuccessAlert = Swal.mixin({
            icon: 'success',
            confirmButtonColor: '#28a745',
            confirmButtonText: '<i class="fa-solid fa-check me-2"></i>OK',
            showClass: {
                popup: 'animate__animated animate__zoomIn animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__zoomOut animate__faster'
            }
        });

        // ========== SHOW SUCCESS/ERROR MESSAGES ==========
        @if (session('success'))
            SuccessAlert.fire({
                title: 'Berhasil!',
                html: '<p class="mb-0" style="color: #545454;">{{ session('success') }}</p>',
                timer: 2500,
                timerProgressBar: true
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
                confirmButtonColor: '#dc3545',
                confirmButtonText: '<i class="fa-solid fa-times me-2"></i>Tutup',
                showClass: {
                    popup: 'animate__animated animate__shakeX'
                }
            });
        @endif

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                html: '<ul style="text-align: left; padding-left: 20px;">' +
                    @foreach ($errors->all() as $error)
                        '<li>{{ $error }}</li>' +
                    @endforeach
                '</ul>',
                confirmButtonColor: '#dc3545',
                confirmButtonText: '<i class="fa-solid fa-times me-2"></i>Tutup',
                showClass: {
                    popup: 'animate__animated animate__shakeX'
                }
            });
        @endif

        // ========== DELETE LAPANGAN WITH SWEETALERT ==========
        document.querySelectorAll('.delete-lapangan-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.dataset.id;
                const nama = this.dataset.nama;

                if (!id) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'ID lapangan tidak ditemukan. Muat ulang halaman dan coba lagi.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Hapus Lapangan?',
                    html: `Apakah Anda yakin ingin menghapus<br><strong style="color: #dc3545;">${nama}</strong>?<br><br><small class="text-muted">Data ini tidak dapat dikembalikan!</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fa-solid fa-trash me-2"></i>Ya, Hapus!',
                    cancelButtonText: '<i class="fa-solid fa-times me-2"></i>Batal',
                    reverseButtons: true,
                    showClass: {
                        popup: 'animate__animated animate__zoomIn animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__zoomOut animate__faster'
                    },
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return new Promise((resolve) => {
                            setTimeout(() => {
                                const form = document.getElementById('deleteLapanganForm');
                                form.action = "{{ url('lapangan') }}/" + id;
                                form.submit();
                                resolve();
                            }, 500);
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus...',
                            html: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    }
                });
            });
        });

        document.addEventListener('input', event => {
            const searchInput = event.target.closest('[data-jadwal-search]');
            if (searchInput) {
                filterJadwalData(searchInput.dataset.jadwalSearch, searchInput.value);
            }
        });

        document.addEventListener('change', event => {
            const perPageSelect = event.target.closest('[data-jadwal-per-page]');
            if (perPageSelect) {
                updateJadwalPerPage(perPageSelect.dataset.jadwalPerPage, perPageSelect.value);
            }
        });

        document.addEventListener('click', event => {
            const pageButton = event.target.closest('[data-jadwal-page]');
            if (pageButton) {
                event.preventDefault();
                const lapanganId = pageButton.dataset.lapanganId;
                const state = jadwalPaginationState[lapanganId];
                if (!state) {
                    return;
                }

                let targetPage = state.page;
                if (pageButton.dataset.jadwalPage === 'prev') {
                    targetPage = state.page - 1;
                } else if (pageButton.dataset.jadwalPage === 'next') {
                    targetPage = state.page + 1;
                } else {
                    targetPage = Number(pageButton.dataset.jadwalPage);
                }
                renderJadwalPage(lapanganId, targetPage);
            }
        });

        // ========== DELETE JADWAL WITH SWEETALERT ==========
        function attachDeleteJadwalEvents() {
            document.querySelectorAll('.delete-jadwal-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const lapanganId = this.dataset.lapanganId;
                    const jadwalId = this.dataset.jadwalId;
                    const tanggal = this.dataset.tanggal;
                    const jam = this.dataset.jam;

                    Swal.fire({
                        title: 'Hapus Jadwal?',
                        html: `Apakah Anda yakin ingin menghapus jadwal:<br><strong style="color: #dc3545;">${tanggal}</strong><br><strong style="color: #dc3545;">${jam}</strong>?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fa-solid fa-trash me-2"></i>Ya, Hapus!',
                        cancelButtonText: '<i class="fa-solid fa-times me-2"></i>Batal',
                        reverseButtons: true,
                        showClass: {
                            popup: 'animate__animated animate__zoomIn animate__faster'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__zoomOut animate__faster'
                        },
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            return new Promise((resolve) => {
                                setTimeout(() => {
                                    const form = document.getElementById('deleteJadwalForm');
                                    form.action = `{{ url('lapangan') }}/${lapanganId}/jadwal/${jadwalId}`;
                                    form.submit();
                                    resolve();
                                }, 500);
                            });
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Menghapus...',
                                html: 'Mohon tunggu sebentar',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        }
                    });
                });
            });
        }

        function setJadwalPaginationState(lapanganId, data, defaultHargaSection) {
            jadwalPaginationState[lapanganId] = {
                allData: data,
                filteredData: data,
                perPage: JADWAL_DEFAULT_PER_PAGE,
                page: 1,
                search: '',
                defaultHargaSection,
            };
            renderJadwalPage(lapanganId);
        }

        function renderJadwalPage(lapanganId, targetPage = 1) {
            const state = jadwalPaginationState[lapanganId];
            if (!state) {
                return;
            }

            const container = document.getElementById(`jadwal-container-${lapanganId}`);
            if (!container) {
                return;
            }

            const totalItems = state.filteredData.length;
            const totalPages = Math.max(1, Math.ceil(Math.max(totalItems, 1) / state.perPage));
            state.page = Math.min(Math.max(targetPage, 1), totalPages);

            const startIndex = (state.page - 1) * state.perPage;
            const rows = state.filteredData.slice(startIndex, startIndex + state.perPage);

            const searchValue = state.search || '';

            const headingHtml = `
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-list me-2"></i> Daftar Jadwal (${totalItems})
                    </h6>
                </div>
            `;

            const controlsHtml = `
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div class="input-group input-group-sm" style="max-width: 280px;">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fa-solid fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" placeholder="Cari tanggal atau jam..."
                            value="${searchValue}" data-jadwal-search="${lapanganId}">
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted small mb-0">Tampil</label>
                        <select class="form-select form-select-sm" data-jadwal-per-page="${lapanganId}">
                            ${JADWAL_PER_PAGE_OPTIONS.map(option => `
                                <option value="${option}" ${option === state.perPage ? 'selected' : ''}>${option}</option>
                            `).join('')}
                        </select>
                        <span class="text-muted small">per halaman</span>
                    </div>
                </div>
            `;

            if (totalItems === 0) {
                container.innerHTML = `
                    ${headingHtml}
                    ${controlsHtml}
                    <div class="alert alert-light border text-center mb-0">
                        <i class="fa-solid fa-circle-info me-1 text-muted"></i>
                        Belum ada jadwal untuk section ini.
                    </div>
                `;
                return;
            }

            let tableHtml = `
                ${headingHtml}
                ${controlsHtml}
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-nowrap">Tanggal</th>
                                <th>Jam Mulai</th>
                                <th>Jam Selesai</th>
                                <th class="text-nowrap">Durasi (Jam)</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            rows.forEach(jadwal => {
                const durasiJam = jadwal.durasi_sewa / 60;
                const durasiFormatted = durasiJam % 1 === 0 ? durasiJam : durasiJam.toFixed(2);
                const totalHarga = jadwal.harga_sewa * durasiJam;
                const defaultHargaSection = state.defaultHargaSection;
                const isDefaultPrice = defaultHargaSection > 0 && Number(jadwal.harga_sewa) === defaultHargaSection;
                const hargaBadge = defaultHargaSection > 0
                    ? `<span class="badge ${isDefaultPrice ? 'bg-primary' : 'bg-warning text-dark'} ms-1">
                           ${isDefaultPrice ? 'Default' : 'Custom'}
                       </span>`
                    : '';

                tableHtml += `
                    <tr>
                        <td>${new Date(jadwal.tanggal).toLocaleDateString('id-ID')}</td>
                        <td>${jadwal.jam_mulai}</td>
                        <td>${jadwal.jam_selesai}</td>
                        <td>${durasiFormatted} jam</td>
                        <td>
                            <span class="fw-bold text-success d-block">
                                ${formatRupiahValue(totalHarga)}
                            </span>
                            <small class="text-muted d-block">
                                ${formatRupiahValue(jadwal.harga_sewa)} / jam ${hargaBadge}
                            </small>
                        </td>
                        <td>
                            <span class="badge ${jadwal.tersedia ? 'bg-success' : 'bg-danger'}">
                                ${jadwal.tersedia ? 'Tersedia' : 'Tidak Tersedia'}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                data-bs-toggle="modal"
                                data-bs-target="#editJadwalModal${jadwal.id}">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button type="button"
                                class="btn btn-sm btn-outline-danger delete-jadwal-btn"
                                data-lapangan-id="${lapanganId}"
                                data-jadwal-id="${jadwal.id}"
                                data-tanggal="${new Date(jadwal.tanggal).toLocaleDateString('id-ID')}"
                                data-jam="${jadwal.jam_mulai} - ${jadwal.jam_selesai}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            tableHtml += `
                        </tbody>
                    </table>
                </div>
            `;

            const showingStart = startIndex + 1;
            const showingEnd = startIndex + rows.length;

            const paginationHtml = `
                <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 gap-2">
                    <small class="text-muted">
                        Menampilkan ${showingStart} - ${showingEnd} dari ${totalItems} jadwal
                    </small>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary"
                            data-jadwal-page="prev"
                            data-lapangan-id="${lapanganId}"
                            ${state.page === 1 ? 'disabled' : ''}>
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <span class="small text-muted">Halaman ${state.page} / ${totalPages}</span>
                        <button class="btn btn-sm btn-outline-secondary"
                            data-jadwal-page="next"
                            data-lapangan-id="${lapanganId}"
                            ${state.page === totalPages ? 'disabled' : ''}>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            `;

            container.innerHTML = tableHtml + paginationHtml;
            attachDeleteJadwalEvents();
        }

        function filterJadwalData(lapanganId, keyword) {
            const state = jadwalPaginationState[lapanganId];
            if (!state) {
                return;
            }
            state.search = keyword.toLowerCase();
            if (!state.search) {
                state.filteredData = state.allData;
            } else {
                state.filteredData = state.allData.filter(item => {
                    const target = [
                        item.tanggal,
                        item.jam_mulai,
                        item.jam_selesai,
                        item.tersedia ? 'tersedia' : 'tidak tersedia'
                    ].join(' ').toLowerCase();
                    return target.includes(state.search);
                });
            }
            state.page = 1;
            renderJadwalPage(lapanganId);
        }

        function updateJadwalPerPage(lapanganId, perPage) {
            const state = jadwalPaginationState[lapanganId];
            const parsed = Number(perPage);
            if (!state || !Number.isFinite(parsed) || parsed <= 0) {
                return;
            }
            state.perPage = parsed;
            state.page = 1;
            renderJadwalPage(lapanganId);
        }

        // Panggil pertama kali
        attachDeleteJadwalEvents();

        // ========== FORM SUBMIT WITH LOADING ==========
        document.querySelectorAll('.form-submit-lapangan').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Menyimpan Data...',
                    html: '<div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 mb-0">Mohon tunggu sebentar</p>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    showClass: {
                        popup: 'animate__animated animate__fadeIn animate__faster'
                    }
                });

                setTimeout(() => {
                    this.submit();
                }, 800);
            });
        });

        document.querySelectorAll('.form-submit-jadwal').forEach(form => {
            form.addEventListener('submit', function(e) {
                const jamMulai = this.querySelector('input[name="jam_mulai"]');
                const jamSelesai = this.querySelector('input[name="jam_selesai"]');

                if (jamMulai && jamSelesai && jamMulai.value && jamSelesai.value) {
                    if (jamMulai.value >= jamSelesai.value) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal!',
                            text: 'Jam selesai harus lebih besar dari jam mulai!',
                            confirmButtonColor: '#dc3545',
                            showClass: {
                                popup: 'animate__animated animate__shakeX'
                            }
                        });
                        return false;
                    }
                }

                e.preventDefault();

                Swal.fire({
                    title: 'Menyimpan Jadwal...',
                    html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 mb-0">Mohon tunggu sebentar</p>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    showClass: {
                        popup: 'animate__animated animate__fadeIn animate__faster'
                    }
                });

                setTimeout(() => {
                    this.submit();
                }, 800);
            });
        });

        // ========== PREVIEW MULTIPLE FOTO ==========
        document.querySelectorAll('.foto-input').forEach((fotoInput) => {
            const previewContainer = fotoInput.closest('.col-12').querySelector('.preview-container');
            let selectedFiles = [];

            fotoInput.addEventListener('change', function(event) {
                const files = Array.from(event.target.files);
                selectedFiles = [...selectedFiles, ...files];
                renderPreview();
            });

            function renderPreview() {
                previewContainer.innerHTML = '';
                const dataTransfer = new DataTransfer();

                selectedFiles.forEach((file, index) => {
                    dataTransfer.items.add(file);

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const wrapper = document.createElement('div');
                        wrapper.classList.add('position-relative', 'd-inline-block', 'me-2', 'mb-2');

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.classList.add('img-thumbnail');
                        img.style.width = '100px';
                        img.style.height = '80px';
                        img.style.objectFit = 'cover';

                        const removeBtn = document.createElement('button');
                        removeBtn.textContent = '×';
                        removeBtn.type = 'button';
                        removeBtn.classList.add('btn', 'btn-sm', 'btn-danger', 'position-absolute',
                            'top-0', 'end-0');
                        removeBtn.style.transform = 'translate(25%, -25%)';
                        removeBtn.style.fontSize = '16px';
                        removeBtn.style.width = '24px';
                        removeBtn.style.height = '24px';
                        removeBtn.style.padding = '0';
                        removeBtn.style.lineHeight = '1';
                        removeBtn.onclick = function() {
                            selectedFiles.splice(index, 1);
                            renderPreview();
                        };

                        wrapper.appendChild(img);
                        wrapper.appendChild(removeBtn);
                        previewContainer.appendChild(wrapper);
                    };
                    reader.readAsDataURL(file);
                });

                fotoInput.files = dataTransfer.files;
            }
        });

        // ========== DYNAMIC PRICE CALCULATION ==========
        document.querySelectorAll('[data-jadwal-form="create"]').forEach(form => {
            const hargaPerJamInput = form.querySelector('[data-harga-per-jam-input]');
            const customDurasiInput = form.querySelector('[data-durasi-jam-input]');
            const slotDurasiInput = form.querySelector('[data-durasi-slot-input]');
            const jamMulaiInput = form.querySelector('[data-jam-mulai-input]');
            const jamSelesaiInput = form.querySelector('[data-jam-selesai-input]');
            const totalDisplay = form.querySelector('[data-harga-total-display]');
            const durasiJamPreview = form.querySelector('[data-durasi-jam-preview]');
            const durasiJamDisplay = form.querySelector('[data-durasi-jam-display]');

            if (!hargaPerJamInput || !totalDisplay) {
                return;
            }

            const formatRupiah = (value) => {
                const safeValue = Number.isFinite(value) ? value : 0;
                const nominal = Math.max(0, Math.round(safeValue));
                return 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(nominal);
            };

            const formatJam = (value) => {
                if (!Number.isFinite(value) || value <= 0) {
                    return '0';
                }
                const rounded = Math.round(value * 100) / 100;
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: rounded < 1 ? 1 : 0,
                    maximumFractionDigits: 2,
                }).format(rounded);
            };

            const normalizeNumber = (value) => {
                if (typeof value !== 'string') {
                    value = String(value ?? '');
                }
                const parsed = parseFloat(value.replace(',', '.'));
                return Number.isFinite(parsed) ? parsed : 0;
            };

            const getMinutesFromTime = (value) => {
                if (!value || !value.includes(':')) {
                    return null;
                }
                const [jamStr, menitStr] = value.split(':');
                const jam = parseInt(jamStr, 10);
                const menit = parseInt(menitStr, 10);
                if (!Number.isInteger(jam) || !Number.isInteger(menit)) {
                    return null;
                }
                if (jam < 0 || jam > 23 || menit < 0 || menit > 59) {
                    return null;
                }
                return (jam * 60) + menit;
            };

            const setTimeFromMinutes = (input, minutesTotal) => {
                if (!input) {
                    return;
                }
                const clampedMinutes = Math.max(0, Math.min(minutesTotal, (23 * 60) + 59));
                const jam = String(Math.floor(clampedMinutes / 60)).padStart(2, '0');
                const menit = String(clampedMinutes % 60).padStart(2, '0');
                input.value = `${jam}:${menit}`;
            };

            const updateDurationDisplays = (durasiJam) => {
                const formattedJam = formatJam(durasiJam);
                if (durasiJamPreview) {
                    durasiJamPreview.textContent = formattedJam;
                }
                if (durasiJamDisplay) {
                    durasiJamDisplay.textContent = formattedJam;
                }
            };

            const getActiveDurationInput = () => getActiveJadwalType(form) === 'simple' ? slotDurasiInput : customDurasiInput;
            const getActiveDurationValue = () => normalizeNumber(getActiveDurationInput()?.value ?? '0');

            const setCustomDurasiValue = (jam) => {
                if (!customDurasiInput) {
                    return;
                }
                const numericJam = Number.isFinite(jam) ? Math.max(0, jam) : 0;
                const decimals = Number.isInteger(numericJam) ? 0 : 2;
                customDurasiInput.value = numericJam.toFixed(decimals);
                updateDurationDisplays(numericJam);
            };

            let isSyncing = false;

            const syncDurationFromTimes = () => {
                if (getActiveJadwalType(form) !== 'custom' || isSyncing || !jamMulaiInput || !jamSelesaiInput) {
                    return;
                }
                const mulai = getMinutesFromTime(jamMulaiInput.value);
                const selesai = getMinutesFromTime(jamSelesaiInput.value);
                if (mulai === null || selesai === null || selesai <= mulai) {
                    if (customDurasiInput) {
                        customDurasiInput.value = '';
                    }
                    updateDurationDisplays(0);
                    return;
                }
                const selisihJam = (selesai - mulai) / 60;
                isSyncing = true;
                setCustomDurasiValue(selisihJam);
                isSyncing = false;
            };

            const syncEndTimeFromDuration = () => {
                if (getActiveJadwalType(form) !== 'custom' || isSyncing || !jamMulaiInput || !jamSelesaiInput || !customDurasiInput) {
                    return;
                }
                const mulai = getMinutesFromTime(jamMulaiInput.value);
                const durasiJam = normalizeNumber(customDurasiInput.value);
                if (mulai === null || durasiJam <= 0) {
                    return;
                }
                let selesai = mulai + Math.round(durasiJam * 60);
                selesai = Math.max(selesai, mulai + 1);
                isSyncing = true;
                setTimeFromMinutes(jamSelesaiInput, selesai);
                isSyncing = false;
                syncDurationFromTimes();
            };

            const updateTotalHarga = () => {
                const durasiJam = getActiveDurationValue();
                const safeDurasiJam = durasiJam > 0 ? durasiJam : 0;
                const hargaPerJam = parseFloat(hargaPerJamInput.value || '0');
                const total = hargaPerJam * safeDurasiJam;
                totalDisplay.textContent = formatRupiah(total);
                updateDurationDisplays(safeDurasiJam);
                if (typeof form.__updateJadwalSummary === 'function') {
                    form.__updateJadwalSummary();
                }
            };

            const handleJamChange = () => {
                syncDurationFromTimes();
                updateTotalHarga();
            };

            const handleDurationChange = (event) => {
                if (event?.currentTarget === customDurasiInput) {
                    syncEndTimeFromDuration();
                }
                updateTotalHarga();
            };

            hargaPerJamInput.addEventListener('input', updateTotalHarga);
            hargaPerJamInput.addEventListener('change', updateTotalHarga);

            [slotDurasiInput, customDurasiInput].forEach(input => {
                if (!input) return;
                ['input', 'change'].forEach(evt => input.addEventListener(evt, handleDurationChange));
            });

            [jamMulaiInput, jamSelesaiInput].filter(Boolean).forEach(input => {
                ['input', 'change'].forEach(evt => input.addEventListener(evt, handleJamChange));
            });

            syncDurationFromTimes();
            updateTotalHarga();
        });
    </script>

    <style>
        .schedule-type-card {
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        .schedule-type-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 .5rem 1rem rgba(13, 110, 253, .15);
        }

        .schedule-type-card.active {
            border-color: #0d6efd;
            box-shadow: 0 .65rem 1.2rem rgba(13, 110, 253, .2);
        }

        .schedule-type-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .mode-card-logo {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: contain;
            padding: 6px;
            background: #f8f9fa;
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.05);
        }

        [data-duration-preset] {
            transition: all 0.15s ease;
        }

        [data-duration-preset].active {
            background-color: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
        }

        .hover-lift {
            transition: all 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-8px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 10%;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .card:hover .carousel-control-prev,
        .card:hover .carousel-control-next {
            opacity: 1;
        }

        .modal-body {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }

        .modal-body::-webkit-scrollbar {
            width: 6px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* SweetAlert2 Custom Styling */
        .swal2-popup {
            font-family: inherit;
            border-radius: 20px;
        }

        .swal2-title {
            font-weight: 600;
            font-size: 1.5rem;
            color: #545454;
        }

        .swal2-html-container {
            font-size: 1rem;
        }
    </style>
@endsection
