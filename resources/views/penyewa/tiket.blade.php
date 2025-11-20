@extends('layouts.sidebar')

@section('title', 'Tiket Saya')

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
.nav-pills .nav-link {
    color: var(--green);
    font-weight: 600;
    border-radius: 50px;
    padding: 8px 20px;
    transition: .3s;
}
.nav-pills .nav-link.active {
    background-color: var(--green);
    color: #fff;
}
.ticket-status-scan {
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 6px;
    padding: 4px 8px;
}
.ticket-status-scan.sudah {
    background: #E6F5EF;
    color: var(--green);
}
.ticket-status-scan.belum {
    background: #FFF5D6;
    color: #C78C00;
}
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">
        <i class="fa-solid fa-ticket me-2"></i>Tiket Saya
    </h2>

    {{-- ====================== FILTER TAB ====================== --}}
    <ul class="nav nav-pills mb-4" id="scanTabs">
        <li class="nav-item">
            <button class="nav-link active" data-filter="all">Semua</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-filter="belum_scan">Belum Discan</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-filter="sudah_scan">Sudah Discan</button>
        </li>
    </ul>

    {{-- ====================== DAFTAR TIKET ====================== --}}
    <div class="row" id="ticketContainer">
        @forelse($sudahDibayar as $p)
        <div class="col-md-6 mb-4 ticket-card" data-status="{{ $p->status_scan }}">
            
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
                        Perubahan jadwal / section telah disetujui dan diperbarui di tiket ini.
                    </div>
                @elseif($p->permintaanPerubahan->status === 'ditolak')
                    <div class="alert alert-danger py-2 px-3 small mb-2">
                        <i class="fa-solid fa-xmark me-1"></i>
                        Permintaan perubahan ditolak.
                    </div>
                @endif
            @endif

            {{-- ================== KARTU TIKET ================== --}}
            <div class="ticket shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="d-flex flex-column flex-md-row">
                    {{-- ================== KIRI ================== --}}
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

        {{-- ✅ Section Info --}}
        @if($sectionAktif)
            <div class="section-info mt-2">
                <i class="fa-solid fa-layer-group"></i>
                <span>{{ $sectionAktif->nama_section }}</span>
            </div>
        @endif
    </div>

    {{-- ✅ Jadwal Info --}}
@if($jadwalAktif)
    @php
        \Carbon\Carbon::setLocale('id'); // ✅ ubah ke bahasa Indonesia
        $tanggal = \Carbon\Carbon::parse($jadwalAktif->tanggal);
        $hari = $tanggal->translatedFormat('l'); // hasilnya: Senin, Selasa, dst.
    @endphp

    <div class="schedule mt-3 text-center">
        <div>
            <i class="fa-solid fa-calendar-day me-1"></i>
            {{ $hari }}, {{ $tanggal->translatedFormat('d F Y') }}
        </div>
        <div>
            <i class="fa-solid fa-clock me-1"></i>
            {{ $jadwalAktif->jam_mulai }} - {{ $jadwalAktif->jam_selesai }}
        </div>
    </div>
@endif

