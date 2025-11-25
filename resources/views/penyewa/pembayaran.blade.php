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

    {{-- BELUM PERNAH AJUKAN --}}
    @if(!$p->permintaanPerubahan)
        <button class="btn btn-warning btn-sm px-3"
                onclick="ajukanPerubahan({{ $p->id }}, {{ $p->lapangan->id }})">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Ajukan Perubahan
        </button>

    {{-- MASIH MENUNGGU --}}
    @elseif($p->permintaanPerubahan->status === 'menunggu')
        <button class="btn btn-outline-primary btn-sm px-3"
                onclick="lihatDetailPerubahan({{ $p->permintaanPerubahan->id }})">
            <i class="fa-solid fa-hourglass-half me-1"></i> Menunggu Persetujuan
        </button>

    {{-- DITOLAK ATAU DISETUJUI → BOLEH AJUKAN LAGI --}}
    @else
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm px-3"
                    onclick="lihatDetailPerubahan({{ $p->permintaanPerubahan->id }})">
                <i class="fa-solid fa-eye me-1"></i> Lihat Detail
            </button>

            <button class="btn btn-warning btn-sm px-3"
                    onclick="ajukanPerubahan({{ $p->id }}, {{ $p->lapangan->id }})">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Ajukan Lagi
            </button>
        </div>
    @endif

                                {{-- @if(!$p->permintaanPerubahan)
                                    <button class="btn btn-outline-primary btn-sm px-3"
                                            onclick="ajukanPerubahan({{ $p->id }}, {{ $p->lapangan->id }})">
                                        <i class="fa-solid fa-arrows-rotate me-1"></i> Ajukan Perubahan
                                    </button>
                                @else
                                    <button class="btn btn-outline-secondary btn-sm px-3"
                                            onclick="lihatDetailPerubahan({{ $p->permintaanPerubahan->id }})">
                                        <i class="fa-solid fa-eye me-1"></i> Lihat Detail Permintaan
                                    </button>
                                @endif --}}
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
    window.userOrders = @json($userOrders);
    window.semuaPemesananUser = @json($semuaPemesananUser);
</script>

