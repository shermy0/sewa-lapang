@extends('layouts.sidebar')

@section('title', 'Dashboard Pemilik')

@section('content')
@php
    $statItems = [
        [
            'label' => 'Lapangan Aktif',
            'value' => number_format($stats['totalLapangan']),
            'icon' => 'fa-solid fa-futbol',
            'class' => 'stat-card--green',
        ],
        [
            'label' => 'Total Pemesanan',
            'value' => number_format($stats['totalPemesanan']),
            'icon' => 'fa-solid fa-calendar-check',
            'class' => 'stat-card--blue',
        ],
        [
            'label' => 'Pendapatan Berhasil',
            'value' => 'Rp ' . number_format($stats['totalPendapatan'], 0, ',', '.'),
            'icon' => 'fa-solid fa-wallet',
            'class' => 'stat-card--orange',
        ],
        [
            'label' => 'Total Penyewa',
            'value' => number_format($stats['totalPengguna']),
            'icon' => 'fa-solid fa-users',
            'class' => 'stat-card--purple',
        ],
    ];

    $statusColors = [
        'menunggu' => 'warning',
        'dibayar' => 'info',
        'selesai' => 'success',
        'batal' => 'danger',
    ];
@endphp

<style>
:root {
  --green: #41A67E;
  --green-light: #E6F5EF;
  --red-light: #FCEAEA;
  --gray-light: #F8F9FA;
  --text-dark: #2E3A35;
  --blue: #2C7BE5;
  --purple: #6259CA;
  --orange: #FF9F43;
}

.dashboard-wrapper {
  color: var(--text-dark);
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 2rem;
}

.page-header h2 {
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin: 0;
}

.page-header h2 i {
  font-size: 1.5rem;
  color: var(--green);
}

.page-header p {
  margin: 0;
  color: #6c757d;
}

.stat-card {
  border-radius: 18px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  color: #fff;
  box-shadow: 0 12px 30px rgba(65, 166, 126, 0.12);
}

.stat-card .icon {
  width: 54px;
  height: 54px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
  background: rgba(255, 255, 255, 0.15);
}

.stat-card h6 {
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.75rem;
  margin-bottom: 0.35rem;
  opacity: 0.85;
}

.stat-card h4 {
  font-size: 1.6rem;
  margin: 0;
  font-weight: 700;
}

