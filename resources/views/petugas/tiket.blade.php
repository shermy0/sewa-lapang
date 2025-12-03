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
                            <th>Komunitas</th>
                            <th>Status Bayar</th>
                            <th>Aksi</th>
                            <th>Status Scan</th>
                            <th>QR</th>
                            <th>Struk</th>
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
                                $bayarStatus = $t->pembayaran->status ?? '-';
                                $bayarClass = match($bayarStatus) {
                                    'berhasil' => 'success',
                                    'pending' => 'warning',
                                    'gagal' => 'danger',
                                    default => 'secondary',
                                };
                                $status = $t->status ?? '-';
                                $statusClass = match($status) {
                                    'dibayar' => 'success',
                                    'menunggu' => 'warning text-dark',
                                    'kadaluarsa', 'gagal', 'batal', 'dibatalkan' => 'secondary',
                                    'selesai' => 'info',
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
                                <td>{{ $t->nama_komunitas ?? '-' }}</td>
                                <td><span class="badge bg-{{ $bayarClass }}">{{ strtoupper($bayarStatus) }}</span></td>
                                <td>
                                    @if(($t->status === 'menunggu') || ($bayarStatus === 'pending'))
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-success pay-midtrans"
                                            data-id="{{ $t->id }}">
                                            <i class="fa-solid fa-credit-card"></i>
                                        </button>
                                    @endif
                                </td>
                                <td>
                                    @if($scanStatus == 'sudah_scan')
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
                                <td>
                                    @if($bayarStatus === 'berhasil')
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary view-receipt"
                                        data-kode="{{ $t->kode_tiket }}"
                                        data-penyewa="{{ $t->nama_komunitas ?? ($t->penyewa->name ?? '-') }}"
                                        data-lapangan="{{ $t->lapangan->nama_lapangan ?? '-' }}"
                                        data-section="{{ $t->jadwal->section->nama_section ?? '-' }}"
                                        data-tanggal="{{ $tanggalMain }}"
                                        data-jam="{{ $jamMain }}"
                                        data-total="{{ $t->pembayaran->jumlah ?? 0 }}"
                                        data-status="{{ strtoupper($bayarStatus) }}"
                                        data-kasir="{{ $petugasName ?? Auth::user()->name }}"
                                        data-metode="{{ $t->pembayaran->metode ?? '-' }}"
                                    >
                                        <i class="fa-solid fa-print"></i>
                                    </button>
                                    @endif
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

<!-- Modal Struk -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="receiptModalLabel">Struk Pembayaran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="receiptBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary btn-sm" id="printReceiptBtn"><i class="fa-solid fa-print me-1"></i>Cetak</button>
      </div>
    </div>
  </div>
</div>

<style>
@media print {
  @page {
    size: 80mm auto;
    margin: 0;
  }
  body * { visibility: hidden; }
  #print-area, #print-area * { visibility: visible; }
  #print-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    display: block !important;
    padding: 5mm;
  }
}
.thermal-receipt { font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.4; color: #000; }
.receipt-divider { border-top: 1px dashed #333; margin: 6px 0; }
.receipt-row { display: flex; justify-content: space-between; margin-bottom: 3px; gap: 10px; }
.receipt-item { margin-bottom: 6px; }
.receipt-total { display: flex; justify-content: space-between; font-weight: 700; margin-top: 6px; }
</style>

<div id="print-area" class="d-none"></div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
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

  // Midtrans Pay Again (untuk penyewa tanpa HP)
  document.querySelectorAll('.pay-midtrans').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      if (!id) return;

      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

      fetch(`/petugas/pemesanan/${id}/midtrans/token`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (data.error || !data.snap_token) {
          throw new Error(data.error || 'Gagal membuat token');
        }

        snap.pay(data.snap_token, {
          onSuccess: function(result) {
            fetch(`/petugas/pemesanan/${id}/midtrans/success`, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({ result })
            }).then(() => window.location.reload());
          },
          onPending: function() {
            alert('Pembayaran masih pending.');
          },
          onError: function() {
            alert('Pembayaran gagal atau dibatalkan.');
          }
        });
      })
      .catch(err => {
        console.error(err);
        alert('Tidak bisa memproses Midtrans.');
      })
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-credit-card"></i>';
      });
    });
  });

  // Receipt preview
  const receiptModalEl = document.getElementById('receiptModal');
  const receiptBody = document.getElementById('receiptBody');
  const printArea = document.getElementById('print-area');

  function renderReceipt(data) {
    const total = Number(data.total || 0);
    const html = `
      <div class="thermal-receipt">
        <div style="text-align:center; margin-bottom:8px;">
          <div style="font-weight:800; letter-spacing:1px;">SEWALAP</div>
          <div style="font-size:12px;">Bukti Pembayaran</div>
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-row"><span>Kode Tiket</span><span>${data.kode}</span></div>
        <div class="receipt-row"><span>Status</span><span>${data.status}</span></div>
        <div class="receipt-row"><span>Metode</span><span>${data.metode}</span></div>
        <div class="receipt-row"><span>Kasir</span><span>${data.kasir}</span></div>
        <div class="receipt-row"><span>Nama</span><span>${data.penyewa}</span></div>
        <div class="receipt-divider"></div>
        <div class="receipt-item">
          <div>${data.lapangan} - ${data.section}</div>
          <div>${data.tanggal} • ${data.jam}</div>
          <div>Total: Rp ${total.toLocaleString('id-ID')}</div>
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-total"><span>TOTAL</span><span>Rp ${total.toLocaleString('id-ID')}</span></div>
        <div class="receipt-divider"></div>
        <div style="text-align:center; font-size:11px;">Terima kasih</div>
      </div>
    `;
    receiptBody.innerHTML = html;
    if (printArea) printArea.innerHTML = html;
  }

  document.querySelectorAll('.view-receipt').forEach(btn => {
    btn.addEventListener('click', () => {
      const data = {
        kode: btn.dataset.kode || '-',
        penyewa: btn.dataset.penyewa || '-',
        lapangan: btn.dataset.lapangan || '-',
        section: btn.dataset.section || '-',
        tanggal: btn.dataset.tanggal || '-',
        jam: btn.dataset.jam || '-',
        total: btn.dataset.total || 0,
        status: btn.dataset.status || '-',
        kasir: btn.dataset.kasir || '-',
        metode: (btn.dataset.metode || '-').toUpperCase(),
      };
      renderReceipt(data);
      const modal = new bootstrap.Modal(receiptModalEl);
      modal.show();
    });
  });

  document.getElementById('printReceiptBtn')?.addEventListener('click', () => {
    window.print();
  });
});
</script>
@endpush
