@extends('layouts.sidebar')

@section('title', 'Pesan Lapangan')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">Pesan {{ $lapangan->nama_lapangan }}</h2>
    <div class="mb-3">
        <p class="fw-bold">Harga per jam: Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}</p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="mb-3">
        <label for="jadwal_id" class="form-label">Pilih Jadwal Tersedia</label>
        <select name="jadwal_id" id="jadwal_id" class="form-select" required>
            <option value="">-- Pilih Jadwal --</option>
            @foreach($jadwalTersedia as $j)
                <option value="{{ $j->id }}">
                    {{ \Carbon\Carbon::parse($j->tanggal)->format('d M Y') }}
                    ({{ $j->jam_mulai }} - {{ $j->jam_selesai }})
                </option>
            @endforeach
        </select>
    </div>
    <button id="pay-button" class="btn btn-success px-4">Pesan & Bayar</button>
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Batal</a>
</div>

{{-- MIDTRANS SNAP --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script>
document.getElementById('pay-button').onclick = function() {
    const jadwalId = document.getElementById('jadwal_id').value;
    if (!jadwalId) {
        alert('Pilih jadwal terlebih dahulu!');
        return;
    }

    // 🔹 Request Snap Token dari server
fetch('{{ route("midtrans.token") }}', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        lapangan_id: {{ $lapangan->id }},
        jadwal_id: jadwalId
    })
})
.then(res => res.json())
.then(data => {
    snap.pay(data.snap_token, {
        onSuccess: function(result){
            fetch('/pemesanan/success/' + data.pemesanan_id, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ result })
            }).then(() => {
                window.location.href = '/penyewa/riwayat';
            });
        },
        onPending: function(result){
            alert("Menunggu pembayaran...");
            window.location.href = '/penyewa/riwayat';
        },
        onError: function(result){
            alert("Pembayaran gagal!");
        }
    });
});

}
</script>
@endsection