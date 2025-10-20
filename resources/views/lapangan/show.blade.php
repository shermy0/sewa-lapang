@extends('layouts.sidebar')

@section('title', 'Detail Lapangan')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-futbol text-success me-2"></i> Detail Lapangan
            </h2>
            <p class="text-muted mb-0">Informasi lengkap dan jadwal tersedia</p>
        </div>
        <a href="{{ route('lapangan.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    {{-- Alert --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Kolom kiri --}}
        <div class="col-lg-8 mx-auto">
            {{-- Foto Lapangan --}}
            <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                <div class="card-body p-0">
                    @php
                        $fotos = $lapangan->foto ?? [];
                        if (!is_array($fotos)) $fotos = [];
                    @endphp

                    @if(count($fotos) > 0)
                        <div id="carouselLapangan" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner rounded">
                                @foreach($fotos as $i => $foto)
                                    <div class="carousel-item {{ $i == 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/' . $foto) }}"
                                             class="d-block w-100 rounded"
                                             alt="Foto Lapangan"
                                             style="height: 220px; object-fit: cover;">
                                    </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselLapangan" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselLapangan" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>
                    @else
                        <img src="https://images.unsplash.com/photo-1459865264687-595d652de67e?w=800"
                             class="w-100 rounded"
                             style="height:220px; object-fit:cover;"
                             alt="Default">
                    @endif
                </div>
            </div>

            {{-- Detail Informasi --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-bold mb-0">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i> Informasi Lapangan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>Nama Lapangan:</strong> <br>
                            {{ $lapangan->nama_lapangan }}
                        </div>
                        <div class="col-md-6">
                            <strong>Kategori:</strong> <br>
                            <span class="badge bg-success px-3 py-2">{{ ucfirst($lapangan->kategori) }}</span>
                        </div>
                        <div class="col-12">
                            <strong>Lokasi:</strong> <br>
                            <i class="fa-solid fa-location-dot text-danger me-2"></i>
                            {{ $lapangan->lokasi }}
                        </div>
                        <div class="col-12">
                            <strong>Deskripsi:</strong>
                            <div class="bg-light rounded p-3 mt-1">
                                {{ $lapangan->deskripsi ?: 'Belum ada deskripsi untuk lapangan ini.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Jadwal Lapangan --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">
                        <i class="fa-solid fa-calendar-days text-success me-2"></i> Jadwal Lapangan
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        use Carbon\Carbon;

                        // Ambil jadwal tersedia
                        $jadwalTersedia = $lapangan->jadwal
                            ->sortBy(['tanggal', 'jam_mulai']);
                    @endphp

                    @if($jadwalTersedia->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-center">
                                <thead class="table-success">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Jam Mulai</th>
                                        <th>Jam Selesai</th>
                                        <th>Durasi</th>
                                        <th>Harga per Jam</th>
                                        <th>Status</th>
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
                                            } catch (\Exception $e) {
                                                $durasiJam = 0;
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ Carbon::parse($jadwal->tanggal)->format('d M Y') }}</td>
                                            <td>{{ $mulai->format('H:i') }}</td>
                                            <td>{{ $selesai->format('H:i') }}</td>
                                            <td>{{ $durasiJam }} jam</td>
                                            <td>Rp {{ number_format($jadwal->harga_sewa, 0, ',', '.') }}</td>
                                            <td>
                                                @if($jadwal->tersedia)
                                                    <span class="badge bg-success px-3 py-2">Tersedia</span>
                                                @else
                                                    <span class="badge bg-secondary px-3 py-2">Tidak Tersedia</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Tidak ada jadwal tersedia saat ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Styling tambahan --}}
<style>
    .carousel-inner {
        height: 220px !important;
    }
    .carousel-item img {
        object-fit: cover;
        height: 220px;
        border-radius: 10px;
    }
    .table th {
        font-weight: 600;
        color: #2d3748;
    }
    .table td {
        vertical-align: middle;
    }
    .card {
        border-radius: 15px !important;
    }
    .card-header h5 {
        font-weight: 600;
        color: #2c3e50;
    }
</style>
@endsection