</div>
                    {{-- ================== KANAN ================== --}}
                    <div class="ticket-right p-4 bg-white flex-grow-1 position-relative">
                        <div class="ticket-info">
                            <p class="mb-1"><strong>Kode Tiket:</strong> {{ $p->kode_tiket }}</p>
                            <p class="mb-1"><strong>Status:</strong> 
                                <span class="badge bg-success">Dibayar</span>
                            </p>
                            <p class="mb-1"><strong>Harga:</strong> Rp {{ number_format($p->jadwal->harga_sewa, 0, ',', '.') }}</p>

                            {{-- ✅ STATUS SCAN BADGE --}}
                            <p class="mt-2 mb-0">
                                {{-- <strong>Scan:</strong>  --}}
                                @if($p->status_scan === 'sudah_scan')
                                    <span class="ticket-status-scan sudah">
                                        <i class="fa-solid fa-check-circle me-1"></i>Sudah Discan
                                    </span>
                                @else
                                    <span class="ticket-status-scan belum">
                                        <i class="fa-solid fa-hourglass-half me-1"></i>Belum Discan
                                    </span>
                                @endif
                            </p>
                        </div>

                        {{-- QR Code --}}
                        <div class="qr text-center mt-3">
                            <div class="d-inline-flex p-3 border border-success border-dashed rounded-3 bg-light">
                                {!! DNS2D::getBarcodeHTML($p->kode_tiket ?? 'SEWALAP', 'QRCODE', 6, 6) !!}
                            </div>
                            <div class="fw-semibold mt-2">{{ $p->kode_tiket }}</div>
                        </div>

                        {{-- Aksi --}}
                        <div class="text-end mt-3 d-flex justify-content-between align-items-center">
                            <a href="{{ route('tiket.download', $p->id) }}" class="btn btn-outline-success btn-sm px-3">
                                <i class="fa-solid fa-download me-1"></i> Download
                            </a>

                            @if(!$p->permintaanPerubahan)
                                <button class="btn btn-warning btn-sm px-3"
                                    onclick="ajukanPerubahan({{ $p->id }}, {{ $p->lapangan->id }})">
                                    <i class="fa-solid fa-arrows-rotate me-1"></i> Ajukan Perubahan
                                </button>
                            @else
                                <button class="btn btn-outline-primary btn-sm px-3"
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
            <p class="text-muted">Belum ada pesanan yang dibayar.</p>
        @endforelse
    </div>
</div>

