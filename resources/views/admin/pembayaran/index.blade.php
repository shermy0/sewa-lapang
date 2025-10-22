@extends('layouts.admin')

@section('title', 'Kelola Pembayaran')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-money-bill-wave me-2 text-success"></i> Manajemen Pembayaran
            </h4>
            <p class="text-muted mb-0">Monitoring transaksi pembayaran yang masuk ke sistem.</p>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form class="row g-2 align-items-center mb-3" method="GET">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="berhasil" @selected(request('status') === 'berhasil')>Berhasil</option>
                        <option value="gagal" @selected(request('status') === 'gagal')>Gagal</option>
                    </select>
                </div>
                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-filter me-1"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.pembayaran.index') }}" class="btn btn-light border">
                        <i class="fa-solid fa-rotate me-1"></i> Reset
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Penyewa</th>
                            <th>Lapangan</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Metode</th>
                            <th>Dibayar</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pembayaran as $item)
                            <tr>
                                <td>{{ $item->order_id }}</td>
                                <td>{{ $item->pemesanan->penyewa->name ?? '-' }}</td>
                                <td>{{ $item->pemesanan->lapangan->nama_lapangan ?? '-' }}</td>
                                <td>Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge text-uppercase
                                        @if ($item->status === 'berhasil') bg-success
                                        @elseif ($item->status === 'pending') bg-warning text-dark
                                        @else bg-danger @endif">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td>{{ strtoupper($item->metode) }}</td>
                                <td>{{ optional($item->tanggal_pembayaran)->format('d M Y H:i') ?? '-' }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#modalUpdatePembayaran{{ $item->id }}">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Update
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalUpdatePembayaran{{ $item->id }}" tabindex="-1"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Perbarui Status Pembayaran</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.pembayaran.update', $item) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="pending" @selected($item->status === 'pending')>Pending</option>
                                                        <option value="berhasil" @selected($item->status === 'berhasil')>Berhasil</option>
                                                        <option value="gagal" @selected($item->status === 'gagal')>Gagal</option>
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
                                <td colspan="8" class="text-center text-muted py-4">Belum ada data pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $pembayaran->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
