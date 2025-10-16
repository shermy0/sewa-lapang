@extends('layouts.admin')

@section('title', 'Detail Laporan Penyalahgunaan')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <a href="{{ route('admin.laporan.penyalahgunaan.index') }}" class="text-decoration-none text-muted small">
                <i class="fa-solid fa-arrow-left-long me-1"></i> Kembali ke daftar
            </a>
            <h4 class="fw-bold text-dark mt-2 mb-1">
                <i class="fa-solid fa-flag text-danger me-2"></i> Detail Laporan #{{ $report->id }}
            </h4>
            <p class="text-muted mb-0">Ditambahkan pada {{ optional($report->created_at)->translatedFormat('d F Y, H:i') }}</p>
        </div>
        <div class="text-end">
            @php
                $badgeClasses = [
                    'pending' => 'bg-warning text-dark',
                    'diproses' => 'bg-info text-dark',
                    'ditutup' => 'bg-success',
                ];
            @endphp
            <span class="badge {{ $badgeClasses[$report->status] ?? 'bg-secondary' }} px-3 py-2">
                Status: {{ Str::headline($report->status) }}
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-user-circle me-2 text-primary"></i> Informasi Pelapor
                    </h6>
                </div>
                <div class="card-body">
                    <p class="fw-semibold mb-0">{{ $report->pelapor->name ?? '-' }}</p>
                    <p class="text-muted">{{ $report->pelapor->email ?? 'Tidak tersedia' }}</p>
                    @if ($report->pelapor?->no_hp)
                        <p class="mb-0">
                            <i class="fa-solid fa-phone me-2 text-muted"></i>{{ $report->pelapor->no_hp }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-user-shield me-2 text-danger"></i> Informasi Terlapor
                    </h6>
                </div>
                <div class="card-body">
                    <p class="fw-semibold mb-0">{{ $report->terlapor->name ?? '-' }}</p>
                    <p class="text-muted">{{ $report->terlapor->email ?? 'Tidak tersedia' }}</p>
                    @if ($report->terlapor?->no_hp)
                        <p class="mb-0">
                            <i class="fa-solid fa-phone me-2 text-muted"></i>{{ $report->terlapor->no_hp }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-warehouse me-2 text-secondary"></i> Terkait Lapangan
                    </h6>
                </div>
                <div class="card-body">
                    <p class="fw-semibold mb-0">{{ $report->lapangan->nama_lapangan ?? '-' }}</p>
                    <p class="text-muted">{{ $report->lapangan->lokasi ?? 'Lokasi tidak tersedia' }}</p>
                    @if ($report->lapangan)
                        <a
                            href="{{ route('lapangan.show', $report->lapangan) }}"
                            class="btn btn-sm btn-outline-secondary mt-2"
                            target="_blank"
                        >
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Lihat lapangan
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-file-lines me-2 text-info"></i> Detail Pengaduan
                    </h6>
                    @if ($report->kategori)
                        <span class="badge bg-light text-dark">{{ $report->kategori }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <p class="text-muted mb-1">Deskripsi kejadian:</p>
                    <p class="mb-0">{{ $report->deskripsi }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-screwdriver-wrench me-2 text-success"></i> Tindak Lanjut Admin
                    </h6>
                </div>
                <div class="card-body">
                    <form
                        action="{{ route('admin.laporan.penyalahgunaan.update-status', $report) }}"
                        method="POST"
                        class="d-grid gap-3"
                    >
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="form-label small text-uppercase text-muted mb-1">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected(old('status', $report->status) === $status)>
                                        {{ Str::headline($status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label small text-uppercase text-muted mb-1">Catatan Admin</label>
                            <textarea
                                name="catatan_admin"
                                rows="4"
                                class="form-control @error('catatan_admin') is-invalid @enderror"
                                placeholder="Tambahkan catatan atau hasil tindak lanjut"
                            >{{ old('catatan_admin', $report->catatan_admin) }}</textarea>
                            @error('catatan_admin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if ($report->penangan)
                            <div class="bg-light border rounded p-3">
                                <p class="small text-muted mb-1">Ditangani oleh</p>
                                <p class="fw-semibold mb-0">{{ $report->penangan->name }}</p>
                                <small class="text-muted">
                                    {{ optional($report->ditangani_pada)->translatedFormat('d F Y, H:i') ?? '-' }}
                                </small>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-success">
                                <i class="fa-solid fa-circle-check me-1"></i> Simpan Perubahan
                            </button>
                            <a
                                href="{{ route('admin.laporan.penyalahgunaan.index') }}"
                                class="btn btn-outline-secondary"
                            >
                                Batalkan
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
