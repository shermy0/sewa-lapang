@extends('layouts.sidebar')

@section('content')
<style>
    :root {
        --primary-green: #41A67E;
        --light-bg: #FAFBFB;
        --white: #FFFFFF;
        --border: #E5E9E8;
        --text: #2E3A35;
    }

    body {
        background-color: var(--light-bg);
        color: var(--text);
    }

    .scan-container {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        padding: 30px;
        max-width: 600px;
        margin: 50px auto;
        text-align: center;
    }

    .scan-title {
        color: var(--primary-green);
        font-weight: bold;
    }

    #reader {
        width: 100%;
        max-width: 400px;
        height: 400px;
        margin: 20px auto;
        border-radius: 16px;
        border: 3px solid var(--primary-green);
        overflow: hidden;
    }

    .btn-scan {
        background-color: var(--primary-green);
        color: var(--white);
        border: none;
        padding: 12px 25px;
        border-radius: 10px;
        font-size: 18px;
        font-weight: 500;
        transition: 0.3s;
    }

    .btn-scan:hover {
        background-color: #36956e;
    }

    .alert {
        border-radius: 10px;
        font-size: 16px;
    }
</style>

<div class="scan-container">
    <h3 class="scan-title mb-3">📷 Scan Tiket Penyewa</h3>
    <p class="text-muted mb-4">Arahkan kamera ke QR Code tiket untuk memverifikasi pembayaran.</p>

    <div id="reader"></div>

    <div id="result" class="mt-4"></div>

    <button onclick="restartScan()" class="btn-scan mt-3">🔁 Scan Lagi</button>

    <div class="mt-4 text-start">
        <small>
            <strong>Contoh Data Dummy:</strong><br>
            - <code>ABC123</code> — Status: Lunas<br>
            - <code>XYZ999</code> — Status: Belum Bayar
        </small>
    </div>
</div>

{{-- Script Kamera QR --}}
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    const html5QrCode = new Html5Qrcode("reader");

    function onScanSuccess(decodedText) {
        html5QrCode.stop();
        document.getElementById("result").innerHTML = `
            <div class="alert alert-info">🔍 Memeriksa tiket...</div>`;

        fetch(`/verify-tiket/${decodedText}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById("result").innerHTML = `
                        <div class="alert alert-success">
                            ✅ Tiket valid!<br>
                            <strong>Kode:</strong> ${data.data.kode_pemesanan}<br>
                            <strong>Tanggal Main:</strong> ${data.data.tanggal_main}
                        </div>`;
                } else {
                    document.getElementById("result").innerHTML = `
                        <div class="alert alert-danger">❌ ${data.message}</div>`;
                }
            })
            .catch(() => {
                document.getElementById("result").innerHTML = `
                    <div class="alert alert-danger">⚠️ Terjadi kesalahan saat memverifikasi tiket.</div>`;
            });
    }

    function restartScan() {
        document.getElementById("result").innerHTML = "";
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 300 },
            onScanSuccess
        );
    }

    // Jalankan scan otomatis saat halaman dibuka
    restartScan();
</script>
@endsection
