@extends('layouts.sidebar')

@section('title', 'Scan Tiket')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">Scan Tiket</h2>

    <div id="barcode-scanner" style="width:500px; height:300px; border:1px solid #ccc"></div>
    <div id="result" class="mt-3"></div>
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
            constraints: { facingMode: "environment" } // kamera belakang
        },
        decoder : { readers : ["code_128_reader"] } // barcode C128
    }, function(err) {
        if (err) { console.error(err); return; }
        Quagga.start();
    });

    Quagga.onDetected(function(data) {
        let kode = data.codeResult.code;
        document.getElementById('result').innerText = "Memverifikasi: " + kode;

        // Kirim ke server untuk verifikasi
        fetch(`/verify-tiket/${kode}`)
            .then(res => res.json())
            .then(result => {
                if(result.status === 'success'){
                    document.getElementById('result').innerHTML = `
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
            });
    });
});
</script>
@endsection