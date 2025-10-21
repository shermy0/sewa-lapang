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
            ['label' => 'Dashboard', 'icon' => 'fa-solid fa-chart-pie', 'url' => '#'],
            ['label' => 'Data Lapangan', 'icon' => 'fa-solid fa-futbol', 'url' => '#'],
            ['label' => 'Pemesanan', 'icon' => 'fa-solid fa-calendar-check', 'url' => '#'],
            ['label' => 'Pembayaran', 'icon' => 'fa-solid fa-money-bill-wave', 'url' => '#'],
            ['label' => 'Laporan', 'icon' => 'fa-solid fa-file-invoice', 'url' => '#'],
            ['label' => 'Pengguna', 'icon' => 'fa-solid fa-users', 'url' => '#'],
            ['label' => 'Pengaturan Akun', 'icon' => 'fa-solid fa-gear', 'url' => '#'],
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
                'route' => 'penyewa.favorit.index',
            ],
            [
                'label' => 'Pemesanan Saya',
                'icon' => 'fa-solid fa-calendar-days',
                'route' => 'penyewa.pemesanan',
            ],
            [
                'label' => 'Pembayaran',
                'icon' => 'fa-solid fa-wallet',
                'route' => 'penyewa.pembayaran',
            ],
            [
                'label' => 'Riwayat Sewa',
                'icon' => 'fa-solid fa-clock-rotate-left',
                'route' => 'penyewa.riwayat',
            ],
            [
                'label' => 'Pengaturan Akun',
                'icon' => 'fa-solid fa-user-gear',
                'route' => 'penyewa.akun',
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
    <img src="{{ Auth::user()->foto_profil ? asset('storage/' . Auth::user()->foto_profil) : asset('images/profile.jpg') }}" alt="Profile" class="profile-photo">
    <div class="user-meta">
      <h6 class="mb-0">{{ Auth::user()->name }}</h6>
      <small class="text-muted text-capitalize">{{ Auth::user()->role }}</small>
    </div>
  </div>

  <nav class="menu-list">
    @foreach ($menuItems as $item)
      @php
          $routes = $item['active_routes'] ?? (isset($item['route']) ? [$item['route']] : []);
          $isActive = $routes ? Route::currentRouteNamed(...$routes) : false;
          $url = '#';

          if (isset($item['route']) && Route::has($item['route'])) {
              $url = route($item['route'], $item['params'] ?? []);
          } elseif (isset($item['url'])) {
              $url = $item['url'];
          }
      @endphp
      <a href="{{ $url }}" class="menu-link {{ $isActive ? 'active' : '' }}">
        <i class="{{ $item['icon'] }}"></i>
        <span class="menu-text">{{ $item['label'] }}</span>
      </a>
    @endforeach
  </nav>

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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('mainContent');
  const toggleSidebar = document.getElementById('toggleSidebar');

  if (toggleSidebar && sidebar && mainContent) {
      toggleSidebar.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
      });
  }

  const sessionErrors = @json($errors->any() ? $errors->all() : []);

  if (typeof Swal !== 'undefined') {
      const flashMessages = {
          error: @json(session('error')),
          success: @json(session('success')),
          status: @json(session('status')),
          warning: @json(session('warning')),
          info: @json(session('info')),
      };

      const flashTitles = {
          error: 'Terjadi Kesalahan',
          success: 'Berhasil',
          status: 'Berhasil',
          warning: 'Perhatian',
          info: 'Informasi',
      };

      const flashOrder = ['error', 'success', 'status', 'warning', 'info'];

      for (const type of flashOrder) {
          const message = flashMessages[type];
          if (!message) {
              continue;
          }

          Swal.fire({
              icon: type === 'status' ? 'success' : type,
              title: flashTitles[type] ?? 'Informasi',
              text: message,
              confirmButtonColor: '#41A67E',
          });

          break;
      }

      if (sessionErrors.length) {
          Swal.fire({
              icon: 'error',
              title: 'Validasi Gagal',
              html: `<ul style="text-align:left;margin:0;padding-left:1rem;">${sessionErrors.map(error => `<li>${error}</li>`).join('')}</ul>`,
              confirmButtonColor: '#41A67E',
          });
      }

      document.querySelectorAll('form[data-confirm]').forEach((form) => {
          form.addEventListener('submit', (event) => {
              if (form.dataset.confirmed === 'true') {
                  return;
              }

              event.preventDefault();

              const confirmOptions = {
                  title: form.dataset.confirmTitle || 'Apakah Anda yakin?',
                  text: form.dataset.confirm || '',
                  icon: form.dataset.confirmIcon || 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#41A67E',
                  cancelButtonColor: '#d33',
                  confirmButtonText: form.dataset.confirmButton || 'Ya',
                  cancelButtonText: form.dataset.cancelButton || 'Batal',
              };

              Swal.fire(confirmOptions).then((result) => {
                  if (result.isConfirmed) {
                      form.dataset.confirmed = 'true';
                      form.submit();
                  }
              });
          });
      });
  }
</script>
@stack('scripts')

</body>
</html>
