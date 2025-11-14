@extends('layouts.sidebar')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h3 class="fw-bold text-dark mb-2">
                <i class="fa-solid fa-shield-halved me-2 text-primary"></i> Dashboard Admin
            </h3>
            <p class="text-muted mb-0">Pantau aktivitas platform dan kelola ekosistem SewaLap.</p>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('admin.lapangan.index') }}" class="btn btn-success shadow-sm">
                <i class="fa-solid fa-futbol me-1"></i> Kelola Lapangan
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-users-viewfinder me-1"></i> Kelola Pengguna
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-3">
                            <i class="fa-solid fa-users fs-4"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['totalUsers']) }}</h3>
                            <small class="text-muted">Total Pengguna</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-success bg-opacity-10 text-success rounded-3 p-2 me-3">
                            <i class="fa-solid fa-user-tie fs-4"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['totalPemilik']) }}</h3>
                            <small class="text-muted">Pemilik Aktif</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-info bg-opacity-10 text-info rounded-3 p-2 me-3">
                            <i class="fa-solid fa-user-group fs-4"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['totalPenyewa']) }}</h3>
                            <small class="text-muted">Total Penyewa</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-3 p-2 me-3">
                            <i class="fa-solid fa-square fs-4"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['totalLapangan']) }}</h3>
                            <small class="text-muted">Unit Lapangan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-secondary bg-opacity-10 text-secondary rounded-3 p-2 me-3">
                            <i class="fa-solid fa-calendar-check fs-4"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['totalPemesanan']) }}</h3>
                            <small class="text-muted">Total Pemesanan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-danger bg-opacity-10 text-danger rounded-3 p-2 me-3">
                            <i class="fa-solid fa-money-bill-trend-up fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">Rp {{ number_format($stats['totalPendapatan'], 0, ',', '.') }}</h4>
                            <small class="text-muted">Total Pendapatan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart & Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fa-solid fa-chart-line me-2 text-primary"></i> Tren Pemesanan 6 Bulan Terakhir
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartPemesanan"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fa-solid fa-bolt me-2 text-warning"></i> Aksi Cepat
                    </h5>
                </div>
                <div class="card-body d-grid gap-3">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-success btn-lg text-start border-2 hover-lift">
                        <i class="fa-solid fa-users-gear me-2"></i>
                        Kelola Data Pengguna
                    </a>
                    <a href="{{ route('admin.pembayaran.index') }}" class="btn btn-outline-primary btn-lg text-start border-2 hover-lift">
                        <i class="fa-solid fa-wallet me-2"></i>
                        Tinjau Laporan Pembayaran
                    </a>
                    <a
                        href="{{ route('admin.laporan.penyalahgunaan.index') }}"
                        class="btn btn-outline-danger btn-lg d-flex justify-content-between align-items-center border-2 hover-lift"
                    >
                        <span>
                            <i class="fa-solid fa-flag me-2"></i>
                            Laporan Penyalahgunaan
                        </span>
                        <span class="badge rounded-pill bg-danger">
                            {{ number_format($stats['pendingReports']) }}
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings & New Users -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">
                        <i class="fa-solid fa-receipt me-2 text-success"></i> Pemesanan Terbaru
                    </h5>
                    <a href="#" class="text-decoration-none fw-semibold">Lihat semua →</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-semibold">Penyewa</th>
                                    <th class="fw-semibold">Lapangan</th>
                                    <th class="fw-semibold">Status</th>
                                    <th class="fw-semibold">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentPemesanan as $pemesanan)
                                    <tr>
                                        <td class="fw-medium">{{ $pemesanan->penyewa->name ?? '-' }}</td>
                                        <td class="text-muted">{{ $pemesanan->lapangan->nama_lapangan ?? '-' }}</td>
                                        <td>
                                            <span class="badge rounded-pill text-uppercase
                                                @if ($pemesanan->status === 'selesai') bg-success
                                                @elseif ($pemesanan->status === 'dibayar') bg-primary
                                                @elseif ($pemesanan->status === 'batal') bg-danger
                                                @else bg-warning text-dark @endif">
                                                {{ $pemesanan->status }}
                                            </span>
                                        </td>
                                        <td class="text-muted">{{ optional($pemesanan->created_at)->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="fa-solid fa-inbox fs-1 mb-3 d-block opacity-50"></i>
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
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">
                        <i class="fa-solid fa-user-plus me-2 text-info"></i> Pengguna Baru
                    </h5>
                    <a href="#" class="text-decoration-none fw-semibold">Kelola pengguna →</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-semibold">Nama</th>
                                    <th class="fw-semibold">Email</th>
                                    <th class="fw-semibold">Role</th>
                                    <th class="fw-semibold">Bergabung</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestUsers as $user)
                                    <tr>
                                        <td class="fw-medium">{{ $user->name }}</td>
                                        <td class="text-muted">{{ $user->email }}</td>
                                        <td><span class="badge rounded-pill bg-secondary text-uppercase">{{ $user->role }}</span></td>
                                        <td class="text-muted">{{ optional($user->created_at)->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="fa-solid fa-user-slash fs-1 mb-3 d-block opacity-50"></i>
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

    <!-- Latest Fields -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">
                        <i class="fa-solid fa-warehouse me-2 text-secondary"></i> Lapangan Terbaru
                    </h5>
                    <a href="{{ route('lapangan.index') }}" class="text-decoration-none fw-semibold">Kelola lapangan →</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-semibold">Nama</th>
                                    <th class="fw-semibold">Pemilik</th>
                                    <th class="fw-semibold">Kategori</th>
                                    <th class="fw-semibold">Status</th>
                                    <th class="fw-semibold">Ditambahkan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestLapangan as $lapangan)
                                    <tr>
                                        <td class="fw-medium">{{ $lapangan->nama_lapangan }}</td>
                                        <td class="text-muted">{{ $lapangan->pemilik?->name ?? '-' }}</td>
                                        <td class="text-muted">{{ $lapangan->kategori ?? '-' }}</td>
                                        <td>
                                            <span class="badge rounded-pill bg-secondary text-uppercase">{{ $lapangan->status }}</span>
                                        </td>
                                        <td class="text-muted">{{ optional($lapangan->created_at)->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="fa-solid fa-store-slash fs-1 mb-3 d-block opacity-50"></i>
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

<style>
    .hover-lift {
        transition: all 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .icon-box {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card {
        transition: all 0.3s ease;
    }
    .table tbody tr {
        transition: background-color 0.2s ease;
    }
    .table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    .btn {
        transition: all 0.3s ease;
    }
    .btn:hover {
        transform: translateY(-2px);
    }
</style>

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
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#0d6efd',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7,
                    borderWidth: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                },
            },
        });
    }
</script>
@endsection
