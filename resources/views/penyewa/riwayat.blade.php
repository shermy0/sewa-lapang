@extends('layouts.sidebar')

@section('title', 'Riwayat Pemesanan')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">Riwayat Pemesanan</h2>

    {{-- Belum Dibayar --}}
    <h4 class="mt-4">Belum Dibayar</h4>
    <div class="row">
        @foreach($belumDibayar as $p)
        <div class="col-md-4 mb-3">
            <div class="card p-3 border-warning">
                <p>Lapangan: {{ $p->lapangan->nama_lapangan }}</p>
                <p>Jadwal: {{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}
                   ({{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }})</p>
                <p>Status: <span class="badge bg-warning text-dark">Belum Dibayar</span></p>
                <button class="btn btn-success btn-pay-again mt-2" data-id="{{ $p->id }}">Bayar</button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Sudah Dibayar --}}
    <h4 class="mt-4">Sudah Dibayar</h4>
    <div class="row">
        @foreach($sudahDibayar as $p)
        <div class="col-md-4 mb-3">
            <div class="card p-3 border-success">
                <p>Lapangan: {{ $p->lapangan->nama_lapangan }}</p>
                <p>Jadwal: {{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}
                   ({{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }})</p>
                <p>Status: <span class="badge bg-success">Dibayar</span></p>
                <p>Kode Tiket: {{ $p->kode_tiket }}</p>
                <div class="mt-2 text-center">
                    {!! DNS1D::getBarcodeHTML($p->kode_tiket, 'C128') !!}
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- MIDTRANS SNAP --}}
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