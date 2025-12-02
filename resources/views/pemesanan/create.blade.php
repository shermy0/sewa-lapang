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

    {{-- ================== INPUT NAMA KOMUNITAS ================== --}}
    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fa-solid fa-users me-2"></i> Silahkan isi Komunitas</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="nama_komunitas" class="form-label fw-semibold">
                            Nama Komunitas
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="nama_komunitas" 
                               name="nama_komunitas" 
                               placeholder="Contoh: Komunitas Futsal Jakarta, Badminton Club Bandung, dll."
                               maxlength="255">
                    </div>
                </div>
            </div>
        </div>
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

    {{-- ================== PILIH TANGGAL ================== --}}
    <div id="pilihTanggalContainer" class="mt-5" style="display:none;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0 text-secondary">Pilih Tanggal</h5>
                <p class="text-muted mb-0">Pilih tanggal untuk melihat jadwal tersedia</p>
            </div>
            <div id="tanggalTerpilihInfo" style="display:none;">
                <span class="badge bg-success">
                    <i class="fa-solid fa-calendar-check me-1"></i>
                    <span id="currentDateDisplay"></span>
                </span>
            </div>
        </div>
        
        <div class="card border-success">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="fa-solid fa-calendar-alt me-1"></i> Pilih Tanggal
                            </label>
                            <div class="input-group">
                                <input type="date" 
                                       id="inputTanggal" 
                                       class="form-control" 
                                       min="{{ date('Y-m-d') }}">
                                <button class="btn btn-outline-success" type="button" id="tombolHariIni">
                                    Hari Ini
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="fa-solid fa-info-circle me-1"></i>
                                Pilih tanggal untuk melihat slot waktu yang tersedia
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- LOADING TANGGAL --}}
        <div id="loadingTanggal" class="text-center py-5" style="display:none;">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Memuat jadwal untuk tanggal terpilih...</p>
        </div>
    </div>

    {{-- ================== JADWAL ================== --}}
    <div id="jadwalContainer" class="mt-5" style="display:none;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0 text-secondary">
                    <i class="fa-solid fa-clock me-2"></i> Jadwal Tersedia
                </h5>
                <p class="text-muted mb-0" id="tanggalDipilihText"></p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" id="refreshJadwalBtn" title="Refresh jadwal">
                    <i class="fa-solid fa-rotate"></i>
                </button>
                <button class="btn btn-outline-success btn-sm" id="gantiTanggalBtn">
                    <i class="fa-solid fa-calendar-alt me-1"></i> Ganti Tanggal
                </button>
            </div>
        </div>
        
        {{-- LOADING JADWAL --}}
        <div id="loadingJadwal" class="text-center py-5" style="display:none;">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Memuat jadwal tersedia...</p>
        </div>
        
        {{-- JADWAL LIST --}}
        <div id="jadwalList" class="mb-4"></div>
        
        {{-- PESAN KOSONG --}}
        <div id="noJadwalMessage" class="text-center py-5" style="display:none;">
            <i class="fa-solid fa-calendar-times fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Tidak ada jadwal tersedia</h5>
            <p class="text-muted mb-3">Tidak ada slot waktu yang tersedia untuk tanggal ini.</p>
            <button class="btn btn-outline-success" id="gantiTanggalBtn2">
                <i class="fa-solid fa-calendar-alt me-1"></i> Coba Tanggal Lain
            </button>
        </div>
    </div>

    {{-- RINGKASAN PEMILIHAN JADWAL --}}
    <div id="ringkasanJadwal" class="mt-4" style="display:none;">
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa-solid fa-clipboard-list me-2"></i>Ringkasan Pemilihan</h5>
                <span class="badge bg-light text-dark" id="jumlahJadwal">0 Jadwal Dipilih</span>
            </div>
            <div class="card-body">
                {{-- INFO KOMUNITAS --}}
                <div id="komunitasInfo" class="mb-3" style="display:none;">
                    <div class="alert alert-success py-2">
                        <i class="fa-solid fa-users me-2"></i>
                        <strong>Komunitas:</strong> <span id="displayNamaKomunitas"></span>
                    </div>
                </div>
                
                {{-- INFO TANGGAL --}}
                <div class="alert alert-info py-2 mb-3">
                    <i class="fa-solid fa-calendar-day me-2"></i>
                    <strong>Tanggal:</strong> <span id="displayTanggal"></span>
                </div>
                
                <div id="daftarJadwalTerpilih"></div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <h5>Total Bayar: <span class="text-success fw-bold" id="totalHarga">Rp 0</span></h5>
                        <small class="text-muted" id="infoKomunitas" style="display:none;">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            Nama komunitas akan disimpan bersama pemesanan.
                        </small>
                    </div>
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
                {{-- INFO KOMUNITAS DI MODAL --}}
                <div id="summaryKomunitasInfo" class="mb-3" style="display:none;">
                    <div class="alert alert-info py-2 mb-3">
                        <i class="fa-solid fa-users me-2"></i>
                        <strong>Komunitas:</strong> <span id="summaryNamaKomunitas"></span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Section:</strong>
                    <p class="mb-0" id="summarySection"></p>
                </div>
                <div class="mb-3">
                    <strong>Tanggal:</strong>
                    <p class="mb-0" id="summaryTanggal"></p>
                </div>
                <div class="mb-3">
                    <strong>Jadwal:</strong>
                    <div id="summaryJadwal"></div>
                </div>
                <div class="mb-3">
                    <strong>Total Bayar:</strong>
                    <p class="mb-0 fw-bold text-warning fs-5" id="summaryTotal"></p>
                </div>
                
                {{-- KONFIRMASI KOMUNITAS --}}
                <div class="alert alert-warning py-2 mb-0">
                    <i class="fa-solid fa-check-circle me-2"></i>
                    <small>Pastikan data di atas sudah benar sebelum melanjutkan pembayaran.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i> Periksa Kembali
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
let selectedTanggal = null;
let selectedJadwals = [];
let summaryModal;
let namaKomunitas = '';
let allJadwals = [];
let isLoadingJadwal = false;

