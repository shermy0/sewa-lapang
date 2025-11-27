<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEWALAP</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .topbar {
            background: #ffffff;
            padding: 10px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        body {
            background: #f5f6fa;
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
            <div class="brand fw-bold fs-5">SEWALAP</div>
            <input id="searchInput" class="form-control form-control-sm d-none d-md-block"
                   placeholder="Cari Lapangan" style="border-radius:20px;max-width:200px;">
        </div>

        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('petugas.scan') }}" class="btn btn-light btn-sm fw-semibold">
                Scan Arena
            </a>
            <a href="{{ route('petugas.display') }}" target="_blank" class="btn btn-light btn-sm fw-semibold">
                <i class="fa-solid fa-tv me-1"></i> Layar Antrian
            </a>
            <div class="text-end d-none d-md-block">
                <small>Petugas: <strong>{{ $petugasName ?? auth()->user()->name }}</strong></small>
            </div>
            <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center"
                 style="width:36px;height:36px;font-weight:600">
                {{ substr($petugasName ?? auth()->user()->name, 0, 1) }}
            </div>
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
    <main class="container py-4">
        @yield('content')
    </main>


    {{-- ===================== --}}
    {{--        SCRIPTS        --}}
    {{-- ===================== --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')

</body>
</html>
