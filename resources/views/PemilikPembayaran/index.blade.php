@extends('layouts.sidebar')

@section('title', 'Kelola Pembayaran')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row align-items-center mb-4">
        <div class="col-lg-8">
            <h2 class="fw-bold text-dark mb-2">
                <i class="fa-solid fa-credit-card text-success me-2"></i> Kelola Pembayaran
            </h2>
            <p class="text-muted mb-0">Kelola dan pantau seluruh transaksi penyewa lapangan</p>
        </div>
        <div class="col-lg-4 text-end">
            <a href="{{ route('pemilik.pembayaran.index') }}" class="btn btn-outline-success">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
            </a>
        </div>
    </div>

    {{-- Statistik Ringkas --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Transaksi</h6>
                    <h4 class="fw-bold">{{ $stat['total'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Berhasil</h6>
                    <h4 class="fw-bold text-success">{{ $stat['berhasil'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Pending</h6>
                    <h4 class="fw-bold text-warning">{{ $stat['pending'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Gagal</h6>
                    <h4 class="fw-bold text-danger">{{ $stat['gagal'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label>Status:</label>
                    <select name="status" class="form-select">
                        <option value="semua" {{ $status == 'semua' ? 'selected' : '' }}>Semua</option>
                        <option value="berhasil" {{ $status == 'berhasil' ? 'selected' : '' }}>Berhasil</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="gagal" {{ $status == 'gagal' ? 'selected' : '' }}>Gagal</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Cari (ID/Metode):</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari pembayaran...">
                </div>
                <div class="col-md-2">
                    <label>Dari:</label>
                    <input type="date" name="tanggal_mulai" value="{{ $tanggal_mulai }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label>Sampai:</label>
                    <input type="date" name="tanggal_selesai" value="{{ $tanggal_selesai }}" class="form-control">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-success w-100"><i class="fa-solid fa-filter"></i></button>
                    <a href="#" class="btn btn-outline-primary w-100"><i class="fa-solid fa-file-export"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Pembayaran --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="fa-solid fa-list me-2 text-success"></i> Data Pembayaran</h6>
            <small class="text-muted">Update terakhir: {{ date('d F Y') }}</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Pemesanan ID</th>
                            <th>Metode</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Order ID</th>
                            <th>Tanggal Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pembayaran as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->pemesanan_id }}</td>
                                <td>{{ $item->metode }}</td>
                                <td>Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                <td>
                                    @if ($item->status == 'berhasil')
                                        <span class="badge bg-success">Berhasil</span>
                                    @elseif ($item->status == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @else
                                        <span class="badge bg-danger">Gagal</span>
                                    @endif
                                </td>
                                <td>{{ $item->order_id }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_pembayaran)->translatedFormat('d F Y') }}</td>
                                <td>
                                    <a href="{{ $item->payment_url }}" target="_blank" class="btn btn-sm btn-outline-success">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fa-regular fa-circle-xmark me-2"></i> Tidak ada data pembayaran ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
