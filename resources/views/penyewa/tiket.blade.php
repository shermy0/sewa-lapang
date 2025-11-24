@extends('layouts.sidebar')

@section('title', 'Tiket Saya')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/tiket.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">
        <i class="fa-solid fa-ticket me-2"></i>Tiket Saya
    </h2>

    <ul class="nav nav-pills mb-4" id="scanTabs">
        <li class="nav-item"><button class="nav-link active" data-filter="all">Semua</button></li>
        <li class="nav-item"><button class="nav-link" data-filter="belum_scan">Belum Discan</button></li>
        <li class="nav-item"><button class="nav-link" data-filter="sudah_scan">Sudah Discan</button></li>
    </ul>

    <div class="row" id="ticketContainer">
        @forelse($sudahDibayar as $p)
        <div class="col-md-6 mb-4 ticket-card" data-status="{{ $p->status_scan }}">
@if($p->permintaanPerubahan)

    {{-- STATUS MENUNGGU --}}
    @if($p->permintaanPerubahan->status === 'menunggu')
        @php
            $expires = \Carbon\Carbon::parse($p->permintaanPerubahan->expires_at);
            $now = \Carbon\Carbon::now();
            $selisihDetik = intval($expires->diffInSeconds($now, false));
        @endphp

@if($selisihDetik < 0)
    <div class="alert alert-warning py-2 px-3 small mb-2">
        <i class="fa-solid fa-hourglass-half me-1"></i>
        Permintaan sedang menunggu persetujuan.
        <br>
        Sisa waktu: <span class="text-danger fw-bold" id="countdown-{{ $p->id }}"></span>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let sisa = {{ abs($selisihDetik) }}; // detik tersisa

            function updateCountdown() {
                if (sisa <= 0) {
                    document.getElementById("countdown-{{ $p->id }}").innerText = "00:00";
                    location.reload();
                    return;
                }

                let menit = Math.floor(sisa / 60);
                let detik = sisa % 60;

                document.getElementById("countdown-{{ $p->id }}")
                    .innerText = `${String(menit).padStart(2, '0')}:${String(detik).padStart(2, '0')}`;

                sisa--;
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        });
    </script>

            {{-- script countdown --}}
        @else
            <div class="alert alert-danger py-2 px-3 small mb-2">
                <i class="fa-solid fa-clock-rotate-left me-1"></i>
                Waktu persetujuan habis. Slot jadwal kembali tersedia.
            </div>
            <button onclick="location.reload()" class="btn btn-outline-danger btn-sm mb-2">
                <i class="fa-solid fa-rotate me-1"></i> Reload Halaman
            </button>
        @endif

    {{-- STATUS DISETUJUI --}}
    @elseif($p->permintaanPerubahan->status === 'disetujui')
        <div class="alert alert-success py-2 px-3 small mb-2">
            <i class="fa-solid fa-check-circle me-1"></i> Perubahan telah disetujui dan diperbarui.
        </div>

    {{-- STATUS DITOLAK --}}
    @elseif($p->permintaanPerubahan->status === 'ditolak')
        <div class="alert alert-danger py-2 px-3 small mb-2">
            <i class="fa-solid fa-xmark me-1"></i> Permintaan perubahan ditolak.
        </div>

    @endif

@endif

            <div class="ticket shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="d-flex flex-column flex-md-row">
                    @php
                        $jadwalAktif = $p->jadwal;
                        $sectionAktif = $p->jadwal?->section;
                        if ($p->permintaanPerubahan && $p->permintaanPerubahan->status === 'disetujui') {
                            $jadwalAktif = $p->permintaanPerubahan->jadwalBaru ?? $jadwalAktif;
                            $sectionAktif = $p->permintaanPerubahan->sectionBaru ?? $sectionAktif;
                        }
                        \Carbon\Carbon::setLocale('id');
                        $tanggal = \Carbon\Carbon::parse($jadwalAktif->tanggal);
                        $hari = $tanggal->translatedFormat('l');
                    @endphp

                    <div class="ticket-left">
                        <div class="ticket-left-header text-center">
                            <div class="lapangan-name">{{ $p->lapangan->nama_lapangan }}</div>
                            <div class="lapangan-meta">
                                <i class="fa-solid fa-tag me-1"></i>{{ ucfirst($p->lapangan->kategori) }}<br>
                                <i class="fa-solid fa-location-dot me-1"></i>{{ $p->lapangan->lokasi }}
                            </div>
                            @if($sectionAktif)
                                <div class="section-info mt-2">
                                    <i class="fa-solid fa-layer-group"></i>
                                    <span>{{ $sectionAktif->nama_section }}</span>
                                    @if($sectionAktif->deskripsi)
                                        <div class="section-desc mt-1 text-muted small" style="line-height:1.3;">
                                            {{ $sectionAktif->deskripsi }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if($jadwalAktif)
                        <div class="schedule mt-3 text-center">
                            <div><i class="fa-solid fa-calendar-day me-1"></i>{{ $hari }}, {{ $tanggal->translatedFormat('d F Y') }}</div>
                            <div><i class="fa-solid fa-clock me-1"></i>{{ $jadwalAktif->jam_mulai }} - {{ $jadwalAktif->jam_selesai }}</div>
                        </div>
                        @endif
                    </div>

                    <div class="ticket-right p-4 bg-white flex-grow-1 position-relative">
                        <div class="ticket-info">
                            <p class="mb-1"><strong>Kode Tiket:</strong> {{ $p->kode_tiket }}</p>
                            <p class="mb-1"><strong>Status:</strong> <span class="badge bg-success">Dibayar</span></p>
                            <p class="mb-1"><strong>Harga:</strong> Rp {{ number_format($p->jadwal->harga_sewa, 0, ',', '.') }}</p>
                            <p class="mt-2 mb-0">
                                @if($p->status_scan === 'sudah_scan')
                                    <span class="ticket-status-scan sudah"><i class="fa-solid fa-check-circle me-1"></i>Sudah Discan</span>
                                @else
                                    <span class="ticket-status-scan belum"><i class="fa-solid fa-hourglass-half me-1"></i>Belum Discan</span>
                                @endif
                            </p>
                        </div>

                        <div class="qr text-center mt-3">
                            <div class="d-inline-flex p-3 border border-success border-dashed rounded-3 bg-light">
                                {!! DNS2D::getBarcodeHTML($p->kode_tiket ?? 'SEWALAP', 'QRCODE', 6, 6) !!}
                            </div>
                            <div class="fw-semibold mt-2">{{ $p->kode_tiket }}</div>
                        </div>

                        <div class="text-end mt-3 d-flex justify-content-between align-items-center">
                            <a href="{{ route('tiket.download', $p->id) }}" class="btn btn-outline-success btn-sm px-3">
                                <i class="fa-solid fa-download me-1"></i> Download
                            </a>

                            {{-- Tombol aksi perubahan --}}
@if($p->status_scan !== 'sudah_scan')

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
@endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
            <p class="text-muted">Belum ada pesanan yang dibayar.</p>
        @endforelse
    </div>
</div>

<script>
window.jadwalAktifUser = @json($semuaPemesananUser->pluck('jadwal_id'));
</script>

<script>
window.userOrders = @json($userOrders);

</script>

<script>
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
                        <p><strong>Berlaku hingga:</strong> ${expiresText}</p>
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