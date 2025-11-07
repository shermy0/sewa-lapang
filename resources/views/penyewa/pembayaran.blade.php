@extends('layouts.sidebar')

@section('title', 'Menunggu Pembayaran')

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">
<div class="container py-4">
    <div class="penyewa-page-header">
        <div>
            <p class="eyebrow">Status Pembayaran</p>
            <h1>Menunggu Pembayaran</h1>
            <p class="subtitle">Segera selesaikan pembayaran agar jadwal bermainmu tetap aman.</p>
        </div>
        <span class="page-pill">
            <i class="fa-solid fa-clock-rotate-left me-1"></i>
            {{ $belumDibayar->count() }} pesanan
        </span>
    </div>

    <div class="payment-grid">
        @forelse($belumDibayar as $p)
        <div class="payment-card shadow-sm">
            <div class="payment-card__head">
                <div>
                    <p class="label">Lapangan</p>
                    <h5>{{ $p->lapangan->nama_lapangan }}</h5>
                </div>
                <span class="status-chip status-chip--warning">
                    <i class="fa-solid fa-coins me-1"></i> Belum Dibayar
                </span>
            </div>
            <div class="payment-card__body">
                <div class="info-row">
                    <span><i class="fa-regular fa-calendar me-2 text-success"></i>Tanggal</span>
                    <strong>{{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}</strong>
                </div>
                <div class="info-row">
                    <span><i class="fa-regular fa-clock me-2 text-success"></i>Jam Main</span>
                    <strong>{{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }}</strong>
                </div>
                <div class="info-row">
                    <span><i class="fa-solid fa-wallet me-2 text-success"></i>Total</span>
                    <strong>Rp{{ number_format($p->total_harga, 0, ',', '.') }}</strong>
                </div>
            </div>

            <p class="countdown-label text-danger" data-countdown data-created-at="{{ $p->created_at->format('c') }}" id="countdown-{{ $p->id }}"></p>

            <div class="payment-card__actions">
                <button class="btn btn-success flex-grow-1 btn-pay-again" data-id="{{ $p->id }}">
                    <i class="fa-solid fa-credit-card me-1"></i> Bayar Sekarang
                </button>
                <form action="{{ route('pemesanan.batalkan', $p->id) }}" method="POST" onsubmit="return confirm('Yakin batalkan pemesanan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fa-solid fa-xmark me-1"></i> Batalkan
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="empty-state-card">
            <i class="fa-solid fa-clipboard-check"></i>
            <h5>Semua pesanan sudah dibayar</h5>
            <p>Nikmati sesi bermainmu! Pesanan baru akan tampil di sini.</p>
        </div>
        @endforelse
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const countdownTargets = document.querySelectorAll('[data-countdown]');

    countdownTargets.forEach(target => {
        const createdAt = new Date(target.dataset.createdAt);
        const deadline = new Date(createdAt.getTime() + 24 * 60 * 60 * 1000);

        const tick = () => {
            const now = new Date();
            const diff = deadline.getTime() - now.getTime();

            if (diff <= 0) {
                target.textContent = 'Waktu pembayaran sudah habis.';
                target.classList.add('text-muted');
                return;
            }

            const h = Math.floor(diff / (1000 * 60 * 60));
            const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const s = Math.floor((diff % (1000 * 60)) / 1000);
            target.textContent = `Sisa waktu pembayaran: ${h}j ${m}m ${s}d`;

            setTimeout(tick, 1000);
        };

        tick();
    });

    document.querySelectorAll('.btn-pay-again').forEach(btn => {
        btn.addEventListener('click', function() {
            const pemesananId = this.dataset.id;
            fetch('/midtrans/token-again/' + pemesananId)
            .then(res => res.json())
            .then(data => {
                if(data.error){
                    alert("Error: " + data.error);
                    return;
                }
                snap.pay(data.snap_token, { 
                    onSuccess: function(result){
                        fetch('/pemesanan/success/' + data.pemesanan_id, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ result })
                        })
                        .then(() => window.location.reload())
                        .catch(err => console.error(err));
                    },
                    onPending: function(){
                        alert("Menunggu pembayaran...");
                        window.location.reload();
                    },
                    onError: function(result){
                        alert("Pembayaran gagal!");
                        console.error(result);
                    }
                });
            })
            .catch(err => {
                console.error(err);
                alert("Terjadi kesalahan saat memproses pembayaran.");
            });
        });
    });
});
</script>
@endsection
