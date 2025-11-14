@extends('layouts.sidebar')

@section('title', 'Scan Tiket')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pemilik.css') }}">

<!-- ========== CSS UI (dipersingkat tapi tetap sama tampilannya) ========== -->
<style>
    .scan-wrapper { max-width: 900px; margin: auto; }
    #qr-reader { width: 100%; max-width: 640px; margin: auto; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 24px rgba(25,135,84,.2); }
    .scanner-status { margin-top: 15px; text-align: center; background:#198754; padding:10px 20px; color:white; font-weight:600; border-radius:25px; }
    #result-box { background:#fff; padding:25px; border-radius:16px; margin-top:25px; box-shadow:0 4px 15px rgba(0,0,0,.1); }
    #result-box h5 { color:#198754; margin-bottom:15px; font-weight:700; }
    .success-result, .error-result { border-radius:12px; padding:20px; margin-top:12px; }
    .success-result { background:#d4edda; border-left:6px solid #28a745; }
    .error-result { background:#f8d7da; border-left:6px solid #dc3545; }
    .badge { padding:6px 12px; border-radius:6px; font-size:13px; font-weight:600; }
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success"><i class="fas fa-qrcode"></i> Scan Tiket QR</h2>

    <div class="scan-wrapper">

        <!-- Scanner -->
        <div id="qr-reader"></div>

        <div class="scanner-status">
            <i class="fas fa-sync-alt fa-spin me-2"></i> Menginisialisasi scanner...
        </div>

        <!-- Hasil -->
        <div id="result-box">
            <h5><i class="fas fa-clipboard-check"></i> Hasil Scan</h5>
            <div id="result">
                <i class="fas fa-camera me-2"></i> Arahkan kamera ke QR code tiket...
            </div>
        </div>

    </div>
</div>

<!-- Library scanner tercepat -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", async function () {

    const resultBox = document.getElementById('result');
    const scannerStatus = document.querySelector('.scanner-status');

    let isProcessing = false;
    let lastScan = "";
    let lastTime = 0;

    function updateStatus(text, icon="fa-circle-notch fa-spin") {
        scannerStatus.innerHTML = `<i class="fas ${icon} me-2"></i>${text}`;
    }

    function showError(msg) {
        resultBox.innerHTML = `
            <div class="error-result">
                <strong><i class="fas fa-exclamation-triangle me-2"></i>Error:</strong> ${msg}
            </div>
        `;
    }

    function renderSuccess(data) {
        const payload = data.data;
        const scanStatus = payload.status_scan === 'sudah_scan' ?
            '<span class="badge bg-success">Sudah Scan</span>' :
            '<span class="badge bg-warning text-dark">Belum Scan</span>';

        resultBox.innerHTML = `
            <div class="success-result">
                <h6><i class="fas fa-check-circle me-2"></i> Tiket Valid</h6>
                <div><strong>Nama Penyewa:</strong> ${payload.nama_penyewa}</div>
                <div><strong>Kode Tiket:</strong> ${payload.kode_tiket}</div>
                <div><strong>Lapangan:</strong> ${payload.lapangan}</div>
                <div><strong>Jam Main:</strong> ${payload.jam_main}</div>
                <div><strong>Durasi:</strong> ${payload.durasi}</div>
                <div><strong>Status Scan:</strong> ${scanStatus}</div>
                <div><strong>Tanggal Main:</strong> ${payload.tanggal_main}</div>
                <div><strong>Pembayaran:</strong> ${payload.status_pembayaran}</div>
                <div><strong>Waktu Scan:</strong> ${payload.waktu_scan}</div>
            </div>
        `;
    }

    async function verify(kode) {
        const now = Date.now();
        if (kode === lastScan && now - lastTime < 2000) return;

        lastScan = kode;
        lastTime = now;

        updateStatus("Memverifikasi tiket...", "fa-sync-alt fa-spin");

        resultBox.innerHTML = `
            <div><i class="fas fa-spinner fa-spin me-2"></i>Memeriksa kode <b>${kode}</b>...</div>
        `;

        try {
            const res = await fetch(`{{ url('/verify-tiket') }}/${kode}`);
            const json = await res.json();

            if (json.status === "success") {
                updateStatus("Scan Berhasil!", "fa-check-circle");
                renderSuccess(json);
            } else {
                updateStatus("Tiket Tidak Valid", "fa-times-circle");
                showError(json.message || "QR tidak valid");
            }
        } catch (e) {
            updateStatus("Error Koneksi", "fa-exclamation-triangle");
            showError("Tidak dapat terhubung ke server.");
        }

        setTimeout(() => {
            updateStatus("Siap Memindai", "fa-circle-notch fa-spin");
        }, 2000);
    }

    function playBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 900;
            gain.gain.value = 0.2;
            osc.start();
            osc.stop(ctx.currentTime + 0.15);
        } catch {}
    }

    // ======== START SCANNER ========
    function startScanner(cameraId) {
        updateStatus("Menyalakan kamera...");

        const qr = new Html5Qrcode("qr-reader");

        qr.start(
            cameraId,
            {
                fps: 15,
                qrbox: { width: 260, height: 260 }
            },
            (decodedText) => {
                if (!isProcessing) {
                    isProcessing = true;
                    playBeep();
                    verify(decodedText.trim());
                    setTimeout(() => isProcessing = false, 1000);
                }
            },
            (err) => {}
        ).catch(err => {
            updateStatus("Gagal membuka kamera", "fa-ban");
            showError("Tidak dapat mengakses kamera. Pastikan izin sudah diberikan.");
        });
    }

    // ======== PILIH KAMERA TERBAIK ========
    const devices = await Html5Qrcode.getCameras();

    if (!devices || devices.length === 0) {
        updateStatus("Tidak ada kamera terdeteksi", "fa-ban");
        showError("Device tidak memiliki kamera.");
        return;
    }

    // Prefer kamera belakang
    const backCam = devices.find(d =>
        d.label.toLowerCase().includes("back") ||
        d.label.toLowerCase().includes("rear")
    );

    startScanner(backCam ? backCam.id : devices[0].id);

    updateStatus("Siap Memindai", "fa-circle-notch fa-spin");
});
</script>

@endsection
