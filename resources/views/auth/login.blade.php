<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                min-height: 100vh;
                background: linear-gradient(135deg, #0f172a 0%, #1a1f35 50%, #0f172a 100%);
                text-slate-100;
                antialiased: true;
                position: relative;
                overflow: hidden;
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }

            /* Enhanced animated background */
            .pointer-events-none {
                pointer-events: none;
                position: absolute;
                inset: 0;
            }

            .absolute {
                position: absolute;
            }

            .inset-0 {
                inset: 0;
            }

            .rounded-full {
                border-radius: 9999px;
            }

            .blur-3xl {
                filter: blur(64px);
            }

            .bg-indigo-500\/40 {
                background-color: rgb(99, 102, 241, 0.4);
            }

            .bg-sky-400\/30 {
                background-color: rgb(56, 189, 248, 0.3);
            }

            .-left-40 {
                left: -160px;
            }

            .-top-40 {
                top: -160px;
            }

            .h-80 {
                height: 320px;
            }

            .w-80 {
                width: 320px;
            }

            .-right-20 {
                right: -80px;
            }

            .top-1\/2 {
                top: 50%;
            }

            .h-72 {
                height: 288px;
            }

            .w-72 {
                width: 288px;
            }

            .-translate-y-1\/2 {
                transform: translateY(-50%);
            }

            /* Add animation to blobs */
            .absolute:nth-child(1) {
                animation: float-blob-1 20s infinite ease-in-out;
            }

            .absolute:nth-child(2) {
                animation: float-blob-2 25s infinite ease-in-out;
            }

            @keyframes float-blob-1 {
                0%, 100% {
                    transform: translate(0, 0);
                }
                25% {
                    transform: translate(30px, -30px);
                }
                50% {
                    transform: translate(0, -50px);
                }
                75% {
                    transform: translate(-30px, -20px);
                }
            }

            @keyframes float-blob-2 {
                0%, 100% {
                    transform: translateY(-50%);
                }
                25% {
                    transform: translateY(calc(-50% + 30px)) translateX(-30px);
                }
                50% {
                    transform: translateY(calc(-50% + 50px)) translateX(0);
                }
                75% {
                    transform: translateY(calc(-50% + 20px)) translateX(30px);
                }
            }

            .relative {
                position: relative;
            }

            .flex {
                display: flex;
            }

            .min-h-screen {
                min-height: 100vh;
            }

            .items-center {
                align-items: center;
            }

            .justify-center {
                justify-content: center;
            }

            .px-6 {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }

            .py-12 {
                padding-top: 3rem;
                padding-bottom: 3rem;
            }

            .w-full {
                width: 100%;
            }

            .max-w-5xl {
                max-width: 64rem;
            }

            .grid {
                display: grid;
            }

            .gap-10 {
                gap: 2.5rem;
            }

            .lg\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            /* Left panel */
            .hidden {
                display: none;
            }

            .rounded-3xl {
                border-radius: 1.5rem;
            }

            .border {
                border-width: 1px;
            }

            .border-white\/10 {
                border-color: rgb(255, 255, 255, 0.1);
            }

            .bg-white\/5 {
                background-color: rgb(255, 255, 255, 0.05);
            }

            .p-10 {
                padding: 2.5rem;
            }

            .shadow-2xl {
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            }

            .backdrop-blur-xl {
                backdrop-filter: blur(20px);
            }

            .lg\:flex {
                display: flex;
            }

            .flex-col {
                flex-direction: column;
            }

            .justify-between {
                justify-content: space-between;
            }

            .inline-flex {
                display: inline-flex;
            }

            .rounded-full {
                border-radius: 9999px;
            }

            .bg-indigo-500\/10 {
                background-color: rgb(99, 102, 241, 0.1);
            }

            .px-3 {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            .py-1 {
                padding-top: 0.25rem;
                padding-bottom: 0.25rem;
            }

            .text-xs {
                font-size: 0.75rem;
                line-height: 1rem;
            }

            .font-semibold {
                font-weight: 600;
            }

            .uppercase {
                text-transform: uppercase;
            }

            .tracking-widest {
                letter-spacing: 0.125em;
            }

            .text-indigo-200 {
                color: rgb(199, 210, 254);
            }

            .mt-6 {
                margin-top: 1.5rem;
            }

            .text-4xl {
                font-size: 2.25rem;
                line-height: 2.5rem;
            }

            .font-bold {
                font-weight: 700;
            }

            .leading-tight {
                line-height: 1.25;
            }

            .text-white {
                color: rgb(255, 255, 255);
            }

            .mt-4 {
                margin-top: 1rem;
            }

            .text-slate-200\/80 {
                color: rgb(226, 232, 240, 0.8);
            }

            .mt-8 {
                margin-top: 2rem;
            }

            .space-y-4 {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .text-sm {
                font-size: 0.875rem;
                line-height: 1.25rem;
            }

            .text-slate-200\/70 {
                color: rgb(226, 232, 240, 0.7);
            }

            .items-start {
                align-items: flex-start;
            }

            .gap-3 {
                gap: 0.75rem;
            }

            .mt-1 {
                margin-top: 0.25rem;
            }

            .h-6 {
                height: 1.5rem;
            }

            .w-6 {
                width: 1.5rem;
            }

            .bg-indigo-500\/20 {
                background-color: rgb(99, 102, 241, 0.2);
            }

            /* Right panel */
            .bg-slate-900\/70 {
                background-color: rgb(15, 23, 42, 0.7);
            }

            .gap-2 {
                gap: 0.5rem;
            }

            .text-center {
                text-align: center;
            }

            .lg\:text-left {
                text-align: left;
            }

            .text-3xl {
                font-size: 1.875rem;
                line-height: 2.25rem;
            }

            .text-medium {
                font-weight: 500;
            }

            /* Form styles */
            .form-wrapper {
                margin-top: 2rem;
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .space-y-6 {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .space-y-2 {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .block {
                display: block;
            }

            .font-medium {
                font-weight: 500;
            }

            .bg-slate-900\/60 {
                background-color: rgb(15, 23, 42, 0.6);
            }

            .rounded-2xl {
                border-radius: 1rem;
            }

            .px-4 {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .py-3 {
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
            }

            .text-slate-100 {
                color: rgb(241, 245, 249);
            }

            .shadow-sm {
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            }

            .outline-none {
                outline: 2px solid transparent;
                outline-offset: 2px;
            }

            .transition {
                transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
                transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                transition-duration: 150ms;
            }

            .focus\:border-indigo-400:focus {
                border-color: rgb(129, 140, 248);
            }

            .focus\:ring-2:focus {
                box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.4);
            }

            .focus\:ring-indigo-500\/40:focus {
                --tw-ring-color: rgb(99, 102, 241, 0.4);
            }

            .text-rose-300 {
                color: rgb(252, 165, 165);
            }

            .flex-items-center {
                display: flex;
                align-items: center;
            }

            .justify-between {
                justify-content: space-between;
            }

            .rounded {
                border-radius: 0.25rem;
            }

            .border-white\/20 {
                border-color: rgb(255, 255, 255, 0.2);
            }

            .bg-transparent {
                background-color: transparent;
            }

            .text-indigo-500 {
                color: rgb(99, 102, 241);
            }

            .focus\:ring-indigo-400:focus {
                --tw-ring-color: rgb(129, 140, 248);
            }

            .bg-indigo-500 {
                background-color: rgb(99, 102, 241);
            }

            .shadow-lg {
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            }

            .shadow-indigo-950\/50 {
                box-shadow: 0 10px 15px -3px rgb(30, 27, 75, 0.5);
            }

            .hover\:bg-indigo-400:hover {
                background-color: rgb(129, 140, 248);
            }

            .focus-visible\:ring-2:focus-visible {
                box-shadow: 0 0 0 2px rgb(129, 140, 248);
            }

            .focus-visible\:ring-offset-2:focus-visible {
                outline-offset: 2px;
            }

            .focus-visible\:ring-offset-slate-950:focus-visible {
                outline-color: rgb(2, 6, 23);
            }

            .focus-visible\:ring-indigo-400:focus-visible {
                --tw-ring-color: rgb(129, 140, 248);
            }

            .text-indigo-300 {
                color: rgb(165, 180, 252);
            }

            .hover\:text-indigo-200:hover {
                color: rgb(199, 210, 254);
            }

            /* Enhanced styles */
            input:focus {
                border-color: rgb(129, 140, 248) !important;
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
            }

            input::placeholder {
                color: rgb(100, 116, 139);
            }

            button {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            button:hover {
                transform: translateY(-2px);
                box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.4) !important;
            }

            button:active {
                transform: translateY(0);
            }

            /* Responsive */
            @media (max-width: 1024px) {
                .hidden {
                    display: none;
                }

                .lg\:grid-cols-2 {
                    grid-template-columns: 1fr;
                }

                .lg\:flex {
                    display: none;
                }

                .lg\:text-left {
                    text-align: center;
                }

                .gap-10 {
                    gap: 1.5rem;
                }
            }
        </style>
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100 antialiased relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -left-40 -top-40 h-80 w-80 rounded-full bg-indigo-500/40 blur-3xl"></div>
            <div class="absolute -right-20 top-1/2 h-72 w-72 -translate-y-1/2 rounded-full bg-sky-400/30 blur-3xl"></div>
        </div>
        <div class="relative flex min-h-screen items-center justify-center px-6 py-12">
            <div class="w-full max-w-5xl grid gap-10 lg:grid-cols-2">
                <div class="hidden rounded-3xl border border-white/10 bg-white/5 p-10 shadow-2xl backdrop-blur-xl lg:flex flex-col justify-between">
                    <div>
                        <span class="inline-flex items-center rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-indigo-200">Sewa Lapang</span>
                        <h2 class="mt-6 text-4xl font-bold leading-tight text-white">Selamat datang kembali!</h2>
                        <p class="mt-4 text-slate-200/80">
                            Akses dashboard untuk mengelola dan menyewa lapangan favorit Anda dengan pengalaman yang modern, cepat, dan aman.
                        </p>
                    </div>
                    <ul class="mt-8 space-y-4 text-sm text-slate-200/70">
                        <li class="flex items-start gap-3">
                            <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-500/20 text-xs font-semibold text-indigo-200">1</span>
                            Kelola jadwal dan pemesanan secara real-time.
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-500/20 text-xs font-semibold text-indigo-200">2</span>
                            Pantau status pembayaran dan riwayat booking dengan mudah.
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-500/20 text-xs font-semibold text-indigo-200">3</span>
                            Komunitas olahraga aktif dengan banyak pilihan lapangan.
                        </li>
                    </ul>
                </div>
                <div class="relative rounded-3xl border border-white/10 bg-slate-900/70 p-10 shadow-2xl backdrop-blur-xl">
                    <div class="flex flex-col gap-2 text-center lg:text-left">
                        <h1 class="text-3xl font-semibold text-white">Log in ke akun Anda</h1>
                        <p class="text-sm text-slate-200/70">Masukkan kredensial untuk melanjutkan ke dashboard.</p>
                    </div>
                    @if (session('status'))
                        <div class="mt-6 rounded-xl border border-blue-400/30 bg-blue-500/10 px-4 py-3 text-sm text-blue-200">
                            {{ session('status') }}
                        </div>
                    @endif
                    <form method="POST" action="{{ url('/login') }}" class="mt-8 space-y-6">
                        @csrf

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-slate-200/90" for="email">Email</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                placeholder="nama@email.com"
                                class="block w-full rounded-2xl border border-white/10 bg-slate-900/60 px-4 py-3 text-slate-100 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/40"
                            >
                            @error('email')
                                <p class="text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-slate-200/90" for="password">Password</label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="••••••••"
                                required
                                class="block w-full rounded-2xl border border-white/10 bg-slate-900/60 px-4 py-3 text-slate-100 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/40"
                            >
                            @error('password')
                                <p class="text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between text-sm text-slate-200/70">
                            <label class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    class="h-4 w-4 rounded border-white/20 bg-transparent text-indigo-500 focus:ring-indigo-400"
                                    {{ old('remember') ? 'checked' : '' }}
                                >
                                Ingat saya
                            </label>
                        </div>

                        <button
                            type="submit"
                            class="relative inline-flex w-full items-center justify-center overflow-hidden rounded-2xl bg-indigo-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-950/50 transition hover:bg-indigo-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950 focus-visible:ring-indigo-400"
                        >
                            Masuk
                        </button>
                    </form>

                    <p class="mt-8 text-center text-sm text-slate-200/70">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="font-semibold text-indigo-300 hover:text-indigo-200">Daftar sekarang</a>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
