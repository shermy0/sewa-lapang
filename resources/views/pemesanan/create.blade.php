@extends('layouts.sidebar')

@section('title', 'Pesan Lapangan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pesan.css') }}">

<div class="container py-5">
    <div class="text-center mb-4">
        <img src="https://cdn-icons-png.flaticon.com/512/2920/2920253.png" width="100" alt="Booking Icon">
        <h2 class="fw-bold mt-3" style="color: var(--green);">Pesan Lapangan</h2>
        <p class="text-muted">Pilih jadwal dan lakukan pembayaran secara mudah dan cepat.</p>
    </div>

    {{-- ======================== FORM PEMESANAN BARU ======================== --}}
    <div class="card-booking">
        <h4 class="fw-bold mb-3">{{ $lapangan->nama_lapangan }}</h4>
        <p class="fw-semibold mb-4">
            Harga per jam: <span style="color: var(--green);">
                Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}
            </span>
        </p>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="mb-3">
            <label for="jadwal_id" class="form-label fw-semibold">Pilih Jadwal Tersedia</label>
            <select name="jadwal_id" id="jadwal_id" class="form-select select-jadwal" required>
                <option value="">-- Pilih Jadwal --</option>
                @foreach($jadwalTersedia as $j)
                    <option value="{{ $j->id }}">
                        {{ \Carbon\Carbon::parse($j->tanggal)->format('d M Y') }}
                        ({{ $j->jam_mulai }} - {{ $j->jam_selesai }})
                    </option>
                @endforeach
            </select>
        </div>

        <div id="summary" class="booking-summary" style="display:none;">
            <strong>Ringkasan Pemesanan:</strong><br>
            Lapangan: {{ $lapangan->nama_lapangan }} <br>
            Jadwal: <span id="summary-jadwal" class="text-success"></span><br>
            Total Bayar: 
            <span class="fw-bold" style="color: var(--gold);">
                Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}
            </span>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button id="pay-button" class="btn btn-green px-4">Pesan & Bayar</button>
            <a href="{{ url()->previous() }}" class="btn btn-outline px-4">Batal</a>
        </div>
    </div>

    {{-- ======================== PEMESANAN PENDING ======================== --}}
    @if($pemesananPending)
    <div class="pending-box mt-5 p-4 rounded shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>⚠️ Kamu belum menyelesaikan pembayaran!</strong><br>
                Jadwal: 
                <span class="text-success">
                    {{ \Carbon\Carbon::parse($pemesananPending->jadwal->tanggal)->format('d M Y') }}
                    ({{ $pemesananPending->jadwal->jam_mulai }} - {{ $pemesananPending->jadwal->jam_selesai }})
                </span><br>
                Total: <strong>Rp {{ number_format($lapangan->harga_per_jam, 0, ',', '.') }}</strong><br>
                <span id="countdown" class="text-danger fw-semibold"></span>
            </div>

            <div class="d-flex flex-column gap-2">
                <button id="resume-payment" class="btn btn-green">Lanjutkan Bayar</button>
                <form action="{{ route('pemesanan.batalkan', $pemesananPending->id) }}" method="POST" onsubmit="return confirm('Yakin batalkan pemesanan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Batalkan</button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- MIDTRANS SNAP --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

<script>
const jadwalSelect = document.getElementById('jadwal_id');
const summary = document.getElementById('summary');
const summaryText = document.getElementById('summary-jadwal');

jadwalSelect.addEventListener('change', () => {
    if (jadwalSelect.value) {
        summary.style.display = 'block';
        summaryText.innerText = jadwalSelect.options[jadwalSelect.selectedIndex].text;
    } else {
        summary.style.display = 'none';
    }
});

// =================== PESAN & BAYAR BARU ===================
document.getElementById('pay-button').onclick = function() {
    const jadwalId = jadwalSelect.value;
    if (!jadwalId) {
        alert('Pilih jadwal terlebih dahulu!');
        return;
    }

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
                }).then(() => window.location.href = '/penyewa/riwayat');
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
};

// =================== LANJUTKAN PEMBAYARAN PENDING ===================
@if($pemesananPending)
document.getElementById('resume-payment').onclick = function() {
    fetch('/midtrans/token-again/{{ $pemesananPending->id }}')
    .then(res => res.json())
    .then(data => {
        if (data.snap_token) {
            snap.pay(data.snap_token, {
                onSuccess: function(result){
                    fetch('/pemesanan/success/' + data.pemesanan_id, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ result })
                    }).then(() => window.location.href = '/penyewa/riwayat');
                },
                onPending: function(result){
                    alert("Menunggu pembayaran...");
                    window.location.href = '/penyewa/riwayat';
                },
                onError: function(result){
                    alert("Pembayaran gagal!");
                }
            });
        } else {
            alert('Gagal mendapatkan token Midtrans.');
        }
    });
};

// =================== COUNTDOWN PEMBAYARAN ===================
const createdAt = new Date("{{ $pemesananPending->created_at }}");
const deadline = new Date(createdAt.getTime() + 24 * 60 * 60 * 1000);
const countdownEl = document.getElementById('countdown');

const timer = setInterval(() => {
    const now = new Date();
    const diff = deadline - now;

    if (diff <= 0) {
        clearInterval(timer);
        countdownEl.innerHTML = "⛔ Waktu pembayaran sudah habis!";
        document.getElementById('resume-payment').disabled = true;
    } else {
        const h = Math.floor(diff / (1000 * 60 * 60));
        const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const s = Math.floor((diff % (1000 * 60)) / 1000);
        countdownEl.innerHTML = `Sisa waktu pembayaran: ${h}j ${m}m ${s}d`;
    }
}, 1000);
@endif
</script>
@endsection