// Initialize Bootstrap Modal
document.addEventListener('DOMContentLoaded', function() {
    summaryModal = new bootstrap.Modal(document.getElementById('summaryModal'));

    // Set min date untuk input tanggal (hari ini)
    const today = new Date().toISOString().split('T')[0];
    const inputTanggal = document.getElementById('inputTanggal');
    inputTanggal.min = today;
    inputTanggal.value = ''; // Kosongkan dulu, biar user pilih sendiri

    // Event listener untuk input nama komunitas
    document.getElementById('nama_komunitas').addEventListener('input', function() {
        namaKomunitas = this.value.trim();
        updateKomunitasDisplay();
    });

    // Event listener untuk input tanggal - AUTO LOAD JADWAL
    inputTanggal.addEventListener('change', function() {
        if (!this.value) return;
        
        selectedTanggal = this.value;
        
        // Validasi: pastikan sudah pilih section
        if (!selectedSection) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Lapangan Dulu!',
                text: 'Silakan pilih lapangan terlebih dahulu.',
                confirmButtonColor: '#41A67E'
            });
            this.value = '';
            return;
        }
        
        updateTanggalDisplay();
        
        // Langsung load jadwal OTOMATIS
        loadJadwalAuto(selectedTanggal);
    });

    // Tombol hari ini
    document.getElementById('tombolHariIni').addEventListener('click', function() {
        document.getElementById('inputTanggal').value = today;
        document.getElementById('inputTanggal').dispatchEvent(new Event('change'));
    });

    // Tombol refresh jadwal
    document.getElementById('refreshJadwalBtn').addEventListener('click', function() {
        if (selectedTanggal && selectedSection) {
            loadJadwalAuto(selectedTanggal);
        }
    });

    // Tombol ganti tanggal (semua tombol)
    const gantiTanggalButtons = ['gantiTanggalBtn', 'gantiTanggalBtn2'];
    gantiTanggalButtons.forEach(btnId => {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.addEventListener('click', showPilihTanggal);
        }
    });

    // ... (kode pending payment yang sama seperti sebelumnya) ...
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
                        }).catch(()=>{}).finally(() => window.location.href = '/penyewa/tiket');
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

        // Reset state
        selectedJadwals = [];
        selectedTanggal = null;
        
        // Reset input tanggal
        document.getElementById('inputTanggal').value = '';
        
        // Tampilkan container pilih tanggal
        document.getElementById('pilihTanggalContainer').style.display = 'block';
        document.getElementById('jadwalContainer').style.display = 'none';
        document.getElementById('ringkasanJadwal').style.display = 'none';
        document.getElementById('jadwalList').innerHTML = '';
        
        // Fokus ke input tanggal
        setTimeout(() => {
            document.getElementById('inputTanggal').focus();
        }, 100);
    });
});

