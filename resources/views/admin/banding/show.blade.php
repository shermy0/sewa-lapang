@extends('layouts.admin')

@section('title', 'Detail Banding Pemilik')

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <a href="{{ route('admin.banding.index') }}" class="text-decoration-none text-muted small">
                <i class="fa-solid fa-arrow-left-long me-1"></i> Kembali ke daftar banding
            </a>
            <h4 class="fw-bold mt-2 mb-1 text-dark">
                <i class="fa-solid fa-scale-balanced text-primary me-2"></i> Banding #{{ $banding->id }}
            </h4>
            <p class="text-muted mb-0">Diajukan pada {{ optional($banding->created_at)->translatedFormat('d F Y, H:i') }}</p>
        </div>
        <div class="text-end">
            @php
                $badge = [
                    'pending' => 'bg-warning text-dark',
                    'diterima' => 'bg-success',
                    'ditolak' => 'bg-danger',
                ][$banding->status] ?? 'bg-secondary';
            @endphp
            <span class="badge {{ $badge }} px-3 py-2">Status: {{ Str::headline($banding->status) }}</span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-user-shield me-2 text-primary"></i> Informasi Pemilik
                    </h6>
                </div>
                <div class="card-body">
                    <p class="fw-semibold mb-0">{{ $banding->pemilik->name ?? '-' }}</p>
                    <p class="text-muted mb-2">{{ $banding->pemilik->email ?? '-' }}</p>
                    <p class="mb-0 small text-muted">
                        Status akun: <span class="fw-semibold text-capitalize">{{ $banding->pemilik->status ?? 'tidak diketahui' }}</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-file-signature me-2 text-secondary"></i> Alasan Banding
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $banding->alasan }}</p>
                </div>
            </div>
        </div>
    </div>

    @if ($banding->lampiran_path)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h6 class="fw-bold mb-1">
                        <i class="fa-solid fa-paperclip me-2 text-secondary"></i> Lampiran Bukti
                    </h6>
                    <p class="text-muted mb-0">Lampiran dikirim oleh pemilik sebagai bukti pendukung.</p>
                </div>
                <a
                    href="{{ route('admin.banding.lampiran', $banding) }}"
                    class="btn btn-outline-primary"
                >
                    <i class="fa-solid fa-eye me-1"></i> Lihat Lampiran
                </a>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">
                <i class="fa-solid fa-screwdriver-wrench me-2 text-success"></i> Tindak Lanjut Admin
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.banding.update', $banding) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-4">
                    <label class="form-label small text-uppercase text-muted mb-1">Status Banding</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="" disabled selected>Pilih tindakan</option>
                        <option value="diterima" @selected(old('status', $banding->status) === 'diterima')>Terima dan pulihkan akun</option>
                        <option value="ditolak" @selected(old('status', $banding->status) === 'ditolak')>Tolak banding</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label small text-uppercase text-muted mb-1">Tanggapan Admin</label>
                    <textarea
                        name="tanggapan_admin"
                        rows="4"
                        class="form-control @error('tanggapan_admin') is-invalid @enderror"
                        placeholder="Catat pertimbangan atau langkah lanjutan"
                    >{{ old('tanggapan_admin', $banding->tanggapan_admin) }}</textarea>
                    @error('tanggapan_admin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-circle-check me-1"></i> Simpan Keputusan
                    </button>
                    <a href="{{ route('admin.banding.index') }}" class="btn btn-outline-secondary">Batalkan</a>
                </div>
            </form>

            @if ($banding->penangan)
                <div class="alert alert-light border mt-4 mb-0">
                    <div class="small text-muted mb-1">Ditangani oleh</div>
                    <div class="fw-semibold">{{ $banding->penangan->name }}</div>
                    <small class="text-muted">{{ optional($banding->ditangani_pada)->translatedFormat('d F Y, H:i') }}</small>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
