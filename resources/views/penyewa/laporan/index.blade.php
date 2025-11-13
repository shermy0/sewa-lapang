@extends('layouts.sidebar')

@section('title', 'Laporan Penyalahgunaan')

@php
    use Illuminate\Support\Str;
    $statusBadges = [
        'pending' => 'bg-warning text-dark',
        'diproses' => 'bg-info text-dark',
        'ditutup' => 'bg-success',
    ];
    $canReport = $lapanganOptions->isNotEmpty();
@endphp

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="text-muted mb-1">Pantau laporan yang Anda kirim ke tim SewaLap.</p>
            <h2 class="fw-bold text-dark mb-0"><i class="fa-solid fa-flag text-danger me-2"></i> Laporan Penyalahgunaan</h2>
        </div>
        <div class="text-end">
            <div class="small text-muted">Total laporan</div>
            <div class="display-6 fw-semibold text-success">{{ number_format($reports->total()) }}</div>
            <button class="btn btn-success mt-2" data-bs-toggle="modal" data-bs-target="#reportModal" @if(!$canReport) disabled @endif>
                <i class="fa-solid fa-plus me-1"></i> Buat Laporan
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> Terjadi kesalahan pada formulir laporan. Periksa kembali input Anda.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small text-uppercase text-muted mb-1">Pencarian</label>
                    <input type="text" class="form-control" name="search" placeholder="Cari deskripsi, lapangan, atau pemilik" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-uppercase text-muted mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ Str::headline($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button class="btn btn-success flex-grow-1" type="submit">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Terapkan
                        </button>
                        <a href="{{ route('penyewa.laporan.index') }}" class="btn btn-light border">Atur ulang</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Lapangan</th>
                            <th>Terlapor</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td class="fw-semibold">#{{ $report->id }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $report->lapangan?->nama_lapangan ?? '-' }}</span>
                                        <small class="text-muted">{{ $report->lapangan?->lokasi ?? 'Lokasi belum tersedia' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $report->terlapor->name ?? '-' }}</span>
                                        <small class="text-muted">{{ $report->terlapor->email ?? 'Tidak tersedia' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $report->kategori_label }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $statusBadges[$report->status] ?? 'bg-secondary' }}">
                                        {{ Str::headline($report->status) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ optional($report->created_at)->translatedFormat('d F Y, H:i') }}</small>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#detailReport{{ $report->id }}">
                                        <i class="fa-solid fa-eye me-1"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-5 text-center text-muted">
                                    <i class="fa-regular fa-flag mb-2 d-block fs-3"></i>
                                    Belum ada laporan yang dikirim.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($reports->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Buat Laporan --}}
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-flag me-2 text-danger"></i> Laporkan Penyalahgunaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('penyewa.laporan.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if (!$canReport)
                        <div class="alert alert-warning">
                            Anda belum memiliki riwayat pemesanan sehingga tidak dapat mengirim laporan saat ini.
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lapangan</label>
                        <select name="lapangan_id" class="form-select @error('lapangan_id') is-invalid @enderror" @disabled(!$canReport)>
                            <option value="">Pilih lapangan</option>
                            @foreach ($lapanganOptions as $lap)
                                <option value="{{ $lap->id }}" @selected(old('lapangan_id', $defaultLapangan) == $lap->id)>
                                    {{ $lap->nama_lapangan }}
                                </option>
                            @endforeach
                        </select>
                        @error('lapangan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kategori</label>
                        <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" @disabled(!$canReport)>
                            <option value="">Pilih kategori laporan</option>
                            @foreach ($categories as $key => $label)
                                <option value="{{ $key }}" @selected(old('kategori') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi Kejadian</label>
                        <textarea name="deskripsi" rows="5" class="form-control @error('deskripsi') is-invalid @enderror" placeholder="Ceritakan kronologi secara jelas" @disabled(!$canReport)>{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Minimal 20 karakter.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" @disabled(!$canReport)>
                        <i class="fa-solid fa-paper-plane me-1"></i> Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Detail per laporan --}}
@foreach ($reports as $report)
    <div class="modal fade" id="detailReport{{ $report->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Laporan #{{ $report->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <p class="text-muted mb-1">Status</p>
                                <span class="badge {{ $statusBadges[$report->status] ?? 'bg-secondary' }}">{{ Str::headline($report->status) }}</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="border rounded p-3 h-100">
                                <p class="text-muted mb-1">Lapangan</p>
                                <p class="mb-0 fw-semibold">{{ $report->lapangan?->nama_lapangan ?? '-' }}</p>
                                <small class="text-muted">{{ $report->lapangan?->lokasi ?? 'Lokasi tidak tersedia' }}</small>
                                @if ($report->lapangan)
                                    <div class="mt-2">
                                        <a href="{{ route('penyewa.detail', $report->lapangan->id) }}" class="btn btn-sm btn-outline-success" target="_blank">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Lihat Lapangan
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold mb-1">Kategori</h6>
                        <p class="mb-0">{{ $report->kategori_label }}</p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold mb-1">Deskripsi Kejadian</h6>
                        <p class="mb-0">{{ $report->deskripsi }}</p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold mb-1">Catatan Admin</h6>
                        <p class="mb-0">{{ $report->catatan_admin ?? 'Belum ada catatan dari admin.' }}</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <p class="text-muted mb-1">Ditangani oleh</p>
                                <p class="mb-0 fw-semibold">{{ $report->penangan->name ?? '-' }}</p>
                                <small class="text-muted">{{ optional($report->ditangani_pada)->translatedFormat('d F Y, H:i') ?? 'Belum ditangani' }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <p class="text-muted mb-1">Terakhir diperbarui</p>
                                <p class="mb-0 fw-semibold">{{ optional($report->updated_at)->translatedFormat('d F Y, H:i') }}</p>
                                <small class="text-muted">{{ optional($report->updated_at)->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Selesai</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection

@push('scripts')
@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('reportModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    });
</script>
@endif
@endpush
