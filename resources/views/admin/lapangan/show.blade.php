@extends('layouts.sidebar')

@section('title', 'Detail Lapangan')

@php
    use Illuminate\Support\Str;

    $badgeStatuses = [
        'pending' => 'bg-warning text-dark',
        'diproses' => 'bg-info text-dark',
        'ditutup' => 'bg-success',
    ];
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <a href="{{ route('admin.lapangan.index') }}" class="text-decoration-none text-muted small">
                <i class="fa-solid fa-arrow-left-long me-1"></i> Kembali ke daftar lapangan
            </a>
            <h3 class="fw-bold text-dark mt-2 mb-1">{{ $lapangan->nama_lapangan }}</h3>
            <p class="text-muted mb-0">
                <i class="fa-solid fa-location-dot text-success me-2"></i>{{ $lapangan->lokasi ?? 'Lokasi belum diisi' }}
            </p>
        </div>
        <div class="text-end">
            <span class="badge bg-success-subtle text-success px-3 py-2">
                Total Section: {{ $lapangan->sections->count() }}
            </span>
            <span class="badge bg-primary-subtle text-primary px-3 py-2">
                Total Jadwal: {{ $totalJadwal }}
            </span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                        <div>
                            <p class="text-muted mb-1">Kategori</p>
                            <h5 class="fw-bold mb-3">{{ $lapangan->kategori ?? 'Tidak dikategorikan' }}</h5>
                            <p class="text-muted mb-0">{{ $lapangan->deskripsi ?: 'Belum ada deskripsi lapangan.' }}</p>
                        </div>
                        <div class="text-end">
                            <p class="text-muted mb-1">Tarif Dasar</p>
                            @if ($lapangan->harga_sewa)
                                <h4 class="text-success fw-bold">Rp {{ number_format($lapangan->harga_sewa, 0, ',', '.') }}</h4>
                                <small class="text-muted">per jam</small>
                            @else
                                <h6 class="text-muted mb-0">Belum diatur</h6>
                            @endif
                        </div>
                    </div>
                </div>
                @if ($lapangan->foto_urls)
                    <div class="card-footer bg-white border-0">
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach ($lapangan->foto_urls as $foto)
                                <img src="{{ $foto }}" alt="Foto lapangan" class="rounded shadow-sm" style="width: 120px; height: 80px; object-fit: cover;">
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <p class="text-muted mb-1">Pemilik</p>
                    <h5 class="fw-semibold mb-0">{{ $lapangan->pemilik?->name ?? 'Tidak diketahui' }}</h5>
                    <p class="text-muted mb-0">{{ $lapangan->pemilik?->email ?? '-' }}</p>
                    @if ($lapangan->pemilik?->no_hp)
                        <small class="text-muted"><i class="fa-solid fa-phone me-1"></i>{{ $lapangan->pemilik->no_hp }}</small>
                    @endif
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Statistik</p>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Section</span>
                        <strong>{{ $lapangan->sections->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Jadwal</span>
                        <strong>{{ $totalJadwal }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Jumlah Laporan</span>
                        <strong>{{ $lapangan->laporanPenyalahgunaan->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-layer-group text-primary me-2"></i> Section & Jadwal
                    </h6>
                    <span class="badge bg-light text-dark">{{ $lapangan->sections->count() }} Section</span>
                </div>
                <div class="card-body">
                    @forelse ($lapangan->sections as $section)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <h6 class="fw-bold mb-1">{{ $section->nama_section }}</h6>
                                    <p class="text-muted mb-0">{{ $section->deskripsi ?: 'Tidak ada deskripsi.' }}</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $section->jadwal->count() }} Jadwal
                                    </span>
                                    @if ($section->harga_per_jam)
                                        <p class="mb-0 text-success fw-semibold">
                                            Rp {{ number_format($section->harga_per_jam, 0, ',', '.') }}/jam
                                        </p>
                                    @endif
                                </div>
                            </div>
                            @if ($section->jadwal->isNotEmpty())
                                <div class="mt-3">
                                    <small class="text-muted d-block mb-1">Jadwal terdaftar</small>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($section->jadwal->take(6) as $jadwal)
                                            <span class="badge bg-light text-dark border">
                                                {{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d M') }},
                                                {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                            </span>
                                        @endforeach
                                        @if ($section->jadwal->count() > 6)
                                            <span class="badge bg-light text-muted border">
                                                +{{ $section->jadwal->count() - 6 }} lainnya
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Belum ada section pada lapangan ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-flag text-danger me-2"></i> Laporan Terkait
                    </h6>
                    <span class="badge bg-danger-subtle text-danger">
                        {{ $lapangan->laporanPenyalahgunaan->count() }} laporan
                    </span>
                </div>
                <div class="card-body">
                    @if ($recentReports->isEmpty())
                        <p class="text-muted mb-0">Belum ada laporan yang terkait dengan lapangan ini.</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($recentReports as $report)
                                <a
                                    href="{{ route('admin.laporan.penyalahgunaan.show', $report) }}"
                                    class="list-group-item list-group-item-action border-0 px-0"
                                >
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-1 fw-semibold">#{{ $report->id }} — {{ $report->kategori_label }}</p>
                                            <small class="text-muted">
                                                Pelapor: {{ $report->pelapor->name ?? '-' }}
                                                • {{ optional($report->created_at)->diffForHumans() }}
                                            </small>
                                        </div>
                                        <span class="badge {{ $badgeStatuses[$report->status] ?? 'bg-secondary' }}">
                                            {{ Str::headline($report->status) }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        @if ($lapangan->laporanPenyalahgunaan->count() > $recentReports->count())
                            <p class="text-end mt-3 mb-0">
                                <a href="{{ route('admin.laporan.penyalahgunaan.index', ['search' => $lapangan->nama_lapangan]) }}"
                                   class="text-decoration-none">
                                    Lihat semua laporan →
                                </a>
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
