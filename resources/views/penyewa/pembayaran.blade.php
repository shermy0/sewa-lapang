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
                @if($p->permintaanPerubahan)
                    @if($p->permintaanPerubahan->status === 'menunggu')
                        <div class="alert alert-warning py-2 px-3 small mb-2">
                            <i class="fa-solid fa-hourglass-half me-1"></i>
                            Menunggu persetujuan perubahan jadwal / section...
                        </div>
                    @elseif($p->permintaanPerubahan->status === 'disetujui')
                        <div class="alert alert-success py-2 px-3 small mb-2">
                            <i class="fa-solid fa-check-circle me-1"></i>
                            Perubahan jadwal / section telah disetujui dan diperbarui.
                        </div>
                    @elseif($p->permintaanPerubahan->status === 'ditolak')
                        <div class="alert alert-danger py-2 px-3 small mb-2">
                            <i class="fa-solid fa-xmark me-1"></i>
                            Permintaan perubahan ditolak.
                        </div>
                    @endif
                @endif

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
                                   data-created-at="{{ $p->created_at->format('c') }}">
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

                                @if(!$p->permintaanPerubahan)
                                    <button class="btn btn-outline-primary btn-sm px-3"
                                            onclick="ajukanPerubahan({{ $p->id }}, {{ $p->lapangan->id }})">
                                        <i class="fa-solid fa-arrows-rotate me-1"></i> Ajukan Perubahan
                                    </button>
                                @else
                                    <button class="btn btn-outline-secondary btn-sm px-3"
                                            onclick="lihatDetailPerubahan({{ $p->permintaanPerubahan->id }})">
                                        <i class="fa-solid fa-eye me-1"></i> Lihat Detail Permintaan
                                    </button>
                                @endif
                            </div>
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
// Countdown pembayaran
document.querySelectorAll('[data-countdown]').forEach(target => {
    const createdAt = new Date(target.dataset.createdAt);
    const deadline = new Date(createdAt.getTime() + 24 * 60 * 60 * 1000);
    const tick = () => {
        const now = new Date();
        const diff = deadline - now;
        if (diff <= 0) {
            target.textContent = '⛔ Waktu pembayaran sudah habis.';
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
                        }).then(() => window.location.reload());
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
            if (result.isConfirmed) {
                fetch(`/pemesanan/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(() => Swal.fire('Dibatalkan!', 'Pemesanan berhasil dibatalkan.', 'success')
                    .then(() => location.reload()))
                .catch(() => Swal.fire('Gagal!', 'Terjadi kesalahan saat membatalkan.', 'error'));
            }
        });
    });
});
// 🟢 Ajukan Perubahan (SweetAlert)
function ajukanPerubahan(pemesananId, lapanganId) {
    Swal.fire({
        title: 'Ajukan Perubahan Jadwal / Section',
        html: `
            <div id="sectionList" class="row g-2"></div>
            <div id="jadwalList" class="mt-3"></div>
            <textarea id="alasan" class="form-control mt-3" placeholder="Alasan pengajuan (opsional)"></textarea>
        `,
        confirmButtonText: 'Kirim Permintaan',
        confirmButtonColor: '#41A67E',
        showCancelButton: true,
        cancelButtonText: 'Batal',

        // ✅ Tambahan ini biar textarea bisa diketik
        focusConfirm: false,

        didOpen: () => {
            fetch(`/lapangan/${lapanganId}/sections`)
                .then(res => res.json())
                .then(data => {
                    const list = document.getElementById('sectionList');
                    list.innerHTML = data.map(s => `
                        <div class="col-md-4">
                            <div class="section-card" data-id="${s.id}" 
                                style="border:2px solid #ddd;padding:10px;border-radius:8px;cursor:pointer;">
                                <strong>${s.nama_section}</strong>
                            </div>
                        </div>`).join('');

                    document.querySelectorAll('.section-card').forEach(card => {
                        card.addEventListener('click', function() {
                            document.querySelectorAll('.section-card').forEach(c => c.style.borderColor='#ddd');
                            this.style.borderColor='#41A67E';
                            const sectionId = this.dataset.id;
                            fetch(`/jadwal/${sectionId}`)
                                .then(res => res.json())
                                .then(jadwals => {
                                    const jadwalList = document.getElementById('jadwalList');
                                    jadwalList.innerHTML = `
                                        <h6 class="mt-3">Pilih Jadwal Baru</h6>
                                        <div class="row g-2">
                                            ${jadwals.map(j => `
                                                <div class="col-md-4">
                                                    <div class="jadwal-item ${j.tersedia ? 'available' : 'unavailable'}" 
                                                        data-id="${j.id}"
                                                        style="padding:10px;border-radius:8px;border:2px solid #eee;
                                                               cursor:${j.tersedia ? 'pointer' : 'not-allowed'};
                                                               background:${j.tersedia ? '#fff' : '#f3f3f3'};">
                                                        ${j.jam_mulai} - ${j.jam_selesai}<br>
                                                        <small>${new Date(j.tanggal).toLocaleDateString('id-ID')}</small>
                                                    </div>
                                                </div>`).join('')}
                                        </div>`;
                                    document.querySelectorAll('.jadwal-item.available').forEach(item => {
                                        item.addEventListener('click', function() {
                                            document.querySelectorAll('.jadwal-item').forEach(i => i.style.borderColor='#eee');
                                            this.style.borderColor='#41A67E';
                                            window.selectedSection = sectionId;
                                            window.selectedJadwal = this.dataset.id;
                                        });
                                    });
                                });
                        });
                    });
                });
        },
        preConfirm: () => {
            const alasan = document.getElementById('alasan').value;
            if (!window.selectedJadwal || !window.selectedSection) {
                Swal.showValidationMessage('Pilih section dan jadwal terlebih dahulu!');
                return false;
            }
            return { section_baru_id: window.selectedSection, jadwal_baru_id: window.selectedJadwal, alasan };
        }
    }).then(result => {
        if (result.isConfirmed) {
            fetch(`/pemesanan/${pemesananId}/ajukan-perubahan`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(result.value)
            })
            .then(res => res.json())
            .then(() => Swal.fire({
                icon: 'success',
                title: 'Permintaan dikirim!',
                text: 'Menunggu persetujuan pemilik lapangan.',
                confirmButtonColor: '#41A67E'
            }).then(() => location.reload()));
        }
    });
}

// 🔍 Lihat detail permintaan
function lihatDetailPerubahan(permintaanId) {
    fetch(`/permintaan-perubahan/${permintaanId}`)
        .then(res => res.json())
        .then(data => {
            const jadwalBaru = data.jadwal_baru ? `
                ${new Date(data.jadwal_baru.tanggal).toLocaleDateString('id-ID')} 
                (${data.jadwal_baru.jam_mulai} - ${data.jadwal_baru.jam_selesai})
            ` : '-';
            Swal.fire({
                title: '<i class="fa-solid fa-arrows-rotate me-1 text-success"></i> Detail Permintaan Perubahan',
                html: `
                    <div class="text-start">
                        <p><strong>Status:</strong> 
                            ${data.status === 'menunggu' 
                                ? '<span class="badge bg-warning text-dark">Menunggu Persetujuan</span>' 
                                : data.status === 'disetujui' 
                                ? '<span class="badge bg-success">Disetujui</span>' 
                                : '<span class="badge bg-danger">Ditolak</span>'}
                        </p>
                        <p><strong>Section Baru:</strong> ${data.section_baru?.nama_section ?? '-'}</p>
                        <p><strong>Jadwal Baru:</strong> ${jadwalBaru}</p>
                        <p><strong>Alasan:</strong> ${data.alasan ?? '-'}</p>
                    </div>
                `,
                showCancelButton: true,
                cancelButtonText: 'Tutup',
                showConfirmButton: data.status === 'menunggu',
                confirmButtonText: 'Batalkan',
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) batalkanPermintaan(data.id);
            });
        });
}

// 🗑 Batalkan permintaan
function batalkanPermintaan(id) {
    Swal.fire({
        title: 'Batalkan Permintaan?',
        text: 'Permintaan perubahan ini akan dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Batalkan',
        confirmButtonColor: '#d33'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(`/permintaan-perubahan/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(() => Swal.fire('Dibatalkan!', 'Permintaan telah dibatalkan.', 'success')
                .then(() => location.reload()));
        }
    });
}
</script>
@endsection
