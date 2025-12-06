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
      position: sticky;
      top: 0;
      z-index: 1020;
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
      top: 90px;
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

    /* Avatar dropdown: fix crop & hide caret */
    .avatar-toggle {
      padding: 0;
      border: 2px solid rgba(255,255,255,0.6);
      overflow: hidden;
    }
    .avatar-toggle::after {
      display: none;
    }
    .avatar-toggle img {
      object-fit: cover;
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

            {{-- Dropdown Layar Antrian --}}
            <div class="dropdown">
                <button class="btn btn-light btn-sm fw-semibold dropdown-toggle" type="button" id="dropdownDisplay" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-tv me-1"></i> Layar Antrian
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownDisplay" id="displayDropdownMenu">
                    <!-- Dynamic items will be appended here -->
                    <li id="loadingDisplay"><span class="dropdown-item-text text-muted small">Memuat...</span></li>
                </ul>
            </div>

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

            {{-- User Dropdown --}}
            <div class="dropdown">
                <div class="avatar-toggle rounded-circle bg-white text-dark d-flex align-items-center justify-content-center dropdown-toggle"
                    role="button" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false"
                    style="width:36px;height:36px;font-weight:600;cursor:pointer;">
                    @if(auth()->user()->foto_profil)
                        <img src="{{ asset('storage/' . auth()->user()->foto_profil) }}" alt="Profile" class="w-100 h-100 object-fit-cover">
                    @else
                        {{ substr($petugasName ?? auth()->user()->name, 0, 1) }}
                    @endif
                </div>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                    <li><h6 class="dropdown-header">Halo, {{ $petugasName ?? auth()->user()->name }}!</h6></li>
                    <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="fa-solid fa-user-gear me-2"></i> Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="m-0 p-0">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')

    @if(session('success') || session('error'))
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: @json(session('success')),
            timer: 2500,
            showConfirmButton: false
          });
        @endif
        @if(session('error'))
          Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            text: @json(session('error')),
            timer: 3000,
            showConfirmButton: false
          });
        @endif
      });
    </script>
    @endif
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const dropdownBtn = document.getElementById('dropdownDisplay');
        const dropdownMenu = document.getElementById('displayDropdownMenu');
        const loadingItem = document.getElementById('loadingDisplay');
        let isLoaded = false;

        dropdownBtn.addEventListener('show.bs.dropdown', function () {
            if (isLoaded) return;

            fetch("{{ route('petugas.api.lapangan-sections') }}")
                .then(response => response.json())
                .then(data => {
                    // Remove loading item
                    if(loadingItem) loadingItem.remove();

                    if(!data || data.length === 0){
                        const li = document.createElement('li');
                        li.innerHTML = '<span class=\"dropdown-item-text text-muted small\">Tidak ada lapangan</span>';
                        dropdownMenu.appendChild(li);
                        return;
                    }

                    data.forEach(lapangan => {
                        const header = document.createElement('li');
                        header.innerHTML = `<h6 class=\"dropdown-header mb-0\">${lapangan.nama_lapangan}</h6>`;
                        dropdownMenu.appendChild(header);

                        if (lapangan.sections && lapangan.sections.length) {
                            lapangan.sections.forEach(sec => {
                                const li = document.createElement('li');
                                const a = document.createElement('a');
                                a.className = 'dropdown-item';
                                a.href = `{{ route('petugas.display') }}?lapangan_id=${lapangan.id}&section_id=${sec.id}`;
                                a.target = '_blank';
                                a.textContent = sec.nama_section;
                                li.appendChild(a);
                                dropdownMenu.appendChild(li);
                            });
                        } else {
                            const li = document.createElement('li');
                            const a = document.createElement('a');
                            a.className = 'dropdown-item';
                            a.href = `{{ route('petugas.display') }}?lapangan_id=${lapangan.id}`;
                            a.target = '_blank';
                            a.textContent = 'Semua Section';
                            li.appendChild(a);
                            dropdownMenu.appendChild(li);
                        }

                        const divider = document.createElement('li');
                        divider.innerHTML = '<hr class=\"dropdown-divider\">';
                        dropdownMenu.appendChild(divider);
                    });

                    // Remove trailing divider
                    const last = dropdownMenu.lastElementChild;
                    if(last && last.querySelector('hr')) last.remove();
                    isLoaded = true;
                })
                .catch(error => {
                    console.error('Error fetching lapangan:', error);
                    if(loadingItem) loadingItem.innerHTML = '<span class="dropdown-item-text text-danger small">Gagal memuat</span>';
                });
        });
      });
    </script>
</body>
</html>