<script>
// Countdown pembayaran (mengikuti batas 20 menit di backend)
document.querySelectorAll('[data-countdown]').forEach(target => {
    const createdAt = new Date(target.dataset.createdAt);
    if (Number.isNaN(createdAt.getTime())) {
        target.textContent = '';
        return;
    }

    const LIMIT_MINUTES = 20;
    const deadline = createdAt.getTime() + LIMIT_MINUTES * 60 * 1000;

    const tick = () => {
        const now = Date.now();
        const diffMs = deadline - now;

        if (diffMs <= 0) {
            target.textContent = '⛔ Waktu pembayaran sudah habis.';
            target.classList.add('text-muted');

            const card = target.closest('.ticket-card');
            if (card) {
                card.querySelectorAll('button').forEach(btn => btn.remove());
            }
            return;
        }

        const totalSeconds = Math.floor(diffMs / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;

        target.textContent = `Sisa waktu pembayaran: ${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
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
function ajukanPerubahan(pemesananId, lapanganId) {
    window.selectedSection = null;
    window.selectedJadwal = null;
    window.selectedJadwalBookingStatus = null;

    Swal.fire({
        title: "Ajukan Perubahan Jadwal / Section",
        width: "90%",
        html: `
            <div class="text-start">
                <div class="modal-title-custom"><i class="fa-solid fa-arrows-rotate me-1"></i>Ajukan Perubahan Jadwal / Section</div>
                <div class="mb-3"><label class="modal-label">Pilih Section</label><div id="sectionList" class="row g-2"></div></div>
                <div id="tanggalWrapper" class="mt-3" style="display:none">
                    <label class="modal-label">Pilih Tanggal</label>
                    <input type="date" id="filterTanggal" class="form-control rounded-3" style="max-width:200px;">
                </div>
                <div id="jadwalWrapper" class="mt-3" style="display:none">
                    <label class="modal-label">Pilih Jam</label>
                    <div id="jadwalList" class="row g-2"></div>
                    <div id="jadwalHint" class="mt-2 small text-muted"></div>
                </div>
                <label class="modal-label mt-3">Alasan Pengajuan (opsional)</label>
                <textarea id="alasan" class="form-control p-3" rows="3" placeholder="Contoh: Jadwal bentrok..."></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: "Lanjutkan",
        confirmButtonColor: "#41A67E",
        didOpen: () => {
            fetch(`/sections/${lapanganId}`)
            .then(res => res.json())
            .then(sections => {
                const container = document.getElementById("sectionList");
                container.innerHTML = sections.map(s => `
                    <div class="col-md-3">
                        <div class="section-card card-select" data-id="${s.id}">
                            <b>${s.nama_section}</b>
                        </div>
                    </div>
                `).join('');

                document.querySelectorAll(".section-card").forEach(card => {
                    card.addEventListener('click', function() {
                        document.querySelectorAll(".section-card").forEach(c=>c.classList.remove('active'));
                        this.classList.add('active');
                        window.selectedSection = this.dataset.id;
                        window.selectedJadwal = null;
                        window.selectedJadwalBookingStatus = null;
                        document.getElementById("jadwalList").innerHTML = '';
                        document.getElementById("jadwalWrapper").style.display = 'none';
                        document.getElementById("tanggalWrapper").style.display = 'block';
                    });
                });
            });

            setTimeout(()=> {
                const dateInput = document.getElementById("filterTanggal");
                dateInput?.addEventListener('change', function(){
                    if (window.selectedSection && this.value) loadJam(window.selectedSection, this.value);
                });
            }, 80);
        },
        preConfirm: () => {
            // jika belum pilih jadwal
            if (!window.selectedSection || !window.selectedJadwal) {
                Swal.showValidationMessage("Pilih section, tanggal, dan jadwal dahulu!");
                return false;
            }

            // Jika jadwal available -> kita akan pindah langsung (tanpa permintaan)
            if (window.selectedJadwalBookingStatus === null || window.selectedJadwalBookingStatus === '' || window.selectedJadwalBookingStatus === 'available') {
                // return marker agar then() meng-handle pindah langsung
                return { action: 'pindah_langsung', section_baru_id: window.selectedSection, jadwal_baru_id: window.selectedJadwal, alasan: document.getElementById('alasan').value };
            }

            // Jika jadwal pending -> buat permintaan perubahan (ajukan)
            if (window.selectedJadwalBookingStatus === 'menunggu') {
                return { action: 'ajukan', section_baru_id: window.selectedSection, jadwal_baru_id: window.selectedJadwal, alasan: document.getElementById('alasan').value };
            }

            // Jika dibayar -> jangan
            Swal.showValidationMessage("Jadwal ini sudah dibayar dan tidak bisa diajukan.");
            return false;
        }
    }).then(result => {
        if (!result.isConfirmed) return;

        const value = result.value;

        if (value.action === 'pindah_langsung') {
            // panggil endpoint pindah langsung
            Swal.fire({ title: 'Memindahkan...', didOpen: ()=>Swal.showLoading(), allowOutsideClick:false });
            fetch(`/pemesanan/${pemesananId}/pindah`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ jadwal_baru_id: value.jadwal_baru_id, section_baru_id: value.section_baru_id })
            }).then(async res => {
                Swal.close();
                if (!res.ok) {
                    const err = await res.json().catch(()=>({message:'Gagal'}));
                    return Swal.fire('Gagal', err.error || (err.message ?? 'Terjadi kesalahan'), 'error');
                }
                Swal.fire('Berhasil', 'Jadwal berhasil dipindahkan.', 'success').then(()=> location.reload());
            }).catch(()=> { Swal.close(); Swal.fire('Gagal', 'Terjadi kesalahan koneksi.', 'error'); });
            return;
        }

        if (value.action === 'ajukan') {
            // kirim permintaan perubahan (seperti sekarang)
            fetch(`/permintaan-perubahan/${pemesananId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ section_baru_id: value.section_baru_id, jadwal_baru_id: value.jadwal_baru_id, alasan: value.alasan })
            }).then(async res => {
                if (!res.ok) {
                    const err = await res.json().catch(()=>({error:'Gagal mengirim'}));
                    return Swal.fire('Gagal', err.error || 'Gagal mengirim permintaan.', 'error');
                }
                Swal.fire('Terkirim', 'Permintaan dikirim, menunggu respons pemilik.', 'success').then(()=> location.reload());
            }).catch(()=> Swal.fire('Gagal', 'Tidak dapat mengirim permintaan.', 'error'));
            return;
        }
    });
}

// loadJam: ambil jadwal untuk section, filter per tanggal & beri info booking_status
function loadJam(sectionId, tanggal) {
    fetch(`/jadwal/section/${sectionId}`)
        .then(res => res.json())
        .then(jadwals => {

            const filtered = jadwals.filter(j => {
                if (!j.tanggal) return false;
                const t = j.tanggal.split('T')[0].split(' ')[0];
                return t === tanggal;
            });

            document.getElementById("jadwalWrapper").style.display = "block";
            const container = document.getElementById("jadwalList");

            if (filtered.length === 0) {
                container.innerHTML = `<p class="text-muted">Tidak ada jadwal pada tanggal ini.</p>`;
                return;
            }

            container.innerHTML = filtered.map(j => {

                let status = "available";
                j.is_user = false; // default

                // ==========================
                // CEK — JIKA JADWAL INI MILIK USER
                // ==========================
                if (window.jadwalAktifUser.includes(j.id)) {
                    const pay = window.userOrders[j.id] ?? null;

                    // → jika kadaluarsa → treat as available
                    if (pay === "kadaluarsa" || pay === null) {
                        status = "available";
                        j.is_user = false;
                    }
                    else {
                        j.is_user = true;
                        status = pay === "menunggu"
                            ? "menunggu"
                            : pay === "berhasil"
                                ? "dibayar"
                                : "available";
                    }
                }

                // ==========================
                // CEK STATUS LAIN
                // ==========================
                else {

                    if (j.booking_status === "dibayar") {
                        status = "dibayar";
                    }

                    else if (j.booking_status === "menunggu") {
                        status = "menunggu";
                    }

                    else {
                        status = j.tersedia ? "available" : "menunggu";
                    }
                }

                // Warna class
                const cls =
                    status === "menunggu" ? "pending" :
                    status === "dibayar" ? "paid" :
                    "available";

                // DISABLED STYLE
                const disabledStyle =
                    j.is_user
                        ? "pointer-events:none; opacity:0.85; background:#e8fff2; border-color:#41A67E;"
                        : status === "dibayar"
                            ? "pointer-events:none; opacity:0.55;"
                            : "";

                return `
                    <div class="col-md-3">
                        <div class="jadwal-item ${cls}"
                            data-id="${j.id}"
                            data-booking="${status}"
                            data-is-user="${j.is_user ? 'true' : 'false'}"
                            style="padding:10px;border-radius:8px;border:2px solid #eee;${disabledStyle}">

                            <b>${j.jam_mulai} - ${j.jam_selesai}</b>
                            <div class="small text-muted">

                                ${
                                    j.is_user
                                        ? (() => {
                                            const pay = window.userOrders[j.id];
                                            if (pay === "kadaluarsa") return "Tersedia";
                                            return `<b style="color:#41A67E">Jadwalmu Saat Ini — ${
                                                pay === "berhasil" ? "Sudah Dibayar" : "Belum Dibayar"
                                            }</b>`;
                                        })()
                                        : status === "menunggu"
                                            ? (j.status_pembayaran === "berhasil"
                                                ? "Sedang dibooking (sudah dibayar)"
                                                : "Sedang dibooking (belum bayar)")
                                            : status === "dibayar"
                                                ? "Sudah dibayar"
                                                : "Tersedia"
                                }

                            </div>

                        </div>
                    </div>
                `;

            }).join("");

            // ==========================
            // CLICK LISTENER
            // ==========================
            document.querySelectorAll(".jadwal-item").forEach(item => {
                const booking = item.dataset.booking;
                const isUser = item.dataset.isUser === "true";

                if (isUser || booking === "dibayar") return;

                item.addEventListener("click", function() {
                    document.querySelectorAll(".jadwal-item").forEach(x => x.style.borderColor = "#eee");
                    this.style.borderColor = "#41A67E";

                    window.selectedJadwal = this.dataset.id;
                    window.selectedJadwalBookingStatus = this.dataset.booking;

                    const hint = document.getElementById("jadwalHint");
                    if (window.selectedJadwalBookingStatus === "menunggu") {
                        hint.textContent = "Jadwal ini sedang dibooking tapi belum dibayar. Jika dilanjutkan, permintaan akan dikirim ke pemilik lapangan.";
                        hint.className = "mt-2 small text-warning";
                    } else {
                        hint.textContent = "Jadwal tersedia — akan dipindah langsung tanpa persetujuan pemilik.";
                        hint.className = "mt-2 small text-success";
                    }
                });
            });

        })
        .catch(() => {
            document.getElementById("jadwalList").innerHTML = `<p class="text-muted">Gagal memuat jadwal.</p>`;
        });
}

// lihat detail permintaan (tetap sama sedikit disesuaikan)
function lihatDetailPerubahan(permintaanId) {
    fetch(`/permintaan-perubahan/${permintaanId}`)
        .then(res => res.json())
        .then(data => {
            const jadwalBaru = data.jadwal_baru ? `${new Date(data.jadwal_baru.tanggal).toLocaleDateString('id-ID')} (${data.jadwal_baru.jam_mulai} - ${data.jadwal_baru.jam_selesai})` : '-';
            const expiresText = data.expires_at ? new Date(data.expires_at).toLocaleString('id-ID') : '-';
            Swal.fire({
                title: '<i class="fa-solid fa-arrows-rotate me-1 text-success"></i> Detail Permintaan Perubahan',
                html: `
                    <div class="text-start">
                        <p><strong>Status:</strong> ${data.status === 'menunggu' ? '<span class="badge bg-warning text-dark">Menunggu</span>' : data.status === 'disetujui' ? '<span class="badge bg-success">Disetujui</span>' : '<span class="badge bg-danger">Ditolak</span>'}</p>
                        <p><strong>Section Baru:</strong> ${data.section_baru?.nama_section ?? '-'}</p>
                        <p><strong>Jadwal Baru:</strong> ${jadwalBaru}</p>
                        <p><strong>Alasan:</strong> ${data.alasan ?? '-'}</p>
                        ${data.alasan_internal ? `<p class="text-muted"><strong>Catatan:</strong> ${data.alasan_internal}</p>` : ''}
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
        })
        .catch(()=> Swal.fire('Gagal', 'Tidak dapat memuat detail.', 'error'));
}

function batalkanPermintaan(id) {
    Swal.fire({
        title: 'Batalkan Permintaan?',
        text: 'Permintaan perubahan ini akan dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Batalkan',
        confirmButtonColor: '#d33',
        cancelButtonText: 'Tidak'
    }).then(result => {
        if (!result.isConfirmed) return;
        fetch(`/permintaan-perubahan/${id}`, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        }).then(()=> Swal.fire('Dibatalkan', 'Permintaan telah dibatalkan.', 'success').then(()=> location.reload()))
          .catch(()=> Swal.fire('Gagal', 'Terjadi kesalahan.', 'error'));
    });
}

</script>
@endsection
