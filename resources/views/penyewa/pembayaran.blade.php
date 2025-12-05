@extends('layouts.sidebar')

@section('title', 'Menunggu Pembayaran')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/tiket.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
:root {
    --green: #41A67E;
    --yellow: #F6C445;
    --gray-light: #f9f9f9;
}

.ticket-status-pay {
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 6px;
    padding: 4px 8px;
}
.ticket-status-pay.pending {
    background: #FFF5D6;
    color: #C78C00;
}
.ticket-status-pay.expired {
    background: #FCEAEA;
    color: #B22222;
}
.countdown-label {
    font-size: 0.9rem;
    font-weight: 500;
}
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">
        <i class="fa-solid fa-clock me-2"></i> Menunggu Pembayaran
    </h2>
    <p class="text-muted mb-4">Segera selesaikan pembayaran agar jadwal bermainmu tetap aman!</p>

    <div class="row" id="ticketContainer">
        @forelse($belumDibayar as $p)
            <div class="col-md-6 mb-4 ticket-card">

                {{-- Notifikasi perubahan --}}

                {{-- Kartu tiket --}}
                <div class="ticket shadow-sm border-0 rounded-4 overflow-hidden">

                    <div class="d-flex flex-column flex-md-row">
                        @php
                            $jadwal = $p->jadwal;
                            $section = $jadwal?->section;
                            \Carbon\Carbon::setLocale('id');
                            $tanggal = \Carbon\Carbon::parse($jadwal->tanggal);
                            $hari = $tanggal->translatedFormat('l');
                        @endphp

                        {{-- KIRI --}}
                        <div class="ticket-left">
                            <div class="ticket-left-header text-center">
                                <div class="lapangan-name">{{ $p->lapangan->nama_lapangan }}</div>
                                <div class="lapangan-meta">
                                    <i class="fa-solid fa-tag me-1"></i>{{ ucfirst($p->lapangan->kategori) }}<br>
                                    <i class="fa-solid fa-location-dot me-1"></i>{{ $p->lapangan->lokasi }}
                                </div>
                                @if($section)
                                    <div class="section-info mt-2">
                                        <i class="fa-solid fa-layer-group"></i>
                                        <span>{{ $section->nama_section }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="schedule mt-3 text-center">
                                <div>
                                    <i class="fa-solid fa-calendar-day me-1"></i>
                                    {{ $hari }}, {{ $tanggal->translatedFormat('d F Y') }}
                                </div>
                                <div>
                                    <i class="fa-solid fa-clock me-1"></i>
                                    {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}
                                </div>
                            </div>
                        </div>

                        {{-- KANAN --}}
                        <div class="ticket-right p-4 bg-white flex-grow-1 position-relative">
                            <div class="ticket-info">
                                <p class="mb-1"><strong>Status:</strong>
                                    <span class="ticket-status-pay pending">
                                        <i class="fa-solid fa-coins me-1"></i> Belum Dibayar
                                    </span>
                                </p>
                                <p class="mb-1"><strong>Harga:</strong>
                                    Rp {{ number_format($jadwal->harga_sewa, 0, ',', '.') }}
                                </p>
<p class="mb-1 countdown-label text-danger"
   data-countdown
   data-order-id="{{ $p->id }}"
   data-expires-at="{{ $p->expires_at }}">
</p>

                            </div>

                            <div class="text-end mt-3 d-flex flex-column gap-2">
                                <button class="btn btn-success btn-sm px-3 btn-pay-again"
                                        data-id="{{ $p->id }}">
                                    <i class="fa-solid fa-credit-card me-1"></i> Bayar Sekarang
                                </button>

                                {{-- Ganti tombol batalkan pakai SweetAlert --}}
                                <button class="btn btn-outline-danger btn-sm px-3 btn-cancel" 
                                        data-id="{{ $p->id }}">
                                    <i class="fa-solid fa-xmark me-1"></i> Batalkan
                                </button>

{{-- Tombol pindah lapang
@if($p->status_scan !== 'sudah_scan')
@php
    $jadwalAktifText = '-';
    $jadwalAktifId = null;

    if($p->jadwal){
        $tanggalFormat = \Carbon\Carbon::parse($p->jadwal->tanggal)->translatedFormat('l, d F Y');
        $jadwalAktifText = $tanggalFormat . ' | ' . $p->jadwal->jam_mulai . ' - ' . $p->jadwal->jam_selesai;
        $jadwalAktifId = $p->jadwal->id;
    }
@endphp

<button class="btn btn-warning btn-sm px-3"
        onclick="pindahLapang(
            {{ $p->id }},
            {{ $p->lapangan->id }},
            '{{ $p->lapangan->nama_lapangan }}',
            '{{ $section?->nama_section ?? '-' }}',
            '{{ $jadwalAktifText }}',
            {{ $jadwalAktifId ?? 'null' }}
        )">
    <i class="fa-solid fa-arrows-rotate me-1"></i> Pindah Lapang
</button>

@endif                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">Belum ada pesanan menunggu pembayaran.</p>
        @endforelse
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Countdown pembayaran dengan auto-expire ke server
document.querySelectorAll('[data-countdown]').forEach(target => {
    const POLL_INTERVAL_MS = 5000;
    let expiresAtMs = target.dataset.expiresAt ? new Date(target.dataset.expiresAt).getTime() : null;
    const orderId = target.dataset.orderId;
    const card = target.closest('.ticket-card');
    const container = document.getElementById('ticketContainer');

    const removeCardIfEmpty = () => {
        if (card) card.remove();
        if (container && container.querySelectorAll('.ticket-card').length === 0) {
            container.innerHTML = '<p class="text-muted">Belum ada pesanan menunggu pembayaran.</p>';
        }
    };

    const markStatus = (status) => {
        const badge = target.closest('.ticket-right')?.querySelector('.ticket-status-pay');
        if (!badge) return;

        badge.classList.remove('pending', 'expired');

        if (status === 'kadaluarsa') {
            badge.textContent = 'Kadaluarsa';
            badge.classList.add('expired');
        } else if (status === 'menunggu') {
            badge.innerHTML = '<i class="fa-solid fa-coins me-1"></i> Belum Dibayar';
            badge.classList.add('pending');
        } else {
            badge.textContent = status;
        }
    };

    const disableActions = (message = '') => {
        if (message) target.textContent = message;
        target.classList.remove('text-danger');
        target.classList.add('text-muted');

        card?.querySelectorAll('button').forEach(btn => btn.remove());
    };

    const markExpired = (message = '⛔ Waktu pembayaran sudah habis.') => {
        markStatus('kadaluarsa');
        disableActions(message);
        removeCardIfEmpty();
    };

    const expireOnServer = () => {
        if (!orderId || !csrfToken) return;
        fetch(`/pemesanan/${orderId}/expire`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.ok ? res.json() : null)
        .then(data => {
            if (!data) return;
            if (data.expires_at) {
                expiresAtMs = new Date(data.expires_at).getTime();
                target.dataset.expiresAt = data.expires_at;
            }

            if (data.status === 'kadaluarsa') {
                markExpired();
            } else if (data.status && data.status !== 'menunggu') {
                markStatus(data.status);
                disableActions(`Status berubah: ${data.status}`);
            }
        })
        .catch(() => {});
    };

    const tick = () => {
        if (target.dataset.done === '1') return;
        if (!expiresAtMs) {
            target.textContent = '';
            return;
        }

        const now = Date.now();
        const diff = expiresAtMs - now;

        if (diff <= 0) {
            target.dataset.done = '1';
            markExpired();
            expireOnServer();
            return;
        }

        const m = Math.floor(diff / 60000);
        const s = Math.floor((diff % 60000) / 1000);

        target.textContent = `Sisa waktu pembayaran: ${m}m ${s}d`;

        setTimeout(tick, 1000);
    };

    tick();

    // Poll server tiap beberapa detik untuk sync status tanpa refresh
    setInterval(expireOnServer, POLL_INTERVAL_MS);
});
// 💳 Midtrans - Bayar Sekarang + Loading
document.querySelectorAll('.btn-pay-again').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        Swal.fire({
            title: 'Memuat Pembayaran...',
            text: 'Harap tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(`/midtrans/token-again/${id}`)
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if (data.error) return Swal.fire('Gagal!', data.error, 'error');
                snap.pay(data.snap_token, { 
                    onSuccess: function(result){
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Menyimpan data transaksi...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                        fetch('/pemesanan/success/' + data.pemesanan_id, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ result })
                        }).catch(()=>{}).finally(() => {
                            window.location.href = "{{ route('penyewa.tiket') }}";
                        });
                    },
                    onPending: function(){
                        Swal.fire('Menunggu Pembayaran', 'Silakan selesaikan pembayaranmu.', 'info')
                            .then(() => window.location.reload());
                    },
                    onError: function(){
                        Swal.fire('Pembayaran Gagal!', 'Terjadi kesalahan saat memproses transaksi.', 'error');
                    }
                });
            })
            .catch(() => {
                Swal.close();
                Swal.fire('Gagal!', 'Tidak dapat terhubung ke server.', 'error');
            });
    });
});

// ❌ SweetAlert konfirmasi pembatalan
const cancelEndpoint = '{{ url('/pemesanan/batalkan') }}';

document.querySelectorAll('.btn-cancel').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        Swal.fire({
            title: 'Batalkan Pemesanan?',
            text: 'Pesanan ini akan dibatalkan dan tidak bisa dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Tidak',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#aaa'
        }).then(result => {
            if (!result.isConfirmed) {
                return;
            }

            fetch(`${cancelEndpoint}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('cancel_failed');
                }
                return response.json();
            })
            .then(() => Swal.fire('Dibatalkan!', 'Pemesanan berhasil dibatalkan.', 'success')
                .then(() => location.reload()))
            .catch(() => Swal.fire('Gagal!', 'Terjadi kesalahan saat membatalkan.', 'error'));
        });
    });
});
</script>
@endsection
