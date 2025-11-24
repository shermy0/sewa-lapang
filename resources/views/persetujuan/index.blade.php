@extends('layouts.sidebar')

@section('title', 'Persetujuan Perubahan Jadwal')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
:root {
  --green: #41A67E;
  --green-dark: #2f7f5c;
  --green-light: #E6F5EF;
  --gray-light: #F8F9FA;
  --text-dark: #2E3A35;
}

/* Heading */
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

/* Filter/Search Card */
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

/* Table */
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
.table-container tbody tr:hover {
  background: var(--gray-light);
  transition: 0.25s;
}
.table-container tbody td {
  vertical-align: middle;
  padding: 14px;
  text-align: center;
}
.section-box {
  border-radius: 10px;
  padding: 8px;
  font-size: 0.85rem;
  line-height: 1.3;
}
.section-box.old { background: #fceaea; color: #b23b3b; }
.section-box.new { background: #e6f5ef; color: var(--green-dark); }

/* Empty State */
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

<div class="container py-4">
  <div class="page-heading">
    <h2>
      <i class="fa-solid fa-clipboard-check"></i>
      Persetujuan Perubahan Jadwal / Section
    </h2>
    <p>Kelola semua permintaan perubahan jadwal dari penyewa lapangan Anda.</p>
  </div>

  {{-- 🔍 Search & Filter --}}
  <div class="card filter-card">
    <div class="card-body">
      <form class="row g-3" method="GET" action="{{ route('persetujuan.index') }}">
        <div class="col-md-6">
          <label for="search" class="form-label">Cari Permintaan</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input
              type="text"
              name="search"
              id="search"
              class="form-control"
              placeholder="Masukkan nama penyewa atau nama lapangan"
              value="{{ request('search') }}"
            >
          </div>
        </div>
        <div class="col-md-4">
          <label for="status" class="form-label">Status</label>
          <select name="status" id="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
            <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
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

  {{-- 🧾 Tabel --}}
  <div class="table-container bg-white">
    @if($permintaan->isEmpty())
      <div class="empty-state">
        <i class="fa-solid fa-inbox"></i>
        <p>Belum ada permintaan perubahan yang cocok dengan filter saat ini.</p>
      </div>
    @else
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Penyewa</th>
              <th>Lapangan</th>
              <th>Sebelum</th>
              <th>Sesudah</th>
              <th>Alasan</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($permintaan as $index => $item)
              @php
                $penyewa = $item->pemesanan->user->name ?? '-';
                $lapangan = $item->pemesanan->lapangan->nama_lapangan ?? '-';
                $sectionLama = $item->jadwalLama->section->nama_section ?? '-';
                $tglLama = $item->jadwalLama ? \Carbon\Carbon::parse($item->jadwalLama->tanggal)->locale('id')->translatedFormat('l, d F Y') : '-';
                $jamLama = $item->jadwalLama ? "{$item->jadwalLama->jam_mulai} - {$item->jadwalLama->jam_selesai}" : '-';
                $sectionBaru = $item->jadwalBaru->section->nama_section ?? '-';
                $tglBaru = $item->jadwalBaru ? \Carbon\Carbon::parse($item->jadwalBaru->tanggal)->locale('id')->translatedFormat('l, d F Y') : '-';
                $jamBaru = $item->jadwalBaru ? "{$item->jadwalBaru->jam_mulai} - {$item->jadwalBaru->jam_selesai}" : '-';
              @endphp
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $penyewa }}</td>
                <td>{{ $lapangan }}</td>
                <td>
                  <div class="section-box old">
                    🧩 {{ $sectionLama }}<br>
                    📅 {{ $tglLama }}<br>
                    🕒 {{ $jamLama }}
                  </div>
                </td>
                <td>
                  <div class="section-box new">
                    🧩 {{ $sectionBaru }}<br>
                    📅 {{ $tglBaru }}<br>
                    🕒 {{ $jamBaru }}
                  </div>
                </td>
                <td>{{ $item->alasan ?? '-' }}</td>
<td>
    @php
        $statusClass = match($item->status) {
            'menunggu' => 'bg-warning text-dark',
            'disetujui' => 'bg-success',
            'ditolak' => 'bg-danger',
            'kadaluarsa' => 'bg-secondary text-white',
            default => 'bg-light'
        };
    @endphp
    <span class="badge {{ $statusClass }}">
        {{ ucfirst($item->status) }}
    @if($item->status === 'menunggu' && $item->expires_at)
        <span 
            class="countdown"
            data-expire="{{ $item->expires_at }}"
            id="cd-{{ $item->id }}"
        >
            ... memuat
        </span>
    @endif
</span>

    </span>
</td>

<td>
    @if($item->status === 'menunggu')
        <button class="btn btn-sm btn-success me-1" onclick="updateStatus({{ $item->id }}, 'disetujui')">
            <i class="fa-solid fa-check"></i>
        </button>
        <button class="btn btn-sm btn-danger" onclick="updateStatus({{ $item->id }}, 'ditolak')">
            <i class="fa-solid fa-xmark"></i>
        </button>
    @elseif($item->status === 'kadaluarsa')
        <span class="text-muted"><i class="fa-solid fa-ban me-1"></i> Kadaluarsa</span>
    @else
        <span class="text-muted"><i class="fa-solid fa-circle-info me-1"></i> Sudah {{ $item->status }}</span>
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
<script>
function startCountdown() {
    const timers = document.querySelectorAll('.countdown');

    timers.forEach(el => {
        const expireTime = new Date(el.dataset.expire).getTime();

        function update() {
            const now = new Date().getTime();
            let diff = Math.floor((expireTime - now) / 1000);

            if (diff <= 0) {
                el.innerHTML = " - 00:00";
                el.closest("tr").querySelector(".badge").classList.remove("bg-warning");
                el.closest("tr").querySelector(".badge").classList.add("bg-secondary");
                el.closest("tr").querySelector(".badge").innerHTML = "Kadaluarsa";
                return;
            }

            const minutes = String(Math.floor(diff / 60)).padStart(2, '0');
            const seconds = String(diff % 60).padStart(2, '0');

            el.innerHTML = ` - ${minutes}:${seconds}`;

            requestAnimationFrame(update);
        }

        update();
    });
}

document.addEventListener("DOMContentLoaded", startCountdown);
</script>

<script>
function updateStatus(id, status) {
  Swal.fire({
    title: status === 'disetujui' ? 'Setujui Permintaan?' : 'Tolak Permintaan?',
    text: status === 'disetujui'
      ? 'Penyewa akan otomatis berpindah ke jadwal baru.'
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
