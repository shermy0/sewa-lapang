@extends('layouts.admin')

@section('title', 'Kelola Lapangan')

@section('content')
<div class="container-fluid py-4">
    <div class="admin-page-header">
        <div>
            <p class="eyebrow text-uppercase text-muted mb-1">Kelola Konten</p>
            <h1 class="fw-bold">
                <i class="fa-solid fa-warehouse me-2 text-success"></i> Manajemen Lapangan
            </h1>
            <p>Pantau daftar lapangan dari seluruh pemilik dan tinjau detailnya.</p>
        </div>
        <span class="admin-badge-pill">
            <i class="fa-solid fa-database"></i>
            {{ $lapangan->total() }} data
        </span>
    </div>

    <form class="admin-toolbar" method="GET">
        <div class="flex-grow-1">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                    placeholder="Cari nama lapangan, pemilik, atau lokasi">
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-filter me-1"></i> Cari
            </button>
            <a href="{{ route('admin.lapangan.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-rotate me-1"></i> Reset
            </a>
        </div>
    </form>

    <div class="admin-table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama Lapangan</th>
                        <th>Pemilik</th>
                        <th>Kategori</th>
                        <th>Harga/Jam</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lapangan as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->nama_lapangan }}</div>
                                <small class="text-muted">{{ $item->lokasi }}</small>
                            </td>
                            <td>{{ $item->pemilik?->name ?? '-' }}</td>
                            <td>
                                <span class="admin-chip">
                                    <i class="fa-solid fa-tag"></i> {{ $item->kategori ?? 'Tidak ada' }}
                                </span>
                            </td>
                            @php
                                $harga = $item->harga_per_jam ?? $item->harga_sewa ?? 0;
                            @endphp
                            <td>Rp {{ number_format($harga, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#modalPreviewLapangan{{ $item->id }}">
                                    <i class="fa-solid fa-eye me-1"></i> Tinjauan
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="modalPreviewLapangan{{ $item->id }}" tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Tinjauan Cepat Lapangan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-5">
                                                @php
                                                    $foto = $item->foto_utama ?? 'https://via.placeholder.com/640x360?text=Lapangan';
                                                @endphp
                                                <img src="{{ $foto }}" alt="Foto Lapangan" class="img-fluid rounded shadow-sm">
                                            </div>
                                            <div class="col-md-7">
                                                <h5 class="fw-bold">{{ $item->nama_lapangan }}</h5>
                                                <p class="text-muted mb-2"><i class="fa-solid fa-location-dot me-1"></i>{{ $item->lokasi }}</p>
                                                <p class="mb-3">{{ $item->deskripsi ?: 'Belum ada deskripsi yang diisi.' }}</p>
                                                <div class="d-flex gap-3 flex-wrap">
                                                    <div>
                                                        <small class="text-muted d-block">Kategori</small>
                                                        <span class="fw-semibold">{{ $item->kategori ?? '-' }}</span>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Harga / Jam</small>
                                                        <span class="fw-semibold">Rp {{ number_format($harga, 0, ',', '.') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="admin-empty-state">
                                    <i class="fa-solid fa-box-open"></i>
                                    <p class="mb-0">Belum ada data lapangan tersedia.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top">
            {{ $lapangan->links() }}
        </div>
    </div>
</div>
@endsection
