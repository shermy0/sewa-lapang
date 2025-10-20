@extends('layouts.sidebar')

@section('title', 'Detail Lapangan')

@section('content')
<div class="container py-5">
    <div class="card shadow border-0">
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- 🖼️ Foto Lapangan -->
                <div class="col-lg-6">
                    @php
                        // Karena $lapangan->foto sudah array (dari $casts)
                        $fotos = $lapangan->foto ?? [];
                    @endphp

                    @if (count($fotos) > 0)
                        <div id="fotoUtamaContainer" class="mb-3 text-center">
                            <img id="fotoUtama"
                                 src="{{ asset('storage/' . $fotos[0]) }}"
                                 class="rounded shadow-sm img-fluid"
                                 style="width:100%; max-height:400px; object-fit:cover; cursor:pointer;">
                        </div>

                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            @foreach($fotos as $foto)
                                <img src="{{ asset('storage/' . $foto) }}"
                                     class="img-thumbnail pilih-foto"
                                     style="width:90px; height:80px; object-fit:cover; cursor:pointer; transition:0.3s;">
                            @endforeach
                        </div>
                    @else
                        <img src="https://images.unsplash.com/photo-1459865264687-595d652de67e?w=800"
                             class="rounded shadow-sm img-fluid"
                             alt="Default Lapangan"
                             style="width:100%; max-height:400px; object-fit:cover;">
                        <p class="text-center text-muted mt-2">Tidak ada foto tersedia</p>
                    @endif
                </div>

                <!-- 📋 Detail Lapangan -->
                <div class="col-lg-6">
                    <h2 class="fw-bold text-dark mb-3">{{ $lapangan->nama_lapangan }}</h2>

                    <div class="mb-3">
                        <span class="badge bg-primary fs-6">
                            <i class="fa-solid fa-futbol me-1"></i> {{ ucfirst($lapangan->kategori) }}
                        </span>
                        <span class="badge bg-success fs-6">
                            <i class="fa-solid fa-circle me-1"></i> {{ ucfirst($lapangan->status) }}
                        </span>
                    </div>

                    <p class="text-muted mb-2">
                        <i class="fa-solid fa-location-dot text-danger me-2"></i>
                        <strong>Lokasi:</strong> {{ $lapangan->lokasi }}
                    </p>

                    <p class="text-muted mb-2">
                        <i class="fa-solid fa-ticket text-info me-2"></i>
                        <strong>Tiket Tersedia:</strong> {{ $lapangan->tiket_tersedia }}
                    </p>

                    <p class="text-muted mb-2">
                        <i class="fa-solid fa-clock text-secondary me-2"></i>
                        <strong>Durasi Sewa:</strong> {{ $lapangan->durasi_sewa }} jam
                    </p>

                    <p class="text-muted mb-2">
                        <i class="fa-solid fa-money-bill-wave text-success me-2"></i>
                        <strong>Harga per Jam:</strong> Rp {{ number_format($lapangan->harga_sewa, 0, ',', '.') }} / jam
                    </p>

                    <hr>

                    <h5 class="fw-semibold mt-3">Deskripsi</h5>
                    <p class="text-secondary">{{ $lapangan->deskripsi ?: 'Belum ada deskripsi untuk lapangan ini.' }}</p>

                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('lapangan.index') }}" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>

            <!-- 📅 Jadwal Lapangan -->
            <hr class="my-4">
            <h4 class="fw-bold text-dark mb-3">
                <i class="fa-solid fa-calendar-days me-2"></i> Jadwal Lapangan
            </h4>

            @if($lapangan->jadwal->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Jam Mulai</th>
                                <th>Jam Selesai</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lapangan->jadwal as $index => $jadwal)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</td>
                                    <td>
                                        @if($jadwal->tersedia)
                                            <span class="badge bg-success">Tersedia</span>
                                        @else
                                            <span class="badge bg-danger">Penuh</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">Belum ada jadwal tersedia untuk lapangan ini.</p>
            @endif
        </div>
    </div>
</div>

<!-- Modal Foto -->
<div class="modal fade" id="fotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <img id="fotoZoom" src="" class="img-fluid rounded shadow">
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const fotoUtama = document.getElementById('fotoUtama');
    const fotoZoom = document.getElementById('fotoZoom');
    const modal = new bootstrap.Modal(document.getElementById('fotoModal'));
    const thumbnails = document.querySelectorAll('.pilih-foto');

    thumbnails.forEach(img => {
        img.addEventListener('click', () => fotoUtama.src = img.src);
        img.addEventListener('dblclick', () => {
            fotoZoom.src = img.src;
            modal.show();
        });
    });

    fotoUtama?.addEventListener('click', () => {
        fotoZoom.src = fotoUtama.src;
        modal.show();
    });
});
</script>
@endsection
@endsection
