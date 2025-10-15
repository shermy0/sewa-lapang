<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin SewaLap')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <style>
:root {
  --primary-green: #41A67E;
  --light-bg: #FAFBFB;
  --white: #FFFFFF;
  --border: #E5E9E8;
  --text: #2E3A35;
}

body {
  font-family: 'Poppins', sans-serif;
  background-color: var(--light-bg);
  color: var(--text);
  margin: 0;
  overflow-x: hidden;
}

/* Sidebar */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: 260px;
  background-color: var(--white);
  border-right: 1px solid var(--border);
  box-shadow: 0 0 18px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  padding: 24px 18px;
  transition: width 0.3s ease;
  z-index: 1000;
}

/* Header */
.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--primary-green);
  font-weight: 700;
  font-size: 20px;
  white-space: nowrap;
}

.brand-logo {
  width: 38px;
  height: 38px;
  object-fit: contain;
  transition: all 0.3s ease;
}

.brand-text {
  transition: all 0.3s ease;
}

.sidebar.collapsed .brand-logo,
.sidebar.collapsed .brand-text {
  display: none;
}

.sidebar.collapsed .sidebar-header {
  justify-content: center;
}

.sidebar.collapsed .toggle-sidebar {
  position: relative;
  left: 0;
  transform: none;
}

.toggle-sidebar {
  border: none;
  background: transparent;
  color: var(--primary-green);
  font-size: 18px;
  cursor: pointer;
  border-radius: 6px;
  padding: 6px;
  transition: background 0.2s ease;
}

.toggle-sidebar:hover {
  background-color: #f1f5f3;
}

/* User Info */
.user-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  margin-bottom: 24px;
  transition: all 0.3s ease;
}

.profile-photo {
  width: 55px;
  height: 55px;
  border: 2px solid var(--primary-green);
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 8px;
  transition: all 0.3s ease;
}

.user-meta {
  transition: opacity 0.3s ease;
}

.user-name {
  font-weight: 600;
  font-size: 14px;
  color: var(--text);
  margin: 0;
}

.user-role {
  font-size: 12px;
  color: var(--white);
  background-color: var(--primary-green);
  padding: 2px 10px;
  border-radius: 12px;
  margin-top: 4px;
  display: inline-block;
}

/* Menu */
.menu-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.menu-link {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: var(--text);
  padding: 10px 14px;
  border-radius: 10px;
  font-weight: 500;
  transition: all 0.25s ease;
  white-space: nowrap;
}

.menu-link i {
  font-size: 18px;
  min-width: 22px;
  text-align: center;
}

.menu-link.active {
  background-color: var(--primary-green);
  color: var(--white);
}

.menu-link:hover {
  background-color: var(--primary-green);
  color: var(--white);
}

.menu-link:hover i {
  transform: scale(1.1);
}

.menu-text {
  transition: all 0.3s ease;
}

/* Logout */
.logout-area {
  margin-top: auto;
}

.logout-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  border: 1px solid var(--primary-green);
  background: transparent;
  color: var(--primary-green);
  font-weight: 600;
  border-radius: 10px;
  padding: 10px;
  transition: all 0.25s ease;
  cursor: pointer;
}

.logout-btn:hover {
  background: var(--primary-green);
  color: var(--white);
}

.logout-text {
  transition: all 0.3s ease;
}

/* ===== COLLAPSED STATE ===== */
.sidebar.collapsed {
  width: 80px;
}

.sidebar.collapsed .brand-text,
.sidebar.collapsed .user-meta,
.sidebar.collapsed .menu-text,
.sidebar.collapsed .logout-text {
  display: none;
}

.sidebar.collapsed .brand {
  justify-content: center;
}

.sidebar.collapsed .user-info {
  align-items: center;
}

.sidebar.collapsed .menu-link {
  justify-content: center;
  padding: 10px 0;
}

.sidebar.collapsed .logout-btn {
  justify-content: center;
}

/* Main Content */
.main-content {
  margin-left: 260px;
  padding: 32px;
  transition: margin-left 0.3s ease;
  min-height: 100vh;
}

.main-content.expanded {
  margin-left: 80px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
  }

  .sidebar.show {
    transform: translateX(0);
  }

  .main-content {
    margin-left: 0;
  }

  .main-content.expanded {
    margin-left: 0;
  }
}
    </style>

    @stack('styles')
</head>

<body>
    @php
        $user = Auth::user();

        $profilePhoto = asset('images/profile.jpg');
        if ($user && $user->foto_profil) {
            $profilePhoto = filter_var($user->foto_profil, FILTER_VALIDATE_URL)
                ? $user->foto_profil
                : asset('storage/' . ltrim($user->foto_profil, '/'));
        }

        $adminMenus = [
            [
                'label' => 'Dashboard',
                'icon' => 'fa-solid fa-gauge-high',
                'route' => route('dashboard.admin'),
                'match' => 'dashboard.admin',
            ],
            [
                'label' => 'Pengguna',
                'icon' => 'fa-solid fa-users-gear',
                'route' => route('admin.users.index'),
                'match' => 'admin.users.*',
            ],
            [
                'label' => 'Lapangan',
                'icon' => 'fa-solid fa-warehouse',
                'route' => route('admin.lapangan.index'),
                'match' => 'admin.lapangan.*',
            ],
            [
                'label' => 'Pemesanan',
                'icon' => 'fa-solid fa-calendar-check',
                'route' => route('admin.pemesanan.index'),
                'match' => 'admin.pemesanan.*',
            ],
            [
                'label' => 'Pembayaran',
                'icon' => 'fa-solid fa-money-bill-transfer',
                'route' => route('admin.pembayaran.index'),
                'match' => 'admin.pembayaran.*',
            ],
            [
                'label' => 'Laporan',
                'icon' => 'fa-solid fa-file-lines',
                'route' => '#',
                'match' => '',
            ],
            [
                'label' => 'Pengaturan Sistem',
                'icon' => 'fa-solid fa-gear',
                'route' => '#',
                'match' => '',
            ],
        ];
    @endphp

    <!-- Sidebar -->
    <aside class="sidebar" id="adminSidebar">
        <!-- Header with toggle -->
        <div class="sidebar-header">
            <div class="brand">
                <img src="{{ asset('images/logo-sewalap.png') }}" alt="Logo" class="brand-logo">
                <span class="brand-text">SewaLap Admin</span>
            </div>
            <button class="toggle-sidebar" id="toggleSidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <!-- User Info -->
        <div class="user-info">
            <img src="{{ $profilePhoto }}" alt="Profile" class="profile-photo">
            <div class="user-meta">
                <p class="user-name">{{ $user->name ?? 'Admin' }}</p>
                <span class="user-role text-capitalize">{{ $user->role ?? 'admin' }}</span>
            </div>
        </div>

        <!-- Menu List -->
        <nav class="menu-list">
            @foreach ($adminMenus as $item)
                <a href="{{ $item['route'] }}"
                   class="menu-link {{ $item['match'] && request()->routeIs($item['match']) ? 'active' : '' }}">
                    <i class="{{ $item['icon'] }}"></i>
                    <span class="menu-text">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <!-- Logout -->
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

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('adminSidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('toggleSidebar');

        // Toggle sidebar collapse
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        // Handle mobile view
        if (window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('show');
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
