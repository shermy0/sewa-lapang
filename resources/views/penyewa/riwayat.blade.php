@extends('layouts.sidebar')

@section('title', 'Riwayat Pemesanan')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">Riwayat Pemesanan</h2>

    {{-- ================= BELUM DIBAYAR ================= --}}
    <h4 class="mt-4">Belum Dibayar</h4>
    <div class="row">
        @forelse($belumDibayar as $p)
        <div class="col-md-4 mb-3">
            <div class="card p-3 border-warning position-relative">
                <p><strong>Lapangan:</strong> {{ $p->lapangan->nama_lapangan }}</p>
                <p><strong>Jadwal:</strong> 
                    {{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}
                    ({{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }})
                </p>
                <p><strong>Status:</strong> 
                    <span class="badge bg-warning text-dark">Belum Dibayar</span>
                </p>

                {{-- COUNTDOWN --}}
                <p class="text-danger fw-semibold mb-1" id="countdown-{{ $p->id }}"></p>

                <div class="d-flex justify-content-between mt-2">
                    <button class="btn btn-success btn-pay-again" data-id="{{ $p->id }}">Bayar Sekarang</button>
                    <form action="{{ route('pemesanan.batalkan', $p->id) }}" method="POST" onsubmit="return confirm('Yakin batalkan pemesanan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Batalkan</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- COUNTDOWN SCRIPT --}}
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const createdAt = new Date("{{ $p->created_at }}");
            const deadline = new Date(createdAt.getTime() + 24 * 60 * 60 * 1000);
            const countdownEl = document.getElementById("countdown-{{ $p->id }}");

            const timer = setInterval(() => {
                const now = new Date();
                const diff = deadline - now;

                if (diff <= 0) {
                    clearInterval(timer);
                    countdownEl.innerHTML = "⛔ Waktu pembayaran sudah habis!";
                } else {
                    const h = Math.floor(diff / (1000 * 60 * 60));
                    const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const s = Math.floor((diff % (1000 * 60)) / 1000);
                    countdownEl.innerHTML = `Sisa waktu pembayaran: ${h}j ${m}m ${s}d`;
                }
            }, 1000);
        });
        </script>
        @empty
        <p class="text-muted">Tidak ada pesanan menunggu pembayaran.</p>
        @endforelse
    </div>

    {{-- ================= SUDAH DIBAYAR ================= --}}
    <h4 class="mt-4">Sudah Dibayar</h4>
    <div class="row">
        @forelse($sudahDibayar as $p)
        <div class="col-md-4 mb-3">
            <div class="card p-3 border-success">
                <p><strong>Lapangan:</strong> {{ $p->lapangan->nama_lapangan }}</p>
                <p><strong>Jadwal:</strong> 
                    {{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}
                    ({{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }})
                </p>
                <p><strong>Status:</strong> 
                    <span class="badge bg-success">Dibayar</span>
                </p>
                <p><strong>Kode Tiket:</strong> {{ $p->kode_tiket }}</p>
                <div class="mt-2 text-center">
                    {!! DNS1D::getBarcodeHTML($p->kode_tiket, 'C128') !!}
                </div>
            </div>
        </div>
        @empty
        <p class="text-muted">Belum ada pesanan yang dibayar.</p>
        @endforelse
    </div>
</div>

{{-- ================= MIDTRANS SNAP ================= --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
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
                    onPending: function(result){
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