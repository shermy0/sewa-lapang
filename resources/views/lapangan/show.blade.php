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
                        $fotos = json_decode($lapangan->foto, true) ?? [];
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
                        <span class="badge bg-primary fs-6"><i class="fa-solid fa-futbol me-1"></i> {{ ucfirst($lapangan->kategori) }}</span>
                        <span class="badge bg-success fs-6"><i class="fa-solid fa-circle me-1"></i> {{ ucfirst($lapangan->status) }}</span>
                    </div>

                    <p class="text-muted mb-2">
                        <i class="fa-solid fa-location-dot text-danger me-2"></i>
                        <strong>Lokasi:</strong> {{ $lapangan->lokasi }}
                    </p>

                    <p class="text-muted mb-2">
                        <i class="fa-solid fa-money-bill-wave text-success me-2"></i>
                        <strong>Harga Sewa:</strong> Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }} / jam
                    </p>

                    <p class="text-muted mb-2">
                        <i class="fa-solid fa-star text-warning me-2"></i>
                        <strong>Rating:</strong>
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="fa-solid fa-star {{ $i <= $lapangan->rating ? 'text-warning' : 'text-secondary' }}"></i>
                        @endfor
                        <span class="ms-1">({{ number_format($lapangan->rating, 1) }})</span>
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
        </div>
    </div>
</div>

<!-- 🔍 Modal Zoom Foto -->
<div class="modal fade" id="fotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <img id="fotoZoom" src="" class="img-fluid rounded shadow">
        </div>
    </div>
</div>

<!-- 🧠 Script Foto Interaktif -->
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const fotoUtama = document.getElementById('fotoUtama');
    const fotoZoom = document.getElementById('fotoZoom');
    const modal = new bootstrap.Modal(document.getElementById('fotoModal'));
    const thumbnails = document.querySelectorAll('.pilih-foto');

    // Klik thumbnail ganti foto utama
    thumbnails.forEach(img => {
        img.addEventListener('click', () => {
            fotoUtama.src = img.src;
        });

        // Klik dua kali untuk zoom
        img.addEventListener('dblclick', () => {
            fotoZoom.src = img.src;
            modal.show();
        });
    });

    // Klik gambar utama untuk zoom
    fotoUtama?.addEventListener('click', () => {
        fotoZoom.src = fotoUtama.src;
        modal.show();
    });
});
</script>
@endsection
@endsection
