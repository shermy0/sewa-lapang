@extends('layouts.sidebar')

@section('title', 'Scan Tiket')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold text-success mb-3">Scan Tiket Penyewa</h2>
    <p class="text-muted">Arahkan kamera ke QR Code dari tiket penyewa untuk memverifikasi pemesanan.</p>

    <div class="card p-4 shadow-sm">
        <!-- HARUS DIV bukan video -->
        <div id="preview" style="width: 100%; height: 300px;" class="rounded border"></div>
    </div>

    <div id="result" class="mt-3 alert d-none"></div>
</div>

{{-- QR Scanner JS --}}
<script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const qrDiv = document.getElementById("preview");
    const resultDiv = document.getElementById("result");

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        resultDiv.className = "alert alert-danger";
        resultDiv.innerHTML = "Browser kamu tidak mendukung kamera.";
        resultDiv.classList.remove("d-none");
        return;
    }

    const html5QrCode = new Html5Qrcode("preview");

    function onScanSuccess(decodedText) {
        console.log("✅ QR Code terdeteksi:", decodedText);

        html5QrCode.stop().then(() => {
            resultDiv.className = "alert alert-info";
            resultDiv.classList.remove("d-none");
            resultDiv.innerHTML = "Memverifikasi tiket...";

            fetch('{{ route('pemilik.scan') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ kode_tiket: decodedText })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    resultDiv.className = "alert alert-success";
                    resultDiv.innerHTML = data.success;
                } else if (data.error) {
                    resultDiv.className = "alert alert-danger";
                    resultDiv.innerHTML = data.error;
                } else if (data.info) {
                    resultDiv.className = "alert alert-warning";
                    resultDiv.innerHTML = data.info;
                }
            })
            .catch(err => {
                console.error("❌ Error:", err);
                resultDiv.className = "alert alert-danger";
                resultDiv.innerHTML = "Terjadi kesalahan saat memproses tiket.";
            });
        });
    }

    function onScanError(errorMessage) {
        console.warn("Scan error:", errorMessage);
    }

    setTimeout(() => {
        console.log("🎥 Memulai kamera...");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            onScanError
        ).catch(err => {
            console.error("Gagal start kamera:", err);
            resultDiv.className = "alert alert-danger";
            resultDiv.classList.remove("d-none");
            resultDiv.innerHTML = "Gagal mengakses kamera. Pastikan izin kamera diaktifkan.";
        });
    }, 500);
});
</script>
@endsection
