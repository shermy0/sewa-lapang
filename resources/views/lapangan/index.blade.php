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
            <button class="btn btn-success btn-lg px-4 shadow" data-bs-toggle="modal" data-bs-target="#tambahLapanganModal">
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
                            class="form-control form-control-lg" 
                            placeholder="Kategori...">
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

    {{-- Alert Success --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-exclamation-triangle me-2"></i>
        <strong>Terjadi kesalahan:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Grid Layout Cards --}}
    <div class="row g-4">
        @foreach ($lapangan as $item)
            @php
                $fotoArray = $item->foto;
                if (!is_array($fotoArray)) {
                    $fotoArray = [];
                }
                $totalJadwal = $item->jadwal->count();
                // Hitung harga rata-rata dari jadwal
                $hargaRataRata = $item->jadwal->avg('harga_sewa');
            @endphp

            <div class="col-lg-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden hover-lift">
                    {{-- Image Section with Carousel --}}
                    <div class="position-relative" style="height: 220px; overflow: hidden;">
                        @if (!empty($fotoArray) && count($fotoArray) > 0)
                            @if (count($fotoArray) > 1)
                                <div id="carouselLapangan{{ $item->id }}" class="carousel slide h-100" data-bs-ride="carousel">
                                    <div class="carousel-inner h-100">
                                        @foreach ($fotoArray as $index => $foto)
                                            <div class="carousel-item h-100 {{ $index == 0 ? 'active' : '' }}">
                                                <img src="{{ asset('storage/' . $foto) }}" 
                                                     class="d-block w-100 h-100" 
                                                     alt="{{ $item->nama_lapangan }}" 
                                                     style="object-fit: cover; object-position: center;">
                                            </div>
                                        @endforeach
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselLapangan{{ $item->id }}" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselLapangan{{ $item->id }}" data-bs-slide="next">
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
                                <img src="{{ asset('storage/' . $fotoArray[0]) }}" 
                                     class="w-100 h-100" 
                                     alt="{{ $item->nama_lapangan }}" 
                                     style="object-fit: cover; object-position: center;">
                            @endif
                        @else
                            <img src="https://images.unsplash.com/photo-1459865264687-595d652de67e?w=600&h=400&fit=crop" 
                                 class="w-100 h-100" 
                                 alt="Default Image" 
                                 style="object-fit: cover; object-position: center;">
                        @endif
                        
                        {{-- Badge Total Jadwal --}}
                        <div class="position-absolute top-0 start-0 m-3" style="z-index: 10;">
                            <span class="badge bg-primary px-3 py-2 shadow">
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
                            @if($hargaRataRata)
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fa-solid fa-money-bill-wave text-success me-1"></i> Harga Rata-rata
                                </small>
                                <span class="fw-bold text-success">
                                    Rp {{ number_format($hargaRataRata, 0, ',', '.') }}
                                </span>
                            </div>
                            @endif
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-success flex-fill" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editLapanganModal{{ $item->id }}">
                                <i class="fa-solid fa-pen me-1"></i> Edit
                            </button>
                            <a href="{{ route('lapangan.show', $item->id) }}" class="btn btn-outline-primary flex-fill">
                                <i class="fa-solid fa-eye me-1"></i> Detail
                            </a>
                            
                            {{-- Tombol Jadwal --}}
                            <button class="btn btn-outline-info flex-fill" 
                                data-bs-toggle="modal" 
                                data-bs-target="#kelolaJadwalModal{{ $item->id }}">
                                <i class="fa-solid fa-calendar me-1"></i> Jadwal
                            </button>
                            
                            <form action="{{ route('lapangan.destroy', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Yakin hapus lapangan?')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Edit untuk Setiap Lapangan --}}
            <div class="modal fade" id="editLapanganModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header border-0 bg-gradient" style="background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);">
                            <h5 class="modal-title text-white fw-bold">
                                <i class="fa-solid fa-pen me-2"></i> Edit Lapangan
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('lapangan.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                                <div class="row g-4">
                                    {{-- Informasi Dasar --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-tag me-1 text-success"></i> Nama Lapangan
                                        </label>
                                        <input type="text" name="nama_lapangan" class="form-control form-control-lg" 
                                            value="{{ $item->nama_lapangan }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-layer-group me-1 text-success"></i> Jenis Olahraga / Kategori
                                        </label>
                                        <input type="text" name="kategori" class="form-control form-control-lg" 
                                            value="{{ $item->kategori }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-location-dot me-1 text-success"></i> Alamat Lengkap
                                        </label>
                                        <input type="text" name="lokasi" class="form-control form-control-lg" 
                                            value="{{ $item->lokasi }}" required>
                                    </div>

                                   
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-calendar me-1 text-primary"></i> Total Jadwal
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-primary text-white">
                                                <i class="fa-solid fa-calendar"></i>
                                            </span>
                                            <input type="text" class="form-control bg-light" value="{{ $totalJadwal }} jadwal" readonly>
                                        </div>
                                        <div class="form-text text-info">
                                            <i class="fa-solid fa-circle-info me-1"></i> 
                                            Total jadwal yang sudah dibuat
                                        </div>
                                    </div>
            
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-align-left me-1 text-success"></i> Deskripsi
                                        </label>
                                        <textarea name="deskripsi" class="form-control" rows="4">{{ $item->deskripsi }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-image me-1 text-success"></i> Upload Foto Lapangan
                                        </label>
                                        <input type="file" name="foto[]" class="form-control form-control-lg foto-input" accept="image/*" multiple>
                                        
                                        <div class="preview-container mt-3 d-flex flex-wrap gap-2"></div>

                                        {{-- Tampilkan foto yang sudah ada --}}
                                        @if (!empty($item->foto))
                                            <div class="mt-3">
                                                <small class="text-muted d-block mb-2">Foto saat ini:</small>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach ($item->foto as $photo)
                                                        <div class="position-relative" style="width: 100px; height: 80px;">
                                                            <img src="{{ asset('storage/' . $photo) }}"
                                                                class="w-100 h-100 rounded border"
                                                                style="object-fit: cover;"
                                                                alt="Foto lapangan">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 bg-light p-4">
                                <button type="button" class="btn btn-lg btn-outline-secondary px-4" data-bs-dismiss="modal">
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
            <div class="modal fade" id="kelolaJadwalModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header border-0 bg-gradient" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);">
                            <h5 class="modal-title text-white fw-bold">
                                <i class="fa-solid fa-calendar me-2"></i> Kelola Jadwal - {{ $item->nama_lapangan }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            {{-- Info Jadwal --}}
                            <div class="alert alert-info mb-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="fa-solid fa-calendar me-2 fs-5"></i>
                                        <div>
                                            <strong>Total Jadwal:</strong> 
                                            <span class="fw-bold">{{ $totalJadwal }}</span> slot
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fa-solid fa-money-bill-wave me-2 fs-5"></i>
                                        <div>
                                            <strong>Harga:</strong> 
                                            <span class="fw-bold text-success">Di-set per jadwal</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fa-solid fa-clock me-2 fs-5"></i>
                                        <div>
                                            <strong>Durasi:</strong> Di-set per jadwal
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Form Tambah Jadwal --}}
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-header bg-transparent border-0">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="fa-solid fa-plus-circle me-2 text-success"></i> Tambah Jadwal Baru
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('lapangan.jadwal.store', $item->id) }}" method="POST" id="formJadwal{{ $item->id }}">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold text-dark">Tanggal</label>
                                                <input type="date" name="tanggal" class="form-control" 
                                                    min="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold text-dark">Jam Mulai</label>
                                                <input type="time" name="jam_mulai" class="form-control" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold text-dark">Jam Selesai</label>
                                                <input type="time" name="jam_selesai" class="form-control" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold text-dark">Durasi (menit)</label>
                                                <input type="number" name="durasi_sewa" class="form-control" 
                                                    min="30" max="300" placeholder="60" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold text-dark">Harga Sewa</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-success text-white">Rp</span>
                                                    <input type="number" name="harga_sewa" class="form-control" 
                                                        placeholder="150000" required>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold text-dark">Status</label>
                                                <select name="tersedia" class="form-select" required>
                                                    <option value="1">Tersedia</option>
                                                    <option value="0">Tidak Tersedia</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fa-solid fa-plus me-1"></i> Tambah Jadwal
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- Daftar Jadwal --}}
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="fa-solid fa-list me-2"></i> Daftar Jadwal ({{ $totalJadwal }})
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Jam Mulai</th>
                                            <th>Jam Selesai</th>
                                            <th>Durasi</th>
                                            <th>Harga</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($item->jadwal->sortBy('tanggal')->sortBy('jam_mulai') as $jadwal)
                                            @php
                                                $jamMulai = \Carbon\Carbon::parse($jadwal->jam_mulai);
                                                $jamSelesai = \Carbon\Carbon::parse($jadwal->jam_selesai);
                                            @endphp
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d/m/Y') }}</td>
                                                <td>{{ $jamMulai->format('H:i') }}</td>
                                                <td>{{ $jamSelesai->format('H:i') }}</td>
                                                <td>{{ $jadwal->durasi_sewa }} menit</td>
                                                <td class="fw-bold text-success">
                                                    Rp {{ number_format($jadwal->harga_sewa, 0, ',', '.') }}
                                                </td>
                                                <td>
                                                    <span class="badge {{ $jadwal->tersedia ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $jadwal->tersedia ? 'Tersedia' : 'Tidak Tersedia' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <form action="{{ route('lapangan.jadwal.destroy', [$item->id, $jadwal->id]) }}" 
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            onclick="return confirm('Yakin hapus jadwal?')">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    <i class="fa-solid fa-calendar-times fa-2x mb-2"></i>
                                                    <br>Belum ada jadwal
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer border-0 bg-light p-4">
                            <button type="button" class="btn btn-lg btn-outline-secondary px-4" data-bs-dismiss="modal">
                                <i class="fa-solid fa-xmark me-2"></i> Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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
            <div class="modal-header border-0 bg-gradient" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <h5 class="modal-title text-white fw-bold">
                    <i class="fa-solid fa-plus-circle me-2"></i> Tambah Lapangan Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('lapangan.store') }}" method="POST" enctype="multipart/form-data" id="formTambah">
                @csrf
                <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row g-4">
                        {{-- Informasi Dasar --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">
                                <i class="fa-solid fa-tag me-1 text-success"></i> Nama Lapangan
                            </label>
                            <input type="text" name="nama_lapangan" class="form-control form-control-lg" placeholder="Contoh: Futsal Arena Pro" value="{{ old('nama_lapangan') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">
                                <i class="fa-solid fa-layer-group me-1 text-success"></i> Jenis Olahraga / Kategori
                            </label>
                            <input type="text" name="kategori" class="form-control form-control-lg" 
                                placeholder="Contoh: Futsal Indoor, Badminton, Basket Outdoor" 
                                value="{{ old('kategori') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">
                                <i class="fa-solid fa-location-dot me-1 text-success"></i> Alamat Lengkap
                            </label>
                            <input type="text" name="lokasi" class="form-control form-control-lg" placeholder="Jl. Sudirman No.123, Jakarta Selatan" value="{{ old('lokasi') }}" required>
                        </div>

                       
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">
                                <i class="fa-solid fa-align-left me-1 text-success"></i> Deskripsi
                            </label>
                            <textarea name="deskripsi" class="form-control" rows="4" placeholder="Jelaskan fasilitas lapangan seperti: AC, lighting, ruang ganti, kantin, dll...">{{ old('deskripsi') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">
                                <i class="fa-solid fa-image me-1 text-success"></i> Upload Foto Lapangan
                            </label>

                            <input 
                                type="file" 
                                name="foto[]" 
                                class="form-control form-control-lg foto-input" 
                                accept="image/*" 
                                multiple 
                                required
                            >

                            <div class="preview-container mt-3 d-flex flex-wrap gap-2"></div>

                            <div class="form-text">
                                <i class="fa-solid fa-circle-info me-1"></i> Bisa upload beberapa foto (JPG, PNG, JPEG) max 2MB/foto
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

<script>
    // ========== PREVIEW MULTIPLE FOTO ==========
    document.querySelectorAll('.foto-input').forEach((fotoInput) => {
        const previewContainer = fotoInput.closest('.col-12').querySelector('.preview-container');
        let selectedFiles = [];

        fotoInput.addEventListener('change', function (event) {
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
                reader.onload = function (e) {
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
                    removeBtn.classList.add('btn', 'btn-sm', 'btn-danger', 'position-absolute', 'top-0', 'end-0');
                    removeBtn.style.transform = 'translate(25%, -25%)';
                    removeBtn.style.fontSize = '16px';
                    removeBtn.style.width = '24px';
                    removeBtn.style.height = '24px';
                    removeBtn.style.padding = '0';
                    removeBtn.style.lineHeight = '1';
                    removeBtn.onclick = function () {
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

    // Validasi form jadwal sebelum submit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const jamMulai = form.querySelector('input[name="jam_mulai"]');
            const jamSelesai = form.querySelector('input[name="jam_selesai"]');
            
            if (jamMulai && jamSelesai && jamMulai.value && jamSelesai.value) {
                if (jamMulai.value >= jamSelesai.value) {
                    e.preventDefault();
                    alert('Jam selesai harus lebih besar dari jam mulai!');
                    return false;
                }
            }
        });
    });

    // Auto close alert setelah 5 detik
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Refresh modal jadwal setelah berhasil tambah jadwal
    @if(session('success') && str_contains(session('success'), 'Jadwal'))
        document.addEventListener('DOMContentLoaded', function() {
            // Cari modal jadwal yang aktif
            const activeModal = document.querySelector('.modal.show');
            if (activeModal && activeModal.id.includes('kelolaJadwalModal')) {
                // Refresh halaman setelah 1 detik untuk update data
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        });
    @endif
</script>

<style>
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
</style>
@endsection