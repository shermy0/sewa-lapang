<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Lapangan</title>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="logo">
            ⚽ SewaLap
            <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
        </div>

        <ul>
            @if(auth()->user()->role === 'pemilik' || auth()->user()->role === 'admin')
                <li><a href="#"><i class="fas fa-tachometer-alt"></i><span> Dashboard</span></a></li>
                <li><a href="#"><i class="fas fa-futbol"></i><span> Data Lapangan</span></a></li>
                <li><a href="#"><i class="fas fa-calendar-check"></i><span> Pemesanan</span></a></li>
                <li><a href="#"><i class="fas fa-money-check-alt"></i><span> Pembayaran</span></a></li>
                <li><a href="#"><i class="fas fa-chart-line"></i><span> Laporan</span></a></li>
                <li><a href="#"><i class="fas fa-users"></i><span> Pengguna</span></a></li>
                <li><a href="#"><i class="fas fa-cog"></i><span> Pengaturan Akun</span></a></li>
                <li><a href="{{ route('logout') }}"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a></li>
            @else
                <li><a href="#"><i class="fas fa-home"></i><span> Beranda</span></a></li>
                <li><a href="#"><i class="fas fa-search"></i><span> Cari Lapangan</span></a></li>
                <li><a href="#"><i class="fas fa-calendar"></i><span> Pemesanan Saya</span></a></li>
                <li><a href="#"><i class="fas fa-wallet"></i><span> Pembayaran</span></a></li>
                <li><a href="#"><i class="fas fa-history"></i><span> Riwayat Sewa</span></a></li>
                <li><a href="#"><i class="fas fa-user-cog"></i><span> Pengaturan Akun</span></a></li>
                <li><a href="{{ route('logout') }}"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a></li>
            @endif
        </ul>
    </div>

    <div class="main-content">
        @yield('content')
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        }
    </script>
</body>
</html>
