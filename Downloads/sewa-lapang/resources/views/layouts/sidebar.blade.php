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
            ['label' => 'Dashboard', 'icon' => 'fa-solid fa-chart-pie', 'route' => '#'],
            ['label' => 'Data Lapangan', 'icon' => 'fa-solid fa-futbol', 'route' => '#'],
            ['label' => 'Pemesanan', 'icon' => 'fa-solid fa-calendar-check', 'route' => '#'],
            ['label' => 'Pembayaran', 'icon' => 'fa-solid fa-money-bill-wave', 'route' => '#'],
            ['label' => 'Laporan', 'icon' => 'fa-solid fa-file-invoice', 'route' => '#'],
            ['label' => 'Pengguna', 'icon' => 'fa-solid fa-users', 'route' => '#'],
            ['label' => 'Pengaturan Akun', 'icon' => 'fa-solid fa-gear', 'route' => '#'],
        ];
    } else {
        $menuItems = [
            ['label' => 'Beranda', 'icon' => 'fa-solid fa-house', 'route' => 'penyewa.beranda'],
            ['label' => 'Cari Lapangan', 'icon' => 'fa-solid fa-magnifying-glass', 'route' => '#'],
            ['label' => 'Pemesanan Saya', 'icon' => 'fa-solid fa-calendar-days', 'route' => '#'],
            ['label' => 'Pembayaran', 'icon' => 'fa-solid fa-wallet', 'route' => '#'],
            ['label' => 'Riwayat Sewa', 'icon' => 'fa-solid fa-clock-rotate-left', 'route' => '#'],
            ['label' => 'Pengaturan Akun', 'icon' => 'fa-solid fa-user-gear', 'route' => '#'],
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
  <img src="{{ Auth::user()->foto_profil ? asset('storage/' . Auth::user()->foto_profil) : asset('images/profile.jpg') }}" alt="Profile" class="profile-photo">
      <div class="user-meta">
      <h6 class="mb-0">{{ Auth::user()->name }}</h6>
      <small class="text-muted text-capitalize">{{ Auth::user()->role }}</small>
    </div>
  </div>

  <nav class="menu-list">
    @foreach ($menuItems as $item)
      <a href="{{ isset($item['route']) && $item['route'] !== '#' ? route($item['route']) : '#' }}" 
         class="menu-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
        <i class="{{ $item['icon'] }}"></i>
        <span class="menu-text">{{ $item['label'] }}</span>
      </a>
    @endforeach
  </nav>

  <div class="logout-area">
    <form action="{{ route('logout') }}" method="POST">
      @csrf
      <button type="submit" class="logout-btn">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span class="logout-text">Keluar</span>
      </button>
    </form>
  </div>
</aside>

<main class="main-content" id="mainContent">
  @yield('content')
</main>

<script>
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('mainContent');
  const toggleSidebar = document.getElementById('toggleSidebar');

  toggleSidebar.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
  });
</script>
</body>
</html>