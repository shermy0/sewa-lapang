@extends('layouts.sidebar')

@section('title', 'Lapangan Difavoritkan')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4 text-dark">Lapangan yang Difavoritkan</h2>

    @forelse ($lapanganFavorit as $lapangan)
        <div class="card mb-3 shadow-sm border-0 rounded-3 p-3">
            <div class="row g-0 align-items-center">
                <div class="col-md-4">
                    <img src="{{ asset('storage/' . $lapangan->foto_lapangan) }}" 
                         class="img-fluid rounded-start" 
                         alt="{{ $lapangan->nama_lapangan }}">
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title">{{ $lapangan->nama_lapangan }}</h5>
                        <p class="text-muted mb-2">
                            <i class="fa-solid fa-location-dot text-secondary me-2"></i>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($lapangan->lokasi) }}" 
                               target="_blank" 
                               class="text-decoration-none text-secondary">
                               {{ $lapangan->lokasi }}
                            </a>
                        </p>
                        <div class="mb-3">
                            <strong>Rating:</strong>
                            @if($lapangan->totalUlasan > 0)
                                @for ($i=1; $i<=5; $i++)
                                    @if($i <= floor($lapangan->avgRating))
                                        <i class="fa-solid fa-star text-warning"></i>
                                    @elseif ($i == ceil($lapangan->avgRating) && $lapangan->avgRating - floor($lapangan->avgRating) >= 0.5)
                                        <i class="fa-solid fa-star-half-stroke text-warning"></i>
                                    @else
                                        <i class="fa-regular fa-star text-warning"></i>
                                    @endif
                                @endfor
                                ({{ number_format($lapangan->avgRating,1) }}/5 dari {{ $lapangan->totalUlasan }} ulasan)
                            @else
                                <span class="text-muted">Belum ada ulasan</span>
                            @endif
                        </div>
                        <button 
                            class="btn btn-outline-dark btn-sm show-favorit"
                            data-favorit='@json($lapangan->favoritedBy)'
                            data-nama="{{ $lapangan->nama_lapangan }}">
                            <i class="fa-solid fa-heart me-1 text-danger"></i>
                            {{ $lapangan->favoritedBy->count() }} Favorit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted text-center">Belum ada lapangan yang difavoritkan.</p>
    @endforelse

    <!-- Modal -->
    <div class="modal fade" id="favoritModal" tabindex="-1" aria-labelledby="favoritModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light border-0 rounded-top-4">
                    <h5 class="modal-title fw-semibold" id="favoritModalLabel">Penyewa yang Memfavoritkan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="favoritModalBody" style="max-height:350px;overflow-y:auto;">
                    <!-- Data penyewa akan dimasukkan lewat JS -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.show-favorit').forEach(btn => {
    btn.addEventListener('click', function() {
        const favoritList = JSON.parse(this.dataset.favorit);
        const modalBody = document.getElementById('favoritModalBody');
        const namaLapangan = this.dataset.nama;
        document.getElementById('favoritModalLabel').innerText = `Penyewa yang Memfavoritkan ${namaLapangan}`;

        if (favoritList.length === 0) {
            modalBody.innerHTML = '<p class="text-center text-muted mb-0">Belum ada yang memfavoritkan lapangan ini.</p>';
        } else {
            modalBody.innerHTML = favoritList.map(user => {
                const foto = user.foto ? `/storage/${user.foto}` : 'https://via.placeholder.com/50';
                return `
                    <div class="favorit-user mb-3">
                    <img src="${user.foto_profil 
                        ? (user.foto_profil.startsWith('http') 
                            ? user.foto_profil 
                            : '/storage/' + user.foto_profil) 
                        : '{{ asset('images/profile.jpg') }}'}" 
                        class="rounded-circle me-3" width="50" height="50">
                        <strong>${user.name}</strong><br>
                    </div>
                `;
            }).join('');
        }

        const modal = new bootstrap.Modal(document.getElementById('favoritModal'));
        modal.show();
    });
});
</script>
@endpush
