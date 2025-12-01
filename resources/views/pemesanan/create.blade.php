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
        {{-- Icon otomatis sesuai kategori --}}
        @if(str_contains(strtolower($lapangan->kategori ?? ''), 'badminton'))
            <i class="fa-solid fa-shuttlecock fa-4x text-success mb-3"></i>
        @elseif(str_contains(strtolower($lapangan->kategori ?? ''), 'futsal'))
            <i class="fa-solid fa-futbol fa-4x text-success mb-3"></i>
        @elseif(str_contains(strtolower($lapangan->kategori ?? ''), 'tenis'))
            <i class="fa-solid fa-table-tennis-paddle-ball fa-4x text-success mb-3"></i>
        @else
            <i class="fa-solid fa-dumbbell fa-4x text-success mb-3"></i>
        @endif

        <h2 class="fw-bold mt-2 text-success">{{ $lapangan->nama_lapangan }}</h2>
        <p class="text-muted mb-2">
            <i class="fa-solid fa-tag me-1"></i> {{ ucfirst($lapangan->kategori) ?? 'Umum' }} &nbsp; | &nbsp;
            <i class="fa-solid fa-location-dot me-1"></i> {{ $lapangan->lokasi }}
        </p>

        @if($lapangan->rating > 0)
            <p class="text-warning mb-2">
                <i class="fa-solid fa-star me-1"></i> {{ number_format($lapangan->rating, 1) }} / 5
            </p>
        @endif

        <p class="text-muted">Pilih lapangan dan jadwal bermain sesuai ketersediaan. Anda dapat memilih lebih dari satu slot waktu.</p>
    </div>

    {{-- ================== SECTION LIST ================== --}}
    <div class="mb-5">
        <h5 class="fw-bold mb-3 text-secondary">Pilih Lapangan</h5>
        <div class="row g-3">
            @foreach($lapangan->sections as $section)
            <div class="col-md-3">
                <div class="section-card text-center p-4 h-100" data-section-id="{{ $section->id }}">
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

    <div class="mb-4 d-flex align-items-center gap-3" id="filterTanggalWrapper" style="display:none;">
        <label class="fw-semibold text-secondary mb-0">
            <i class="fa-solid fa-filter me-1"></i> Pilih Tanggal
        </label>

        <input type="date" id="filterTanggal" class="filter-tgl-input">
        
        <button class="btn btn-outline-success btn-sm px-3" id="resetFilter">
            Reset
        </button>
    </div>
</div>

<div id="jadwalList"></div>

    {{-- RINGKASAN PEMILIHAN JADWAL --}}
    <div id="ringkasanJadwal" class="mt-4" style="display:none;">
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa-solid fa-clipboard-list me-2"></i>Ringkasan Pemilihan</h5>
                <span class="badge bg-light text-dark" id="jumlahJadwal">0 Jadwal Dipilih</span>
            </div>
            <div class="card-body">
                <div id="daftarJadwalTerpilih"></div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <h5>Total Bayar: <span class="text-success fw-bold" id="totalHarga">Rp 0</span></h5>
                    <button id="lanjutBayar" class="btn btn-success">
                        <i class="fa-solid fa-money-bill-wave me-1"></i> Lanjut ke Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================== MODAL RINGKASAN ================== --}}
<div class="modal fade" id="summaryModal" tabindex="-1" aria-labelledby="summaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="summaryModalLabel">
                    <i class="fa-solid fa-clipboard-list me-2"></i> Ringkasan Pemesanan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Section:</strong>
                    <p class="mb-0" id="summarySection"></p>
                </div>
                <div class="mb-3">
                    <strong>Jadwal:</strong>
                    <div id="summaryJadwal"></div>
                </div>
                <div class="mb-3">
                    <strong>Total Bayar:</strong>
                    <p class="mb-0 fw-bold text-warning fs-5" id="summaryTotal"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i> Batal
                </button>
                <button id="pay-button" class="btn btn-success">
                    <i class="fa-solid fa-money-bill-wave me-1"></i> Pesan & Bayar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ================== MIDTRANS ================== --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

<script>
let selectedSection = null;
let selectedJadwals = []; // Ubah dari selectedJadwal ke selectedJadwals (array)
let summaryModal;

// Initialize Bootstrap Modal
document.addEventListener('DOMContentLoaded', function() {
    summaryModal = new bootstrap.Modal(document.getElementById('summaryModal'));

    @if($pemesananPending)
    const pendingModal = new bootstrap.Modal(document.getElementById('pendingPaymentModal'));
    pendingModal.show();

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

    // =================== PEMBAYARAN PENDING ===================
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
    @endif
});

