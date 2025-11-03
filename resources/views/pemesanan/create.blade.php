@extends('layouts.sidebar')

@section('title', 'Pesan Lapangan')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pesan.css') }}">
{{-- Font Awesome --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container py-5">
    {{-- HEADER --}}
    <div class="text-center mb-5">
        <i class="fa-solid fa-calendar-check fa-4x text-success mb-3"></i>
        <h2 class="fw-bold mt-2 text-success">{{ $lapangan->nama_lapangan }}</h2>
        <p class="text-muted">Pilih section dan jadwal bermain sesuai ketersediaan.</p>
    </div>

    {{-- ================== SECTION LIST ================== --}}
    <div class="mb-5">
        <h5 class="fw-bold mb-3 text-secondary">Pilih Section Lapangan</h5>
        <div class="row g-3">
            @foreach($lapangan->sections as $section)
            <div class="col-md-3">
                <div class="section-card text-center p-4 h-100" data-section-id="{{ $section->id }}">
                    {{-- Contoh: icon tergantung kategori lapangan --}}
                    @if(str_contains(strtolower($lapangan->nama_lapangan), 'badminton'))
                        <i class="fa-solid fa-shuttlecock fa-3x text-success mb-3"></i>
                    @elseif(str_contains(strtolower($lapangan->nama_lapangan), 'futsal'))
                        <i class="fa-solid fa-futbol fa-3x text-success mb-3"></i>
                    @elseif(str_contains(strtolower($lapangan->nama_lapangan), 'tenis'))
                        <i class="fa-solid fa-table-tennis-paddle-ball fa-3x text-success mb-3"></i>
                    @else
                        <i class="fa-solid fa-dumbbell fa-3x text-success mb-3"></i>
                    @endif
                    <h6 class="fw-bold mb-1">{{ $section->nama_section }}</h6>
                    <p class="text-muted small mb-0">{{ $section->deskripsi ?? 'Lapangan indoor standar' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ================== JADWAL ================== --}}
    <div id="jadwalContainer" class="mt-5" style="display:none;">
        <h5 class="fw-bold mb-4 text-secondary">Pilih Jadwal Tersedia</h5>
        <div id="jadwalList"></div>
    </div>

    {{-- ================== RINGKASAN ================== --}}
    <div id="summaryCard" class="card mt-5 border-0 p-4" style="display:none; border:2px solid var(--border);">
        <h5 class="fw-bold text-success mb-3"><i class="fa-solid fa-clipboard-list me-2"></i> Ringkasan Pemesanan</h5>
        <p><strong>Section:</strong> <span id="summarySection"></span></p>
        <p><strong>Jadwal:</strong> <span id="summaryJadwal" class="text-success"></span></p>
        <p><strong>Total Bayar:</strong> <span id="summaryTotal" class="fw-bold text-warning"></span></p>

        <div class="text-end mt-3">
            <button id="pay-button" class="btn btn-success px-4"><i class="fa-solid fa-money-bill-wave me-1"></i> Pesan & Bayar</button>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary px-4"><i class="fa-solid fa-xmark me-1"></i> Batal</a>
        </div>
    </div>
</div>

{{-- ================== MIDTRANS ================== --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

<script>
let selectedSection = null;
let selectedJadwal = null;

// ========== PILIH SECTION ==========
document.querySelectorAll('.section-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.section-card').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        selectedSection = this.dataset.sectionId;

        // reset jadwal & ringkasan
        selectedJadwal = null;
        document.getElementById('jadwalContainer').style.display = 'none';
        document.getElementById('summaryCard').style.display = 'none';

        fetch(`/jadwal/section/${selectedSection}`)
        .then(res => res.json())
        .then(jadwals => showJadwal(jadwals));
    });
});

// ========== TAMPILKAN JADWAL ==========
function showJadwal(jadwals){
    const jadwalContainer = document.getElementById('jadwalContainer');
    const jadwalList = document.getElementById('jadwalList');
    jadwalContainer.style.display = 'block';
    jadwalList.innerHTML = '';

    const groupByDate = {};
    jadwals.forEach(j => {
        if (!groupByDate[j.tanggal]) groupByDate[j.tanggal] = [];
        groupByDate[j.tanggal].push(j);
    });

    Object.keys(groupByDate).forEach(date => {
        const card = document.createElement('div');
        card.className = 'jadwal-card mb-4';
        card.innerHTML = `
            <div class="jadwal-header">
                <i class="fa-solid fa-calendar-day me-2"></i>
                ${new Date(date).toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' })}
            </div>
            <div class="jadwal-body row g-3 mt-1">
                ${groupByDate[date].map(j => `
                    <div class="col-md-3 col-sm-6">
                        <div class="jadwal-item ${j.tersedia ? 'available' : 'unavailable'}" 
                             data-id="${j.id}" 
                             data-tanggal="${j.tanggal}" 
                             data-mulai="${j.jam_mulai}" 
                             data-selesai="${j.jam_selesai}" 
                             data-harga="${j.harga_sewa}">
                            <i class="fa-solid fa-clock me-1"></i> ${j.jam_mulai} - ${j.jam_selesai}
                            <small class="d-block mt-1 fw-semibold text-muted">Rp ${parseInt(j.harga_sewa).toLocaleString('id-ID')}</small>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        jadwalList.appendChild(card);
    });

    document.querySelectorAll('.jadwal-item.available').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.jadwal-item').forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
            selectedJadwal = this.dataset.id;

            document.getElementById('summaryCard').style.display = 'block';
            document.getElementById('summarySection').innerText = document.querySelector('.section-card.active h6').innerText;
            document.getElementById('summaryJadwal').innerText = `${this.dataset.tanggal} (${this.dataset.mulai} - ${this.dataset.selesai})`;
            document.getElementById('summaryTotal').innerText = 'Rp ' + parseInt(this.dataset.harga).toLocaleString('id-ID');
        });
    });
}

