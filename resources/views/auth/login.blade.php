<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Masuk</title>
        <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="antialiased bg-gray-100">
        <div class="auth-container">
                <h1 class="text-2xl font-semibold text-center mb-6">Masuk ke akun Anda</h1>
                @if (session('status'))
                    <div class="mb-4 rounded-md bg-blue-50 border border-blue-200 px-3 py-2 text-sm text-blue-700">
                        {{ session('status') }}
                    </div>
                @endif
                <form method="POST" action="{{ url('/login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="password">Kata sandi</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center text-sm text-gray-600">
                            <input
                                type="checkbox"
                                name="remember"
                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <span class="ml-2">Ingat saya</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                Lupa kata sandi?
                            </a>
                        @endif
                    </div>

                    <button
                        type="submit"
                        class="w-full py-2 px-4 rounded-md bg-indigo-600 text-white font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 login-button"
                    >
                        <span class="button-loader" aria-hidden="true"></span>
                        <span class="button-text">Masuk</span>
                    </button>

                    <div id="login-loading" class="loading-indicator" aria-live="polite">
                        <span class="spinner" aria-hidden="true"></span>
                        <span class="loading-text">Sedang memverifikasi, mohon tunggu...</span>
                    </div>
                </form>

                <p class="mt-6 text-center text-sm text-gray-600">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Daftar</a>
                </p>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.querySelector('form');
                const submitButton = form?.querySelector('.login-button');
                const buttonText = submitButton?.querySelector('.button-text');
                const loadingIndicator = document.getElementById('login-loading');
                const defaultButtonText = buttonText?.textContent?.trim() || 'Masuk';

                if (!form || !submitButton || !buttonText || !loadingIndicator) return;

                const resetLoadingState = () => {
                    form.dataset.submitting = 'false';
                    submitButton.disabled = false;
                    submitButton.classList.remove('is-loading');
                    buttonText.textContent = defaultButtonText;
                    loadingIndicator.classList.remove('active');
                };

                // Ensure state is clean on initial load (e.g., after failed login redirect or bfcache).
                resetLoadingState();

                form.addEventListener('submit', () => {
                    if (form.dataset.submitting === 'true') return;

                    form.dataset.submitting = 'true';
                    submitButton.disabled = true;
                    submitButton.classList.add('is-loading');
                    buttonText.textContent = 'Memverifikasi...';
                    loadingIndicator.classList.add('active');
                });

                // Handle browser back/forward cache where submit state might persist visually.
                window.addEventListener('pageshow', resetLoadingState);
            });
        </script>
    </body>
</html>
