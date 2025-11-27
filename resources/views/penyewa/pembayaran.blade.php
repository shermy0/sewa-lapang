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

{{-- Tombol pindah lapang --}}
@if($p->status_scan !== 'sudah_scan')
    <button class="btn btn-warning btn-sm px-3"
            onclick="pindahLapang({{ $p->id }}, {{ $p->lapangan->id }})">
        <i class="fa-solid fa-arrows-rotate me-1"></i> Pindah Lapang
    </button>
@endif                            </div>
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
// Countdown pembayaran
document.querySelectorAll('[data-countdown]').forEach(target => {
    const expiresAt = new Date(target.dataset.expiresAt).getTime();

    const tick = () => {
        const now = Date.now();
        const diff = expiresAt - now;

        if (diff <= 0) {
            target.textContent = '⛔ Waktu pembayaran sudah habis.';
            target.classList.add('text-muted');

            const card = target.closest('.ticket-card');
            if (card) {
                card.querySelectorAll('button').forEach(btn => btn.remove());
            }

            return;
        }

        const m = Math.floor(diff / 60000);
        const s = Math.floor((diff % 60000) / 1000);

        target.textContent = `Sisa waktu pembayaran: ${m}m ${s}d`;

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

<script>
window.jadwalAktifUser = @json($semuaPemesananUser->pluck('jadwal_id'));
window.userOrders = @json($userOrders);
/*
  Logic utama:
  - loadJam() mengembalikan objek jadwal yang berisi booking_status:
    null/undefined/'' => available (tersedia)
    'menunggu' => dibooking tapi belum bayar (pending_payment)
    'dibayar' => paid
  - jika user pilih jadwal available -> lakukan pindah langsung (PATCH)
  - jika pilih jadwal pending -> lakukan ajukan permintaan (POST ke permintaan-perubahan)
  - jika paid -> tidak bisa dipilih
*/

document.querySelectorAll('#scanTabs .nav-link').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('#scanTabs .nav-link').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('.ticket-card').forEach(card => {
            if (filter === 'all' || card.dataset.status === filter) card.style.display = 'block';
            else card.style.display = 'none';
        });
    });
});

