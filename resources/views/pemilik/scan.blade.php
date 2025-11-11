@extends('layouts.sidebar')

@section('title', 'Scan Tiket')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pemilik.css') }}">
<style>
    .scan-wrapper {
        max-width: 800px;
        margin: 0 auto;
    }

    #barcode-scanner {
        position: relative;
        width: 100%;
        max-width: 640px;
        height: 480px;
        margin: 0 auto 20px;
        border: 3px solid #198754;
        border-radius: 12px;
        overflow: hidden;
        background: #000;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    #barcode-scanner video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #barcode-scanner canvas {
        position: absolute;
        top: 0;
        left: 0;
    }

    .scanner-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 80%;
        height: 50%;
        border: 3px solid #198754;
        border-radius: 8px;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
        pointer-events: none;
        z-index: 10;
    }

    .scanner-line {
        position: absolute;
        width: 100%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #198754, transparent);
        animation: scan 2s linear infinite;
        box-shadow: 0 0 10px #198754;
    }

    @keyframes scan {
        0%, 100% { top: 0; }
        50% { top: calc(100% - 2px); }
    }

    .scanner-corners {
        position: absolute;
        width: 100%;
        height: 100%;
    }

    .scanner-corners::before,
    .scanner-corners::after {
        content: '';
        position: absolute;
        width: 30px;
        height: 30px;
        border: 3px solid #198754;
    }

    .scanner-corners::before {
        top: -3px;
        left: -3px;
        border-right: none;
        border-bottom: none;
    }

    .scanner-corners::after {
        top: -3px;
        right: -3px;
        border-left: none;
        border-bottom: none;
    }

    .scanner-corners-bottom::before,
    .scanner-corners-bottom::after {
        content: '';
        position: absolute;
        width: 30px;
        height: 30px;
        border: 3px solid #198754;
    }

    .scanner-corners-bottom::before {
        bottom: -3px;
        left: -3px;
        border-right: none;
        border-top: none;
    }

    .scanner-corners-bottom::after {
        bottom: -3px;
        right: -3px;
        border-left: none;
        border-top: none;
    }

    #result-box {
        background: #fff;
        border: 2px solid #dee2e6;
        border-radius: 12px;
        padding: 25px;
        margin-top: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    #result-box h5 {
        color: #198754;
        margin-bottom: 15px;
        font-weight: bold;
        font-size: 18px;
    }

    #result {
        font-size: 16px;
        line-height: 1.8;
    }

    .loader {
        display: inline-block;
        width: 18px;
        height: 18px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #198754;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 10px;
        vertical-align: middle;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .success-result {
        color: #155724;
        padding: 20px;
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border-radius: 8px;
        border-left: 5px solid #28a745;
        margin-top: 10px;
    }

    .success-result h6 {
        font-size: 20px;
        margin-bottom: 15px;
        font-weight: bold;
    }

    .success-result > div {
        margin-bottom: 8px;
    }

    .error-result {
        color: #721c24;
        padding: 20px;
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        border-radius: 8px;
        border-left: 5px solid #dc3545;
        margin-top: 10px;
    }

    .badge {
        padding: 5px 12px;
        font-size: 14px;
        font-weight: 600;
    }

    .scanner-tips {
        background: #e7f3ff;
        border-left: 4px solid #0066cc;
        padding: 15px;
        margin-top: 20px;
        border-radius: 8px;
    }

    .scanner-tips h6 {
        color: #0066cc;
        margin-bottom: 10px;
        font-weight: bold;
    }

    .scanner-tips ul {
        margin: 0;
        padding-left: 20px;
    }

    .scanner-tips li {
        margin-bottom: 5px;
        color: #004080;
    }

    @media (max-width: 768px) {
        #barcode-scanner {
            height: 350px;
        }

        .scanner-overlay {
            width: 90%;
            height: 40%;
        }
    }
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">📱 Scan Tiket Barcode</h2>

    <div class="scan-wrapper">
        <!-- Kamera Scanner -->
        <div id="barcode-scanner">
            <div class="scanner-overlay">
                <div class="scanner-line"></div>
                <div class="scanner-corners"></div>
                <div class="scanner-corners-bottom"></div>
            </div>
        </div>

        <!-- Hasil Scan -->
        <div id="result-box">
            <h5>📋 Hasil Scan</h5>
            <div id="result">Arahkan kamera ke barcode tiket untuk mulai memindai...</div>
        </div>

        <!-- Tips Scanner -->
        <div class="scanner-tips">
            <h6>💡 Tips Scanning:</h6>
            <ul>
                <li>Jarak optimal: 15-25 cm dari kamera</li>
                <li>Pastikan pencahayaan cukup terang</li>
                <li>Posisikan barcode horizontal di area hijau</li>
                <li>Tahan stabil selama 1-2 detik</li>
                <li>Tunggu kamera fokus (gambar tajam)</li>
            </ul>
        </div>
    </div>
