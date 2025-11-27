<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scan Tiket • Petugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/logo-sewalap.svg') }}" type="image/svg+xml">
    <style>
        body { background:#f5f7fb; font-family: Inter, system-ui, -apple-system, 'Segoe UI', sans-serif; }
        .topbar { background:#41A67E; color:#fff; padding:14px 18px; }
        .brand { font-weight:700; letter-spacing:.4px; }
        .scan-wrapper { max-width: 900px; margin: 30px auto; }
        #qr-reader { width: 100%; max-width: 640px; margin: auto; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 24px rgba(25,135,84,.2); }
        .scanner-status { margin-top: 15px; text-align: center; background:#198754; padding:10px 20px; color:white; font-weight:600; border-radius:25px; }
        #result-box { background:#fff; padding:25px; border-radius:16px; margin-top:25px; box-shadow:0 4px 15px rgba(0,0,0,.1); }
        #result-box h5 { color:#198754; margin-bottom:15px; font-weight:700; }
        .success-result, .error-result { border-radius:12px; padding:20px; margin-top:12px; }
        .success-result { background:#d4edda; border-left:6px solid #28a745; }
        .error-result { background:#f8d7da; border-left:6px solid #dc3545; }
        .badge { padding:6px 12px; border-radius:6px; font-size:13px; font-weight:600; }
    </style>
</head>
<body>

<header class="topbar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="brand">SEWA-LAPANG • Petugas Kasir</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('petugas.index') }}" class="btn btn-light btn-sm">Kembali ke POS</a>
        <div class="text-end me-2 d-none d-md-block">
            <small>Petugas: <strong>{{ auth()->user()->name ?? '-' }}</strong></small>
        </div>
        <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center"
             style="width:36px;height:36px">
            {{ substr(auth()->user()->name ?? 'P', 0, 1) }}
        </div>
    </div>
</header>

<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success"><i class="fas fa-qrcode"></i> Scan Tiket QR</h2>

    <div class="scan-wrapper">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <div class="small text-muted mb-1">Pilih mode scan</div>
                <div class="btn-group" role="group" id="checkpointToggle">
                    <button type="button" class="btn btn-outline-success active" data-checkpoint="gor">
                        Masuk Arena
                    </button>
                    <button type="button" class="btn btn-outline-success" data-checkpoint="lapang">
                        Masuk Lapang
                    </button>
                </div>
            </div>
            <div class="small text-muted">Scan di pintu Arena dulu, lalu pintu Lapang.</div>
        </div>

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

<!-- Library scanner -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", async function () {

    const resultBox = document.getElementById('result');
    const scannerStatus = document.querySelector('.scanner-status');
    const checkpointButtons = document.querySelectorAll('[data-checkpoint]');
    let currentCheckpoint = 'gor';

    let isProcessing = false;
    let lastScan = "";
    let lastTime = 0;

    checkpointButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            checkpointButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentCheckpoint = btn.dataset.checkpoint || 'gor';
            updateStatus(`Siap memindai (${currentCheckpoint === 'gor' ? 'Masuk Arena' : 'Masuk Lapang'})`, "fa-circle-notch fa-spin");
        });
    });

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

    function renderResult(payload, statusFlag, message, isSuccess) {
        const statusLabel = (() => {
            const map = {
                valid_lobby: { text: 'Scan Arena', cls: 'bg-info text-dark' },
                valid_lapang: { text: 'Scan Lapang', cls: 'bg-success' },
                expired: { text: 'Expired', cls: 'bg-danger' },
                double_scan: { text: 'Double Scan', cls: 'bg-warning text-dark' },
                double_scan_lobby: { text: 'Sudah Scan Arena', cls: 'bg-warning text-dark' },
                double_scan_lapang: { text: 'Sudah Scan Lapang', cls: 'bg-warning text-dark' },
                too_early: { text: 'Belum Waktunya', cls: 'bg-secondary' },
                unpaid: { text: 'Belum Dibayar', cls: 'bg-secondary' },
                not_found: { text: 'Tidak Ditemukan', cls: 'bg-secondary' },
                invalid: { text: 'Tidak Valid', cls: 'bg-secondary' },
            };

            if (statusFlag === 'valid') {
                return currentCheckpoint === 'gor'
                    ? map.valid_lobby
                    : map.valid_lapang;
            }

            return map[statusFlag] || { text: 'Info', cls: 'bg-secondary' };
        })();

        const scanStatus = payload.status_scan === 'sudah_scan'
            ? '<span class="badge bg-success">Sudah Scan Lapang</span>'
            : payload.status_scan === 'scan_lobby'
                ? '<span class="badge bg-info text-dark">Sudah Scan Arena</span>'
                : '<span class="badge bg-warning text-dark">Belum Scan</span>';

        resultBox.innerHTML = `
            <div class="${isSuccess ? 'success-result' : 'error-result'}">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge ${statusLabel.cls}">${statusLabel.text}</span>
                    <strong>${message || (isSuccess ? 'Tiket valid' : 'Tiket tidak valid')}</strong>
                </div>
                <div><strong>Nama Penyewa:</strong> ${payload.nama_penyewa ?? '-'}</div>
                <div><strong>Kode Tiket:</strong> ${payload.kode_tiket ?? '-'}</div>
                <div><strong>Lapangan:</strong> ${payload.lapangan ?? '-'}</div>
                <div><strong>Jam Main:</strong> ${payload.jam_main ?? '-'}</div>
                <div><strong>Durasi:</strong> ${payload.durasi ?? '-'}</div>
                <div><strong>Status Scan:</strong> ${scanStatus}</div>
                <div><strong>Tanggal Main:</strong> ${payload.tanggal_main ?? '-'}</div>
                <div><strong>Pembayaran:</strong> ${payload.status_pembayaran ?? '-'}</div>
                <div><strong>Waktu Scan:</strong> ${payload.waktu_scan ?? '-'}</div>
            </div>
        `;
    }

    const verifyBaseUrl = @json(route('petugas.verify-tiket', ['kode' => '__CODE__']));

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
            const verifyUrl = verifyBaseUrl.replace('__CODE__', encodeURIComponent(kode));
            const res = await fetch(`${verifyUrl}?checkpoint=${currentCheckpoint}`, {
                headers: { 'Accept': 'application/json' }
            });

            let json;
            try {
                json = await res.json();
            } catch (e) {
                throw new Error('INVALID_JSON');
            }

            if (!res.ok && !json) {
                throw new Error('BAD_RESPONSE');
            }

            const flag = json.status_flag || (json.status === "success" ? "valid" : "invalid");
            const payload = json.data || {};
            const message = json.message || (json.status === "success" ? "Tiket valid" : "QR tidak valid");

            if (json.status === "success") {
                updateStatus("Scan Berhasil!", "fa-check-circle");
                renderResult(payload, flag, message, true);
            } else {
                updateStatus("Tiket Tidak Valid", "fa-times-circle");
                renderResult(payload, flag, message, false);
            }
        } catch (e) {
            updateStatus("Error Koneksi", "fa-exclamation-triangle");
            showError("Tidak dapat terhubung ke server atau format respons tidak valid. Pastikan sesi login masih aktif.");
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
</body>
</html>
