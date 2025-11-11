@extends('layouts.sidebar')

@section('title', 'Menunggu Pembayaran')

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
:root {
    --green: #41A67E;
    --yellow: #F6C445;
    --gray-light: #f9f9f9;
}
.section-card, .jadwal-item {
    transition: .2s ease;
}
.section-card:hover, .jadwal-item.available:hover {
    transform: scale(1.03);
}
.jadwal-item.unavailable {
    opacity: 0.6;
}
.alert-status {
    font-size: .85rem;
    border-radius: 8px;
}
</style>

<div class="container py-4">
    <div class="penyewa-page-header">
        <div>
            <p class="eyebrow">Status Pembayaran</p>
            <h1>Menunggu Pembayaran</h1>
            <p class="subtitle">Segera selesaikan pembayaran agar jadwal bermainmu tetap aman.</p>
        </div>
        <span class="page-pill">
            <i class="fa-solid fa-clock-rotate-left me-1"></i>
            {{ $belumDibayar->count() }} pesanan
        </span>
    </div>

    <div class="payment-grid">
        @forelse($belumDibayar as $p)
        <div class="payment-card shadow-sm">

            {{-- 🔸 Notifikasi permintaan perubahan --}}
            @if($p->permintaanPerubahan)
                @if($p->permintaanPerubahan->status === 'menunggu')
                    <div class="alert alert-warning py-2 px-3 alert-status mb-2">
                        <i class="fa-solid fa-hourglass-half me-1"></i>
                        Menunggu persetujuan perubahan jadwal / section...
                    </div>
                @elseif($p->permintaanPerubahan->status === 'disetujui')
                    <div class="alert alert-success py-2 px-3 alert-status mb-2">
                        <i class="fa-solid fa-check-circle me-1"></i>
                        Perubahan jadwal / section telah disetujui dan diperbarui.
                    </div>
                @elseif($p->permintaanPerubahan->status === 'ditolak')
                    <div class="alert alert-danger py-2 px-3 alert-status mb-2">
                        <i class="fa-solid fa-xmark me-1"></i>
                        Permintaan perubahan ditolak.
                    </div>
                @endif
            @endif

            {{-- 🔸 Kartu pembayaran utama --}}
            <div class="payment-card__head">
                <div>
                    <p class="label">Lapangan</p>
                    <h5>{{ $p->lapangan->nama_lapangan }}</h5>
                </div>
                <span class="status-chip status-chip--warning">
                    <i class="fa-solid fa-coins me-1"></i> Belum Dibayar
                </span>
            </div>

            <div class="payment-card__body">
                <div class="info-row">
                    <span><i class="fa-regular fa-calendar me-2 text-success"></i>Tanggal</span>
                    <strong>{{ \Carbon\Carbon::parse($p->jadwal->tanggal)->translatedFormat('d F Y') }}</strong>
                </div>
                <div class="info-row">
                    <span><i class="fa-regular fa-clock me-2 text-success"></i>Jam Main</span>
                    <strong>{{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }}</strong>
                </div>

                @php
                    $totalBayar = $p->total_harga ?? 0;
                    if ($totalBayar <= 0) {
                        $sections = $p->lapangan->sections ?? collect();
                        $totalHarga = 0; $jumlahJadwal = 0;
                        foreach ($sections as $section) {
                            foreach ($section->jadwal as $jadwal) {
                                if (is_numeric($jadwal->harga_sewa) && $jadwal->harga_sewa > 0) {
                                    $totalHarga += $jadwal->harga_sewa;
                                    $jumlahJadwal++;
                                }
                            }
                        }
                        if ($jumlahJadwal > 0) $totalBayar = $totalHarga / $jumlahJadwal;
                    }
                @endphp
                <div class="info-row">
                    <span><i class="fa-solid fa-wallet me-2 text-success"></i>Total</span>
                    <strong>{{ 'Rp' . number_format($totalBayar, 0, ',', '.') }}</strong>
                </div>
            </div>

            <p class="countdown-label text-danger" data-countdown data-created-at="{{ $p->created_at->format('c') }}"></p>

            <div class="payment-card__actions">
                <button class="btn btn-success flex-grow-1 btn-pay-again" data-id="{{ $p->id }}">
                    <i class="fa-solid fa-credit-card me-1"></i> Bayar Sekarang
                </button>

                <form action="{{ route('pemesanan.batalkan', $p->id) }}" method="POST" onsubmit="return confirm('Yakin batalkan pemesanan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fa-solid fa-xmark me-1"></i> Batalkan
                    </button>
                </form>

                @if(!$p->permintaanPerubahan)
                    <button class="btn btn-outline-primary mt-2" onclick="ajukanPerubahan({{ $p->id }}, {{ $p->lapangan_id }})">
                        <i class="fa-solid fa-arrows-rotate me-1"></i> Ajukan Perubahan
                    </button>
                @else
                    <button class="btn btn-outline-secondary mt-2" onclick="lihatDetailPerubahan({{ $p->permintaanPerubahan->id }})">
                        <i class="fa-solid fa-eye me-1"></i> Lihat Detail Permintaan
                    </button>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state-card">
            <i class="fa-solid fa-clipboard-check"></i>
            <h5>Semua pesanan sudah dibayar</h5>
            <p>Nikmati sesi bermainmu! Pesanan baru akan tampil di sini.</p>
        </div>
        @endforelse
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script>
// 🕓 Countdown pembayaran
document.querySelectorAll('[data-countdown]').forEach(target => {
    const createdAt = new Date(target.dataset.createdAt);
    const deadline = new Date(createdAt.getTime() + 24 * 60 * 60 * 1000);
    const tick = () => {
        const now = new Date();
        const diff = deadline - now;
        if (diff <= 0) {
            target.textContent = 'Waktu pembayaran sudah habis.';
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

// 💳 Midtrans - Bayar Ulang
document.querySelectorAll('.btn-pay-again').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch(`/midtrans/token-again/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) return alert(data.error);
snap.pay(data.snap_token, { 
    onSuccess: function(result){
        fetch('/pemesanan/success/' + data.pemesanan_id, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ result })
        })
        .then(() => window.location.reload())
        .catch(err => console.error(err));
    },
    onPending: function(){
        alert("Menunggu pembayaran...");
        window.location.reload();
    },
    onError: function(result){
        alert("Pembayaran gagal!");
        console.error(result);
    }
});

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
