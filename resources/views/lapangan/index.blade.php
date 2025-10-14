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
                        <select name="kategori" class="form-select form-select-lg">
                            <option value="">Semua Kategori</option>
                            <option value="futsal" {{ request('kategori')=='futsal' ? 'selected' : '' }}>Futsal</option>
                            <option value="badminton" {{ request('kategori')=='badminton' ? 'selected' : '' }}>Badminton</option>
                            <option value="basket" {{ request('kategori')=='basket' ? 'selected' : '' }}>Basket</option>
                            <option value="tenis" {{ request('kategori')=='tenis' ? 'selected' : '' }}>Tenis</option>
                            <option value="voli" {{ request('kategori')=='voli' ? 'selected' : '' }}>Voli</option>
                            <option value="mini-soccer" {{ request('kategori')=='mini-soccer' ? 'selected' : '' }}>Mini Soccer</option>
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div class="col-lg-2">
                        <select name="status" class="form-select form-select-lg">
                            <option value="">Semua Status</option>
                            <option value="premium" {{ request('status')=='premium' ? 'selected' : '' }}>Premium</option>
                            <option value="populer" {{ request('status')=='populer' ? 'selected' : '' }}>Populer</option>
                            <option value="promo" {{ request('status')=='promo' ? 'selected' : '' }}>Promo</option>
                            <option value="standard" {{ request('status')=='standard' ? 'selected' : '' }}>Standard</option>
                        </select>
                    </div>

                    {{-- Filter Harga --}}
                    <div class="col-lg-2">
                        <select name="sort_harga" class="form-select form-select-lg">
                            <option value="">Urutkan Harga</option>
                            <option value="asc" {{ request('sort_harga')=='asc' ? 'selected' : '' }}>Termurah</option>
                            <option value="desc" {{ request('sort_harga')=='desc' ? 'selected' : '' }}>Termahal</option>
                        </select>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="col-lg-2 d-flex gap-2">
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

    {{-- Grid Layout Cards --}}
    <div class="row g-4">
        @foreach ($lapangan as $item)
            <div class="col-lg-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden hover-lift">
                    {{-- Image Section with Carousel --}}
                    <div class="position-relative" style="height: 220px; overflow: hidden;"> {{-- Diperkecil dari 280px --}}
                        @if (!empty($item->foto) && is_array(json_decode($item->foto, true)) && count(json_decode($item->foto, true)) > 0)
                            @php $fotoArray = json_decode($item->foto, true); @endphp
                            
                            {{-- Jika lebih dari 1 foto, gunakan carousel --}}
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
                                    {{-- Indikator jumlah foto --}}
                                    <div class="position-absolute bottom-0 end-0 mb-2 me-2" style="z-index: 10;">
                                        <span class="badge bg-dark bg-opacity-75 px-2 py-1">
                                            <i class="fa-solid fa-images me-1"></i> {{ count($fotoArray) }} Foto
                                        </span>
                                    </div>
                                </div>
                            @else
                                {{-- Jika hanya 1 foto --}}
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
                        
                        {{-- Badge Status --}}
                        <div class="position-absolute top-0 end-0 m-3" style="z-index: 10;">
                            <span class="badge bg-success px-3 py-2 shadow">
                                <i class="fa-solid fa-star me-1"></i> {{ ucfirst($item->status) }}
                            </span>
                        </div>
                        
                        {{-- Badge Kategori --}}
                        <div class="position-absolute bottom-0 start-0 m-3" style="z-index: 10;">
                            <span class="badge bg-dark bg-opacity-75 px-3 py-2">
                                <i class="fa-solid 
                                    @if ($item->kategori == 'futsal') fa-futbol
                                    @elseif($item->kategori == 'badminton') fa-table-tennis-paddle-ball
                                    @elseif($item->kategori == 'basket') fa-basketball
                                    @elseif($item->kategori == 'voli') fa-volleyball
                                    @elseif($item->kategori == 'tenis') fa-table-tennis
                                    @else fa-dumbbell @endif me-1"></i>
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
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <small class="text-muted d-block">Harga Sewa</small>
                                <h4 class="text-success fw-bold mb-0">Rp {{ number_format($item->harga_per_jam, 0, ',', '.') }}<small class="text-muted fs-6">/jam</small></h4>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Rating</small>
                                <div>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star {{ $i <= $item->rating ? 'text-warning' : 'text-muted' }}"></i>
                                    @endfor
                                    <small class="text-muted ms-1">({{ number_format($item->rating, 1) }})</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-success flex-fill" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editLapanganModal{{ $item->id }}">
                                <i class="fa-solid fa-pen me-1"></i> Edit
                            </button>
                            <a href="{{ route('lapangan.show', $item->id) }}" class="btn btn-outline-primary flex-fill">
                                <i class="fa-solid fa-eye me-1"></i> Detail
                            </a>
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
                            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;"> {{-- Ditambahkan scroll --}}
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-tag me-1 text-success"></i> Nama Lapangan
                                        </label>
                                        <input type="text" name="nama_lapangan" class="form-control form-control-lg" 
                                            value="{{ $item->nama_lapangan }}" required>
                                        @error('nama_lapangan')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-layer-group me-1 text-success"></i> Jenis Olahraga
                                        </label>
                                        <input type="text" name="kategori" class="form-control form-control-lg" 
                                            value="{{ $item->kategori }}" placeholder="Contoh: Futsal, Badminton, Basket" required>
                                        <div class="form-text">
                                            <i class="fa-solid fa-circle-info me-1"></i> Masukkan jenis olahraga (bisa lebih dari 1, pisahkan dengan koma)
                                        </div>
                                        @error('kategori')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-location-dot me-1 text-success"></i> Alamat Lengkap
                                        </label>
                                        <input type="text" name="lokasi" class="form-control form-control-lg" 
                                            value="{{ $item->lokasi }}" required>
                                        @error('lokasi')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-money-bill-wave me-1 text-success"></i> Harga per Jam
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-success text-white fw-bold">Rp</span>
                                            <input type="number" name="harga_per_jam" class="form-control" 
                                                value="{{ $item->harga_per_jam }}" required>
                                        </div>
                                        @error('harga_per_jam')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-medal me-1 text-success"></i> Status
                                        </label>
                                        <select name="status" class="form-select form-select-lg" required>
                                            <option value="premium" {{ $item->status == 'premium' ? 'selected' : '' }}>Premium</option>
                                            <option value="populer" {{ $item->status == 'populer' ? 'selected' : '' }}>Populer</option>
                                            <option value="promo" {{ $item->status == 'promo' ? 'selected' : '' }}>Promo</option>
                                            <option value="standard" {{ $item->status == 'standard' ? 'selected' : '' }}>Standard</option>
                                        </select>
                                        @error('status')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-star text-warning me-1"></i> Rating
                                        </label>
                                        <div class="rating fs-4" id="editRating{{ $item->id }}">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="fa-regular fa-star {{ $i <= $item->rating ? 'fa-solid text-warning' : '' }}" 
                                                   data-value="{{ $i }}"></i>
                                            @endfor
                                        </div>
                                        <input type="hidden" name="rating" id="editRatingValue{{ $item->id }}" value="{{ $item->rating }}" required>
                                        @error('rating')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-align-left me-1 text-success"></i> Deskripsi Fasilitas
                                        </label>
                                        <textarea name="deskripsi" class="form-control" rows="4">{{ $item->deskripsi }}</textarea>
                                        @error('deskripsi')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-image me-1 text-success"></i> Upload Foto Lapangan
                                        </label>
                                        <input type="file" name="foto[]" class="form-control form-control-lg foto-input" accept="image/*" multiple>
                                        <div class="form-text">
                                            <i class="fa-solid fa-circle-info me-1"></i> Bisa upload beberapa foto (JPG, PNG, JPEG) max 2MB/foto
                                        </div>

                                        <!-- Tempat preview muncul -->
                                        <div class="preview-container mt-3 d-flex flex-wrap gap-2"></div>

                                        @error('foto.*')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                        
                                        {{-- Tampilkan foto yang sudah ada --}}
                                        @if ($item->foto && is_array(json_decode($item->foto, true)))
                                            <div class="mt-3">
                                                <small class="text-muted d-block mb-2">Foto saat ini:</small>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach (json_decode($item->foto, true) as $photo)
                                                        <div class="position-relative" style="width: 100px; height: 80px;"> {{-- Diperkecil --}}
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
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">
                                    <i class="fa-solid fa-tag me-1 text-success"></i> Nama Lapangan
                                </label>
                                <input type="text" name="nama_lapangan" class="form-control form-control-lg" placeholder="Contoh: Futsal Arena Pro" value="{{ old('nama_lapangan') }}" required>
                                @error('nama_lapangan')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">
                                    <i class="fa-solid fa-layer-group me-1 text-success"></i> Jenis Olahraga
                                </label>
                                <input type="text" name="kategori" class="form-control form-control-lg" placeholder="Contoh: Futsal, Badminton, Basket" value="{{ old('kategori') }}" required>
                                <div class="form-text">
                                    <i class="fa-solid fa-circle-info me-1"></i> Masukkan jenis olahraga (bisa lebih dari 1, pisahkan dengan koma)
                                </div>
                                @error('kategori')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">
                                    <i class="fa-solid fa-location-dot me-1 text-success"></i> Alamat Lengkap
                                </label>
                                <input type="text" name="lokasi" class="form-control form-control-lg" placeholder="Jl. Sudirman No.123, Jakarta Selatan" value="{{ old('lokasi') }}" required>
                                @error('lokasi')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">
                                    <i class="fa-solid fa-money-bill-wave me-1 text-success"></i> Harga per Jam
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-success text-white fw-bold">Rp</span>
                                    <input type="number" name="harga_per_jam" class="form-control" placeholder="150000" value="{{ old('harga_per_jam') }}" required>
                                </div>
                                @error('harga_per_jam')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">
                                    <i class="fa-solid fa-medal me-1 text-success"></i> Status
                                </label>
                                <select name="status" class="form-select form-select-lg" required>
                                    <option value="premium" {{ old('status') == 'premium' ? 'selected' : '' }}>Premium</option>
                                    <option value="populer" {{ old('status') == 'populer' ? 'selected' : '' }}>Populer</option>
                                    <option value="promo" {{ old('status') == 'promo' ? 'selected' : '' }}>Promo</option>
                                    <option value="standard" {{ old('status') == 'standard' ? 'selected' : '' }}>Standard</option>
                                </select>
                                @error('status')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">
                                    <i class="fa-solid fa-star text-warning me-1"></i> Rating
                                </label>
                                <div class="rating fs-4">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="fa-regular fa-star {{ $i <= old('rating', 0) ? 'fa-solid text-warning' : '' }}" 
                                           data-value="{{ $i }}"></i>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" id="ratingValue" value="{{ old('rating', 0) }}" required>
                                @error('rating')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">
                                    <i class="fa-solid fa-align-left me-1 text-success"></i> Deskripsi Fasilitas
                                </label>
                                <textarea name="deskripsi" class="form-control" rows="4" placeholder="Jelaskan fasilitas lapangan seperti: AC, lighting, ruang ganti, kantin, dll...">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">
                                    <i class="fa-solid fa-image me-1 text-success"></i> Upload Foto Lapangan
                                </label>

                                <!-- Input File -->
                                <input 
                                    type="file" 
                                    name="foto[]" 
                                    class="form-control form-control-lg foto-input" 
                                    accept="image/*" 
                                    multiple 
                                    required
                                >

                                <!-- Preview Container -->
                                <div class="preview-container mt-3 d-flex flex-wrap gap-2"></div>

                                <div class="form-text">
                                    <i class="fa-solid fa-circle-info me-1"></i> Bisa upload beberapa foto (JPG, PNG, JPEG) max 2MB/foto
                                </div>

                                @error('foto.*')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* -----------------------------
     * ⭐ RATING SYSTEM - CREATE MODAL
     * ----------------------------- */
    const starsCreate = document.querySelectorAll('#tambahLapanganModal .rating i');
    const ratingInputCreate = document.getElementById('ratingValue');
    if (starsCreate && ratingInputCreate) {
        starsCreate.forEach(star => {
            star.addEventListener('click', function () {
                const value = parseInt(this.getAttribute('data-value'));
                ratingInputCreate.value = value;
                starsCreate.forEach((s, index) => {
                    if (index < value) {
                        s.classList.remove('fa-regular');
                        s.classList.add('fa-solid', 'text-warning');
                    } else {
                        s.classList.remove('fa-solid', 'text-warning');
                        s.classList.add('fa-regular');
                    }
                });
            });
        });
    }

    /* -----------------------------
     * ⭐ RATING SYSTEM - EDIT MODALS
     * ----------------------------- */
    document.querySelectorAll('[id^="editRating"]').forEach(ratingContainer => {
        const modalId = ratingContainer.id.replace('editRating', '');
        const stars = ratingContainer.querySelectorAll('i');
        const ratingInput = document.getElementById('editRatingValue' + modalId);

        stars.forEach(star => {
            star.addEventListener('click', function () {
                const value = parseInt(this.getAttribute('data-value'));
                ratingInput.value = value;
                stars.forEach((s, index) => {
                    if (index < value) {
                        s.classList.remove('fa-regular');
                        s.classList.add('fa-solid', 'text-warning');
                    } else {
                        s.classList.remove('fa-solid', 'text-warning');
                        s.classList.add('fa-regular');
                    }
                });
            });
        });
    });

    /* -----------------------------
     * 🖼️ PREVIEW MULTIPLE FOTO (TAMBAH FILE TANPA HILANG)
     * ----------------------------- */

    // Ambil semua input file (bisa ada di form tambah & edit)
    document.querySelectorAll('.foto-input').forEach((fotoInput) => {
        const previewContainer = fotoInput.closest('.col-12').querySelector('.preview-container');
        let selectedFiles = []; // Menyimpan semua file yang sudah dipilih

        // Saat input file berubah
        fotoInput.addEventListener('change', function (event) {
            const files = Array.from(event.target.files);
            selectedFiles = [...selectedFiles, ...files]; // Tambahkan ke array lama
            renderPreview();
        });

        // Fungsi render ulang preview
        function renderPreview() {
            previewContainer.innerHTML = ''; // Bersihkan preview lama
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
                    img.style.width = '100px'; // Diperkecil
                    img.style.height = '80px';  // Diperkecil
                    img.style.objectFit = 'cover';

                    // Tombol hapus di tiap gambar
                    const removeBtn = document.createElement('button');
                    removeBtn.textContent = '×';
                    removeBtn.type = 'button';
                    removeBtn.classList.add('btn', 'btn-sm', 'btn-danger', 'position-absolute', 'top-0', 'end-0');
                    removeBtn.style.transform = 'translate(25%, -25%)';
                    removeBtn.style.fontSize = '12px';
                    removeBtn.style.width = '20px';
                    removeBtn.style.height = '20px';
                    removeBtn.style.padding = '0';
                    removeBtn.style.display = 'flex';
                    removeBtn.style.alignItems = 'center';
                    removeBtn.style.justifyContent = 'center';
                    removeBtn.onclick = function () {
                        selectedFiles.splice(index, 1); // Hapus dari array
                        renderPreview(); // Render ulang
                    };

                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    previewContainer.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });

            // Update isi input file agar dikirim semua
            fotoInput.files = dataTransfer.files;
        }
    });

});
</script>

<style>
    .hover-lift {
        transition: all 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-8px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
    }
    .carousel-item img {
        transition: transform 0.3s ease;
    }
    .card:hover .carousel-item.active img {
        transform: scale(1.05);
    }
    .btn {
        transition: all 0.3s ease;
    }
    .btn:hover {
        transform: translateY(-2px);
    }
    .rating i {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .rating i:hover {
        transform: scale(1.2);
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
    
    /* Styling khusus untuk modal yang scrollable */
    #tambahLapanganModal .modal-body,
    [id^="editLapanganModal"] .modal-body {
        scrollbar-width: thin;
        scrollbar-color: #c1c1c1 #f1f1f1;
    }
    
    #tambahLapanganModal .modal-body::-webkit-scrollbar,
    [id^="editLapanganModal"] .modal-body::-webkit-scrollbar {
        width: 6px;
    }
    
    #tambahLapanganModal .modal-body::-webkit-scrollbar-track,
    [id^="editLapanganModal"] .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    #tambahLapanganModal .modal-body::-webkit-scrollbar-thumb,
    [id^="editLapanganModal"] .modal-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    
    #tambahLapanganModal .modal-body::-webkit-scrollbar-thumb:hover,
    [id^="editLapanganModal"] .modal-body::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endsection