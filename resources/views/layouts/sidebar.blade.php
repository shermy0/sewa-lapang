
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'SewaLap Dashboard')</title>

  <link rel="icon" href="{{ asset('images/logo-sewalap.png') }}" type="image/png">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
</head>

<body>
@php
    $user = Auth::user();

    // MENU ADMIN
    if ($user && $user->role === 'admin') {
        $menuItems = [
            [
                'label' => 'Dashboard',
                'icon' => 'fa-solid fa-gauge-high',
                'route' => 'dashboard.admin',
                'active_routes' => ['dashboard.admin'],
            ],
            [
                'label' => 'Pengguna',
                'icon' => 'fa-solid fa-users-gear',
                'route' => 'admin.users.index',
                'active_routes' => ['admin.users.*'],
            ],
            [
                'label' => 'Lapangan',
                'icon' => 'fa-solid fa-warehouse',
                'route' => 'admin.lapangan.index',
                'active_routes' => ['admin.lapangan.*'],
            ],
            [
                'label' => 'Pembayaran',
                'icon' => 'fa-solid fa-money-bill-transfer',
                'route' => 'admin.pembayaran.index',
                'active_routes' => ['admin.pembayaran.*'],
            ],
            [
                'label' => 'Banner',
                'icon' => 'fa-solid fa-image',
                'route' => 'admin.banners.index',
                'active_routes' => ['admin.banners.*'],
            ],
            [
                'label' => 'Laporan Penyalahgunaan',
                'icon' => 'fa-solid fa-flag',
                'route' => 'admin.laporan.penyalahgunaan.index',
                'active_routes' => ['admin.laporan.penyalahgunaan.*'],
            ],
            [
                'label' => 'Banding Pemilik',
                'icon' => 'fa-solid fa-scale-balanced',
                'route' => 'admin.banding.index',
                'active_routes' => ['admin.banding.*'],

            ],
            [
                'label' => 'Pengaturan Akun',
                'icon' => 'fa-solid fa-user-gear',
                'route' => 'admin.account.edit',
                'active_routes' => ['admin.account.*'],
            ],
        ];
    }

    // MENU PEMILIK
    elseif ($user && $user->role === 'pemilik') {
        $menuItems = [
            [
                'label' => 'Dashboard',
                'icon' => 'fa-solid fa-chart-pie',
                'route' => 'dashboard.pemilik',
                'active_routes' => ['dashboard.pemilik'],
            ],
            [
                'label' => 'Data Lapangan',
                'icon' => 'fa-solid fa-futbol',
                'route' => 'lapangan.index',
                'active_routes' => ['lapangan.index'],
            ],
            [
                'label' => 'Kelola Kategori',
                'icon' => 'fa-solid fa-tags',
                'route' => 'kategori.index',
                'active_routes' => ['kategori.index'],
            ],
            [
                'label' => 'Persetujuan',
                'icon' => 'fa-solid fa-check-circle',
                'route' => 'persetujuan.index',
                'active_routes' => ['persetujuan.index'],
            ],
            [
                'label' => 'Scan',
                'icon' => 'fa-solid fa-qrcode',
                'route' => 'pemilik.scan',
                'active_routes' => ['pemilik.scan'],
            ],
            [
                'label' => 'Pengaturan Akun',
                'icon' => 'fa-solid fa-user-gear',
                'route' => 'profile.index',
                'active_routes' => ['profile.index'],
            ],
        ];
    }

    // MENU PENYEWA
    else {
        $menuItems = [
            [
                'label' => 'Beranda',
                'icon' => 'fa-solid fa-house',
                'route' => 'penyewa.beranda',
                'active_routes' => ['penyewa.beranda', 'penyewa.detail'],
            ],
            [
                'label' => 'Favorit',
                'icon' => 'fa-solid fa-heart',
                'route' => 'favorit.index',
                'active_routes' => ['favorit.index'],
            ],
            [
                'label' => 'Pemesanan Saya',
                'icon' => 'fa-solid fa-calendar-days',
                'submenu' => [
                    [
                        'label' => 'Tiket Saya',
                        'route' => 'penyewa.tiket',
                        'active_routes' => ['penyewa.tiket'],
                    ],
                    [
                        'label' => 'Menunggu Pembayaran',
                        'route' => 'penyewa.pembayaran',
                        'active_routes' => ['penyewa.pembayaran'],
                    ],
                    [
                        'label' => 'Riwayat',
                        'route' => 'penyewa.riwayat',
                        'active_routes' => ['penyewa.riwayat'],
                    ],
                ],
            ],
            [
                'label' => 'Pengaturan Akun',
                'icon' => 'fa-solid fa-user-gear',
                'route' => 'profile.index',
                'active_routes' => ['profile.index'],
            ],
        ];
    }