function pindahLapang(pemesananId, lapanganId) {
    window.selectedSection = null;
    window.selectedJadwal = null;

    Swal.fire({
        title: "Pindah Lapang",
        width: "90%",
        html: `
            <div class="text-start">
                <div class="modal-title-custom">
                    <i class="fa-solid fa-arrows-rotate me-1"></i>Pindah Lapang
                </div>

                <div class="mb-3">
                    <label class="modal-label">Pilih Section</label>
                    <div id="sectionList" class="row g-2"></div>
                </div>

                <div id="tanggalWrapper" class="mt-3" style="display:none">
                    <label class="modal-label">Pilih Tanggal</label>
                    <input type="date" id="filterTanggal" class="form-control rounded-3" style="max-width:200px;">
                </div>

                <div id="jadwalWrapper" class="mt-3" style="display:none">
                    <label class="modal-label">Pilih Jam</label>
                    <div id="jadwalList" class="row g-2"></div>
                </div>

                <div class="mt-3">
                    <label class="modal-label">Alasan Pindah</label>
                    <textarea id="alasanPindah" class="form-control" placeholder="Tulis alasan pindah..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: "Pindahkan",
        confirmButtonColor: "#41A67E",
        preConfirm: () => {
            const alasan = document.getElementById('alasanPindah').value.trim();
            if (!window.selectedSection || !window.selectedJadwal) {
                Swal.showValidationMessage("Pilih section, tanggal, dan jam terlebih dahulu.");
                return false;
            }
            if (!alasan) {
                Swal.showValidationMessage("Tulis alasan pindah.");
                return false;
            }

            return {
                section_baru_id: window.selectedSection,
                jadwal_baru_id: window.selectedJadwal,
                alasan: alasan
            };
        },
        didOpen: () => {
            // LOAD SECTION
            fetch(`/sections/${lapanganId}`)
            .then(res=>res.json())
            .then(data=>{
                document.getElementById("sectionList").innerHTML =
                    data.map(s => `
                        <div class="col-md-3">
                            <div class="section-card card-select" data-id="${s.id}">
                                <b>${s.nama_section}</b>
                            </div>
                        </div>
                    `).join('');

                document.querySelectorAll(".section-card").forEach(card => {
                    card.onclick = function() {
                        document.querySelectorAll(".section-card").forEach(c=>c.classList.remove("active"));
                        this.classList.add("active");

                        window.selectedSection = this.dataset.id;
                        window.selectedJadwal = null;

                        document.getElementById("jadwalWrapper").style.display = 'none';
                        document.getElementById("tanggalWrapper").style.display = 'block';
                    };
                });
            });

            // TANGGAL → LOAD JAM
            setTimeout(()=>{
                const tgl = document.getElementById("filterTanggal");
                if (!tgl) return;
                tgl.addEventListener('change', ()=> {
                    if (window.selectedSection && tgl.value) loadJam(window.selectedSection, tgl.value);
                });
            }, 100);
        }
    }).then(result=>{
        if (!result.isConfirmed) return;

        Swal.fire({
            title: "Memindahkan...",
            allowOutsideClick: false,
            didOpen: ()=> Swal.showLoading()
        });

        fetch(`/pemesanan/${pemesananId}/pindah`, {
            method: "PATCH",
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                jadwal_baru_id: result.value.jadwal_baru_id,
                section_baru_id: result.value.section_baru_id,
                alasan: result.value.alasan
            })
        }).then(async res=>{
            if (!res.ok) {
                const err = await res.json().catch(()=>({}));
                return Swal.fire("Gagal", err.error ?? "Terjadi kesalahan", "error");
            }

            Swal.fire("Berhasil", "Jadwal berhasil dipindahkan!", "success")
                .then(()=> location.reload());
        });
    });
}

function loadJam(sectionId, tanggal) {
    fetch(`/jadwal/section/${sectionId}`)
        .then(res => res.json())
        .then(jadwals => {

            const filtered = jadwals.filter(j => {
                if (!j.tanggal) return false;
                const t = j.tanggal.split('T')[0].split(' ')[0];
                return t === tanggal;
            });

            const container = document.getElementById("jadwalList");
            container.innerHTML = "";
            document.getElementById("jadwalWrapper").style.display = "block";

            if (filtered.length === 0) {
                container.innerHTML = `<p class="text-muted">Tidak ada jadwal pada tanggal ini.</p>`;
                return;
            }

            filtered.forEach(j => {
                let status = "available";
                let isUser = false;

                // ===============================
                // CEK: JADWAL MILIK USER
                // ===============================
                if (window.jadwalAktifUser.includes(j.id)) {
                    const pay = window.userOrders[j.id] ?? null;

                    if (pay === "kadaluarsa" || pay === null) {
                        status = "available";
                        isUser = false;
                    } else {
                        isUser = true;
                        status = (pay === "menunggu" || pay === "pending") ? "menunggu" : "dibayar";
                    }
                }
                // ===============================
                // CEK: JADWAL ORANG LAIN
                // ===============================
                else {
                    if (j.booking_status === "dibayar") status = "dibayar";
                    else if (j.booking_status === "menunggu") status = "menunggu";
                    else status = j.tersedia ? "available" : "menunggu";
                }

                // ===============================
                // CSS & DISABLE LOGIC
                // ===============================
                const cls = status === "menunggu" ? "pending" :
                            status === "dibayar" ? "paid" : "available";

                const disabledStyle = (isUser && status !== "available")
                    ? "pointer-events:none; opacity:0.85; background:#e8fff2; border-color:#41A67E;"
                    : (status === "dibayar" || status === "menunggu") // semua milik orang lain
                        ? "pointer-events:none; opacity:0.55;"
                        : ""; // available → aktif

                // ===============================
                // TEXT STATUS
                // ===============================
                let statusText = "Tersedia";

                if (isUser) {
                    const pay = window.userOrders[j.id];
                    if (pay === "menunggu" || pay === "pending") statusText = `<b style="color:#41A67E">Jadwalmu (Belum Dibayar)</b>`;
                    else if (pay === "berhasil") statusText = `<b style="color:#41A67E">Jadwalmu (Sudah Dibayar)</b>`;
                } else {
                    if (status === "menunggu") statusText = "Sedang dibooking (belum bayar)";
                    else if (status === "dibayar") statusText = "Sedang dibooking (sudah dibayar)";
                }

                container.innerHTML += `
                    <div class="col-md-3">
                        <div class="jadwal-item ${cls}"
                             data-id="${j.id}"
                             data-booking="${status}"
                             data-is-user="${isUser}"
                             style="padding:10px;border-radius:8px;border:2px solid #eee;${disabledStyle}">
                            <b>${j.jam_mulai} - ${j.jam_selesai}</b>
                            <div class="small text-muted">${statusText}</div>
                        </div>
                    </div>
                `;
            });

            // ===============================
            // EVENT CLICK
            // ===============================
            document.querySelectorAll(".jadwal-item").forEach(item => {
                const booking = item.dataset.booking;
                const isUser = item.dataset.isUser === "true";

                // User sendiri boleh klik jika status available (kadaluarsa dianggap available)
                if (isUser && booking !== "available") return;
                // Semua jadwal orang lain yang sedang menunggu/dibayar tidak bisa diklik
                if (!isUser && (booking === "menunggu" || booking === "dibayar")) return;

                item.addEventListener("click", function() {
                    document.querySelectorAll(".jadwal-item").forEach(x => x.style.borderColor = "#eee");
                    this.style.borderColor = "#41A67E";

                    window.selectedJadwal = this.dataset.id;
                    window.selectedJadwalBookingStatus = this.dataset.booking;
                });
            });
        });
}
</script>
@endsection