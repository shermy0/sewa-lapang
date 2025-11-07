@extends('layouts.sidebar')

@section('title', 'Detail Lapangan')

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row align-items-center mb-4">
        <div class="col-lg-8">
            <h2 class="fw-bold text-dark mb-2">
                <i class="fa-solid fa-layer-group me-2 text-success"></i> Detail Lapangan
            </h2>
            <p class="text-muted mb-0">Informasi lengkap lapangan beserta jadwal sewa</p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="{{ route('lapangan.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    {{-- Alert --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-check-circle me-2"></i>
                <div class="flex-grow-1">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <div class="row g-4">
        {{-- Kolom kiri - Foto Lapangan --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-0 bg-light">
                    @php
                        $fotos = $lapangan->foto ?? [];
                        if (!is_array($fotos)) $fotos = [];
                    @endphp

                    @if(count($fotos) > 0)
                        @php $carouselId = 'carouselLapanganDetail' . $lapangan->id; @endphp
                        <div id="{{ $carouselId }}" class="carousel slide h-100" data-bs-ride="carousel">
                            <div class="carousel-inner h-100" style="height: 400px;">
                                @foreach($fotos as $i => $foto)
                                    <div class="carousel-item h-100 {{ $i == 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/' . $foto) }}" 
                                             class="d-block w-100 h-100"
                                             alt="{{ $lapangan->nama_lapangan }}"
                                             style="object-fit: cover; object-position: center;">
                                    </div>
                                @endforeach
                            </div>
                            @if(count($fotos) > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                                <div class="position-absolute bottom-0 end-0 m-3" style="z-index: 10;">
                                    <span class="badge bg-dark bg-opacity-75 px-2 py-1">
                                        <i class="fa-solid fa-images me-1"></i> {{ count($fotos) }} Foto
                                    </span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="w-100 h-100" style="height: 400px;">
                            <img src="https://images.unsplash.com/photo-1459865264687-595d652de67e?w=600&h=400&fit=crop"
                                 class="w-100 h-100"
                                 alt="Default Image"
                                 style="object-fit: cover; object-position: center;">
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom kanan - Detail Informasi --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    {{-- Header Info --}}
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h3 class="fw-bold text-dark mb-2">{{ $lapangan->nama_lapangan }}</h3>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-success text-uppercase px-3 py-2">
                                    {{ $lapangan->kategori ? ucfirst($lapangan->kategori) : 'Tanpa Kategori' }}
                                </span>
                                <span class="text-muted d-flex align-items-center">
                                    <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                    {{ $lapangan->lokasi }}
                                </span>
                            </div>
                        </div>
                        @if($lapangan->harga_sewa)
                            <div class="text-end">
                                <div class="small text-muted">Harga Rata-rata per Jam</div>
                                <div class="h4 fw-bold text-success">
                                    Rp {{ number_format($lapangan->harga_sewa, 0, ',', '.') }}
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-4">
                        <h6 class="fw-semibold text-dark mb-2">
                            <i class="fa-solid fa-align-left me-2 text-primary"></i> Deskripsi
                        </h6>
                        <p class="text-muted mb-0">
                            {{ $lapangan->deskripsi ?: 'Belum ada deskripsi yang ditambahkan untuk lapangan ini.' }}
                        </p>
                    </div>

                    {{-- Statistik Sections --}}
                    <div class="row g-3 mb-4">
                        @php
                            $totalSections = $lapangan->sections->count();
                            $totalJadwal = 0;
                            $hargaRataRata = 0;
                            
                            foreach ($lapangan->sections as $section) {
                                $totalJadwal += $section->jadwal->count();
                                if ($section->jadwal->count() > 0) {
                                    $hargaRataRata += $section->jadwal->avg('harga_sewa');
                                }
                            }
                            if ($lapangan->sections->count() > 0) {
                                $hargaRataRata = $hargaRataRata / $lapangan->sections->count();
                            }
                        @endphp

                        <div class="col-md-4">
                            <div class="card border-0 bg-primary bg-opacity-10">
                                <div class="card-body text-center py-3">
                                    <div class="text-primary mb-1">
                                        <i class="fa-solid fa-layer-group fa-2x"></i>
                                    </div>
                                    <div class="h4 fw-bold text-primary mb-0">{{ $totalSections }}</div>
                                    <small class="text-muted">Total Section</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-success bg-opacity-10">
                                <div class="card-body text-center py-3">
                                    <div class="text-success mb-1">
                                        <i class="fa-solid fa-calendar fa-2x"></i>
                                    </div>
                                    <div class="h4 fw-bold text-success mb-0">{{ $totalJadwal }}</div>
                                    <small class="text-muted">Total Jadwal</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-info bg-opacity-10">
                                <div class="card-body text-center py-3">
                                    <div class="text-info mb-1">
                                        <i class="fa-solid fa-money-bill-wave fa-2x"></i>
                                    </div>
                                    <div class="h4 fw-bold text-info mb-0">
                                        Rp {{ number_format($hargaRataRata, 0, ',', '.') }}
                                    </div>
                                    <small class="text-muted">Harga Rata-rata</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Daftar Sections --}}
                    <div class="mb-4">
                        <h6 class="fw-semibold text-dark mb-3">
                            <i class="fa-solid fa-layer-group me-2 text-primary"></i> Daftar Section
                        </h6>
                        <div class="row g-3">
                            @foreach($lapangan->sections as $section)
                                <div class="col-md-6">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="fw-bold text-dark mb-0">{{ $section->nama_section }}</h6>
                                                <span class="badge bg-primary">
                                                    {{ $section->jadwal->count() }} jadwal
                                                </span>
                                            </div>
                                            @if($section->deskripsi)
                                                <p class="text-muted small mb-0">{{ $section->deskripsi }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Jadwal Lapangan --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-0 py-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="fa-solid fa-calendar-days text-success me-2"></i> Jadwal Lapangan
                    </h5>
                    <span class="text-muted">Daftar slot waktu yang tersedia maupun terisi</span>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                    Total jadwal: {{ $lapangan->jadwal->count() }}
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            @php
                use Carbon\Carbon;

                // Ambil jadwal tersedia
                $jadwalTersedia = $lapangan->jadwal
                    ->sortBy(['tanggal', 'jam_mulai']);
            @endphp

            @if($jadwalTersedia->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-success bg-opacity-10 text-success fw-semibold">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Tanggal</th>
                                <th>Section</th>
                                <th>Rentang Waktu</th>
                                <th class="text-center">Durasi</th>
                                <th class="text-center">Harga Total</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jadwalTersedia as $i => $jadwal)
                                @php
                                    try {
                                        $mulai = Carbon::parse($jadwal->jam_mulai);
                                        $selesai = Carbon::parse($jadwal->jam_selesai);
                                        $durasiMenit = $jadwal->durasi_sewa ?? $mulai->diffInMinutes($selesai);
                                        $durasiJam = $durasiMenit / 60;
                                        $hargaTotal = $jadwal->harga_sewa * $durasiJam;
                                    } catch (\Exception $e) {
                                        $durasiJam = 0;
                                        $hargaTotal = 0;
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center fw-semibold">{{ $i + 1 }}</td>
                                    <td class="text-nowrap">{{ Carbon::parse($jadwal->tanggal)->translatedFormat('d M Y') }}</td>
                                    <td class="text-nowrap">
                                        <span class="fw-semibold">{{ $jadwal->section->nama_section ?? '-' }}</span>
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
            @else
                <div class="p-5 text-center text-muted">
                    <i class="fa-solid fa-calendar-xmark fa-2x mb-3"></i>
                    <p class="mb-0">Belum ada jadwal yang ditambahkan untuk lapangan ini.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Styling tambahan --}}
<style>
    .bg-success-subtle {
        background: rgba(25, 135, 84, 0.12);
    }
    .bg-success-subtle.text-success {
        color: #198754 !important;
    }
    .table > :not(caption) > * > * {
        vertical-align: middle;
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
</style>

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- Animate.css for smooth animations --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<script>
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
</script>
@endsection