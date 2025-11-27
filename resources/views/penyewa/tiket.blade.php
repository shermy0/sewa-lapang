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

            <div class="ticket shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="d-flex flex-column flex-md-row">
                    @php
                        $jadwalAktif = $p->jadwal;
                        $sectionAktif = $p->jadwal?->section;
                        if (
    $p->permintaanPerubahan &&
    $p->permintaanPerubahan->status === 'disetujui' &&
    $p->permintaanPerubahan->jadwal_baru_id == $p->jadwal_id
) {

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
{{-- Tombol pindah lapang --}}
@if($p->status_scan !== 'sudah_scan')
<button class="btn btn-warning btn-sm px-3"
        onclick="pindahLapang({{ $p->id }}, {{ $p->lapangan->id }}, '{{ $p->lapangan->nama_lapangan }}', '{{ $sectionAktif?->nama_section ?? '-' }}', '{{ $jadwalAktif?->jam_mulai.' - '.$jadwalAktif?->jam_selesai ?? '-' }}', {{ $jadwalAktif?->id ?? 'null' }})">
    <i class="fa-solid fa-arrows-rotate me-1"></i> Pindah Lapang
</button>

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
window.userOrders = @json($userOrders);

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

function pindahLapang(pemesananId, lapanganId, namaLapangan = '', namaSection = '', jadwalAktifText = '', jadwalAktifId = null) {
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

                <div class="mb-3 p-2 border rounded-2 bg-light">
                    <p class="mb-1"><strong>Arena:</strong> ${namaLapangan}</p>
                    <p class="mb-1"><strong>Lapang Saat Ini:</strong> ${namaSection}</p>
                    <p class="mb-0"><strong>Jadwal Saat Ini:</strong> ${jadwalAktifText}</p>
                </div>

                <div class="mb-3">
                    <label class="modal-label">Pilih Section Baru</label>
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
            fetch(`/sections/${lapanganId}`)
            .then(res => res.json())
            .then(data => {
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

            setTimeout(()=>{
                const tgl = document.getElementById("filterTanggal");
                if (!tgl) return;
                tgl.addEventListener('change', ()=> {
                    if (window.selectedSection && tgl.value) loadJam(window.selectedSection, tgl.value, jadwalAktifId);
                });
            }, 100);
        }
    }).then(result => {
        if (result.isConfirmed && result.value) {
            const payload = result.value;

            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/pemesanan/${pemesananId}/pindah`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(resp => {
                Swal.close();
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Pindah lapang berhasil disimpan.'
                    }).then(()=> location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: resp.message || 'Terjadi kesalahan.'
                    });
                }
            })
            .catch(err => {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Tidak bisa menghubungi server.'
                });
            });
        }
    });
}

function loadJam(sectionId, tanggal, currentJadwalId = null) {
    fetch(`/jadwal/section/${sectionId}`)
        .then(res => res.json())
        .then(jadwals => {
            const filtered = jadwals.filter(j => j.tanggal.split('T')[0] === tanggal);
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

                if (window.jadwalAktifUser.includes(j.id)) {
                    const pay = window.userOrders[j.id] ?? null;
                    if (pay === "kadaluarsa" || pay === null) {
                        status = "available";
                    } else {
                        isUser = true;
                        status = (pay === "menunggu" || pay === "pending") ? "menunggu" : "dibayar";
                    }
                } else {
                    if (j.booking_status === "dibayar") status = "dibayar";
                    else if (j.booking_status === "menunggu") status = "menunggu";
                    else status = j.tersedia ? "available" : "menunggu";
                }

                let bgColor = "";
                let extraContent = "";
                let borderStyle = "2px solid #eee";

                if (isUser && status === "menunggu") bgColor = "#FFF3CD";
                else if (isUser && status === "dibayar") bgColor = "#D1E7DD";
                if (j.id == currentJadwalId) {
                    borderStyle = "3px dashed #41A67E";
                    extraContent = `<div class="jadwal-highlight-icon">🔹</div>`;
                }

                const disabledStyle = (isUser && status !== "available")
                    ? `pointer-events:none; opacity:0.85; background:${bgColor || '#e8fff2'}; border:${borderStyle}; position:relative;`
                    : (status === "dibayar" || status === "menunggu")
                        ? `pointer-events:none; opacity:0.55; background:${bgColor || '#f8f9fa'}; border:${borderStyle}; position:relative;`
                        : `background:${bgColor || '#fff'}; border:${borderStyle}; position:relative;`;

                let statusText = "Tersedia";
                if (isUser) {
                    const pay = window.userOrders[j.id];
                    if (pay === "menunggu" || pay === "pending") statusText = `<b style="color:#856404">Jadwalmu (Belum Dibayar)</b>`;
                    else if (pay === "berhasil") statusText = `<b style="color:#0F5132">Jadwalmu (Sudah Dibayar)</b>`;
                } else {
                    if (status === "menunggu") statusText = "Sedang dibooking (belum bayar)";
                    else if (status === "dibayar") statusText = "Sedang dibooking (sudah dibayar)";
                }

                container.innerHTML += `
                    <div class="col-md-3">
                        <div class="jadwal-item"
                             data-id="${j.id}"
                             data-booking="${status}"
                             data-is-user="${isUser}"
                             style="padding:10px; border-radius:8px; ${disabledStyle}">
                            <b>${j.jam_mulai} - ${j.jam_selesai}</b>
                            <div class="small text-muted">${statusText}</div>
                            ${extraContent}
                        </div>
                    </div>
                `;
            });

            document.querySelectorAll(".jadwal-item").forEach(item => {
                const booking = item.dataset.booking;
                const isUser = item.dataset.isUser === "true";

                if (isUser && booking !== "available") return;
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