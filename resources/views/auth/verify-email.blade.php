<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Verifikasi Email</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-fade-in {
                animation: fadeIn 0.6s ease-out;
            }
            .gradient-green {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            }
            .hover-lift {
                transition: all 0.3s ease;
            }
            .hover-lift:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 24px rgba(16, 185, 129, 0.3);
            }
        </style>
    </head>
    <body class="antialiased bg-gradient-to-br from-gray-50 via-green-50 to-gray-50 min-h-screen">
        <div class="min-h-screen flex items-center justify-center px-4 py-8">
            <div class="w-full max-w-md">
                <!-- Header dengan icon -->
                <div class="text-center mb-8 animate-fade-in">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full gradient-green mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Verifikasi Email</h1>
                    <p class="text-gray-500">Selesaikan proses verifikasi akun Anda</p>
                </div>

                <!-- Main Card -->
                <div class="bg-white shadow-xl rounded-2xl p-8 space-y-6 hover-lift animate-fade-in">
                    <!-- Error Message -->
                    @if ($errors->has('verification'))
                        <div class="flex items-start p-4 rounded-lg bg-red-50 border border-red-200 space-x-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-red-700">{{ $errors->first('verification') }}</span>
                        </div>
                    @endif

                    <!-- Success Message with Button -->
                    @if (session('fallbackVerificationUrl'))
                        <div class="flex items-start p-4 rounded-lg bg-green-50 border border-green-200 space-x-3">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="font-medium text-green-900 mb-2">Tautan verifikasi cadangan siap digunakan</p>
                                <a
                                    href="{{ session('fallbackVerificationUrl') }}"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg gradient-green text-white font-medium hover:opacity-90 transition-opacity text-sm"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                    Verifikasi Sekarang
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Status Message -->
                    @if (session('status'))
                        <div class="flex items-start p-4 rounded-lg bg-blue-50 border border-blue-200 space-x-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0zm3 1a1 1 0 100-2 1 1 0 000 2zm2 0a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-blue-700">{{ session('status') }}</span>
                        </div>
                    @else
                        <div class="flex items-start p-4 rounded-lg bg-amber-50 border border-amber-200 space-x-3">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-sm text-amber-800">
                                Menunggu verifikasi dari Google. Silakan cek email Anda dan tekan tombol <strong>Accept</strong>.
                            </div>
                        </div>
                    @endif

                    <!-- Description -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4 border border-green-100">
                        <p class="text-sm text-gray-700 leading-relaxed">
                            Kami telah mengirim email verifikasi ke <span class="font-semibold text-green-700">{{ auth()->user()->email }}</span>. Klik tombol <strong>Accept</strong> pada email tersebut untuk mengaktifkan akun Anda.
                        </p>
                    </div>

                    <!-- Resend Email Form -->
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full py-3 px-4 rounded-lg gradient-green text-white font-semibold hover:shadow-lg transition-all duration-200 flex items-center justify-center space-x-2 hover:opacity-95"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span>Kirim Ulang Email Verifikasi</span>
                        </button>
                    </form>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}" class="text-center pt-2">
                        @csrf
                        <button
                            type="submit"
                            class="text-sm text-gray-500 hover:text-green-600 transition-colors duration-200 font-medium"
                        >
                            Keluar dari akun
                        </button>
                    </form>
                </div>

                <!-- Footer Note -->
                <p class="text-center text-xs text-gray-400 mt-6">
                    Butuh bantuan? Hubungi tim support kami
                </p>
            </div>
        </div>
    </body>
</html>