.stat-card--green {
  background: linear-gradient(135deg, var(--green), #2E8E69);
}

.stat-card--blue {
  background: linear-gradient(135deg, var(--blue), #1E5BBB);
}

.stat-card--orange {
  background: linear-gradient(135deg, var(--orange), #F57C00);
}

.stat-card--purple {
  background: linear-gradient(135deg, var(--purple), #4A3BB3);
}

.card-soft {
  border-radius: 18px;
  border: none;
  box-shadow: 0 12px 30px rgba(34, 34, 34, 0.08);
  overflow: hidden;
}

.card-soft .card-header {
  background: #fff;
  border: none;
  padding: 1.25rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.card-soft .card-header h5 {
  margin: 0;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: var(--text-dark);
}

.card-soft .card-header h5 i {
  color: var(--green);
}

.recent-list .recent-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 0;
  border-bottom: 1px solid var(--gray-light);
}

.recent-list .recent-item:last-child {
  border-bottom: none;
}

.recent-item .info {
  flex: 1;
}

.recent-item .info h6 {
  margin-bottom: 0.35rem;
  font-weight: 600;
  color: var(--text-dark);
}

.recent-item .info span {
  font-size: 0.85rem;
  display: block;
  color: #6c757d;
}

.badge-status {
  padding: 0.55rem 0.85rem;
  border-radius: 12px;
  font-weight: 600;
  text-transform: capitalize;
  font-size: 0.8rem;
}

.badge-status.bg-warning {
  background: rgba(255, 193, 7, 0.2) !important;
  color: #b38406;
}

.badge-status.bg-info {
  background: rgba(13, 202, 240, 0.2) !important;
  color: #0aa3bd;
}

.badge-status.bg-success {
  background: rgba(25, 135, 84, 0.18) !important;
  color: #1f6f47;
}

.badge-status.bg-danger {
  background: rgba(220, 53, 69, 0.18) !important;
  color: #a52d3c;
}

.table-modern {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 12px;
}

.table-modern thead th {
  font-size: 0.78rem;
  text-transform: uppercase;
  color: #6c757d;
  font-weight: 600;
  border: none;
  padding: 0.5rem 1.25rem;
}

.table-modern tbody tr {
  background: #fff;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
  border-radius: 16px;
}

.table-modern tbody td {
  border: none;
  padding: 1.1rem 1.25rem;
  vertical-align: middle;
}

.table-modern tbody tr td:first-child {
  border-top-left-radius: 16px;
  border-bottom-left-radius: 16px;
}

.table-modern tbody tr td:last-child {
  border-top-right-radius: 16px;
  border-bottom-right-radius: 16px;
  text-align: right;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: #6c757d;
}

.empty-state i {
  font-size: 3rem;
  color: var(--green);
  opacity: 0.35;
  margin-bottom: 1rem;
}
</style>

<div class="dashboard-wrapper container py-4">
  <div class="page-header">
      <div>
          <h2><i class="fa-solid fa-chart-pie"></i> Dashboard Pemilik</h2>
          <p>Selamat datang kembali, {{ $user->name }}! Kelola performa lapanganmu dari satu tempat.</p>
      </div>
  </div>

  <div class="row g-3 mb-4">
      @foreach ($statItems as $stat)
          <div class="col-md-3 col-sm-6">
              <div class="stat-card {{ $stat['class'] }}">
                  <div class="icon">
                      <i class="{{ $stat['icon'] }}"></i>
                  </div>
                  <div>
                      <h6>{{ $stat['label'] }}</h6>
                      <h4>{{ $stat['value'] }}</h4>
                  </div>
              </div>
          </div>
      @endforeach
  </div>

  <div class="row g-4">
      <div class="col-lg-7">
          <div class="card card-soft">
              <div class="card-header">
                  <h5><i class="fa-solid fa-chart-line"></i> Statistik Pemesanan & Pendapatan</h5>
              </div>
              <div class="card-body">
                  <canvas id="pemesananChart" height="260"></canvas>
              </div>
          </div>
      </div>
      <div class="col-lg-5">
          <div class="card card-soft h-100">
              <div class="card-header">
                  <h5><i class="fa-solid fa-clock-rotate-left"></i> Pemesanan Terbaru</h5>
              </div>
              <div class="card-body recent-list">
                  @forelse ($recentPemesanan as $item)
                      <div class="recent-item">
                          <div class="info">
                              <h6>{{ $item->lapangan->nama_lapangan ?? 'Lapangan tidak tersedia' }}</h6>
                              <span>
                                  <i class="fa-solid fa-user me-1"></i>
                                  {{ $item->penyewa->name ?? 'Penyewa tidak tersedia' }}
                              </span>
                              <span>
                                  <i class="fa-solid fa-calendar-day me-1"></i>
                                  {{ $item->jadwal?->tanggal?->translatedFormat('d F Y') ?? '-' }}
                                  · {{ $item->jadwal?->jam_mulai }} - {{ $item->jadwal?->jam_selesai }}
                              </span>
                          </div>
                          <span class="badge badge-status bg-{{ $statusColors[$item->status] ?? 'secondary' }}">
                              {{ ucfirst($item->status) }}
                          </span>
                      </div>
                  @empty
                      <div class="empty-state">
                          <i class="fa-regular fa-calendar-xmark"></i>
                          <p>Belum ada pemesanan terbaru.</p>
                      </div>
                  @endforelse
              </div>
          </div>
      </div>
  </div>

  <div class="card card-soft mt-4">
      <div class="card-header">
          <h5><i class="fa-solid fa-table-list"></i> Ringkasan Pemesanan</h5>
      </div>
      <div class="card-body">
          <div class="table-responsive">
              <table class="table-modern table align-middle">
                  <thead>
                      <tr>
                          <th>#</th>
                          <th>Lapangan & Kode</th>
                          <th>Penyewa</th>
                          <th>Jadwal</th>
                          <th>Status</th>
                          <th>Pembayaran</th>
                          <th>Dibuat</th>
                      </tr>
                  </thead>
                  <tbody>
                      @forelse ($recentPemesanan as $index => $item)
                          <tr>
                              <td>{{ $index + 1 }}</td>
                              <td>
                                  <div class="fw-semibold">{{ $item->lapangan->nama_lapangan ?? '-' }}</div>
                                  @if ($item->kode_tiket)
                                      <small class="text-muted">Kode: {{ $item->kode_tiket }}</small>
                                  @endif
                              </td>
                              <td>
                                  <div class="fw-semibold">{{ $item->penyewa->name ?? '-' }}</div>
                                  <small class="text-muted">{{ $item->penyewa->email ?? '-' }}</small>
                              </td>
                              <td>
                                  <div>{{ $item->jadwal?->tanggal?->translatedFormat('d F Y') ?? '-' }}</div>
                                  <small class="text-muted">{{ $item->jadwal?->jam_mulai }} - {{ $item->jadwal?->jam_selesai }}</small>
                              </td>
                              <td>
                                  <span class="badge badge-status bg-{{ $statusColors[$item->status] ?? 'secondary' }}">
                                      {{ ucfirst($item->status) }}
                                  </span>
                              </td>
                              <td>
                                  @if ($item->pembayaran)
                                      <div>{{ 'Rp ' . number_format($item->pembayaran->jumlah ?? 0, 0, ',', '.') }}</div>
                                      <small class="text-muted text-capitalize">{{ $item->pembayaran->status }}</small>
                                  @else
                                      <span class="text-muted">Belum ada</span>
                                  @endif
                              </td>
                              <td>
                                  <div>{{ $item->created_at?->translatedFormat('d M Y') ?? '-' }}</div>
                                  <small class="text-muted">{{ $item->created_at?->format('H:i') ?? '' }}</small>
                              </td>
                          </tr>
                      @empty
                          <tr>
                              <td colspan="7">
                                  <div class="empty-state">
                                      <i class="fa-regular fa-folder-open"></i>
                                      <p>Data pemesanan belum tersedia.</p>
                                  </div>
                              </td>
                          </tr>
                      @endforelse
                  </tbody>
              </table>
          </div>
      </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = @json($monthlyLabels);
    const counts = @json($monthlyCounts);
    const revenue = @json($monthlyRevenue);

    const ctx = document.getElementById('pemesananChart');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Pemesanan',
                    data: counts,
                    borderColor: '#41A67E',
                    backgroundColor: 'rgba(65, 166, 126, 0.15)',
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Pendapatan (Rp)',
                    data: revenue,
                    borderColor: '#2C7BE5',
                    backgroundColor: 'rgba(44, 123, 229, 0.15)',
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        usePointStyle: true,
                        boxWidth: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            if (context.dataset.label.includes('Pendapatan')) {
                                return `${context.dataset.label}: Rp ${Number(context.parsed.y || 0).toLocaleString('id-ID')}`;
                            }
                            return `${context.dataset.label}: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    ticks: {
                        callback: (value) => `Rp ${Number(value).toLocaleString('id-ID')}`
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
</script>
@endsection