// ========== PILIH SECTION ==========
document.querySelectorAll('.section-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.section-card').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        selectedSection = this.dataset.sectionId;

        // reset jadwal & modal
        selectedJadwals = [];
        document.getElementById('jadwalContainer').style.display = 'none';
        document.getElementById('ringkasanJadwal').style.display = 'none';

        fetch(`/jadwal/section/${selectedSection}`)
        .then(res => res.json())
        .then(jadwals => showJadwal(jadwals));
    });
});

// ========== TAMPILKAN JADWAL ==========
// Tampilkan filter tanggal setelah section dipilih
function showJadwal(jadwals){
    document.getElementById('filterTanggalWrapper').style.display = 'flex';

    const jadwalContainer = document.getElementById('jadwalContainer');
    const jadwalList = document.getElementById('jadwalList');
    jadwalContainer.style.display = 'block';
    jadwalList.innerHTML = '';

    // Simpan semua jadwal untuk filter
    window.allJadwal = jadwals;

    renderJadwal(jadwals);
}

// Render berdasarkan filter
function renderJadwal(data) {
    const jadwalList = document.getElementById('jadwalList');
    jadwalList.innerHTML = '';

    const groupByDate = {};
    data.forEach(j => {
        if (!groupByDate[j.tanggal]) groupByDate[j.tanggal] = [];
        groupByDate[j.tanggal].push(j);
    });

    Object.keys(groupByDate).forEach(date => {
        const card = document.createElement('div');
        card.className = 'jadwal-card mb-4';
        card.innerHTML = `
            <div class="jadwal-header">
                <i class="fa-solid fa-calendar-day me-2"></i>
                ${new Date(date).toLocaleDateString('id-ID', {
                    weekday:'long', day:'numeric', month:'long', year:'numeric'
                })}
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
                            <i class="fa-solid fa-clock me-1"></i>
                            ${j.jam_mulai} - ${j.jam_selesai}
                            <small class="d-block mt-1 fw-semibold text-muted">
                                Rp ${parseInt(j.harga_sewa).toLocaleString('id-ID')}
                            </small>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        jadwalList.appendChild(card);
    });

    // event pilih jadwal
    document.querySelectorAll('.jadwal-item.available').forEach(item => {
        item.addEventListener('click', function() {
            const jadwalId = this.dataset.id;
            const jadwalInfo = {
                id: jadwalId,
                tanggal: this.dataset.tanggal,
                mulai: this.dataset.mulai,
                selesai: this.dataset.selesai,
                harga: parseInt(this.dataset.harga)
            };

            // Toggle selection
            const index = selectedJadwals.findIndex(j => j.id === jadwalId);
            if (index > -1) {
                // Jika sudah ada, hapus dari array
                selectedJadwals.splice(index, 1);
                this.classList.remove('selected');
            } else {
                // Jika belum ada, tambahkan ke array
                selectedJadwals.push(jadwalInfo);
                this.classList.add('selected');
            }

            // Update ringkasan
            updateRingkasan();
        });
    });
}

// Update ringkasan pemilihan jadwal
function updateRingkasan() {
    const ringkasanContainer = document.getElementById('ringkasanJadwal');
    const daftarJadwalContainer = document.getElementById('daftarJadwalTerpilih');
    const jumlahJadwalEl = document.getElementById('jumlahJadwal');
    const totalHargaEl = document.getElementById('totalHarga');

    if (selectedJadwals.length === 0) {
        ringkasanContainer.style.display = 'none';
        return;
    }

    ringkasanContainer.style.display = 'block';
    jumlahJadwalEl.textContent = `${selectedJadwals.length} Jadwal Dipilih`;

    // Tampilkan daftar jadwal terpilih
    daftarJadwalContainer.innerHTML = selectedJadwals.map((jadwal, index) => `
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
            <div>
                <strong>${jadwal.tanggal}</strong>
                <span class="ms-2">${jadwal.mulai} - ${jadwal.selesai}</span>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3">Rp ${jadwal.harga.toLocaleString('id-ID')}</span>
                <button class="btn btn-sm btn-outline-danger" onclick="hapusJadwal('${jadwal.id}')">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        </div>
    `).join('');

    // Hitung total harga
    const totalHarga = selectedJadwals.reduce((total, jadwal) => total + jadwal.harga, 0);
    totalHargaEl.textContent = `Rp ${totalHarga.toLocaleString('id-ID')}`;
}

// Hapus jadwal dari ringkasan
function hapusJadwal(jadwalId) {
    const index = selectedJadwals.findIndex(j => j.id === jadwalId);
    if (index > -1) {
        selectedJadwals.splice(index, 1);
        // Hapus kelas selected dari elemen
        document.querySelector(`.jadwal-item[data-id="${jadwalId}"]`).classList.remove('selected');
        updateRingkasan();
    }
}

// FILTER ACTION
document.getElementById('filterTanggal').addEventListener('change', function(){
    const tgl = this.value;
    if (!tgl) {
        renderJadwal(window.allJadwal);
        return;
    }

    const filtered = window.allJadwal.filter(j => j.tanggal === tgl);
    renderJadwal(filtered);
});

document.getElementById('resetFilter').addEventListener('click', function(){
    document.getElementById('filterTanggal').value = '';
    renderJadwal(window.allJadwal);
});

// Event listener untuk tombol lanjut bayar
document.getElementById('lanjutBayar').addEventListener('click', function() {
    if (selectedJadwals.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Jadwal!',
            text: 'Silakan pilih minimal satu jadwal sebelum melanjutkan.',
            confirmButtonColor: '#41A67E'
        });
        return;
    }

    // Update modal summary
    document.getElementById('summarySection').innerText = 
        document.querySelector('.section-card.active h6').innerText;
    
    // Tampilkan semua jadwal yang dipilih
    const summaryJadwal = document.getElementById('summaryJadwal');
    summaryJadwal.innerHTML = selectedJadwals.map(jadwal => `
        <div class="mb-1">
            <strong>${jadwal.tanggal}</strong>: ${jadwal.mulai} - ${jadwal.selesai}
            <span class="ms-2">Rp ${jadwal.harga.toLocaleString('id-ID')}</span>
        </div>
    `).join('');
    
    // Hitung total harga
    const totalHarga = selectedJadwals.reduce((total, jadwal) => total + jadwal.harga, 0);
    document.getElementById('summaryTotal').innerText = `Rp ${totalHarga.toLocaleString('id-ID')}`;

    summaryModal.show();
});

// =================== PESAN & BAYAR ===================
document.getElementById('pay-button').onclick = async function() {
    if (selectedJadwals.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Jadwal!',
            text: 'Silakan pilih minimal satu jadwal sebelum melanjutkan.',
            confirmButtonColor: '#41A67E'
        });
        return;
    }

    // ========== POPUP LOADING ========== //
    Swal.fire({
        title: 'Menyiapkan Pembayaran...',
        html: `
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 mb-0">Mohon tunggu sebentar</p>
        `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        showClass: {
            popup: 'animate__animated animate__fadeIn animate__faster'
        }
    });

    try {
        // Ambil ID jadwal yang dipilih
    const jadwalIds = selectedJadwals.map(j => parseInt(j.id));
        
        const res = await fetch('{{ route("midtrans.token") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                lapangan_id: {{ $lapangan->id }},
                jadwal_ids: jadwalIds // Kirim array ID jadwal
            })
        });

        const data = await res.json();

        if (res.status === 409) {
            Swal.close();
            Swal.fire({
                icon: 'info',
                title: 'Sudah Dipesan!',
                text: data.error || 'Salah satu jadwal yang kamu pilih sudah dipesan sebelumnya.',
                showCancelButton: true,
                confirmButtonText: 'Ke Menu Pembayaran',
                cancelButtonText: 'Oke',
                confirmButtonColor: '#41A67E',
                cancelButtonColor: '#6c757d',
            }).then((result) => {
                if (result.isConfirmed && data.redirect) {
                    window.location.href = data.redirect;
                }
            });
            return;
        }

        if (!data.snap_token) {
            throw new Error('Gagal mendapatkan token Midtrans.');
        }

        // ✅ Tutup popup loading
        Swal.close();
        summaryModal.hide();

        // 🔹 Jalankan Snap Popup
        snap.pay(data.snap_token, {
            onSuccess: function(result) {
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
            onPending: function(result) {
                Swal.fire({
                    icon: 'info',
                    title: 'Menunggu Pembayaran',
                    text: 'Silakan selesaikan pembayaranmu sebelum waktu habis.',
                    confirmButtonColor: '#41A67E'
                }).then(() => window.location.href = '/penyewa/pembayaran');
            },
            onError: function(result) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran Gagal!',
                    text: 'Terjadi kesalahan saat memproses transaksi.',
                    confirmButtonColor: '#41A67E'
                });
            }
        });
    } catch (error) {
        // ❌ Tutup loading kalau error
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: error.message || 'Tidak bisa mendapatkan token pembayaran.',
            confirmButtonColor: '#41A67E'
        });
    }
};

</script>

@endsection