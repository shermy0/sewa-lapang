<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>SEWALAP - Kasir</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --accent: #41A67E;
      --bg: #f5f7fb;
      --card-hover: rgba(0, 0, 0, 0.08);
    }

    body {
      background: var(--bg);
      font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
      margin: 0;
      padding: 0;
    }

    .topbar {
      background: var(--accent);
      color: #fff;
      padding: 12px 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .brand {
      font-weight: 700;
      font-size: 1.2rem;
    }

    .lapangan-card {
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
      overflow: hidden;
      background: #fff;
    }

    .lapangan-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 20px var(--card-hover);
    }

    .lapangan-img {
      height: 140px;
      width: 300px;
      object-fit: cover;
      width: 100%;
    }

    .cart {
      position: sticky;
      top: 20px;
      max-height: calc(100vh - 40px);
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
      padding: 15px;
      overflow-y: auto;
    }

    .cart h6 {
      font-weight: 600;
    }

    .btn-pay {
      background: var(--accent);
      color: #fff;
      font-weight: 600;
    }

    input#searchInput {
      border-radius: 20px;
      max-width: 200px;
    }

    select#filterKategori {
      max-width: 180px;
    }

    @media (max-width: 991px) {
      .cart {
        position: relative;
        height: auto;
        max-height: none;
        margin-top: 15px;
      }
      .lapangan-img {
        height: 120px;
      }
    }

    .card .carousel-inner img {
      height: 140px;
      object-fit: cover;
    }

    .card .text-truncate {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  </style>
  @stack('styles')
</head>
<body>

    {{-- ===================== --}}
    {{--        HEADER         --}}
    {{-- ===================== --}}
    <header class="topbar d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('petugas.index') }}" class="brand fw-bold fs-5 text-decoration-none text-white">SEWALAP</a>
        </div>

        <div class="d-flex align-items-center gap-3">

            {{-- Tombol Kasir --}}
            <a href="{{ route('petugas.index') }}" class="btn btn-light btn-sm fw-semibold">
                <i class="fa-solid fa-cash-register me-1"></i> Kasir
            </a>

            {{-- Tombol Scan QR --}}
            <a href="{{ route('petugas.scan') }}" class="btn btn-light btn-sm fw-semibold">
                <i class="fa-solid fa-qrcode me-1"></i> Scan QR
            </a>
            
             <a href="{{ route('petugas.display') }}" target="_blank" class="btn btn-light btn-sm fw-semibold">
                <i class="fa-solid fa-tv me-1"></i> Layar Antrian
            </a>

            {{-- Tombol Tambah Penyewa --}}
            <a href="{{ route('petugas.penyewa') }}" class="btn btn-light btn-sm fw-semibold">
                <i class="fa-solid fa-user-plus me-1"></i> Tambah Penyewa
            </a>

            {{-- Tombol Tiket --}}
            <a href="{{ route('petugas.tiket') }}" class="btn btn-light btn-sm fw-semibold">
                <i class="fa-solid fa-ticket-simple me-1"></i> Tiket
            </a>

            {{-- Nama Petugas --}}
            <div class="text-end d-none d-md-block">
                <small>Petugas: <strong>{{ $petugasName ?? auth()->user()->name }}</strong></small>
            </div>

            {{-- Inisial --}}
            <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center"
                style="width:36px;height:36px;font-weight:600">
                {{ substr($petugasName ?? auth()->user()->name, 0, 1) }}
            </div>

            {{-- Logout --}}
            <form action="{{ route('logout') }}" method="POST" class="m-0 p-0">
                @csrf
                <button type="submit" class="btn btn-light btn-sm">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>

        </div>
    </header>

    {{-- ===================== --}}
    {{--       CONTENT         --}}
    {{-- ===================== --}}
    <main class="container-fluid py-4">
        @yield('content')
    </main>


    {{-- ===================== --}}
    {{--        SCRIPTS        --}}
    {{-- ===================== --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')

</body>
</html>
