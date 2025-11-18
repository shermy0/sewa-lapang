@extends('layouts.sidebar')

@section('title', 'Edit Ulasan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-pen-to-square me-2 text-success"></i>Edit Ulasan</h5>
                    <a href="{{ route('penyewa.detail', $ulasan->pemesanan->lapangan_id) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    @foreach (['success','error'] as $flash)
                        @if(session($flash))
                            <div class="alert alert-{{ $flash === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                                {{ session($flash) }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    @endforeach

                    <form action="{{ route('ulasan.update', $ulasan->id) }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lapangan</label>
                            <input type="text" class="form-control" value="{{ $ulasan->pemesanan->lapangan->nama_lapangan ?? '-' }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rating</label>
                            <div class="rating-stars">
                                @for ($i = 1; $i <= 5; $i++)
                                    <input type="radio" id="edit-star-{{ $i }}" name="rating" value="{{ $i }}" {{ $ulasan->rating == $i ? 'checked' : '' }}>
                                    <label for="edit-star-{{ $i }}" title="{{ $i }} stars">
                                        @if($ulasan->rating >= $i)
                                            <i class="fa-solid fa-star text-warning"></i>
                                        @else
                                            <i class="fa-regular fa-star text-warning"></i>
                                        @endif
                                    </label>
                                @endfor
                            </div>
                            @error('rating')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Komentar</label>
                            <textarea name="komentar" class="form-control @error('komentar') is-invalid @enderror" rows="4" required>{{ old('komentar', $ulasan->komentar) }}</textarea>
                            @error('komentar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('penyewa.detail', $ulasan->pemesanan->lapangan_id) }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                        </div>
                    </form>

                    <form action="{{ route('ulasan.hapus', $ulasan->id) }}" method="POST" class="mt-3" onsubmit="return confirm('Hapus ulasan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fa-solid fa-trash me-1"></i> Hapus Ulasan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CSS rating sederhana --}}
<style>
    .rating-stars {
        display: flex;
        gap: 0.4rem;
        align-items: center;
    }
    .rating-stars input[type="radio"] { display: none; }
    .rating-stars label { cursor: pointer; font-size: 1.5rem; margin: 0; }
</style>

@endsection
