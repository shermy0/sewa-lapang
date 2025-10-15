@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-shield-halved me-2 text-primary"></i> Dashboard Admin
            </h4>
            <p class="text-muted mb-0">Pantau aktivitas platform dan kelola ekosistem SewaLap.</p>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('admin.lapangan.index') }}" class="btn btn-outline-success">
                <i class="fa-solid fa-futbol me-1"></i> Kelola Lapangan
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-users-viewfinder me-1"></i> Moderasi Pengguna
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="badge bg-primary bg-opacity-10 text-primary mb-2">
                        <i class="fa-solid fa-users me-1"></i> Pengguna
                    </span>
                    <h5 class="fw-bold mb-0">{{ number_format($stats['totalUsers']) }}</h5>
                    <small class="text-muted">Total akun terdaftar</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="badge bg-success bg-opacity-10 text-success mb-2">
                        <i class="fa-solid fa-user-tie me-1"></i> Pemilik
                    </span>
                    <h5 class="fw-bold mb-0">{{ number_format($stats['totalPemilik']) }}</h5>
                    <small class="text-muted">Pemilik lapangan aktif</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="badge bg-info bg-opacity-10 text-info mb-2">
                        <i class="fa-solid fa-user-group me-1"></i> Penyewa
                    </span>
                    <h5 class="fw-bold mb-0">{{ number_format($stats['totalPenyewa']) }}</h5>
                    <small class="text-muted">Pengguna penyewa terdaftar</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="badge bg-warning bg-opacity-10 text-warning mb-2">
                        <i class="fa-solid fa-square me-1"></i> Lapangan
                    </span>
                    <h5 class="fw-bold mb-0">{{ number_format($stats['totalLapangan']) }}</h5>
                    <small class="text-muted">Unit lapangan aktif</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary mb-2">
                        <i class="fa-solid fa-calendar-check me-1"></i> Pemesanan
                    </span>
                    <h5 class="fw-bold mb-0">{{ number_format($stats['totalPemesanan']) }}</h5>
                    <small class="text-muted">Total transaksi pemesanan</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="badge bg-danger bg-opacity-10 text-danger mb-2">
                        <i class="fa-solid fa-money-bill-trend-up me-1"></i> Pendapatan
                    </span>
                    <h5 class="fw-bold mb-0">Rp {{ number_format($stats['totalPendapatan'], 0, ',', '.') }}</h5>
                    <small class="text-muted">Pembayaran berhasil</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5 g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-chart-line me-2 text-primary"></i> Tren Pemesanan 6 Bulan Terakhir
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="chartPemesanan"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-bolt me-2 text-warning"></i> Aksi Cepat
                    </h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light border text-start">
                        <i class="fa-solid fa-user-check me-2 text-success"></i>
                        Verifikasi Pemilik Baru
                    </a>
                    <a href="{{ route('admin.pembayaran.index') }}" class="btn btn-light border text-start">
                        <i class="fa-solid fa-wallet me-2 text-primary"></i>
                        Tinjau Laporan Pembayaran
                    </a>
                    <a href="#" class="btn btn-light border text-start">
                        <i class="fa-solid fa-flag me-2 text-danger"></i>
                        Laporan Penyalahgunaan
                    </a>
                    <a href="#" class="btn btn-light border text-start">
                        <i class="fa-solid fa-pen-to-square me-2 text-secondary"></i>
                        Kelola Konten & Banner
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4 g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-receipt me-2 text-success"></i> Pemesanan Terbaru
                    </h6>
                    <a href="#" class="text-decoration-none small">Lihat semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Penyewa</th>
                                    <th>Lapangan</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentPemesanan as $pemesanan)
                                    <tr>
                                        <td>{{ $pemesanan->penyewa->name ?? '-' }}</td>
                                        <td>{{ $pemesanan->lapangan->nama_lapangan ?? '-' }}</td>
                                        <td>
                                            <span class="badge text-uppercase
                                                @if ($pemesanan->status === 'selesai') bg-success
                                                @elseif ($pemesanan->status === 'dibayar') bg-primary
                                                @elseif ($pemesanan->status === 'batal') bg-danger
                                                @else bg-warning text-dark @endif">
                                                {{ $pemesanan->status }}
                                            </span>
                                        </td>
                                        <td>{{ optional($pemesanan->created_at)->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            Belum ada data pemesanan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-user-plus me-2 text-info"></i> Pengguna Baru
                    </h6>
                    <a href="#" class="text-decoration-none small">Kelola pengguna</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Bergabung</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestUsers as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td><span class="badge bg-light text-dark text-uppercase">{{ $user->role }}</span></td>
                                        <td>{{ optional($user->created_at)->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            Belum ada pengguna baru.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-warehouse me-2 text-secondary"></i> Lapangan Terbaru
                    </h6>
                    <a href="{{ route('lapangan.index') }}" class="text-decoration-none small">Kelola lapangan</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Pemilik</th>
                                    <th>Kategori</th>
                                    <th>Status</th>
                                    <th>Ditambahkan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestLapangan as $lapangan)
                                    <tr>
                                        <td>{{ $lapangan->nama_lapangan }}</td>
                                        <td>{{ $lapangan->pemilik?->name ?? '-' }}</td>
                                        <td>{{ $lapangan->kategori ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark text-uppercase">{{ $lapangan->status }}</span>
                                        </td>
                                        <td>{{ optional($lapangan->created_at)->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Belum ada data lapangan.
                                        </td>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartCtx = document.getElementById('chartPemesanan');

    if (chartCtx) {
        const labels = @json($monthlyLabels);
        const dataPoints = @json($monthlyPemesanan);

        new Chart(chartCtx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Pemesanan',
                    data: dataPoints,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.15)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#0d6efd',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    },
                    x: {
                        grid: { display: false },
                    },
                },
            },
        });
    }
</script>
@endsection