<script>
// 🔹 Tab filter (scan)
document.querySelectorAll('#scanTabs .nav-link').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('#scanTabs .nav-link').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;

        document.querySelectorAll('.ticket-card').forEach(card => {
            if (filter === 'all' || card.dataset.status === filter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

function ajukanPerubahan(pemesananId, lapanganId) {
    window.selectedSection = null;
    window.selectedJadwal = null;

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
        didOpen: () => {
            fetch(`/sections/${lapanganId}`)
                .then(res => res.json())
                .then(data => {
                    const list = document.getElementById('sectionList');
                    list.innerHTML = data.map(s => `
                        <div class="col-md-3">
                            <div class="section-card" data-id="${s.id}" 
                                 style="border:2px solid #ddd;padding:10px;border-radius:8px;cursor:pointer;">
                                <strong>${s.nama_section}</strong><br>
                                <small>${s.deskripsi ?? ''}</small>
                            </div>
                        </div>`).join('');

                    document.querySelectorAll('.section-card').forEach(card => {
                        card.addEventListener('click', function() {
                            document.querySelectorAll('.section-card').forEach(c => c.style.borderColor='#ddd');
                            this.style.borderColor='#41A67E';
                            const sectionId = this.dataset.id;
                            fetch(`/jadwal/section/${sectionId}`)
                                .then(res => res.json())
                                .then(jadwals => {
                                    const jadwalList = document.getElementById('jadwalList');
                                    jadwalList.innerHTML = `
                                        <h6 class="mt-3">Pilih Jadwal Baru</h6>
                                        <div class="row g-2">
                                            ${jadwals.map(j => `
                                                <div class="col-md-3">
                                                    <div class="jadwal-item ${j.tersedia ? 'available' : 'unavailable'}" 
                                                        data-id="${j.id}"
                                                        data-status="${j.booking_status || (j.tersedia ? 'available' : 'unavailable')}"
                                                        style="padding:10px;border-radius:8px;border:2px solid #eee;
                                                               cursor:${j.tersedia ? 'pointer' : 'not-allowed'};
                                                               background:${j.tersedia ? '#fff' : '#f3f3f3'};">
                                                        ${j.jam_mulai} - ${j.jam_selesai}<br>
                                                        <small>${new Date(j.tanggal).toLocaleDateString('id-ID')}</small>
                                                        <div class="mt-1">
                                                            ${
                                                                j.booking_status === 'menunggu'
                                                                    ? '<span class="badge bg-warning text-dark">Menunggu pembayaran</span>'
                                                                    : j.booking_status === 'dibayar'
                                                                        ? '<span class="badge bg-danger">Sudah dibayar</span>'
                                                                        : j.tersedia
                                                                            ? '<span class="badge bg-success">Tersedia</span>'
                                                                            : '<span class="badge bg-secondary">Tidak tersedia</span>'
                                                            }
                                                        </div>
                                                    </div>
                                                </div>`).join('')}
                                        </div>`;

                                    document.querySelectorAll('.jadwal-item').forEach(item => {
                                        item.addEventListener('click', function() {
                                            const status = this.dataset.status;
                                            const selectItem = () => {
                                                document.querySelectorAll('.jadwal-item').forEach(i => i.style.borderColor='#eee');
                                                this.style.borderColor='#41A67E';
                                                window.selectedSection = sectionId;
                                                window.selectedJadwal = this.dataset.id;
                                            };

                                            if (status === 'dibayar') {
                                                return Swal.fire('Tidak bisa', 'Jadwal ini sudah dibayar.', 'error');
                                            }

                                            if (status === 'menunggu') {
                                                return Swal.fire({
                                                    title: 'Jadwal sudah dibooking',
                                                    text: 'Pesanan lain sedang menunggu pembayaran. Kirim permintaan untuk mengambil jadwal ini?',
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonText: 'Kirim Ajukan',
                                                    cancelButtonText: 'Batal',
                                                    confirmButtonColor: '#41A67E',
                                                }).then(res => {
                                                    if (res.isConfirmed) selectItem();
                                                });
                                            }

                                            if (status === 'unavailable') {
                                                return Swal.fire('Tidak tersedia', 'Pilih jadwal lain.', 'info');
                                            }

                                            selectItem();
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
            fetch(`/permintaan-perubahan/${pemesananId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(result.value)
            })
            .then(res => res.json())
            .then((data) => {
                if (data.error) {
                    return Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.error,
                    });
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Permintaan dikirim!',
                    text: data.expires_at
                        ? `Menunggu persetujuan pemilik. Berlaku sampai ${new Date(data.expires_at).toLocaleString('id-ID')}.`
                        : 'Menunggu persetujuan pemilik lapangan.',
                    confirmButtonColor: '#41A67E'
                }).then(() => location.reload());
            })
            .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan saat mengirim permintaan.', 'error'));
        }
    });
}

// Modal lihat detail
function lihatDetailPerubahan(permintaanId) {
    fetch(`/permintaan-perubahan/${permintaanId}`)
        .then(res => res.json())
        .then(data => {
            const jadwalBaru = data.jadwal_baru ? `
                ${new Date(data.jadwal_baru.tanggal).toLocaleDateString('id-ID')} 
                (${data.jadwal_baru.jam_mulai} - ${data.jadwal_baru.jam_selesai})
            ` : '-';
            const expiresText = data.expires_at
                ? new Date(data.expires_at).toLocaleString('id-ID')
                : '-';

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
                confirmButtonColor: '#d33',
                footer: data.status === 'menunggu'
                    ? `<button class="swal2-confirm btn btn-outline-primary btn-sm" onclick="editPermintaan(${data.pemesanan_id}, ${data.lapangan_id}, ${data.id})">
                        <i class='fa-solid fa-pen-to-square me-1'></i>Edit
                       </button>`
                    : ''
            }).then((result) => {
                if (result.isConfirmed) batalkanPermintaan(data.id);
            });
        });
}

// Batalkan permintaan
function batalkanPermintaan(permintaanId) {
    Swal.fire({
        title: 'Batalkan Permintaan?',
        text: 'Permintaan perubahan ini akan dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Batalkan',
        confirmButtonColor: '#d33',
        cancelButtonText: 'Tidak'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(`/permintaan-perubahan/${permintaanId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(() => Swal.fire('Dibatalkan!', 'Permintaan telah dibatalkan.', 'success')
                .then(() => location.reload()));
        }
    });
}

function editPermintaan(pemesananId, lapanganId, permintaanId) {
    ajukanPerubahan(pemesananId, lapanganId, permintaanId);
}
</script>
@endsection