</div>

{{-- QuaggaJS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
{{-- jsQR for QR code detection --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsqr/1.4.0/jsQR.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const resultBox = document.getElementById('result');
    let isProcessing = false;
    let liveStarted = false;
    let lastScannedCode = '';
    let lastScanTime = 0;

    const showLoading = (kode, sumber) => {
        resultBox.innerHTML = `
            <div class="d-flex align-items-center">
                <span class="loader"></span>
                <span>Memverifikasi ${sumber} <b>${kode}</b>...</span>
            </div>
        `;
    };

    const showError = (message) => {
        resultBox.innerHTML = `<div class="error-result"><strong>❌ Error:</strong> ${message}</div>`;
    };

    const renderScanResult = (payload = {}) => {
        const statusScan = payload.status_scan_label
            || (payload.status_scan === 'sudah_scan' ? 'Sudah Scan'
                : payload.status_scan === 'belum_scan' ? 'Belum Scan'
                : (payload.waktu_scan && payload.waktu_scan !== '-' ? 'Sudah Scan' : 'Belum Scan'));

        const statusPembayaran = payload.status_pembayaran_label
            || (payload.status_pembayaran
                ? payload.status_pembayaran.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
                : '-');

        return `
            <div class="success-result">
                <div><strong>Nama Penyewa:</strong> ${payload.nama_penyewa || '-'}</div>
                <div><strong>Kode Tiket:</strong> ${payload.kode_tiket || '-'}</div>
                <div><strong>Lapangan:</strong> ${payload.lapangan || '-'}</div>
                <div><strong>Jam Main:</strong> ${payload.jam_main || '-'}</div>
                <div><strong>Durasi:</strong> ${payload.durasi || '-'}</div>
                <div><strong>Status Scan:</strong> ${statusScan}</div>
                <div><strong>Tanggal Main:</strong> ${payload.tanggal_main || '-'}</div>
                <div><strong>Status Pembayaran:</strong> ${statusPembayaran}</div>
                <div><strong>Waktu Scan:</strong> ${payload.waktu_scan || '-'}</div>
            </div>
        `;
    };

    const updateResult = (data) => {
        if(data.status === 'success'){
            resultBox.innerHTML = renderScanResult(data.data);
        } else {
            showError(data.message || 'Tiket tidak valid atau sudah digunakan');
        }
    };

    const readers = [
        "code_128_reader",
        "ean_reader",
        "ean_8_reader",
        "code_39_reader",
        "upc_reader"
    ];

    const verifyKode = (kode, sumberLabel = 'kode tiket') => {
        const now = Date.now();
        if (kode === lastScannedCode && (now - lastScanTime) < 3000) {
            return;
        }

        lastScannedCode = kode;
        lastScanTime = now;

        showLoading(kode, sumberLabel);

fetch(`{{ url('/verify-tiket') }}/${kode}`, {
    method: 'GET',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
    }
})

            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(updateResult)
            .catch(err => {
                console.error('Fetch error:', err);
                showError('Terjadi kesalahan koneksi. Pastikan server berjalan dan route tersedia.');
            })
            .finally(() => {
                setTimeout(() => {
                    isProcessing = false;
                }, 2000);
            });
    };

    const startLiveScanner = () => {
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#barcode-scanner'),
                constraints: {
                    width: { min: 640, ideal: 1280, max: 1920 },
                    height: { min: 480, ideal: 720, max: 1080 },
                    facingMode: "environment",
                    aspectRatio: { min: 1, max: 2 }
                },
                area: {
                    top: "25%",
                    right: "10%",
                    left: "10%",
                    bottom: "25%"
                }
            },
            locator: {
                patchSize: "medium",
                halfSample: true
            },
             numOfWorkers: 2,
            frequency: 15,
decoder: {
    readers: ["code_128_reader"],
            multiple: false

},

            locate: true,
debug: false

        }, function(err) {
            if (err) {
                console.error('Quagga init error:', err);
                showError('❌ Tidak bisa mengakses kamera. Pastikan:<br>1. Izin kamera sudah diberikan<br>2. Menggunakan HTTPS atau localhost<br>3. Kamera tidak digunakan aplikasi lain');
                return;
            }
            console.log("✅ Quagga initialized successfully");
            Quagga.start();
            liveStarted = true;
            resultBox.innerHTML = '📸 Scanner aktif. Dekatkan barcode ke area hijau dan tahan stabil...';
        });

        let detectionCount = {};

        Quagga.onProcessed(function(result) {
            const drawingCtx = Quagga.canvas.ctx.overlay;
            const drawingCanvas = Quagga.canvas.dom.overlay;

            if (result) {
                drawingCtx.clearRect(0, 0, parseInt(drawingCanvas.getAttribute("width")), parseInt(drawingCanvas.getAttribute("height")));

                if (result.boxes) {
                    result.boxes.filter(box => box !== result.box).forEach(box => {
                        Quagga.ImageDebug.drawPath(box, {x: 0, y: 1}, drawingCtx, {
                            color: "rgba(0, 255, 0, 0.3)",
                            lineWidth: 2
                        });
                    });
                }

                if (result.box) {
                    Quagga.ImageDebug.drawPath(result.box, {x: 0, y: 1}, drawingCtx, {
                        color: "#00FF00",
                        lineWidth: 3
                    });
                }

                if (result.codeResult && result.codeResult.code) {
                    Quagga.ImageDebug.drawPath(result.line, {x: 'x', y: 'y'}, drawingCtx, {
                        color: '#FF0000',
                        lineWidth: 3
                    });
                }
            }
        });

        Quagga.onDetected(function(result) {
            if (isProcessing) return;

            const code = result?.codeResult?.code;
            if (!code) return;

            // Validasi dengan multiple detection untuk akurasi
            detectionCount[code] = (detectionCount[code] || 0) + 1;

            console.log(`📷 Code detected: ${code}, Count: ${detectionCount[code]}`);

            // Butuh minimal 2 deteksi yang sama untuk konfirmasi
            if (detectionCount[code] >= 1) {
                isProcessing = true;

                // Reset counter
                detectionCount = {};

                // Beep sound feedback
                try {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);

                    oscillator.frequency.value = 800;
                    oscillator.type = 'sine';

                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.15);

                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.15);
                } catch(e) {
                    console.log('🔇 Audio not supported');
                }

                console.log(`✅ Verifying code: ${code}`);
                verifyKode(code, 'kamera');
            }
        });
    };

    // Check camera permissions
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        resultBox.innerHTML = '⏳ Meminta izin akses kamera...';

        navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: "environment",
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        })
        .then(function(stream) {
            console.log('✅ Camera access granted');
            stream.getTracks().forEach(track => track.stop());
            startLiveScanner();
        })
        .catch(function(err) {
            console.error('❌ Camera permission error:', err);
            showError('Akses kamera ditolak. Silakan:<br>1. Klik ikon gembok/kamera di address bar<br>2. Izinkan akses kamera<br>3. Refresh halaman ini');
        });
    } else {
        showError('Browser Anda tidak mendukung akses kamera. Gunakan browser modern seperti Chrome, Firefox, atau Safari.');
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (liveStarted) {
            console.log('🛑 Stopping Quagga scanner');
            Quagga.stop();
        }
    });

    // Cleanup on visibility change (tab switch)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && liveStarted) {
            console.log('⏸️ Page hidden, pausing scanner');
        } else if (!document.hidden && liveStarted) {
            console.log('▶️ Page visible, resuming scanner');
        }
    });

});
</script>
@endsection
