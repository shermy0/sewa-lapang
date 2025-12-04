@extends('layouts.master')

@section('title', 'History Transaksi')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">History Transaksi</h1>
        <span class="text-muted small">{{ $orders->count() }} order</span>
    </div>

    @forelse($orders as $orderId => $payments)
        @php
            $orderKey = $orderId ?: 'TANPA-ORDER';
            $firstPay = $payments->first();
            $metode = strtoupper($firstPay->metode ?? '-');
            $status = $firstPay->status ?? '-';
            $statusClass = match($status) {
                'berhasil' => 'success',
                'pending' => 'warning',
                'gagal' => 'danger',
                'batal', 'kadaluarsa' => 'secondary',
                default => 'secondary',
            };
            $total = $payments->sum('jumlah');
            $waktu = optional($firstPay->tanggal_pembayaran ?? $firstPay->created_at)?->timezone('Asia/Jakarta');
            $firstOrderPemesanan = optional($firstPay->pemesanan);
            $penyewaName = optional($firstOrderPemesanan->penyewa)->name ?? '-';
        @endphp

        <div class="card shadow-sm mb-3">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="fw-bold">Order {{ $orderKey }}</div>
                    <div class="text-muted small">{{ $waktu ? $waktu->format('d M Y H:i') : '-' }}</div>
                    <div class="text-muted small">Penyewa: {{ $penyewaName }}</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $statusClass }}">{{ strtoupper($status) }}</span>
                    <span class="badge bg-secondary">{{ $metode }}</span>
                    <div class="fw-bold text-success">Rp {{ number_format($total, 0, ',', '.') }}</div>
                    @php
                        $itemPayload = $payments->map(function ($pay) {
                            $p = $pay->pemesanan;
                            $tanggalMain = optional($p?->jadwal)->tanggal
                                ? \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y')
                                : '-';
                            $jamMain = optional($p?->jadwal)->jam_mulai
                                ? substr($p->jadwal->jam_mulai, 0, 5) . ' - ' . substr($p->jadwal->jam_selesai, 0, 5)
                                : '-';
                            return [
                                'kode' => $p->kode_tiket ?? '-',
                                'lapangan' => $p->lapangan->nama_lapangan ?? '-',
                                'section' => $p->jadwal->section->nama_section ?? '-',
                                'tanggal' => $tanggalMain,
                                'jam' => $jamMain,
                                'harga' => $pay->jumlah ?? 0,
                            ];
                        })->values();
                    @endphp
                    @if($status === 'berhasil')
                        <button
                            class="btn btn-sm btn-outline-primary view-receipt"
                            data-order="{{ $orderKey }}"
                            data-penyewa="{{ $penyewaName }}"
                            data-metode="{{ $metode }}"
                            data-total="{{ $total }}"
                            data-kasir="{{ $petugasName ?? Auth::user()->name }}"
                            data-status="{{ strtoupper($status) }}"
                            data-items='@json($itemPayload)'
                        >
                            <i class="fa-solid fa-print me-1"></i> Struk
                        </button>
                    @endif
                </div>
            </div>
            <div class="list-group list-group-flush">
                @foreach($payments as $pay)
                    @php
                        $p = $pay->pemesanan;
                        $tanggalMain = optional($p?->jadwal)->tanggal
                            ? \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y')
                            : '-';
                        $jamMain = optional($p?->jadwal)->jam_mulai
                            ? substr($p->jadwal->jam_mulai, 0, 5) . ' - ' . substr($p->jadwal->jam_selesai, 0, 5)
                            : '-';
                        $statusPesan = $p->status ?? '-';
                        $statusPesanClass = match($statusPesan) {
                            'dibayar' => 'success',
                            'menunggu' => 'warning text-dark',
                            'selesai' => 'info',
                            'kadaluarsa','batal','gagal','dibatalkan' => 'secondary',
                            default => 'secondary',
                        };
                        $scanStatus = $p->status_scan ?? 'belum_scan';
                        $scanClass = match(true) {
                            in_array($scanStatus, ['sudah_scan','masuk_lapang']) => 'success',
                            in_array($scanStatus, ['scan_lobby','masuk_arena']) => 'info text-dark',
                            default => 'secondary',
                        };
                    @endphp
                    <div class="list-group-item d-flex flex-wrap align-items-center gap-3">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-{{ $statusPesanClass }}">{{ strtoupper($statusPesan) }}</span>
                                <span class="badge bg-{{ $statusClass }}">{{ strtoupper($pay->status ?? '-') }}</span>
                                <span class="badge bg-light text-dark">{{ $p->kode_tiket ?? '-' }}</span>
                            </div>
                            <div class="fw-semibold">{{ $p->lapangan->nama_lapangan ?? '-' }} • {{ $p->jadwal->section->nama_section ?? '-' }}</div>
                            <div class="text-muted small">{{ $tanggalMain }} • {{ $jamMain }}</div>
                            <div class="text-muted small">Komunitas: {{ $p->nama_komunitas ?? '-' }}</div>
                            <div class="text-muted small">Scan: <span class="badge bg-{{ $scanClass }}">{{ strtoupper($scanStatus) }}</span></div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success">Rp {{ number_format($pay->jumlah ?? 0, 0, ',', '.') }}</div>
                            <div class="text-muted small">{{ strtoupper($pay->metode ?? '-') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-4">Belum ada transaksi.</div>
    @endforelse
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const receiptModalEl = document.getElementById('receiptModal');
  const receiptBody = document.getElementById('receiptBody');
  const printArea = document.getElementById('print-area');

  function renderReceipt(data) {
    const itemsHtml = (data.items || []).map(item => `
      <div class="receipt-item">
        <div>${item.lapangan} - ${item.section}</div>
        <div>${item.tanggal} • ${item.jam}</div>
        <div>Kode: ${item.kode}</div>
        <div>Rp ${Number(item.harga || 0).toLocaleString('id-ID')}</div>
      </div>
    `).join('');

    const total = Number(data.total || 0);
    const html = `
      <div class="thermal-receipt">
        <div style="text-align:center; margin-bottom:8px;">
          <div style="font-weight:800; letter-spacing:1px;">SEWALAP</div>
          <div style="font-size:12px;">Bukti Pembayaran</div>
        </div>
        <div class="receipt-divider"></div>
        <div class="receipt-row"><span>Order ID</span><span>${data.order || '-'}</span></div>
        <div class="receipt-row"><span>Status</span><span>${data.status || '-'}</span></div>
        <div class="receipt-row"><span>Metode</span><span>${data.metode || '-'}</span></div>
        <div class="receipt-row"><span>Kasir</span><span>${data.kasir || '-'}</span></div>
        <div class="receipt-row"><span>Penyewa</span><span>${data.penyewa || '-'}</span></div>
        <div class="receipt-divider"></div>
        ${itemsHtml}
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
      const itemsRaw = btn.dataset.items ? JSON.parse(btn.dataset.items) : [];
      const data = {
        order: btn.dataset.order || '-',
        penyewa: btn.dataset.penyewa || '-',
        metode: (btn.dataset.metode || '-').toUpperCase(),
        total: btn.dataset.total || 0,
        kasir: btn.dataset.kasir || '-',
        status: btn.dataset.status || '-',
        items: itemsRaw,
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
@endsection
