@extends('layouts.admin')

@section('title', 'Kelola Pemesanan')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-calendar-check me-2 text-primary"></i> Manajemen Pemesanan
            </h4>
            <p class="text-muted mb-0">Pantau dan kelola status pemesanan lapangan.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form class="row g-2 align-items-center mb-3" method="GET">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                        <option value="dibayar" @selected(request('status') === 'dibayar')>Dibayar</option>
                        <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                        <option value="batal" @selected(request('status') === 'batal')>Batal</option>
                    </select>
                </div>
                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-filter me-1"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.pemesanan.index') }}" class="btn btn-light border">
                        <i class="fa-solid fa-rotate me-1"></i> Reset
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Penyewa</th>
                            <th>Lapangan</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pemesanan as $item)
                            <tr>
                                <td>{{ $item->penyewa->name ?? '-' }}</td>
                                <td>{{ $item->lapangan->nama_lapangan ?? '-' }}</td>
                                <td>
                                    <span class="badge text-uppercase
                                        @if ($item->status === 'selesai') bg-success
                                        @elseif ($item->status === 'dibayar') bg-primary
                                        @elseif ($item->status === 'batal') bg-danger
                                        @else bg-warning text-dark @endif">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td>{{ optional($item->created_at)->format('d M Y H:i') }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#modalUpdatePemesanan{{ $item->id }}">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Update
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalUpdatePemesanan{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Perbarui Status Pemesanan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.pemesanan.update', $item) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="menunggu" @selected($item->status === 'menunggu')>Menunggu</option>
                                                        <option value="dibayar" @selected($item->status === 'dibayar')>Dibayar</option>
                                                        <option value="selesai" @selected($item->status === 'selesai')>Selesai</option>
                                                        <option value="batal" @selected($item->status === 'batal')>Batal</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada data pemesanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $pemesanan->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
