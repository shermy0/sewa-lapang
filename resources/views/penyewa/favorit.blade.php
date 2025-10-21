@extends('layouts.sidebar')

@section('title', 'Lapangan Favorit')

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container py-4">
    <h1 class="fw-bold mb-4" style="color: var(--primary-green);">Lapangan Favorit</h1>
    @if ($favoritLapangan->isEmpty())
        <div class="text-center py-5">
            <i class="fa-solid fa-heart-circle-plus text-success fs-1 mb-3"></i>
            <h5 class="fw-semibold">Belum ada lapangan favorit.</h5>
            <p class="text-muted mb-0">Tambahkan lapangan ke favorit dari halaman detail untuk memudahkan akses.</p>
        </div>
    @else
        <div class="row">
            @foreach ($favoritLapangan as $lapangan)
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <img src="{{ asset('poto/'.$lapangan->foto) }}" class="card-img-top"
                             alt="Foto {{ $lapangan->nama_lapangan }}" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $lapangan->nama_lapangan }}</h5>
                            <p class="text-muted mb-1">
                                <i class="fa-solid fa-location-dot text-success me-1"></i>
                                {{ $lapangan->lokasi }}
                            </p>
                            <p class="fw-semibold text-success mb-3">
                                Rp{{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}/jam
                            </p>
                            <div class="mt-auto d-flex gap-2">
                                <a href="{{ route('penyewa.detail', $lapangan->id) }}" class="btn btn-success flex-grow-1">
                                    Detail
                                </a>
                                <form action="{{ route('penyewa.favorit.destroy', $lapangan) }}" method="POST" class="d-inline"
                                      data-confirm="Hapus lapangan ini dari daftar favorit?"
                                      data-confirm-title="Hapus Favorit"
                                      data-confirm-button="Ya, hapus"
                                      data-cancel-button="Batal">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger flex-grow-1">
                                        <i class="fa-solid fa-heart-crack me-1"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
