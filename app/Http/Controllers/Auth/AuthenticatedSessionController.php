<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'email' => ['required', 'string', 'lowercase', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (! Auth::attempt($credentials, $request->boolean('remember'))) {
        return back()
            ->withErrors([
                'email' => __('The provided credentials do not match our records.'),
            ])
            ->onlyInput('email');
    }

    $request->session()->regenerate();

    $user = $request->user();

    if ($user->status === 'nonaktif') {
        Auth::logout();

        return back()
            ->withErrors([
                'email' => __('Akun Anda dinonaktifkan. Silakan hubungi administrator.'),
            ])
            ->onlyInput('email');
    }

    // Cek verifikasi email (kalau kamu pakai fitur itu)
    if ($user instanceof MustVerifyEmailContract && ! $user->hasVerifiedEmail()) {
        return redirect()
            ->route('verification.notice')
            ->with('status', __('Menunggu verifikasi dari Google.'));
    }

    // 🚀 Arahkan berdasarkan role pengguna
    if ($user->role === 'penyewa') {
        return redirect()->route('penyewa.beranda')->with('status', 'Login berhasil sebagai Penyewa.');
    } elseif ($user->role === 'pemilik') {
        return redirect()->route('dashboard.pemilik')->with('status', 'Login berhasil sebagai Pemilik.');
    } elseif ($user->role === 'admin') {
        return redirect()->route('dashboard.admin');
    }

    // Default kalau role-nya tidak dikenali
    return redirect('/')
        ->with('status', __('Login berhasil.'));
}


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login'); // <-- ini yang bikin diarahkan ke login
    }
}