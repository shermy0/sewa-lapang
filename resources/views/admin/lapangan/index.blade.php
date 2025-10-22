@extends('layouts.admin')

@section('title', 'Kelola Lapangan')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-warehouse me-2 text-success"></i> Manajemen Lapangan
            </h4>
            <p class="text-muted mb-0">Pantau daftar lapangan dari seluruh pemilik.</p>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form class="row g-2 align-items-center mb-3" method="GET">
                <div class="col-xl-4 col-lg-5 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Cari nama lapangan atau lokasi">
                    </div>
                </div>
                <div class="col-lg-3 col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-filter me-1"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.lapangan.index') }}" class="btn btn-light border">
                        <i class="fa-solid fa-rotate me-1"></i> Reset
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Lapangan</th>
                            <th>Pemilik</th>
                            <th>Kategori</th>
                            <th>Harga/Jam</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lapangan as $item)
                            <tr>
                                <td class="fw-semibold">{{ $item->nama_lapangan }}</td>
                                <td>{{ $item->pemilik?->name ?? '-' }}</td>
                                <td>{{ $item->kategori ?? '-' }}</td>
                                <td>Rp {{ number_format($item->harga_per_jam ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <i class="fa-solid fa-star text-warning me-1"></i>{{ number_format($item->rating ?? 0, 1) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge text-uppercase
                                        @if ($item->status === 'promo') bg-info
                                        @elseif ($item->status === 'standard') bg-secondary
                                        @elseif ($item->status === 'nonaktif') bg-danger
                                        @else bg-warning text-dark @endif">
                                        {{ $item->status }}
                                    </span>
                                </td>
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
                                                        $foto = $item->foto_urls[0] ?? 'https://via.placeholder.com/640x360?text=Lapangan';
                                                    @endphp
                                                    <img src="{{ $foto }}" alt="Foto Lapangan" class="img-fluid rounded shadow-sm">
                                                </div>
                                                <div class="col-md-7">
                                                    <h5 class="fw-bold">{{ $item->nama_lapangan }}</h5>
                                                    <p class="text-muted mb-2"><i class="fa-solid fa-location-dot me-1"></i>{{ $item->lokasi }}</p>
                                                    <p class="mb-3">{{ $item->deskripsi ?: 'Belum ada deskripsi yang diisi.' }}</p>
                                                    <div class="d-flex gap-3">
                                                        <div>
                                                            <small class="text-muted d-block">Kategori</small>
                                                            <span class="fw-semibold">{{ $item->kategori }}</span>
                                                        </div>
                                                        <div>
                                                            <small class="text-muted d-block">Harga / Jam</small>
                                                            <span class="fw-semibold">Rp {{ number_format($item->harga_per_jam ?? 0, 0, ',', '.') }}</span>
                                                        </div>
                                                        <div>
                                                            <small class="text-muted d-block">Rating</small>
                                                            <span class="fw-semibold"><i class="fa-solid fa-star text-warning me-1"></i>{{ number_format($item->rating ?? 0, 1) }}</span>
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
                                <td colspan="7" class="text-center text-muted py-4">Belum ada data lapangan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $lapangan->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
