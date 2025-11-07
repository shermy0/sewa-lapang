<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin SewaLap')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">


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
                'label' => 'Pembayaran',
                'icon' => 'fa-solid fa-money-bill-transfer',
                'route' => route('admin.pembayaran.index'),
                'match' => 'admin.pembayaran.*',
            ],
            [
                'label' => 'Laporan Penyalahgunaan',
                'icon' => 'fa-solid fa-flag',
                'route' => route('admin.laporan.penyalahgunaan.index'),
                'match' => 'admin.laporan.penyalahgunaan.*',
            ],
            [
                'label' => 'Pengaturan Akun',
                'icon' => 'fa-solid fa-user-gear',
                'route' => route('admin.account.edit'),
                'match' => 'admin.account.*',
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const sidebar = document.getElementById('adminSidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('toggleSidebar');

        if (toggleBtn && sidebar && mainContent) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });

            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            }

            window.addEventListener('resize', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('show');
                }
            });
        }

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
