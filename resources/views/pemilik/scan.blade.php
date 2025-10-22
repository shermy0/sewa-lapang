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
<script>
document.addEventListener('DOMContentLoaded', function(){
    Quagga.init({
        inputStream : {
            name : "Live",
            type : "LiveStream",
            target: document.querySelector('#barcode-scanner'),
            constraints: { facingMode: "environment" }
        },
        decoder : { readers : ["code_128_reader"] }
    }, function(err) {
        if (err) { console.error(err); return; }
        Quagga.start();
    });

    let isProcessing = false; // biar gak dobel scan

    Quagga.onDetected(function(data) {
        if (isProcessing) return;
        isProcessing = true;

        let kode = data.codeResult.code;
        document.getElementById('result').innerHTML = `
            <span class="loader"></span> Memverifikasi kode tiket <b>${kode}</b> ...
        `;

        fetch(`/verify-tiket/${kode}`)
            .then(res => res.json())
            .then(result => {
                if(result.status === 'success'){
                    document.getElementById('result').innerHTML = `
                        <b>Nama Penyewa:</b> ${result.data.nama_penyewa} <br>
                        <b>Status Scan:</b> ${result.data.status_scan} <br>
                        <b>Tanggal Main:</b> ${result.data.tanggal_main} <br>
                        <b>Status Pembayaran:</b> ${result.data.status_pembayaran} <br>
                        <b>Waktu Scan:</b> ${result.data.waktu_scan}
                    `;
                } else {
                    document.getElementById('result').innerHTML = `<span style="color:red">${result.message}</span>`;
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('result').innerHTML = `<span style="color:red">Terjadi kesalahan server</span>`;
            })
            .finally(() => {
                setTimeout(() => { isProcessing = false; }, 2000);
            });
    });
});
</script>
@endsection