// ========== LOAD JADWAL OTOMATIS SETELAH PILIH TANGGAL ==========
async function loadJadwalAuto(tanggal) {
    if (!selectedSection || !tanggal || isLoadingJadwal) return;
    
    isLoadingJadwal = true;
    
    // Tampilkan loading di container tanggal
    document.getElementById('loadingTanggal').style.display = 'block';
    
    try {
        const response = await fetch(`/jadwal/section/${selectedSection}?tanggal=${tanggal}`);
        const data = await response.json();
        
        // Filter hanya jadwal dengan tanggal yang dipilih
        const jadwals = data.filter(j => j.tanggal === tanggal);
        allJadwals = jadwals;
        
        // Sembunyikan loading tanggal
        document.getElementById('loadingTanggal').style.display = 'none';
        
        // Tampilkan container jadwal
        document.getElementById('pilihTanggalContainer').style.display = 'none';
        document.getElementById('jadwalContainer').style.display = 'block';
        
        // Render jadwal
        renderJadwal(jadwals);
        
        // Reset pilihan jadwal
        selectedJadwals = [];
        updateRingkasan();
        
    } catch (error) {
        console.error('Error loading jadwal:', error);
        document.getElementById('loadingTanggal').style.display = 'none';
        
        Swal.fire({
            icon: 'error',
            title: 'Gagal memuat jadwal',
            text: 'Terjadi kesalahan saat memuat jadwal. Silakan coba lagi.',
            confirmButtonColor: '#41A67E'
        });
    } finally {
        isLoadingJadwal = false;
    }
}

// ========== UPDATE DISPLAY TANGGAL ==========
function updateTanggalDisplay() {
    if (!selectedTanggal) return;
    
    const dateObj = new Date(selectedTanggal);
    const formattedDate = dateObj.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
    
    // Update display di berbagai tempat
    document.getElementById('currentDateDisplay').textContent = formattedDate;
    document.getElementById('tanggalDipilihText').textContent = formattedDate;
    document.getElementById('displayTanggal').textContent = formattedDate;
    document.getElementById('summaryTanggal').textContent = formattedDate;
    
    document.getElementById('tanggalTerpilihInfo').style.display = 'inline-block';
}

// ========== RENDER JADWAL ==========
function renderJadwal(jadwals) {
    const jadwalList = document.getElementById('jadwalList');
    const loadingJadwal = document.getElementById('loadingJadwal');
    const noJadwalMessage = document.getElementById('noJadwalMessage');
    
    // Sembunyikan loading jadwal
    loadingJadwal.style.display = 'none';
    
    if (jadwals.length === 0) {
        jadwalList.innerHTML = '';
        noJadwalMessage.style.display = 'block';
        return;
    }
    
    noJadwalMessage.style.display = 'none';
    
    // Kelompokkan berdasarkan jam
    const groupByTime = {};
    jadwals.forEach(j => {
        const key = `${j.jam_mulai}-${j.jam_selesai}`;
        if (!groupByTime[key]) {
            groupByTime[key] = {
                jam_mulai: j.jam_mulai,
                jam_selesai: j.jam_selesai,
                harga_sewa: j.harga_sewa,
                tersedia: j.tersedia,
                id: j.id
            };
        }
    });
    
    // Tampilkan jadwal
    jadwalList.innerHTML = `
        <div class="row g-3">
            ${Object.values(groupByTime).map(j => `
                <div class="col-md-3 col-sm-6">
                    <div class="jadwal-item ${j.tersedia ? 'available' : 'unavailable'}"
                         data-id="${j.id}"
                         data-mulai="${j.jam_mulai}"
                         data-selesai="${j.jam_selesai}"
                         data-harga="${j.harga_sewa}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fa-solid fa-clock me-1"></i>
                                <strong>${j.jam_mulai} - ${j.jam_selesai}</strong>
                            </div>
                            ${!j.tersedia ? '<span class="badge bg-danger">Booked</span>' : ''}
                        </div>
                        <small class="d-block mt-2 fw-semibold text-success">
                            Rp ${parseInt(j.harga_sewa).toLocaleString('id-ID')}
                        </small>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    // Event listener untuk pilih jadwal
    document.querySelectorAll('.jadwal-item.available').forEach(item => {
        item.addEventListener('click', function() {
            const jadwalId = this.dataset.id;
            const jadwalInfo = {
                id: jadwalId,
                tanggal: selectedTanggal,
                mulai: this.dataset.mulai,
                selesai: this.dataset.selesai,
                harga: parseInt(this.dataset.harga)
            };

            // Toggle selection
            const index = selectedJadwals.findIndex(j => j.id === jadwalId);
            if (index > -1) {
                selectedJadwals.splice(index, 1);
                this.classList.remove('selected');
            } else {
                selectedJadwals.push(jadwalInfo);
                this.classList.add('selected');
            }

            updateRingkasan();
        });
    });
}

