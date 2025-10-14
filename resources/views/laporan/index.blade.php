@extends('layouts.sidebar')

@section('title', 'Laporan')

@section('content')
    <div class="container-fluid py-4">
        {{-- Judul halaman --}}
        <div class="row align-items-center mb-4">
            <div class="col-lg-8">
                <h2 class="fw-bold text-dark mb-2">
                    <i class="fa-solid fa-file-invoice me-2 text-success"></i> Laporan
                </h2>
                <p class="text-muted mb-0">Ringkasan dan data laporan</p>
            </div>
        </div>

        {{-- Form Filter + Export --}}
        <div class="row mb-4">
            <div class="col">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form method="GET" class="d-flex flex-wrap gap-3 align-items-end justify-content-between">
                            <div class="d-flex flex-wrap gap-3">
                                <div>
                                    <label>Dari:</label>
                                    <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                                        class="form-control">
                                </div>

                                <div>
                                    <label>Sampai:</label>
                                    <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"
                                        class="form-control">
                                </div>

                                <div>
                                    <label>Status:</label>
                                    <select name="status" class="form-control">
                                        <option value="">Semua</option>
                                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>
                                            Selesai</option>
                                        <option value="dibatalkan"
                                            {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                            Pending</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('laporan.excel') }}" class="btn btn-success">
                                    <i class="fa-solid fa-file-excel me-1"></i> Export Excel
                                </a>
                                <a href="{{ route('laporan.pdf') }}" class="btn btn-danger">
                                    <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
                                </a>
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Laporan --}}
        <div class="row">
            <div class="col">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">
                            <i class="fa-solid fa-list me-2 text-success"></i> Data Laporan
                        </h6>
                        <small class="text-muted">Tahun {{ date('Y') }}</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal</th>
                                        <th>User</th>
                                        <th>Status</th>
                                        <th>Total Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($laporan ?? [] as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->tanggal_laporan ?? '-' }}</td>
                                            <td>{{ $item->user->name ?? '-' }}</td>
                                            <td>{{ ucfirst($item->status ?? '-') }}</td>
                                            <td>Rp {{ number_format($item->total_harga ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-start ps-3">Belum ada data laporan</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
