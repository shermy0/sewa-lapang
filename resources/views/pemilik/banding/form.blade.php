<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Banding Pemilik</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <style>
        body {
            background: #f6f9fb;
            font-family: 'Poppins', sans-serif;
        }
        .banding-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }
        .banding-card {
            max-width: 520px;
            width: 100%;
            border-radius: 24px;
            border: none;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        }
        .brand-circle {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
            font-size: 28px;
            font-weight: 600;
        }
        textarea.form-control {
            resize: none;
        }
        .btn-primary {
            background: #198754;
            border: none;
        }
        .btn-primary:hover {
            background: #157347;
        }
    </style>
</head>
<body>
    <div class="banding-wrapper">
        <div class="card banding-card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="brand-circle">SL</div>
                    <h3 class="fw-bold mb-2">Ajukan Banding Akun Pemilik</h3>
                    <p class="text-muted mb-0">
                        Halaman ini hanya untuk pemilik yang diblokir akibat laporan penyalahgunaan.
                        Sampaikan kronologi dan bukti agar tim dapat meninjau kembali akun Anda.
                    </p>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-warning">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        Terjadi kesalahan pada formulir. Periksa kembali input Anda.
                    </div>
                @endif

                <form action="{{ route('banding.store') }}" method="POST" class="d-grid gap-3" enctype="multipart/form-data">
                    @csrf

                    <div>
                        <label class="form-label fw-semibold text-muted">Email Akun Pemilik</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control form-control-lg @error('email') is-invalid @enderror"
                            value="{{ old('email', $prefillEmail) }}"
                            placeholder="nama@contoh.com"
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label fw-semibold text-muted">Alasan Banding</label>
                        <textarea
                            name="alasan"
                            rows="6"
                            class="form-control form-control-lg @error('alasan') is-invalid @enderror"
                            placeholder="Jelaskan kronologi kejadian dan bukti pendukung"
                            required
                        >{{ old('alasan') }}</textarea>
                        @error('alasan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Minimal 20 karakter.</small>
                    </div>

                    <div>
                        <label class="form-label fw-semibold text-muted">Lampiran Bukti (opsional)</label>
                        <input
                            type="file"
                            name="lampiran"
                            class="form-control form-control-lg @error('lampiran') is-invalid @enderror"
                            accept=".jpg,.jpeg,.png,.pdf"
                        >
                        @error('lampiran')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format yang diterima: JPG, PNG, atau PDF (maks. 2MB).</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">
                        Kirim Banding
                    </button>
                </form>

                <p class="text-center text-muted mt-4 mb-0">
                    Jika mengalami kendala, hubungi admin SewaLap melalui support@sewalap.id
                </p>
            </div>
        </div>
    </div>
</body>
</html>
