@extends('layouts.sidebar')

@section('title', 'Persetujuan Perubahan Jadwal')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
:root {
  --green: #41A67E;
  --green-light: #E6F5EF;
  --red-light: #FCEAEA;
  --gray-light: #F8F9FA;
  --text-dark: #2E3A35;
}

/* ===== Table Styling ===== */
.table-container {
  border-radius: 12px;
  overflow: hidden; /* biar radius berfungsi di atas */
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

.table {
  border-collapse: separate;
  border-spacing: 0;
  width: 100%;
}

.table thead th {
  background: var(--green);
  color: #fff;
  padding: 15px;
  border-right: 1px solid rgba(255, 255, 255, 0.3);
}

.table thead th:last-child {
  border-right: none;
}

/* Border radius pojok atas */
.table thead th:first-child {
  border-top-left-radius: 12px;
}
.table thead th:last-child {
  border-top-right-radius: 12px;
}

.table td, .table th {
  vertical-align: middle;
  text-align: center;
}

.table tbody tr {
  transition: 0.2s;
}

.table tbody tr:hover {
  background: var(--gray-light);
}

.section-box {
  background: var(--red-light);
  border-radius: 8px;
  padding: 8px 10px;
}

.section-box.new {
  background: var(--green-light);
}

.section-title {
  font-weight: 600;
  font-size: 0.9rem;
}

.section-date {
  font-size: 0.85rem;
  opacity: 0.9;
}

.section-time {
  font-size: 0.8rem;
  color: #555;
}

.badge {
  font-size: 0.85rem;
  padding: 6px 10px;
  border-radius: 8px;
}

.btn-success {
  background-color: var(--green);
  border-color: var(--green);
}

.btn-success:hover {
  background-color: #368b6b;
  border-color: #368b6b;
}

h2 i {
  color: var(--green);
}
</style>

<div class="container py-4">
    <h2 class="fw-bold text-center mb-4">
        <i class="fa-solid fa-clipboard-check me-2"></i>
        Persetujuan Perubahan Jadwal / Section
    </h2>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            @if($permintaan->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fa-solid fa-inbox fa-3x mb-3"></i>
                    <p class="mb-0">Belum ada permintaan perubahan.</p>
                </div>
            @else
                <div class="table-responsive table-container">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Penyewa</th>
                                <th>Lapangan</th>
                                <th>Sebelum Perubahan</th>
                                <th>Setelah Perubahan</th>
                                <th>Alasan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permintaan as $item)
                                @php
                                    $penyewa = $item->pemesanan->user->name ?? '-';
                                    $lapangan = $item->pemesanan->lapangan->nama_lapangan ?? '-';

                                    $sectionLama = $item->jadwalLama->section->nama_section ?? '-';
                                    $tglLama = $item->jadwalLama 
                                        ? \Carbon\Carbon::parse($item->jadwalLama->tanggal)->locale('id')->translatedFormat('l, d M Y') 
                                        : '-';
                                    $jamLama = $item->jadwalLama 
                                        ? "{$item->jadwalLama->jam_mulai} - {$item->jadwalLama->jam_selesai}" 
                                        : '-';

                                    $sectionBaru = $item->jadwalBaru->section->nama_section ?? '-';
                                    $tglBaru = $item->jadwalBaru 
                                        ? \Carbon\Carbon::parse($item->jadwalBaru->tanggal)->locale('id')->translatedFormat('l, d M Y') 
                                        : '-';
                                    $jamBaru = $item->jadwalBaru 
                                        ? "{$item->jadwalBaru->jam_mulai} - {$item->jadwalBaru->jam_selesai}" 
                                        : '-';
                                @endphp

                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <i class="fa-solid fa-user me-1 text-success"></i>
                                        <span class="fw-semibold">{{ $penyewa }}</span>
                                    </td>
                                    <td>{{ $lapangan }}</td>

                                    {{-- Sebelum --}}
                                    <td>
                                        <div class="section-box">
                                            <div class="section-title text-danger">{{ $sectionLama }}</div>
                                            <div class="section-date">{{ $tglLama }}</div>
                                            <div class="section-time">{{ $jamLama }}</div>
                                        </div>
                                    </td>

                                    {{-- Setelah --}}
                                    <td>
                                        <div class="section-box new">
                                            <div class="section-title text-success">{{ $sectionBaru }}</div>
                                            <div class="section-date">{{ $tglBaru }}</div>
                                            <div class="section-time">{{ $jamBaru }}</div>
                                        </div>
                                    </td>

                                    <td class="text-muted">{{ $item->alasan ?? '-' }}</td>

                                    <td>
                                        <span class="badge 
                                            @if($item->status == 'menunggu') bg-warning text-dark
                                            @elseif($item->status == 'disetujui') bg-success
                                            @else bg-danger @endif">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>

                                    <td>
                                        @if($item->status === 'menunggu')
                                            <button class="btn btn-sm btn-success me-1" 
                                                onclick="updateStatus({{ $item->id }}, 'disetujui')">
                                                <i class="fa-solid fa-check"></i> Setujui
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                onclick="updateStatus({{ $item->id }}, 'ditolak')">
                                                <i class="fa-solid fa-xmark"></i> Tolak
                                            </button>
                                        @else
                                            <span class="text-muted">
                                                <i class="fa-solid fa-circle-info me-1"></i> Sudah {{ $item->status }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function updateStatus(id, status) {
    Swal.fire({
        title: status === 'disetujui' ? 'Setujui Permintaan?' : 'Tolak Permintaan?',
        text: status === 'disetujui' 
            ? 'Penyewa akan otomatis berpindah ke jadwal dan section baru.' 
            : 'Permintaan akan ditolak dan jadwal tetap sama.',
        icon: status === 'disetujui' ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonColor: status === 'disetujui' ? '#41A67E' : '#d33',
        cancelButtonText: 'Batal',
        confirmButtonText: status === 'disetujui' ? 'Setujui' : 'Tolak'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(`/persetujuan/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status })
            })
            .then(res => res.json())
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: `Permintaan telah ${status}.`,
                    confirmButtonColor: '#41A67E'
                }).then(() => location.reload());
            });
        }
    });
}
</script>
@endsection
