@extends('layouts.sidebar')

@section('title', 'Scan Tiket')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pemilik.css') }}">
<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">Scan Tiket</h2>

    <div class="scan-wrapper">
        <!-- Kamera Scanner -->
        <div id="barcode-scanner"></div>

        <!-- Hasil Scan -->
        <div id="result-box">
            <h5>Hasil Scan</h5>
            <div id="result">Arahkan kamera ke barcode tiket untuk mulai memindai...</div>
        </div>
    </div>
</div>

{{-- QuaggaJS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
{{-- PDF.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const resultBox = document.getElementById('result');
    let isProcessing = false;
    let liveStarted = false;

    const showLoading = (kode, sumber) => {
        resultBox.innerHTML = `
            <span class="loader"></span> Memverifikasi ${sumber} <b>${kode}</b> ...
        `;
    };

    const showError = (message) => {
        resultBox.innerHTML = `<span style="color:red">${message}</span>`;
    };

    const updateResult = (data) => {
        if(data.status === 'success'){
            resultBox.innerHTML = `
                <b>Nama Penyewa:</b> ${data.data.nama_penyewa} <br>
                <b>Status Scan:</b> ${data.data.status_scan} <br>
                <b>Tanggal Main:</b> ${data.data.tanggal_main} <br>
                <b>Status Pembayaran:</b> ${data.data.status_pembayaran} <br>
                <b>Waktu Scan:</b> ${data.data.waktu_scan}
            `;
        } else {
            showError(data.message);
        }
    };

    const verifyKode = (kode, sumberLabel = 'kode tiket') => {
        showLoading(kode, sumberLabel);
        fetch(`/verify-tiket/${kode}`)
            .then(res => res.json())
            .then(updateResult)
            .catch(err => {
                console.error(err);
                showError('Terjadi kesalahan server');
            })
            .finally(() => {
                setTimeout(() => { isProcessing = false; }, 2000);
            });
    };

    const startLiveScanner = () => {
        Quagga.init({
            inputStream: {
                name : "Live",
                type : "LiveStream",
                target: document.querySelector('#barcode-scanner'),
                constraints: { facingMode: "environment" }
            },
            decoder: { readers: ["code_128_reader"] },
            locate: true
        }, function(err) {
            if (err) {
                console.error(err);
                showError('Tidak bisa mengakses kamera. Pastikan izin kamera aktif.');
                return;
            }
            Quagga.start();
            liveStarted = true;
        });

        Quagga.onDetected(function(result) {
            if (isProcessing) { return; }
            const code = result?.codeResult?.code;
            if (!code) { return; }
            isProcessing = true;
            verifyKode(code, 'hasil kamera');
        });
    };

    startLiveScanner();

    window.addEventListener('beforeunload', () => {
        if (liveStarted) {
            Quagga.stop();
        }
    });
});
</script>
@endsection
