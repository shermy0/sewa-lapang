@extends('layouts.sidebar')

@section('title', 'Pemesanan Lapangan')

@section('content')
@php
    $statusBadges = [
        'menunggu' => ['label' => 'Menunggu', 'class' => 'bg-warning text-dark'],
        'dibayar' => ['label' => 'Dibayar', 'class' => 'bg-info text-dark'],
        'selesai' => ['label' => 'Selesai', 'class' => 'bg-success'],
        'batal' => ['label' => 'Batal', 'class' => 'bg-danger'],
    ];
@endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root {
  --green: #41A67E;
  --green-dark: #2f7f5c;
  --green-light: #E6F5EF;
  --gray-light: #F8F9FA;
  --text-dark: #2E3A35;
}

.page-heading {
  text-align: center;
  margin-bottom: 2rem;
  color: var(--text-dark);
}

.page-heading h2 {
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
}

.page-heading h2 i {
  color: var(--green);
}

.page-heading p {
  margin-top: 0.5rem;
  color: #6c757d;
}

.filter-card {
  border-radius: 16px;
  border: none;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
  margin-bottom: 1.5rem;
}

.filter-card .form-label {
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #6c757d;
}

.filter-card .btn {
  border-radius: 12px;
  background: var(--green);
  border: none;
  font-weight: 600;
}

.filter-card .btn:hover {
  background: var(--green-dark);
}

.table-container {
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.table-container table {
  border-collapse: separate;
  border-spacing: 0;
  width: 100%;
}

.table-container thead th {
  background: var(--green);
  color: #fff;
  text-transform: uppercase;
  font-size: 0.8rem;
  letter-spacing: 0.08em;
  padding: 16px;
}

.table-container thead th:first-child {
  border-top-left-radius: 16px;
}

.table-container thead th:last-child {
  border-top-right-radius: 16px;
}

.table-container tbody tr {
  transition: 0.25s;
}

.table-container tbody tr:hover {
  background: var(--gray-light);
}

.table-container tbody td {
  vertical-align: middle;
  padding: 18px 16px;
  text-align: center;
}

.table-container tbody td:first-child {
  font-weight: 600;
  color: var(--text-dark);
}

.identity-box {
  text-align: left;
}

.identity-box .title {
  font-weight: 600;
  color: var(--text-dark);
}

.identity-box .subtitle {
  font-size: 0.85rem;
  color: #6c757d;
}

.code-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.78rem;
  padding: 6px 12px;
  border-radius: 999px;
  background: var(--green-light);
  color: var(--green-dark);
  font-weight: 600;
  margin-top: 0.35rem;
}

.schedule-box {
  background: #fff;
  border-radius: 12px;
  padding: 12px;
  text-align: left;
  border: 1px dashed rgba(65, 166, 126, 0.35);
}

.schedule-box .day {
  font-weight: 600;
  color: var(--text-dark);
  margin-bottom: 4px;
}

.schedule-box .time,
.schedule-box .section {
  font-size: 0.82rem;
  color: #6c757d;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.payment-box {
  display: grid;
  gap: 0.25rem;
}

