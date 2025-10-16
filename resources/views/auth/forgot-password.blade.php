<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Lupa Password</title>
        <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="antialiased bg-gray-100">
        <div class="auth-container">
            <h1 class="text-2xl font-semibold text-center mb-6">Reset Password</h1>
            <p class="text-sm text-gray-600 text-center mb-6">
                Masukkan alamat email yang terdaftar. Kami akan mengirim tautan untuk mengganti password melalui Gmail atau layanan email Anda.
            </p>

            @if (session('status'))
                <div class="mb-4 rounded-md bg-blue-50 border border-blue-200 px-3 py-2 text-sm text-blue-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
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

                <button
                    type="submit"
                    class="w-full py-2 px-4 rounded-md bg-indigo-600 text-white font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Kirim Tautan Reset
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                Sudah ingat password?
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Kembali ke Login</a>
            </p>
        </div>
    </body>
</html>
