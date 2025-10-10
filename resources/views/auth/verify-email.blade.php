<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Verifikasi Email</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="antialiased bg-gray-100">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="w-full max-w-lg bg-white shadow rounded-lg p-6 space-y-6">
                <h1 class="text-2xl font-semibold text-center">Verifikasi Email</h1>

                @if ($errors->has('verification'))
                    <div class="rounded-md bg-red-50 border border-red-200 px-3 py-2 text-sm text-red-700">
                        {{ $errors->first('verification') }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="rounded-md bg-blue-50 border border-blue-200 px-3 py-2 text-sm text-blue-700">
                        {{ session('status') }}
                    </div>
                @else
                    <div class="rounded-md bg-yellow-50 border border-yellow-200 px-3 py-2 text-sm text-yellow-800">
                        Menunggu verifikasi dari Google. Silakan cek email Anda dan tekan tombol <strong>Accept</strong>.
                    </div>
                @endif

                <p class="text-sm text-gray-600">
                    Kami telah mengirim email verifikasi ke <strong>{{ auth()->user()->email }}</strong>. Klik tombol <strong>Accept</strong> pada email tersebut untuk mengaktifkan akun Anda. Jika belum menerima email,
                    Anda dapat mengirim ulang menggunakan tombol di bawah.
                </p>

                <form method="POST" action="{{ route('verification.send') }}" class="space-y-2">
                    @csrf
                    <button
                        type="submit"
                        class="w-full py-2 px-4 rounded-md bg-indigo-600 text-white font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="text-center">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </body>
</html>
