@extends('layouts.sidebar') {{-- pastikan file layout yang kamu kirim bernama sidebar.blade.php --}}

@section('title', 'Dashboard Pemilik')

@section('content')
<div class="container-fluid py-4">
  <div class="row mb-4">
    <div class="col">
      <h4 class="fw-bold text-dark mb-0">
        <i class="fa-solid fa-chart-pie me-2 text-success"></i> Dashboard Pemilik
      </h4>
      <p class="text-muted">Selamat datang kembali, {{ Auth::user()->name }}!</p>
    </div>
  </div>

  {{-- Statistik ringkas --}}
  <div class="row g-4">
    <div class="col-md-3 col-sm-6">
      <div class="card shadow-sm border-0">
        <div class="card-body d-flex align-items-center">
          <div class="icon-box bg-success bg-opacity-10 text-success me-3 p-3 rounded-4">
            <i class="fa-solid fa-futbol fa-lg"></i>
          </div>
          <div>
            <h6 class="fw-semibold mb-1">Total Lapangan</h6>
            <h5 class="fw-bold mb-0">12</h5>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6">
      <div class="card shadow-sm border-0">
        <div class="card-body d-flex align-items-center">
          <div class="icon-box bg-primary bg-opacity-10 text-primary me-3 p-3 rounded-4">
            <i class="fa-solid fa-calendar-check fa-lg"></i>
          </div>
          <div>
            <h6 class="fw-semibold mb-1">Total Pemesanan</h6>
            <h5 class="fw-bold mb-0">84</h5>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6">
      <div class="card shadow-sm border-0">
        <div class="card-body d-flex align-items-center">
          <div class="icon-box bg-warning bg-opacity-10 text-warning me-3 p-3 rounded-4">
            <i class="fa-solid fa-money-bill-wave fa-lg"></i>
          </div>
          <div>
            <h6 class="fw-semibold mb-1">Total Pendapatan</h6>
            <h5 class="fw-bold mb-0">Rp 12.500.000</h5>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6">
      <div class="card shadow-sm border-0">
        <div class="card-body d-flex align-items-center">
          <div class="icon-box bg-danger bg-opacity-10 text-danger me-3 p-3 rounded-4">
            <i class="fa-solid fa-users fa-lg"></i>
          </div>
          <div>
            <h6 class="fw-semibold mb-1">Jumlah Pengguna</h6>
            <h5 class="fw-bold mb-0">32</h5>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Grafik atau Data Laporan --}}
  <div class="row mt-5">
    <div class="col-lg-8">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold mb-0">
            <i class="fa-solid fa-chart-line me-2 text-success"></i> Statistik Pemesanan Bulanan
          </h6>
          <small class="text-muted">Tahun 2025</small>
        </div>
        <div class="card-body">
          <canvas id="chartPemesanan"></canvas>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0">
          <h6 class="fw-bold mb-0">
            <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Pemesanan Terbaru
          </h6>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <li class="list-group-item px-0 d-flex justify-content-between">
              <span>Lapangan A</span>
              <small class="text-success">Selesai</small>
            </li>
            <li class="list-group-item px-0 d-flex justify-content-between">
              <span>Lapangan B</span>
              <small class="text-warning">Menunggu</small>
            </li>
            <li class="list-group-item px-0 d-flex justify-content-between">
              <span>Lapangan C</span>
              <small class="text-danger">Dibatalkan</small>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('chartPemesanan');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep'],
      datasets: [{
        label: 'Jumlah Pemesanan',
        data: [12, 19, 15, 22, 30, 25, 27, 32, 40],
        borderColor: '#198754',
        backgroundColor: 'rgba(25, 135, 84, 0.2)',
        fill: true,
        tension: 0.3
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
</script>
@endsection
