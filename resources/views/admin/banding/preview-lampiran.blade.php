<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lampiran Banding #{{ $banding->id }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f6f9ff, #fdfbfb);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            font-family: 'Poppins', sans-serif;
        }
        .preview-card {
            width: 100%;
            max-width: 860px;
            border: none;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.12);
        }
        .preview-img {
            max-height: 580px;
            width: 100%;
            object-fit: contain;
            border-radius: 16px;
            background: #000;
        }
        .badge-id {
            font-size: 0.85rem;
            letter-spacing: 0.08em;
        }
    </style>
</head>
<body>
    <div class="card preview-card">
        <div class="card-body p-4 d-flex flex-column gap-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <div class="badge text-bg-success badge-id">BANDING #{{ $banding->id }}</div>
                    <h5 class="mt-3 mb-1 fw-semibold">Lampiran Bukti</h5>
                    <p class="text-muted mb-0">Lampiran dikirim oleh {{ $banding->pemilik->name ?? 'Pemilik' }}.</p>
                </div>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <img src="{{ $dataUri }}" alt="Lampiran banding" class="preview-img shadow-sm">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">Diunggah pada {{ optional($banding->created_at)->translatedFormat('d F Y, H:i') }}</small>
                <a
                    href="{{ Storage::disk('public')->url($banding->lampiran_path) }}"
                    class="btn btn-success"
                    download
                >
                    <i class="fa-solid fa-download me-1"></i> Unduh file asli
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js" defer></script>
</body>
</html>
