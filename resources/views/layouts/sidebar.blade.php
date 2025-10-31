<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'SewaLap Dashboard')</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
</head>

<body>
@php
    $user = Auth::user();

    if ($user && $user->role === 'pemilik') {
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
                'icon' => 'fa-solid fa-futbol',
                'route' => 'kategori.index',
                'active_routes' => ['kategori.index'],
            ],            ['label' => 'Pemesanan', 'icon' => 'fa-solid fa-calendar-check', 'url' => '#'],
            ['label' => 'Pembayaran', 'icon' => 'fa-solid fa-money-bill-wave', 'url' => '#'],
            ['label' => 'Laporan', 'icon' => 'fa-solid fa-file-invoice', 'url' => '#'],
            ['label' => 'Persetujuan', 'icon' => 'fa-solid fa-check-circle', 'url' => '#'],

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
    } else {
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
            // Dropdown untuk pemesanan
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
                ]
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

  <div class="user-info">
  @php
      $user = Auth::user();
      $avatarUrl = $user->foto_profil
          ? asset('storage/' . $user->foto_profil)
          :'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=41A67E&color=fff';
  @endphp

  <img src="{{ $avatarUrl }}" alt="Profile" class="profile-photo">
  <div class="user-meta">
    <h6 class="mb-0">{{ $user->name }}</h6>
    <small class="text-muted text-capitalize">{{ $user->role }}</small>
  </div>
</div>
  <nav class="menu-list">
    @foreach ($menuItems as $item)
      @if (isset($item['submenu']))
        @php
          // Deteksi apakah salah satu submenu sedang aktif
          $isParentActive = false;
          foreach ($item['submenu'] as $sub) {
              if (isset($sub['active_routes']) && Route::currentRouteNamed(...$sub['active_routes'])) {
                  $isParentActive = true;
                  break;
              }
          }
        @endphp

        {{-- Dropdown --}}
        <div class="menu-item">
          <div class="menu-link dropdown-toggle {{ $isParentActive ? 'active' : '' }}" data-bs-toggle="submenu">
            <div>
              <i class="{{ $item['icon'] }}"></i>
              <span class="menu-text">{{ $item['label'] }}</span>
            </div>
            <i class="fa-solid fa-chevron-down"></i>
          </div>
          <div class="submenu {{ $isParentActive ? 'show' : '' }}">
            @foreach ($item['submenu'] as $sub)
              @php
                $isActive = isset($sub['active_routes']) && Route::currentRouteNamed(...$sub['active_routes']);
                $url = Route::has($sub['route']) ? route($sub['route']) : '#';
              @endphp
              <a href="{{ $url }}" class="{{ $isActive ? 'active' : '' }}">{{ $sub['label'] }}</a>
            @endforeach
          </div>
        </div>
      @else
        {{-- Single menu item --}}
        @php
          $routes = $item['active_routes'] ?? (isset($item['route']) ? [$item['route']] : []);
          $isActive = $routes ? Route::currentRouteNamed(...$routes) : false;
          $url = isset($item['route']) && Route::has($item['route'])
                ? route($item['route'])
                : ($item['url'] ?? '#');
        @endphp
        <a href="{{ $url }}" class="menu-link {{ $isActive ? 'active' : '' }}">
          <i class="{{ $item['icon'] }}"></i>
          <span class="menu-text">{{ $item['label'] }}</span>
        </a>
      @endif
    @endforeach
  </nav>

  <form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit" class="logout-btn">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span class="logout-text">Keluar</span>
    </button>
  </form>
</aside>

<main class="main-content" id="mainContent">
  @yield('content')
</main>
    <!-- ⭐ PENTING: Bootstrap JS Bundle (termasuk Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script Sidebar Toggle -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleSidebar = document.getElementById('toggleSidebar');

        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    </script>

    <!-- Stack untuk script tambahan dari halaman child -->
    @stack('scripts')
<script>
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('mainContent');
  const toggleSidebar = document.getElementById('toggleSidebar');
  const dropdownToggles = document.querySelectorAll('[data-bs-toggle="submenu"]');

  toggleSidebar.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
  });

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
