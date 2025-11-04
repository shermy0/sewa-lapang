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

    <div class="row">
        @forelse($sudahDibayar as $p)
        <div class="col-md-6 mb-4">

            {{-- Notifikasi kecil di atas tiket --}}
            @if($p->permintaanPerubahan && $p->permintaanPerubahan->status === 'menunggu')
                <div class="alert alert-warning py-2 px-3 small mb-2">
                    <i class="fa-solid fa-hourglass-half me-1"></i>
                    Sedang mengajukan perubahan jadwal / lapangan...
                </div>
            @endif

            <div class="ticket shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="d-flex flex-column flex-md-row">
                    
                    {{-- Kiri --}}
                    <div class="ticket-left bg-success text-white p-4 d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold mb-2">{{ $p->lapangan->nama_lapangan }}</h5>
                            <p class="mb-1"><i class="fa-solid fa-tag me-1"></i>{{ ucfirst($p->lapangan->kategori) }}</p>
                            <p class="mb-0"><i class="fa-solid fa-location-dot me-1"></i>{{ $p->lapangan->lokasi }}</p>
                        </div>
                        <div class="mt-3">
                            <p class="mb-1"><i class="fa-solid fa-calendar-day me-1"></i>
                                {{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}</p>
                            <p class="mb-0"><i class="fa-solid fa-clock me-1"></i>
                                {{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }}</p>
                        </div>
                    </div>

                    {{-- Kanan --}}
                    <div class="ticket-right p-4 flex-grow-1 bg-white position-relative">
                        <div class="ticket-info">
                            <p class="mb-1"><strong>Kode Tiket:</strong> {{ $p->kode_tiket }}</p>
                            <p class="mb-1"><strong>Status:</strong> <span class="badge bg-success">Dibayar</span></p>
                            <p class="mb-0"><strong>Harga:</strong> Rp {{ number_format($p->jadwal->harga_sewa, 0, ',', '.') }}</p>
                        </div>

                        <div class="qr text-center mt-3">
                            {!! DNS1D::getBarcodeHTML($p->kode_tiket, 'C128') !!}
                        </div>

                        <div class="text-end mt-3 d-flex justify-content-between align-items-center">
                            <a href="{{ route('tiket.download', $p->id) }}" class="btn btn-outline-success btn-sm px-3">
                                <i class="fa-solid fa-download me-1"></i> Download
                            </a>

                            {{-- Kalau belum ada perubahan, tampilkan tombol ajukan --}}
                            @if(!$p->permintaanPerubahan)
                                <button class="btn btn-warning btn-sm px-3"
                                    onclick="ajukanPerubahan({{ $p->id }}, {{ $p->lapangan->id }})">
                                    <i class="fa-solid fa-arrows-rotate me-1"></i> Ajukan Perubahan
                                </button>
                            @else
                                {{-- Kalau sudah ada perubahan, tombol berubah jadi lihat detail --}}
                                <button class="btn btn-outline-primary btn-sm px-3"
                                    onclick="lihatDetailPerubahan({{ $p->permintaanPerubahan->id }})">
                                    <i class="fa-solid fa-eye me-1"></i> Lihat Detail
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

{{-- ==================== SCRIPT ==================== --}}
<script>
// Ajukan Perubahan (sama seperti sebelumnya)
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
            fetch(`/permintaan-perubahan/${pemesananId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(result.value)
            })
            .then(res => res.json())
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Permintaan dikirim!',
                    text: 'Menunggu persetujuan pemilik lapangan.',
                    confirmButtonColor: '#41A67E'
                }).then(() => location.reload());
            });
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
