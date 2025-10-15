@extends('layouts.sidebar')

@section('title', 'Pesan Lapangan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pesan.css') }}">
{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '{{ session("error") }}',
                    confirmButtonColor: '#41A67E'
                });
            </script>
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
                <form id="cancel-form" action="{{ route('pemesanan.batalkan', $pemesananPending->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" id="cancel-button" class="btn btn-danger">Batalkan</button>
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
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Jadwal!',
            text: 'Silakan pilih jadwal terlebih dahulu sebelum melanjutkan.',
            confirmButtonColor: '#41A67E'
        });
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
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Berhasil!',
                    text: 'Transaksi kamu berhasil diselesaikan.',
                    confirmButtonColor: '#41A67E'
                }).then(() => {
                    fetch('/pemesanan/success/' + data.pemesanan_id, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ result })
                    }).then(() => window.location.href = '/penyewa/tiket');
                });
            },
            onPending: function(result){
                Swal.fire({
                    icon: 'info',
                    title: 'Menunggu Pembayaran',
                    text: 'Silakan selesaikan pembayaranmu sebelum waktu habis.',
                    confirmButtonColor: '#41A67E'
                }).then(() => window.location.href = '/penyewa/pembayaran');
            },
            onError: function(result){
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran Gagal!',
                    text: 'Terjadi kesalahan saat memproses transaksi.',
                    confirmButtonColor: '#41A67E'
                });
            }
        });
    })
    .catch(() => {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Tidak bisa mendapatkan token pembayaran.',
            confirmButtonColor: '#41A67E'
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Berhasil!',
                        text: 'Transaksi kamu berhasil diselesaikan.',
                        confirmButtonColor: '#41A67E'
                    }).then(() => {
                        fetch('/pemesanan/success/' + data.pemesanan_id, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ result })
                        }).then(() => window.location.href = '/penyewa/tiket');
                    });
                },
                onPending: function(result){
                    Swal.fire({
                        icon: 'info',
                        title: 'Menunggu Pembayaran',
                        text: 'Silakan selesaikan pembayaranmu.',
                        confirmButtonColor: '#41A67E'
                    }).then(() => {
                    window.location.href = window.location.pathname;
                    });
                },
                onError: function(result){
                    Swal.fire({
                        icon: 'error',
                        title: 'Pembayaran Gagal!',
                        text: 'Terjadi kesalahan saat memproses transaksi.',
                        confirmButtonColor: '#41A67E'
                    });
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Token Midtrans tidak ditemukan.',
                confirmButtonColor: '#41A67E'
            });
        }
    });
};

// =================== KONFIRMASI BATALKAN PEMBAYARAN ===================
document.getElementById('cancel-button').addEventListener('click', function() {
    Swal.fire({
        title: 'Yakin ingin membatalkan?',
        text: "Pemesanan ini akan dihapus dan tidak bisa dikembalikan.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, batalkan',
        cancelButtonText: 'Tidak jadi'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('cancel-form').submit();
        }
    });
});

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
