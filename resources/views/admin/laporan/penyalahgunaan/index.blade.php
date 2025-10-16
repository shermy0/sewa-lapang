@extends('layouts.admin')

@section('title', 'Laporan Penyalahgunaan')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-flag text-danger me-2"></i> Laporan Penyalahgunaan
            </h4>
            <p class="text-muted mb-0">Pantau dan tindaklanjuti laporan dari komunitas penyewa maupun pemilik.</p>
        </div>
        <div class="text-end">
            <span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2">
                Total laporan: {{ number_format($reports->total()) }}
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

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-uppercase text-muted mb-1">Pencarian</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Cari laporan, pelapor, terlapor, atau lapangan"
                    >
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-uppercase text-muted mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ Str::headline($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 ms-auto">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Terapkan
                        </button>
                        <a href="{{ route('admin.laporan.penyalahgunaan.index') }}" class="btn btn-light border">
                            Atur ulang
                        </a>
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
                            <th>Pelapor</th>
                            <th>Terlapor</th>
                            <th>Lapangan</th>
                            <th>Kategori</th>
                            <th>Ringkasan</th>
                            <th>Status</th>
                            <th>Terakhir Diperbarui</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td class="fw-semibold">#{{ $report->id }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $report->pelapor->name ?? '-' }}</span>
                                        <small class="text-muted">{{ $report->pelapor->email ?? '' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $report->terlapor->name ?? '-' }}</span>
                                        <small class="text-muted">{{ $report->terlapor->email ?? '' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $report->lapangan->nama_lapangan ?? '-' }}</span>
                                        <small class="text-muted">{{ $report->lapangan->lokasi ?? '' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $report->kategori ?? 'Umum' }}</span>
                                </td>
                                <td class="text-muted">
                                    {{ Str::limit($report->deskripsi, 80) }}
                                </td>
                                <td>
                                    @php
                                        $badgeClasses = [
                                            'pending' => 'bg-warning text-dark',
                                            'diproses' => 'bg-info text-dark',
                                            'ditutup' => 'bg-success',
                                        ];
                                    @endphp
                                    <span class="badge {{ $badgeClasses[$report->status] ?? 'bg-secondary' }}">
                                        {{ Str::headline($report->status) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ optional($report->updated_at)->diffForHumans() }}
                                    </small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a
                                            href="{{ route('admin.laporan.penyalahgunaan.show', $report) }}"
                                            class="btn btn-outline-primary"
                                        >
                                            <i class="fa-solid fa-eye me-1"></i> Detail
                                        </a>
                                        <form
                                            action="{{ route('admin.laporan.penyalahgunaan.destroy', $report) }}"
                                            method="POST"
                                            onsubmit="return confirm('Hapus laporan ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-5 text-center text-muted">
                                    <i class="fa-regular fa-flag mb-2 d-block fs-3"></i>
                                    Belum ada laporan penyalahgunaan.
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
@endsection