@endphp

<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="brand">
      <img src="{{ asset('images/logo-sewalap.png') }}" alt="Logo SewaLap" class="brand-logo">
      <span class="brand-text">SewaLap</span>
    </div>
    <button class="toggle-sidebar" id="toggleSidebar">
      <i class="fa-solid fa-bars"></i>
    </button>
  </div>

  {{-- Foto Profil --}}
  <div class="user-info">
    @php
        $avatarUrl = $user->foto_profil 
            ? asset('storage/' . $user->foto_profil)
            : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=41A67E&color=fff';
    @endphp

    <img src="{{ $avatarUrl }}" alt="Profile" class="profile-photo">
    <div class="user-meta">
      <h6 class="mb-0">{{ $user->name }}</h6>
      <small class="text-muted text-capitalize">{{ $user->role }}</small>
    </div>
  </div>

  {{-- Menu --}}
  <nav class="menu-list">
    @foreach ($menuItems as $item)
      @if (isset($item['submenu']))
        @php
          $isParentActive = collect($item['submenu'])->contains(fn($sub) => Route::currentRouteNamed(...$sub['active_routes']));
        @endphp
        <div class="menu-item">
          <div class="menu-link dropdown-toggle {{ $isParentActive ? 'active' : '' }}" data-bs-toggle="submenu">
            <div><i class="{{ $item['icon'] }}"></i><span class="menu-text">{{ $item['label'] }}</span></div>
            <i class="fa-solid fa-chevron-down"></i>
          </div>
          <div class="submenu {{ $isParentActive ? 'show' : '' }}">
            @foreach ($item['submenu'] as $sub)
              @php
                $isActive = isset($sub['active_routes']) && Route::currentRouteNamed(...$sub['active_routes']);
              @endphp
              <a href="{{ route($sub['route']) }}" class="{{ $isActive ? 'active' : '' }}">{{ $sub['label'] }}</a>
            @endforeach
          </div>
        </div>
      @else
        @php
          $isActive = isset($item['active_routes']) && Route::currentRouteNamed(...$item['active_routes']);
        @endphp
        <a href="{{ route($item['route']) }}" class="menu-link {{ $isActive ? 'active' : '' }}">
          <i class="{{ $item['icon'] }}"></i> <span class="menu-text">{{ $item['label'] }}</span>
        </a>
      @endif
    @endforeach
  </nav>

  {{-- Logout --}}
  <form id="logout-form" action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="button" id="logout-button" class="logout-btn">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span class="logout-text">Keluar</span>
    </button>
  </form>
</aside>

<main class="main-content" id="mainContent">
  <button class="btn btn-outline-success d-md-none mb-3" id="mobileMenuBtn">
      <i class="fa-solid fa-bars"></i> Menu
  </button>
  @yield('content')
</main>

{{-- SCRIPT --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Sidebar toggle
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('mainContent');
  const toggleSidebar = document.getElementById('toggleSidebar');
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');

  if (toggleSidebar) {
      toggleSidebar.addEventListener('click', () => {
          sidebar.classList.toggle('collapsed');
          mainContent.classList.toggle('expanded');
      });
  }

  if (mobileMenuBtn) {
      mobileMenuBtn.addEventListener('click', () => {
          sidebar.classList.toggle('show');
      });
  }

  // Logout confirm
  document.getElementById('logout-button').addEventListener('click', function() {
    Swal.fire({
        title: 'Yakin ingin keluar?',
        text: "Kamu akan logout dari akun ini",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, keluar!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('logout-form').submit();
        }
    });
  });
</script>

    <!-- ⭐ PENTING: Bootstrap JS Bundle (termasuk Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Stack untuk script tambahan dari halaman child -->
    @stack('scripts')
<script>
  const sidebarEl = document.getElementById('sidebar');
  const mainContentEl = document.getElementById('mainContent');
  const toggleSidebarBtn = document.getElementById('toggleSidebar');
  const dropdownToggles = document.querySelectorAll('[data-bs-toggle="submenu"]');

  if (toggleSidebarBtn && sidebarEl && mainContentEl) {
    toggleSidebarBtn.addEventListener('click', () => {
      sidebarEl.classList.toggle('collapsed');
      mainContentEl.classList.toggle('expanded');
    });
  }

  dropdownToggles.forEach(toggle => {
    toggle.addEventListener('click', () => {
      const submenu = toggle.nextElementSibling;
      const isShown = submenu.classList.contains('show');

      document.querySelectorAll('.submenu').forEach(s => s.classList.remove('show'));
      document.querySelectorAll('.menu-link.dropdown-toggle').forEach(l => l.classList.remove('active'));

      if (!isShown) {
        submenu.classList.add('show');
        toggle.classList.add('active');
      }
    });
  });
</script>


</body>
</html>