// ========== TAMPILKAN PILIH TANGGAL ==========
function showPilihTanggal() {
    // Reset jadwal container
    document.getElementById('jadwalContainer').style.display = 'none';
    document.getElementById('jadwalList').innerHTML = '';
    document.getElementById('noJadwalMessage').style.display = 'none';
    document.getElementById('loadingJadwal').style.display = 'none';
    
    // Tampilkan pilih tanggal container
    document.getElementById('pilihTanggalContainer').style.display = 'block';
    document.getElementById('loadingTanggal').style.display = 'none';
    
    // Sembunyikan ringkasan
    document.getElementById('ringkasanJadwal').style.display = 'none';
    
    // Reset pilihan jadwal
    selectedJadwals = [];
    
    // Fokus ke input tanggal
    setTimeout(() => {
        document.getElementById('inputTanggal').focus();
    }, 100);
}

// ========== UPDATE DISPLAY KOMUNITAS ==========
function updateKomunitasDisplay() {
    const komunitasInfo = document.getElementById('komunitasInfo');
    const displayNamaKomunitas = document.getElementById('displayNamaKomunitas');
    const infoKomunitas = document.getElementById('infoKomunitas');
    const summaryKomunitasInfo = document.getElementById('summaryKomunitasInfo');
    const summaryNamaKomunitas = document.getElementById('summaryNamaKomunitas');

    if (namaKomunitas) {
        komunitasInfo.style.display = 'block';
        displayNamaKomunitas.textContent = namaKomunitas;
        infoKomunitas.style.display = 'block';
        summaryKomunitasInfo.style.display = 'block';
        summaryNamaKomunitas.textContent = namaKomunitas;
    } else {
        komunitasInfo.style.display = 'none';
        infoKomunitas.style.display = 'none';
        summaryKomunitasInfo.style.display = 'none';
    }
}

// ========== UPDATE RINGKASAN ==========
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

    daftarJadwalContainer.innerHTML = selectedJadwals.map((jadwal, index) => `
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
            <div>
                <strong>${jadwal.mulai} - ${jadwal.selesai}</strong>
                <span class="ms-2 text-muted">${jadwal.tanggal}</span>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3">Rp ${jadwal.harga.toLocaleString('id-ID')}</span>
                <button class="btn btn-sm btn-outline-danger" onclick="hapusJadwal('${jadwal.id}')">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        </div>
    `).join('');

    const totalHarga = selectedJadwals.reduce((total, jadwal) => total + jadwal.harga, 0);
    totalHargaEl.textContent = `Rp ${totalHarga.toLocaleString('id-ID')}`;
    updateKomunitasDisplay();
}

// ========== HAPUS JADWAL ==========
function hapusJadwal(jadwalId) {
    const index = selectedJadwals.findIndex(j => j.id === jadwalId);
    if (index > -1) {
        selectedJadwals.splice(index, 1);
        const item = document.querySelector(`.jadwal-item[data-id="${jadwalId}"]`);
        if (item) item.classList.remove('selected');
        updateRingkasan();
    }
}

// ========== LANJUT KE PEMBAYARAN ==========
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

    namaKomunitas = document.getElementById('nama_komunitas').value.trim();

    // Update modal summary
    document.getElementById('summarySection').innerText = 
        document.querySelector('.section-card.active h6').innerText;
    
    const tanggalText = new Date(selectedTanggal).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
    document.getElementById('summaryTanggal').innerText = tanggalText;
    
    const summaryJadwal = document.getElementById('summaryJadwal');
    summaryJadwal.innerHTML = selectedJadwals.map(jadwal => `
        <div class="mb-1">
            ${jadwal.mulai} - ${jadwal.selesai}
            <span class="ms-2">Rp ${jadwal.harga.toLocaleString('id-ID')}</span>
        </div>
    `).join('');
    
    const totalHarga = selectedJadwals.reduce((total, jadwal) => total + jadwal.harga, 0);
    document.getElementById('summaryTotal').innerText = `Rp ${totalHarga.toLocaleString('id-ID')}`;

    // Update display komunitas
    if (namaKomunitas) {
        document.getElementById('summaryKomunitasInfo').style.display = 'block';
        document.getElementById('summaryNamaKomunitas').textContent = namaKomunitas;
    } else {
        document.getElementById('summaryKomunitasInfo').style.display = 'none';
    }

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
        showConfirmButton: false
    });

    try {
        const jadwalIds = selectedJadwals.map(j => parseInt(j.id));
        namaKomunitas = document.getElementById('nama_komunitas').value.trim();
        
        const res = await fetch('{{ route("midtrans.token") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                lapangan_id: {{ $lapangan->id }},
                jadwal_ids: jadwalIds,
                nama_komunitas: namaKomunitas || null
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

        Swal.close();
        summaryModal.hide();

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
                    }).catch(()=>{}).finally(() => window.location.href = '/penyewa/tiket');
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