.payment-box .amount {
  font-weight: 600;
  color: var(--text-dark);
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

.table-footer {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: #fff;
}

.table-footer small {
  color: #6c757d;
}
</style>

<div class="container py-4">
  <div class="page-heading">
      <h2>
          <i class="fa-solid fa-calendar-check"></i>
          Pemesanan Lapangan
      </h2>
      <p>Kelola seluruh pemesanan yang masuk pada lapangan milik Anda.</p>
  </div>

  <div class="card filter-card">
      <div class="card-body">
          <form class="row g-3" method="GET" action="{{ route('pemilik.pemesanan.index') }}">
              <div class="col-md-6">
                  <label for="search" class="form-label">Cari Pemesanan</label>
                  <div class="input-group">
                      <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                      <input
                          type="text"
                          name="search"
                          id="search"
                          class="form-control"
                          placeholder="Masukkan nama penyewa, lapangan, atau kode tiket"
                          value="{{ $searchTerm }}"
                      >
                  </div>
              </div>
              <div class="col-md-4">
                  <label for="status" class="form-label">Status Pemesanan</label>
                  <select name="status" id="status" class="form-select">
                      <option value="">Semua Status</option>
                      @foreach ($statusOptions as $value => $label)
                          <option value="{{ $value }}" {{ $selectedStatus === $value ? 'selected' : '' }}>
                              {{ $label }}
                          </option>
                      @endforeach
                  </select>
              </div>
              <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-success w-100">
                      <i class="fa-solid fa-filter me-2"></i> Terapkan
                  </button>
              </div>
          </form>
      </div>
  </div>

  <div class="table-container bg-white">
      @if ($pemesanan->isEmpty())
          <div class="empty-state">
              <i class="fa-regular fa-calendar-xmark"></i>
              <p>Belum ada pemesanan yang cocok dengan filter saat ini.</p>
          </div>
      @else
          <div class="table-responsive">
              <table class="table align-middle mb-0">
                  <thead>
                      <tr>
                          <th>#</th>
                          <th>Penyewa</th>
                          <th>Lapangan</th>
                          <th>Jadwal</th>
                          <th>Status</th>
                          <th>Pembayaran</th>
                          <th>Dibuat</th>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach ($pemesanan as $index => $item)
                          @php
                              $badge = $statusBadges[$item->status] ?? ['label' => ucfirst($item->status), 'class' => 'bg-secondary'];
                              $pembayaran = $item->pembayaran;
                              $jadwal = $item->jadwal;
                          @endphp
                          <tr>
                              <td>{{ $pemesanan->firstItem() + $index }}</td>
                              <td>
                                  <div class="identity-box">
                                      <div class="title">{{ $item->penyewa->name ?? '-' }}</div>
                                      <div class="subtitle">
                                          {{ $item->penyewa->email ?? '-' }}
                                          @if (!empty($item->penyewa?->no_hp))
                                              &middot; {{ $item->penyewa->no_hp }}
                                          @endif
                                      </div>
                                      @if ($item->kode_tiket)
                                          <span class="code-badge">
                                              <i class="fa-solid fa-ticket"></i> {{ $item->kode_tiket }}
                                          </span>
                                      @endif
                                  </div>
                              </td>
                              <td>
                                  <div class="identity-box">
                                      <div class="title">{{ $item->lapangan->nama_lapangan ?? '-' }}</div>
                                      <div class="subtitle">
                                          {{ $jadwal?->section?->nama_section ?? 'Section tidak tersedia' }}
                                      </div>
                                  </div>
                              </td>
                              <td>
                                  <div class="schedule-box">
                                      <div class="day">
                                          {{ $jadwal?->tanggal?->translatedFormat('l, d F Y') ?? '-' }}
                                      </div>
                                      <div class="time">
                                          <i class="fa-regular fa-clock me-1"></i>
                                          {{ $jadwal?->jam_mulai }} - {{ $jadwal?->jam_selesai }}
                                      </div>
                                  </div>
                              </td>
                              <td>
                                  <span class="badge status-badge {{ $badge['class'] }}">
                                      {{ $badge['label'] }}
                                  </span>
                              </td>
                              <td>
                                  <div class="payment-box">
                                      <span class="amount">
                                          {{ $pembayaran ? 'Rp ' . number_format($pembayaran->jumlah ?? 0, 0, ',', '.') : '-' }}
                                      </span>
                                      <span class="badge status-badge {{ $pembayaran?->status === 'berhasil' ? 'bg-success' : ($pembayaran?->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                          {{ $pembayaran?->status ? ucfirst($pembayaran->status) : 'Belum Ada' }}
                                      </span>
                                  </div>
                              </td>
                              <td>
                                  <div class="identity-box text-center">
                                      <div class="title">{{ $item->created_at?->translatedFormat('d M Y') ?? '-' }}</div>
                                      <div class="subtitle">{{ $item->created_at?->format('H:i') ?? '' }}</div>
                                  </div>
                              </td>
                          </tr>
                      @endforeach
                  </tbody>
              </table>
          </div>
          <div class="table-footer">
              <small>
                  Menampilkan {{ $pemesanan->firstItem() }} - {{ $pemesanan->lastItem() }} dari {{ $pemesanan->total() }} pemesanan
              </small>
              {{ $pemesanan->onEachSide(1)->links('pagination::bootstrap-5') }}
          </div>
      @endif
  </div>
</div>
@endsection
