<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Register - Sewa Lapang</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                background: linear-gradient(135deg, #0f172a 0%, #1a1f2e 50%, #0f172a 100%);
                min-height: 100vh;
                color: #e2e8f0;
                position: relative;
                overflow: hidden;
            }

            .animated-bg {
                position: fixed;
                inset: 0;
                overflow: hidden;
                pointer-events: none;
                z-index: 1;
            }

            .blob-1 {
                position: absolute;
                width: 450px;
                height: 450px;
                background: radial-gradient(circle, rgba(139, 92, 246, 0.35) 0%, rgba(139, 92, 246, 0) 70%);
                border-radius: 50%;
                top: -150px;
                right: -100px;
                animation: float1 25s infinite;
                filter: blur(50px);
            }

            .blob-2 {
                position: absolute;
                width: 400px;
                height: 400px;
                background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, rgba(16, 185, 129, 0) 70%);
                border-radius: 50%;
                bottom: -100px;
                left: -80px;
                animation: float2 30s infinite;
                filter: blur(50px);
            }

            .blob-3 {
                position: absolute;
                width: 350px;
                height: 350px;
                background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, rgba(59, 130, 246, 0) 70%);
                border-radius: 50%;
                top: 50%;
                right: 10%;
                animation: float3 28s infinite;
                filter: blur(45px);
            }

            @keyframes float1 {
                0%, 100% { transform: translate(0, 0) scale(1); }
                50% { transform: translate(-40px, 40px) scale(1.1); }
            }

            @keyframes float2 {
                0%, 100% { transform: translate(0, 0) scale(1); }
                50% { transform: translate(40px, -40px) scale(1.1); }
            }

            @keyframes float3 {
                0%, 100% { transform: translate(0, 0) scale(1); }
                50% { transform: translate(-30px, 30px) scale(1.05); }
            }

            .container {
                position: relative;
                z-index: 10;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .wrapper {
                width: 100%;
                max-width: 1300px;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 40px;
                align-items: center;
            }

            .left-panel {
                background: rgba(30, 41, 59, 0.6);
                border: 1px solid rgba(148, 163, 184, 0.1);
                border-radius: 28px;
                padding: 48px 40px;
                backdrop-filter: blur(20px);
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: slideRight 0.9s ease-out;
            }

            @keyframes slideRight {
                from {
                    opacity: 0;
                    transform: translateX(-30px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            .form-header {
                margin-bottom: 28px;
                text-align: center;
            }

            .form-header h1 {
                font-size: 32px;
                font-weight: 700;
                color: white;
                margin-bottom: 8px;
            }

            .form-header p {
                font-size: 14px;
                color: #94a3b8;
            }

            .alert {
                margin-bottom: 20px;
                padding: 16px;
                background: rgba(59, 130, 246, 0.1);
                border: 1px solid rgba(59, 130, 246, 0.3);
                border-radius: 12px;
                color: #93c5fd;
                font-size: 13px;
                line-height: 1.5;
                animation: slideDown 0.5s ease-out;
            }

            .alert.warning {
                background: rgba(217, 119, 6, 0.1);
                border-color: rgba(217, 119, 6, 0.3);
                color: #fcd34d;
            }

            .alert code {
                background: rgba(255, 255, 255, 0.1);
                padding: 2px 6px;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
                font-size: 12px;
            }

            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-label {
                display: block;
                font-size: 13px;
                font-weight: 600;
                color: #cbd5e1;
                margin-bottom: 10px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .form-input,
            .form-select {
                width: 100%;
                padding: 14px 16px;
                background: rgba(15, 23, 42, 0.4);
                border: 1.5px solid rgba(148, 163, 184, 0.15);
                border-radius: 12px;
                color: #e2e8f0;
                font-size: 14px;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                outline: none;
                font-family: inherit;
            }

            .form-input:focus,
            .form-select:focus {
                background: rgba(15, 23, 42, 0.6);
                border-color: #10b981;
                box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            }

            .form-input::placeholder {
                color: #64748b;
            }

            .form-select {
                cursor: pointer;
                appearance: none;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right 12px center;
                background-size: 20px;
                padding-right: 40px;
            }

            .form-select option {
                background: #1e293b;
                color: #e2e8f0;
            }

            .error-message {
                color: #fca5a5;
                font-size: 12px;
                margin-top: 6px;
                display: flex;
                align-items: center;
                gap: 4px;
            }

            .password-strength {
                margin-top: 8px;
                height: 4px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 2px;
                overflow: hidden;
            }

            .password-strength-bar {
                height: 100%;
                background: linear-gradient(90deg, #ef4444 0%, #f97316 50%, #10b981 100%);
                width: 0%;
                transition: width 0.3s;
            }

            .submit-btn {
                width: 100%;
                padding: 14px 24px;
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                border: none;
                border-radius: 12px;
                color: white;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-top: 8px;
            }

            .submit-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 15px 40px rgba(16, 185, 129, 0.4);
            }

            .submit-btn:active {
                transform: translateY(0);
            }

            .form-footer {
                margin-top: 28px;
                text-align: center;
                font-size: 14px;
                color: #94a3b8;
            }

            .form-footer a {
                color: #10b981;
                text-decoration: none;
                font-weight: 600;
                transition: color 0.3s;
            }

            .form-footer a:hover {
                color: #6ee7b7;
                text-decoration: underline;
            }

            .right-panel {
                display: none;
                flex-direction: column;
                justify-content: space-between;
                animation: slideLeft 0.9s ease-out;
            }

            @keyframes slideLeft {
                from {
                    opacity: 0;
                    transform: translateX(30px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            @media (min-width: 1024px) {
                .right-panel {
                    display: flex;
                }
            }

            .brand {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 32px;
            }

            .brand-icon {
                width: 44px;
                height: 44px;
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 24px;
                color: white;
            }

            .brand-text {
                font-size: 24px;
                font-weight: 700;
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .right-panel h2 {
                font-size: 40px;
                font-weight: 700;
                line-height: 1.2;
                margin-bottom: 20px;
                color: white;
            }

            .right-panel p {
                font-size: 16px;
                color: #cbd5e1;
                line-height: 1.6;
                margin-bottom: 40px;
            }

            .benefits-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .benefit-card {
                background: rgba(16, 185, 129, 0.05);
                border: 1px solid rgba(16, 185, 129, 0.2);
                border-radius: 16px;
                padding: 24px;
                backdrop-filter: blur(10px);
                transition: all 0.3s;
            }

            .benefit-card:hover {
                background: rgba(16, 185, 129, 0.1);
                border-color: rgba(16, 185, 129, 0.4);
                transform: translateY(-2px);
            }

            .benefit-card:last-child {
                grid-column: 1 / -1;
            }

            .benefit-title {
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #10b981;
                margin-bottom: 8px;
            }

            .benefit-text {
                font-size: 15px;
                color: white;
                line-height: 1.5;
            }

            @media (max-width: 768px) {
                .wrapper {
                    grid-template-columns: 1fr;
                    gap: 24px;
                }

                .left-panel {
                    padding: 32px 24px;
                }

                .form-header h1 {
                    font-size: 24px;
                }

                .right-panel h2 {
                    font-size: 28px;
                }

                .benefits-grid {
                    grid-template-columns: 1fr;
                }

                .benefit-card:last-child {
                    grid-column: 1;
                }
            }
        </style>
    </head>
    <body>
        <div class="animated-bg">
            <div class="blob-1"></div>
            <div class="blob-2"></div>
            <div class="blob-3"></div>
        </div>

        <div class="container">
            <div class="wrapper">
                <div class="left-panel">
                    <div class="form-header">
                        <h1>Buat Akun</h1>
                        <p>Mulai kelola dan sewakan lapangan favorit Anda sekarang</p>
                    </div>

                    @if (session('status'))
                        <div class="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="alert warning">
                            {{ session('warning') }}
                        </div>
                    @endif

                    @if (empty($notificationEmail))
                        <div class="alert warning">
                            Pastikan variabel <code>REGISTER_NOTIFICATION_EMAIL</code> pada file <code>.env</code> sudah terisi untuk menerima notifikasi pendaftaran.
                        </div>
                    @endif

                    @php
                        $availableRoles = isset($roles) && is_array($roles) && count($roles) ? $roles : ['penyewa'];
                        $defaultRole = old('role', $availableRoles[0] ?? 'penyewa');
                    @endphp

                    <form method="POST" action="{{ url('/register') }}" class="space-y-5">
                        @csrf

                        <div class="form-group">
                            <label class="form-label" for="name">Nama Lengkap</label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Masukkan nama lengkap Anda"
                                required
                                autofocus
                                class="form-input"
                            >
                            @error('name')
                                <div class="error-message">⚠️ {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">Email Address</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="nama@email.com"
                                required
                                class="form-input"
                            >
                            @error('email')
                                <div class="error-message">⚠️ {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="••••••••"
                                required
                                class="form-input"
                            >
                            <div class="password-strength">
                                <div class="password-strength-bar"></div>
                            </div>
                            @error('password')
                                <div class="error-message">⚠️ {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                placeholder="••••••••"
                                required
                                class="form-input"
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="role">Pilih Role</label>
                            <select
                                id="role"
                                name="role"
                                required
                                class="form-select"
                            >
                                @foreach ($availableRoles as $role)
                                    <option value="{{ $role }}" @selected($defaultRole === $role)>
                                        {{ ucfirst(str_replace('_', ' ', $role)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="error-message">⚠️ {{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="submit-btn">Daftar Sekarang</button>
                    </form>

                    <div class="form-footer">
                        Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
                    </div>
                </div>

                <div class="right-panel">
                    <div>
                        <div class="brand">
                            <div class="brand-icon">⚽</div>
                            <div class="brand-text">Sewa Lapang</div>
                        </div>
                        <h2>Gabung dengan komunitas olahraga terbaik</h2>
                        <p>Tingkatkan pengalaman booking lapangan dengan sistem modern, dukungan multi-role untuk pemilik dan penyewa, serta automasi notifikasi real-time.</p>
                    </div>
                    <div class="benefits-grid">
                        <div class="benefit-card">
                            <div class="benefit-title">📅 Pengelolaan Jadwal</div>
                            <div class="benefit-text">Optimalkan slot lapangan agar selalu produktif dan terkelola dengan baik.</div>
                        </div>
                        <div class="benefit-card">
                            <div class="benefit-title">🔔 Notifikasi Otomatis</div>
                            <div class="benefit-text">Pemberitahuan cepat untuk setiap transaksi baru dan update penting.</div>
                        </div>
                        <div class="benefit-card">
                            <div class="benefit-title">👥 Komunitas Aktif</div>
                            <div class="benefit-text">Terhubung dengan pemain lain, nikmati promo eksklusif, dan bangun jaringan profesional.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            const passwordInput = document.getElementById('password');
            const strengthBar = document.querySelector('.password-strength-bar');

            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    let strength = 0;

                    if (password.length >= 6) strength += 25;
                    if (password.length >= 10) strength += 25;
                    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
                    if (/\d/.test(password)) strength += 15;
                    if (/[!@#$%^&*]/.test(password)) strength += 10;

                    strengthBar.style.width = Math.min(strength, 100) + '%';
                });
            }
        </script>
    </body>
</html>