// =================== PESAN & BAYAR ===================
document.getElementById('pay-button').onclick = function() {
    if (!selectedJadwal) {
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
            jadwal_id: selectedJadwal
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

// =================== PEMBAYARAN PENDING ===================
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

<script>
let selectedSection = null;
let selectedJadwal = null;

// ========== PILIH SECTION ==========
document.querySelectorAll('.section-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.section-card').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        selectedSection = this.dataset.sectionId;

        // reset jadwal & ringkasan
        selectedJadwal = null;
        document.getElementById('jadwalContainer').style.display = 'none';
        document.getElementById('summaryCard').style.display = 'none';

        fetch(`/jadwal/section/${selectedSection}`)
        .then(res => res.json())
        .then(jadwals => showJadwal(jadwals));
    });
});

// ========== TAMPILKAN JADWAL ==========
function showJadwal(jadwals){
    const jadwalContainer = document.getElementById('jadwalContainer');
    const jadwalList = document.getElementById('jadwalList');
    jadwalContainer.style.display = 'block';
    jadwalList.innerHTML = '';

    const groupByDate = {};
    jadwals.forEach(j => {
        if (!groupByDate[j.tanggal]) groupByDate[j.tanggal] = [];
        groupByDate[j.tanggal].push(j);
    });

    Object.keys(groupByDate).forEach(date => {
        const card = document.createElement('div');
        card.className = 'jadwal-card mb-4';
        card.innerHTML = `
            <div class="jadwal-header">
                <i class="fa-solid fa-calendar-day me-2"></i>
                ${new Date(date).toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' })}
            </div>
            <div class="jadwal-body row g-3 mt-1">
                ${groupByDate[date].map(j => `
                    <div class="col-md-3 col-sm-6">
                        <div class="jadwal-item ${j.tersedia ? 'available' : 'unavailable'}" 
                             data-id="${j.id}" 
                             data-tanggal="${j.tanggal}" 
                             data-mulai="${j.jam_mulai}" 
                             data-selesai="${j.jam_selesai}" 
                             data-harga="${j.harga_sewa}">
                            <i class="fa-solid fa-clock me-1"></i> ${j.jam_mulai} - ${j.jam_selesai}
                            <small class="d-block mt-1 fw-semibold text-muted">Rp ${parseInt(j.harga_sewa).toLocaleString('id-ID')}</small>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        jadwalList.appendChild(card);
    });

    document.querySelectorAll('.jadwal-item.available').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.jadwal-item').forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
            selectedJadwal = this.dataset.id;

            document.getElementById('summaryCard').style.display = 'block';
            document.getElementById('summarySection').innerText = document.querySelector('.section-card.active h6').innerText;
            document.getElementById('summaryJadwal').innerText = `${this.dataset.tanggal} (${this.dataset.mulai} - ${this.dataset.selesai})`;
            document.getElementById('summaryTotal').innerText = 'Rp ' + parseInt(this.dataset.harga).toLocaleString('id-ID');
        });
    });
}
</script>

<style>
:root {
  --primary-green: #41A67E;
  --gold: #F59E0B;
  --light-bg: #FAFBFB;
  --white: #FFFFFF;
  --border: #E5E9E8;
  --text: #2E3A35;
}

.section-card {
  border: 2px solid var(--border);
  border-radius: 14px;
  background: var(--white);
  transition: all 0.3s ease;
}
.section-card:hover {
  border-color: var(--primary-green);
  background-color: #F9FFFB;
  transform: translateY(-4px);
}
.section-card.active {
  border-color: var(--primary-green);
  background: linear-gradient(135deg, #E8FBF2, #FFFFFF);
}
.section-card.active h6 {
  color: var(--primary-green);
}
.section-card i {
  transition: transform 0.3s ease;
}
.section-card:hover i {
  transform: scale(1.1);
}

.jadwal-card {
  border: 2px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  background: var(--white);
}
.jadwal-header {
  background-color: var(--primary-green);
  color: var(--white);
  font-weight: 600;
  padding: 10px 15px;
}
.jadwal-body {
  padding: 15px 20px;
}
.jadwal-item {
  border: 2px solid var(--border);
  border-radius: 10px;
  text-align: center;
  padding: 14px;
  transition: all 0.25s ease;
  cursor: pointer;
  background: var(--white);
  color: var(--text);
}
.jadwal-item.available:hover {
  border-color: var(--primary-green);
  /* background-color: #F1FCF7; */
}
.jadwal-item.unavailable {
  /* background-color: #F3F4F4; */
  color: #999;
  cursor: not-allowed;
}
.jadwal-item.selected {
  background-color: var(--primary-green);
  color: var(--white);
  border-color: var(--primary-green);
}
</style>
@endsection
