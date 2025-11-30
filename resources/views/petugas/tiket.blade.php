@extends('layouts.master')

@section('title', 'Tiket Petugas')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Daftar Tiket</h1>
        <span class="text-muted small">{{ $tiket->count() }} tiket</span>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Kode Tiket</th>
                            <th>Penyewa</th>
                            <th>Lapangan/Section</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Status Pesanan</th>
                            <th>Status Bayar</th>
                            <th>Status Scan</th>
                            <th>QR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tiket as $t)
                            @php
                                $tanggalMain = optional($t->jadwal)->tanggal
                                    ? \Carbon\Carbon::parse($t->jadwal->tanggal)->format('d M Y')
                                    : '-';
                                $jamMain = optional($t->jadwal)->jam_mulai
                                    ? substr($t->jadwal->jam_mulai, 0, 5) . ' - ' . substr($t->jadwal->jam_selesai, 0, 5)
                                    : '-';
                                $statusClass = match($t->status) {
                                    'dibayar' => 'success',
                                    'menunggu' => 'warning',
                                    'kadaluarsa' => 'secondary',
                                    default => 'secondary',
                                };
                                $bayarStatus = $t->pembayaran->status ?? '-';
                                $bayarClass = match($bayarStatus) {
                                    'berhasil' => 'success',
                                    'pending' => 'warning',
                                    'gagal' => 'danger',
                                    default => 'secondary',
                                };
                                $scanStatus = $t->status_scan ?? 'belum_scan';
                                $scanClass = match(true) {
                                    in_array($scanStatus, ['sudah_scan', 'masuk_lapang']) => 'success',
                                    in_array($scanStatus, ['scan_lobby', 'masuk_arena']) => 'info text-dark',
                                    default => 'secondary',
                                };
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $t->kode_tiket }}</td>
                                <td>{{ $t->penyewa->name ?? '-' }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $t->lapangan->nama_lapangan ?? '-' }}</div>
                                    <div class="text-muted small">{{ $t->jadwal->section->nama_section ?? '-' }}</div>
                                </td>
                                <td>{{ $tanggalMain }}</td>
                                <td>{{ $jamMain }}</td>
                                <td><span class="badge bg-{{ $statusClass }}">{{ strtoupper($t->status ?? '-') }}</span></td>
                                <td><span class="badge bg-{{ $bayarClass }}">{{ strtoupper($bayarStatus) }}</span></td>
                                <td>
                                    @if(in_array($scanStatus, ['sudah_scan','masuk_lapang']))
                                        <span class="badge bg-success">MASUK LAPANG</span>
                                    @elseif(in_array($scanStatus, ['scan_lobby','masuk_arena']))
                                        <span class="badge bg-info text-dark">MASUK ARENA</span>
                                    @else
                                        <span class="badge bg-secondary">BELUM SCAN</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary view-qr" data-code="{{ $t->kode_tiket }}">
                                        <i class="fa-solid fa-qrcode"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada tiket.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal QR -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="qrModalLabel">QR Tiket</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body d-flex flex-column align-items-center">
        <div id="qrContainer" class="mb-3"></div>
        <div id="qrCodeText" class="fw-semibold"></div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const qrModalEl = document.getElementById('qrModal');
  const qrModal = new bootstrap.Modal(qrModalEl);
  const qrContainer = document.getElementById('qrContainer');
  const qrCodeText = document.getElementById('qrCodeText');
  let qrInstance = null;

  document.querySelectorAll('.view-qr').forEach(btn => {
    btn.addEventListener('click', () => {
      const code = btn.dataset.code || '';
      qrContainer.innerHTML = '';
      qrCodeText.textContent = code;
      qrInstance = new QRCode(qrContainer, {
        text: code,
        width: 180,
        height: 180,
        correctLevel: QRCode.CorrectLevel.H
      });
      qrModal.show();
    });
  });

  qrModalEl.addEventListener('hidden.bs.modal', () => {
    qrContainer.innerHTML = '';
    qrInstance = null;
  });
});
</script>
@endpush
